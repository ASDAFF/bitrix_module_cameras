<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arGroups = $USER->GetUserGroupArray();

$currentUserGroups = array();
foreach($arGroups as $id){
    $currentUserGroups[$id] = 1;
}

$rsGroups = CGroup::GetList ($by = "c_sort", $order = "asc", Array ());
while($arGroups = $rsGroups->Fetch())
{
    $arUsersGroups[] = $arGroups;
}

$cameras_show_all = 0;
if(isset($currentUserGroups[1])){
    $cameras_show_all = 1;
}

foreach($arUsersGroups as $group){
    if($group["STRING_ID"] == "EXTRANET_ADMIN" && isset($currentUserGroups[$group["ID"]])){
        $cameras_show_all = 1;
    }
}
$arResult["SHOW_EDIT_COMPONENT"] = $cameras_show_all;
$arResult["IBLOCK_TYPE"] = COption::GetOptionString('cameras', 'socnet_iblock_type_id');
$arResult["CAMERAS_ALL_IBLOCK_ID"] = COption::GetOptionString('cameras', 'socnet_iblock_id_all');
$arResult["CAMERAS_BY_GROUP_IBLOCK_ID"] = COption::GetOptionString('cameras', 'socnet_iblock_id_by_group');
$arResult["IBLOCK_ID"] = COption::GetOptionString('cameras', 'socnet_iblock_id_by_group');
$arResult["SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];
/*echo '<br/> $arResult '. __LINE__.'* ' .  __FILE__ . ' <pre>';
print_r($arResult);
echo '</pre>';*/
$this->IncludeComponentTemplate();
?>
