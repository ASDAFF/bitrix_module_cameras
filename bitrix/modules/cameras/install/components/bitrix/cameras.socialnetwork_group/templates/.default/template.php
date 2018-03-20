<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?

//region TITLE
$sTitle = $sTitleShort = GetMessage("CAMERAS_TITLE_GROUP_CAMERAS");
$APPLICATION->SetPageProperty("title", $sTitle);
$APPLICATION->SetTitle($sTitleShort);
//endregion TITLE
/*
if($arResult["SHOW_EDIT_COMPONENT"] == 1) {
    $ID = $APPLICATION->IncludeComponent(
        'bitrix:cameras.socialnetwork_group.edit',
        '',
        Array(
            'IBLOCK_TYPE' => $arResult["IBLOCK_TYPE"],
            'CAMERAS_ALL_IBLOCK_ID' => $arResult["CAMERAS_ALL_IBLOCK_ID"],
            'CAMERAS_BY_GROUP_IBLOCK_ID' => $arResult["CAMERAS_BY_GROUP_IBLOCK_ID"],
            'SOCNET_GROUP_ID' => $arResult["SOCNET_GROUP_ID"],
            'IBLOCK_ID' => $arResult["IBLOCK_ID"],
        ),
        ""
    );
}
*/
?>

<?$ID = $APPLICATION->IncludeComponent(
    'bitrix:cameras.socialnetwork_group.list',
    '',
    Array(
        'IBLOCK_TYPE' => $arResult["IBLOCK_TYPE"],
        'CAMERAS_ALL_IBLOCK_ID' => $arResult["CAMERAS_ALL_IBLOCK_ID"],
        'CAMERAS_BY_GROUP_IBLOCK_ID' => arResult["CAMERAS_BY_GROUP_IBLOCK_ID"],
        'SOCNET_GROUP_ID' => $arResult["SOCNET_GROUP_ID"],
        'IBLOCK_ID' => $arResult["IBLOCK_ID"],
    ),
    ""
);?>