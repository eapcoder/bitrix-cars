<?

use Bitrix\Main\Diag\Debug;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<h2>Категория</h2>

<? foreach ($arResult['ITEMS'] as $arElement) : ?>


    <article>
        <?php if (!empty($arElement['NAME'])) : ?>
            <div>
                <?= $arElement['NAME']; ?>
            </div>
            <div class="col-12 tekst">Доступность начало <?= $arElement["PROPERTY_WORK_START_VALUE"] ?></div>
            <div class="col-12 tekst">Доступность окончание <?= $arElement["PROPERTY_WORK_END_VALUE"] ?></div>
        <?php endif; ?>
        <? if ($arParams["SEF_MODE"] == "Y") : ?>
            <p><a href="<?= $arParams["SEF_FOLDER"] . $arParams['SECTION_CODE'] . "/" . $arElement["CODE"] . $arParams["ENDSTART"]; ?>">Вперед в элемент</a></p>
        <? else : ?>
            <p><a href="<?= '?ELEMENT_ID=' . $arElement['ID'] . '&SECTION_ID=' . $arParams["SECTION_ID"] . $arParams["ENDSTART"]; ?>">Вперед в элемент</a></p>
        <? endif; ?>
    </article>
<? endforeach ?>

<hr>
<? if ($arParams["SEF_MODE"] == "Y") : ?>
    <p><a href="<?= $arParams["SEF_FOLDER"] .  substr($arParams["ENDSTART"], 1, strlen($arParams["ENDSTART"])); ?>">Назад в раздел</a></p>
<? else : ?>
    <p><a href="<?= $arParams["CATALOG_URL"] .  $arParams["ENDSTART"]; ?>">Назад в раздел</a></p>
<? endif; ?>