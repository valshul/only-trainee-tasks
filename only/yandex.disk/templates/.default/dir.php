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

$APPLICATION->IncludeComponent(
    "only:yandex.disk.dir",
    "",
    [
        "OAUTH_USERFIELD_ID" => $arParams["OAUTH_USERFIELD_ID"],
        "VARIABLE_ALIASES_DIR" => $arParams["VARIABLE_ALIASES_DIR"],
        "VARIABLE_ALIASES_FILE" => $arParams["VARIABLE_ALIASES_FILE"],
        "DIR" => $arResult["VARIABLES"]["DIR"],
        "DISK_URL" => $arResult["URL_TEMPLATES"]["disk"],
        "DIR_URL" => $arResult["URL_TEMPLATES"]["dir"],
        "FILE_URL" => $arResult["URL_TEMPLATES"]["file"],
    ],
    $component
);
