$(document).ready(function() {

    // ==========================================
    //               Info popup
    // ==========================================
    
    $("#loader").fadeOut(200, function() { infoPopup( message, icon ); });
    $("#popup_container").delay(200).fadeIn(500);
    
    $("button.cancel").click(function() {
        href = $(this).attr("href");
        $("#page_fader, #popup_container").fadeOut(500);
        setTimeout(function() { location.href = href; }, 500);
        $(this).blur();
        return false;
    });
    
    if($("input#find").length) {
        find = $("input#find").val();
        $(".found-text").each(function() {
            text = $(this).text();
            pattern = new RegExp(find, "gi")
            text = text.replace(pattern, "<span class='highlight'>" + find + "</span>");
            //alert(text);
            $(this).html(text);
        });
    };
    
    $("button.search").click(function() {
        find = $("input#find").val();
        href = location.href.split("find=").shift() + "find=" + encodeURIComponent(find);
        setTimeout(function() { location.href = href; }, 500);
        $(this).blur();
        return false;
    });

});