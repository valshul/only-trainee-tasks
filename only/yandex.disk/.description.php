<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

$arComponentDescription = [
    "NAME" => GetMessage("ONLY_YANDEX_DISK_NAME"),
    "DESCRIPTION" => GetMessage("ONLY_YANDEX_DISK_DESCRIPTION"),
    "SORT" => 20,
    "CACHE_PATH" => "Y",
    "PATH" => [
        "ID" => "only",
        "NAME" => "Компоненты Only",
    ],
];