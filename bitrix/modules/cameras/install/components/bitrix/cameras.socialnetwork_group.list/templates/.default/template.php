<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$this->addExternalCss($this->GetFolder() . "/style.cameras.show.css");
$this->addExternalJS($this->GetFolder() . "/script.cameras.show.js");
?>
<div class="container-component-cameras-show">
    <div class="component-title">
        <h3>Доступные к просмотру камеры</h3>
    </div>
    <div class="main-cameras-view">
        <iframe width="860px" height="484px" src="" frameborder="0" allowfullscreen></iframe>
    </div>
    <div class="all-cameras-available">
    <?
        $elements_in_row_all = 4;
        $elements_in_row_counter = 0;
    ?>
        <? foreach($arResult["CAMERAS_ALL_ITEMS"] as $itemId => $item):?>
            <? if($elements_in_row_counter % $elements_in_row_all == 0):?>
            <div class="all-cameras-available-row">
            <? endif;?>
            <? $elements_in_row_counter++; ?>
                <div class="item-camera-available" data-link="<?= $item["PROPERTIES"]["LINK"]["VALUE"] ?>">
                    <div class="item-camera-available-image">
                        <img src="<? if(CFile::GetPath($item["PREVIEW_PICTURE"])) echo CFile::GetPath($item["PREVIEW_PICTURE"]); else echo SITE_TEMPLATE_PATH."/images/no_camera_image.png"; ?>">
                    </div>
                    <div class="item-camera-available-text">
                    <?= $item["NAME"] ?>
                    </div>
                </div>
            <? if($elements_in_row_counter == $elements_in_row_all):?>
            </div>
            <? endif;?>
        <? endforeach;?>
    </div>
</div>
<?

?>
