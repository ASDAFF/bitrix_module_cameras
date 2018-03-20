<?
IncludeModuleLangFile(__FILE__);

class CCamerasNotifySchema
{
	public function __construct()
	{
	}

	public static function OnGetNotifySchema()
	{
		return array(
			"cameras" => array(
				"comment" => Array(
					"NAME" => GetMessage("CAMERAS_NS_COMMENT"),
				),
/*
				"mention" => Array(
					"NAME" => GetMessage("CAMERAS_NS_MENTION"),
				),
*/
			),
		);
	}
}