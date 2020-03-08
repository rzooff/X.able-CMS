$(document).ready(function() {
    
    // Move style to Head
    $style = $(".plugin-cookies-alert .plugin_style");
    $("head").append($style.clone());
    $style.remove();
    
    // ==========================================
    //               Cookies alert
    // ==========================================

    //setCookie("cookies_alert", "false", -1) // hard reset for testing
    
    // Load cookies
    var cookies = document.cookie.split(";").map(function(e){return e.trim();});
    // Check alert done cookie
    if(cookies.indexOf("cookies_alert=done") > -1) {
        $("#cookies_popup").hide();
    }
    else {
        $("#cookies_popup").show();
    };
    // Close action
    $("#cookies_popup button").click(function() {
        $(this).blur();
        $("#cookies_popup").fadeOut(500, function() {
            setCookie("cookies_alert", "done", 365)
        });
    });
    
})