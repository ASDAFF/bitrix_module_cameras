<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();?>
<?
$pageId = "group_cameras";
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork_group/templates/.default/util_group_menu.php");
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork_group/templates/.default/util_group_profile.php");
?>
<?$APPLICATION->IncludeComponent(
	'bitrix:cameras.menu',
	'',
	Array(
		'IBLOCK_TYPE' => COption::GetOptionString('cameras', 'socnet_iblock_type_id'),
		'IBLOCK_ID' => COption::GetOptionString('cameras', 'socnet_iblock_id'),
		'ELEMENT_NAME' => isset($arResult['VARIABLES']['title']) ? $arResult['VARIABLES']['title'] : $arResult['VARIABLES']['cameras_name'],
		'MENU_TYPE' => 'page',
		'PATH_TO_POST' => $arResult['PATH_TO_GROUP_CAMERAS_POST'],
		'PATH_TO_POST_EDIT' => $arResult['PATH_TO_GROUP_CAMERAS_POST_EDIT'],
		'PATH_TO_CATEGORIES' => $arResult['PATH_TO_GROUP_CAMERAS_CATEGORIES'],
		'PATH_TO_DISCUSSION' => $arResult['PATH_TO_GROUP_CAMERAS_POST_DISCUSSION'],
		'PATH_TO_HISTORY' => $arResult['PATH_TO_GROUP_CAMERAS_POST_HISTORY'],
		'PATH_TO_HISTORY_DIFF' => $arResult['PATH_TO_GROUP_CAMERAS_POST_HISTORY_DIFF'],
		'PAGE_VAR' => 'title',
		'OPER_VAR' => 'oper',
		'USE_REVIEW' => COption::GetOptionString('cameras', 'socnet_use_review'),
		'SOCNET_GROUP_ID' => $arResult['VARIABLES']['group_id']
	),
	$component
);?>
<?$APPLICATION->IncludeComponent(
	'bitrix:cameras.edit',
	'',
	Array(
		'PATH_TO_POST' => $arResult['PATH_TO_GROUP_CAMERAS_POST'],
		'PATH_TO_POST_EDIT' => $arResult['PATH_TO_GROUP_CAMERAS_POST_EDIT'],
		'PATH_TO_CATEGORIES' => $arResult['PATH_TO_GROUP_CAMERAS_CATEGORIES'],
		'PATH_TO_DISCUSSION' => $arResult['PATH_TO_GROUP_CAMERAS_POST_DISCUSSION'],
		'PATH_TO_HISTORY' => $arResult['PATH_TO_GROUP_CAMERAS_POST_HISTORY'],
		'PATH_TO_HISTORY_DIFF' => $arResult['PATH_TO_GROUP_CAMERAS_POST_HISTORY_DIFF'],
		'PAGE_VAR' => 'title',
		'OPER_VAR' => 'oper',
		'IBLOCK_TYPE' => COption::GetOptionString('cameras', 'socnet_iblock_type_id'),
		'IBLOCK_ID' => COption::GetOptionString('cameras', 'socnet_iblock_id'),
		'ELEMENT_NAME' => isset($arResult['VARIABLES']['title']) ? $arResult['VARIABLES']['title'] : $arResult['VARIABLES']['cameras_name'],
		'SOCNET_GROUP_ID' => $arResult['VARIABLES']['group_id'],
		'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'],
		'HIDE_OWNER_IN_TITLE' => $arParams['HIDE_OWNER_IN_TITLE']
	),
	$component
);?>
