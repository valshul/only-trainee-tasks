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
        "DIR" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("DIR_PATH"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "DISK_URL" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("DISK_URL_TEMPLATE"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "DIR_URL" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("DIR_URL_TEMPLATE"),
            "TYPE" => "STRING",
            "DEFAULT" => "?".$arCurrentValues["VARIABLE_ALIASES_DIR"]."=#DIR#",
        ],
        "FILE_URL" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("FILE_URL_TEMPLATE"),
            "TYPE" => "STRING",
            "DEFAULT" => "?".$arCurrentValues["VARIABLE_ALIASES_FILE"]."=#FILE#",
        ],
    ],
];