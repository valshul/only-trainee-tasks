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

CPageOption::SetOptionString("main", "nav_page_in_session", "N");

class NewsListIblockTypeComponent extends CBitrixComponent
{
    /**
     * Prepare input parameters
     * @param array $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams) : array
    {
        $arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"] ?? "");
        $arParams["IBLOCK_ID"] = trim($arParams["IBLOCK_ID"] ?? "");
        $arParams["PARENT_SECTION"] = (int)($arParams["PARENT_SECTION"] ?? 0);
        $arParams["PARENT_SECTION_CODE"] ??= "";
        $arParams["INCLUDE_SUBSECTIONS"] = ($arParams["INCLUDE_SUBSECTIONS"] ?? "") !== "N";
        $arParams["SET_LAST_MODIFIED"] = ($arParams["SET_LAST_MODIFIED"] ?? "") === "Y";

        // sorting
        $orderExpression = "/^(asc|desc|nulls)(,asc|,desc|,nulls)?$/i";
        $arParams["SORT_BY1"] = trim($arParams["SORT_BY1"] ?? "");
        if (empty($arParams["SORT_BY1"])) {
            $arParams["SORT_BY1"] = "ACTIVE_FROM";
        }
        if (!isset($arParams["SORT_ORDER1"]) || !preg_match($orderExpression, $arParams["SORT_ORDER1"])) {
            $arParams["SORT_ORDER1"] = "DESC";
        }
        $arParams["SORT_BY2"] = trim($arParams["SORT_BY2"] ?? "");
        if (empty($arParams["SORT_BY2"])) {
            if (mb_strtoupper($arParams["SORT_BY1"]) === "SORT") {
                $arParams["SORT_BY2"] = "ID";
                $arParams["SORT_ORDER2"] = "DESC";
            } else {
                $arParams["SORT_BY2"] = "SORT";
            }
        }
        if (!isset($arParams["SORT_ORDER2"]) || !preg_match($orderExpression, $arParams["SORT_ORDER2"])) {
            $arParams["SORT_ORDER2"] = "ASC";
        }
        $arParams["CUSTOM_ELEMENT_SORT"] ??= [];

        // filter
        if (!empty($arParams["FILTER_NAME"]) && preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"])) {
            $arParams["arrFilter"] = $GLOBALS[$arParams["FILTER_NAME"]] ?? [];
            if (!is_array($arParams["arrFilter"])) {
                $arParams["arrFilter"] = [];
            }
        }
        $arParams["CHECK_DATES"] = ($arParams["CHECK_DATES"] ?? "") !== "N";

        // cache
        if (!isset($arParams["CACHE_TIME"])) {
            $arParams["CACHE_TIME"] = 36000000;
        }
        $arParams["CACHE_FILTER"] = ($arParams["CACHE_FILTER"] ?? "") === "Y";
        if (!$arParams["CACHE_FILTER"] && !empty($arParams["arrFilter"])) {
            $arParams["CACHE_TIME"] = 0;
        }
        $arParams["CACHE_GROUPS"] ??= "";

        // codes
        if (empty($arParams["FIELD_CODE"]) || !is_array($arParams["FIELD_CODE"])) {
            $arParams["FIELD_CODE"] = [];
        }
        foreach ($arParams["FIELD_CODE"] as $key=>$val) {
            if (!$val) {
                unset($arParams["FIELD_CODE"][$key]);
            }
        }
        if (empty($arParams["PROPERTY_CODE"]) || !is_array($arParams["PROPERTY_CODE"])) {
            $arParams["PROPERTY_CODE"] = [];
        }
        foreach ($arParams["PROPERTY_CODE"] as $key=>$val) {
            if (!$val) {
                unset($arParams["PROPERTY_CODE"][$key]);
            }
        }

        // urls
        $arParams["DETAIL_URL"] = trim($arParams["DETAIL_URL"] ?? "");
        $arParams["SECTION_URL"] = trim($arParams["SECTION_URL"] ?? "");
        $arParams["IBLOCK_URL"] = trim($arParams["IBLOCK_URL"] ?? "");

        $arParams["NEWS_COUNT"] = (int)($arParams["NEWS_COUNT"] ?? 0);
        if ($arParams["NEWS_COUNT"] <= 0)
        {
            $arParams["NEWS_COUNT"] = 20;
        }

        // page settings
        $arParams["SET_TITLE"] = ($arParams["SET_TITLE"] ?? "") !== "N";
        $arParams["SET_BROWSER_TITLE"] = ($arParams["SET_BROWSER_TITLE"] ?? "") === "N" ? "N" : "Y";
        $arParams["SET_META_KEYWORDS"] = ($arParams["SET_META_KEYWORDS"] ?? "") === "N" ? "N" : "Y";
        $arParams["SET_META_DESCRIPTION"] = ($arParams["SET_META_DESCRIPTION"] ?? "") === "N" ? "N" : "Y";
        $arParams["ADD_SECTIONS_CHAIN"] = ($arParams["ADD_SECTIONS_CHAIN"] ?? "") !== "N"; //Turn on by default
        $arParams["INCLUDE_IBLOCK_INTO_CHAIN"] = ($arParams["INCLUDE_IBLOCK_INTO_CHAIN"] ?? "") !== "N";
        $arParams["STRICT_SECTION_CHECK"] = ($arParams["STRICT_SECTION_CHECK"] ?? "") === "Y";
        $arParams["ACTIVE_DATE_FORMAT"] = trim($arParams["ACTIVE_DATE_FORMAT"] ?? "");
        if (empty($arParams["ACTIVE_DATE_FORMAT"])) {
            global $DB;
            $arParams["ACTIVE_DATE_FORMAT"] = $DB->DateFormatToPHP(\CSite::GetDateFormat("SHORT"));
        }
        $arParams["PREVIEW_TRUNCATE_LEN"] = (int)($arParams["PREVIEW_TRUNCATE_LEN"] ?? 0);
        $arParams["HIDE_LINK_WHEN_NO_DETAIL"] = ($arParams["HIDE_LINK_WHEN_NO_DETAIL"] ?? "") === "Y";

        // pager
        $arParams["DISPLAY_TOP_PAGER"] = ($arParams["DISPLAY_TOP_PAGER"] ?? "") === "Y";
        $arParams["DISPLAY_BOTTOM_PAGER"] = ($arParams["DISPLAY_BOTTOM_PAGER"] ?? "") !== "N";
        $arParams["PAGER_TITLE"] = trim($arParams["PAGER_TITLE"] ?? "");
        $arParams["PAGER_SHOW_ALWAYS"] = ($arParams["PAGER_SHOW_ALWAYS"] ?? "") === "Y";
        $arParams["PAGER_TEMPLATE"] = trim($arParams["PAGER_TEMPLATE"] ?? "");
        $arParams["PAGER_DESC_NUMBERING"] = ($arParams["PAGER_DESC_NUMBERING"] ?? "") === "Y";
        $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"] = (int)($arParams["PAGER_DESC_NUMBERING_CACHE_TIME"] ?? 0);
        $arParams["PAGER_SHOW_ALL"] = ($arParams["PAGER_SHOW_ALL"] ?? "") === "Y";
        $arParams["PAGER_BASE_LINK_ENABLE"] ??= "N";
        $arParams["PAGER_BASE_LINK"] ??= "";
        if ($arParams["DISPLAY_TOP_PAGER"] || $arParams["DISPLAY_BOTTOM_PAGER"]) {
            $arParams["arNavParams"] = [
                "nPageSize" => $arParams["NEWS_COUNT"],
                "bDescPageNumbering" => $arParams["PAGER_DESC_NUMBERING"],
                "bShowAll" => $arParams["PAGER_SHOW_ALL"],
            ];
            $arParams["arNavigation"] = CDBResult::GetNavParams($arParams["arNavParams"]);
            if ((int)$arParams["arNavigation"]["PAGEN"] === 0 && $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"] > 0) {
                $arParams["CACHE_TIME"] = $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"];
            }
        } else {
            $arParams["arNavParams"] = [
                "nTopCount" => $arParams["NEWS_COUNT"],
                "bDescPageNumbering" => $arParams["PAGER_DESC_NUMBERING"],
            ];
            $arParams["arNavigation"] = false;
        }
        if (!empty($arParams["PAGER_PARAMS_NAME"]) && preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["PAGER_PARAMS_NAME"])) {
            $arParams["pagerParameters"] = $GLOBALS[$arParams["PAGER_PARAMS_NAME"]] ?? [];
            if (!is_array($arParams["pagerParameters"])) {
                $arParams["pagerParameters"] = [];
            }
        }

        // permissions
        $arParams["CHECK_PERMISSIONS"] = ($arParams["CHECK_PERMISSIONS"] ?? "") !== "N";
        $arParams["USE_PERMISSIONS"] = ($arParams["USE_PERMISSIONS"] ?? "") === "Y";
        if (!is_array($arParams["GROUP_PERMISSIONS"] ?? null)) {
            $adminGroupCode = 1;
            $arParams["GROUP_PERMISSIONS"] = [$adminGroupCode];
        }
        $arParams["bUSER_HAVE_ACCESS"] = !$arParams["USE_PERMISSIONS"];
        if ($arParams["USE_PERMISSIONS"] && isset($GLOBALS["USER"]) && is_object($GLOBALS["USER"])) {
            global $USER;
            $arUserGroupArray = $USER->GetUserGroupArray();
            foreach ($arParams["GROUP_PERMISSIONS"] as $PERM) {
                if (in_array($PERM, $arUserGroupArray)) {
                    $arParams["bUSER_HAVE_ACCESS"] = true;
                    break;
                }
            }
        }
        
        $arParams["INTRANET_TOOLBAR"] ??= "";
        $arParams["MESSAGE_404"] ??= "";
        $arParams["SET_STATUS_404"] ??= "N";
        $arParams["SHOW_404"] ??= "N";
        $arParams["FILE_404"] ??= "";

        return $arParams;
    }

    /**
     * Main component method
     * @return void
     */
    public function executeComponent() : void
    {
        global $USER;
        global $APPLICATION;
        $arParams = $this->arParams;

        if ($this->startResultCache(
            false, 
            [
                ($arParams["CACHE_GROUPS"]==="N" ? false : $USER->GetGroups()),
                $arParams["bUSER_HAVE_ACCESS"],
                $arParams["arNavigation"],
                $arParams["arrFilter"],
                $arParams["pagerParameters"]
            ]
        )) {
            $this->initResult();
            if (empty($this->arResult["IBLOCKS"])) {
                $this->abortResultCache();
                ShowError(GetMessage("IBLOCK_NOT_FOUND"));
                return;
            }
            $this->includeComponentTemplate();
        }
        
        $arResult = $this->arResult;
        $iblockId = end(array_keys($arResult["IBLOCKS"]));

        $arTitleOptions = null;
        if ($USER->IsAuthorized()) {
            if (
                $APPLICATION->GetShowIncludeAreas()
                || (is_object($GLOBALS["INTRANET_TOOLBAR"]) && $arParams["INTRANET_TOOLBAR"]!=="N")
                || $arParams["SET_TITLE"]
            ) {
                if(Loader::includeModule("iblock")) {
                    $arButtons = CIBlock::GetPanelButtons(
                        $iblockId,
                        0,
                        $arParams["PARENT_SECTION"],
                        ["SECTION_BUTTONS" => false]
                    );

                    if ($APPLICATION->GetShowIncludeAreas()) {
                        $this->addIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $arButtons));
                    }

                    if (
                        is_array($arButtons["intranet"])
                        && is_object($INTRANET_TOOLBAR)
                        && $arParams["INTRANET_TOOLBAR"]!=="N"
                    ) {
                        $APPLICATION->AddHeadScript("/bitrix/js/main/utils.js");
                        foreach($arButtons["intranet"] as $arButton) {
                            $INTRANET_TOOLBAR->AddButton($arButton);
                        }
                    }

                    if ($arParams["SET_TITLE"]) {
                        if (isset($arButtons["submenu"]["edit_iblock"])) {
                            $arTitleOptions = [
                                "ADMIN_EDIT_LINK" => $arButtons["submenu"]["edit_iblock"]["ACTION"],
                                "PUBLIC_EDIT_LINK" => "",
                                "COMPONENT_NAME" => $this->getName(),
                            ];
                        }
                    }
                }
            }
        }

        $this->setTemplateCachedData($arResult["NAV_CACHED_DATA"]);

        $ipropertyExists = (!empty($arResult["IPROPERTY_VALUES"][$iblockId]) && is_array($arResult["IPROPERTY_VALUES"][$iblockId]));
        $iproperty = ($ipropertyExists ? $arResult["IPROPERTY_VALUES"][$iblockId] : []);
        if ($arParams["SET_TITLE"]) {
            if ($ipropertyExists && $iproperty["SECTION_PAGE_TITLE"] != "") {
                $APPLICATION->SetTitle($iproperty["SECTION_PAGE_TITLE"], $arTitleOptions);
            } elseif (isset($arResult["IBLOCKS"][$iblockId]["NAME"])) {
                $APPLICATION->SetTitle($arResult["IBLOCKS"][$iblockId]["NAME"], $arTitleOptions);
            }
        }
        if ($ipropertyExists) {
            if ($arParams["SET_BROWSER_TITLE"] === "Y" && $iproperty["SECTION_META_TITLE"] != "") {
                $APPLICATION->SetPageProperty("title", $iproperty["SECTION_META_TITLE"], $arTitleOptions);
            }
            if ($arParams["SET_META_KEYWORDS"] === "Y" && $iproperty["SECTION_META_KEYWORDS"] != "") {
                $APPLICATION->SetPageProperty("keywords", $iproperty["SECTION_META_KEYWORDS"], $arTitleOptions);
            }
            if ($arParams["SET_META_DESCRIPTION"] === "Y" && $iproperty["SECTION_META_DESCRIPTION"] != "") {
                $APPLICATION->SetPageProperty("description", $iproperty["SECTION_META_DESCRIPTION"], $arTitleOptions);
            }
        }

        if ($arParams["INCLUDE_IBLOCK_INTO_CHAIN"] && isset($arResult["IBLOCKS"][$iblockId]["NAME"])) {
            if ($arParams["ADD_SECTIONS_CHAIN"] && is_array($arResult["SECTION"][$iblockId])) {
                $APPLICATION->AddChainItem(
                    $arResult["IBLOCKS"][$iblockId]["NAME"],
                    $arParams["IBLOCK_URL"] <> "" ? $arParams["IBLOCK_URL"] : $arResult["LIST_PAGE_URL"]
                );
            } else {
                $APPLICATION->AddChainItem($arResult["IBLOCKS"][$iblockId]["NAME"]);
            }
        }

        if($arParams["ADD_SECTIONS_CHAIN"] && is_array($arResult["SECTION"][$iblockId])) {
            foreach($arResult["SECTION"][$iblockId]["PATH"] as $arPath) {
                if ($arPath["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"] != "") {
                    $APPLICATION->AddChainItem($arPath["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"], $arPath["~SECTION_PAGE_URL"]);
                } else {
                    $APPLICATION->AddChainItem($arPath["NAME"], $arPath["~SECTION_PAGE_URL"]);
                }
            }
        }

        if ($arParams["SET_LAST_MODIFIED"] && $arResult["ITEMS_TIMESTAMP_X"]) {
            Context::getCurrent()->getResponse()->setLastModified($arResult["ITEMS_TIMESTAMP_X"]);
        }

        unset($iproperty);
        unset($ipropertyExists);
    }

    /**
     * Initialize result
     * @return void
     */
    private function initResult () : void
    {
        $arParams = $this->arParams;
        $arResult = &$this->arResult;

        $arrFilter = $arParams["arrFilter"];
        $pagerParameters = $arParams["pagerParameters"];
        
        if (!Loader::includeModule("iblock")) {
            $this->abortResultCache();
            ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
            return;
        }

        if (empty($arParams["IBLOCK_TYPE"])) {
            $this->abortResultCache();
            ShowError(GetMessage("IBLOCK_TYPE_NOT_SET"));
            return;
        } elseif (empty($arParams["IBLOCK_ID"])) {
            $rsIBlock = CIBlock::GetList([], [
                "ACTIVE" => "Y",
                "TYPE" => $arParams["IBLOCK_TYPE"],
            ]);
        } elseif (is_numeric($arParams["IBLOCK_ID"])) {
            $rsIBlock = CIBlock::GetList([], [
                "ACTIVE" => "Y",
                "TYPE" => $arParams["IBLOCK_TYPE"],
                "ID" => $arParams["IBLOCK_ID"],
            ]);
        } else {
            $rsIBlock = CIBlock::GetList([], [
                "ACTIVE" => "Y",
                "TYPE" => $arParams["IBLOCK_TYPE"],
                "CODE" => $arParams["IBLOCK_ID"],
                "SITE_ID" => SITE_ID,
            ]);
        }
        
        while ($arIBlock = $rsIBlock->GetNext()) {
            $arIBlockId = $arIBlock["ID"];
            $arResult["IBLOCKS"][$arIBlockId] = $arIBlock;
        }
        if (!$arResult) {
            $this->abortResultCache();
            Iblock\Component\Tools::process404(
                trim($arParams["MESSAGE_404"]) ?: GetMessage("T_NEWS_NEWS_NA"),
                true,
                $arParams["SET_STATUS_404"] === "Y",
                $arParams["SHOW_404"] === "Y",
                $arParams["FILE_404"]
            );
            return;
        }
        $arResult["USER_HAVE_ACCESS"] = $arParams["bUSER_HAVE_ACCESS"];

        //SELECT
        $arSelect = array_merge($arParams["FIELD_CODE"], [
            "ID",
            "IBLOCK_ID",
            "IBLOCK_SECTION_ID",
            "NAME",
            "ACTIVE_FROM",
            "TIMESTAMP_X",
            "DETAIL_PAGE_URL",
            "LIST_PAGE_URL",
            "DETAIL_TEXT",
            "DETAIL_TEXT_TYPE",
            "PREVIEW_TEXT",
            "PREVIEW_TEXT_TYPE",
            "PREVIEW_PICTURE",
        ]);
        $bGetProperty = !empty($arParams["PROPERTY_CODE"]);

        //WHERE
        $arFilter = [
            "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
            "IBLOCK_LID" => SITE_ID,
            "ACTIVE" => "Y",
            "CHECK_PERMISSIONS" => $arParams["CHECK_PERMISSIONS"] ? "Y" : "N",
        ];
        if ($arParams["CHECK_DATES"]) {
            $arFilter["ACTIVE_DATE"] = "Y";
        }
        if (!empty($arParams["IBLOCK_ID"])) {
            $arFilter["IBLOCK_ID"] = array_keys($arResult["IBLOCKS"]);
        }

        foreach (array_keys($arResult["IBLOCKS"]) as $iblockId) {
            $PARENT_SECTION = CIBlockFindTools::GetSectionID(
                $arParams["PARENT_SECTION"],
                $arParams["PARENT_SECTION_CODE"],
                [
                    "GLOBAL_ACTIVE" => "Y",
                    "IBLOCK_ID" => $iblockId,
                ]
            );

            if (
                $arParams["STRICT_SECTION_CHECK"]
                && (
                    $arParams["PARENT_SECTION"] > 0
                    || $arParams["PARENT_SECTION_CODE"] <> ""
                )
            ) {
                if ($PARENT_SECTION <= 0) {
                    $this->abortResultCache();
                    Iblock\Component\Tools::process404(
                        trim($arParams["MESSAGE_404"]) ?: GetMessage("T_NEWS_NEWS_NA"),
                        true,
                        $arParams["SET_STATUS_404"] === "Y",
                        $arParams["SHOW_404"] === "Y",
                        $arParams["FILE_404"]
                    );
                    return;
                }
            }

            $arParams["PARENT_SECTION"] = $PARENT_SECTION;
            if ($arParams["PARENT_SECTION"] > 0) {
                $arFilter["SECTION_ID"] = $arParams["PARENT_SECTION"];
                if ($arParams["INCLUDE_SUBSECTIONS"]) {
                    $arFilter["INCLUDE_SUBSECTIONS"] = "Y";
                }
    
                $arResult["SECTION"][$iblockId] = ["PATH" => []];
                $rsPath = CIBlockSection::GetNavChain(
                    $iblockId,
                    $arParams["PARENT_SECTION"],
                    [
                        "ID",
                        "IBLOCK_ID",
                        "NAME",
                        "SECTION_PAGE_URL",
                    ]
                );
    
                $rsPath->SetUrlTemplates("", $arParams["SECTION_URL"], $arParams["IBLOCK_URL"]);
                while ($arPath = $rsPath->GetNext()) {
                    $ipropValues = new Iblock\InheritedProperty\SectionValues($iblockId, $arPath["ID"]);
                    $arPath["IPROPERTY_VALUES"] = $ipropValues->getValues();
                    $arResult["SECTION"][$iblockId]["PATH"][] = $arPath;
                }
                unset($arPath, $rsPath);
    
                $ipropValues = new Iblock\InheritedProperty\SectionValues($iblockId, $arParams["PARENT_SECTION"][$iblockId]);
                $arResult["IPROPERTY_VALUES"][$iblockId] = $ipropValues->getValues();
            } else {
                $arResult["SECTION"] = false;
            }
        }

        //ORDER BY
        $arSort = [];
        if (!empty($arParams["CUSTOM_ELEMENT_SORT"]) && is_array($arParams["CUSTOM_ELEMENT_SORT"])) {
            foreach ($arParams["CUSTOM_ELEMENT_SORT"] as $field => $value) {
                $field = strtoupper($field);
                if (isset($arSort[$field])) {
                    continue;
                }
                if ($field === "ID" && !empty($value) && is_array($value)) {
                    Collection::normalizeArrayValuesByInt($value, false);
                    if (empty($value)) {
                        continue;
                    }
                } else {
                    if (!is_string($value)) {
                        continue;
                    }
                    if (!preg_match($orderExpression, $value)) {
                        continue;
                    }
                } 
                $arSort[$field] = $value;
            }
            unset($field, $value);
        }
        if (empty($arSort)) {
            $arSort = [
                $arParams["SORT_BY1"] => $arParams["SORT_ORDER1"],
                $arParams["SORT_BY2"] => $arParams["SORT_ORDER2"],
            ];
        }
        if (!isset($arSort["ID"])) {
            $arSort["ID"] = "DESC";
        }

        $shortSelect = ["ID", "IBLOCK_ID"];
        foreach (array_keys($arSort) as $index) {
            if (!in_array($index, $shortSelect)) {
                $shortSelect[] = $index;
            }
        }

        $listPageUrl = "";
        $arResult["ITEMS"] = [];
        $arResult["ELEMENTS"] = [];
        $rsElement = CIBlockElement::GetList(
            $arSort,
            array_merge($arFilter, $arrFilter),
            false,
            $arParams["arNavParams"],
            $shortSelect
        );
        while ($row = $rsElement->Fetch()) {
            $id = (int)$row["ID"];
            $iblockId = (int)$row["IBLOCK_ID"];
            $arResult["ITEMS"][$iblockId][$id] = $row;
            $arResult["ELEMENTS"][] = $id;
        }
        unset($row);

        if (!empty($arResult["ITEMS"])) {
            $elementFilter = [
                "IBLOCK_ID" => array_keys($arResult["IBLOCKS"]),
                "IBLOCK_LID" => SITE_ID,
                "ID" => $arResult["ELEMENTS"]
            ];
            if (isset($arrFilter["SHOW_NEW"])) {
                $elementFilter["SHOW_NEW"] = $arrFilter["SHOW_NEW"];
            }

            $obParser = new CTextParser;
            $iterator = CIBlockElement::GetList(
                [],
                $elementFilter,
                false,
                false,
                $arSelect
            );
            $iterator->SetUrlTemplates(
                $arParams["DETAIL_URL"],
                "",
                ($arParams["IBLOCK_URL"] ?? "")
            );
            while ($arItem = $iterator->GetNext()) {
                $arButtons = CIBlock::GetPanelButtons(
                    $arItem["IBLOCK_ID"],
                    $arItem["ID"],
                    0,
                    ["SECTION_BUTTONS" => false, "SESSID" => false]
                );
                $arItem["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"] ?? "";
                $arItem["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"] ?? "";

                if ($arParams["PREVIEW_TRUNCATE_LEN"] > 0) {
                    $arItem["PREVIEW_TEXT"] = $obParser->html_cut($arItem["PREVIEW_TEXT"], $arParams["PREVIEW_TRUNCATE_LEN"]);
                }
                if ($arItem["ACTIVE_FROM"] <> "") {
                    $arItem["DISPLAY_ACTIVE_FROM"] = CIBlockFormatProperties::DateFormat(
                        $arParams["ACTIVE_DATE_FORMAT"],
                        MakeTimeStamp($arItem["ACTIVE_FROM"],
                        CSite::GetDateFormat()));
                } else {
                    $arItem["DISPLAY_ACTIVE_FROM"] = "";
                }

                Iblock\InheritedProperty\ElementValues::queue($arItem["IBLOCK_ID"], $arItem["ID"]);

                $arItem["FIELDS"] = [];
                if ($bGetProperty) {
                    $arItem["PROPERTIES"] = [];
                }
                $arItem["DISPLAY_PROPERTIES"] = array();

                if ($arParams["SET_LAST_MODIFIED"]) {
                    $time = DateTime::createFromUserTime($arItem["TIMESTAMP_X"]);
                    if (
                        !isset($arResult["ITEMS_TIMESTAMP_X"])
                        || $time->getTimestamp() > $arResult["ITEMS_TIMESTAMP_X"]->getTimestamp()
                    ) {
                        $arResult["ITEMS_TIMESTAMP_X"] = $time;
                    }
                }

                if ($listPageUrl === "" && isset($arItem["~LIST_PAGE_URL"])) {
                    $listPageUrl = $arItem["~LIST_PAGE_URL"];
                }

                $id = (int)$arItem["ID"];
                $iblockId = (int)$arItem["IBLOCK_ID"];
                $arResult["ITEMS"][$iblockId][$id] = $arItem;
            }
            unset($obElement);
            unset($iterator);

            if ($bGetProperty) {
                unset($elementFilter["IBLOCK_LID"]);
                foreach (array_keys($arResult["ITEMS"]) as $iblockId) {
                    CIBlockElement::GetPropertyValuesArray(
                        $arResult["ITEMS"][$iblockId],
                        $iblockId,
                        $elementFilter
                    );
                }
            }
        }

        foreach (array_keys($arResult["ITEMS"]) as $iblockId) {
            $arResult["ITEMS"][$iblockId] = array_values($arResult["ITEMS"][$iblockId]);
            foreach ($arResult["ITEMS"][$iblockId] as &$arItem) {
                if ($bGetProperty) {
                    foreach ($arParams["PROPERTY_CODE"] as $pid) {
                        $prop = &$arItem["PROPERTIES"][$pid];
                        if (
                            (is_array($prop["VALUE"]) && count($prop["VALUE"]) > 0)
                            || (!is_array($prop["VALUE"]) && $prop["VALUE"] <> "")
                        ) {
                            $arItem["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arItem, $prop);
                        }
                    }
                }
    
                $ipropValues = new Iblock\InheritedProperty\ElementValues($arItem["IBLOCK_ID"], $arItem["ID"]);
                $arItem["IPROPERTY_VALUES"] = $ipropValues->getValues();
                Iblock\Component\Tools::getFieldImageData(
                    $arItem,
                    array("PREVIEW_PICTURE", "DETAIL_PICTURE"),
                    Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
                    "IPROPERTY_VALUES"
                );
    
                foreach($arParams["FIELD_CODE"] as $code) {
                    if(array_key_exists($code, $arItem)) {
                        $arItem["FIELDS"][$code] = $arItem[$code];
                    }
                }
            }
            unset($arItem);
            if ($bGetProperty) {
                \CIBlockFormatProperties::clearCache();
            }
        }

        $navComponentParameters = [];
        if ($arParams["PAGER_BASE_LINK_ENABLE"] === "Y") {
            $pagerBaseLink = trim($arParams["PAGER_BASE_LINK"]);
            if ($pagerBaseLink === "" && $listPageUrl !== "") {
                $pagerBaseLink = $listPageUrl;
            }
            if ($pagerParameters && isset($pagerParameters["BASE_LINK"])) {
                $pagerBaseLink = $pagerParameters["BASE_LINK"];
                unset($pagerParameters["BASE_LINK"]);
            }
            $navComponentParameters["BASE_LINK"] = CHTTP::urlAddParams($pagerBaseLink, $pagerParameters, ["encode" => true]);
        }

        $arResult["NAV_STRING"] = $rsElement->GetPageNavStringEx(
            $navComponentObject,
            $arParams["PAGER_TITLE"],
            $arParams["PAGER_TEMPLATE"],
            $arParams["PAGER_SHOW_ALWAYS"],
            $this,
            $navComponentParameters
        );
        $arResult["NAV_CACHED_DATA"] = null;
        $arResult["NAV_RESULT"] = $rsElement;
        $arResult["NAV_PARAM"] = $navComponentParameters;

        $this->setResultCacheKeys([
            "IBLOCKS",
            "LIST_PAGE_URL",
            "NAV_CACHED_DATA",
            "SECTION",
            "ELEMENTS",
            "IPROPERTY_VALUES",
            "ITEMS_TIMESTAMP_X",
        ]);
    }
}