<?if (CModule::IncludeModule("iblock")):
	IncludeModuleLangFile(__FILE__);
?>
<script>
function ChangeStatus(pointer)
{
	if (typeof pointer != "object" || (document.forms['cameras_form'] == null))
		return false;
	if (pointer.name == 'create_iblock_type')
	{
		document.forms['cameras_form'].elements['iblock_type_id'].disabled = (pointer.id == 'create_iblock_type_y');
		document.forms['cameras_form'].elements['iblock_type_name'].disabled = !(pointer.id == 'create_iblock_type_y');
	}
	else if (pointer.name == 'create_forum_group')
	{
		document.forms['cameras_form'].elements['forum_group_id'].disabled = (pointer.id == 'create_forum_group_y');
		document.forms['cameras_form'].elements['forum_group_name'].disabled = !(pointer.id == 'create_forum_group_y');
	}
	else if (pointer.name == 'create_socnet_iblock_type')
	{
		document.forms['cameras_form'].elements['socnet_iblock_type_id'].disabled = (pointer.id == 'create_socnet_iblock_type_y');
		document.forms['cameras_form'].elements['socnet_iblock_type_name'].disabled = !(pointer.id == 'create_socnet_iblock_type_y');
	}
	else if (pointer.name == 'create_socnet_forum_group')
	{
		document.forms['cameras_form'].elements['socnet_forum_group_id'].disabled = (pointer.id == 'create_socnet_forum_group_y');
		document.forms['cameras_form'].elements['socnet_forum_group_name'].disabled = !(pointer.id == 'create_socnet_forum_group_y');
	}
}

function CheckCreate(pointer)
{
	if (!pointer || typeof pointer != "object" || !document.getElementById(pointer.id + '_create'))
		return false;
	document.getElementById(pointer.id + '_create').style.display = (pointer.checked ? "" : "none");
}
CheckCreate(document.getElementById('iblock'));
CheckCreate(document.getElementById('socnet_iblock'));

</script>
<form action="<?=$APPLICATION->GetCurPage()?>" name="cameras_form" id="cameras_form" class="form-photo" method="POST">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<input type="hidden" name="id" value="cameras">
<input type="hidden" name="install" value="Y">
<input type="hidden" name="step" value="2">
<table class="list-table">
	<?if ($GLOBALS["APPLICATION"]->GetGroupRight("iblock") >= "W"):?>
    <tbody id="iblock_create">
    <tr><td><span class="required">*</span><?=GetMessage("CAMERAS_CREATE_NEW_IBLOCK_TYPE")?>: </td><td>
            <input onclick="ChangeStatus(this)" type="radio" name="create_iblock_type" id="create_iblock_type_n" value="N" <?=($_REQUEST["create_iblock_type"] != "Y" ? " checked=\"checked\"" : "")?> />
            <label for="create_iblock_type_n"><?=GetMessage("CAMERAS_SELECT")?>: </label>
            <select name="iblock_type_id"><?
                $arIBlockType = array();
                $rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
                while ($arr=$rsIBlockType->Fetch())
                {
                    if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
                    {
                        ?><option value="<?=$ar["ID"]?>" <?=($_REQUEST["iblock_type_id"] == $ar["ID"] ? " selected='selected'" : "")?>><?="[".$ar["ID"]."] ".$ar["NAME"]?></option><?
                    }
                }
                ?></select><br />
            <input onclick="ChangeStatus(this)" type="radio" name="create_iblock_type" id="create_iblock_type_y" value="Y" checked="checked" />
            <label for="create_iblock_type_y"><?=GetMessage("CAMERAS_CREATE")?>: </label>
            <span class="required">*</span><?=GetMessage("CAMERAS_ID")?> (ID):
            <input type="text" name="iblock_type_name" value="cameras"/><br />
        </td></tr>
    </tbody>

    <tr class="head"><td colspan="2"><input type="checkbox" name="iblock" id="iblock" value="Y" onclick="CheckCreate(this);" checked='checked'/> <label for="iblock"><?=GetMessage("CAMERAS_CREATE_NEW_IBLOCK")?></label></td></tr>

    <tr><td><span class="required">*</span><?=GetMessage("CAMERAS_CREATE_NEW_IBLOCK_NAME")?>: </td><td><input type="text" name="iblock_name" value="cameras_all" /></td></tr>

    <?
	endif;

	if (IsModuleInstalled("socialnetwork")):
		if ($GLOBALS["APPLICATION"]->GetGroupRight("iblock") >= "W"):?>
		<tr class="head"><td colspan="2"><input type="checkbox" name="socnet_iblock" id="socnet_iblock" value="Y" onclick="CheckCreate(this);" checked='checked'/> <label for="socnet_iblock"><?=GetMessage("CAMERAS_CREATE_NEW_SOCNET_IBLOCK")?></label></td></tr>
        <tr><td><span class="required">*</span><?=GetMessage("CAMERAS_CREATE_NEW_SOCNET_IBLOCK_NAME")?>: </td><td><input type="text" name="socnet_iblock_name" value="cameras_groups" /></td></tr>
        <!--<tbody id="socnet_iblock_create">
		<tr><td><span class="required">*</span><?/*=GetMessage("CAMERAS_CREATE_NEW_SOCNET_IBLOCK_NAME")*/?>: </td><td><input type="text" name="socnet_iblock_name" value="cameras_groups" /></td></tr>
		<tr><td><span class="required">*</span><?/*=GetMessage("CAMERAS_CREATE_NEW_SOCNET_IBLOCK_TYPE")*/?>: </td><td>
			<input onclick="ChangeStatus(this)" type="radio" name="create_socnet_iblock_type" id="create_socnet_iblock_type_n" value="N" <?/*=($_REQUEST["create_socnet_iblock_type"] != "Y" ? " checked=\"checked\"" : "")*/?> />
			<label for="create_iblock_type_n"><?/*=GetMessage("CAMERAS_SELECT")*/?>: </label>
			<select name="socnet_iblock_type_id"><?/*
				$arIBlockType = array();
				$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
				while ($arr=$rsIBlockType->Fetch())
				{
					if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
					{
						*/?><option value="<?/*=$arr["ID"]*/?>" <?/*=($_REQUEST["socnet_iblock_type_id"] == $arr["ID"] ? " selected='selected'" : "")*/?>><?/*="[".$arr["ID"]."] ".$ar["NAME"]*/?></option><?/*
					}
				}
				*/?></select><br />
			<input onclick="ChangeStatus(this)" type="radio" name="create_socnet_iblock_type" id="create_socnet_iblock_type_y" value="Y" checked="checked" />
			<label for="create_iblock_type_y"><?/*=GetMessage("CAMERAS_CREATE")*/?>: </label>
			<span class="required">*</span><?/*=GetMessage("CAMERAS_ID")*/?> (ID):
				<input type="text" name="socnet_iblock_type_name" value="cameras"/><br />
			</td></tr>
		</tbody>-->

        <?endif;

	endif;
	?>
	<tr>
		<td colspan="2"><input type="submit" value="<?=GetMessage("MOD_INSTALL")?>" /></td>
	</tr>
</table>
</form>
<?endif;?>