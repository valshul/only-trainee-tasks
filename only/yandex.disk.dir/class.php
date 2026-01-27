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

use \Bitrix\Main\Engine\Contract\Controllerable;
use \Bitrix\Main\Errorable;
use \Bitrix\Main\Error;
use \Bitrix\Main\ErrorCollection;
use \Arhitector\Yandex\Disk;

require_once $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";

define("DISK_OAUTH", "y0__xD3qqWqBBj-lD0ggZS_lhbnJe5ugWfzqiwzqJDCcYAujit32g");

class YandexDiskDirComponent extends CBitrixComponent implements Controllerable, Errorable
{
    private $DiskOAuthUserFieldName = "UF_YANDEX_DISK_OAUTH";

    /** @var ErrorCollection */
    protected $errorCollection;

    /**
     * Prepare input parameters
     * @param array $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams): array
    {
        if (isset($arParams["OAUTH_USERFIELD_ID"]) && !empty($arParams["OAUTH_USERFIELD_ID"])) {
            $this->DiskOAuthUserFieldName = $arParams["OAUTH_USERFIELD_ID"];
        } else {
            ShowError("userfield name for oauth was not given");
        }
        $this->errorCollection = new ErrorCollection();

        $arParams["VARIABLE_ALIASES"] = [
            "DIR" => $arParams["VARIABLE_ALIASES_DIR"],
            "FILE" => $arParams["VARIABLE_ALIASES_FILE"],
        ];
        $arParams["DIR"] = urldecode($arParams["DIR"]);

        return $arParams;
    }

    /**
     * Main component method
     * @return void
     */
    public function executeComponent(): void
    {
        $this->initResult();
        $this->includeComponentTemplate();
    }

    /**
     * Initialize result
     * @return void
     */
    private function initResult(): void
    {
        $arParams = $this->arParams;
        $arResult = &$this->arResult;
        
        $dirItems = [];

        try {
            $disk = new Disk($this->getDiskOAuth());
            try {
                $resource = $disk->getResource("disk:/" . $arParams["DIR"]);
                if ($resource->isDir()) {
                    foreach ($resource->items as $item) {
                        $itemParams = $item->toArray(["path", "name", "type", "size"]);
                        $itemParams["path"] = str_replace("disk:/", "", $itemParams["path"]);
                        
                        $itemParams["url"] = str_replace(
                            "#" . strtoupper($itemParams["type"]) . "#",
                            urlencode($itemParams["path"]),
                            $arParams[strtoupper($itemParams["type"]) . "_URL"]
                        );
                        
                        $dirItems[] = $itemParams;
                    }
                    $arResult["ITEMS"] = $dirItems;

                    if ($arParams["DIR"] != "") {
                        $previousDir = explode("/", $arParams["DIR"], -1);
                        if (count($previousDir) > 0) {
                            $previousDir = implode("/", $previousDir);
                            $arResult["PREVIOUS_DIR_URL"] = str_replace(
                                "#DIR#",
                                urlencode($previousDir),
                                $arParams["DIR_URL"]
                            );
                        } else {
                            $arResult["PREVIOUS_DIR_URL"] = $arParams["DISK_URL"];
                        }
                    }
                } else {
                    ShowError("directory not found.");
                }
            } catch (Arhitector\Yandex\Client\Exception\NotFoundException $e) {
                ShowError("resource not found.");
            }
        } catch (Arhitector\Yandex\Client\Exception\UnauthorizedException $e) {
            ShowError("unable to authorise / invalid token.");
        }
    }

    private function getDiskOAuth(): string
    {
        global $USER;
        $user = \Bitrix\Main\UserTable::getList([
            "filter" => ["ID" => $USER->GetID()],
            "select" => [$this->DiskOAuthUserFieldName],
        ])->Fetch();
        return $user[$this->DiskOAuthUserFieldName];
    }

    public function configureActions(): array
    {
        return [];
    }

    public function deleteResourceAction($resourcePath)
    {
        if (!isset($resourcePath) || empty($resourcePath)) {
            $this->errorCollection[] = new Error("no resource path was given");
            return null;
        }

        try {
            $disk = new Disk($this->getDiskOAuth());
            try {
                $resource = $disk->getResource("disk:/" . $resourcePath);
                $resource->delete();
            } catch (Arhitector\Yandex\Client\Exception\NotFoundException $e) {
                $this->errorCollection[] = new Error("resource not found.");
                return null;
            }
        } catch (Arhitector\Yandex\Client\Exception\UnauthorizedException $e) {
            $this->errorCollection[] = new Error("unable to authorise / invalid token.");
            return null;
        }

        return $resourcePath;
    }

    public function createResourceAction($dirPath, $resourceName, $resourceType)
    {
        if (!isset($resourceName) || empty($resourceName)) {
            $this->errorCollection[] = new Error("no resource name was given");
            return null;
        }
        if (str_contains($resourceName, "/")) {
            $this->errorCollection[] = new Error("invalid resourecName: can't contain slash '/'");
            return null;
        }

        try {
            $disk = new Disk($this->getDiskOAuth());
        } catch (Arhitector\Yandex\Client\Exception\UnauthorizedException $e) {
            $this->errorCollection[] = new Error("unable to authorise / invalid token.");
            return null;
        }

        $dirPath = empty($dirPath) ? "" : $dirPath . "/";
        $resource = $disk->getResource("disk:/" . $dirPath . $resourceName);
        if ($resource->has()) {
            $this->errorCollection[] = new Error("resource with that name already exists.");
            return null;
        }
        
        try {
            if ($resourceType == "file") {
                $stream = fopen("php://temp", "r+");
                $resource->upload($stream);
                fclose($stream);
            } elseif ($resourceType == "dir") {
                $resource->create();
            } else {
                $this->errorCollection[] = new Error("invalid resourceType.");
                return null;
            }
        } catch (Exception $e) {
            $this->errorCollection[] = new Error("resource was not created");
            return null;
        }

        return $dirPath . $resourceName;
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