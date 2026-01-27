<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
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
<script>
    function updateResource(path) {
        textValue = document.getElementById("text_editor").value;
        BX.ajax.runComponentAction("only:yandex.disk.file", "updateResource", {
            mode: "class",
            data: {
                resourcePath: path,
                text: textValue
            },
        }).then(function (response) {
            // console.log(response);
        }, function (response) {
            displayError(response);
        });
    }

    function displayError(response) {
        errorDiv = document.getElementById("error_msg");
        errorDiv.innerHTML = response["errors"][0]["message"];
        errorDiv.style.display = "block";
    }
</script>

<div id="error_msg" style="display:none; color:red;"></div>
<h1><?= $arResult["CONTENTS"]["PARAMS"]["path"] ?></h1>
<?php if (isset($arResult["PREVIOUS_DIR_URL"])): ?>
    <a href=<?= $arResult["PREVIOUS_DIR_URL"] ?>>back to directory</a>
<?php endif; ?>
<hr>

<?php if (isset($arResult["CONTENTS"]["TEXT"])): ?>
    <textarea id="text_editor" style="width: 100%; height: 350px;"><?= $arResult["CONTENTS"]["TEXT"] ?></textarea>
    <button onclick=<?= "\"updateResource('" . $arParams["FILE"] . "')\"" ?>>save</button>
<?php endif; ?>

<?php if (isset($arResult["CONTENTS"]["IMAGE"])): ?>
    <img src=<?= $arResult["CONTENTS"]["IMAGE"] ?>>
<?php endif; ?>