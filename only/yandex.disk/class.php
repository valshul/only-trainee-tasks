<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CIntranetToolbar $INTRANET_TOOLBAR */

global $INTRANET_TOOLBAR;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\Collection;
use Bitrix\Main\Type\DateTime;
use Bitrix\Iblock;

require_once $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";

class YandexDiskComponent extends CBitrixComponent
{
    /**
     * Prepare input parameters
     * @param array $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams): array
    {
        $arParams["VARIABLE_ALIASES"] = [
            "DIR" => $arParams["VARIABLE_ALIASES_DIR"],
            "FILE" => $arParams["VARIABLE_ALIASES_FILE"],
        ];

        return $arParams;
    }

    /**
     * Main component method
     * @return void
     */
    public function executeComponent(): void
    {
        $this->initResult();
        $this->includeComponentTemplate($this->arResult["COMPONENT_PAGE"]);
    }

    /**
     * Initialize result
     * @return void
     */
    private function initResult(): void
    {
        $arParams = $this->arParams;
        $arResult = &$this->arResult;
        global $APPLICATION;

        $arDefaultVariableAliases = [];
        $arComponentVariables = [
            "DIR",
            "FILE",
        ];
        $arVariables = [];

        $arVariableAliases = CComponentEngine::makeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
        CComponentEngine::initComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

        $componentPage = "";
        if (isset($arVariables["FILE"]) && $arVariables["FILE"] != "") {
            $componentPage = "file";
        } elseif (isset($arVariables["DIR"])) {
            $componentPage = "dir";
        } else {
            $componentPage = "dir";
        }

        $arResult = [
            "URL_TEMPLATES" => [
                "disk" => htmlspecialcharsbx($APPLICATION->GetCurPage()),
                "dir" => htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arVariableAliases["DIR"]."=#DIR#"),
                "file" => htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arVariableAliases["FILE"]."=#FILE#"),
            ],
            "VARIABLES" => $arVariables,
            "ALIASES" => $arVariableAliases,
            "COMPONENT_PAGE" => $componentPage,
        ];
        $arResult["VARIABLES"]["DIR"] ??= "";
        $arResult["VARIABLES"]["FILE"] ??= "";
    }
}