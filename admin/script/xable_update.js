$(document).ready(function() {
    
    urlQuery();
	
	// Add names to all inputs
	$("form select, form input").each(function() { $(this).attr("name", $(this).attr("class")); });
	
    // ==========================================
    //                 Menu bar
    // ==========================================

	// Show menu dropdown
    $("nav label.menu").mouseenter(function() {
        $(this).find("ul").stop().show(200);
        $(this).find("p").css({ "opacity": "0.25" });
    });

	// Hide menu dropdown
    $("nav label.menu").mouseleave(function() {
        $(this).find("ul").stop().hide(100);
        $(this).find("p").css({ "opacity": "1" });
    });
    
	// Menu actions
	$("nav li").click(function() {
		// Hide dropdown
        $(this).closest("label").find("ul").stop().hide(100);
        $(this).closest("label").find("p").css({ "opacity": "1" });
        // Actions
		action = $(this).html().replace(/&nbsp;/g, "_").toLowerCase();
		if(action == "quit") {
            location.href = "index.php";
        }
		else if(action == "users" || action == "creator" || action == "explorer") {
            location.href = "xable_" + action + ".php";
        }
        else {
            alert("Unimplemented: " + action)
        };
    });
        
    // ==========================================
    //                   Edit
    // ==========================================
    
    $("article, section").fadeIn(200);
    
    $("details").click(function() { $(this).focusout(); });
    
    $("#create button").click(function() {
        location.href = "xable_update.php?action=installer"; 
    });
    
    $("#code .remove").click(function() {
        if(confirm("Delete archive file.\nAre you sure?")) {
            file = $(this).attr("value");
            location.href = "xable_update.php?remove=" + encodeURI(file);
        }
        else {
            $(this).blur();
        }
    });
    
    $("#update button").click(function() {
        val = $(this).closest("form").find("input").val();
        if(val == "") {
            alert("Select package to install");
            return false;
        }
        else if(confirm("Upload and install update.\nAre you sure?")) {
            return true;
        }
        else {
            $(this).blur();
            return false;
        }
    });
    
    // ==========================================
    //                Changelog
    // ==========================================
    
    $("#changelog .preview").click(function() {
        path = $("#changelog input.path").val();
        changes = $("#changelog textarea").val();
        popup = "<div id='popup_container'>" +
            "<div class='popup' id='log_preview'>" +
                "<nav>" +
                    "<p>Changelog Preview</p>" +
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
                    "<p>Update Changelog</p>" +
                    "<div class='buttons'><button class='cancel'><span class='fi-x'></span></button></div>" +
                "</nav>" +
                "<form action='xable_update.php?action=" + "changelog" + "' method='post'>" +
                    "<input type='hidden' name='path' value='" + encodeURI(path) + "'>" +
                    "<p>Time</p>" + 
                    "<input name='time' type='text' class='time'>" +
                    "<p>Info</p>" + 
                    "<textarea name='info' class='info'></textarea>" +
                    "<p>Files</p>" + 
                    "<input name='files' type='text' class='files'>" +
                    "<div class='center'><button class='confirm'>Save</button></div>" +
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
                    alert("Fill missing data.");
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
    
});