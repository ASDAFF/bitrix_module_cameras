<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/*$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetPageProperty("title", htmlspecialcharsbx(COption::GetOptionString("main", "site_name", "Bitrix24")));*/
?>
<?

/*echo '<br><br>extranet gusergroup<br><br>';
$nameTemplate = CSite::GetNameFormat(false);
echo '$nameTemplate  ' . $nameTemplate . '<br>';
$_POST['DEPARTMENT_ID'] = '105';
$ar1 = Array(
    'USERS' => CSocNetLogDestination::GetUsers(Array('deportament_id' => $_POST['DEPARTMENT_ID'], "NAME_TEMPLATE" => $nameTemplate)),
);

echo ' $ar1 <pre>';
print_r($ar1);
echo '</pre>';


echo '<br><br>intranet gusergroup<br><br>';
$nameTemplate = CSite::GetNameFormat(false);
echo '$nameTemplate  ' . $nameTemplate . '<br>';
$_POST['DEPARTMENT_ID'] = '92';
$ar1 = Array(
    'USERS' => CSocNetLogDestination::GetUsers(Array('deportament_id' => $_POST['DEPARTMENT_ID'], "NAME_TEMPLATE" => $nameTemplate)),
);

echo ' $ar1 <pre>';
print_r($ar1);
echo '</pre>';*/

require_once($_SERVER["DOCUMENT_ROOT"]."/local/components/bitrix/socialnetwork.group_create.ex.add_structure/include.php");

CModule::IncludeModule("socialnetwork");

CSocNetAllowed::RunEventForAllowedFeature();



exit('333');
$arFilter= array();
$dbUsers = CUser::GetList(
    ($sort_by = array('last_name'=> 'asc', 'IS_ONLINE'=>'desc')),
    ($dummy=''),
    $arFilter,
    array('ID', 'NAME'/*, "UF_DEPARTMENT"*/)
);

while ($arUser = $dbUsers->GetNext())
{
    /*echo ' $arUser1 <pre>';
    print_r($arUser);
    echo '</pre>';*/
}

$order = array('sort' => 'asc');
$tmp = 'sort'; // параметр проигнорируется методом, но обязан быть
$arParameters["SELECT"] = array(
        'ID', '*', 'UF_*'

);
$rsUsers = CUser::GetList($order, $tmp, array(), $arParameters);

while ($arUser = $rsUsers->GetNext())
{
    /*echo ' $arUser2 <pre>';
    print_r($arUser);
    echo '</pre>';*/
}

// получит символьный код родительской группы
/*
$arFilter = Array('ID'=>190);
$db_list = CIBlockSection::GetList(Array($by=>$order), $arFilter, true);

while($ar_result = $db_list->GetNext())
{
    echo ' $ar_result 3 <pre>';
    print_r($ar_result);
    echo '</pre>';
}*/
// departments_extranet IBLOCK_CODE

/*
$rsParentSection = CIBlockSection::GetByID(190);
if ($arParentSection = $rsParentSection->GetNext())
{
   echo ' $arParentSection <pre>';
   print_r($arParentSection);
   echo '</pre>';
}*/
$nameTemplate = CSite::GetNameFormat(false);
$arParams['deportament_id'] = 190;

$arParams["NAME_TEMPLATE"] = $nameTemplate;
$arFilter = array();
$arFilter['UF_DEPARTMENT_EXTR'] = intval($arParams['deportament_id']);

$arExtParams = Array(
    "FIELDS" => array("ID", "LAST_NAME", "NAME", "SECOND_NAME", "LOGIN", "EMAIL", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION", "IS_ONLINE", "EXTERNAL_AUTH_ID"),
    "SELECT" => array('UF_DEPARTMENT')
);

$dbUsers = CUser::GetList(
    ($sort_by = array('last_name'=> 'asc', 'IS_ONLINE'=>'desc')),
    ($dummy=''),
    $arFilter,
    $arExtParams
);

while ($arUser = $dbUsers->GetNext()) {
    echo ' $arUser <pre>';
    print_r($arUser);
    echo '</pre>';
    if (
        !$bSelf
        && is_object($USER)
        && $userId == $arUser["ID"]
    ) {
        continue;
    }

    if (
        !isset($arFilter['UF_DEPARTMENT']) // all users
        && $bExtranetInstalled
    ) {
        if (
            isset($arUser["UF_DEPARTMENT"])
            && (
                !is_array($arUser["UF_DEPARTMENT"])
                || empty($arUser["UF_DEPARTMENT"])
                || intval($arUser["UF_DEPARTMENT"][0]) <= 0
            ) // extranet user
            && (
                empty($arUserIdVisible)
                || !is_array($arUserIdVisible)
                || !in_array($arUser["ID"], $arUserIdVisible)
            )
        ) {
            continue;
        }
    }

    $sName = trim(CUser::FormatName(empty($arParams["NAME_TEMPLATE"]) ? CSite::GetNameFormat(false) : $arParams["NAME_TEMPLATE"], $arUser, true, false));

    if (empty($sName)) {
        $sName = $arUser["~LOGIN"];
    }

    $arFileTmp = CFile::ResizeImageGet(
        $arUser["PERSONAL_PHOTO"],
        $avatarSize,
        BX_RESIZE_IMAGE_EXACT,
        false
    );

    $arUsers['U' . $arUser["ID"]] = Array(
        'id' => 'U' . $arUser["ID"],
        'entityId' => $arUser["ID"],
        'name' => $sName,
        'avatar' => empty($arFileTmp['src']) ? '' : $arFileTmp['src'],
        'desc' => $arUser['WORK_POSITION'] ? $arUser['WORK_POSITION'] : ($arUser['PERSONAL_PROFESSION'] ? $arUser['PERSONAL_PROFESSION'] : '&nbsp;'),
        'isExtranet' => (in_array($arUser["ID"], $extranetUserIdList) ? "Y" : "N"),
        'isEmail' => ($arUser['EXTERNAL_AUTH_ID'] == 'email' ? 'Y' : 'N'),
        'isCrmEmail' => (
        $arUser['EXTERNAL_AUTH_ID'] == 'email'
        && !empty($arUser['UF_USER_CRM_ENTITY'])
            ? 'Y'
            : 'N'
        )
    );

    if ($arUser['EXTERNAL_AUTH_ID'] == 'email') {
        $arUsers['U' . $arUser["ID"]]['email'] = $arUser['EMAIL'];
    }

    $arUsers['U' . $arUser["ID"]]['checksum'] = md5(serialize($arUsers['U' . $arUser["ID"]]));

    if (defined("BX_COMP_MANAGED_CACHE")) {
        $CACHE_MANAGER->RegisterTag("USER_NAME_" . IntVal($arUser["ID"]));
    }
    echo ' $arUsers end <pre>';
    print_r($arUsers);
    echo '</pre>';
}
?>






<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>