<?php

use \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

class dev_cprop extends CModule
{
    var $MODULE_ID  = 'dev.cprop';

    function __construct()
    {
        $arModuleVersion = [];
        include __DIR__ . '/version.php';

        $this->MODULE_ID = 'dev.cprop';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('DEV_CPROP_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('DEV_CPROP_MODULE_DESC');

        $this->PARTNER_NAME = Loc::getMessage('DEV_CPROP_PARTNER_NAME');

        $this->FILE_PREFIX = 'cprop';
        $this->MODULE_FOLDER = str_replace('.', '_', $this->MODULE_ID);
        $this->FOLDER = 'bitrix';

        $this->INSTALL_PATH_FROM = '/' . $this->FOLDER . '/modules/' . $this->MODULE_ID;
    }

    function isVersionD7()
    {
        return true;
    }

    function DoInstall()
    {
        global $APPLICATION;
        if($this->isVersionD7())
        {
            $this->InstallDB();
            $this->InstallEvents();
            $this->InstallFiles();

            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
        }
        else
        {
            $APPLICATION->ThrowException(Loc::getMessage('DEV_CPROP_INSTALL_ERROR_VERSION'));
        }
    }

    function DoUninstall()
    {
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

        $this->UnInstallFiles();
        $this->UnInstallEvents();
        $this->UnInstallDB();
    }

    function InstallDB()
    {
        return true;
    }

    function UnInstallDB()
    {
        return true;
    }

    function installFiles()
    {
        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/components',
            $_SERVER['DOCUMENT_ROOT'] . '/local/components',
            true,
            true
        );
        return true;
    }

    function uninstallFiles()
    {
        DeleteDirFilesEx('/local/components/dev');
        return true;
    }

    function getEvents($classHandler)
    {
        if ($classHandler == 'CIBlockPropertyComplex') {
            return [
                ['FROM_MODULE' => 'iblock',
                'EVENT' => 'OnIBlockPropertyBuildList',
                'TO_METHOD' => 'GetUserTypeDescription'],
            ];
        } elseif ($classHandler == 'CUserTypeComplex') {
            return [
                ['FROM_MODULE' => 'main',
                'EVENT' => 'OnUserTypeBuildList',
                'TO_METHOD' => 'getDescription'],
            ];
        } else {
            return [];
        }
    }

    function InstallEvents()
    {
        $classHandlers = ['CIBlockPropertyComplex', 'CUserTypeComplex'];
        $eventManager = EventManager::getInstance();

        foreach ($classHandlers as $classHandler) {
            $arEvents = $this->getEvents($classHandler);
            foreach ($arEvents as $arEvent) {
                $eventManager->registerEventHandler(
                    $arEvent['FROM_MODULE'],
                    $arEvent['EVENT'],
                    $this->MODULE_ID,
                    $classHandler,
                    $arEvent['TO_METHOD']
                );
            }
        }

        return true;
    }

    function UnInstallEvents()
    {
        $classHandlers = ['CIBlockPropertyComplex', 'CUserTypeComplex'];
        $eventManager = EventManager::getInstance();

        foreach ($classHandlers as $classHandler) {
            $arEvents = $this->getEvents($classHandler);
            foreach ($arEvents as $arEvent) {
                $eventManager->unregisterEventHandler(
                    $arEvent['FROM_MODULE'],
                    $arEvent['EVENT'],
                    $this->MODULE_ID,
                    $classHandler,
                    $arEvent['TO_METHOD']
                );
            }
        }
        
        return true;
    }
}