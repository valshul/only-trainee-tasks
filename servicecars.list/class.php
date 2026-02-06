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

use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;

class ServiceCarsListComponent extends CBitrixComponent implements Controllerable, Errorable
{
    /** @var ErrorCollection */
    protected $errorCollection;

    /**
     * Prepare input parameters
     * @param array $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams): array
    {
        $this->errorCollection = new ErrorCollection();

        foreach ($arParams as $param) {
            $param = trim($param ?? "");
        }
        return $arParams;
    }

    /**
     * Main component method
     * @return void
     */
    public function executeComponent(): void
    {
        // $this->initResult();
        $this->includeComponentTemplate();
    }

    /**
     * Initialize result
     * @return void
     */
    private function initResult(): void
    {
        global $USER;
        $arParams = $this->arParams;
        $arResult = &$this->arResult;

        if (!Loader::includeModule("iblock")) {
            ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
            return;
        }
        if (!Loader::includeModule("highloadblock")) {
            ShowError(GetMessage("HIGHLOADBLOCK_MODULE_NOT_INSTALLED"));
            return;
        }

        foreach ($arParams as $param) {
            if (!isset($param) || empty($param)) {
                ShowError(GetMessage("PARAMS_NOT_SET"));
                return;
            }
        }

        $comfortCategories = $this->getHLBlockArray(
            $arParams["COMFORT_CATEGORIES_HLBLOCK_ID"],
            "ID",
            ["ID", "UF_NAME"],
        );
        if (!$comfortCategories) {
            ShowError(GetMessage("COMFORT_CATEGORIES_INFO_NOT_FOUND"));
            return;
        }

        $carModels = $this->getHLBlockArray(
            $arParams["CAR_MODELS_HLBLOCK_ID"],
            "UF_XML_ID",
            ["UF_NAME", "UF_XML_ID", "UF_COMFORT"],
        );
        if (!$carModels) {
            ShowError(GetMessage("CAR_MODELS_INFO_NOT_FOUND"));
            return;
        }

        $userWorkPosition = \Bitrix\Main\UserTable::getList([
            "filter" => ["ID" => $USER->GetID()],
            "select" => [$arParams["WORK_POSITION_UFIELD_NAME"]],
        ])->Fetch()[$arParams["WORK_POSITION_UFIELD_NAME"]];
        $userComforts = $this->getHLBlockArray(
            $arParams["WORK_POSITIONS_HLBLOCK_ID"],
            "ID",
            ["ID", "UF_COMFORT"],
            ["ID" => $userWorkPosition],
        );
        if (!$userComforts) {
            ShowError(GetMessage("WORK_POSITIONS_INFO_NOT_FOUND"));
            return;
        }
        $userComforts = $userComforts[$userWorkPosition]["UF_COMFORT"];

        $rsElement = CIBlockElement::GetList(
            [],
            [
                "ACTIVE" => "Y",
                "IBLOCK_ID" => $arParams["SERVICE_CARS_IBLOCK_ID"],
                "SITE_ID" => SITE_ID,
            ],
            false,
            false,
            [
                "ID",
                "NAME",
                "PROPERTY_SERVICE_CAR_MODEL",
                "PROPERTY_SERVICE_CAR_DRIVER",
            ]
        );

        $arResult["ITEMS"] = [];
        while ($arElement = $rsElement->GetNext()) {
            $model["XML_ID"] = $arElement["PROPERTY_SERVICE_CAR_MODEL_VALUE"];
            $model["NAME"] = $carModels[$model["XML_ID"]]["UF_NAME"];
            $item["MODEL"] = $model;
            
            $comfort["ID"] = $carModels[$model["XML_ID"]]["UF_COMFORT"];
            if (!in_array($comfort["ID"], $userComforts, true)) {
                continue;
            }
            $comfort["NAME"] = $comfortCategories[$comfort["ID"]]["UF_NAME"];
            $item["COMFORT"] = $comfort;

            $item["DRIVER"] = \Bitrix\Main\UserTable::getList([
                "filter" => ["ID" => $arElement["PROPERTY_SERVICE_CAR_DRIVER_VALUE"]],
                "select" => ["NAME", "SECOND_NAME", "LAST_NAME"],
            ])->Fetch();
            
            $item["CAR"]["ID"] = $arElement["ID"];
            $item["CAR"]["NAME"] = $arElement["NAME"];

            $arResult["ITEMS"][] = $item;
        }
    }

    private function getHLBlockArray($HLBlockId, $idName, $selectArray, $filterArray=[]): mixed
    {
        // $HLBlockId = HighloadBlockTable::resolveHighloadblock($HLBlockName)["ID"];
        $HLBlock = HighloadBlockTable::getById($HLBlockId)->Fetch();
        if (!$HLBlock) {
            return false;
        }
        $HLBlockDataClass = HighloadBlockTable::compileEntity($HLBlock)->getDataClass();
        $rsElement = $HLBlockDataClass::getList([
            "select" => $selectArray,
            "filter" => $filterArray,
        ]);

        $result = [];
        while ($arElement = $rsElement->Fetch()) {
            $id = $arElement[$idName];
            unset($arElement[$idName]);
            $result[$id] = $arElement;
        }
        if (!$result) {
            return false;
        }
        
        return $result;
    }

    public function configureActions(): array
    {
        return [
            "getAvailableItems" => [
                "prefilters" => [new HttpMethod([HttpMethod::METHOD_GET])],
                "postfilters" => []
            ]
        ];
    }

    public function getAvailableItemsAction($startDatetime, $endDatetime, $params)
    {
        $errorCollection = &$this->errorCollection;
        if (!Loader::includeModule("iblock")) {
            $errorCollection[] = new Error(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
            return null;
        }

        $arParams = &$this->arParams;
        $arParams = json_decode($params, true);
        foreach ($arParams as $param) {
            if (!isset($param) || empty($param)) {
                $errorCollection[] = new Error(GetMessage("PARAMS_NOT_SET"));
                return null;
            }
        }
        if (!isset($startDatetime) || empty($startDatetime)) {
            $errorCollection[] = new Error(GetMessage("START_DATETIME_NOT_SET"));
            return null;
        }
        if (!isset($endDatetime) || empty($endDatetime)) {
            $errorCollection[] = new Error(GetMessage("END_DATETIME_NOT_SET"));
            return null;
        }
        $requestedStartDatetime = DateTime::createFromUserTime($startDatetime);
        $requestedEndDatetime = DateTime::createFromUserTime($endDatetime);
        $requestedRange = [$requestedStartDatetime, $requestedEndDatetime];

        $this->initResult();
        $result = $this->arResult;

        $rsElement = CIBlockElement::GetList(
            [],
            [
                "ACTIVE" => "Y",
                "IBLOCK_ID" => $arParams["SERVICE_RIDES_IBLOCK_ID"],
                "SITE_ID" => SITE_ID,
            ],
            false,
            false,
            [
                "PROPERTY_SERVICE_RIDE_START_TIME",
                "PROPERTY_SERVICE_RIDE_END_TIME",
                "PROPERTY_SERVICE_RIDE_CAR",
            ]
        );

        $occupiedCars = [];
        while ($arElement = $rsElement->GetNext()) {
            if (in_array($arElement["PROPERTY_SERVICE_RIDE_CAR_VALUE"], $occupiedCars, true)) {
                continue;
            }
            $occupiedStartDatetime = DateTime::createFromUserTime(
                $arElement["PROPERTY_SERVICE_RIDE_START_TIME_VALUE"]
            );
            $occupiedEndDatetime = DateTime::createFromUserTime(
                $arElement["PROPERTY_SERVICE_RIDE_END_TIME_VALUE"]
            );
            $occupiedRange = [$occupiedStartDatetime, $occupiedEndDatetime];

            if ($this->isIntersecting($requestedRange, $occupiedRange)) {
                $occupiedCars[] = $arElement["PROPERTY_SERVICE_RIDE_CAR_VALUE"];
            }
        }

        foreach ($result["ITEMS"] as $itemId=>$itemParams) {
            if (in_array($itemParams["CAR"]["ID"], $occupiedCars)) {
                unset($result["ITEMS"][$itemId]);
            }
        }

        return $result;
    }

    private function isBetween($datetime, $datetimeRange): bool
    {
        return $datetime > $datetimeRange[0] && $datetime < $datetimeRange[1];
    }

    private function isIntersecting($datetimeRange1, $datetimeRange2): bool
    {
        if ($this->isBetween($datetimeRange1[0], $datetimeRange2)) {
            return true;
        }
        if ($this->isBetween($datetimeRange1[1], $datetimeRange2)) {
            return true;
        }
        if ($this->isBetween($datetimeRange2[0], $datetimeRange1)) {
            return true;
        }
        return false;
    }

    public function getErrors()
    {
        if (!is_null($this->errorCollection)) {
            return $this->errorCollection->toArray();
        }
        return [];
    }

    public function getErrorByCode($code)
    {
        if (!is_null($this->errorCollection)) {
            return $this->errorCollection->getErrorByCode($code);
        }
        return "";
    }
}
