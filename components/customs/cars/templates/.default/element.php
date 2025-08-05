<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
// подключаем компонент

//var_dump($arParams["CUSTOM_PARAMS"]);
$APPLICATION->IncludeComponent(
    "customs:cars.element",
    "",
    array(
        "CACHE_TIME" => "3600",
        "CACHE_TYPE" => "A",
        "SEF_MODE" => $arParams["SEF_MODE"],
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
        "SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
        "ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
        "ELEMENT_CODE" => $arResult["VARIABLES"]["ELEMENT_CODE"],
        "SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
        "SEF_FOLDER" => $arParams["SEF_FOLDER"],
        "HIGHLIGHT_BLOCK_ID" => $arParams["HIGHLIGHT_BLOCK_ID"],
        "ENDSTART_TEMPLATE" => $arParams["ENDSTART_TEMPLATE"],
        "IBLOCK_PROPERTY_SHOW" => $arParams["IBLOCK_PROPERTY_SHOW"]
    ),
    $component
);
