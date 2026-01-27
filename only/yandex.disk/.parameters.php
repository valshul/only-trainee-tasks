<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

/** @var array $arCurrentValues */

$arComponentParameters = [
    "GROUPS" => [],
    "PARAMETERS" => [
        "OAUTH_USERFIELD_ID" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("YANDEX_DISK_OAUTH_USERFIELD_ID"),
            "TYPE" => "STRING",
            "DEFAULT" => "UF_YANDEX_DISK_OAUTH",
        ],
        "VARIABLE_ALIASES" => [
            "DIR" => ["NAME" => GetMessage("DIR_ID_NAME")],
            "FILE" => ["NAME" => GetMessage("FILE_ID_NAME")],
        ],
    ],
];