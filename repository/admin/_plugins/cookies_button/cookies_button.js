$(document).ready(function() {
    
    // ====== Reset Cookies Button ======
    $reset_cookies_button = $("article.cookies_alert .reset button");
    if($reset_cookies_button.length) {
        var cookies = document.cookie.split(";").map(function(e){return e.trim();});
        // Cookies exiets
        if(cookies.indexOf("cookies_alert=done") > -1) {
            $reset_cookies_button.click(function() {
                $(this).blur();
                action = $(this).attr("href").split(":").pop();
                if(action == "cookies-reset") {
                    setCookie("cookies_alert", "false", -1);
                    alert("Cookie deleted!")
                }
                $(this).unbind("click").css({ "opacity": 0.3 });
            })
        }
        // No cookie to reset
        else {
            $reset_cookies_button.css({ "opacity": 0.3 });
        };
    };
    
})