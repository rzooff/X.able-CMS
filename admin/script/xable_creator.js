$(document).ready(function() {

    urlQuery();
    
    // Hide file manager fields
    $("article input.string").each(function() {
        if($(this).val() == "_file_manager") {
            $(this).closest("article").css({ "position": "fixed", "left": "-100%"});
        };
    });

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
                    if( $(this).find("input.article_multi").prop("checked") == true && tag == memo_tag ) {
                        $(this).find("input.article_tag").val(article_tag);
                    };
                });
            };
        };
    };
	
	function checkTag($tag) {
		if($tag.val() == "") {
			alert("Tag can't be empty");
            $tag.val(memo_tag);
			$tag.focus();
		}
		else if($tag.val().length < 1) {
			alert("At least two charactes needed");
            $tag.val(memo_tag);
			$tag.focus();
		}
		else {
			$tag.val( $tag.val().toLowerCase().replace(/ /g, "_") );
            autoUpdateTag($tag);
			updateXml();
		}
	};
    
    language = $("input#language").val();
    languages = $("input#languages").val().split(",");
    xml_path = $("input#xml_path").val();

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
		if($(this).attr("class") != "separator") {
			// Hide dropdown
			$(this).closest("label").find("ul").stop().hide(100);
			$(this).closest("label").find("p").css({ "opacity": "1" });
			// Actions
			action = $(this).html().replace(/&nbsp;/g, "_").toLowerCase();
			if(action == "quit") {
				if(!changesDetected() || confirm("Your changes will be lost\nAre you sure?")) {
					if(xml_path != "") {
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
                };
            }
			else if(action == "update" || action == "users" || action == "explorer") {
				if(!changesDetected() || confirm("Your changes will be lost\nAre you sure?")) {
					location.href = "xable_" + action + ".php";
				};
			}
			else if(action == "update_code") {
				updateXml();
			}
			else if(action == "save") {
				if(!changesDetected()) {
					alert("No changes to save");
				}
				else {
					$("form#save").submit();
				};
			}
			else if(action == "open") {
				if(!changesDetected() || confirm("Your changes will be lost\nAre you sure?")) {
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
				location.href = "xable_creator.php";
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
                if(dir.indexOf(file_name) < 0 || (confirm("WARNING!\nFile \"" + file_name + "\" exists! Overwrite?"))) {
                    //alert("OK -> " + file_path);
                    $("#popup_container").fadeOut(100);
					$("form#save").attr("action", "xable_creator.php?save=" + encodeURIComponent(file_path)).submit();
                    //location.href = "xable_creator.php?save=" + encodeURIComponent(file_path);
                };
            }
			// ====== OPEN ======
            else if(action == "open") {
                if(dir.indexOf(file_name) < 0) {
                    alert("File \"" + file_name + "\" not found!");
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
        "<span class='fi-comment article_icon'></span>";
    
    articleFooter =
        "<div class='dropdown'><dl class='button'>" +
        "<dt>+</dt>" +
        "<dd>string</dd>" +
        "<dd>text</dd>" +
        "<dd>textarea</dd>" +
        "<dd>table</dd>" +
        "<dd>option</dd>" +
        "<dd>media</dd>" +
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
        $("article").last().show(200, function() {
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
    
    nonEditable = "<label class='option'><input class='non_editable' type='checkbox'><span>non-editable</span></label>";
    
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
		});
		$(".dropdown dl").unbind("mouseleave").mouseleave(function() {
			$(this).find("dd").stop().hide(200);
			$(this).find("dt").css({ "opacity": "1.0" });
		});
		// ====== New Section ======
        $(".dropdown dd").unbind("click").click(function() {
            section_type = $(this).text();
            // Hide dropdown
            $(this).closest("dl").find("dd").stop().hide(200);
            $(this).closest("dl").find("dt").css({ "opacity": "1.0" });
            // Build section HTML
            html = sectionHeader +
                "<label class='section_tag'><span class='type'>" + section_type + "</span><input type='text' class='section_tag string' value='new_section'></label>" +
                nonEditable +
				"<form>" +
				"<input type='text' class='label' value='' placeholder='Label'>" +
				"<input type='text' class='description' value='' placeholder='Description'>";
            // ====== Types ======
			if(section_type == "string") { html = html + "<input type='hidden' class='string' value=''>"; }
			if(section_type == "text") { html = html + textHtml(); }
			else if(section_type == "textarea") { html = html + textareaHtml(); }
			else if(section_type == "option") { html = html + optionHtml(); }
			else if(section_type == "media") { html = html + mediaHtml(); }
			else if(section_type == "table") { html = html + tableHtml(); }
			else {};
            // ===================
			html = html + "</form>";
            // Add new section
            $(this).closest(".dropdown").before("<section>" + html + "</section>");
            $(this).blur();
            // Show new section and focus on tag field
			$(this).closest("article").find("section").last().show(200, function() {
				$(this).find(".section_tag input").focus().select();
			});
            
            // Initialize buttons
            reinitializeAll();

        });
    };
    
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
		"<li><label><input type='checkbox' class='type' value='image'><span class='fi-photo'></span></label><input type='hidden' class='path'><input type='text' class='folder' placeholder='Image folder path'><button class='browse'><span class='fi-folder'></span></button></li>" +
		"<li><label><input type='checkbox' class='type' value='gallery'><span class='fi-thumbnails'></span></label><input type='hidden' class='path'><input type='text' class='folder' placeholder='Gallery folder path'><button class='browse'><span class='fi-folder'></span></button></li>" +
		"<li><label><input type='checkbox' class='type' value='file'><span class='fi-page'></span></label><input type='hidden' class='path'><input type='text' class='folder' placeholder='File folder path'><button class='browse'><span class='fi-folder'></span></button></li>" +
		"<li><label><input type='checkbox' class='type' value='files'><span class='fi-page-copy'></span></label><input type='hidden' class='path'><input type='text' class='folder' placeholder='Files folder path'><button class='browse'><span class='fi-folder'></span></button></li>" +
		"<li><label><input type='checkbox' class='type' value='video'><span class='fi-play-video'></span></label><input type='hidden' class='path' value=''><input type='text' placeholder='Video link' disabled></li>" +
		"<li><label><input type='checkbox' class='type' value='none'><span class='fi-prohibited'></span></label><input type='text' class='path' placeholder='None' value='' disabled></li>" +
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
                        alert("Existing FILE(s) path changed.\nRemember to move them to the new location!")
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
    
	textareaHeader = "<div class='format'>" +
		"<label><span class='fi-asterisk'></span><input type='checkbox' value='*' class='all'></label>" +
		"<label><span class='fi-bold'></span><input type='checkbox' value='b'></label>" +
		"<label><span class='fi-italic'></span><input type='checkbox' value='i'></label>" +
		"<label><span class='fi-underline'></span><input type='checkbox' value='u'></label>" +
		"<label><span class='index'>X<sup>2</sup></span><input type='checkbox' value='^'></label>" +
		"<label><span class='index'>X<sub>2</sub></span><input type='checkbox' value='v'></label>" +
		"<label><span class='fi-list-bullet'></span><input type='checkbox' value='='></label>" +
		"<label><span class='fi-align-center'></span><input type='checkbox' value='c'></label>" +
		"<label><span>&bull;</span><input type='checkbox' value='-'></label>" +
		"<label><span class='fi-minus'></span><input type='checkbox' value='_'></label>" +
        "<label><span class='fi-mail'></span><input type='checkbox' value='m'></label>" +
		"<label><span class='fi-link'></span><input type='checkbox' value='l'></label>" +
		"<label><span class='fi-page-filled'></span><input type='checkbox' value='a'></label>" +
		"<label><span class='fi-photo'></span><input type='checkbox' value='f'></label>" +
		"<label><span class='fi-layout'></span><input type='checkbox' value='p'></label>" +
		"<div class='buttons'><button class='none'>None</button></div>" +
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
            $container.hide().show(200);            
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
                    $container.prev().find("select").each(function() {
                        option = select_set[n++];
                        $(this).find("option").each(function() {
                            if( $(this).val() == option ) { $(this).prop("selected", true); };
                        });
                    });
                    updateXml();
                }, 10);
            };
            // initialize buttons for clone
            reinitializeAll();
			return false;
        });
	};
    
    memo_tag = false; // Global Variable!
    
    function reinitializeAll() {
        $("form input").unbind("change");
        
        initializeNewSectionButton();
        initializeMediaButtons();
        initializeNewOptionButton();
        initializeEditButtons();

        $("input.article_tag").unbind("blur").blur(function() { checkTag( $(this) ); memo_tag = false; });
        $("input.article_multi").unbind("change").change(function() { updateXml(); });
        $("input.non_editable").unbind("change").change(function() { updateXml(); });
        $("input.section_tag").unbind("blur").blur(function() { checkTag( $(this) ); });
        $("form input").change(function() { updateXml(); });

        initializeFormatButtons();
        updateXml();
        
        // Needed for autoUpdateTag()
        $("input.article_tag").unbind("focus").focus(function() {
            if(memo_tag == false) { memo_tag = $(this).val(); };
        });
    };
    
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
                        if(tag == "text") { alert("val: " + val); };
                        if(( tag != "label" || val != "") && (tag != "description" || val != "" )) { // no empty label or description
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
    
    function updateXml() {
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
	
});