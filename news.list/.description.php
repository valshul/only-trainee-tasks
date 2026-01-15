<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

$arComponentDescription = [
    "NAME" => GetMessage("T_IBLOCK_DESC_LIST"),
    "DESCRIPTION" => GetMessage("T_IBLOCK_DESC_LIST_DESC"),
    "SORT" => 20,
    "CACHE_PATH" => "Y",
    "PATH" => [
        "ID" => "only",
        "NAME" => GetMessage("T_IBLOCK_DESC_PATH_NAME"),
    ],
];