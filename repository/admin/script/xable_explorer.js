$(document).ready(function() {
    
    current_path = $("#current_dir h2").text().substr(1);
    
    urlQuery();
    urlQuery("path=" + current_path);
    
    // Explorer script protection data
    explorer_script_path = window.location.pathname;
    explorer_sript = explorer_script_path.path("basename");
    admin_path = explorer_script_path.path("dirname").split("/").pop();

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
    
	// Menu actions
	$("nav li").click(function() {
		// Hide dropdown
        $(this).closest("label").find("ul").stop().hide(100);
        $(this).closest("label").find("p").css({ "opacity": "1" });
        // Actions
		action = $(this).attr("value");
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
        else if(action == "separator") {
            return false;
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
        
        //functions = functions.split(",");
        var html = [];
        for(i in functions) {
            html[html.length] = "<li class='action' value='" + i + "'>" + functions[i].capitalize(1) + "</li>";
        };
        $("main").append("<ul id='context_menu'>\n\t" + html.join("\t\n") + "\n</ul>\n");
        $("#context_menu").css({ "left": x - 10, "top": y - 10 }).fadeIn(250);
        
        // Below visible window fix
        win_bottom = $(window).scrollTop() + $(window).height();
        menu_bottom = y + $("#context_menu").height();
        if(menu_bottom > win_bottom) {
            y = y - (menu_bottom - win_bottom);
            $("#context_menu").css({ "top": y - 10 })
        };
        
        $(window).scroll(function() { hideContextPopup(); })

        $("#context_menu").mouseleave(function() {  });
        $("#context_menu li").click(function() {
            val = $(this).attr("value");
            action = $("#browser form").attr("action");
            dir_list = $("#dir_list").val().split(";");
            if(val == "delete") {
                to_delete = [];
                $("#browser input.select:checked").each(function() {
                    to_delete[to_delete.length] = $(this).val();
                });
                if(current_path == admin_path && to_delete.indexOf(explorer_sript) > -1) {
                    alert(LOCALIZE["file-protected"] + ":\n" + explorer_sript);
                    hideContextPopup();
                }
                else if(confirm(LOCALIZE["delete-label"] + "?\n-\n" + to_delete.join("\n"))) {
                    $("#browser form").attr("action", action.replace("@action", val)).submit();
                }
                else {
                    hideContextPopup();
                };
            }
            else if(val == "duplicate" || val == "rename") {
                $("main").append(getNamePopup(val, $(this).text()));
                $("#popup_container").fadeIn(200, function() {
                    $("#popup_container input.filename").val( path.path("basename") ).focus();
                    $("#popup_container .confirm").click(function() {
                        input = $("#popup_container input.filename").val();
                        if(input == "") {
                            alert(LOCALIZE["empty-name-alert"]); //Nazwa nie może być pusta!
                            $(this).blur();
                            $("#popup_container input.filename").focus();
                            return false;
                        }
                        else if(dir_list.indexOf(input) > -1) {
                            alert(LOCALIZE["name-exists-alert"]); //Taka nazwa już istnieje!
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
            else if(val == "zip1") {
                to_zip = [];
                $("#browser input.select:checked").each(function() { to_zip[to_zip.length] = $(this).val(); });
                alert(to_zip);
                return false;
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
        $(".fake_cover").hide();
        document.getSelection().removeAllRanges();
    };
    
    function getSelectionFunctions() {
        var functions = false;
        if($("#browser form input:checked").length == 1) {
            //single selection functions = "copy,cut,delete,duplicate,rename";
            functions = {
                "copy": LOCALIZE["copy-label"].replace(/ /g, "&nbsp;"),
                "cut": LOCALIZE["cut-label"].replace(/ /g, "&nbsp;"),
                "delete": LOCALIZE["delete-label"].replace(/ /g, "&nbsp;"),
                "duplicate": LOCALIZE["duplicate-label"].replace(/ /g, "&nbsp;"),
                "rename": LOCALIZE["rename-label"].replace(/ /g, "&nbsp;"),
                "zip": LOCALIZE["zip-label"].replace(/ /g, "&nbsp;"),
            };
            
            if($("#browser form input:checked").val().path("extension") == "zip") {
               functions["unzip"] = LOCALIZE["unzip-label"].replace(/ /g, "&nbsp;");
            }
        }
        else {
            // multiple selection functions = "copy,cut,delete"
            functions = {
                "copy": LOCALIZE["copy-label"].replace(/ /g, "&nbsp;"),
                "cut": LOCALIZE["cut-label"].replace(/ /g, "&nbsp;"),
                "delete": LOCALIZE["delete-label"].replace(/ /g, "&nbsp;"),
                "zip": LOCALIZE["zip-label"].replace(/ /g, "&nbsp;")
            };
        };
        return functions;
    }
    
    $("#browser tr.path").contextmenu(function(e) {
        $(this).find("input.select").prop("checked", true);
        $(this).addClass("selected");
        var x = event.pageX;
        var y = event.pageY - $(window).scrollTop();
        var file_path = $(this).attr("path");
        functions = getSelectionFunctions();
        contextPopup(x, y, file_path, functions);
        $(this).blur();
        return false;
    });

    $("#browser tr .more_options").click(function() {
        $tr = $(this).closest("tr");
        setTimeout(function() {
            // Reselect
            $select = $tr.find("input.select");
            if($select.prop("checked") == false) {
                $select.prop("checked", true);
                $tr.addClass("selected");
            }
            // Show context menu
            $(".fake_cover").show();
            xy = $tr.offset();
            var file_path = $tr.attr("path");
            functions = getSelectionFunctions();
            contextPopup(xy.left, xy.top, file_path, functions);
            $(".fake_cover").click(function() { hideContextPopup(); });
        }, 100);
        $(this).blur();
    });
    
    // ==========================================
    //              Browser: Actions
    // ==========================================
    
    function getNamePopup(action, title) {
        var popup = "<div id='popup_container'>" +
            "<div class='popup'>" +
                "<nav>" +
                    "<p>" + title + "</p>" +
                    "<div class='buttons'><button class='cancel'><span class='fi-x'></span></button></div>" +
                "</nav>" +
                "<form>" +
                    "<p>" + LOCALIZE["file-folder-name"] + "</p>" + 
                    "<input name='filename' type='text' class='filename'>" +
                    "<div class='center'><button class='confirm'>" + LOCALIZE["ok-label"] + "</button></div>" +
                "</form>" +
            "</div>" +
        "</div>";
        return popup;
    };
    
    function uploadPopup() {
        var popup = "<div id='popup_container'>" +
            "<div class='popup'>" +
                "<nav>" +
                    "<p>" + LOCALIZE["files-upload-label"] + "</p>" +
                    "<div class='buttons'><button class='cancel'><span class='fi-x'></span></button></div>" +
                "</nav>" +
                "<form action='xable_explorer.php?action=upload' method='post' enctype='multipart/form-data'>" +
                    "<input type='hidden' name='path' value='" + $("#browser input.path").val() + "'>" +
                    "<p><input class='files' type='file' name='upload[]' multiple></p>" +
                    "<p><button class='confirm'>" + LOCALIZE["upload-label"] + "</button></p>" +
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
                    if(overwrite.length == 0 || (confirm(LOCALIZE["overwrite-label"] + "?\n-\n" + overwrite.join("\n")))) {
                        $("#popup_container").fadeOut(250, function() {
                            $("#working_info").fadeIn(500);
                        })
                        return true;
                    };
                    return false;
                }
                else {
                    alert(LOCALIZE["nothing-to-upload"]);
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
        $("main").append(getNamePopup(val, $(this).text()));
        $("#popup_container").fadeIn(200, function() {
            $("#popup_container input.filename").focus();
            var dir_list = $("#dir_list").val().split(";");
            $("#popup_container .confirm").click(function() {
                input = $("#popup_container input.filename").val();
                if(input == "") {
                    alert(LOCALIZE["empty-name-alert"]);
                    $(this).blur();
                    $("#popup_container input.filename").focus();
                    return false;
                }
                else if(dir_list.indexOf(input) > -1) {
                    alert(LOCALIZE["name-exists-alert"]);
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
        if(overwrite.length == 0 || confirm(LOCALIZE["overwrite-label"] + "?\n-\n" + overwrite.join("\n"))) {
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
                    "<button class='cancel float-right'>" + LOCALIZE["cancel-label"] + "</button><button class='save float-right'>" + LOCALIZE["save-label"] + "</button>" +
                    "<p><b>" + LOCALIZE["edit-label"] + "</b></p>" + 
                    "<textarea name='content'></textarea>" +
                "</form>" +
            "</div>" +
        "</div>";
        
        $("main").append(popup);
        $("#popup_container form").append($("form input.show_hidden").clone());
        //alert($("#popup_container").html());
        
        $("#popup_container").fadeIn(200, function() {
            content = $("#file_content").val().replace("\t", "    ");
            
            $("#editor textarea").val(content).animate({ scrollTop: 0 }).focus();
            
            $("#editor .save").click(function() {
                edited = $("#editor textarea").val();
                if(content == edited) {
                    alert(LOCALIZE["no-changes-done"]);
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