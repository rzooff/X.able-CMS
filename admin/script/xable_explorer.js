$(document).ready(function() {
    
    urlQuery();

    // ==========================================
    //           Hidden files / folders
    // ==========================================
    
    // ====== Mark Hidden ======
    $("table tr.path").each(function() {
        path = $(this).attr("path");
        if(path.split("/").pop().substr(0, 1) == ".") {
            $(this).addClass("hidden_path");
        };
    });
    
    // ====== Initialize hidden ======
    if($("form input.show_hidden").val() != "true") {
        $("table tr.hidden_path").fadeOut(250);
    }
    else {
        $("table tr.hidden_path").fadeIn(250);
        $("#show_hidden_trigger").prop("checked", true);
    };
    
    // ====== Trigger ======
    $("#show_hidden_trigger").change(function() {
        if($(this).prop("checked") == false) {
            $("table tr.hidden_path").hide();
            $("form input.show_hidden").val("false");
        }
        else {
            $("table tr.hidden_path").show();
            $("form input.show_hidden").val("true");
        };
    });
    

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
            edit_path = $("#root").val() + "/" + $("#current_file").val();
            editable = [ "csv", "draft", "order", "prev", "template", "txt", "xml" ];
            if(editable.indexOf(edit_path.path("extension"))) {
                location.href = "index.php?path=" + edit_path;
            }
            else {
                location.href = "index.php";
            };
        }
        else if($("#current_file").length && action == "creator") {
            edit_path = $("#root").val() + "/" + $("#current_file").val();
            editable = [ "draft", "template", "xml" ];
            if(editable.indexOf(edit_path.path("extension"))) {
                location.href = "xable_creator.php?open=" + edit_path;
            }
            else {
                location.href = "xable_creator.php";
            };
        }
		else if(action == "users" || action == "update" || action == "creator") {
            location.href = "xable_" + action + ".php";
        }
        else {
            alert("Unimplemented: " + action)
        };
    });
    
    // ==========================================
    //         Browser: explore / preview
    // ==========================================

    function doubleClick() {
        $("#explorer tr.path").unbind("click").click(function() {
            $clicked = $(this);
            // Double click
            $clicked.unbind("click").click(function () {
                goToClicked($clicked);
            });
            // Reset click
            setTimeout(function() {
                doubleClick();
            }, 300);
            $(this).blur;
            // Select/deselect
            $select = $(this).find("input.select");
            if($select.prop("checked") == false) {
                $select.prop("checked", true);
                $(this).addClass("selected");
            }
            else {
                $select.prop("checked", false);
                $(this).removeClass("selected");
            };
            $(this).blur();
            document.getSelection().removeAllRanges();
        });
    };
    doubleClick();

    function goToClicked($clicked) {
        path = $clicked.attr("path");
        if(jQuery.type(path) != "string") {
            // no action
        }
        else if(path.length > 1 && path.substr(0, 1) == "/") {
            href = "xable_explorer.php?path=" + path.substr(1);
            $("form").first().attr("action", href).submit();
        }
        else {
            href = "xable_explorer.php?path=" + path;
            $("form").first().attr("action", href).submit();
        };
    };
    
    // Level up button
    $("#explorer button.path").click(function() { goToClicked($(this)); $(this).blur(); });
    
    $("#explorer .deselect").click(function() {
        if($("#browser input.select:checked").length) {
            $("#browser input.select").prop("checked", false);
            $("#browser tr.path").removeClass("selected");
        }
        else {
            $("#browser input.select").prop("checked", true);
            $("#browser tr.path").addClass("selected");
        };
    });
    
    // ==========================================
    //           Browser: Context menu
    // ==========================================

    function contextPopup(x, y, path, functions) {
        functions = functions.split(",");
        var html = [];
        for(i in functions) {
            html[html.length] = "<li class='action' value='" + functions[i] + "'>" + functions[i].capitalize(1) + "</li>";
        };
        $("main").append("<ul id='context_menu'>\n\t" + html.join("\t\n") + "\n</ul>\n");
        $("#context_menu").css({ "left": x - 10, "top": y - 10 }).fadeIn(250);
        $("#context_menu").mouseleave(function() { hideContextPopup(); });
        $("#context_menu li").click(function() {
            val = $(this).attr("value");
            action = $("#browser form").attr("action");
            dir_list = $("#dir_list").val().split(";");
            if(val == "delete") {
                to_delete = [];
                $("#browser input.select:checked").each(function() {
                    to_delete[to_delete.length] = $(this).val();
                });
                if(confirm("Delete?\n-\n" + to_delete.join("\n"))) {
                    $("#browser form").attr("action", action.replace("@action", val)).submit();
                }
                else {
                    hideContextPopup();
                };
            }
            else if(val == "duplicate" || val == "rename") {
                $("main").append(getNamePopup(val));
                $("#popup_container").fadeIn(200, function() {
                    $("#popup_container input.filename").val( path.path("basename") ).focus();
                    $("#popup_container .confirm").click(function() {
                        input = $("#popup_container input.filename").val();
                        if(input == "") {
                            alert("Name can't be empty!");
                            $(this).blur();
                            $("#popup_container input.filename").focus();
                            return false;
                        }
                        else if(dir_list.indexOf(input) > -1) {
                            alert("Name already exists!");
                            $(this).blur();
                            $("#popup_container input.filename").focus();
                            return false;
                        }
                        else {
                            $("#browser form").append( $("#popup_container input").clone() );
                            $("#browser form").attr("action", action.replace("@action", val)).submit();
                            return false;
                        };
                    });
                    
                    $("#popup_container .cancel").click(function() {
                        $("#popup_container").fadeOut(200, function() { $(this).remove(); });
                        return false;
                    });
                });
            }
            else {
                $("#browser form").attr("action", action.replace("@action", val)).submit();
                return false;
            };
        });      

    };

    function hideContextPopup() {
        $("#context_menu").fadeOut(250, function() { $(this).remove(); });
        $("#browser tr.path").css({ "outline": "none" });
        document.getSelection().removeAllRanges();
    };
    
    $("#browser tr.path").contextmenu(function(e) {
        $(this).find("input.select").prop("checked", true);
        $(this).addClass("selected");
        var x = event.pageX;
        var y = event.pageY - $(window).scrollTop();
        var file_path = $(this).attr("path");
        if($("#browser form input:checked").length == 1) { functions = "copy,cut,delete,duplicate,rename"; }
        else { functions = "copy,cut,delete" }
        contextPopup(x, y, file_path, functions);
        $(this).blur();
        return false;
    });
    
    // ==========================================
    //              Browser: Actions
    // ==========================================
    
    function getNamePopup(action) {
        var popup = "<div id='popup_container'>" +
            "<div class='popup'>" +
                "<nav>" +
                    "<p>" + action.replace("_", " ").capitalize(1) + "</p>" +
                    "<div class='buttons'><button class='cancel'><span class='fi-x'></span></button></div>" +
                "</nav>" +
                "<form>" +
                    "<p>New filename:</p>" + 
                    "<input name='filename' type='text' class='filename'>" +
                    "<div class='center'><button class='confirm'>OK</button></div>" +
                "</form>" +
            "</div>" +
        "</div>";
        return popup;
    };
    
    function uploadPopup() {
        var popup = "<div id='popup_container'>" +
            "<div class='popup'>" +
                "<nav>" +
                    "<p>Upload file(s)</p>" +
                    "<div class='buttons'><button class='cancel'><span class='fi-x'></span></button></div>" +
                "</nav>" +
                "<form action='xable_explorer.php?action=upload' method='post' enctype='multipart/form-data'>" +
                    "<input type='hidden' name='path' value='" + $("#browser input.path").val() + "'>" +
                    "<p><input class='files' type='file' name='upload[]' multiple></p>" +
                    "<p><button class='confirm'>Upload</button></p>" +
                "</form>" +
            "</div>" +
        "</div>";
        $("main").append(popup);
        $("#popup_container").fadeIn(200, function() {
            // Confirm upload
            $("#popup_container .confirm").click(function() {
                var dir_list = $("#dir_list").val();
                overwrite = [];
                files = $("#popup_container input.files").get(0).files;
                if(files.length > 0) {
                    for (var i = 0; i < files.length; i++) {
                        name = files[i].name;
                        if(dir_list.indexOf(name) > -1) {
                            overwrite[overwrite.length] = name;
                        };
                    };
                    if(overwrite.length == 0 || (confirm("Overwite?\n-\n" + overwrite.join("\n")))) {
                        return true;
                    };
                    return false;
                }
                else {
                    alert("Nothing to upload!");
                    return false;
                }
            });
            // Canlec
            $("#popup_container .cancel").click(function() {
                $("#popup_container").fadeOut(200, function() { $(this).remove(); });
                return false;
            });
        });
    };
    $("#explorer .upload").click(function() { uploadPopup(); });
    
    $("#explorer button.new_folder, #explorer button.new_file").click(function() {
        val = $(this).attr("class");
        dir_list = $("#dir_list").val().split(";");
        $("main").append(getNamePopup(val));
        $("#popup_container").fadeIn(200, function() {
            $("#popup_container input.filename").focus();
            var dir_list = $("#dir_list").val().split(";");
            $("#popup_container .confirm").click(function() {
                input = $("#popup_container input.filename").val();
                if(input == "") {
                    alert("Name can't be empty!");
                    $(this).blur();
                    $("#popup_container input.filename").focus();
                    return false;
                }
                else if(dir_list.indexOf(input) > -1) {
                    alert("Name already exists!");
                    $(this).blur();
                    $("#popup_container input.filename").focus();
                    return false;
                }
                else {
                    $("#browser form").append( $("#popup_container input").clone() );
                    action = $("#browser form").attr("action");
                    $("#browser form").attr("action", action.replace("@action", val)).submit();
                    return false;
                };
            });
            
            $("#popup_container .cancel").click(function() {
                $("#popup_container").fadeOut(200, function() { $(this).remove(); });
                return false;
            });
        });
    });
    
    // Show clipboard if it's not empty
    if($("#clipboard p.clipboard").length < 1) {
        $("#clipboard").hide();
    }
    else {
        $("#clipboard").animate({ "opacity": 1 }, 200);
    };

    $("#clipboard button.paste").click(function() {
        dir_list = $("#dir_list").val();
        var overwrite = [];
        $("#clipboard p.clipboard").each(function() {
            name = $(this).text().path("basename");
            if(dir_list.indexOf(name) > -1) {
                overwrite[overwrite.length] = name;
            };
        })
        if(overwrite.length == 0 || confirm("Overwrite?\n-\n" + overwrite.join("\n"))) {
            return true;
        }
        else {
            return false;
        };
    })
    
    // ==========================================
    //                  Editor
    // ==========================================
    
    $("#code .edit").click(function() {
        path = $("#current_file").val();
        popup = "<div id='popup_container'>" +
            "<div class='popup' id='editor'>" +
                "<form action='xable_explorer.php?action=" + "save" + "' method='post'>" +
                    "<input type='hidden' name='path' value='" + encodeURI(path.path("dirname")) + "'>" +
                    "<input type='hidden' name='filename' value='" + encodeURI(path.path("basename")) + "'>" +
                    "<button class='cancel float-right'>Cancel</button><button class='save float-right'>Save</button>" +
                    "<p><b>Text Editor</b></p>" + 
                    "<textarea name='content'></textarea>" +
                "</form>" +
            "</div>" +
        "</div>";
        
        $("main").append(popup);
        $("#popup_container form").append($("form input.show_hidden").clone());
        alert($("#popup_container").html());
        
        $("#popup_container").fadeIn(200, function() {
            content = $("#file_content").val().replace("\t", "    ");
            
            $("#editor textarea").val(content).animate({ scrollTop: 0 }).focus();
            
            $("#editor .save").click(function() {
                edited = $("#editor textarea").val();
                if(content == edited) {
                    alert("Nothing changed!");
                    return false;
                }
                else {
                    return true;
                };
            });
            
            $("#editor .cancel").click(function() {
                $("#popup_container").fadeOut(200, function() { $(this).remove(); });
                return false;
            });
        });
    });
    
    // ==========================================
    //                  Launch
    // ==========================================
    
    setTimeout(function() {
        $("article, section").fadeIn(250);
    }, 250);
    
});