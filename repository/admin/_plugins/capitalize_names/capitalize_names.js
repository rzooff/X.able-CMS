$(document).ready(function() {

    var CAPITALIZE_NAV_GROUP = [ "pages/zespol" ];

    // ==========================================
    //          Capitalize Nav Titles
    // ==========================================
    
    var persons = {};
    var tests = {};
    //alert(CAPITALIZE_NAV_GROUP[0]);
    
    function capitalizeNav() {
        $("nav dl div.group a").each(function() {
            href = $(this).attr("href");
            $dd = $(this).find("dd");
            text = $dd.text();

            for(i = 0; i < CAPITALIZE_NAV_GROUP.length; i++) {
                group = CAPITALIZE_NAV_GROUP[i];
                ext = href.split("/").pop().split("&").shift().split("?").shift().path("extension");
                if($dd.length && href.indexOf("/" + group + "/") > -1 && ext != "order") {
                    text = text.capitalize("title");
                    $dd.find(".nav_label").text(text);
                }
            }
        })
    };
    capitalizeNav();
    


})