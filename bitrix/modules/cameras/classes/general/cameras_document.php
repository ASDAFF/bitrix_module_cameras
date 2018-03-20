<?

IncludeModuleLangFile(__FILE__);

class CCamerasDocument extends CIBlockDocument
{
	function CanUserOperateDocument($operation, $userId, $documentId, $arParameters = array())
	{    
		if (CCamerasSocnet::IsSocNet())
		{
			return CCamerasUtils::CheckAccess('write');
		}
		else
			return parent::CanUserOperateDocument($operation, $userId, $documentId, $arParameters);    
	}
}

?>