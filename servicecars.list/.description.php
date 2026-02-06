<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

$arComponentDescription = [
    "NAME" => GetMessage("SERVICE_CARS_COMPONENT_NAME"),
    "DESCRIPTION" => GetMessage("SERVICE_CARS_COMPONENT_DESC"),
    "SORT" => 20,
    "CACHE_PATH" => "Y",
    "PATH" => [
        "ID" => "only",
        "NAME" => GetMessage("SERVICE_CARS_COMPONENT_PATH_NAME"),
    ],
];