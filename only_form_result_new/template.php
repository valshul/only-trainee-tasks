<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
$this->addExternalCss($this->GetFolder() . "/css/common.css");
require "helper.php";
?>

<?php
echo $arResult["FORM_NOTE"] ?? "";
if ($arResult["isFormNote"] != "Y"):
?>

<div class="contact-form">
    <div class="contact-form__head">
        <div class="contact-form__head-title">
            <?= $arResult["FORM_TITLE"] ?>
        </div>
        <div class="contact-form__head-text">
            <?= $arResult["FORM_DESCRIPTION"] ?>
        </div>
    </div>
    <?= generateHeaderWithClass($arResult["FORM_HEADER"], "contact-form__form") ?>
        <div class="contact-form__form-inputs">
            <?php
            foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion) {
                if ($arQuestion["STRUCTURE"][0]["FIELD_TYPE"] != "textarea") {
                    echo generateInput(
                        $FIELD_SID,
                        $arQuestion,
                        $arResult["FORM_ERRORS"] ?? "",
                    );
                }
            }
            ?>
        </div>
        <div class="contact-form__form-message">
            <?php
            foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion) {
                if ($arQuestion["STRUCTURE"][0]["FIELD_TYPE"] == "textarea") {
                    echo generateInput(
                        $FIELD_SID,
                        $arQuestion,
                        $arResult["FORM_ERRORS"] ?? "",
                    );
                }
            }
            ?>
        </div>
        <div class="contact-form__bottom">
            <div class="contact-form__bottom-policy">
                <?= generateAgreement($arResult["arForm"]["BUTTON"]) ?>
            </div>
            <input class="form-button contact-form__bottom-button"
                type="submit"
                name="web_form_submit"
                value="<?= $arResult["arForm"]["BUTTON"] ?>"
                data-success="Отправлено"
                data-error="Ошибка отправки" />
        </div>
    <?= $arResult["FORM_FOOTER"] ?>
</div>

<?php
endif;
?>