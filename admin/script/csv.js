$(document).ready(function() {
    
    $("head").append("<link rel='stylesheet' type='text/css' href='style/csv.css' />");
    
    // ====== Nav slider ======
    marg = 40;
    $("body").css({ "padding-left": marg });
    nav_wid = $("nav").width();
    
    function initializeNavShow() {
        $("#show_nav").mouseenter(function() {
            $("nav").stop().animate({ "left": 0}, 500 );
            $("#help_popup").hide();
        });
    };

    function initializeNavHide() {
        $("nav").mouseleave(function() {
            $("nav").animate({ "left": nav_wid * -1 }, 200);
            $("#help_popup").hide();
        });
    };
    
    // ====== Hide Nav ======
    $("nav").delay(500).animate({ "left": nav_wid * -1 }, 500, function() { initializeNavShow(); initializeNavHide(); });
    
    
});