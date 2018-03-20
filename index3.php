<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?
$module_id = 'cameras';
$socnet_iblock_id_all = COption::GetOptionString($module_id, 'socnet_iblock_id_all');
$socnet_iblock_id_by_group = COption::GetOptionString($module_id, 'socnet_iblock_id_by_group');
echo '$socnet_iblock_id_all ' . $socnet_iblock_id_all . '<br>';
echo '$socnet_iblock_id_by_group ' . $socnet_iblock_id_by_group . '<br>';
die();
$arFields = Array(
    "NAME" => "Топ оптовых продаж",
    "ACTIVE" => "Y",
    "SORT" => "98",
    "CODE" => "top_opt",
    "PROPERTY_TYPE" => "N",
    "IBLOCK_ID" => 40,
    "SEARCHABLE" => "Y",
    "LIST_TYPE" => "C",
    "FILTRABLE" => "Y"
);


$ibp = new CIBlockProperty;
$PropID = $ibp->Add($arFields);


/*SELECT
    BE.ID as ID,BE.IBLOCK_ID as IBLOCK_ID,BE.NAME as NAME, FPV0.VALUE as PROPERTY_CAMERA_ID_VALUE, FPV0.ID as PROPERTY_CAMERA_ID_VALUE_ID,BE.SORT as SORT
FROM b_iblock B
INNER JOIN b_lang L ON B.LID=L.LID
INNER JOIN b_iblock_element BE ON BE.IBLOCK_ID = B.ID
LEFT JOIN b_iblock_property FP0 ON FP0.IBLOCK_ID = B.ID AND FP0.CODE='CAMERA_ID'
LEFT JOIN b_iblock_element_property FPV0 ON FPV0.IBLOCK_PROPERTY_ID = FP0.ID AND FPV0.IBLOCK_ELEMENT_ID = BE.ID
WHERE 1=1 AND ( ((((BE.ID = '680')))) ) AND (((BE.WF_STATUS_ID=1 AND BE.WF_PARENT_ELEMENT_ID IS NULL)))
ORDER BY BE.SORT asc*/

$element = new CIBlockElement;
$PROP = array();
$PROP['FORUM_TOPIC_ID_TEST']['VALUE']['TYPE'] = 'text';
$PROP['FORUM_TOPIC_ID_TEST']['VALUE']['TEXT'] = 'value text';
$arLoadArray = array(
    "IBLOCK_ID"      => 40,
    "PROPERTY_VALUES"=> $PROP,
    "NAME"           => "Название элемента"
);
$element->Add($arLoadArray);
?>

<!--SELECT
    BE.ID as ID,
    BE.IBLOCK_ID as IBLOCK_ID,
    BE.NAME as NAME,
    FPV0.VALUE as PROPERTY_CAMERA_ID_VALUE,
    FPV0.ID as PROPERTY_CAMERA_ID_VALUE_ID,
    BE.SORT as SORT
FROM
    b_iblock B
INNER JOIN b_lang L
    ON
        B.LID=L.LID
INNER JOIN b_iblock_element BE
    ON
        BE.IBLOCK_ID = B.ID
LEFT JOIN b_iblock_property FP0
    ON
        FP0.IBLOCK_ID = B.ID AND FP0.CODE='CAMERA_ID'
LEFT JOIN b_iblock_element_property FPV0
    ON
        FPV0.IBLOCK_PROPERTY_ID = FP0.ID
        AND FPV0.IBLOCK_ELEMENT_ID = BE.ID
WHERE 1=1
    AND ( ((((BE.ID = '680')))) )
    AND (((BE.WF_STATUS_ID=1 AND BE.WF_PARENT_ELEMENT_ID IS NULL)))
ORDER BY BE.SORT asc-->


<!--SELECT BE.ID as ID,
    BE.NAME as NAME,
    BE.SORT as SORT,
    DATE_FORMAT(BE.TIMESTAMP_X, '%d.%m.%Y %H:%i:%s') as TIMESTAMP_X,
    UNIX_TIMESTAMP(BE.TIMESTAMP_X) as TIMESTAMP_X_UNIX,
    BE.MODIFIED_BY as MODIFIED_BY,
    DATE_FORMAT(BE.DATE_CREATE, '%d.%m.%Y %H:%i:%s') as DATE_CREATE,
    UNIX_TIMESTAMP(BE.DATE_CREATE) as DATE_CREATE_UNIX,
    BE.CREATED_BY as CREATED_BY,
    BE.IBLOCK_ID as IBLOCK_ID,
    BE.IBLOCK_SECTION_ID as IBLOCK_SECTION_ID,
    BE.ACTIVE as ACTIVE,
    IF(
        EXTRACT(HOUR_SECOND FROM BE.ACTIVE_FROM)>0,
        DATE_FORMAT(BE.ACTIVE_FROM, '%d.%m.%Y %H:%i:%s'),
        DATE_FORMAT(BE.ACTIVE_FROM, '%d.%m.%Y')
        ) as ACTIVE_FROM,
    IF(
        EXTRACT(HOUR_SECOND FROM BE.ACTIVE_TO)>0,
        DATE_FORMAT(BE.ACTIVE_TO, '%d.%m.%Y %H:%i:%s'),
        DATE_FORMAT(BE.ACTIVE_TO, '%d.%m.%Y')
    ) as ACTIVE_TO,
    IF(
        EXTRACT(HOUR_SECOND FROM BE.ACTIVE_FROM)>0,
        DATE_FORMAT(BE.ACTIVE_FROM, '%d.%m.%Y %H:%i:%s'),
        DATE_FORMAT(BE.ACTIVE_FROM, '%d.%m.%Y')
    ) as DATE_ACTIVE_FROM,
    IF(
        EXTRACT(HOUR_SECOND FROM BE.ACTIVE_TO)>0,
        DATE_FORMAT(BE.ACTIVE_TO, '%d.%m.%Y %H:%i:%s'),
        DATE_FORMAT(BE.ACTIVE_TO, '%d.%m.%Y')) as DATE_ACTIVE_TO,
        BE.PREVIEW_PICTURE as PREVIEW_PICTURE,
        BE.PREVIEW_TEXT as PREVIEW_TEXT,
        BE.PREVIEW_TEXT_TYPE as PREVIEW_TEXT_TYPE,
        BE.DETAIL_PICTURE as DETAIL_PICTURE,
        BE.DETAIL_TEXT as DETAIL_TEXT,
        BE.DETAIL_TEXT_TYPE as DETAIL_TEXT_TYPE,
        BE.SEARCHABLE_CONTENT as SEARCHABLE_CONTENT,
        BE.WF_STATUS_ID as WF_STATUS_ID,
        BE.WF_PARENT_ELEMENT_ID as WF_PARENT_ELEMENT_ID,
        BE.WF_LAST_HISTORY_ID as WF_LAST_HISTORY_ID,
        BE.WF_NEW as WF_NEW,
        if(BE.WF_DATE_LOCK is null, 'green',
        if(DATE_ADD(BE.WF_DATE_LOCK, interval 60 MINUTE)-->
