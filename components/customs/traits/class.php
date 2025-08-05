<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
// пространства имен для работы с языковыми файлами

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Localization\Loc;
// пространства имен для всех исключений в системе
use Bitrix\Main\SystemException;
// пространства имен для загрузки необходимых файлов, классов, модулей
use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;


class CommonClass extends CBitrixComponent
{


    // выполняет основной код компонента, аналог конструктора (метод подключается автоматически)
    public function executeComponent()
    {

        include_once(dirname(__DIR__) . '/cars/MyTrait.php');
        $m = new MyTrait();
        $m->myTraitMethod();

        try {
            // подключаем метод проверки подключения модуля «Информационные блоки»
            $this->checkModules();
            // подключаем метод подготовки массива $arResult
            $this->getResult();
        } catch (SystemException $e) {
            ShowError($e->getMessage());
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
        // если модуль не подключен
        if (!Loader::includeModule('iblock'))
            // выводим сообщение в catch
            throw new SystemException(Loc::getMessage('IBLOCK_MODULE_NOT_INSTALLED'));
    }
    // обработка массива $arParams (метод подключается автоматически)
    public function onPrepareComponentParams($arParams)
    {
        // время кеширования
        if (!isset($arParams['CACHE_TIME'])) {
            $arParams['CACHE_TIME'] = 3600;
        } else {
            $arParams['CACHE_TIME'] = intval($arParams['CACHE_TIME']);
        }
        // возвращаем в метод новый массив $arParams     
        return $arParams;
    }
    // подготовка массива $arResult (метод подключается внутри класса try...catch)
    protected function check($cheackToArray)
    {

        $array = ['YYYY', 'MM', 'DD', 'HH', 'MI', 'SS'];
        foreach ($cheackToArray as $item) {

            if (!in_array($item, $array)) {
                ShowError('Ошибка в формете даты - ' . $item . ', укажите "Форма времени через адресную строку" в настройках кмопонента!');
                return false;
            }
        }
        return true;
    }


    private function addProperty($n)
    {
        if (strpos($n, 'PROPERTY_') === false) {
            return 'PROPERTY_' . $n;
        }
    }
    
    public function arrayMerge($tomerge) {
        $b = array_map([$this, 'addProperty'], $tomerge);
        $arrProperty = ['ID', 'NAME', 'DETAIL_PAGE_URL', 'PROPERTY_WORK_START', 'PROPERTY_WORK_END', 'SECTION_ID']; // Выбираемые поля
        return array_unique(array_merge($arrProperty, $b));

    }


    public function getUserData()
    {
        global $USER;

        $rsUser = CUser::GetByID($USER->getId());

        $arUser = $rsUser->Fetch();
        $job = $arUser;
        $uidjob = $job['UF_JOB_TYPE'];
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
            $endString = $_GET['end'];
            
            if ($this->check(explode('-', $this->arParams["ENDSTART_TEMPLATE"]))) {

                $e = explode('-', $this->arParams["ENDSTART_TEMPLATE"]);

                $keyYYYY    = array_search('YYYY', $e);
                $keyMM      = array_search('MM', $e);
                $keyDD      = array_search('DD', $e);
                $keyHH      = array_search('HH', $e);
                $keyMI      = array_search('MI', $e);
                $keySS      = array_search('SS', $e);

                $str = [];
                $str['y'] = explode('-', $dateString2)[$keyYYYY];
                $str['m'] = explode('-', $dateString2)[$keyMM];
                $str['d'] = explode('-', $dateString2)[$keyDD];
                $str['H'] = explode('-', $dateString2)[$keyHH];
                $str['i'] = explode('-', $dateString2)[$keyMI];
                $str['s'] = explode('-', $dateString2)[$keySS];

                $endstr = [];
                $endstr['y'] = explode('-', $endString)[$keyYYYY];
                $endstr['m'] = explode('-', $endString)[$keyMM];
                $endstr['d'] = explode('-', $endString)[$keyDD];
                $endstr['H'] = explode('-', $endString)[$keyHH];
                $endstr['i'] = explode('-', $endString)[$keyMI];
                $endstr['s'] = explode('-', $endString)[$keySS];
            }

            $format = 'Y-m-d-H-i-s';
            $dateTimeObj2 = DateTime::createFromFormat($format, /*$dateString2*/ implode('-', $str));
            $endTimeObj = DateTime::createFromFormat($format, /* $endString */ implode('-', $endstr));

            $arrFilter = [];
            if ($endTimeObj < $dateTimeObj2) {
                //Debug::dump($endTimeObj < $dateTimeObj2);
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
}
