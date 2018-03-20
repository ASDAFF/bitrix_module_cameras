<?php

use \Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class CCameras
{
	/**
	 *
	 *
	 * @var CIBlockElement
	 */
	var $cIB_E = null;
	const PAGE_UPDATED_CACHE_ID = "CAMERAS_PAGE_UPDATED_";
	const GET_BY_NAME_CACHE_ID = "CAMERAS_BY_NAME_CACHE_ID_";
	const CCAMERAS_CACHE_TTL = 36000000;

	/** @var ErrorCollection */
	protected $errorCollection;

	function __construct()
	{
		$this->cIB_E = new CIBlockElement();
		$this->errorCollection = new ErrorCollection();
	}

	function Add($arFields)
	{
		$arFields['XML_ID'] = $arFields['NAME'];

		$arCats = array();
		$CCamerasParser = new CCamerasParser();
		$arFields['DETAIL_TEXT'] = $CCamerasParser->parseBeforeSave($arFields['DETAIL_TEXT'], $arCats, $arFields['NAME_TEMPLATE']);
		if (CCamerasSocnet::IsSocNet())
			$arFields['IBLOCK_SECTION_ID'] = CCamerasSocnet::$iCatId;

		//add item
		$ID = $this->cIB_E->Add($arFields);

		$this->CleanCache($ID, $arFields['NAME'],$arFields['IBLOCK_ID']);

		//serve category / bindings
		$this->UpdateCategory($ID, $arFields['IBLOCK_ID'], $arCats);

		//$this->UpdateHistory($ID, $arFields['IBLOCK_ID']);

		return $ID;
	}

	function Update($ID, $arFields)
	{
		$arCats = array();
		$CCamerasParser = new CCamerasParser();
		$arFields['DETAIL_TEXT'] = $CCamerasParser->parseBeforeSave($arFields['DETAIL_TEXT'], $arCats, $arFields['NAME_TEMPLATE']);

		$this->CleanCache($ID, $arFields['NAME'], $arFields['IBLOCK_ID']);
		//save item
		$this->cIB_E->Update($ID, $arFields);

		//serve category / bindings
		$arCats = str_replace("/", "-", $arCats);	//http://jabber.bx/view.php?id=28447
		$this->UpdateCategory($ID, $arFields['IBLOCK_ID'], $arCats);

		$modifyComment = isset($arFields["MODIFY_COMMENT"]) ? $arFields["MODIFY_COMMENT"] : "";

		$this->UpdateHistory($ID, $arFields['IBLOCK_ID'], $modifyComment);

		return true;
	}

	function Recover($HISTORY_ID, $ID, $IBLOCK_ID)
	{
		$this->CleanCacheById($ID, $IBLOCK_ID);

		$rIBlock = CIBlock::getList(Array(), array('ID' => $IBLOCK_ID, 'CHECK_PERMISSIONS' => 'N'));
		$arIBlock = $rIBlock->GetNext();
		if ($arIBlock['BIZPROC'] == 'Y' && CModule::IncludeModule('bizproc'))
		{
			$arErrorsTmp = array();
			$arHistoryResult = CBPDocument::GetDocumentFromHistory($HISTORY_ID, $arErrorsTmp);
			$modifyComment = GetMessage('CAMERAS_RECOVER_COMMENT')." ".$arHistoryResult["MODIFIED"];
			if (CBPHistoryService::RecoverDocumentFromHistory($HISTORY_ID))
			{
				if ($this->UpdateHistory($ID, $IBLOCK_ID, $modifyComment))
					return true;
				else
					return false;
			}
			else
				return false;
		}
		else
			return false;
	}

	function UpdateHistory($ID, $IBLOCK_ID, $modifyComment=false)
	{
		global $USER;

		$rIBlock = CIBlock::getList(Array(), array('ID' => $IBLOCK_ID, 'CHECK_PERMISSIONS' => 'N'));
		$arIBlock = $rIBlock->GetNext();

		// add changes history
		if ($arIBlock['BIZPROC'] == 'Y' && CModule::IncludeModule('bizproc'))
		{
			$cRuntime = CBPRuntime::GetRuntime();
			$cRuntime->StartRuntime();
			$documentService = $cRuntime->GetService('DocumentService');

			$historyIndex = CBPHistoryService::Add(
				array(
					'DOCUMENT_ID' => array('iblock', 'CCamerasDocument', $ID),
					'NAME' => 'New',
					'DOCUMENT' => null,
					'USER_ID' => $USER->GetID()
				)
			);

			$arDocument = $documentService->GetDocumentForHistory(array('iblock', 'CCamerasDocument', $ID), $historyIndex);
			$arDocument["MODIFY_COMMENT"] = $modifyComment ? $modifyComment : '';

			if (is_array($arDocument))
			{
				CBPHistoryService::Update(
					$historyIndex,
					array(
						'NAME' => $arDocument['NAME'],
						'DOCUMENT' => $arDocument,
					)
				);
			}
			return true;
		}
		return false;
	}

	function UpdateCategory($ID, $IBLOCK_ID, $arCats)
	{

		$this->CleanCacheById($ID, $IBLOCK_ID);

		$arFilter = array(
			'IBLOCK_ID' => $IBLOCK_ID,
			'CHECK_PERMISSIONS' => 'N'
		);
		$arElement = self::GetElementById($ID, $arFilter);
		$bCategoryPage = false;
		$sCatName = '';
		$arCatsID = array();
		if (CCamerasUtils::IsCategoryPage($arElement['~NAME'], $sCatName))
			$bCategoryPage = true;

		if ($bCategoryPage)
		{
			// get current category
			$arFilter =  array('NAME' => $sCatName, 'IBLOCK_ID' => $IBLOCK_ID, 'CHECK_PERMISSIONS' => 'N');
			if (CCamerasSocnet::IsSocNet())
			{
				$arFilter['>LEFT_BORDER'] = CCamerasSocnet::$iCatLeftBorder;
				$arFilter['<RIGHT_BORDER'] = CCamerasSocnet::$iCatRightBorder;
			}
			$rsCurCats = CIBlockSection::GetList(array(), $arFilter);
			$arCurCat = $rsCurCats->GetNext();

			if (empty($arCurCat))
			{
				$CIB_S = new CIBlockSection();
				$_arFields = array();
				$_arFields['IBLOCK_ID'] = $IBLOCK_ID;
				$_arFields['ACTIVE'] = 'Y';
				$_arFields['NAME'] = $sCatName;
				$_arFields['XML_ID'] = $sCatName;
				if (CCamerasSocnet::IsSocNet())
					$_arFields['IBLOCK_SECTION_ID'] = CCamerasSocnet::$iCatId;
				$iCurCatID = $CIB_S->Add($_arFields);
				if ($iCurCatID != false)
					$arCatsID[] = $iCurCatID;
			}
			else
			{
				$iCurCatID = $arCurCat['ID'];
				$arCatsID[] = $arCurCat['ID'];
			}

			// Page bind only to this category
			CIBlockElement::SetElementSection($ID, $arCatsID);

			$CIB_S = new CIBlockSection();
			if (!empty($arCats))
			{
				// Nova create a category if it still has no
				$arFilter =  array('NAME' => $arCats[0], 'IBLOCK_ID' => $IBLOCK_ID, 'CHECK_PERMISSIONS' => 'N');
				if (CCamerasSocnet::IsSocNet())
				{
					$arFilter['>LEFT_BORDER'] = CCamerasSocnet::$iCatLeftBorder;
					$arFilter['<RIGHT_BORDER'] = CCamerasSocnet::$iCatRightBorder;
				}
				$rsCats = CIBlockSection::GetList(array(), $arFilter);
				$arCat = $rsCats->GetNext();

				if (empty($arCat))
				{
					$_arFields = array();
					$_arFields['IBLOCK_ID'] = $IBLOCK_ID;
					$_arFields['ACTIVE'] = 'Y';
					$_arFields['NAME'] = CCamerasUtils::htmlspecialcharsback($arCats[0]);
					$_arFields['XML_ID'] = CCamerasUtils::htmlspecialcharsback($arCats[0]);
					$_arFields['CHECK_PERMISSIONS'] = 'N';
					if (CCamerasSocnet::IsSocNet())
						$_arFields['IBLOCK_SECTION_ID'] = CCamerasSocnet::$iCatId;

					$iCatID = $CIB_S->Add($_arFields);
				}
				else
					$iCatID = $arCat['ID'];

				$_arFields = array();
				$_arFields['IBLOCK_ID'] = $IBLOCK_ID;
				$_arFields['ACTIVE'] = 'Y';
				$_arFields['IBLOCK_SECTION_ID'] = $iCatID;
				// current category doing this subcategory
				$CIB_S->Update($iCurCatID, $_arFields);
			}
			else
			{
				$_arFields = array();
				$_arFields['IBLOCK_ID'] = $IBLOCK_ID;
				$_arFields['ACTIVE'] = 'Y';
				$_arFields['IBLOCK_SECTION_ID'] = 0;
				if (CCamerasSocnet::IsSocNet())
					$_arFields['IBLOCK_SECTION_ID'] = CCamerasSocnet::$iCatId;
				// bind to the root category
				$CIB_S->Update($iCurCatID, $_arFields);
			}
		}
		else  //not category
		{
			$arExistsCatsId = array();
			$arDelCatId = array();
			$rsSect = CIBlockElement::GetElementGroups($ID, false);
			//$arResult['SECTIONS'] = array(); //erase candidat
			while($arSect = $rsSect->GetNext())
				$arExistsCatsId[] = $arSect['ID'];

			if (!empty($arCats))
			{
				$arFilter =  array('NAME' => $arCats, 'IBLOCK_ID' => $IBLOCK_ID, 'CHECK_PERMISSIONS' => 'N');
				if (CCamerasSocnet::IsSocNet())
				{
					$arFilter['>LEFT_BORDER'] = CCamerasSocnet::$iCatLeftBorder;
					$arFilter['<RIGHT_BORDER'] = CCamerasSocnet::$iCatRightBorder;
				}
				$rsCats = CIBlockSection::GetList(array(), $arFilter);
				while($arCat = $rsCats->GetNext())
				{
					$arExiststInBlockCats[] = $arCat['~NAME'];
					$arCatsID[] = $arCat['ID'];
				}

				$CIB_S = new CIBlockSection();
				foreach ($arCats as $sCatName)
				{
					if (!in_array($sCatName, $arExiststInBlockCats))
					{
						$_arFields = array();
						$_arFields['IBLOCK_ID'] = $IBLOCK_ID;
						$_arFields['ACTIVE'] = 'Y';
						$_arFields['NAME'] = CCamerasUtils::htmlspecialcharsback($sCatName, false);
						$_arFields['XML_ID'] = CCamerasUtils::htmlspecialcharsback($sCatName, false);
						$_arFields['CHECK_PERMISSIONS'] = 'N';
						if (CCamerasSocnet::IsSocNet())
							$_arFields['IBLOCK_SECTION_ID'] = CCamerasSocnet::$iCatId;
						$iCatID = $CIB_S->Add($_arFields);
						if ($iCatID != false)
							$arCatsID[] = $iCatID;
					}
				}

				//bind to the item
				if (!empty($arCatsID))
				{
					//if (CCamerasSocnet::IsSocNet())
					//	$arCatsID[] = CCamerasSocnet::$iCatId;
					CIBlockElement::SetElementSection($ID, $arCatsID);
				}
			}
			else
			{
				$arCatsID = array();
				if (CCamerasSocnet::IsSocNet())
					$arCatsID = CCamerasSocnet::$iCatId;
				CIBlockElement::SetElementSection($ID, $arCatsID);
			}

			if (is_array($arCatsID))
				$arDelCatId = array_diff($arExistsCatsId, $arCatsID);
			if (!empty($arDelCatId))
			{
				foreach ($arDelCatId as $_iCatId)
				{
					$obRes = CIBlockSection::GetList(array(), array('ID' => $_iCatId, 'IBLOCK_ID' => $IBLOCK_ID), true);
					$arCatProp = $obRes->Fetch();
					if ($arCatProp['ELEMENT_CNT'] == 0)
						CIBlockSection::Delete($_iCatId);
				}
			}
		}
	}

	//TODO: Delete (check) all comments
	function Delete($ID, $IBLOCK_ID)
	{
		$rIBlock = CIBlock::getList(Array(), array('ID' => $IBLOCK_ID, 'CHECK_PERMISSIONS' => 'N'));
		$arIBlock = $rIBlock->GetNext();

		// erase the history of changes
		if ($arIBlock['BIZPROC'] == 'Y' && CModule::IncludeModule('bizproc'))
		{
			$historyService = new CBPHistoryService();
			$historyService->DeleteHistoryByDocument(array('iblock', 'CCamerasDocument', $ID));
		}

		$this->CleanCacheById($ID,$IBLOCK_ID);

		// delete item
		$bResult = $this->cIB_E->Delete($ID);

		return $bResult;
	}

	function AddImage($ID, $IBLOCK_ID, $arImage)
	{
		$arProperties = array();
		$arCurImages = array();
		$arCurImagesNew = array();
		$arAddImage = array();

		$rsProperties = CIBlockElement::GetProperty($IBLOCK_ID, $ID, 'value_id', 'asc', array('ACTIVE' => 'Y', 'CODE' => 'IMAGES'));
		while($arProperty = $rsProperties->Fetch())
		{
			if($arProperty['CODE'] == 'IMAGES')
			{
				$arProperties['IMAGES'] = $arProperty;
				$arCurImages[$arProperty['VALUE']] = '';
			}
		}

		$rsFile = CFile::GetList(array(), array('@ID' => implode(',', array_keys($arCurImages))));
		while($arFile = $rsFile->Fetch())
			$arCurImages[$arFile['ID']] = $arFile['ORIGINAL_NAME'];

		if(array_search($arImage['name'], $arCurImages) !== false)
		{
			$this->errorCollection->add(array(new Error(Loc::getMessage('CAMERAS_ERROR_IMAGE_ATTACHED'))));
			return false;
		}

		$obProperty = new CIBlockProperty();
		$res = true;
		if(!array_key_exists('IMAGES', $arProperties))
		{
			$res = $obProperty->Add(array(
				'IBLOCK_ID' => $IBLOCK_ID,
				'ACTIVE' => 'Y',
				'PROPERTY_TYPE' => 'F',
				'MULTIPLE' => 'Y',
				'NAME' => 'Images',
				'CODE' => 'IMAGES'
			));
		}

		$arFields = array();

		CFile::ResizeImage($arImage, array(
			'width' => COption::GetOptionString('cameras', 'image_max_width', 600),
			'height' => COption::GetOptionString('cameras', 'image_max_height', 600)
		));

		$arFields['PROPERTY_VALUES'] = array('IMAGES' => $arImage);
		$arFields['BLOCK_ID'] = $IBLOCK_ID;
		$arFields['ELEMENT_ID'] = $ID;

		$this->cIB_E->Update($ID, $arFields);

		$rsProperties = CIBlockElement::GetProperty($IBLOCK_ID, $ID, 'value_id', 'asc', array('ACTIVE' => 'Y', 'CODE' => 'IMAGES', 'EMPTY' => 'N'));
		while($arProperty = $rsProperties->Fetch())
		{
			if($arProperty['CODE'] == 'IMAGES')
				$arCurImagesNew[$arProperty['VALUE']] = '';
		}

		$arAddImage = array_diff(array_keys($arCurImagesNew), array_keys($arCurImages));
		list(, $imgId) = each($arAddImage);
		return $imgId;
	}

	function DeleteImage($IMAGE_ID, $ID, $IBLOCK_ID)
	{
		$rsProperties = CIBlockElement::GetProperty($IBLOCK_ID, $ID, 'value_id', 'asc', array('ACTIVE' => 'Y', 'CODE' => 'IMAGES'));
		$_iPropertyId = 0;
		while($arProperty = $rsProperties->Fetch())
		{
			if($arProperty['CODE'] == 'IMAGES' && $arProperty['VALUE'] == $IMAGE_ID)
			{
				$_iPropertyId = $arProperty['PROPERTY_VALUE_ID'];
				break;
			}
		}

		if (!empty($_iPropertyId))
		{
			$arPropertyValues = array();
			$arPropertyValues[$_iPropertyId] = array('VALUE' => array('del' => 'Y'), 'DESCRIPTION' => '');
			$this->cIB_E->SetPropertyValues($ID, $IBLOCK_ID, $arPropertyValues, 'IMAGES');
		}
	}

	function Rename($ID, $arFields, $bUpdateSearch=true)
	{
		$arFilter = array('IBLOCK_ID' => $arFields['IBLOCK_ID'], 'CHECK_PERMISSIONS' => 'N');

		// checking for the existence of a page with this name
		$arElement = self::GetElementByName($arFields['NAME'], $arFilter);
		$arOldElement = self::GetElementById($ID, $arFilter);

		$bRename = false;
		if ($arOldElement != false)
		{
			if ($arElement == false)
				$bRename = true;
			else if($arElement['ID'] == $ID)
				$bRename = true;
		}

		if ($bRename)
		{
			$this->CleanCacheById($ID, $arFields['IBLOCK_ID']);

			$arFields['XML_ID'] = $arFields['NAME'];
			$this->cIB_E->Update($ID, $arFields, false, $bUpdateSearch);

			$sCatName = '';
			if(CCamerasUtils::IsCategoryPage($arFields['NAME'], $sCatName))
			{
				$sCatNameOld = '';
				if (CCamerasUtils::IsCategoryPage($arOldElement['NAME'], $sCatNameOld))
				{
					// rename a category
					$arFilter =  array('NAME' => $sCatNameOld, 'IBLOCK_ID' => $arFields['IBLOCK_ID'], 'CHECK_PERMISSIONS' => 'N');
					if (CCamerasSocnet::IsSocNet())
					{
						$arFilter['>LEFT_BORDER'] = CCamerasSocnet::$iCatLeftBorder;
						$arFilter['<RIGHT_BORDER'] = CCamerasSocnet::$iCatRightBorder;
					}
					$rsCats = CIBlockSection::GetList(array(), $arFilter);
					$arCat = $rsCats->GetNext();

					if ($arCat != false)
					{
						$CIB_S = new CIBlockSection();

						$_arFields = array();
						$_arFields['IBLOCK_ID'] = $arFields['IBLOCK_ID'];
						$_arFields['NAME'] = $sCatName;
						$_arFields['XML_ID'] = $sCatName;
						$_arFields['CHECK_PERMISSIONS'] = 'N';

						$CIB_S->Update($arCat['ID'], $_arFields);
					}
				}
			}

			$arOldElement['NAME'] = CCamerasUtils::htmlspecialcharsback($arOldElement['NAME']);

			if (self::GetDefaultPage($arFields['IBLOCK_ID']) == false
				|| (self::GetDefaultPage($arFields['IBLOCK_ID']) == $arOldElement['NAME']
					&& $arOldElement['NAME'] != $arFields['NAME']))
				self::SetDefaultPage($arFields['IBLOCK_ID'], $arFields['NAME']);

			return true;
		}

		return false;
	}


	/**
	 * Renames inner links, and categories on cameras pages
	 * alternatively you must do that manualy, after page, or catgory was renamed.
	 * @param int $iBlockId (mandatory)- id of iblock witch contain page, or category.
	 * @param str $oldName (mandatory)- old page or category name.
	 * @param str $newName (mandatory)- new page or category name.
	 * @param int $iBlockSectId (optional) - id of iBlock section witch contain page, or category.
	 *			if cameras used with socnet groups, this param must be setted, otherwise all pages of all soc. groups
	 *			will be changed.
	 * @return int the amount of changed pages.
	 */
	function RenameLinkOnPages($iBlockId, $oldName, $newName, $iBlockSectId = false)
	{
		if(!$iBlockId || !$oldName || !$newName)
			return false;

		$arFilter["IBLOCK_ID"] = $iBlockId;
		$arFilter["CHECK_PERMISSIONS"]="N";

		if($iBlockSectId)
		{
			$arFilter["SECTION_ID"] = $iBlockSectId;
			$arFilter["INCLUDE_SUBSECTIONS"] = "Y";
		}

		$count = 0;
		$sCatName = '';
		$isCategory = CCamerasUtils::IsCategoryPage($oldName , $sCatName);

		$catSearch = "[[".GetMessage('CATEGORY_NAME').":".$sCatName."]]";

		$arPatterns = array(
			//link and link_name are equal
			array(
			"search" => "[[".$oldName."|".$oldName."]]",
			"pattern" => "/\[\[(".preg_quote($oldName).")\|(".preg_quote($oldName).")\]\]/isU".BX_UTF_PCRE_MODIFIER,
			"replacement" => "[[".$newName."|".$newName."]]"
			),

			//link and link_name are different
			array(
			"search" => "[[".$oldName."|",
			"pattern" => "/\[\[(".preg_quote($oldName).")\|(.*)\]\]/isU".BX_UTF_PCRE_MODIFIER,
			"replacement" => "[[".$newName."|$2]]"
			),

			//exist only link
			array(
			"search" => "[[".$oldName."]]",
			"pattern" => "/\[\[".preg_quote($oldName)."\]\]/isU".BX_UTF_PCRE_MODIFIER,
			"replacement" => "[[".$newName."]]"
			)
		);



		$dbRes = CIBlockElement::GetList(array(), $arFilter, false, false, array("ID", "NAME", "DETAIL_TEXT"));

		while($arElement = $dbRes->GetNext())
		{
			$bChanged = false;

			$newText = $arElement["~DETAIL_TEXT"];

			foreach ($arPatterns as $arPattern)
			{
				if(strpos($newText, $arPattern["search"]) !== false)
				{
					$newText = preg_replace($arPattern["pattern"], $arPattern["replacement"], $newText);
					$bChanged = true;
				}
			}


			if ($isCategory)
				if(strpos($newText, $catSearch) !== false)
				{
					$newText = $this->RenameCategoryOnPage($newText, $sCatName, $newName);
					$bChanged = true;
				}

			if($bChanged)
			{
				$this->CleanCache($arElement["ID"], $arElement["NAME"], $iBlockId);
				$this->cIB_E->Update($arElement["ID"], array("DETAIL_TEXT" => $newText), false, true);
				self::MarkPageAsUpdated($iBlockId, $iBlockSectId, $arElement["NAME"]);
				$count++;
			}
		}

		return $count;
	}

	function RenameCategoryOnPage($pageText, $oldCategoryName, $newCategoryName)
	{
		$newCategoryName = preg_replace("/category:/isU", "", $newCategoryName);
		return preg_replace("/\[\[".GetMessage('CATEGORY_NAME').":".$oldCategoryName."\]\]/isU", "[[".GetMessage('CATEGORY_NAME').":".$newCategoryName."]]", $pageText);
	}

	static function SetDefaultPage($IBLOCK_ID, $NAME)
	{
		if (CCamerasSocnet::IsSocNet())
		{
			$ENTITY_ID = 'IBLOCK_'.$IBLOCK_ID.'_SECTION';
			$ELEMENT_ID = CCamerasSocnet::$iCatId;
		}
		else
		{
			$ENTITY_ID = 'IBLOCK_'.$IBLOCK_ID;
			$ELEMENT_ID = $IBLOCK_ID;
		}

		AddEventHandler("main", "OnUserTypeBuildList", array("CUserTypeCameras", "GetUserTypeDescription"));
		$GLOBALS['USER_FIELD_MANAGER']->CleanCache();
		$GLOBALS['USER_FIELD_MANAGER']->arUserTypes = '';

		$arElement = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields($ENTITY_ID, $ELEMENT_ID);

		if ($arElement !== false)
		{
			if (!isset($arElement['UF_CAMERAS_INDEX']))
			{
				$arFields = array();
				$arFields['ENTITY_ID'] = $ENTITY_ID;
				$arFields['FIELD_NAME'] = 'UF_CAMERAS_INDEX';
				$arFields['USER_TYPE_ID'] = 'cameras';
				$CAllUserTypeEntity = new CUserTypeEntity();
				$intID=$CAllUserTypeEntity->Add($arFields);
				if (false == $intID)
				{
					$e = $GLOBALS['APPLICATION']->GetException();
					if ($e)
						ShowError(GetMessage("CAMERAS_USER_T_ADD_ERR").$e->GetString());
				}
			}

			if (empty($arElement['UF_CAMERAS_INDEX']['VALUE']) || $arElement['UF_CAMERAS_INDEX']['VALUE'] != $NAME)
			{
				$arFields = array();
				$arFields['UF_CAMERAS_INDEX'] = $NAME;
				$GLOBALS['USER_FIELD_MANAGER']->Update($ENTITY_ID, $ELEMENT_ID, $arFields);
			}
			return true;
		}
		return false;
	}

	static function GetDefaultPage($IBLOCK_ID)
	{
		if (CCamerasSocnet::IsSocNet())
		{
			$ENTITY_ID = 'IBLOCK_'.$IBLOCK_ID.'_SECTION';
			$ELEMENT_ID = CCamerasSocnet::$iCatId;
		}
		else
		{
			$ENTITY_ID = 'IBLOCK_'.$IBLOCK_ID;
			$ELEMENT_ID = $IBLOCK_ID;
		}

		AddEventHandler("main", "OnUserTypeBuildList", array("CUserTypeCameras", "GetUserTypeDescription"));
		$GLOBALS['USER_FIELD_MANAGER']->CleanCache();
		$GLOBALS['USER_FIELD_MANAGER']->arUserTypes = '';

		$arElement = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields(
			$ENTITY_ID,
			$ELEMENT_ID
		);

		return isset($arElement['UF_CAMERAS_INDEX']['VALUE']) ? $arElement['UF_CAMERAS_INDEX']['VALUE'] : '';
	}

	function GetCategory($NAME, $IBLOCK_ID)
	{
		global $arParams;

		$arResult = array();
		$arResult[] = array(
			'TITLE' => GetMessage('Service:Categories_TITLE'),
			'NAME' => GetMessage('Service:Categories'),
			'LINK' => CHTTP::urlAddParams(
				CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_CATEGORIES'],
					array(
						'cameras_name' => 'Service:Categories',
						'group_id' => CCamerasSocnet::$iSocNetId
					)
				),
				array()
			),
			'IS_RED' => 'N',
			'IS_SERVICE' => 'Y'
		);

		$arFilter['=XML_ID'] = CCamerasUtils::htmlspecialcharsback($NAME);
		$arFilter['IBLOCK_ID'] = $IBLOCK_ID;
		$arFilter['CHECK_PERMISSIONS'] = 'N';

		if (CCamerasSocnet::IsSocNet())
			$arFilter['SUBSECTION'] = CCamerasSocnet::$iCatId;

		$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, Array());
		$arElement = $rsElement->GetNext();

		$sCatName = '';
		if (CCamerasUtils::IsCategoryPage($NAME, $sCatName))
			return 	$arResult;

		$arLink = array();
		$arLinkExists = array();
		$arCat = array();
		$rsSect = CIBlockElement::GetElementGroups($arElement['ID'], false);
		while($arSect = $rsSect->GetNext())
		{
			$arCat[$arSect['ID']] = $arSect;
			$arLink[] = 'category:'.CCamerasUtils::htmlspecialcharsback($arSect['NAME']);
		}

		/*if(empty($arLink))
			return array();*/

		if (CCamerasSocnet::IsSocNet() && isset($arCat[CCamerasSocnet::$iCatId]))
			unset($arCat[CCamerasSocnet::$iCatId]);

		$arFilter = array();
		$arFilter['=NAME'] = $arLink;
		$arFilter['IBLOCK_ID'] = $IBLOCK_ID;
		$arFilter['ACTIVE'] = 'Y';
		$arFilter['CHECK_PERMISSIONS'] = 'N';
		if (CCamerasSocnet::IsSocNet())
			$arFilter['SUBSECTION'] = CCamerasSocnet::$iCatId;

		$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, Array());

		while($obElement = $rsElement->GetNextElement())
		{
			$arFields = $obElement->GetFields();
			$arLinkExists[] = preg_replace('/^(category|'.GetMessage('CATEGORY_NAME').'):/i'.BX_UTF_PCRE_MODIFIER, '', $arFields['NAME']);
		}

		if (!empty($arCat))
		{
			foreach ($arCat as $_arCat)
			{
				$_arCat['NAME'] = CCamerasUtils::htmlspecialcharsback($_arCat['NAME'], false);
				$_arResult = array();
				$_arResult['ID'] = $_arCat['ID'];
				$_arResult['IS_RED'] = 'N';
				$_arResult['LINK'] = CHTTP::urlAddParams(
					CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_CATEGORY'],
						array(
							'cameras_name' => 'Category:'.$_arCat['NAME'],
							'group_id' => CCamerasSocnet::$iSocNetId
						)
					),
					array()
				);

				$_arResult['TITLE'] = $_arCat['NAME'];
				$_arResult['NAME'] = $_arCat['NAME'];
				$_arResult['IS_SERVICE'] = 'N';
				if (!in_array($_arCat['NAME'], $arLinkExists))
					$_arResult['IS_RED'] = 'Y';
				$arResult[] = $_arResult;
			}
		}
		return $arResult;
	}

	/**
	 *
	 *
	 *
	 * @param int $ID
	 * @return array
	 */
	public static function GetElementById($ID, $arFilter)
	{
		global $arParams;
		$arFilter['ID'] = $ID;
		if (CCamerasSocnet::IsSocNet())
			$arFilter['SUBSECTION'] = CCamerasSocnet::$iCatId;
		$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, Array());
		$obElement = $rsElement->GetNextElement();
		$arResult = false;
		if ($obElement !== false)
		{
			$arResult = $obElement->GetFields();

			if (isset($arResult['NAME']))
				$arResult['NAME'] = htmlspecialcharsbx($arResult['NAME']);
			$rsProperties = $obElement->GetProperties(array(), array('CODE' => 'IMAGES'));

			foreach ($rsProperties as $arProperty)
				$arResult[$arProperty['CODE']] = $arProperty['VALUE'];

			$arResult['SECTIONS'] = self::GetCategory($arResult['XML_ID'], $arFilter['IBLOCK_ID']);
			if (!empty($arResult['TAGS']))
			{
				$_arTAGS = explode(',', $arResult['TAGS']);
				$arResult['_TAGS'] = array();
				foreach ($_arTAGS as $sTag)
				{
					$arTag = array('NAME' => $sTag);
					if (!empty($arParams['PATH_TO_SEARCH']))
					{
						$arP = $arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N' ? array($arParams['OPER_VAR'] => 'search') : array();
						$arP['tags'] = rawurlencode($sTag);
						$arTag['LINK'] = CHTTP::urlAddParams(
									CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_SEARCH'],
										array(
											'cameras_name' => $arParams['ELEMENT_NAME'],
											'group_id' => CCamerasSocnet::$iSocNetId)
										),
										$arP
									);
					}
					$arResult['_TAGS'][] = $arTag;
				}
			}
		}
		return $arResult;
	}

	/**
	 * @param string $NAME (mandatory) - the name of page
	 * @param array $arFilter (mandatory) - the filter for CIBlockElement::GetList
	 * @param array $arComponentParams (optional) - params of the calling cameras component
	 *		  using indexes: CACHE_TIME, PATH_TO_SEARCH, IN_COMPLEX, SEF_MODE, OPER_VAR, ELEMENT_NAME
	 *		  necessary for: building search tag links
	 * @return array
	 */
	public static function GetElementByName($NAME, $arFilter, $arComponentParams = array())
	{
		global $CACHE_MANAGER;

		$iCatId = "";

		if (CCamerasSocnet::IsSocNet())
		{
			$arFilter['SUBSECTION'] = CCamerasSocnet::$iCatId;
			$iCatId = $arFilter['SUBSECTION'];
		}

		$cacheByNameID = self::GetIdForCacheByName($arFilter['IBLOCK_ID'], $iCatId, $NAME);

		$cacheTime = isset($arComponentParams['CACHE_TIME']) ? intval($arComponentParams['CACHE_TIME']) : CCameras::CCAMERAS_CACHE_TTL;

		if($CACHE_MANAGER->Read($cacheTime, $cacheByNameID))
		{
			$cachedElement = $CACHE_MANAGER->Get($cacheByNameID);

			if($cachedElement)
			{
				//if cached element satisfied to filter's conditions
				$sameFilter = true;
				foreach ($arFilter as $key => $value)
				{
					if(isset($cachedElement[$key]) && $value != "" && $cachedElement[$key] != $value)
					{
						$sameFilter = false;
						break;
					}
				}

				if($sameFilter)
					return $cachedElement;
			}
		}

		$NAME = CCamerasUtils::UnlocalizeCategoryName($NAME);
		$NAME = CCamerasUtils::htmlspecialcharsback($NAME);
		$arFilter['=XML_ID'] = $NAME;

		$rsElement = CIBlockElement::GetList(array(), $arFilter);
		$obElement = $rsElement->GetNextElement();
		$arResult = false;
		if ($obElement !== false)
		{
			$arResult = $obElement->GetFields();
			if (isset($arResult['NAME']))
				$arResult['NAME'] = htmlspecialcharsbx($arResult['NAME']);
			$rsProperties = $obElement->GetProperties(array(), array('CODE' => 'IMAGES'));

			foreach ($rsProperties as $arProperty)
				$arResult[$arProperty['CODE']] = $arProperty['VALUE'];

			$rsProperties = $obElement->GetProperties(array(), array('CODE' => 'FORUM_TOPIC_ID'));

			foreach ($rsProperties as $arProperty)
				$arResult[$arProperty['CODE']] = $arProperty['VALUE'];

			$arResult['SECTIONS'] = self::GetCategory($arResult['XML_ID'], $arFilter['IBLOCK_ID']);
			if (!empty($arResult['TAGS']))
			{
				$_arTAGS = explode(',', $arResult['TAGS']);
				$arResult['_TAGS'] = array();
				foreach ($_arTAGS as $sTag)
				{
					$sTag = trim($sTag);
					$arTag = array('NAME' => $sTag);
					if (!empty($arComponentParams) && isset($arComponentParams['PATH_TO_SEARCH']))
					{
						$arP = $arComponentParams['IN_COMPLEX'] == 'Y' && $arComponentParams['SEF_MODE'] == 'N' ? array($arComponentParams['OPER_VAR'] => 'search') : array();
						$arP['tags'] = rawurlencode($sTag);
						$arTag['LINK'] = CHTTP::urlAddParams(
									CComponentEngine::MakePathFromTemplate($arComponentParams['PATH_TO_SEARCH'],
										array(
											'cameras_name' => $arComponentParams['ELEMENT_NAME'],
											'group_id' => CCamerasSocnet::$iSocNetId
										)
									),
									$arP
								);
					}
					$arResult['_TAGS'][] = $arTag;
				}
			}
		}

		if(!empty($arComponentParams)) //Let's store only full page data with tag links
			$CACHE_MANAGER->Set($cacheByNameID, $arResult);

		return $arResult;
	}

	/**
	 * @return \Bitrix\Main\ErrorCollection
	 */
	public function getErrors()
	{
		return $this->errorCollection;
	}

	public function CleanCacheById($ID, $iBlockId = false)
	{
		return $this->CleanCache($ID, false, $iBlockId);
	}

	public function CleanCache($ID = false, $Name = false, $iBlockId = false)
	{
		if($ID === false && !$Name)
			return false;

		global $CACHE_MANAGER;

		if($ID !== false)
			$CACHE_MANAGER->ClearByTag('cameras_'.$ID);

		if(!$iBlockId)
			return true;

		$iCatId = CCamerasSocnet::IsSocNet() ? CCamerasSocnet::$iCatId : "";

		if($ID !== false )
		{
			$cacheByNameID = self::GetIdForCacheByName($iBlockId, $iCatId, $ID);
			$CACHE_MANAGER->Clean($cacheByNameID);

			if(!$Name)
			{
				$arFilter = array(
					'IBLOCK_ID' => $iBlockId,
					'CHECK_PERMISSIONS' => 'N'
					);

				$arElement = self::GetElementById($ID, $arFilter);
				if($arElement != false)
					$elName = $arElement['NAME'];
			}
			else
			{
				$elName = $Name;
			}
		}

		$cacheByNameID = self::GetIdForCacheByName($iBlockId, $iCatId, $elName);
		$CACHE_MANAGER->Clean($cacheByNameID);

		return true;
	}

	private static function GetIdForCacheByName($iBlockId, $iSocCatId, $elementName)
	{
		return self::GET_BY_NAME_CACHE_ID.$iBlockId.$iSocCatId.$elementName;
	}

	public static function UnMarkPageAsUpdated($iBlockId, $iSocCatId, $name)
	{
		global $CACHE_MANAGER;

		$cacheId = self::GetCacheIdForPageUpdated($iBlockId, $iSocCatId, $name);

		$CACHE_MANAGER->Clean($cacheId);

		return true;
	}

	public static function IsPageUpdated($iBlockId, $iSocCatId, $name, $cacheTime = self::CCAMERAS_CACHE_TTL)
	{
		global $CACHE_MANAGER;

		$cacheId = self::GetCacheIdForPageUpdated($iBlockId, $iSocCatId, $name);

		if($CACHE_MANAGER->Read($cacheTime, $cacheID))
			return ($CACHE_MANAGER->Get($cacheId) == "Y");

		return false;
	}

	private static function MarkPageAsUpdated($iBlockId, $iSocCatId, $name)
	{
		global $CACHE_MANAGER;

		$cacheId = self::GetCacheIdForPageUpdated($iBlockId, $iSocCatId, $name);

		$CACHE_MANAGER->Set($cacheId, "Y");

		return true;
	}

	private static function GetCacheIdForPageUpdated($iBlockId, $iSocCatId, $name)
	{
		return self::PAGE_UPDATED_CACHE_ID.$iBlockId.$iSocCatId.$name;
	}

}

?>