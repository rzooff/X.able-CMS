$(document).ready(function() {

	// Add names to all inputs
	$("form select, form input").each(function() { $(this).attr("name", $(this).attr("class")); });

    // ==========================================
    //                 Menu bar
    // ==========================================
    
	// Menu actions
	$("nav li").click(function() {
		// Hide dropdown
        $(this).closest("label").find("ul").stop().hide(100);
        $(this).closest("label").find("p").css({ "opacity": "1" });
        // Actions
		action = $(this).attr("value");
		if(action == "quit") {
            location.href = "index.php";
        }
		else if(action == "users" || action == "creator" || action == "explorer") {
            location.href = "xable_" + action + ".php";
        }
        else if(action == "separator") {
            return false;
        }
        else {
            alert("Unimplemented: " + action)
        };
    });
        
    // ==========================================
    //              Advanced Tools
    // ==========================================
    
    $("#advanced_tools a").click(function() {
        href = $(this).attr("href");
        win_wid = $(window).width();
        if(win_wid > 1200) { win_wid = win_wid * 0.5; }
        else if(win_wid > 800) { win_wid = win_wid * 0.7; }
        else { win_wid = "100%"; };
        win_hei = $(window).height()
        if(win_hei > 600) { win_hei = win_hei * 0.85; }
        else { win_hei = "100%"; };        
        //window.open(href);
        window.open(href, "advanced_tools", "width=" + win_wid + ",height=" + win_hei);
        return false;
    })
        
    // ==========================================
    //                   Edit
    // ==========================================
    
    $("article, section").fadeIn(200);
    
    $("details").click(function() { $(this).focusout(); });
    
    $("#create button").click(function() {
        $("#working_info").fadeIn(500, function() {
            location.href = "xable_update.php?action=installer";
        })
    });
    
    $("#code .remove").click(function() {
        if(confirm(LOCALIZE["zip-delete-confirm"])) {
            file = $(this).attr("value");
            location.href = "xable_update.php?remove=" + encodeURI(file);
        }
        else {
            $(this).blur();
        }
    });
    
    $("#update form input.installer").change(function() {
        val = $(this).val().replace(/\\/g, "/");
        if(val.path("extension") != "zip" || val.path("filename").toLowerCase().indexOf("xable") < 0) {
            alert(LOCALIZE["not-xable-zip"]);
            $(this).val("");
        }
    });
    
    $("#update button").click(function() {
        val = $(this).closest("form").find("input").val();
        if(val == "") {
            alert(LOCALIZE["select-zip-alert"]);
            return false;
        }
        else if(confirm(LOCALIZE["add-zip-confirm"])) {
            $("#working_info p").text(LOCALIZE["upload-in-progress"]);
            $("#working_info").fadeIn(500, function() { return true; });
        }
        else {
            $(this).blur();
            return false;
        }
    });

    // ==========================================
    //                Update CMS
    // ==========================================

    if($("#action_done").val() == "updated") {
        $("#working_info p").text(LOCALIZE["update-label"]);
        $("#working_info").fadeIn(500, function() {
            setTimeout(function() {
                location.href = "_logout.php?info=" + encodeURI(LOCALIZE["logout-info"]) + "&back_url=" + encodeURI("index.php?popup=" + LOCALIZE["cms-updated-info"]);
                //_logout.php?info=<logout-info>&back_url=index.php%3Fpopup%3D<cms-updated-info>
            }, 3000);
        })
    };

    // ==========================================
    //                Changelog
    // ==========================================
    
    $("#changelog .preview").click(function() {
        path = $("#changelog input.path").val();
        changes = $("#changelog textarea").val();
        popup = "<div id='popup_container'>" +
            "<div class='popup' id='log_preview'>" +
                "<nav>" +
                    "<p>" + LOCALIZE["changelog-preview"] + "</p>" +
                    "<div class='buttons'><button class='cancel'><span class='fi-x'></span></button></div>" +
                "</nav>" +
                "<form action='xable_update.php?action=" + "changelog_preview" + "' method='post'>" +
                    "<input type='hidden' name='path' value='" + encodeURI(path) + "'>" +
                    "<p>" + changes + "</p>" +
                "</form>" +
            "</div>" +
        "</div>";
        $("main").append(popup);
        $("#popup_container").fadeIn(200, function() {
            //$('#log_preview form').scrollTop($('#log_preview form')[0].scrollHeight);
            
            $("#log_preview form").animate({
                scrollTop: $('#log_preview form')[0].scrollHeight
            }, 500);
            
            $("#popup_container .cancel").click(function() {
                $("#popup_container").fadeOut(200, function() { $(this).remove(); });
                return false;
            });
        });
    });
    
    $("#changelog .add").click(function() {
        path = $("#changelog input.path").val();
        popup = "<div id='popup_container'>" +
            "<div class='popup' id='add_log'>" +
                "<nav>" +
                    "<p>" + LOCALIZE["changelog-new"] + "</p>" +
                    "<div class='buttons'><button class='cancel'><span class='fi-x'></span></button></div>" +
                "</nav>" +
                "<form action='xable_update.php?action=" + "changelog" + "' method='post'>" +
                    "<input type='hidden' name='path' value='" + encodeURI(path) + "'>" +
                    "<p>" + LOCALIZE["date-time-label"] + "</p>" + 
                    "<input name='time' type='text' class='time'>" +
                    "<p>" + LOCALIZE["description-label"] + "</p>" + 
                    "<textarea name='info' class='info'></textarea>" +
                    "<p>" + LOCALIZE["files-label"] + "</p>" + 
                    "<input name='files' type='text' class='files'>" +
                    "<div class='center'><button class='confirm'>" + LOCALIZE["save-label"] + "</button></div>" +
                "</form>" +
            "</div>" +
        "</div>";
        $("main").append(popup);
        $("#popup_container").fadeIn(200, function() {
            $("#add_log textarea").focus(); //.animate({ scrollTop: 0 }).focus();
            $("#add_log .time").val( currentTime("yyyy-mm-dd, ho:mi:se") );
            
            $("#add_log .confirm").click(function() {
                if($("#add_log .time").val() != "" && $("#add_log .info").val() != "" && $("#add_log .files").val() != "") {
                    $("#popup_container").fadeOut(200);
                    return true;
                }
                else {
                    alert(LOCALIZE["missing-data-alert"]);
                    $("#add_log .info").focus();
                    return false;
                }
            });
            
            $("#add_log .cancel").click(function() {
                $("#popup_container").fadeOut(200, function() { $(this).remove(); });
                return false;
            });
        });
    });
    
    urlQuery();
});