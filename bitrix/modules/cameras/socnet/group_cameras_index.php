<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();?>
<?

$pageId = "group_cameras";
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork_group/templates/.default/util_group_menu.php");
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork_group/templates/.default/util_group_profile.php");


$APPLICATION->IncludeComponent(
    "bitrix:cameras.socialnetwork_group",
    ".default",
    Array(
        'SOCNET_GROUP_ID' => $arResult['VARIABLES']['group_id']
    ),
    $component,
    array()
);
?>


