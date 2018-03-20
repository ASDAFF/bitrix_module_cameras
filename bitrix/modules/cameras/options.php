<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2012 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

global $MESS;
include(GetLangFileName($GLOBALS['DOCUMENT_ROOT'].'/bitrix/modules/cameras/lang/', '/options.php'));
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/options.php');

$module_id = 'cameras';
CModule::IncludeModule($module_id);

CModule::IncludeModule('iblock');
$MOD_RIGHT = $APPLICATION->GetGroupRight($module_id);
if($MOD_RIGHT>='R'):

	// set up form
	/*$arAllOptions =	Array(
		Array('allow_html', GetMessage('CAMERAS_OPTIONS_ALLOW_HTML'), 'Y', Array('checkbox')),
		Array('image_max_width', GetMessage('CAMERAS_OPTIONS_IMAGE_MAX_WIDTH'), '600', Array('text')),
		Array('image_max_height', GetMessage('CAMERAS_OPTIONS_IMAGE_MAX_HEIGHT'), '600', Array('text')),
		Array('note' => GetMessage('CAMERAS_OPTIONS_IMAGE_DESCR'))
	);*/

if($MOD_RIGHT>='Y' || $USER->IsAdmin()):

	if ($REQUEST_METHOD=='GET' && strlen($RestoreDefaults)>0 && check_bitrix_sessid())
	{
		COption::RemoveOption($module_id);
		$z = CGroup::GetList($v1='id',$v2='asc', array('ACTIVE' => 'Y', 'ADMIN' => 'N'));
		while($zr = $z->Fetch())
			$APPLICATION->DelGroupRight($module_id, array($zr['ID']));
	}

	if($REQUEST_METHOD=='POST' && strlen($Update)>0 && check_bitrix_sessid())
	{
		$arOptions = $arAllOptions;

		//fix: http://jabber.bx/view.php?id=20941 (for compatibility)
		COption::RemoveOption($module_id,'socnet_message_per_page');

		foreach($arOptions as $option)
		{
			if(!is_array($option) || isset($option['note']))
				continue;

			$name = $option[0];
			$val = ${$name};
			if($option[3][0] == 'checkbox' && $val != 'Y')
				$val = 'N';
			if($option[3][0] == 'multiselectbox')
				$val = @implode(',', $val);
			if ($name == 'image_max_width' || $name == 'image_max_height')
				$val = (int) $val;

			COption::SetOptionString($module_id, $name, $val, $option[1]);
		}

		if(IsModuleInstalled('socialnetwork'))
		{
			COption::SetOptionString($module_id, 'socnet_iblock_id_by_group', $_POST['socnet_iblock_id_by_group']);
            COption::SetOptionString($module_id, 'socnet_iblock_id_all', $_POST['socnet_iblock_id_all']);

			COption::SetOptionString($module_id, 'socnet_iblock_type', $_POST['socnet_iblock_type']);
            COption::SetOptionString($module_id, 'socnet_enable', $_POST['socnet_enable']);
			CCamerasSocnet::EnableSocnet($_POST['socnet_enable'] === 'Y');
		}
	}

endif; //if($MOD_RIGHT>="W"):

$aTabs = array();
//$aTabs[] = array('DIV' => 'set', 'TAB' => GetMessage('MAIN_TAB_SET'), 'ICON' => 'cameras_settings', 'TITLE' => GetMessage('MAIN_TAB_TITLE_SET'));

if(IsModuleInstalled('socialnetwork'))
{
	$aTabs[] = array(
		'DIV' => 'socnet',
		'TAB' => GetMessage('CAMERAS_TAB_SOCNET'),
		'TITLE' => GetMessage('CAMERAS_TAB_TITLE_SOCNET'),
		'ICON' => 'cameras_settings'
	);
}
$aTabs[] = array('DIV' => 'rights', 'TAB' => GetMessage('MAIN_TAB_RIGHTS'), 'ICON' => 'cameras_settings', 'TITLE' => GetMessage('MAIN_TAB_TITLE_RIGHTS'));

$tabControl = new CAdminTabControl('tabControl', $aTabs);
?>
<?
$tabControl->Begin();
?>
<style>
table.edit-table td.field-name  {
	width: 40% !important;
}
</style>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?=LANGUAGE_ID?>" name="cameras_settings">
<?/*$tabControl->BeginNextTab();*/?>
<?__AdmSettingsDrawList('cameras', $arAllOptions);?>
<?
if(IsModuleInstalled('socialnetwork'))
{
	$socnet_iblock_id_all = COption::GetOptionString($module_id, 'socnet_iblock_id_all');
    $socnet_iblock_id_by_group = COption::GetOptionString($module_id, 'socnet_iblock_id_by_group');
	$socnet_enable = COption::GetOptionString($module_id, 'socnet_enable') == 'Y' && CCamerasSocnet::IsEnabledSocnet() ? 'Y' : 'N';
	$tabControl->BeginNextTab();

	__AdmSettingsDrawRow('cameras', array('socnet_enable', GetMessage('CAMERAS_OPTIONS_SOCNET_ENABLE'), $socnet_enable, Array('checkbox')))
	?>
	<tr>
		<td><?echo GetMessage('CAMERAS_OPTIONS_SOCNET_IBLOCK_ID_BY_GROUP')?></td>
		<td><?echo GetIBlockDropDownList($socnet_iblock_id_by_group, 'socnet_iblock_type_id', 'socnet_iblock_id_by_group', false, 'class="adm-detail-iblock-types"', 'class="adm-detail-iblock-list"');?></td>
	</tr>
    <tr>
        <td><?echo GetMessage('CAMERAS_OPTIONS_SOCNET_IBLOCK_ID_ALL')?></td>
        <td><?echo GetIBlockDropDownList($socnet_iblock_id_all, 'socnet_iblock_type_id', 'socnet_iblock_id_all', false, 'class="adm-detail-iblock-types"', 'class="adm-detail-iblock-list"');?></td>
    </tr>
	<?
}?>
<?$tabControl->BeginNextTab();?>
<?require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/admin/group_rights.php');?>
<?$tabControl->Buttons();?>
<script language="JavaScript">
function RestoreDefaults()
{
	if(confirm('<?echo AddSlashes(GetMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING'))?>'))
		window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?echo LANG?>&mid=<?echo rawurlencode($mid)."&".bitrix_sessid_get();?>";
}
</script>
<input type="submit" name="Update" <?if ($MOD_RIGHT<'W') echo "disabled" ?> value="<?echo GetMessage('MAIN_SAVE')?>">
<input type="reset" name="reset" value="<?echo GetMessage('MAIN_RESET')?>">
<input type="hidden" name="Update" value="Y">
<?=bitrix_sessid_post();?>
<input type="button" <?if ($MOD_RIGHT<'W') echo "disabled" ?> title="<?echo GetMessage('MAIN_HINT_RESTORE_DEFAULTS')?>" OnClick="RestoreDefaults();" value="<?echo GetMessage('MAIN_RESTORE_DEFAULTS')?>">
<?$tabControl->End();?>
</form>
<?endif;
?>
