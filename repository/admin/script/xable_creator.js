$(document).ready(function() {

    // Hide file manager fields
    $("article input.string").each(function() {
        if($(this).val() == "_file_manager") {
            $(this).closest("article").css({ "position": "fixed", "left": "-100%"});
        };
    });
    
    function updateOptionMode($select) {
    // Change other related option modes
        mode = $select.val();
        article_tag = $select.closest("article").find("input.article_tag").val();
        section_tag = $select.closest("section").find("input.section_tag").val();
        $("main article").each(function() {
            if($(this).find("input.article_tag").val() == article_tag) {
                $(this).find("section").each(function() {
                    if($(this).find("input.section_tag").val() == section_tag) {
                        $(this).find("div.option select").val(mode);
                    }
                })
            }
        });
    };
    
    function updateOptionLabel($item) {
    // Rename other related options & selected (if needed)
        optionVal = $item.val();
        options = [];
        match = [];
        i = 0;
        $item.closest("ul").find("li .label").each(function() {
            val = $(this).val();
            if(val == optionVal) { match[match.length] = i; };
            options[i++] = val;
        });
        if(match.length > 1) {
            $item.val(optionMemo).focus();
            alert("length: " + match.length);
            alert(LOCALIZE["name-exists-alert"]);
        }
        else {
            // Update all articles
            article_tag = $item.closest("article").find("input.article_tag").val();
            section_tag = $item.closest("section").find("input.section_tag").val();
            $("main article").each(function() {
                if($(this).find("input.article_tag").val() == article_tag) {
                    $(this).find("section").each(function() {
                        if($(this).find("input.section_tag").val() == section_tag) {
                            // Update item label
                            $(this).find("div.option li .label").eq(match[0]).val(optionVal);
                            // Update selected
                            $selected = $(this).find("div.option input.selected");
                            selected = $selected.val();
                            if(selected == optionMemo) { $selected.val(optionVal); }
                            else if(options.indexOf(selected) < 0) { $selected.val(options[0]); };
                        }
                    })
                }
            })
        }
        optionMemo = false;
    };
    
    function autoUpdateTag($item) {
    // Rename other related multi_articles
        $section = $item.closest("section");
        $article = $item.closest("article");
        article_tag = $article.find("input.article_tag").val();
        // Section update
        if($section.length) {
            //alert("section update"); for futore developement
        }
        else if($article.length) {
            if( $article.find("input.article_multi").prop("checked") == true ) {
                
                $("article").each(function() {
                    tag = $(this).find("input.article_tag").val();
                    if( $(this).find("input.article_multi").prop("checked") == true && tag == articleTagMemo ) {
                        $(this).find("input.article_tag").val(article_tag);
                    };
                });
            };
        };
    };
    
	function checkTag($tag, memo) {
		if($tag.val() == "") {
			alert(LOCALIZE["empty-tag"]); //Tag nie może być pusty
            $tag.val(memo);
			$tag.focus();
		}
		else if($tag.val().length < 1) {
			alert(LOCALIZE["tag-too-short"]); //Tag musi składać się z minimóm 2 znaków
            $tag.val(memo);
			$tag.focus();
		}
		else {
			$tag.val( $tag.val().toLowerCase().replace(/ /g, "_") );
            autoUpdateTag($tag);
			updateXml();
		}
	};
    
    function clearSectionContent($section) {
        // media pathes
        var path_media = [ "image", "gallery", "file", "files" ];
        $section.find("form > .media ul li").each(function() {
            media_type = $(this).find("label input.type").val();
            if(path_media.indexOf(media_type) > -1) {
                $(this).find("input.path").val( $(this).find("input.folder").val() );
            }
        })
        // text content
        $section.find("form textarea, form input.string").val("");
    };
    
    language = $("input#language").val();
    languages = $("input#languages").val().split(",");
    xml_path = $("input#xml_path").val();

    // ==========================================
    //                 Menu bar
    // ==========================================
	
	// Menu actions
	$("nav li").click(function() {
        
		if($(this).attr("class") != "separator") {
			// Hide dropdown
			$(this).closest("label").find("ul").stop().hide(100);
			$(this).closest("label").find("p").css({ "opacity": "1" });
			// Actions
			action = $(this).attr("value");
            
			if(action == "quit") { //unsaved-changes-alert
				if(!changesDetected() || confirm(LOCALIZE["unsaved-changes-alert"].replace(/<br>/g, "\n"))) {
					if(xml_path != "") {
                        if(xml_path.path("extension") != "xml") { xml_path = xml_path.path("dirname") + "/" + xml_path.path("filename"); }
						location.href = "index.php?path=" + encodeURIComponent(xml_path);
					}
					else {
						location.href = "index.php";
					};
				};
			}
			else if($("#xml_path").length && action == "explorer") {
                edit_path = $("#xml_path").val();
                if(edit_path.length > root_path.length) {
                    edit_path = edit_path.substr(root_path.length + 1);
                    location.href = "xable_explorer.php?path=" + edit_path;
                }
                else {
                    location.href = "xable_explorer.php";
                }
            }
			else if(action == "update" || action == "users" || action == "explorer") {
				if(!changesDetected() || confirm(LOCALIZE["unsaved-changes-alert"].replace(/<br>/g, "\n"))) {
					location.href = "xable_" + action + ".php";
				};
			}
			else if(action == "update_code") {
				updateXml();
			}
			else if(action == "save") {
				if(!changesDetected()) {
					alert(LOCALIZE["no-changes-done"]); //Brak zmian do zapisania
				}
				else {
					$("form#save").submit();
				};
			}
			else if(action == "reload") {
				if(!changesDetected()) {
					alert(LOCALIZE["no-changes-done"]); //Brak zmian do zapisania
				}
				else if(confirm(LOCALIZE["unsaved-changes-alert"].replace(/<br>/g, "\n"))) {
					location.href = "xable_creator.php?open=" + encodeURI(xml_path);
                    //alert("xable_creator.php?path=" + encodeURI(xml_path));
				};
			}
			else if(action == "open") {
				if(!changesDetected() || confirm(LOCALIZE["unsaved-changes-alert"].replace(/<br>/g, "\n"))) {
					$("#popup_container .confirm").text("Open");
					$("#tree #file").val("");
					$("#popup_container").fadeIn(200);
				};
			}
			else if(action == "save_as") {
				$("#popup_container .confirm").text("Save");
				$("#tree #file").val(""); // Name if editing?
				$("#popup_container").fadeIn(200);
			}
			else if(action == "new") {
                if(!changesDetected() || confirm(LOCALIZE["unsaved-changes-alert"].replace(/<br>/g, "\n"))) {
				    location.href = "xable_creator.php";
                }
			}
			else if(action == "fill_languages") {
                if(confirm(LOCALIZE["fill-languages-alert"])) {
                    completeLanguages();
                    updateXml();
                };
			}
			else if(action == "delete_unused_languages") {
                if(confirm(LOCALIZE["delete-unused-languages-alert"])) {
                    deleteUnusedLanguages();
                    updateXml();
                };
			}
			else if(action == "unify_sections") {
                if(confirm(LOCALIZE["unify-sections-alert"])) {
                    unifySections();
                    updateXml();
                };
			}
			else if(action == "make_template") {
                if(confirm(LOCALIZE["make-template-alert"])) {
                    makeTemplate();
                    updateXml();
                };
			}
			else if(action == "change_pathes") {
                if(xml_path.path("extension") != "xml") {
                    alert(LOCALIZE["move-folder-alert"]);
                }
                else if($("form#save").find("input.move_files").length) {
                    alert(LOCALIZE["move-folder-twice"]);
                }
                else {
                    var folder = prompt(LOCALIZE["new-folder-path"]);
                    if(jQuery.type(folder) == "string" && folder.trim() != "") {
                        folder = folder.trim();
                        if(folder.substr(folder.length - 1) == "/") {
                            folder = folder.substr(0, folder.length - 1);
                        };
                        changePathes(folder);
                        updateXml();
                    };
                };
			}
            else if(action == "separator") {
                return false;
            }
			else {
				alert("Unimplemented: " + action)
			};
		};
	});
	
	// New file -> Turn off reload & save options
	if(xml_path == "") { $("nav li.active").removeClass("active").addClass("inactive"); }; // Change style
	$("nav li.inactive").unbind("click"); // Deactivate
   
    // ==========================================
    //               Browse tree
    // ==========================================

    // Stylize list content
    function zebra($details) {
        flag = false;
        $details.children().each(function() {
            if( $(this).prop("tagName") == "P" ) { $item = $(this); }
            else { $item = $(this).children("summary") };
            if(flag == true) {
                $item.css({ "background-color": "inherit" });
                flag = false;
            }
            else {
                $item.css({ "background-color": "#f4f4f4" });
                flag = true;
            };
        });
    };
    
    $("#tree").find(".size, .modified, .disabled").remove();
    $root_details = $("#tree #list").children("details");
    $root_details.children("summary").hide();
    root_path = $root_details.children("summary").attr("path");

    $("#tree").find("details").each(function() { zebra($(this)); });
    $("#tree details").show(200);

    // ====== Open folder content ======
    $("#tree summary").click(function() {
        $details = $(this).closest("details");
        if($details.parent("details").length) {
            $details.parent("details").children().each(function() {
                $(this).hide(200);
            })
            $details.show(200);
            current_dir = $details.children("summary").attr("path");
            current_dir = current_dir.substr(root_path.length + 1);
            $("#tree input#dir").val(current_dir).blur();
            $details.children("summary").hide();
            
            action = $("#popup_container .confirm").text().toLowerCase();
            if(action == "select") { $("#tree #input #file").val(current_dir).blur(); };
        }
        else {
            setTimeout( function() {
                $details.prop("open", true);
            }, 100 );
        };
        setTimeout( function() { $("#tree").focusout(); }, 250);
    });
    
    // ====== Move back to enclosing folder ======
    $("#tree #back").click(function() {
        $(this).blur();
        // Get current opened details
        $("#tree details").each(function() { if( $(this).prop("open") == true ) { $details = $(this); }; });
        $enclosing = $details.parent("details");
        if($enclosing.length) {
            $enclosing.children().each(function() {
                $(this).show(200);
            });
            current_dir = $enclosing.children("summary").attr("path");
            current_dir = current_dir.substr(root_path.length + 1);
            $("#tree input#dir").val(current_dir).blur();
            $("#tree details").find("details").children("summary").show();
            $enclosing.children("summary").hide();
            $details.removeProp("open");
            
            action = $("#popup_container .confirm").text().toLowerCase();
            if(action == "select") { $("#tree #input #file").val(current_dir).blur(); };
        };

        
        setTimeout( function() { $("#tree").focusout(); }, 250);
    });

    $("#tree #list p.valid").click(function() {
        file_name = $(this).find(".name").text();
        action = $("#popup_container .confirm").text().toLowerCase();
		// Open on click
		if(action == "select") {
            // No action!
        }
		else if(action == "open") {
			file_path = completePath( [ root_path, $("#tree #dir").val(), file_name ] );
			location.href = "xable_creator.php?open=" + encodeURIComponent(file_path);
		}
        else {
            $("#tree #input #file").val(file_name);
        };
    });

    $("#popup_container .cancel").click(function() {
        $("#popup_container").fadeOut(100);
    });
	
	function completePath( path_parts ) {
		file_path = [];
		for(i in path_parts) { if(path_parts[i] != "") { file_path[file_path.length] = path_parts[i]; }; };
		return file_path.join("/");
	};

    $("#popup_container .confirm").click(function() {
        $(this).blur();
        file_name = $("#tree #input #file").val();
        // Get current dir taken file_names
        dir = [];
        $("#tree details").each(function() { if( $(this).prop("open") == true ) { $details = $(this); }; });
        $details.children("p.valid").each(function() { dir[dir.length] = $(this).find(".name").text(); });
        if(file_name == "") {
            alert("Enter the filename");
        }
        else {
            if(file_name.path("extension") != "xml" && file_name.path("extension") != "template") { file_name = file_name.path("file_name") + ".xml"; };
            file_path = completePath( [ root_path, $("#tree #dir").val(), file_name ] );

            
            action = $("#popup_container .confirm").text().toLowerCase();
            // ====== SAVE AS ======
            if(action == "save") {
                if(dir.indexOf(file_name) < 0 || (confirm(LOCALIZE["overwrite-alert"].replace("@filename", file_name)))) {
                    //alert("OK -> " + file_path);
                    $("#popup_container").fadeOut(100);
					$("form#save").attr("action", "xable_creator.php?save=" + encodeURIComponent(file_path)).submit();
                    //location.href = "xable_creator.php?save=" + encodeURIComponent(file_path);
                };
            }
			// ====== OPEN ======
            else if(action == "open") {
                if(dir.indexOf(file_name) < 0) {
                    alert(LOCALIZE["not-found-alert"].replace("@filename", file_name));
                }
                else {
                    $("#popup_container").fadeOut(100);
                    location.href = "xable_creator.php?open=" + encodeURIComponent(file_path);
                };
            }
            // ====== SELECT ======
            else if(action == "select" && $browse != false && $browse.length) {
                $browse.closest("li").find("input.path").val( $("#tree #input #file").val() );
                $browse.closest("li").find("input.type").prop("checked", true);
                $("#popup_container").fadeOut(100);
                updateXml();
            };
        };
    });

    // ==========================================
    //                New Article
    // ==========================================
    
    articleHeader =
        "<div class='buttons'>" +
        "<button class='up'><span class='fi-arrow-up'></span></button>" +
        "<button class='down'><span class='fi-arrow-down'></span></button>" +
        "<button class='clone'><span class='fi-page-multiple'></span></button>" +
        "<button class='delete'><span class='fi-x'></span></button>" +
        "</div>" +
        "<span class='article_icon'></span>";
    
    articleFooter =
        "<div class='dropdown'><dl class='button'>" +
        "<dt>+</dt>" +
        "<dd>string</dd>" +
        "<dd>code</dd>" +
        "<dd>text</dd>" +
        "<dd>textarea</dd>" +
        "<dd>table</dd>" +
        "<dd>option</dd>" +
        "<dd>media</dd>" +
        "<dd>date</dd>" +
        "<dd>button</dd>" +
        "</dl></div>";
    
    
    function showArticles() {
        time = 0;
        $("main article").each(function() {
            $(this).prepend(articleHeader).append(articleFooter).delay(time++ * 100).show(200);
        });
    };

    $(".new_article").click(function() {
        // Build article HTML
        html =
            articleHeader +
            "<input type='text' class='article_tag string' value='new_article'>" + // default name for testing
            "<label class='option'><input type='checkbox' class='article_multi checkbox'><span>multi</span></label>" +
            articleFooter;
        // Add new article
        $(this).before("<article>" + html + "</article>");
        $(this).blur();
        // Show new article
        $new_container = $("article").last();
        $new_container.show(200, function() {
            $new_container.addClass("new_container");
            $(this).children("input.article_tag").focus().select();
        });
		reinitializeAll();
    });
    
    // ==========================================
    //                 New Section
    // ==========================================
    
    sectionHeader =
        "<div class='buttons'>" +
        "<button class='up'><span class='fi-arrow-up'></span></button>" +
        "<button class='down'><span class='fi-arrow-down'></span></button>" +
        //"<button class='clone'><span class='fi-page-multiple'></span></button>" +
        "<button class='delete'><span class='fi-x'></span></button>" +
        "</div>";
    
    nonEditable = "<label class='option'><input class='non_editable' type='checkbox'><span>" + LOCALIZE["disabled-label"] + "</span></label>";

    $("main section").prepend(sectionHeader).show();
    $("main section input.non_editable").each(function() {
        non_editable = $(this).val();
        $section = $(this).closest("section");
        $(this).replaceWith( nonEditable );
        if(non_editable == "true") { $section.find("input.non_editable").prop("checked", true); };
    });

    function initializeNewSectionButton() {
        // Select new section  type
		$(".dropdown dl").unbind("mouseenter").mouseenter(function() {
			$(this).find("dd").stop().show(200);
			$(this).find("dt").css({ "opacity": "0.25" });
            $(".fake_cover").show();
		});
		$(".dropdown dl").unbind("mouseleave").mouseleave(function() {
			$(this).find("dd").stop().hide(200);
			$(this).find("dt").css({ "opacity": "1.0" });
            $(".fake_cover").hide();
		});
		$(".fake_cover").click(function() {
			$(".dropdown dl dd").stop().hide(200);
			$(".dropdown dl dt").css({ "opacity": "1.0" });
            $(".fake_cover").hide();
		});
		// ====== New Section ======
        $(".dropdown dd").unbind("click").click(function() {
            section_type = $(this).text();
            // Hide dropdown
            $(this).closest("dl").find("dd").stop().hide(200);
            $(this).closest("dl").find("dt").css({ "opacity": "1.0" });
            $(".fake_cover").hide();
            // Build section HTML
            html = sectionHeader +
                "<label class='section_tag'><span class='type'>" + section_type + "</span><input type='text' class='section_tag string' value='new_section'></label>" +
                nonEditable +
				"<form>" +
				"<input type='text' class='label' value='' placeholder='" + LOCALIZE["title-label"] + "'>" +
				"<input type='text' class='description' value='' placeholder='" + LOCALIZE["description-label"] + "'>";
            // ====== Types ======
			if(section_type == "string") { html = html + stringHTML(); }
			if(section_type == "code") { html = html + "<textarea class='code'></textarea>"; }
			if(section_type == "text") { html = html + textHtml(); }
			else if(section_type == "textarea") { html = html + textareaHtml(); }
			else if(section_type == "option") { html = html + optionHtml(); }
			else if(section_type == "media") { html = html + mediaHtml(); }
			else if(section_type == "table") { html = html + tableHtml(); }
			else if(section_type == "button") { html = html + "<p>" + LOCALIZE["action-label"] + "</p><input type='text' class='action' value=''>"; }
            else if(section_type == "date") { html = html + dateHtml(); }
			else {};
            // ===================
			html = html + "</form>";
            // Add new section
            $(this).closest(".dropdown").before("<section>" + html + "</section>");
            $(this).blur();
            // Show new section and focus on tag field
            $new_container = $(this).closest("article").find("section").last();
			$new_container.show(200, function() {
                $new_container.addClass("new_container");
				$(this).find(".section_tag input").focus().select();
			});

            // Initialize buttons
            reinitializeAll();
        });
        
    };
    
    // ==========================================
    //                  String
    // ==========================================
    
    function stringHTML() {
        html = "<input type='hidden' class='string' value=''>" +
            "<input type='text' class='options' value='' placeholder='" + LOCALIZE["more-options"] + "'>";
        return html;
    }
    
    // ==========================================
    //                   Date
    // ==========================================
    
	function dateHtml() {
		html = "<input type='hidden' class='date' value=''>" +
            "<input type='text' class='options' value='' placeholder='" + LOCALIZE["more-options"] + "'>";
        return html;
	}
    
    // ==========================================
    //                   Table
    // ==========================================
    
	function tableHtml() {
		html = "<div class='table'>";
		for(i in languages) {
			lang = languages[i];
			html = html + "<textarea class='" + lang + "'></textarea>";
		};
		html = html + "</div>";
        return html;
	}
    
    // ==========================================
    //                   Media
    // ==========================================
	
	mediaHeader = "<ul>" +
		"<li><label><input type='checkbox' class='type' value='image'><span class='fi-photo'></span></label><input type='hidden' class='path'><input type='text' class='folder' placeholder='" + LOCALIZE["folder-path-label"] + "'><button class='browse'><span class='fi-folder'></span></button></li>" +
		"<li><label><input type='checkbox' class='type' value='gallery'><span class='fi-thumbnails'></span></label><input type='hidden' class='path'><input type='text' class='folder' placeholder='" + LOCALIZE["folder-path-label"] + "'><button class='browse'><span class='fi-folder'></span></button></li>" +
		"<li><label><input type='checkbox' class='type' value='file'><span class='fi-page'></span></label><input type='hidden' class='path'><input type='text' class='folder' placeholder='" + LOCALIZE["folder-path-label"] + "'><button class='browse'><span class='fi-folder'></span></button></li>" +
		"<li><label><input type='checkbox' class='type' value='files'><span class='fi-page-copy'></span></label><input type='hidden' class='path'><input type='text' class='folder' placeholder='" + LOCALIZE["folder-path-label"] + "'><button class='browse'><span class='fi-folder'></span></button></li>" +
		"<li><label><input type='checkbox' class='type' value='video'><span class='fi-play-video'></span></label><input type='hidden' class='path' value=''><input type='text' placeholder='" + LOCALIZE["video-link"] + "' disabled></li>" +
		"<li><label><input type='checkbox' class='type' value='none'><span class='fi-prohibited'></span></label><input type='text' class='path' placeholder='" + LOCALIZE["status-none"].capitalize() + "' value='' disabled></li>" +
		"</ul>";
		
	// Complete loaded data
	$("section form .media ul").each(function() {
		media = {};
		$(this).find("li input").each(function() {
			type = $(this).attr("class");
			path = $(this).val();
			media[type] = path;
		});
		//for(i in media) { alert( "media: " + media[i] ); };
		$media = $(this).closest(".media");
		$(this).replaceWith( mediaHeader );
		$media.find("li").each(function() {
			type = $(this).find("input.type").val();
            if(jQuery.type( media[type] ) == "string") {
                $(this).find("input.path").val( media[type] );
                $(this).find("input.type").prop("checked", true);
                
                if($(this).find("input.folder").length) {
                    folder = media[type].split(";").shift().path("dirname");
                    $(this).find("input.folder").val( folder );
                };
            };
		});
	});
	
	function mediaHtml() {
		return "<div class='media'>" +
			mediaHeader +
			"<input type='hidden' class='set' value=''>" +
			"</div>";
	};
	
    $browse = false; // Global!
	function initializeMediaButtons() {
		$("form .browse").unbind("click").click(function() {
            $browse = $(this);
			$("#popup_container .confirm").text("Select");
			$("#popup_container").fadeIn(200);
			$(this).blur();
			return false;
		});
        $("form .media input.folder").change(function() {
            $li = $(this).closest("li");
            if( $(this).val() != "" ) {
                $li.find("input.type").prop("checked", true);
                folder = $li.find("input.folder").val();
                path = $li.find("input.path").val();
                // update path, based on folder input
                if(path != folder) {
                    pathes = path.split(";");
                    if(pathes.length > 1 || pathes[0].path("extension") != "") {
                        alert(LOCALIZE["path-change-alert"]);
                        for(i in pathes) {
                            pathes[i] = folder + "/" + pathes[i].path("basename");
                        };
                    }
                    else {
                        pathes = [ folder ];
                    }

                    $li.find("input.path").val(pathes.join(";"));
                };
                updateXml();
            };
        });     
	};
	
    // ==========================================
    //                  Option
    // ==========================================
    
    optionHeader = "<div class='buttons'>" +
        "<button class='up'><span class='fi-arrow-up'></span></button>" +
        "<button class='down'><span class='fi-arrow-down'></span></button>" +
        "<button class='delete'><span class='fi-x'></span></button>" +
        "</div>";
    
    optionFooter = "<button class='add'><span class='fi-plus'></span></button>";
    
	// Complete loaded data
    $("main form .option li").append(optionHeader).show();
    $("main form .option").append(optionFooter);
    
	function optionHtml() {
		return "<div class='option'>" +
			"<select><option value='checkbox'>Checkbox</option><option value='radio'>Radio</option></select>" +
			"<ul></ul>" +
            "<input type='hidden' class='selected' value=''>" +
			optionFooter +
			"</div>";
	};
    
    function checkSelected($select) {
        if( $select.closest(".option").find("input.selected").val() == "" &&
            $select.val() == "radio" &&
            $select.closest(".option").find("ul li input.label").length ) {
                selected = $select.closest(".option").find("ul li input.label").first().val();
                $select.closest(".option").find("input.selected").val(selected);
        };
        updateOptionMode($select);
        updateXml();
    };
	
	function initializeNewOptionButton() {
		$("form .option select").unbind("change").change(function() { checkSelected( $(this) ); });
		
		$("form .option button.add").unbind("click").click(function() {
			html = "<li>" +
			"<span class='fi-checkbox icon'></span><input type='text' class='label'>" +
            optionHeader +
			"</li>";
			$ul = $(this).closest(".option").find("ul");
			$ul.append(html);
			$ul.find("li").last().show(200, function() {
				$(this).find("input.label").focus();
			});
			// Check for selected -> required in radio mode
            $("form .option input").unbind("change").change(function() {
                checkSelected( $(this).closest(".option").find("select") );
            });
            reinitializeAll();
			return false;
		});
	};
	
    // ==========================================
    //                 TextArea
    // ==========================================
    // ALL: b,i,u,^,v,-,c,.,_,m,l,a,f,p
	textareaHeader = "<div class='format'>" +
		"<label><span class='fi-asterisk'></span><input type='checkbox' value='*' class='all'></label>" +
		"<label><span class='fi-bold'></span><input type='checkbox' value='b'></label>" +
		"<label><span class='fi-italic'></span><input type='checkbox' value='i'></label>" +
		"<label><span class='fi-underline'></span><input type='checkbox' value='u'></label>" +
		"<label><span class='index'>X<sup>2</sup></span><input type='checkbox' value='^'></label>" +
		"<label><span class='index'>X<sub>2</sub></span><input type='checkbox' value='v'></label>" +
		"<label><span class='fi-list-bullet'></span><input type='checkbox' value='-'></label>" +
		"<label><span class='fi-align-center'></span><input type='checkbox' value='c'></label>" +
		"<label><span>&bull;</span><input type='checkbox' value='.'></label>" +
		"<label><span class='fi-minus'></span><input type='checkbox' value='_'></label>" +
        "<label><span class='fi-mail'></span><input type='checkbox' value='m'></label>" +
		"<label><span class='fi-link'></span><input type='checkbox' value='l'></label>" +
		"<label><span class='fi-page-filled'></span><input type='checkbox' value='a'></label>" +
		"<label><span class='fi-photo'></span><input type='checkbox' value='f'></label>" +
		"<label><span class='fi-layout'></span><input type='checkbox' value='p'></label>" +
		"<div class='buttons'><button class='none'>" + LOCALIZE["uncheck-button"] + "</button></div>" +
		"</div>";
		
	function textHtml() {
		html = "<div class='text'>";
		for(i in languages) {
			lang = languages[i];
			html = html + "<textarea class='" + lang + "'></textarea>";
		};
		html = html + "</div>";
        return html;
	}
	
    function textareaHtml() {
		return textareaHeader + textHtml();
    };
	
	// Complete loaded data
	$("section form input.format").each(function() {
		format = $(this).val().split(",");
		$form = $(this).closest("form");
		$(this).replaceWith( textareaHeader );
		$form.find(".format label input").each(function() {
			if(format.indexOf( $(this).val() ) > -1) { $(this).prop("checked", true); };
		});
	});
    
    function initializeFormatButtons() {
        // None button
        $(".format .none").unbind("click").click(function() {
            $(this).closest(".format").find("input").prop("checked", false);
            $(this).blur();
			updateXml();
            return false;
        });
        // Checkboxes
        $(".format input").unbind("change").change(function() {
            $format = $(this).closest(".format");
            // All
            if( $(this).attr("class") == "all" ) {
                if( $(this).prop("checked") == true ) {
                    $format.find("input").prop("checked", false);
                    $(this).prop("checked", true);
                };
            }
            // Not all
            else {
                if( $(this).prop("checked") == true ) {
                    $format.find(".all").prop("checked", false);
                };
            };
            updateXml();
        });
    };
    
    // ==========================================
    //           Delete & Move Buttons
    // ==========================================

	function initializeEditButtons() {
        // ====== Delete ======
		$(".delete").unbind("click").click(function() {
			$container = $(this).parent().parent();
			$container.hide(200, function() {
				$(this).remove();
				updateXml();
                resizeCode(); // function form xable_nav.js
			});
			return false;
		});
        // ====== Move UP ======
        $(".up").unbind("click").click(function() {
            $(this).blur();
            $container = $(this).parent().parent();
            if($container.prop("tagName") == $container.prev().prop("tagName")) {
                $container.hide(200, function() {
                    $swapWith = $container.prev();
                    $container.after($swapWith.detach());
                    $container.show(200);
					updateXml();
                });
            };
			return false;
        });
        // ====== Move DOWN ======
        $(".down").unbind("click").click(function() {
            $(this).blur();
            $container = $(this).parent().parent();
            if($container.prop("tagName") == $container.next().prop("tagName")) {
                $container.hide(200, function() {
                    $swapWith = $container.next();
                    $container.before($swapWith.detach());
                    $container.show(200);
					updateXml();
                });
            };
			return false;
        });
        // ====== CLONE ======
        $(".clone").unbind("click").click(function() {
            $(this).blur();
            $container = $(this).parent().parent();
            $container.before($container.clone());
            $new_container = $container.prev();
            $new_container.find("section").each(function() { clearSectionContent($(this)); });
            $new_container.css({ "opacity": 0 }).hide().show(200);
            // Copy selecet settings
            if($(this).closest("article").find("select").length) {
                //alert("select");
                select_set = [];
                n = 0;
                $container.find("select").each(function() {
                    select_set[n++] = $(this).find("option:selected").val();
                    //alert( $(this).find("option:selected").val() );
                });
                n = 0;
                setTimeout(function() {
                    $new_container.find("select").each(function() {
                        option = select_set[n++];
                        $(this).find("option").each(function() {
                            if( $(this).val() == option ) { $(this).prop("selected", true); };
                        });
                    });
                    updateXml();
                }, 10);
            };
            // New container highlight
            $new_container.find(".new_container").removeClass("new_container");
            $new_container.addClass("new_container");
            $new_container.animate({ "opacity": 1 }, 500);
            // initialize buttons for clone
            reinitializeAll();
			return false;
        });
	};
    
    var articleTagMemo = false; // Global Variable!
    var sectionTagMemo = false; // Global Variable!
    var optionMemo = false;     // Global Variable!
    
    function reinitializeAll() {
        $("form input").unbind("change");
        
        initializeNewSectionButton();
        initializeMediaButtons();
        initializeNewOptionButton();
        initializeEditButtons();

        $("input.non_editable").unbind("change").change(function() { updateXml(); });
        $("input.article_multi").unbind("change").change(function() { updateMulti($(this)); updateXml(); });
        $("div.option input.label").unbind("change").change(function() { updateOptionLabel($(this)); updateXml(); });
        $("input.article_tag").unbind("blur").blur(function() { checkTag($(this), articleTagMemo); articleTagMemo = false; });
        $("input.section_tag").unbind("blur").blur(function() { checkTag($(this), sectionTagMemo); sectionTagMemo = false; });
        $("form input").change(function() { updateXml(); });

        initializeFormatButtons();
        updateXml();
        
        // Needed for autoUpdateTag()
        $("input.article_tag").unbind("focus").focus(function() {
            if(articleTagMemo == false) { articleTagMemo = $(this).val(); };
        });
        $("input.section_tag").unbind("focus").focus(function() {
            if(sectionTagMemo == false) { sectionTagMemo = $(this).val(); };
        });
        $("div.option input.label").unbind("focus").focus(function() {
            if(optionMemo == false) { optionMemo = $(this).val(); };
        });
        
        setTimeout(function() { resizeCode(); }, 500); // function form xable_nav.js
    };
    
    // ==========================================
    // ==========================================
    // ==========================================
    //                Extra Tools
    // ==========================================
    // ==========================================
    // ==========================================
    
    function getSections($article) {
        var sections = {};
        $article.find("section").each(function() {
            section_tag = $(this).find("input.section_tag").val();
            sections[section_tag] = $(this);
        })
        return sections;
    }
    
    function articleBlink($article) {
        $article.animate({ "opacity": 0.1 }, 500, function() {
            $(this).delay(500).animate({ "opacity": 1.0 }, 500);
        });
    };

    // ====== Unify Sections ======
    function unifySections() {
        var articles = {};
        $("main article").each(function() {
            $article = $(this);
            articleBlink($article);
            article_tag = $article.find("input.article_tag").val();
            if(article_tag != "_file_manager") {
                if(!articles[article_tag]) {
                    articles[article_tag] = getSections($(this));
                }
                else {
                    var sections = getSections($(this));
                    var master_sections = articles[article_tag];
                    $old_sections = $article.find("section");
                    for(tag in master_sections) {
                        $master_section = master_sections[tag];
                        type = $master_section.find("label span.type").text();
                        // Section exists
                        if(($section = sections[tag]) && $section.find("label span.type").text() == type) {
                            label = $master_section.find("form input.label").val();
                            $section.find("form input.label").val(label);
                            description = $master_section.find("form input.description").val();
                            $section.find("form input.description").val(description);
                            $article.append($section.clone());
                            selected = $section.find("input.selected").val();
                            $section = $article.find("section").last(); // New section
                        }
                        // No such section or wrong type
                        else {
                            $article.append($master_section.clone());
                            selected = $master_section.find("input.selected").val();
                            $section = $article.find("section").last();
                            clearSectionContent($section);
                        }
                        
                        if(type == "option") {
                            // Options
                            $section_ul = $section.find("ul");
                            $section_ul.find("li").remove();
                            
                            options = [];
                            $master_section.find("ul li").each(function() {
                                $section_ul.append( $(this).clone() );
                                options[options.length] = $(this).find(".label").val();
                            });
                            // Checkbox/Radio mode
                            $section.find("select").val( $master_section.find("select").val() );
                            
                            // Selected
                            if(options.indexOf(selected) < 0) { selected = options[0]; };
                            $section.find("input.selected").val(selected);
                        }
                    }
                    $old_sections.remove();
                    //$old_sections.css({ "background-color": "green" });
                };
            }
            
        });
        reinitializeAll();
    };
    
    // ====== Change Media Location(s) ======
    function changePathes(new_folder) {
        var folders_to_move = [];
        var files_to_move = [];
        
        $("main article").each(function() {
            $article = $(this);
            articleBlink($article);
            article_tag = $article.find("input.article_tag").val();

            $article.find("section form .media ul li").each(function() {
                $path = $(this).find("input.path");
                $folder = $(this).find("input.folder");
                if($folder.length && $folder.val() != "") {
                    folder = $folder.val();
                    pathes = $path.val().split(";");
                    
                    if(folder != pathes[0] || pathes.length > 1) {
                        if(folders_to_move.indexOf(folder) < 0 && folder != new_folder) {
                            folders_to_move[folders_to_move.length] = folder;
                        }
                    };
                    
                    for(i in pathes) {
                        old_path = pathes[i];
                        new_path = new_folder + old_path.substr(folder.length);
                        pathes[i] = new_path;
                        files_to_move[ old_path ] = new_path;
                    };

                    $path.val(pathes.join(";"));
                    $folder.val(new_folder);
                }
            })
        });
        if(folders_to_move.length > 0) {
            moveFiles(files_to_move);
            setTimeout(function() {
                alert(LOCALIZE["move-folder-info"].replace("@new_folder", new_folder).replace("@old_folders", folders_to_move.join(", ")));
            }, 1000);
        }
        else {
            alert(LOCALIZE["move-folder-none"]);
        }
    };
    
    function moveFiles(files_to_move) {
        var files = [];
        var n = 0;
        for(old_path in files_to_move) {
            new_path = files_to_move[old_path];
            if(old_path.path("extension") != "" && old_path != new_path) {
                n++;
                files[files.length] = "<input name='move_files-old_" + n + "' value='" + old_path + "'><input name='move_files-new_" + n + "' value='" + new_path + "'>";
            };
        };
        if(files.length > 0) {
            files[files.length] = "<input class='move_files' name='action' value='move_files'>";
            $("form#save").append(files);
        };
    };
    
    if($("input#show_popup").length) {
        alert($("input#show_popup").val());
    };
    
    // ====== auto complete Languages ======
    function completeLanguages() {
        // Get main & other languages
        other_langs = $("input#languages").val().split(",");
        main_lang = other_langs.shift();
        if(other_langs.length > 0) {
            $("main article").each(function() {
                articleBlink($(this));
                $(this).find("form > .table, form > .text").each(function() {
                    tag = $(this).closest("section").find("label input.section_tag").val();
                    // Get default language content(s)
                    var $default = $(this).children("." + main_lang);
                    // Check all languages loop
                    for(i in other_langs) {
                        $div = $(this); // div container
                        $content = $(this).find("." + other_langs[i]); // language content(s)
                        // Delete empty content(s)
                        if($content.length) {
                            var empty = true;
                            $content.each(function() {
                                val = $(this).val().split(";");
                                for(n in val) {
                                    if(val[n] != "") {
                                        empty = false;
                                        //alert("<" + tag + ">\nLANG: " + other_langs[i] + "\n" + n + ". " + val[n]);
                                    }
                                    else {
                                        //alert("<" + tag + ">\nLANG: " + other_langs[i] + "\n" + n + ". *empty*");
                                    }
                                }
                            });
                            // Delete empty language conetnt(s)
                            if(empty) {
                                //alert("<" + tag + ">\nDELETE LANG -> " + other_langs[i]);
                                $content.remove();
                                $content = false;
                            };
                        };
                        // Add default content(s)
                        if(!$content.length) {
                            $default.each(function() {
                                $div.append( $(this).clone() );
                                $div.children("." + main_lang).last().attr("class", other_langs[i])
                            })
                        };
                    } // end of languages loop
                })
            });
        }
        else {
            alert(LOCALIZE["no-languages-alert"]);
        };
    };
    
    // ====== Delete Unused Languages ======
    function deleteUnusedLanguages() {
        var all_langs = $("input#languages").val().split(",");
        $("main article").each(function() {
            var $article = $(this);
            var modified_flag = false;
            $(this).find("form > .table, form > .text").each(function() {
                tag = $(this).closest("section").find("label input.section_tag").val();
                $(this).children("textarea").each(function() {
                    var lang = $(this).attr("class");
                    if(all_langs.indexOf(lang) < 0) {
                        $(this).remove();
                        modified_flag = true;
                    };
                })
            })
            if(modified_flag) { articleBlink($article); };
        });
    }
    
    // ====== Make a template ======
    function makeTemplate() {
        page_name = xml_path.path("filename");
        dirname = xml_path.path("dirname");
        if(jQuery.type(dirname) == "string" && dirname != "") {
            page_folder = dirname.split("/").pop();
        }
        else {
            page_folder = false;
        };
        
        //alert("page_folder = " + page_folder);
        
        // Delete multi articles
        memo_tag = false;
        $("main article").each(function() {
            tag = $(this).find("input.article_tag").first().val();
            if(tag == memo_tag) { $(this).remove(); }
            memo_tag = tag;
        });
        
        // Clear content
        $("main article section").each(function() {
            clearSectionContent($(this));
            
            // Change media folder byName
            type = $(this).children(".section_tag").find(".type").text();
            if(type == "media") {
                $(this).find("form div.media li input").each(function() {
                    val = $(this).val().split("/");
                    
                    if(val.length > 1 && val[val.length - 1] == page_name) {
                        val[val.length - 1] = "@filename";
                        if(page_folder && val[val.length - 2] == page_folder) {
                            val[val.length - 2] = "@folder";
                        }
                        $(this).val(val.join("/"));
                    }
                })
            }
            
            // Change category path byName
            section_tag = $(this).find("input.section_tag").first().val();
            if(section_tag == "_category") {
                $path = $(this).find("input.description");
                val = $path.val();
                filename = val.split(":").pop().trim().path("filename");
                if(filename == page_name) {
                    val = val.replace("/" + filename + ".", "/@filename.", val);
                    $path.val(val);
                }
            }
            if(section_tag == "_categories_list") {
                memo_row_lang = false;
                $(this).find("div.table").find("textarea").each(function() {
                    row_lang = $(this).attr("class");
                    if(row_lang == memo_row_lang) { $(this).remove(); };
                    memo_row_lang = row_lang;
                })
            }
        });
        
        // Delete file manager data
        $file_manager = $("main article").first();
        if($file_manager.length && $file_manager.children(".article_tag").val() == "_file_manager") {
            $file_manager.find("form div.media li input.path").each(function() {
                $(this).val("");
            })
        };
        
        // Resize layout
        setTimeout(function() { resizeCode(); }, 500);
    }

    
    // ==========================================
    //                Code update
    // ==========================================
    
    $("#xml").click(function() {
        updateXml();
        $(this).blur();
    });

    function getXml() {
        xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<xable>\n\n";
        $("main article").each(function() {
            // Article begin
            article_tag = $(this).find("input.article_tag").val();
            if( $(this).find("input.article_multi").prop("checked") == true ) { article_tag = "multi_" + article_tag; }; // MULTI
            xml = xml + "\t<" + article_tag + ">\n";
            $(this).find("section").each(function() {
                // Section begin
                section_tag = $(this).find("input.section_tag").val();
                // Disabled in editor
                xml = xml + "\t\t<" + section_tag + ">\n";
                if( $(this).find("input.non_editable").prop("checked") == true ) {
                    xml = xml + "\t\t\t<disabled>true</disabled>\n";
                };
                // Type
                xml = xml + "\t\t\t<type>" + $(this).find("span.type").text() + "</type>\n"; // TYPE
                // Content
                $(this).find("form").children().each(function() {
                    tag = $(this).attr("class");
                    val = $(this).val();
                    if( jQuery.type(val) != "string" ) { val = $(this).attr("value"); }; // Get value
					// ====== COMPLEX TYPES ======
                    if(tag == "format") {
                        format = [];
                        $(this).find("input").each(function() {
                            if( $(this).prop("checked") == true ) { format[format.length] = $(this).val(); };
                        });
                        xml = xml + "\t\t\t<format>" + format.join(",") + "</format>\n";
                    }
					else if(tag == "option") {
						option_tag = $(this).find("select").val();
						xml = xml + "\t\t\t<" + option_tag + ">\n";
						$(this).find("li input.label").each(function() {
							xml = xml + "\t\t\t\t<option>" + $(this).val() + "</option>\n";
						});
						xml = xml + "\t\t\t</" + option_tag + ">\n";
						xml = xml + "\t\t\t<selected>" + $(this).find("input.selected").val() + "</selected>\n";
					}
					else if(tag == "media") {
						xml = xml + "\t\t\t<media>\n";
						$(this).find("li").each(function() {
							if( $(this).find("input.type").prop("checked") == true ) {
								media_type = $(this).find("input.type").val();
								media_path = $(this).find("input.path").val();
								xml = xml + "\t\t\t\t<" + media_type + ">" + media_path + "</" + media_type + ">\n";
							};
						});
						xml = xml + "\t\t\t</media>\n";
						xml = xml + "\t\t\t<set>" + $(this).find("input.set").val() + "</set>\n";
					}
                    else if(tag == "text" || tag == "textarea") {
						xml = xml + "\t\t\t<" + tag + ">\n";
						$(this).children("textarea").each(function() {
							lang = $(this).attr("class");
							val = $(this).val();
							xml = xml + "\t\t\t\t<" + lang + ">" + val + "</" + lang + ">\n";
						});
						xml = xml + "\t\t\t</" + tag + ">\n";
                    }
                    else if(tag == "table") {
						xml = xml + "\t\t\t<" + tag + ">\n";
						$(this).children("textarea").each(function() {
							lang = $(this).attr("class");
							val = $(this).val();
							xml = xml + "\t\t\t\t<" + lang + ">" + val + "</" + lang + ">\n";
						});
						xml = xml + "\t\t\t</" + tag + ">\n";
                    }
					// ====== STANDARD TYPES ======
					else if( jQuery.type(tag) == "string" && jQuery.type(val) == "string" ) {
                        //if(tag == "text") { alert("val: " + val); };
                        if(( tag != "label" || val != "") && (tag != "description" || val != "" )) { // no empty label or description
                            val = val.replace(/</g, "&lt;").replace(/>/g, "&gt;"); // inner <tag>(s) fix
                            xml = xml + "\t\t\t<" + tag + ">" + val + "</" + tag + ">\n";
                        };
                        
					};
                });
                // Section end
                xml = xml + "\t\t</" + section_tag + ">\n";
            });
            // Article end
            xml = xml + "\t</" + article_tag + ">\n\n";
            
        });
        return xml + "</xable>";
    };
    
    initial_xml = false; // global
    current_xml = false; // global
    
    function changesDetected() { return initial_xml != current_xml; };
    
    function updateMulti($input) {
        $article = $input.closest("article");
        article_tag = $article.find(".article_tag").val();
        article_multi = $article.find("input.article_multi").prop("checked");
        $("main article").each(function() {
            tag = $(this).find(".article_tag").val();
            if(tag == article_tag) {
                $(this).find("input.article_multi").prop("checked", article_multi);
            }
        });
    };
    
    function updateXml() {
        // Update post icon
        $("main article").each(function() {
            if($(this).find("input.article_multi").prop("checked") == true) {
                $(this).find(".article_icon").addClass("fi-comments").removeClass("fi-comment");
            }
            else {
                $(this).find(".article_icon").addClass("fi-comment").removeClass("fi-comments");
            }
        });
        // Update XML
        xml = getXml();
        // Set global variables needed to detect changes
        if(initial_xml == false) { initial_xml = xml; };
        current_xml = xml;
        // Update code display field
        $("aside textarea").val(xml);
        html = xml;
        html = html.replace(/<\?(.*?)\?>/g, "[?$1?]");
        html = html.replace(/<(.*?)>/g, "<span class='tag'>&lt;$1&gt;</span>");
        html = html.replace(/\[\?(.*?)\?\]/g, "<span class='flag'>&lt;?$1?&gt;</span>");
        html = html.replace(/\n/g, "<br>");
        html = html.replace(/\t/g, "&nbsp;&nbsp;&nbsp;&nbsp;");
        $("#code p").html(html);
    };
	updateXml();
    
    // ==========================================
    //            Show loaded content
    // ==========================================
    
    if($("main article").length) {
        showArticles();
        reinitializeAll();
    };
    
    // Fix save -> open URL
    save = urlQuery("save");
    if(save) {
        urlQuery("open=" + save);
    }

});