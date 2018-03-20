$(function() {

    $(".item-camera-available-image").on("click", function(){
        console.log('click item-camera-available-image');
        showCurrentCameraAsMain($(this));
    });
    $(".item-camera-available-text").on("click", function(){
        showCurrentCameraAsMain($(this));
    });
    function showCurrentCameraAsMain(elem){
        $(".main-cameras-view").css("display", "block");
        var link = elem.closest(".item-camera-available").attr("data-link");
        console.log('link - ' + link);
        $(".main-cameras-view iframe").attr("src", link);
    }
});