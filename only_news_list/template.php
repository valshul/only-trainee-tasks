<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);

$this->addExternalCss($this->GetFolder()."/css/common.css");
?>

<div id="barba-wrapper">
	<div class="article-list">
		<?foreach($arResult["ITEMS"] as $arItem):?>
			<?
			$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
			$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
			?>
			
			<?if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])):?>
				<a class="article-item article-list__item" id="<?=$this->GetEditAreaId($arItem['ID']);?>" href="<?echo $arItem["DETAIL_PAGE_URL"]?>" data-anim="anim-3">
					<div class="article-item__background">
						<?if($arParams["DISPLAY_PICTURE"]!="N" && $arItem["PREVIEW_PICTURE"]):?>
							<img
								src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>"
								data-src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>"
								width="<?=$arItem["PREVIEW_PICTURE"]["WIDTH"]?>"
								height="<?=$arItem["PREVIEW_PICTURE"]["HEIGHT"]?>"
								alt="<?=$arItem["PREVIEW_PICTURE"]["ALT"]?>"
								/>
						<?else:?>
							<img src="<?=$this->GetFolder()."/images/article-bg.png"?>" alt=""/>
						<?endif;?>
					</div>

					<div class="article-item__wrapper">
						<?if($arParams["DISPLAY_NAME"]!="N" && $arItem["NAME"]):?>
							<div class="article-item__title"><?echo $arItem["NAME"]?></div>
						<?endif;?>
						<?if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && $arItem["PREVIEW_TEXT"]):?>
							<div class="article-item__content">
								<?echo $arItem["PREVIEW_TEXT"];?>
							</div>
						<?endif;?>
					</div>
				</a>
			<?endif;?>
		<?endforeach;?>
	</div>
</div>
