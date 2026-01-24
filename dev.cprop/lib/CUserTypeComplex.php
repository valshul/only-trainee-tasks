<?php

\Bitrix\Main\Loader::includeModule('dev.cprop');
use Bitrix\Main\UserField\Types\BaseType,
    \Bitrix\Main\Localization\Loc;

class CUserTypeComplex extends BaseType
{
    public const
        USER_TYPE_ID = 'ComplexUserType',
        RENDER_COMPONENT = 'dev:dev.field.complex';

    public static function getDescription(): array
    {
        return [
            'USER_TYPE_ID' => self::USER_TYPE_ID,
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => Loc::getMessage('DEV_CUSERTYPE_DESC'),
            'BASE_TYPE' => \CUserTypeManager::BASE_TYPE_STRING,
        ];
    }

    public static function getDbColumnType(): string
    {
        return "text(1000)";
    }

    public static function getEditFormHTML(array $userField, ?array $htmlControl): string
    {
        // $arProperty:
        $arProperty = $userField;
        $arProperty['USER_TYPE_SETTINGS'] = $arProperty['SETTINGS'];

        // $value:
        if (is_array($arProperty['VALUE'])) {
            preg_match('/(?<=\[)\d+(?=\])/', $htmlControl['NAME'], $matches);
            $arProperty['VALUE'] = $arProperty['VALUE'][(int)$matches[0]];
        }
        $value = CIBlockPropertyComplex::ConvertFromDB([], $arProperty);

        // $strHTMLControlName:
        $strHTMLControlName = $htmlControl;
        $strHTMLControlName['VALUE'] = $htmlControl['NAME'];
        
        return CIBlockPropertyComplex::GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName);
    }

    public static function prepareSettings(array $userField): array
    {
        if (isset($userField['SETTINGS']) && is_array($userField['SETTINGS'])) {
            return $userField['SETTINGS'];
        }
        return [];
    }
    
    public static function onBeforeSave($userField, $value)
    {
        // $arProperty:
        $arProperty = $userField;
        $arProperty['USER_TYPE_SETTINGS'] = $arProperty['SETTINGS'];
        
        // $arValue:
        $arValue = [
            'VALUE' => $value
        ];

        $arResult = CIBlockPropertyComplex::ConvertToDB($arProperty, $arValue);
        return $arResult['VALUE'];
    }
}