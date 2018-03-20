<?
IncludeModuleLangFile(__FILE__);

class CRatingsComponentsCameras
{
	function OnAddRatingVote($id, $arParams)
	{
		if ($arParams['ENTITY_TYPE_ID'] == 'IBLOCK_ELEMENT')
		{
			global $CACHE_MANAGER;
			$CACHE_MANAGER->ClearByTag('cameras_'.intval($arParams['ENTITY_ID']));

			return true;
		}
		return false;
	}

	function OnCancelRatingVote($id, $arParams)
	{
		return CRatingsComponentsCameras::OnAddRatingVote($id, $arParams);
	}
	
	function BeforeIndex($arParams)
	{
		if (
			$arParams['PARAM1'] == 'cameras' 
			&& intval($arParams['PARAM2']) > 0 
			&& intval($arParams['ITEM_ID']) > 0
		)
		{
			$arParams['ENTITY_TYPE_ID'] = 'IBLOCK_ELEMENT';
			$arParams['ENTITY_ID'] = intval($arParams['ITEM_ID']);
			return $arParams;
		}
	}
}
?>