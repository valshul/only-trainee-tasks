<?php

namespace Dev\Site\Handlers;
\CModule::IncludeModule('iblock');

define('LOG_IBLOCK_CODE', 'LOG');

class Iblock
{
    public static function AddLog($arFields)
    {
        /*
         * Проверяем, что новый/измененный элемент не в LOG и что добавление/изменение успешно
         */
        $arProps = \CIBlock::GetByID($arFields['IBLOCK_ID'])->GetNext();
        if ($arProps['CODE'] == LOG_IBLOCK_CODE || !$arFields['RESULT']) {
            return;
        }
        /*
         * Получаем текст описания для анонса
         */
        $sectionChain = \Dev\Site\Helpers\Iblock::GetSectionNameById($arFields['IBLOCK_SECTION'][0]);
        $logPreviewText = implode(' -> ', [$arProps['NAME'], $sectionChain, $arFields['NAME']]);
        /*
         * Проверяем существование раздела в LOG, создаем если его нет
         */
        $logIblockId = \Dev\Site\Helpers\Iblock::GetIblockIdByCode(LOG_IBLOCK_CODE);
        $logSectionFields = [
            'NAME' => $arProps['NAME'],
            'CODE' => $arProps['CODE'],
            'IBLOCK_ID' => $logIblockId,
        ];
        $logSection = \CIBlockSection::GetList([], $logSectionFields, false, ['ID'])->GetNext();
        $logSectionId = $logSection['ID'];
        if ($logSection == false) {
            $bs = new \CIBlockSection;
            $logSectionId = $bs->Add($logSectionFields);
        }
        /*
         * Добавляем элемент в LOG
         */
        $el = new \CIBlockElement;
        $arLoadProductArray = [
            'IBLOCK_SECTION_ID' => $logSectionId,
            'IBLOCK_ID' => $logIblockId,
            'NAME' => $arFields['ID'],
            'ACTIVE' => 'Y',
            'ACTIVE_FROM' => date('d.m.Y'),
            'PREVIEW_TEXT' => $logPreviewText,
        ];
        $el->Add($arLoadProductArray);
    }
}