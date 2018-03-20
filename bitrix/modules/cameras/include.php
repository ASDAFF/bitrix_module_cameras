<?

if(!CModule::IncludeModule('iblock'))
	return false;

IncludeModuleLangFile(__FILE__);


CModule::AddAutoloadClasses(
	'cameras',
	array(
		'CCameras' => 'classes/general/cameras.php',
		'CCamerasUtils'  => 'classes/general/cameras_utils.php',
		'CCamerasParser' => 'classes/general/cameras_parser.php',
		'CCamerasSocnet' => 'classes/general/cameras_socnet.php',
		'CCamerasCategories' => 'classes/general/cameras_categories.php',
		'CCamerasCategoryParams' => 'classes/general/cameras_categories.php'
	)
);

?>