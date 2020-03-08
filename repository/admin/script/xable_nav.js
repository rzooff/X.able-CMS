function resizeCode() {
    $("#code").css({ "min-height": "100vh" });
    $("main").css({ "min-height": "100vh" });
    if($("#code").height() < $("main").height()) {
        $("#code").css({ "min-height": $("main").height() });
    }
    else {
        $("main").css({ "min-height": $("aside").height() }); // "#code" is inside "aside" (padding-top)
    }
}


$(document).ready(function() {

    setTimeout(function() { resizeCode(); }, 500);
    setTimeout(function() { resizeCode(); }, 1000);

    // ==========================================
    //                 Menu bar
    // ==========================================

	// Show menu dropdown
    $("nav label.menu").mouseenter(function() {
        $(this).find("ul").stop().show(200);
        $(this).find("p").css({ "opacity": "0.5" });
        $(".fake_cover").show();
    });

	// Hide menu dropdown
    $("nav label.menu").mouseleave(function() {
        $(this).find("ul").stop().hide(100);
        $(this).find("p").css({ "opacity": "1" });
        $(".fake_cover").hide();
    });
    
    // Touch screen fix
    $(".fake_cover").click(function() {
        $("nav label.menu ul").stop().hide(100);
        $("nav label.menu p").css({ "opacity": "1" });
        $(".fake_cover").hide();
    })
    
    $("#view_switch").click(function() {
        if($("aside").css("display") == "none") {
            $("aside").stop().css({ "left": "100%" }).show().animate({ "left": 0}, 250);
            $("body").css({ "overflow-y": "hidden" });
        }
        else {
            $("aside").stop().animate({ "left": "100%" }, 250, function() { $(this).hide(); });
            $("body").css({ "overflow-y": "auto" });
        }
    })
    
    $(window).resize(function() {
        if($("aside").css("position") != "fixed" && $("aside").attr("style") != "") {
            $("aside").attr("style", "");
        };
        resizeCode();
    })
    
})