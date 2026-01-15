<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$this->setFrameMode(true);
?>

<div class="news-list">
    <?php
    if($arParams["DISPLAY_TOP_PAGER"]) {
        echo $arResult["NAV_STRING"] . "<br>";
    }
    ?>

    <?php foreach(array_keys($arResult["ITEMS"]) as $iblockId): ?>
        <h2><?= $arResult["IBLOCKS"][$iblockId]["NAME"] ?></h2>

        <?php foreach($arResult["ITEMS"][$iblockId] as $arItem): ?>
            <?php 
            $this->AddEditAction(
                $arItem["ID"],
                $arItem["EDIT_LINK"],
                CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT")
            );
            $this->AddDeleteAction(
                $arItem["ID"],
                $arItem["DELETE_LINK"],
                CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"),
                ["CONFIRM" => GetMessage("CT_BNL_ELEMENT_DELETE_CONFIRM")]
            );
            ?>
            <p class="news-item" id="<?= $this->GetEditAreaId($arItem["ID"]); ?>">
                <?php if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arItem["PREVIEW_PICTURE"])): ?>
                    <img
                        class="preview_picture"
                        border="0"
                        src="<?= $arItem["PREVIEW_PICTURE"]["SRC"] ?>"
                        width="290px"
                        alt="<?= $arItem["PREVIEW_PICTURE"]["ALT"] ?>"
                        title="<?= $arItem["PREVIEW_PICTURE"]["TITLE"] ?>"
                        style="float:left"
                    />
                <?php endif; ?>

                <?php if($arParams["DISPLAY_DATE"]!="N" && $arItem["DISPLAY_ACTIVE_FROM"]): ?>
                    <span class="news-date-time"><?= $arItem["DISPLAY_ACTIVE_FROM"] ?></span>
                <?php endif; ?>

                <?php if($arParams["DISPLAY_NAME"]!="N" && $arItem["NAME"]): ?>
                    <?php if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])): ?>
                        <a href="<?= $arItem["DETAIL_PAGE_URL"] ?>">
                            <b><?= $arItem["NAME"] ?></b>
                        </a>
                        <br>
                    <?php else: ?>
                        <b><?= $arItem["NAME"] ?></b>
                        <br>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && $arItem["PREVIEW_TEXT"]): ?>
                    <?= $arItem["PREVIEW_TEXT"]; ?>
                <?php endif; ?>

                <?php if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arItem["PREVIEW_PICTURE"])): ?>
                    <div style="clear:both"></div>
                <?php endif; ?>

                <?php foreach($arItem["FIELDS"] as $code=>$value): ?>
                    <small>
                        <?= GetMessage("IBLOCK_FIELD_".$code) ?>:&nbsp;<?= $value; ?>
                    </small>
                    <br>
                <?php endforeach; ?>

                <?php foreach($arItem["DISPLAY_PROPERTIES"] as $pid=>$arProperty): ?>
                    <small>
                        <?= $arProperty["NAME"] ?>:&nbsp;
                        <?php if(is_array($arProperty["DISPLAY_VALUE"])): ?>
                            <?= implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]); ?>
                        <?php else: ?>
                            <?= $arProperty["DISPLAY_VALUE"]; ?>
                        <?php endif ?>
                    </small>
                    <br>
                <?php endforeach; ?>
            </p>
        <?php endforeach; ?>
        
    <?php endforeach; ?>
    
    <?php
    if($arParams["DISPLAY_BOTTOM_PAGER"]) {
        echo $arResult["NAV_STRING"] . "<br>";
    }
    ?>
</div>