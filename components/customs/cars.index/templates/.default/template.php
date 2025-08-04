<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<?
foreach ($arResult as $arSection) : ?>
    <article>
     
        <h2>Раздел</h2>
        <? if (!empty($arSection['NAME'])) : ?>
            <div>
                <?= $arSection['NAME']; ?>
            </div>
        <? endif; ?>
        <? if ($arParams["SEF_MODE"] == "Y") : ?>
            <p><a href="<?= $arSection['CODE'] . '' . $arParams["ENDSTART"]; ?>">Вперед в категории...</a></p>
        <? else : ?>
            <p><a href="<?= '?SECTION_ID=' . $arSection['ID'] . $arParams["ENDSTART"]; ?>">Вперед в категории</a></p>
        <? endif; ?>
    </article>
<? endforeach ?>