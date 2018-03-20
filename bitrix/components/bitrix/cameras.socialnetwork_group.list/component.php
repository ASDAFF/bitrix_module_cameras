<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();?>
<?

/*echo '<br/> $arParams '. __LINE__.'* ' .  __FILE__ . ' <pre>';
print_r($arParams);
echo '</pre>';*/

if (CCamerasSocnet::isEnabledSocnet() && !empty($arParams['SOCNET_GROUP_ID']))
{

    if(!CModule::IncludeModule('socialnetwork'))
    {
    	ShowError(GetMessage('SOCNET_MODULE_NOT_INSTALLED'));
        return;
    }
}
if (CCamerasSocnet::isEnabledSocnet() && !empty($arParams['SOCNET_GROUP_ID'])) {
    $iblock_id_tmp = CCamerasSocnet::RecalcIBlockID($arParams["SOCNET_GROUP_ID"]);
    if ($iblock_id_tmp)
        $arParams['IBLOCK_ID'] = $iblock_id_tmp;

    if (!CCamerasSocnet::Init($arParams['SOCNET_GROUP_ID'], $arParams['IBLOCK_ID'])) {
        ShowError(GetMessage('WIKI_SOCNET_INITIALIZING_FAILED'));
        return;
    }
}


$arSelect = Array("ID", "IBLOCK_ID", "NAME", "IBLOCK_SECTION_ID", "*", "PROPERTY_*");
$arFilter = Array(
    "IBLOCK_ID"=> $arParams["CAMERAS_ALL_IBLOCK_ID"],
    "SHOW_HISTORY" => "Y",
    "ACTIVE" => "Y",
    "PROPERTY_USER_GROUPS" => $iblockSectionId
);

$db_list = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);

while($ob = $db_list->GetNextElement()){

    /*echo '<br/> $ar_result '. __LINE__.'* ' .  __FILE__ . ' <pre>';
    print_r($ar_result);
    echo '</pre>';*/

    $arFields = $ob->GetFields();
    $arProps = $ob->GetProperties();
    $arFields["PROPERTIES"] = $arProps;

    /*echo '<br/> $arFields '. __LINE__.'* ' .  __FILE__ . ' <pre>';
    print_r($arFields);
    echo '</pre>';
    echo '<br/> $arProps '. __LINE__.'* ' .  __FILE__ . ' <pre>';
    print_r($arProps);
    echo '</pre>';*/

    $arResult["CAMERAS_ALL_ITEMS_NOT_SORTED"][$arFields["ID"]] = $arFields;
}
$currentSocnetCamerasId = CCamerasSocnet::$iCatId;
foreach($arResult["CAMERAS_ALL_ITEMS_NOT_SORTED"] as $id => $val){
    $arrCamerasAll = array_flip($val["PROPERTIES"]["USER_GROUPS"]["VALUE"]);
    if(isset($arrCamerasAll[$currentSocnetCamerasId])){
        $arResult["CAMERAS_ALL_ITEMS"][$id] = $val;
    }
}
/*echo '<br/> $arResult["CAMERAS_ALL_ITEMS"] '. __LINE__.'* ' .  __FILE__ . ' <pre>';
print_r($arResult["CAMERAS_ALL_ITEMS"]);
echo '</pre>';*/

/*foreach($arResult["CAMERAS_ALL_ITEMS"] as $itemId => $item){
	if(!isset($arResult["AVAILABLE_CAMERAS"][$itemId])){
		unset($arResult["CAMERAS_ALL_ITEMS"][$itemId]);
	}
}*/

/*echo '<br/> $arResult["CAMERAS_ALL_ITEMS"] '. __LINE__.'* ' .  __FILE__ . ' <pre>';
print_r($arResult["CAMERAS_ALL_ITEMS"]);
echo '</pre>';*/

$this->IncludeComponentTemplate();
?>




















