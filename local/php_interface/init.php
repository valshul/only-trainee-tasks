<?

\Bitrix\Main\Loader::includeModule('dev.site');
use Dev\Site\Handlers\Iblock;

AddEventHandler(
	"iblock",
	"OnAfterIBlockElementAdd",
	[Iblock::class, "AddLog"]
);

AddEventHandler(
	"iblock",
	"OnAfterIBlockElementUpdate",
	[Iblock::class, "AddLog"]
);