<?

use Bitrix\Main\Diag\Debug;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
// подключаем компонент


$APPLICATION->IncludeComponent(
    "customs:cars.section",
    "",
    array(
        "CACHE_TIME" => "3600",
        "CACHE_TYPE" => "A",
        "SEF_FOLDER" => $arParams["SEF_FOLDER"],
        "SEF_MODE" => $arParams["SEF_MODE"],
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
        "SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
        "SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
        "CATALOG_URL" => $arResult["ALIASES"]["CATALOG_URL"],
        "HIGHLIGHT_BLOCK_ID" => $arParams["HIGHLIGHT_BLOCK_ID"],
        "ENDSTART_TEMPLATE" => $arParams["ENDSTART_TEMPLATE"],
        "IBLOCK_PROPERTY_SHOW" => $arParams["IBLOCK_PROPERTY_SHOW"]
    ),
    $component
);
