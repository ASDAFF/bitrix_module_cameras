<?
	if(!check_bitrix_sessid() || !CModule::IncludeModule("iblock"))
		return;

	$strWarning = "";
	$bVarsFromForm = false;
	$arUGroupsEx = Array();
	$dbUGroups = CGroup::GetList($by = "c_sort", $order = "asc");
	while($arUGroups = $dbUGroups -> Fetch())
	{
		if ($arUGroups["ANONYMOUS"] == "Y")
			$arUGroupsEx[$arUGroups["ID"]] = "R";
	}


	if ($_REQUEST["iblock"] == "Y" && $GLOBALS["APPLICATION"]->GetGroupRight("iblock") >= "W")
	{
		if ($_REQUEST["create_iblock_type"] == "Y")
		{
			$arIBTLang = array();
			$arLang = array();
			$l = CLanguage::GetList($lby="sort", $lorder="asc");
			while($ar = $l->ExtractFields("l_"))
				$arIBTLang[]=$ar;

			for($i=0; $i<count($arIBTLang); $i++)
				$arLang[$arIBTLang[$i]["LID"]] = array("NAME" => $_REQUEST["iblock_type_name"]);

			$arFields = array(
				"ID" => $_REQUEST["iblock_type_name"],
				"LANG" => $arLang,
				"SECTIONS" => "Y");

			$GLOBALS["DB"]->StartTransaction();
			$obBlocktype = new CIBlockType;
			$IBLOCK_TYPE_ID = $obBlocktype->Add($arFields);
			if (strLen($IBLOCK_TYPE_ID) <= 0)
			{
				$strWarning .= $obBlocktype->LAST_ERROR;
				$GLOBALS["DB"]->Rollback();
				$bVarsFromForm = true;
			}
			else
			{
				$GLOBALS["DB"]->Commit();
				$_REQUEST["iblock_type_id"] = $IBLOCK_TYPE_ID;
			}
		}
		else
		    $IBLOCK_TYPE_ID = $_REQUEST["iblock_type_id"];

		if ($IBLOCK_TYPE_ID)
		{
			$DB->StartTransaction();

			$arFields = Array(
				"ACTIVE"=>"Y",
				"NAME"=>$_REQUEST["iblock_name"],
				"IBLOCK_TYPE_ID"=>$IBLOCK_TYPE_ID,
				"LID"=>array(),
				"DETAIL_PAGE_URL" => "#SITE_DIR#/$IBLOCK_TYPE_ID/#EXTERNAL_ID#/",
				"SECTION_PAGE_URL" => "#SITE_DIR#/$IBLOCK_TYPE_ID/category:#EXTERNAL_ID#/",
				"LIST_PAGE_URL" => "#SITE_DIR#/$IBLOCK_TYPE_ID/",
				"GROUP_ID" => Array("1" => "X", "2" => "R", "3" => "W")
			);

			/*if (IsModuleInstalled("bizproc"))
			{
				$arFields['WORKFLOW'] = 'N';
				$arFields['BIZPROC'] = 'Y';
			}*/

            if (IsModuleInstalled("workflow"))
            {
                $arFields['WORKFLOW'] = 'N';
            }

			$ib = new CIBlock;

			$db_sites = CSite::GetList($lby="sort", $lorder="asc");
			while ($ar_sites = $db_sites->Fetch())
			{
				if ($ar_sites["ACTIVE"] == "Y")
					$arFields["LID"][] = $ar_sites["LID"];
				$arSites[] = $ar_sites;
			}

			if (empty($arFields["LID"]))
				$arFields["LID"][] = $ar_sites[0]["LID"];
			if (!empty($arUGroupsEx))
				$arFields["GROUP_ID"] = $arUGroupsEx;

			$ID = $ib->Add($arFields);
            $createPropertyUserGroupsInIblockAll = false;
			if($ID <= 0)
			{
				$strWarning .= $ib->LAST_ERROR."<br>";
				$bVarsFromForm = true;
				$DB->Rollback();
			}
			else
			{
				$DB->Commit();
				$_REQUEST["new_iblock_name"] = "";
				$_REQUEST["new_iblock"] = "created";
                $arFields1 = Array(
                    "NAME" => "Ссылка на камеру",
                    "ACTIVE" => "Y",
                    "SORT" => "100",
                    "CODE" => "LINK",
                    "PROPERTY_TYPE" => "S",
                    "IBLOCK_ID" => $ID,
                    "SEARCHABLE" => "Y",
                    "FILTRABLE" => "Y"
                );

                $createPropertyUserGroupsInIblockAll = true;

                $ibpAll = new CIBlockProperty;
                $PropID = $ibpAll->Add($arFields1);

                COption::SetOptionString("cameras", "socnet_iblock_type", $IBLOCK_TYPE_ID);
                COption::SetOptionString("cameras", "socnet_iblock_id_all", $ID);
			}
		}
	}
    if (!$bVarsFromForm && IsModuleInstalled("socialnetwork"))
	{
		CModule::IncludeModule("socialnetwork");

		require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/cameras/include.php');

		if ($_REQUEST["socnet_iblock"] == "Y" && $GLOBALS["APPLICATION"]->GetGroupRight("iblock") >= "W")
		{
			$IBLOCK_TYPE_ID = $_REQUEST["iblock_type_id"];

			if ($IBLOCK_TYPE_ID)
			{
				$DB->StartTransaction();

				$arFields = Array(
					"ACTIVE"=>"Y",
					"NAME"=>$_REQUEST["socnet_iblock_name"],
					"IBLOCK_TYPE_ID"=>$IBLOCK_TYPE_ID,
					"LID"=>array(),
					"DETAIL_PAGE_URL" => "",
					"SECTION_PAGE_URL" => "",
					"LIST_PAGE_URL" => "",
					"INDEX_ELEMENT" => "N",
					"INDEX_SECTION" => "N",
					"GROUP_ID" => Array('1' => 'X', "2" => "R", "3" => "W")
				);

				/*if (IsModuleInstalled('bizproc'))
				{
					$arFields['WORKFLOW'] = 'N';
					$arFields['BIZPROC'] = 'Y';
				}*/

                if (IsModuleInstalled("workflow"))
                {
                    $arFields['WORKFLOW'] = 'N';
                }

				$ib = new CIBlock;

				$db_sites = CSite::GetList($lby="sort", $lorder="asc");
				while ($ar_sites = $db_sites->Fetch())
				{
					if ($ar_sites["ACTIVE"] == "Y")
						$arFields["LID"][] = $ar_sites["LID"];
					$arSites[] = $ar_sites;
				}

				if (empty($arFields["LID"]))
					$arFields["LID"][] = $ar_sites[0]["LID"];
				if (!empty($arUGroupsEx))
					$arFields["GROUP_ID"] = $arUGroupsEx;

				$SOCNET_ID = $ib->Add($arFields);
				if($SOCNET_ID <= 0)
				{
					$strWarning .= $ib->LAST_ERROR."<br>";
					$bVarsFromForm = true;
					$DB->Rollback();
				}
				else
				{
					$DB->Commit();
					$_REQUEST["new_socnet_iblock_name"] = "";
					$_REQUEST["new_socnet_iblock"] = "created";
					COption::SetOptionString("cameras", "socnet_iblock_type", $IBLOCK_TYPE_ID);
					COption::SetOptionString("cameras", "socnet_iblock_id_by_group", $SOCNET_ID);
					COption::SetOptionString("cameras", "socnet_enable", "Y");
					CCamerasSocnet::EnableSocnet(true);

                    if($createPropertyUserGroupsInIblockAll){
                        $arFields2 = Array(
                            "NAME" => "Группы пользователей",
                            "ACTIVE" => "Y",
                            "SORT" => "100",
                            "CODE" => "USER_GROUPS",
                            "PROPERTY_TYPE" => "G",
                            "MULTIPLE" => "Y",
                            "IBLOCK_ID" => $ID,
                            "SEARCHABLE" => "Y",
                            "FILTRABLE" => "Y",
                            "LINK_IBLOCK_ID" => $SOCNET_ID
                        );

                        $PropID2 = $ibpAll->Add($arFields2);
                    }

				}
			}
		}
   }

    if ($bVarsFromForm)
	{
		ShowError($strWarning);
		include("step.php");
	}
	else
	{
		?>
		<script>
		window.location='/bitrix/admin/module_admin.php?step=3&lang=<?=LANGUAGE_ID."&id=cameras&install=y&".bitrix_sessid_get()?>';
		</script>
		<?
	}

?>