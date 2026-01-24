<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

\Bitrix\Main\Loader::includeModule('dev.cprop');

$arProperty = $arResult['userField'];
$arProperty['USER_TYPE_SETTINGS'] = $arProperty['SETTINGS'];
$strHTMLControlName = $arResult['additionalParameters'];
$arPropertyFields = [];

echo CIBlockPropertyComplex::GetSettingsHTML($arProperty, $strHTMLControlName, $arPropertyFields);
