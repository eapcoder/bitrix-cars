<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<article>
    
    <? if (!empty($arResult[0]['NAME'])) : ?>
        <div>
            #<?= $arResult[0]['NAME']; ?>
        </div>
    <? endif; ?>
    <? if (!empty($arResult[0]['DETAIL_TEXT'])) : ?>
        <div>
            <?= $arResult[0]['DETAIL_TEXT']; ?>
        </div>
    <? endif; ?>
    <? if ($arParams["SEF_MODE"] == "Y") : ?>
        <p><a href="<?= $arParams['SEF_FOLDER'] . $arParams['SECTION_CODE'] .  $arParams["ENDSTART"]; ?>">Назад в раздел</a></p>
    <? else : ?>
        <p><a href="<?= '?SECTION_ID=' . $arParams['SECTION_ID'] .  $arParams["ENDSTART"]; ?>/?start=2025-07-31-19-28-20&end=2025-08-01-12-00-20&clear_cache=Y">Назад в раздел</a></p>
    <? endif; ?>
</article>