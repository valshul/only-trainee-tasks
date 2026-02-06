<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

/** @var array $arCurrentValues */

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;

if (!Loader::includeModule("iblock") || !Loader::includeModule("highloadblock")) {
    return;
}

$filter = [
    "ACTIVE" => "Y",
];
if (isset($_REQUEST["site"])) {
    $filter["SITE_ID"] = $_REQUEST["site"];
}
$arIBlocks = [];
$db_iblock = CIBlock::GetList(["SORT"=>"ASC"], $filter);
while($arRes = $db_iblock->Fetch()) {
    $arIBlocks[$arRes["ID"]] = "[" . $arRes["ID"] . "] " . $arRes["NAME"];
}

$arHLBlocks = [];
$db_hlblock = HighloadBlockTable::getList([
    "select" => ["ID", "NAME"],
    "order" => ["ID" => "ASC"],
]);
while ($arRes = $db_hlblock->Fetch()) {
    $arHLBlocks[$arRes["ID"]] = "[" . $arRes["ID"] . "] " . $arRes["NAME"];
}

$arComponentParameters = [
    "GROUPS" => [],
    "PARAMETERS" => [
        "SERVICE_CARS_IBLOCK_ID" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("PARAMS_SERVICE_CARS_IBLOCK_ID"),
            "TYPE" => "LIST",
            "VALUES" => $arIBlocks,
            "DEFAULT" => "",
            "ADDITIONAL_VALUES" => "Y",
            "REFRESH" => "Y",
        ],
        "SERVICE_RIDES_IBLOCK_ID" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("PARAMS_SERVICE_RIDES_IBLOCK_ID"),
            "TYPE" => "LIST",
            "VALUES" => $arIBlocks,
            "DEFAULT" => "",
            "ADDITIONAL_VALUES" => "Y",
            "REFRESH" => "Y",
        ],
        "COMFORT_CATEGORIES_HLBLOCK_ID" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("PARAMS_COMFORT_CATEGORIES_HLBLOCK_ID"),
            "TYPE" => "LIST",
            "VALUES" => $arHLBlocks,
            "DEFAULT" => "",
            "ADDITIONAL_VALUES" => "Y",
            "REFRESH" => "Y",
        ],
        "CAR_MODELS_HLBLOCK_ID" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("PARAMS_CAR_MODELS_HLBLOCK_ID"),
            "TYPE" => "LIST",
            "VALUES" => $arHLBlocks,
            "DEFAULT" => "",
            "ADDITIONAL_VALUES" => "Y",
            "REFRESH" => "Y",
        ],
        "WORK_POSITIONS_HLBLOCK_ID" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("PARAMS_WORK_POSITIONS_HLBLOCK_ID"),
            "TYPE" => "LIST",
            "VALUES" => $arHLBlocks,
            "DEFAULT" => "",
            "ADDITIONAL_VALUES" => "Y",
            "REFRESH" => "Y",
        ],
        "WORK_POSITION_UFIELD_NAME" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("PARAMS_WORK_POSITION_UFIELD_NAME"),
            "TYPE" => "STRING",
            "DEFAULT" => "UF_WORK_POSITION",
        ],
    ],
];