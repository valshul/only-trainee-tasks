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
    function deleteResource(path) {
        BX.ajax.runComponentAction("only:yandex.disk.dir", "deleteResource", {
            mode: "class",
            data: {
                resourcePath: path
            },
        }).then(function (response) {
            location.reload();
        }, function (response) {
            displayError(response);
        });
    }

    function createResource(type) {
        name = document.getElementById(type + "_name").value;
        BX.ajax.runComponentAction("only:yandex.disk.dir", "createResource", {
            mode: "class",
            data: {
                dirPath: "<?= $arParams["DIR"] ?>",
                resourceName: name,
                resourceType: type
            },
        }).then(function (response) {
            location.reload();
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
<h1>disk:/<?= $arParams["DIR"] ?></h1>
<hr>

<table>
    <tr>
        <th>type</th>
        <th>name</th>
        <th>size (bytes)</th>
        <th>&nbsp;</th>
    </tr>
    
    <?php if (isset($arResult["PREVIOUS_DIR_URL"])): ?>
        <tr>
            <td>&nbsp;</td>
            <td>
                <a href=<?= $arResult["PREVIOUS_DIR_URL"]; ?>>..</a>
            </td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    <?php endif; ?>

    <?php foreach ($arResult["ITEMS"] as $item): ?>
        <tr>
            <td><?= $item["type"]; ?></td>
            <td>
                <a href=<?= $item["url"]; ?>><?= $item["name"]; ?></a>
            </td>
            <td><?= $item["size"]; ?></td>
            <td><button onclick=<?= "\"deleteResource('" . $item["path"] . "')\"" ?>>delete</button></td>
        </tr>
    <?php endforeach; ?>
</table>

<hr>
<table>
    <tr>
        <td>new directory name:</td>
        <td><input id="dir_name" type="text"></td>
        <td><button onclick="createResource('dir')">create directory</button></td>
    </tr>
    <tr>
        <td>new file name:</td>
        <td><input id="file_name" type="text"></td>
        <td><button onclick="createResource('file')">create file</button></td>
    </tr>
</table>

