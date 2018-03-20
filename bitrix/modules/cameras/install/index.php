<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen('/install/index.php'));
include(GetLangFileName($strPath2Lang.'/lang/', '/install/index.php'));

if(class_exists('cameras')) return;
Class cameras extends CModule
{
	var $MODULE_ID = 'cameras';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = 'Y';
	var $error = '';

	function cameras()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen('/index.php'));
		include($path.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}
		else
		{
			$this->MODULE_VERSION = CAMERAS_VERSION;
			$this->MODULE_VERSION_DATE = CAMERAS_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage('CAMERAS_INSTALL_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('CAMERAS_INSTALL_DESCRIPTION');
	}

	function InstallDB()
	{
		COption::SetOptionString('cameras', 'GROUP_DEFAULT_RIGHT', 'R');
		RegisterModule('cameras');
		RegisterModuleDependences('main', 'OnAddRatingVote', 'cameras', 'CRatingsComponentsCameras', 'OnAddRatingVote', 200);
		RegisterModuleDependences('main', 'OnCancelRatingVote', 'cameras', 'CRatingsComponentsCameras', 'OnCancelRatingVote', 200);
		RegisterModuleDependences('search', 'BeforeIndex', 'cameras', 'CRatingsComponentsCameras', 'BeforeIndex');
		RegisterModuleDependences('socialnetwork', 'BeforeIndexSocNet', 'cameras', 'CCamerasSocNet', 'BeforeIndexSocNet');
		RegisterModuleDependences("im", "OnGetNotifySchema", "cameras", "CCamerasNotifySchema", "OnGetNotifySchema");

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler('socialnetwork', 'onLogIndexGetContent', 'cameras', '\Bitrix\Cameras\Integration\Socialnetwork\Log', 'onIndexGetContent');
		return true;
	}

	function UnInstallDB($request)
	{
		COption::RemoveOption('cameras');
		UnRegisterModule('cameras');
		UnRegisterModuleDependences('main', 'OnAddRatingVote', 'cameras', 'CRatingsComponentsCameras', 'OnAddRatingVote');
		UnRegisterModuleDependences('main', 'OnCancelRatingVote', 'cameras', 'CRatingsComponentsCameras', 'OnCancelRatingVote');
		UnRegisterModuleDependences('search', 'BeforeIndex', 'cameras', 'CRatingsComponentsCameras', 'BeforeIndex');
		UnRegisterModuleDependences('socialnetwork', 'BeforeIndexSocNet', 'cameras', 'CCamerasSocNet', 'BeforeIndexSocNet');
		UnRegisterModuleDependences("im", "OnGetNotifySchema", "cameras", "CCamerasNotifySchema", "OnGetNotifySchema");

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler('socialnetwork', 'onLogIndexGetContent', 'cameras', '\Bitrix\Cameras\Integration\Socialnetwork\Log', 'onIndexGetContent');
        if($request["savedata"] != "Y") {
            global $DB;
            $DB->StartTransaction();
            if (!CIBlockType::Delete('cameras')) {
                $DB->Rollback();
                echo 'Delete error!';
            } else {
                echo 'success';
            }
            $DB->Commit();
        }
		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/cameras/install/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin', true, true);
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/cameras/install/images', $_SERVER['DOCUMENT_ROOT'].'/bitrix/images/cameras', true, true);
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/cameras/install/themes', $_SERVER['DOCUMENT_ROOT'].'/bitrix/themes', true, true);
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/cameras/install/components', $_SERVER['DOCUMENT_ROOT'].'/bitrix/components', true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/cameras/install/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin');
		DeleteDirFilesEx('/bitrix/images/cameras/');
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/cameras/install/themes/.default/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/themes/.default');//css
		DeleteDirFilesEx('/bitrix/themes/.default/icons/cameras/');//icons
		return true;
	}

	function DoInstall()
	{
		global $DB, $APPLICATION, $step;
		$step = IntVal($step);

		if(!CBXFeatures::IsFeatureEditable('Cameras'))
		{
			$this->error = GetMessage('MAIN_FEATURE_ERROR_EDITABLE');
			$GLOBALS['errors'] = $this->error;
			$APPLICATION->IncludeAdminFile(GetMessage('CAMERAS_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/cameras/install/step3.php');
		}
		elseif ($step < 2)
			$APPLICATION->IncludeAdminFile(GetMessage('CAMERAS_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/cameras/install/step.php');
		elseif ($step == 2)
			$APPLICATION->IncludeAdminFile(GetMessage('CAMERAS_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/cameras/install/step2.php');
		else
		{
			$this->InstallDB();
			$this->InstallFiles();
			CBXFeatures::SetFeatureEnabled('Cameras', true);
			$APPLICATION->IncludeAdminFile(GetMessage('CAMERAS_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/cameras/install/step3.php');
		}
	}

	function DoUninstall()
	{
        global $APPLICATION, $DB, $step;
        $step = IntVal($step);
        if($step<2)
            $APPLICATION->IncludeAdminFile(GetMessage('CAMERAS_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/cameras/install/unstep1.php');
        elseif($step==2)
        {
            if (CModule::IncludeModule('socialnetwork'))
            {
                require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/cameras/include.php');
                CCamerasSocnet::EnableSocnet(false);
            }

            $this->UnInstallDB(array(
                "savedata" => $_REQUEST["savedata"],
            ));
            $this->UnInstallFiles();

            $APPLICATION->IncludeAdminFile(GetMessage('CAMERAS_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/cameras/install/unstep2.php');
        }

	}

	function GetModuleRightList()
	{
		$arr = array(
			'reference_id' => array('D', 'R', 'W', 'Y'),
			'reference' => array(
					'[D] '.GetMessage('CAMERAS_PERM_D'),
					'[R] '.GetMessage('CAMERAS_PERM_R'),
					'[W] '.GetMessage('CAMERAS_PERM_W'),
					//'[X] '.GetMessage('CAMERAS_PERM_X'),
					'[Y] '.GetMessage('CAMERAS_PERM_Y'),
					//'[Z] '.GetMessage('CAMERAS_PERM_Z')
				)
			);
		return $arr;
	}

}
?>