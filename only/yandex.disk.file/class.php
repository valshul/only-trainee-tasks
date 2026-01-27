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

class YandexDiskFileComponent extends CBitrixComponent implements Controllerable, Errorable
{
    private $DiskOAuthUserFieldName = "UF_YANDEX_DISK_OAUTH";
    private $supportedTextFormats = ["txt", "html"];
    private $supportedImageFormats = ["png", "jpeg", "jpg"];

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
        $arParams["FILE"] = urldecode($arParams["FILE"]);

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
        $supportedFormats = array_merge(
            $this->supportedTextFormats,
            $this->supportedImageFormats
        );

        $fileContents = [];

        try {
            $disk = new Disk($this->getDiskOAuth());
            try {
                $resource = $disk->getResource("disk:/" . $arParams["FILE"]);
                if ($resource->isFile()) {
                    $fileParams = $resource->toArray(["path", "name", "sizes"]);
                    $fileFormat = end(explode(".", $fileParams["name"]));
                    
                    if ($fileFormat != "" && in_array($fileFormat, $supportedFormats)) {
                        $fileContents["PARAMS"] = $fileParams;

                        if (in_array($fileFormat, $this->supportedTextFormats)) {
                            $fileContents["TEXT"] = $this->getText($resource);
                            
                        } elseif (in_array($fileFormat, $this->supportedImageFormats)) {
                            $fileContents["IMAGE"] = $this->getImage($resource, $fileFormat);
                        }

                        $arResult["CONTENTS"] = $fileContents;
                    } else {
                        ShowError("file format is not supported.");
                    }
                    
                    $previousDir = explode("/", $arParams["FILE"], -1);
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
                } else {
                    ShowError("file not found.");
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

    private function getText($resource): string
    {
        $stream = fopen("php://temp", "r+");
        $resource->download($stream);
        rewind($stream);

        $text = "";
        while (!feof($stream)) {
            $text .= fgets($stream);
        }

        fclose($stream);
        return htmlspecialcharsbx($text);
    }

    private function getImage($resource, $imageFormat): string
    {
        $stream = fopen("php://temp", "r+");
        $resource->download($stream);
        rewind($stream);

        $text = "";
        while (!feof($stream)) {
            $text .= fgets($stream);
        }
        $image = "data:image/" . $imageFormat . ";base64," . base64_encode($text);
        
        fclose($stream);
        return $image;
    }
    
    public function configureActions(): array
    {
        return [];
    }

    public function updateResourceAction($resourcePath, $text)
    {
        if (!isset($resourcePath) || empty($resourcePath)) {
            $this->errorCollection[] = new Error("no resource path was given");
            return null;
        }

        try {
            $disk = new Disk($this->getDiskOAuth());
            try {
                $resource = $disk->getResource("disk:/" . $resourcePath);
                $stream = fopen("php://temp", "r+");

                fwrite($stream, $text);
                rewind($stream);
                $resource->upload($stream, true);

                fclose($stream);
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