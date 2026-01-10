<?php

namespace Dev\Site\Helpers;
\CModule::IncludeModule('iblock');

define('LOG_IBLOCK_CODE', 'LOG');

class Iblock
{
    static function GetSectionNameById($sectionId)
    {
        $sectionProps = \CIBlockSection::GetByID($sectionId)->GetNext();
        $sectionName = $sectionProps['NAME'];
        
        if ($sectionProps['IBLOCK_SECTION_ID'] != '') {
            $sectionParentName = self::GetSectionNameById($sectionProps['IBLOCK_SECTION_ID']);
            return implode(' -> ', [$sectionParentName, $sectionName]);
        } else {
            return $sectionName;
        }
    }
    
    static function GetIblockIdByCode($iblockCode)
    {
        $iblock = \CIBlock::GetList([], ['CODE' => $iblockCode])->GetNext();
        return $iblock['ID'];
    }
}