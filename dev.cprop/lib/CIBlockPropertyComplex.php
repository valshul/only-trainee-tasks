<?php

use \Bitrix\Main\Localization\Loc;

class CIBlockPropertyComplex
{
    private static $showedCss = false;
    private static $showedJs = false;
    
    public static function GetUserTypeDescription()
    {
        return [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'ComplexProperty',
            'DESCRIPTION' => Loc::getMessage('DEV_CPROP_DESC'),
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            'ConvertToDB' => [__CLASS__, 'ConvertToDB'],
            'ConvertFromDB' => [__CLASS__, 'ConvertFromDB'],
            'GetSettingsHTML' => [__CLASS__, 'GetSettingsHTML'],
            'PrepareSettings' => [__CLASS__, 'PrepareSettings'],
            'GetLength' => [__CLASS__, 'GetLength'],
            'GetPublicViewHTML' => [__CLASS__, 'GetPublicViewHTML']
        ];
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        $hideText = Loc::getMessage('DEV_CPROP_HIDE_TEXT');
        $clearText = Loc::getMessage('DEV_CPROP_CLEAR_TEXT');

        self::ShowCss();
        self::ShowJs();

        if (!empty($arProperty['USER_TYPE_SETTINGS'])) {
            $arFields = self::PrepareUserTypeSettings($arProperty['USER_TYPE_SETTINGS']);
        } else {
            return '<span>' . Loc::getMessage('DEV_CPROP_ERROR_INCORRECT_SETTINGS') . '</span>';
        }

        $result = '<div class="mf-gray"><a class="cl mf-toggle">' . $hideText . '</a>';
        if ($arProperty['MULTIPLE'] === 'Y') {
            $result .= ' | <a class="cl mf-delete">' . $clearText . '</a></div>';
        }

        $result .= '<table class="mf-fields-list active">';
        foreach ($arFields as $code => $arItem) {
            if ($arItem['TYPE'] === 'string') {
                $result .= self::ShowString($code, $arItem['TITLE'], $value, $strHTMLControlName);
            } elseif ($arItem['TYPE'] === 'file') {
                $result .= self::ShowFile($code, $arItem['TITLE'], $value, $strHTMLControlName);
            } elseif ($arItem['TYPE'] === 'text') {
                $result .= self::ShowTextarea($code, $arItem['TITLE'], $value, $strHTMLControlName);
            } elseif ($arItem['TYPE'] === 'html') {
                $result .= self::ShowHtmlEditor($code, $arItem['TITLE'], $value, $strHTMLControlName);
            } elseif ($arItem['TYPE'] === 'date') {
                $result .= self::ShowDate($code, $arItem['TITLE'], $value, $strHTMLControlName);
            } elseif ($arItem['TYPE'] === 'element') {
                $result .= self::ShowBindElement($code, $arItem['TITLE'], $value, $strHTMLControlName);
            }
        }
        $result .= '</table>';

        return $result;
    }

    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return $value;
    }

    public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
    {
        $arPropertyFields = [
            'USER_TYPE_SETTINGS_TITLE' => Loc::getMessage('DEV_CPROP_SETTINGS_TITLE'),
            'HIDE' => [
                'ROW_COUNT',
                'COL_COUNT',
                'DEFAULT_VALUE',
                'SEARCHABLE',
                'SMART_FILTER',
                'WITH_DESCRIPTION',
                'FILTRABLE',
                'MULTIPLE_CNT',
                'IS_REQUIRED'
            ],
            'SET' => [
                'MULTIPLE_CNT' => 1,
                'SMART_FILTER' => 'N',
                'FILTRABLE' => 'N',
            ],
        ];

        self::ShowJsForSettings($strHTMLControlName['NAME']);
        self::ShowCssForSettings();

        $result = 
        '<tr><td colspan="2" align="center">
            <table id="many-fields-table" class="many-fields-table internal">        
                <tr valign="top" class="heading mf-setting-title">
                    <td>XML_ID</td>
                    <td>' . Loc::getMessage('DEV_CPROP_SETTING_FIELD_TITLE') . '</td>
                    <td>' . Loc::getMessage('DEV_CPROP_SETTING_FIELD_SORT') . '</td>
                    <td>' . Loc::getMessage('DEV_CPROP_SETTING_FIELD_TYPE') . '</td>
                </tr>';

        $arFields = self::PrepareUserTypeSettings($arProperty['USER_TYPE_SETTINGS']);
        if (!empty($arFields)) {
            foreach ($arFields as $code => $arItem) {
                $nameStart = $strHTMLControlName["NAME"] . '[' . $code;
                $result .= 
                '<tr valign="top">
                    <td><input type="text" class="inp-code" size="20" value="' . $code . '"></td>
                    <td><input type="text" class="inp-title" size="35" name="' . $nameStart . '_TITLE]" value="' . $arItem['TITLE'] . '"></td>
                    <td><input type="text" class="inp-sort" size="5" name="' . $nameStart . '_SORT]" value="' . $arItem['SORT'] . '"></td>
                    <td>
                        <select class="inp-type" name="' . $nameStart . '_TYPE]">
                            ' . self::GetOptionList($arItem['TYPE']) . '
                        </select>
                    </td>
                </tr>';
            }
        }

        $result .= 
                '<tr valign="top">
                    <td><input type="text" class="inp-code" size="20"></td>
                    <td><input type="text" class="inp-title" size="35"></td>
                    <td><input type="text" class="inp-sort" size="5" value="500"></td>
                    <td>
                        <select class="inp-type">' . self::GetOptionList() . '</select>
                    </td>
                </tr>
            </table>
            <tr><td colspan="2" style="text-align: center;">
                <input type="button" value="' . Loc::getMessage('DEV_CPROP_SETTING_BTN_ADD') . '" onclick="addNewRows()">
            </td></tr>
        </td></tr>';

        return $result;
    }

    public static function PrepareSettings($arFields)
    {
        if (isset($arFields['USER_TYPE_SETTINGS']) && is_array($arFields['USER_TYPE_SETTINGS'])) {
            return $arFields['USER_TYPE_SETTINGS'];
        }
        return [];
    }

    public static function GetLength($arProperty, $arValue)
    {
        $arFields = self::PrepareUserTypeSettings(unserialize($arProperty['USER_TYPE_SETTINGS']));
        foreach ($arValue['VALUE'] as $code => $value) {
            if ($arFields[$code]['TYPE'] === 'file') {
                if (!empty($value['name']) || (!empty($value['OLD']) && empty($value['DEL']))) {
                    return true;
                }
            } else {
                if (!empty($value)) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function ConvertToDB($arProperty, $arValue)
    {
        $arFields = self::PrepareUserTypeSettings($arProperty['USER_TYPE_SETTINGS']);
        foreach ($arValue['VALUE'] as $code => $value) {
            if ($arFields[$code]['TYPE'] === 'file') {
                $arValue['VALUE'][$code] = self::PrepareFileToDB($value);
            }
        }

        foreach ($arValue['VALUE'] as $value) {
            if (!empty($value)) {
                $arResult['VALUE'] = json_encode($arValue['VALUE']);
                return $arResult;
            }
        }

        return ['VALUE' => '', 'DESCRIPTION' => ''];
    }

    public static function ConvertFromDB($arProperty, $arValue)
    {
        $result = [];
        if (!empty($arValue['VALUE'])) {
            $result['VALUE'] = json_decode($arValue['VALUE'], true);
        }
        return $result;
    }

    /*
     * Отображение полей комплексного свойства
     */
    private static function ShowString($code, $title, $arValue, $strHTMLControlName)
    {
        $value = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';
        $result = 
        '<tr>
            <td align="right">' . $title . ': </td>
            <td><input
                type="text"
                value="' . $value . '"
                name="' . $strHTMLControlName['VALUE'] . '[' . $code . ']"
            /></td>
        </tr>';
        return $result;
    }

    private static function ShowFile($code, $title, $arValue, $strHTMLControlName)
    {
        if (!empty($arValue['VALUE'][$code]) && !is_array($arValue['VALUE'][$code])) {
            $fileId = $arValue['VALUE'][$code];
        } elseif (!empty($arValue['VALUE'][$code]['OLD'])) {
            $fileId = $arValue['VALUE'][$code]['OLD'];
        } else {
            $fileId = '';
        }

        if (!empty($fileId)) {
            $file = CFile::GetByID($fileId)->Fetch();
            if ($file) {
                $filePath = $file['SRC'];

                if (str_contains($file['CONTENT_TYPE'], 'image')) {
                    $content = '<img src="' . $filePath . '">';
                } else {
                    $content = '<div class="mf-file-name">' . $file['FILE_NAME'] . '</div>';
                }

                $result =
                '<tr>
                    <td align="right" valign="top">' . $title . ': </td>
                    <td><table class="mf-img-table">
                        <tr><td>' . $content . '<br>
                            <div>
                                <label>
                                    <input
                                        name="' . $strHTMLControlName['VALUE'] . '[' . $code.'][DEL]"
                                        value="Y"
                                        type="checkbox"
                                    />
                                    ' . Loc::getMessage("DEV_CPROP_FILE_DELETE") . '
                                </label>
                                <input
                                    name="' . $strHTMLControlName['VALUE'] . '[' . $code.'][OLD]"
                                    value="' . $fileId . '"
                                    type="hidden"
                                />
                            </div>
                        </td></tr>
                    </table></td>
                </tr>';
            }
        } else {
            $result =
            '<tr>
                <td align="right">' . $title . ': </td>
                <td>
                    <input
                        type="file"
                        value=""
                        name="' . $strHTMLControlName['VALUE'] . '[' . $code . ']"
                    />
                </td>
            </tr>';
        }

        return $result;
    }

    public static function ShowTextarea($code, $title, $arValue, $strHTMLControlName)
    {
        $value = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';
        $result =
        '<tr>
            <td align="right" valign="top">' . $title . ': </td>
            <td>
                <textarea
                    rows="8"
                    name="' . $strHTMLControlName['VALUE'] . '[' . $code . ']">'
                    . $value .
                '</textarea>
            </td>
        </tr>';

        return $result;
    }

    public static function ShowHtmlEditor($code, $title, $arValue, $strHTMLControlName)
    {
        $value = !empty($arValue['VALUE'][$code]) ? htmlspecialcharsbx($arValue['VALUE'][$code]) : '';
        $placeholderName = 'PLACEHOLDER_FOR_HTMLAREA_FIELD_NAME';

        $result =
        '<tr>
            <td align="right" valign="top">' . $title . ': </td>
            <td>';
        
        ob_start();
        if (CModule::IncludeModule("fileman")) {
            CFileMan::AddHTMLEditorFrame(
                $placeholderName,
                $value,
                'html',
                'html',
            );
        }
        $result .= ob_get_clean();

        $result .=
            '</td>
        </tr>';
        
        $result = str_replace(
            'name="' . $placeholderName . '"',
            'name="' . $strHTMLControlName['VALUE'] . '[' . $code . ']"',
            $result
        );

        return $result;
    }

    public static function ShowDate($code, $title, $arValue, $strHTMLControlName)
    {
        $value = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';
        $result =
        '<tr>
            <td align="right" valign="top">' . $title . ': </td>
            <td><table>
                <tr><td style="padding: 0;">
                    <div class="adm-input-wrap adm-input-wrap-calendar">
                        <input
                            class="adm-input adm-input-calendar"
                            type="text"
                            name="' . $strHTMLControlName['VALUE'] . '[' . $code . ']"
                            size="23"
                            value="'.$value.'"
                        />
                        <span 
                            class="adm-calendar-icon"
                            onclick="BX.calendar({node: this, field:\'' . $strHTMLControlName['VALUE'] . '[' . $code . ']\', form: \'\', bTime: true, bHideTime: false});">
                        </span>
                    </div>
                </td></tr>
            </table></td>
        </tr>';

        return $result;
    }

    public static function ShowBindElement($code, $title, $arValue, $strHTMLControlName)
    {
        $value = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';

        $elementUrl = '';
        if (!empty($value)) {
            $arElement = \CIBlockElement::GetList(
                [],
                ['ID' => $value],
                false,
                false,
                ['ID', 'IBLOCK_ID', 'IBLOCK_TYPE_ID', 'NAME']
            )->Fetch();
            if (!empty($arElement)) {
                $elementUrl = 
                '<a
                    target="_blank"
                    href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=' . $arElement['IBLOCK_ID'] . '&ID=' . $arElement['ID'] . '&type=' . $arElement['IBLOCK_TYPE_ID'] . '">
                    ' . $arElement['NAME'] . '
                </a>';
            }
        }

        $result =
        '<tr>
            <td align="right">' . $title . ': </td>
            <td>
                <input
                    name="' . $strHTMLControlName['VALUE'] . '[' . $code . ']"
                    id="' . $strHTMLControlName['VALUE'] . '[' . $code.']"
                    value="' . $value . '"
                    size="8"
                    type="text"
                    class="mf-inp-bind-elem"
                />
                <input
                    type="button"
                    value="..."
                    onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang=ru&IBLOCK_ID=0&n=' . $strHTMLControlName['VALUE'] . '&k=' . $code . '\', 900, 700);"
                />
                &nbsp;
                <span>' . $elementUrl . '</span>
            </td>
        </tr>';

        return $result;
    }

    /*
     * CSS & JS
     */
    private static function ShowCss()
    {
        if (!self::$showedCss) {
            self::$showedCss = true;
            ?>
            <style>
                .cl {cursor: pointer;}
                .mf-gray {color: #797777;}
                .mf-fields-list {display: none; padding-top: 10px; margin-bottom: 10px!important; margin-left: -300px!important; border-bottom: 1px #e0e8ea solid!important;}
                .mf-fields-list.active {display: block;}
                .mf-fields-list td {padding-bottom: 5px;}
                .mf-fields-list td:first-child {width: 300px; color: #616060;}
                .mf-fields-list td:last-child {padding-left: 5px;}
                .mf-fields-list input[type="text"] {width: 350px!important;}
                .mf-fields-list textarea {min-width: 350px; max-width: 650px; color: #000;}
                .mf-fields-list img {max-height: 150px; margin: 5px 0;}
                .mf-img-table {background-color: #e0e8e9; color: #616060; width: 100%;}
                .mf-fields-list input[type="text"].adm-input-calendar {width: 170px!important;}
                .mf-file-name {word-break: break-word; padding: 5px 5px 0 0; color: #101010;}
                .mf-fields-list input[type="text"].mf-inp-bind-elem {width: unset!important;}
            </style>
            <?php
        }
    }

    private static function ShowJs()
    {
        $showText = Loc::getMessage('DEV_CPROP_SHOW_TEXT');
        $hideText = Loc::getMessage('DEV_CPROP_HIDE_TEXT');
        
        if (!self::$showedJs) {
            \CJSCore::Init(['jquery3']);
            self::$showedJs = true;
            ?>
            <script>
                $(document).on('click', 'a.mf-toggle', function (e) {
                    e.preventDefault();
                    var table = $(this).closest('tr').find('table.mf-fields-list');
                    $(table).toggleClass('active');
                    if ($(table).hasClass('active')) {
                        $(this).text('<?= $hideText ?>');
                    } else {
                        $(this).text('<?= $showText ?>');
                    }
                });

                $(document).on('click', 'a.mf-delete', function (e) {
                    e.preventDefault();
                    var textInputs = $(this).closest('tr').find('input[type="text"]');
                    $(textInputs).each(function (i, item) {
                        $(item).val('');
                    });
                    var textarea = $(this).closest('tr').find('textarea');
                    $(textarea).each(function (i, item) {
                        $(item).text('');
                    });
                    var checkBoxInputs = $(this).closest('tr').find('input[type="checkbox"]');
                    $(checkBoxInputs).each(function (i, item) {
                        $(item).attr('checked', 'checked');
                    });
                    $(this).closest('tr').hide('slow');
                });
            </script>
            <?php
        }
    }

    private static function ShowCssForSettings()
    {
        if(!self::$showedCss) {
            self::$showedCss = true;
            ?>
            <style>
                .many-fields-table {margin: 0 auto;}
                .mf-setting-title td {text-align: center!important; border-bottom: unset!important;}
                .many-fields-table td {text-align: center;}
                .many-fields-table > input, .many-fields-table > select {width: 90%!important;}
                .inp-sort {text-align: center;}
                .inp-type {min-width: 125px;}
            </style>
            <?php
        }
    }

    private static function ShowJsForSettings($inputName)
    {
        if (!self::$showedJs) {
            \CJSCore::Init(['jquery3']);
            self::$showedJs = true;
            ?>
            <script>
                function addNewRows() {
                    $("#many-fields-table").append('' +
                        '<tr valign="top">' +
                        '<td><input type="text" class="inp-code" size="20"></td>' +
                        '<td><input type="text" class="inp-title" size="35"></td>' +
                        '<td><input type="text" class="inp-sort" size="5" value="500"></td>' +
                        '<td><select class="inp-type"><?= self::GetOptionList() ?></select></td>' +
                        '</tr>');
                }

                $(document).on('change', '.inp-code', function() {
                    var code = $(this).val();
                    if(code.length <= 0) {
                        $(this).closest('tr').find('input.inp-title').removeAttr('name');
                        $(this).closest('tr').find('input.inp-sort').removeAttr('name');
                        $(this).closest('tr').find('select.inp-type').removeAttr('name');
                    } else {
                        $(this).closest('tr').find('input.inp-title').attr('name', '<?= $inputName ?>[' + code + '_TITLE]');
                        $(this).closest('tr').find('input.inp-sort').attr('name', '<?= $inputName ?>[' + code + '_SORT]');
                        $(this).closest('tr').find('select.inp-type').attr('name', '<?= $inputName ?>[' + code + '_TYPE]');
                    }
                });

                $(document).on('input', '.inp-sort', function(){
                    var num = $(this).val();
                    $(this).val(num.replace(/[^0-9]/gim,''));
                });
            </script>
            <?php
        }
    }

    /*
     * Helpers
     */
    private static function PrepareUserTypeSettings($arSettings)
    {
        $arResult = [];

        foreach ($arSettings as $key => $value) {
            if (strstr($key, '_TITLE') !== false) {
                $code = str_replace('_TITLE', '', $key);
                $arResult[$code]['TITLE'] = $value;
            } elseif(strstr($key, '_SORT') !== false) {
                $code = str_replace('_SORT', '', $key);
                $arResult[$code]['SORT'] = $value;
            } elseif(strstr($key, '_TYPE') !== false) {
                $code = str_replace('_TYPE', '', $key);
                $arResult[$code]['TYPE'] = $value;
            }
        }

        uasort($arResult, function ($a, $b)
            {
                if ($a['SORT'] == $b['SORT']) {
                    return 0;
                }
                return ($a['SORT'] < $b['SORT']) ? -1 : 1;
            }
        );
        return $arResult;
    }

    private static function GetOptionList($selected = 'string')
    {
        $arOption = [
            'string' => Loc::getMessage('DEV_CPROP_FIELD_TYPE_STRING'),
            'file' => Loc::getMessage('DEV_CPROP_FIELD_TYPE_FILE'),
            'text' => Loc::getMessage('DEV_CPROP_FIELD_TYPE_TEXT'),
            'html' => Loc::getMessage('DEV_CPROP_FIELD_TYPE_HTML'),
            'date' => Loc::getMessage('DEV_CPROP_FIELD_TYPE_DATE'),
            'element' => Loc::getMessage('DEV_CPROP_FIELD_TYPE_ELEMENT'),
        ];

        $result = '';
        foreach ($arOption as $code => $name){
            $s = '';
            if ($code === $selected) {
                $s = 'selected';
            }
            $result .= '<option value="' . $code . '" ' . $s . '>' . $name . '</option>';
        }
        return $result;
    }

    private static function PrepareFileToDB($arValue)
    {
        $result = false;

        if (!empty($arValue['DEL']) && $arValue['DEL'] === 'Y' && !empty($arValue['OLD'])) {
            CFile::Delete($arValue['OLD']);
        } elseif(!empty($arValue['OLD'])) {
            $result = $arValue['OLD'];
        } elseif(!empty($arValue['name'])) {
            $result = CFile::SaveFile($arValue, 'dev_cprop');
        }
        return $result;
    }
}