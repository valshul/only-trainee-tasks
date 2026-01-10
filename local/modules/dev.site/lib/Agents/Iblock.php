<?php

namespace Dev\Site\Agents;
\CModule::IncludeModule('iblock');

define('LOG_IBLOCK_CODE', 'LOG');

class Iblock
{
    public static function clearOldLogs()
    {
        /*
         * Получаем список идентификаторов элементов LOG
         * отсортированных в убывающем порядке по дате изменения
         */
        $rsLogs = \CIBlockElement::GetList(
            ['TIMESTAMP_X' => 'DESC'],
            ['IBLOCK_ID' => \Dev\Site\Helpers\Iblock::GetIblockIdByCode(LOG_IBLOCK_CODE)],
            false,
            false,
            ['ID']
        );
        $rsLogs->NavStart();
        $arLogs = $rsLogs->arResult;
        /*
         * Удаляем все элементы начиная с индекса 10
         */
        foreach(array_slice($arLogs, 10) as $arKey => $arValue) {
            \CIBlockElement::Delete($arValue['ID']);
        }
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }
}