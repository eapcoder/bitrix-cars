<?
use Bitrix\Main\Application;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;

\Bitrix\Main\Loader::IncludeModule("highloadblock");

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class Cars extends CBitrixComponent
{
    
    // обработка массива $arParams (метод подключается автоматически)
    public function onPrepareComponentParams($arParams)
    {
        Loc::loadMessages(__FILE__);
        // время кеширования
        if (!isset($arParams['CACHE_TIME'])) {
            $arParams['CACHE_TIME'] = 3600;
        } else {
            $arParams['CACHE_TIME'] = intval($arParams['CACHE_TIME']);
        }
           
        return $arParams;
    }

    public function executeComponent()
    {
        try {
            $this->checkModules();
            $this->getResult();
            
        } catch (SystemException $e) {
            ShowError($e->getMessage());
        }
    }

    // подготовка массива $arResult (метод подключается внутри класса try...catch)
    protected function getResult()
    {
        global $USER;
        
        if (!$USER->IsAuthorized()) {
            ShowError(Loc::getMessage('ERROR_NO_USER'));
        }
       
        $start = htmlentities($_GET['start']);
        $end = htmlentities($_GET['end']);
        
        // если выбран режим поддержки ЧПУ, вызываем метод sefMode()
        if ($this->arParams["SEF_MODE"] === "Y") {
            $componentPage = $this->sefMode();
            $this->arParams["ENDSTART"] = '/?start=' . $start . '&end=' . $end . '&clear_cache=Y';
            
        }
        // если отключен режим поддержки ЧПУ, вызываем метод noSefMode()
        if ($this->arParams["SEF_MODE"] != "Y") {
            $componentPage = $this->noSefMode();
            $this->arParams["ENDSTART"] = '&start=' . $start . '&end=' . $end . '&clear_cache=Y';
        }

        // если нет валидного кеша, получаем данные из БД
        if ($this->startResultCache(false, $USER->getId(). $start. $end)) //startResultCache используется не для кеширования html, а для кеширования arResult
        {
            // Запрос к инфоблоку через класс ORM
            if (CModule::IncludeModule("iblock")) {
                $iblock_id = \Bitrix\Iblock\IblockTable::getList(['filter'=>['CODE'=> $this->arParams['IBLOCK_TYPE']]])->Fetch()["ID"];
                
                if(empty($arrFilter = $this->getUserData())) {
              
                    $this->IncludeComponentTemplate('error');

                } else{
                    //TODO Для предварительного вывода на главной странице комонента
                    //TODO $onIndexPage = $this->onIndexPage($iblock_id, $arrFilter);

                    if (isset($this->arResult)) {

                        $this->SetResultCacheKeys(
                            array('')
                        );
                        // подключаем шаблон и сохраняем кеш
                        $this->IncludeComponentTemplate($componentPage);
                    } else { // если выяснилось что кешировать данные не требуется, прерываем кеширование и выдаем сообщение «Страница не найдена»
                      
                        $this->pushError();
                    }
                }

            }
        }

        
    }

    /**
     * //TODO
     */
    protected function onIndexPage($iblock_id, $arrFilter) {

        $my_elements = CIBlockElement::GetList(
            ["ID" => "ASC"], // Сортировка
            ["IBLOCK_ID" => $iblock_id, $arrFilter], // Фильтр
            false, // Группировка
            false, // Постраничная навигация
            ['ID', 'NAME', 'DETAIL_PAGE_URL', 'PROPERTY_WORK_START', 'PROPERTY_WORK_END'] // Выбираемые поля
        );

        while ($arResult = $my_elements->GetNext()) {

            $this->arResult['ITEMS'][] = $arResult;
            //Debug::dump($arResult);
            ///echo urldecode($arResult['DETAIL_PAGE_URL']) . "<br>";
        }
    }

     // метод обработки режима ЧПУ
    protected function sefMode()
    {
        //******************************************************//
        // Обработка GET параметров                             //
        //******************************************************//
        // дополнительные GET параметры которые будем отлавливать в запросе, в массив $arVariables будет добавлена переменная sort, значение которой будет получено из $_REQUEST['sort'], применяется когда не нужно указывать точный псевдоним для ключа
        $arComponentVariables = [
            'sort'
        ];
        // дополнительные GET параметры которые будем отлавливать в запросе, полезно например для постраничной навигации. В массив $arVariableAliases будет добавлена переменная ELEMENT_COUNT, значение которой будет получено из $_REQUEST['count'], отлавливаться параметр будет только в разделе section, в итоге данные попадут в $arVariables, применяется когда нужно указать точный псевдоним для ключа
        $arDefaultVariableAliases404 = array(
            'section' => array(
                'ELEMENT_COUNT' => 'count',
            )
        );
        // метод предназначен для объединения дефолтных GET параметров которые приходят в $arParams["VARIABLE_ALIASES"], в режиме ЧПУ $arParams["VARIABLE_ALIASES"] будет пустой и дополнительных GET параметров из массива $arDefaultVariableAliases404. Параметры из настроек $arrParams заменяют дополнительные из $arDefaultVariableAliases404
        $arVariableAliases = CComponentEngine::makeComponentVariableAliases(
            // массив псевдонимов переменных из GET параметра
            $arDefaultVariableAliases404,
            // массив псевдонимов из $arParams, в режиме ЧПУ $arParams["VARIABLE_ALIASES"] будет пустой
            $this->arParams["VARIABLE_ALIASES"]
        );
        //*****************************************************//
        // Обработка данных по маске из URL запроса           //
        //*****************************************************//
        // если в комплексном компоненте не задан базовый URL
        
        if (empty($this->arParams["SEF_FOLDER"])) {
            // получаем данные из настроек инфоблока
            $dbResult = CIBlock::GetByID($this->arParams["IBLOCK_ID"])->GetNext();
            if (!empty($dbResult)) {
                // перетираем данные в $arParams["SEF_URL_TEMPLATES"]
                $this->arParams["SEF_URL_TEMPLATES"]["element"] = $dbResult["DETAIL_PAGE_URL"];
                $this->arParams["SEF_URL_TEMPLATES"]["section"] = $dbResult["SECTION_PAGE_URL"];
                $this->arParams["SEF_FOLDER"] = $dbResult["LIST_PAGE_URL"];
            }
        }
        // значение маски URL по умолчанию
        $arDefaultUrlTemplates404 = [
            "section" => "#SECTION_CODE#/",
            "element" => "#SECTION_CODE#/#ELEMENT_CODE#/",
        ];
        // метод предназначен для объединения дефолтных параметров масок URL которые приходят в arParams["SEF_URL_TEMPLATES"] и из массива $arDefaultUrlTemplates404. Параметры из настроек $arrParams заменяют дефолтные из $arDefaultUrlTemplates404
        $arUrlTemplates = CComponentEngine::makeComponentUrlTemplates(
            // массив переменных с масками по умолчанию
            $arDefaultUrlTemplates404,
            // массив переменных с масками из входных параметров $arParams["SEF_URL_TEMPLATES"]
            $this->arParams["SEF_URL_TEMPLATES"]
        );
        //*****************************************************//
        // Получение шаблона для подключения                   //
        //*****************************************************//
        // объект для поиска шаблонов
        $engine = new CComponentEngine($this);
        // главная переменная комплексного компонента, именно она будут записана в массив $arResult, как результат работы комплексного компонента. Она будет доступна в файлах section.php, element.php, index.php, которые будут подключены, после того как отработает class.php
        $arVariables = [];
        // определение шаблона, какой файл подключать section.php, element.php, index.php и заполнение $arVariables получеными URL в соответствие с масками
        $componentPage = $engine->guessComponentPath(
            // путь до корня секции
            $this->arParams["SEF_FOLDER"],
            // массив масок
            $arUrlTemplates,
            // путь до секции SECTION_CODE и элемента ELEMENT_CODE
            $arVariables
        );
        // проверяем, если не удалось сопоставить шаблон, значит выводим index.php
        if ($componentPage == FALSE) {
            $componentPage = 'index';
        }
        //*****************************************************//
        // Формируем $arResult                                 //
        //*****************************************************//
        // метод предназначен для объединения GET и URL параметров, результат записываем в $arVariables
        CComponentEngine::initComponentVariables(
            // нужен для режима ЧПУ, содержит файл который будет подключен section.php, element.php, index.php
            $componentPage,
            // массив дополнительных GET параметров без псевдонимов
            $arComponentVariables,
            // массив основных GET параметров с псевдонимами
            $arVariableAliases,
            // обьединяем все найденные URL и GET параметры и записываем в переменну
            $arVariables
        );
        // формируем arResult
        $this->arResult = [
            // данные полученые из GET и URL параметров
            "VARIABLES" => $arVariables,
            // массив с параметрами псевдонимов для возможности востановления дальше в обычном компоненте
            "ALIASES" => $arVariableAliases
        ];
        return $componentPage;
    }
    // метод обработки режима без ЧПУ
    protected function noSefMode()
    {
        //******************************************************//
        // Переименование GET параметров                        //
        //******************************************************//
        // если в комплексном компоненте не задан базовый URL
        if (empty($this->arParams["VARIABLE_ALIASES"]["CATALOG_URL"])) {
            // получаем данные из настроек инфоблока
            $dbResult = CIBlock::GetByID($this->arParams["IBLOCK_ID"])->GetNext();
            if (!empty($dbResult)) {
                // перетираем данные в $arParams["VARIABLE_ALIASES"]
                $this->arParams["VARIABLE_ALIASES"]["ELEMENT_ID"] = preg_replace('/\#/', '', $dbResult["DETAIL_PAGE_URL"]);
                $this->arParams["VARIABLE_ALIASES"]["SECTION_ID"] = preg_replace('/\#/', '', $dbResult["SECTION_PAGE_URL"]);
                $this->arParams["VARIABLE_ALIASES"]["CATALOG_URL"] = preg_replace('/\#/', '', $dbResult["LIST_PAGE_URL"]);
            }
        }
       
        // дополнительные GET параметры которые будем отлавливать в запросе, полезно например для постраничной навигации. В массив $arVariableAliases будет добавлена переменная ELEMENT_COUNT, значение которой будет получено из $_REQUEST['count'], в итоге данные попадут в $arVariables, применяется когда нужно указать точный псевдоним для ключа
        $arDefaultVariableAliases = [
            'ELEMENT_COUNT' => 'count',
        ];
        // метод предназначен для объединения дефолтных GET параметров которые приходят в $arParams["VARIABLE_ALIASES"] и дополнительных GET параметров из массива $arDefaultVariableAliases. Параметры из настроек $arrParams заменяют дополнительные из $arDefaultVariableAliases
        $arVariableAliases = CComponentEngine::makeComponentVariableAliases(
            // массив псевдонимов переменных из GET параметра
            $arDefaultVariableAliases,
            // массив псевдонимов из $arParams
            $this->arParams["VARIABLE_ALIASES"]
        );
        //******************************************************//
        // Получение и обьединение GET параметров               //
        //******************************************************//
        // главная переменная комплексного компонента, именно она будут записана в массив $arResult, как результат работы комплексного компонента. Она будет доступна в файлах section.php, element.php, index.php, которые будут подключены, после того как отработает class.php
        $arVariables = [];
        // дополнительные GET параметры которые будем отлавливать в запросе, в массив $arVariables будет добавлена переменная sort, значение которой будет получено из $_REQUEST['sort'], применяется когда не нужно указывать точный псевдоним для ключа
        $arComponentVariables = [
            'sort'
        ];
        // метод предназначен для получения и объединения GET параметров результат записываем в $arVariables
        CComponentEngine::initComponentVariables(
            // нужен для режима ЧПУ, содержит файл который будет подключен section.php, element.php, index.php
            false,
            // массив дополнительных GET параметров без псевдонимов
            $arComponentVariables,
            // массив основных GET параметров с псевдонимами
            $arVariableAliases,
            // обьединяем все найденные GET параметры и записываем в переменну
            $arVariables
        );

     
        //*****************************************************//
        // Получение реального URL                             //
        //*****************************************************//
        // получаем контекст текущего хита
        $context = Application::getInstance()->getContext();
        // получаем объект Request
        $request = $context->getRequest();
        // получаем директорию запрошенной страницы
        $rDir = $request->getRequestedPageDirectory();
        //*****************************************************//
        // Получение нужного шаблона                           //
        //*****************************************************//
        // переменная предназначен для хранения подключаемого шаблона section.php, element.php, index.php
        $componentPage = "";
        // если запрошенная директория равна переданой в arParams["CATALOG_URL"], определяем тип страницы стартовая
        if ($arVariableAliases["CATALOG_URL"] == $rDir) {
            $componentPage = "index";
        }
        // по найденным параметрам $arVariables определяем тип страницы секция
        if ((isset($arVariables["SECTION_ID"]) && intval($arVariables["SECTION_ID"]) > 0) || (isset($arVariables["SECTION_CODE"]) && $arVariables["SECTION_CODE"] <> '')) {
            $componentPage = "section";
        }
        // по найденным параметрам $arVariables определяем тип страницы элемент
        if ((isset($arVariables["ELEMENT_ID"]) && intval($arVariables["ELEMENT_ID"]) > 0) || (isset($arVariables["ELEMENT_CODE"]) && $arVariables["ELEMENT_CODE"] <> '')) {
            $componentPage = "element";
        }
       
        //*****************************************************//
        // Формируем $arResult                                 //
        //*****************************************************//
        // формируем $arResult
        $this->arResult = [
            // данные полученые из GET параметров
            "VARIABLES" => $arVariables,
            // массив с параметрами псевдонимов для возможности востановления дальше в обычном компоненте
            "ALIASES" => $arVariableAliases
        ];
        return $componentPage;
    }


    public function pushError()
    {
        $this->AbortResultCache();
        \Bitrix\Iblock\Component\Tools::process404(
            Loc::getMessage('PAGE_NOT_FOUND'),
            true,
            true
        );
    }

    public function getUserData()
    {
        global $USER;
        $rsUser = CUser::GetByID($USER->getId());

        $arUser = $rsUser->Fetch();
        $job = $arUser;
        $uidjob = $job['UF_JOB_TYPE'];
        if(!$uidjob) {
            ShowError('Не указана должность');
        }
        if ($USER->IsAuthorized() || $USER->IsAdmin()) {
            
            // делаем выборку хайлоуд блока с ID
            
            $arHLBlock = Bitrix\Highloadblock\HighloadBlockTable::getById($this->arParams['HIGHLIGHT_BLOCK_ID'])->fetch();
            // инициализируем класс сущности хайлоуд блока с ID 4
            $obEntity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arHLBlock);
            // обращаемся к DataManager
            $strEntityDataClass = $obEntity->getDataClass();
            // стандартный запрос getList
            $rsData = $strEntityDataClass::getList(array(
                // необходимые для выборки поля
                'select' => array('ID', 'UF_NAME', 'UF_FILE', 'UF_XML_ID'),
                'filter' => array('=ID' => $uidjob)
            ));
            // формируем массив данных
            while ($arItem = $rsData->Fetch()) {
                $arItems[] = $arItem;
            }
  
            $dateString2 = $_GET['start'];
            $dateFormat2 = 'Y-m-d-H-i-s';
            $dateTimeObj2 = DateTime::createFromFormat($dateFormat2, $dateString2);

            $endString = $_GET['end'];

            $endFormat = 'Y-m-d-H-i-s';
            $endTimeObj = DateTime::createFromFormat($endFormat, $endString);
            

            $arrFilter = [];
            if($endTimeObj < $dateTimeObj2) {
                ShowError(Loc::getMessage('ERROR'));
                return false;
            } else {
                
                if ($dateTimeObj2 !== false) {
                    array_push($arrFilter, array(
                        "<=PROPERTY_WORK_START" => $dateTimeObj2->format('Y-m-d H:i:s'),
                        ">=PROPERTY_WORK_END" => $dateTimeObj2->format('Y-m-d H:i:s'),
                    ));
                }
                if ($endTimeObj !== false) {
                
                    array_push($arrFilter, array(
                        "<=PROPERTY_WORK_START" => $endTimeObj->format('Y-m-d H:i:s'),
                        ">=PROPERTY_WORK_END" => $endTimeObj->format('Y-m-d H:i:s'),
                    ));
                }
                if (!empty($arItems[0]['UF_XML_ID'])) {
                    array_push($arrFilter, array("=PROPERTY_JOB_FUNCTION" => $arItems[0]['UF_XML_ID']));
                }
            }
            return $arrFilter;
        }
    }

    // подключение языковых файлов (метод подключается автоматически)
    public function onIncludeComponentLang()
    {
        Loc::loadMessages(__FILE__);
    }

    // проверяем установку модуля «Информационные блоки» (метод подключается внутри класса try...catch)
    protected function checkModules()
    {

        $start = htmlentities($_GET['start']);
        $end = htmlentities($_GET['end']);
        
        if(empty($start) || empty($end)) {
            throw new SystemException(Loc::getMessage('NO_TIME'));
        }
        // если модуль не подключен
        if (!Loader::includeModule('iblock'))
            // выводим сообщение в catch
            throw new SystemException(Loc::getMessage('IBLOCK_MODULE_NOT_INSTALLED'));
            
    }
    

}
