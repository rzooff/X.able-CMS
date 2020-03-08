$(document).ready(function() {
    
    var no_preview_mode = "xable-nopreview/";
    
    // Move style to Head
    $style = $("#preview_mode_container .preview_mode_style");
    $("head").append($style.clone());
    $style.remove();
    
    // ==========================================
    //         Preview mode info popup
    // ==========================================
    if($("input#preview_mode").length && $("input#preview_mode").val() == "draft") {
        html = "<div id='preview_info'>\n" +
            "\t<span class='status'>&#9679;</span>\n" +
            "\t<p><b>X.able</b> | Draft preview</p>\n" +
            "\t<button>Wyłącz podgląd</button>\n" +
            "</div>\n";
        $("body").append(html);
        
        if($("input#preview_mode_status").length) {
            status = $("input#preview_mode_status").val().split(";");
            if(status.indexOf("unpublished") > -1) {
                status = "unpublished";
            }
            else if(status.indexOf("edited") > -1) {
                status = "edited";
            }
            else {
                status = "published";
            }
            //alert(status);
            $("#preview_info span").addClass("color-" + status);
        }

        // back to non-preview page
        $("#preview_info button").click(function() {
            if(jQuery.type(CURRENT_PAGE) == "string") {
                //alert(ROOT + no_preview_mode + CURRENT_PAGE + "/")
                location.href = ROOT + no_preview_mode + CURRENT_PAGE + "/";
            };
        });
    };

});