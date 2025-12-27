<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}
$this->setFrameMode(true);
$this->addExternalCss($this->GetFolder() . "/css/common.css");
?>

<div class="article-card">
    <?php if(
        (!isset($arParams["DISPLAY_NAME"]) || $arParams["DISPLAY_NAME"] != "N") &&
        $arResult["NAME"]
    ): ?>
        <div class="article-card__title">
            <?= $arResult["NAME"] ?>
        </div>
    <?php endif; ?>
    
    <?php if(
        (!isset($arParams["DISPLAY_DATE"]) || $arParams["DISPLAY_DATE"] != "N") &&
        $arResult["DISPLAY_ACTIVE_FROM"]
    ):?>
        <div class="article-card__date">
            <?= $arResult["DISPLAY_ACTIVE_FROM"] ?>
        </div>
    <?php endif; ?>
    
    <div class="article-card__content">
        <?php if (
            (!isset($arParams["DISPLAY_PICTURE"]) || $arParams["DISPLAY_PICTURE"] != "N")
            && is_array($arResult["DETAIL_PICTURE"])
        ): ?>
            <div class="article-card__image sticky">
                <img
                    src="<?= $arResult["DETAIL_PICTURE"]["SRC"] ?>"
                    alt="<?= $arResult["DETAIL_PICTURE"]["ALT"] ?>"
                    data-object-fit="cover"
                    />
            </div>
        <?php endif; ?>
        <div class="article-card__text">
            <div class="block-content" data-anim="anim-3">
                <?php if($arResult["DETAIL_TEXT"] != "") {
                    echo $arResult["DETAIL_TEXT"]; 
                } else {
                    echo $arResult["PREVIEW_TEXT"];
                } ?>
            </div>
            <a class="article-card__button" href="<?= $arResult["LIST_PAGE_URL"] ?>">
                Назад к новостям
            </a>
        </div>
    </div>
</div>