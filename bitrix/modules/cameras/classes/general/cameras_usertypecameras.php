<?

IncludeModuleLangFile(__FILE__);

class CUserTypeCameras extends CUserTypeString
{
    function GetUserTypeDescription()
	{
		return array(
			'USER_TYPE_ID' => 'cameras',
			'CLASS_NAME' => 'CUserTypeCameras',
			'DESCRIPTION' => 'USER_TYPE_CAMERAS_DESCRIPTION', //TODO: Lang file
			'BASE_TYPE' => 'string',
		);
	}

	function CheckPermission()
	{
		if (!CModule::IncludeModule('cameras') || !CCamerasUtils::IsReadable())
			return false;

		return true;
	}
}
?>
