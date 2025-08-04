<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// пространства имен highloadblock
use Bitrix\Highloadblock\HighloadBlockTable;
// подключаем модуль highloadblock
\Bitrix\Main\Loader::includeModule("highloadblock");

// проверяем установку модуля «Информационные блоки»
if (!CModule::IncludeModule('iblock')) {
    return;
}
// получаем массив всех типов инфоблоков для возможности выбора
$arIBlockType = CIBlockParameters::GetIBlockTypes();
// пустой массив для вывода 
$arInfoBlocks = array();
// выбираем активные инфоблоки
$arFilterInfoBlocks = array('ACTIVE' => 'Y');
// сортируем по озрастанию поля сортировка
$arOrderInfoBlocks = array('SORT' => 'ASC');
// если уже выбран тип инфоблока, выбираем инфоблоки только этого типа
if (!empty($arCurrentValues['IBLOCK_TYPE'])) {
    $arFilterInfoBlocks['TYPE'] = $arCurrentValues['IBLOCK_TYPE'];
}
// метод выборки информационных блоков
$rsIBlock = CIBlock::GetList($arOrderInfoBlocks, $arFilterInfoBlocks);
// перебираем и выводим в адмику доступные информационные блоки
while ($obIBlock = $rsIBlock->Fetch()) {
    $arInfoBlocks[$obIBlock['ID']] = '[' . $obIBlock['ID'] . '] ' . $obIBlock['NAME'];
}


// стандартный запрос getList
$arHlData = HighloadBlockTable::getList(array(
    'select' => array("ID", "NAME"),
    'order' => array('ID' => 'ASC'),
    'limit' => '50',
));
// формируем массив данных
while ($arHlbk = $arHlData->Fetch()) {
    $arrHlblocks[$arHlbk['ID']] = $arHlbk['NAME'];
}


// настройки компонента, формируем массив $arParams
$arComponentParameters = [
    // основной массив с параметрами
    "PARAMETERS" => [
        // выбор самого инфоблока
        'IBLOCK_ID' => array(
            'PARENT' => 'BASE',
            'NAME' => 'Родительский инфоблок свободные машины',
            'TYPE' => 'LIST',
            'VALUES' => $arInfoBlocks,
            'REFRESH' => 'Y',
            "DEFAULT" => '',
            "ADDITIONAL_VALUES" => "Y",
            'SORT' => 1,
        ),
        
        'ENDSTART_TEMPLATE' =>  array(
            'PARENT' => 'BASE',
            'NAME' => 'Форма времени через адресную строку',
            'DEFAULT' => 'YYYY-MM-DD-HH-MM-SS', //?start=2025-07-31-19-28-20&end=2025-08-01-12-00-20&clear_cache=Y
            'VALUES' => '',
            'SORT' => 5,
            
        ),
        'HIGHLIGHT_BLOCK_ID' => array(
            'PARENT' => 'BASE',
            'NAME' => 'Выберите highlight блок',
            'TYPE' => 'LIST',
            'VALUES' => $arrHlblocks,
            'REFRESH' => 'Y',
            "DEFAULT" => '',
            "ADDITIONAL_VALUES" => "Y",
            'SORT' => 6,
        ),
        // выбор типа инфоблока
        'IBLOCK_TYPE' => array(                  // ключ массива $arParams в component.php
            'PARENT' => 'BASE',                  // название группы
            'NAME' => 'Выберите тип инфоблока',  // название параметра
            'TYPE' => 'LIST',                    // тип элемента управления, в котором будет устанавливаться параметр
            'VALUES' => $arIBlockType,           // входные значения
            'REFRESH' => 'Y',                    // перегружать настройки или нет после выбора (N/Y)
            'DEFAULT' => 'news',                 // значение по умолчанию
            'MULTIPLE' => 'N',                   // одиночное/множественное значение (N/Y)
            'SORT' => 7,
        ),
     
        // настройки режима без ЧПУ, доступно в админке до активации чекбокса
        "VARIABLE_ALIASES" => [
            // элемент
            "ELEMENT_ID" => [
                "NAME" => 'GET параметр для ID элемента без ЧПУ',
                "DEFAULT" => "ELEMENT_ID",
            ],
            // секция
            "SECTION_ID" => [
                "NAME" => 'GET параметр для ID раздела без ЧПУ',
                "DEFAULT" => "SECTION_ID",
            ],
            // базовый URL
            "CATALOG_URL" => [
                "NAME" => 'Базовый URL каталога без ЧПУ',
                "DEFAULT" => "/cars/",
            ]
        ],
        // настройки режима ЧПУ, доступно в админке после активации чекбокса
        "SEF_MODE" => [
            // настройки для секции
            "section" => [
                "NAME" => 'Страница раздела',
                "DEFAULT" => "#SECTION_CODE#/",
            ],
            // настройки для элемента
            "element" => [
                "NAME" => 'Детальная страница',
                "DEFAULT" => "#SECTION_CODE#/#ELEMENT_CODE#/",
            ]
        ],
    ]
];
