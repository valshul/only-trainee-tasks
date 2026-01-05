<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if (!$USER->IsAdmin()) {
    LocalRedirect("/");
}
\Bitrix\Main\Loader::includeModule("iblock");

$IBLOCK_ID = 7;
$el = new CIBlockElement;

$rsProp = CIBlockPropertyEnum::GetList(
    ["SORT" => "ASC", "VALUE" => "ASC"],
    ["IBLOCK_ID" => $IBLOCK_ID]
);
while ($arProp = $rsProp->Fetch()) {
    $key = trim($arProp["VALUE"]);
    $key = mb_strtolower($key);
    $arProps[$arProp["PROPERTY_CODE"]][$key] = $arProp["ID"];
}

if (($handle = fopen("vacancy.csv", "r")) !== false) {
    fgets($handle); // skip header
    
    while (($row = fgetcsv($handle, 1000, ",")) !== false) {
        if ($row[3] == "") {
            continue; // if no name is given
        }
        
        $PROP["ACTIVITY"] = $row[9];
        $PROP["FIELD"] = $row[11];
        $PROP["OFFICE"] = $row[1];
        $PROP["LOCATION"] = $row[2];
        $PROP["REQUIRE"] = $row[4];
        $PROP["DUTY"] = $row[5];
        $PROP["CONDITIONS"] = $row[6];
        $PROP["EMAIL"] = $row[12];
        $PROP["DATE"] = date("d.m.Y");
        $PROP["TYPE"] = $row[8];
        $PROP["SALARY_TYPE"] = "";
        $PROP["SALARY_VALUE"] = $row[7];
        $PROP["SCHEDULE"] = $row[10];
        
        if (stripos($PROP["SALARY_VALUE"], "-") !== false) {
            $PROP["SALARY_VALUE"] = "";
        } elseif (stripos($PROP["SALARY_VALUE"], "по договоренности") !== false) {
            $PROP["SALARY_VALUE"] = "";
            $PROP["SALARY_TYPE"] = "договорная";
        } elseif (preg_match("/^от|^до/m", $PROP["SALARY_VALUE"])) {
            $PROP["SALARY_TYPE"] = mb_substr($PROP["SALARY_VALUE"], 0, 2);
            $PROP["SALARY_VALUE"] = preg_replace("/[^0-9]/", "", $PROP["SALARY_VALUE"]);
        } else {
            $PROP["SALARY_TYPE"] = "=";
        }
        
        foreach ($PROP as $key => &$value) {
            $value = trim($value, "\n");
            if ($value == "") {
                continue;
            }
            
            if ($key == "REQUIRE" || $key == "DUTY" || $key == "CONDITIONS") {
                if (stripos($value, "•") !== false) {
                    $value = explode("•", $value);
                } elseif (preg_match("/^\d+\./m", $value)) {
                    $value = preg_split("/^\d+\./m", $value);
                } elseif (preg_match("/^-/m", $value)) {
                    $value = preg_split("/^-/m", $value);
                } else {
                    $value = explode("\n", $value);
                }
                $value = array_filter($value);
                $value = array_map("trim", $value);
            } elseif ($arProps[$key]) {
                $value = str_replace(["\n", "\r\n"], " ", $value);
                $value = mb_strtolower($value);
                if ($arProps[$key][$value]) {
                    $value = $arProps[$key][$value];
                } else {
                    $bestPercent = 0;
                    $bestMatch = "";
                    foreach ($arProps[$key] as $arPropKey => $arPropValue) {
                        if (stripos($arPropKey, $value) !== false) {
                            $bestMatch = $arPropValue;
                            break;
                        }
                        similar_text($arPropKey, $value, $percent);
                        if ($percent > $bestPercent) {
                            $bestPercent = $percent;
                            $bestMatch = $arPropValue;
                        }
                    }
                    $value = $bestMatch;
                }
            }
        }
        
        $arLoadProductArray = [
            "MODIFIED_BY" => $USER->GetID(),
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => $IBLOCK_ID,
            "PROPERTY_VALUES" => $PROP,
            "NAME" => $row[3],
            "ACTIVE" => end($row) ? "Y" : "N",
        ];
        
        if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
            echo "Добавлен элемент с ID: " . $PRODUCT_ID . ";<br>";
        } else {
            echo "Ошибка: " . $el->LAST_ERROR . "<br>";
        }
    }
    fclose($handle);
}