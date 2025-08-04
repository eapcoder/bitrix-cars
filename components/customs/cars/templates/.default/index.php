<?

use Bitrix\Main\Diag\Debug;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
// подключаем компонент

$APPLICATION->IncludeComponent(
    "customs:cars.index",
    "",
    array(
        "CACHE_TIME" => "3600",
        "CACHE_TYPE" => "A",
        "SEF_MODE" => $arParams["SEF_MODE"],
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
    ),
    $component
);
