<?

use Bitrix\Main\Diag\Debug;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
// пространства имен для работы с языковыми файлами

CBitrixComponent::includeComponentClass("customs:traits");
class CIblocListWithEvent extends CommonClass
{
   
    // подготовка массива $arResult (метод подключается внутри класса try...catch)
    protected function getResult()
    {
        global $USER;

        //Debug::dump($this->arParams);

        $start = htmlentities($_GET['start']);
        $end = htmlentities($_GET['end']);

        //2025-07-31-19-28-20
        // если выбран режим поддержки ЧПУ, вызываем метод sefMode()
        if ($this->arParams["SEF_MODE"] === "Y") {
                       
            $this->arParams["ENDSTART"] = '/?start=' . $start . '&end=' . $end . '&clear_cache=Y';
        }
        // если отключен режим поддержки ЧПУ, вызываем метод noSefMode()
        if ($this->arParams["SEF_MODE"] != "Y") {
            
            $this->arParams["ENDSTART"] = '&start=' . $start . '&end=' . $end . '&clear_cache=Y';
        }
      

        // если нет валидного кеша, получаем данные из БД
        if ($this->startResultCache(false, $USER->getId() . $start . $end)) {
            $sectionId = $this->arParams['SECTION_ID'];
            $iblockId = $this->arParams['IBLOCK_ID'];
            // для режима ЧПУ получаем ID секции по символьному коду
            
         
            
            if ($this->arParams['SEF_MODE'] == 'Y') {
                $section_code = $this->arParams['SECTION_CODE'];
                $sectionId = CIBlockFindTools::GetSectionID(false, $section_code, []);
               
            }
           
            if (empty($arrFilter = $this->getUserData())) {
                $this->IncludeComponentTemplate('error');
            }

          
                  
            $my_elements = CIBlockElement::GetList(
                ["ID" => "ASC"], // Сортировка
                ["IBLOCK_ID" => $iblockId, "SECTION_ID"=> $sectionId, $arrFilter, 'INCLUDE_SUBSECTIONS'=> 'Y'], // Фильтр
                false, // Группировка
                false, // Постраничная навигация
                ['ID', 'NAME', 'DETAIL_PAGE_URL', 'PROPERTY_WORK_START', 'PROPERTY_WORK_END', 'SECTION_ID'] // Выбираемые поля
            );
           
          // Debug::dump($my_elements);

       
            // формируем массив arResult
            while ($arItem = $my_elements->GetNext()) {
              
                $this->arResult['ITEMS'][] = $arItem;
            }
            // кэш не затронет весь код ниже, он будут выполняться на каждом хите, здесь работаем с другим $arResult, будут доступны только те ключи массива, которые перечислены в вызове SetResultCacheKeys()
            if (!empty($this->arResult)) {
                // ключи $arResult перечисленные при вызове этого метода, будут доступны в component_epilog.php и ниже по коду, обратите внимание там будет другой $arResult
                $this->SetResultCacheKeys(
                    array()
                );
                // подключаем шаблон и сохраняем кеш
                $this->IncludeComponentTemplate();
            } else { // если выяснилось что кешировать данные не требуется, прерываем кеширование и выдаем сообщение «Страница не найдена»
                $this->AbortResultCache();
                \Bitrix\Iblock\Component\Tools::process404(
                    "Данные не найдены",
                    true,
                    true
                );
            }
        }
    }

    
}
