$(document).ready(function() {
    
    //$("form").css({ "min-height": $("nav").height() });
    
    // Disable submit on enter click in input field
    $('form main input').on('keypress', function (e) { if(e.which == 13){ return false; }; });
    
    // ==========================================
    //             Global Variables
    // ==========================================

    //$("main").css({ "min-height": $("nav").height() });
    
    var site_root = $("input#root").val();
    var current_path = $("input#path").val();
    var edit_path = $("input#edit_path").val();
    var save_path = $("input#save_path").val();
    var languages = $("input#languages").val().split(",");
    var lang = $("input#lang").val();
    var admin_lang = $("input#admin_lang").val();
    var textarea_min = 120;
    
    protected_files = [ site_root + "/settings.xml", site_root + "/navigation.xml" ];

    // ==========================================
    //             Detect changes
    // ==========================================
    
    if(edit_path.path("extension") == "template") {
        $("#detectChanges").val("NEW");
    };
    
    function ContenteditableChange() {
        // Add change trigger to contenteditable elements
        $('body').on('focus', '[contenteditable]', function() {
            var $this = $(this);
            $this.data('before', $this.html());
            return $this;
        }).on('blur keyup paste input', '[contenteditable]', function() {
            var $this = $(this);
            if ($this.data('before') !== $this.html()) {
                $this.data('before', $this.html());
                $this.trigger('change');
            }
            return $this;
        });
    };

    function initializeDectectChanges() {
        ContenteditableChange();
        
        $("td").blur(function() {
            content = $(this).html();
            content = content.replace(/<br>/gi, "[br]"); // Line breaks
            content = content.replace(/\n/g, ""); // Line breaks
            $(this).html(content);
            content = $(this).text().replace(/\[br\]/gi, "<br>");
            $(this).html(content);
        });
        
        // Fix ctr-enter line separator
        $("textarea").on("paste", function() {
            $textarea = $(this);
            setTimeout(function() {
                content = $textarea.val();
                var ls = String.fromCharCode(8232);
                find = new RegExp(ls + "(.?)", "g");
                content = content.replace(find, "\n$1");
                $textarea.val(content);
                
            }, 200);
        });
        
        // Auto resize textarea height to content
        $("textarea").each(function() { textareaResize($(this), textarea_min); });
        $("textarea").unbind("keyup").keyup(function() { textareaResize($(this), textarea_min); });
        
        // Fix unchecked radio option
        $("section ul.radio").each(function() {
            if(!$(this).find("input:checked").length) {
                $(this).find("input").first().prop("checked", true);
            };
        });
    };

    initializeDectectChanges();
    
    // ==========================================
    //             Scroll to search
    // ==========================================
    
    if($("input#search-scroll").length) {
        setTimeout(function() {
            var find = $("input#search-scroll").val().toLowerCase();
            //alert(find);
            var search_scroll = false;
            $("form section").each(function() {
                if($(this).children(".text").length) {
                    if($(this).find("textarea").length) {
                        text = $(this).find("textarea." + lang).text().toLowerCase();
                    }
                    else {
                        text = $(this).find("input." + lang).val().toLowerCase();
                    };
                    if(text.indexOf(find) > -1) {
                        if(search_scroll == false) { search_scroll = $(this).offset().top; };
                        //$(this).css({ "outline": "1px solid #ffcc33" });
                        $(this).find(".text input, .text textarea").css({ "background-color": "#ffffbb" });
                    };
                }
                else if($(this).children(".string").length) {
                    text = $(this).children(".string").val();
                    if(text.indexOf(find) > -1) {
                        if(search_scroll == false) { search_scroll = $(this).offset().top; };
                        //$(this).css({ "outline": "1px solid #ffcc33" });
                        $(this).css({ "background-color": "#ffffbb" });
                    };
                }
                else if($(this).children(".table").length) {
                    $(this).find("td").each(function() {
                        text = $(this).text().toLowerCase();
                        if(text.indexOf(find) > -1) {
                            if(search_scroll == false) { search_scroll = $(this).offset().top; };
                            //$(this).css({ "outline": "1px solid #ffcc33" });
                            $(this).css({ "background-color": "#ffffbb" });
                        };
                    });

                };

            });
            if(search_scroll != false && search_scroll > 30) {
                $("html, body").animate({ scrollTop: search_scroll - 30 }, 500);
            };
        }, 1000); // wait for article auto-fold complete
    };
    
    // ==========================================
    //                Navigation
    // ==========================================

    function initializeNav() {
    // --------------------------------
    // current_path = <string> !Global
    // --------------------------------
    // Initialize nav bar links dynamic style
    // --------------------------------
        $("nav dl a").each(function() {
            path = $(this).attr("href").split("?path=").pop().split("&lang=").shift();
            if(path == current_path) {
                $(this).removeClass("active").addClass("current");
            }
            else {
                $(this).removeClass("current").addClass("active");
            };
        });
        $("nav a.current").unbind("click").click(function() { return false; $(this).blur(); });
        
        $("nav dt").each(function() {
            if(jQuery.type($(this).attr("help")) == "string" && $(this).attr("help") != "") {
                help = $(this).attr("help");
                html = "<span class='fi-info manual show_manual'></span>";
                $(this).append(html);
                $(this).find(".show_manual").attr("help", help);
            };
        })
    };
    initializeNav();
    
    function deletePage(path) {
        showPopup("fi-trash", "Czy na pewno chcesz usunąć stronę?<br>\"" + path.path("basename") + "\"<br><br><em><b>UWAGA!</b> Usunięte zostaną wszystkie powiązane z nią pliki.<br>Tej operacji nie da się cofnąć!</em>", "Tak,Nie");
        $("#popup_box .confirm").click(function() {
            $("#cms").attr("action", "_remove.php?path=" + path).submit(); // output
        });        
        $("#popup_box .cancel").click(function() {
            hidePopup();
        });
    };

    $("nav .remove").click(function() {
        $(this).blur();
        path = $(this).closest("a").attr("href").split("?path=").pop().split("&lang=").shift();
        // Check protected files list
        if(protected_files.indexOf(path) > -1 || path.path("extension") == "order") {
            infoPopup("Tego pliku nie można usunąć!");
        }
        // Remove
        else {
            if(detectChanges()) {
                showPopup("fi-alert", "Nie zapisano zmian!<br>Czy mimo to chcesz kontynuować?", "Tak,Nie");
                $("#popup_box .confirm").click(function() { hidePopup(); deletePage(path); });
                $("#popup_box .cancel").click(function() { hidePopup(); return false; });
            }
            else {
                deletePage(path);
            };
        };
        // end
        return false;
    });
	
	// Unsaved changes protection
    $("nav a, footer a.unsaved").click(function() {
        if(!$(this).find("#page_preview").length) {
            href = $(this).attr("href");
            if(detectChanges()) {
                showPopup("fi-alert", "Nie zapisano zmian!<br>Czy mimo to chcesz kontynuować?", "Tak,Nie");
                $("#popup_box .confirm").click(function() { location.href = href; });
                $("#popup_box .cancel").click(function() { hidePopup(); return false; });
                $(this).blur();
                return false;
            }
            else {
                $(this).blur();
                return true;
            };
        };
    });

    // ==========================================
    //               Save / Cancel
    // ==========================================
    
    // Cancel / reset changes
	$("header button.cancel").click(function() {
        if(detectChanges()) {
            url = window.location.href.split("?");
            if(url.length > 1) {
                get = url.pop().split("&");
                for(i in get) {
                    if(get[i].substr(0, 5) == "lang=") { get[i] = "lang=" + lang; };
                }
                get = get.join("&");
            }
            else {
                get = "path=" + current_path + "&lang=" + lang;
            };
            $("#confirm_popup button").unbind();
            showPopup("fi-refresh", "Czy na pewno chcesz anulować wszystkie zmiany?", "Tak,Nie");
            $("#popup_box .confirm").click(function() {
                if(edit_path.path("extension") == "template") {
                    $("#cms").attr("action", "index.php").submit();
                }
                else {
                    $("#cms").attr("action", "index.php?" + get).submit();
                };
            });        
            $("#popup_box .cancel").click(function() {
                hidePopup();
                $(this).unbind();
            });
            $(this).blur();
            return false;
        }
        else {
            infoPopup("Nie dokonano żadnych zmian");
            $(this).blur();
            return false;
        };
	});
    
    $("header button.publish").click(function () {
        if(detectChanges() || edit_path.path("extension") == "draft") {
            updateOutput();
            return true;
        }
        else {
            infoPopup("Brak zmian do publikacji");
            $(this).blur();
            return false;
        };
    });
    
    $("header button.save").click(function () {
        if(detectChanges()) {
            updateOutput();
            return true;
        }
        else {
            infoPopup("Nie dokonano żadnych zmian");
            $(this).blur();
            return false;
        };
    });
    
    $("header button.discard").click(function () {
        if($("input#published_version").val() != "true") {
            infoPopup("Ta strona posiada jedynie wersję roboczą");
            $(this).blur();
            return false;
        }
        else if(edit_path.path("extension") != "draft") {
            infoPopup("Brak wersji roboczej do odrzucenia");
            $(this).blur();
            return false;
        }
        else {
            $form = $(this).closest("form");
            updateOutput();
            showPopup("fi-trash", "Czy na pewno chcesz odrzucić wszystkie zmiany?", "Tak,Nie");
            $("#popup_box .confirm").click(function() {
                name = $button.attr("name");
                val = $button.val();
                $form.prepend("<input name='" + name + "' value='" + val + "'>").submit();
            });        
            $("#popup_box .cancel").click(function() {
                hidePopup();
                $(this).unbind();
            });
            return false;
        };
    });
    
    $("header button.unpublish").click(function () {
        file_path = save_path.substr(site_root.length + 1);
        if(file_path.path("dirname") == "") {
            infoPopup("Ten dokument musi posiadać wersję publiczną");
            $(this).blur();
            return false;
        }
        else if($("input#published_version").val() == "true") {
            $form = $(this).closest("form");
            updateOutput();
            showPopup("fi-prohibited", "Czy na pewno chcesz wycofać wersję publiczną?", "Tak,Nie");
            $("#popup_box .confirm").click(function() {
                name = $button.attr("name");
                val = $button.val();
                $form.prepend("<input name='" + name + "' value='" + val + "'>").submit();
            });        
            $("#popup_box .cancel").click(function() {
                hidePopup();
                $(this).unbind();
            });
            return false;
        }
        else {
            infoPopup("Brak wersji publicznej do wycofania");
            $(this).blur();
            return false;
        };
    });
    
    $("header button.revert").click(function () {
        if($("input#previous_version").val() == "true") {
            $form = $(this).closest("form");
            updateOutput();
            showPopup("fi-rewind", "Czy na pewno chcesz przywrócić poprzednią wersję?", "Tak,Nie");
            $("#popup_box .confirm").click(function() {
                name = $button.attr("name");
                val = $button.val();
                $form.prepend("<input name='" + name + "' value='" + val + "'>").submit();
            });
            $("#popup_box .cancel").click(function() {
                hidePopup();
                $(this).unbind();
            });
        }
        else {
            infoPopup("Brak poprzedniej wersji do przywrócenia");
            $(this).blur().css("outline", "none");
        }
        return false;
    });
    
    $("#page_preview").click(function() { $(this).blur(); });
    
    // ==========================================
    //                Translate
    // ==========================================
    
    // Get dictionary.csv content from php
    translate_dictionary = $("input#translate_dictionary").val().split("|");
    
    dictionary = [];
    for(i in translate_dictionary) {
        item = translate_dictionary[i].split(";");
        if(item[0].substr(0, 2) != "//" && item.length == 2) {
            dictionary[ item[0].capitalize() ] = item[1].capitalize();
            dictionary[ item[0].toUpperCase() ] = item[1].toUpperCase();
            dictionary[ item[0].toLowerCase() ] = item[1].toLowerCase();
        };
    };
    
    function translate(text) { // used in: setButtons()
    // --------------------------------
    // text = <string>
    // --------------------------------
    // RETURNS: <string> Translation based on dictionary
    // --------------------------------
        for(word in dictionary) {
            find = new RegExp(word, "g");
            text = text.replace(find, dictionary[word]);
        };
        return text;
    };

    // Auto translate media types
    $("div.set span").each(function() {
        $(this).html( translate($(this).html()) );
    }); 
    
    // ==========================================
    //            User & Notifications
    // ==========================================
    
    $(".pull_down").mouseenter(function() { $(this).find("li").stop().show(200); });
    $(".pull_down").mouseleave(function() { $(this).find("li").stop().hide(200); });
    
    $("#menu_user .user_logout").click(function() { hrefAction("_logout.php"); });
    $("#menu_user .user_password").click(function() { hrefAction("password.php?page=" + save_path); });
    $("#menu_notifications .old_backup").click(function() { hrefAction("backup.php?page=" + save_path); });
    $("#menu_notifications .new_edit").click(function() { hrefAction("index.php?path=" + $(this).attr("path")); });

    function hrefAction(href) {
        if(detectChanges()) {
            showPopup("fi-alert", "Nie zapisano zmian!<br>Czy mimo to chcesz kontynuować?", "Tak,Nie");
            $("#popup_box .confirm").click(function() { location.href = href; });
            $("#popup_box .cancel").click(function() { hidePopup(); return false; });
            $(this).blur();
            return false;
        }
        else {
            location.href = href;
            $(this).blur();
            return true;
        };
    };
    
    $("#menu_notifications .show_log").click(function() {
        href = "show-log.php?page=" + save_path;
        if(detectChanges()) {
            showPopup("fi-alert", "Nie zapisano zmian!<br>Czy mimo to chcesz kontynuować?", "Tak,Nie");
            $("#popup_box .confirm").click(function() { location.href = href; });
            $("#popup_box .cancel").click(function() { hidePopup(); return false; });
            $(this).blur();
            return false;
        }
        else {
            location.href = href;
            $(this).blur();
            return true;
        }
        
    });
    
    // ==========================================
    //                 Languages
    // ==========================================
    
    $("form").append("<input type='hidden' id='language_set' name='language_set' value=''>");
    
    /*
    function addLangManual() {
    // --------------------------------
    // Add info to languages icon
    // --------------------------------
        manual = "Brakujące wpisy w innych językach zostaną automatycznie uzupełnione treścią z wersji " + admin_lang.toUpperCase()
        $("#lang span").addClass("manual").attr("help", manual);
    };
    addLangManual();
    */
    
    function fillMissingLanguages() {
    // --------------------------------
    // languages = <array> !Global
    // admin_lang = <string> !Global
    // --------------------------------
    // Fill missing language tags based on default language data
    // --------------------------------
        $("form section div.text").each(function() {
            $deflt = $(this).find("." + admin_lang);
            if($deflt.length) {
                deflt_html = $deflt.get(0).outerHTML;
                if($deflt.prop("tagName") == "TEXTAREA") {
                    deflt_html = deflt_html.replace(/<textarea (.*?)>(.*?)<\/textarea>/, "<textarea $1></textarea>");
                }
                else { // INPUT
                    deflt_html = deflt_html.replace(/ value=\"(.*?)\"/, " value=\"\"");
                };
                for(i = 0; i < languages.length; i++) {
                    $lang_content = $(this).find("." + languages[i]);
                    if(!$lang_content.length) {
                        deflt_html = deflt_html.replace("class=\"" + admin_lang + "\"", "class=\"" + languages[i] + "\"");
                        $(this).append( deflt_html );
                    };
                };
            };
        });
    };
    fillMissingLanguages()
    
    function hideShowLangMenu(time) {
    // --------------------------------
    // lang = <string> !Global
    // --------------------------------
    // Show only the current language in menu
    // --------------------------------
        if(time == undefined) { time = 0; };
        $("#lang li").each(function() {
            if($(this).attr("value") == lang) {
                $(this).show(time);
            }
            else {
                $(this).hide(time);
            };
        });
    };
    hideShowLangMenu();
    
    function changeLanguage(time) {
    // --------------------------------
    // lang = <string> !Global
    // --------------------------------
    // Show only the current language content
    // --------------------------------
        if(time == undefined) { time = 0; };
        $("#lang li").stop();
        $("form article").animate({ "opacity": "0" }, time, function() {
            $("form .text input, form .text textarea, form .table table").each(function() {
                if($(this).closest("article").attr("class").substr(0, 1) != "_") { // only for xml
                    language = $(this).attr("class");
                    if(language == lang) { $(this).show() }
                    else { $(this).hide() };
                };
            });
            $("form article").animate({ "opacity": "1" }, time);
        });
        // Update languade in navigation links
        $("nav dl a").each(function() {
            link = $(this).attr("href").split("&lang=");
            href = link[0] + "&lang=" + lang;
            //alert("link: " + link[0] + ", path: " + path);
            $(this).attr("href", href);
        });
        // Update output -> back from publish
        $("input#language_set").val(lang);
        initializeDectectChanges();
    };
    changeLanguage();

    // Initialize language menu (if more than one language exists)
    if(languages.length > 1) {
        $("#lang ul").mouseenter(function() {
            $("#lang li").stop().show(200);
        })    
        $("#lang ul").mouseleave(function() {
            hideShowLangMenu(200);
        });
        $("#lang li").click(function() {
            lang = $(this).attr("value");
            hideShowLangMenu(200)
            changeLanguage(200);
        });
    };

    // ==========================================
    //                Help Popup
    // ==========================================

	function initializeHelp() { // used in: style functions, new post
	// -------------------------------------------
	// Initialize show help popup on element hoover
	// -------------------------------------------
        // Create popup HTML if not exists
        if( $("#help_popup").length == 0 ) { $("body").append("<div id='help_popup'><p>Help</p></div>"); };
        $activeElements = $("button, label, .help, .manual");
        $activeElements.unbind("mouseenter").unbind("mouseleave");
        $activeElements.click( function() { $("#help_popup").hide(); });
        // ====== Deactivate help popup on touch screen ======
        $activeElements.on("touchstart", function() { $activeElements.unbind("mouseenter"); });
        // ====== MouseEnter ======
		$activeElements.on("mouseenter", function() {
			marg = 3; // popup margin in px
			$button = $(this);
			// ====== MouseLeave  ======
			$button.on("mouseleave", function() {
				$("#help_popup").stop().fadeOut(100);
			});
			$("#touch_mouseleave").click(function() {
				$("#help_popup").stop().fadeOut(100);
			});
			$("#help_popup").hide(0, function() { // hide previous if any
				help = $button.attr("help"); // Read help attribute
				if(help != undefined) {
                    // Maual type help class
                    if($button.attr("class").split(" ").indexOf("manual") > -1) {
                        $("#help_popup").addClass("black_bubble").removeClass("white_bubble");
                    }
                    // Standard type help class
                    else {
                        $("#help_popup").removeClass("black_bubble").addClass("white_bubble");
                    };
					// ====== Change popup position ======
					xy = $button.offset();
					win_top = $("body").scrollTop();
					win_bot = win_top + $(window).height();
					if(parseInt( xy.top ) - win_top > (win_bot - parseInt( xy.top )) * 3) { dir = "up"; } else { dir = "down"; }; // 3/1
					wid = $button.width() + parseInt( $button.css("padding-left") ) * 2;
					hei = $button.height() + parseInt( $button.css("padding-top") ) * 2;
                    if( $button.parent("div").attr("id") == "style_buttons" ) { dir = "up"; }; // Force help up to not cover the textarea
                     // Popup over button
					if( dir == "up" && parseInt(xy.left) > ($(window).width() / 2) ) { // popun on the left
						//alert("up left");
						$("#help_popup").css({
							"top": "inherit", "bottom": $(window).height() - xy.top + marg * 1.5,
							"left": "inherit", "right": $(window).width() - xy.left + marg - (wid / 2),
							"border-radius": "10px 10px 0 10px"
						});
					}
					else if( dir == "up" && parseInt(xy.left) <= ($(window).width() / 2) ) { // popun on the right
						//alert("up right");
						$("#help_popup").css({
							"top": "inherit", "bottom": $(window).height() - xy.top + marg * 1.5,
							"left": xy.left + wid + marg - (wid / 2), "right": "inherit",
							"border-radius": "10px 10px 10px 0" 
						});
					}
					// Popup below button
					else if( dir == "down" &&  parseInt(xy.left) > ($(window).width() / 2) ) { // popun on the left
						//alert("down left");
						$("#help_popup").css({
							"top": xy.top + hei + marg * 1.5, "bottom": "inherit", 
							"left": "inherit", "right": $(window).width() - xy.left + marg - (wid / 2),
							"border-radius": "10px 0 10px 10px"
						});
					}
					else if( dir == "down" && parseInt(xy.left) <= ($(window).width() / 2) ) { // popun on the right
						//alert("down right");
						$("#help_popup").css({
							"top": xy.top + hei + marg * 1.5, "bottom": "inherit",
							"left": xy.left + wid + marg - (wid / 2), "right": "inherit",
							"border-radius": "0 10px 10px 10px" 
						});
					};
                    // ====== Popup special styles ======
                    help = help.replace(/<d>/gi, "<span class='small_info'>");
                    help = help.replace(/<\/d>/gi, "</span>");
					// ====== Change popup text & show ======
                    $("#help_popup").html(help).fadeIn(250);
				}
                else {
                    $("#help_popup").stop().hide();
                };
			});
		});
	};
    initializeHelp();

    // ==========================================
    //                  Popup
    // ==========================================
    
    function showPopup(icon, text, buttons, input) {
    // --------------------------------
    // text = <string>
    // buttons = <string> BUTTONS labels
    // input = optional: <boolean> show INPUT; or <string> input placeholder content
    // --------------------------------
    // Adds & show page fader & popup box
    // --------------------------------
        $("#page_fader, #popup_container").remove();
        html = "<div id='page_fader'></div>";
        html = html + "<div id='popup_container'><div id='popup_box'>";
        html = html + "<h6><span class='" + icon + "'></span></h6>";
        html = html + "<p class='info'>" + text + "</p>";
        if(input != undefined && input != false) {
            if(jQuery.type(input) != "string") { input = ""; };
            html = html + "<input type='text' placeholder='" + input + "'>";
        };
        // Buttons
        buttons = buttons.split(",");
        if(buttons.length == 2) {
            html = html + "<div class='buttons two'><button class='confirm'>" + buttons[0] + "</button><button class='cancel'>" + buttons[1] + "</button></div>";
        }
        else {
            html = html + "<div class='buttons one'><button class='confirm'>" + buttons[0] + "</button></div>";
        };
        // End
        html = html + "</div></div>"; // close popup box & container
        $("body").append( html );
        
        $("#page_fader").fadeIn(500);
        $("#popup_container").fadeIn(250, function() {
            // Focus on firt input
            if( $("#popup_container input").length ) { $("#popup_container input").first().focus(); };
            // Enter for confirm
            $("#popup_box input").on('keypress', function (e) {
                if(e.which == 13) {
                    $(this).closest("#popup_box").find("button.confirm").trigger( "click" );
                };
            });
        });
    };

    function hidePopup() {
    // --------------------------------
    // Hide & remove page fader & popup box
    // --------------------------------
        $("#page_fader, #popup_container").fadeOut(250, function() { $(this).remove(); });
    };
    
    function infoPopup(text, icon) {
    // --------------------------------
    // text = <string> mesage text
    // icon = <string> icon type
    // --------------------------------
    // Show custom vanishing alert box
    // --------------------------------
        // icon
        color = false;
        if(icon == "done") { icon = "fi-checkbox"; color = "color_done"; }
        else if(icon == "error") { icon = "fi-x"; color = "color_error"; }
        else if(icon == "alert") { icon = "fi-alert"; }
        else if(jQuery.type(icon) != "string" || icon.substr(0, 3) != "fi-") { icon = "fi-lightbulb"; };
        // show popup
        $("#info_box").remove();
        html = "<div id='info_box'>";
        html = html + "<h6><span class='" + icon + "'></span></h6>";
        html = html + "<p class='info'>" + text + "</p>";
        html = html + "</div>"; // close popup box & container
        $("body").append( html );
        //if(color != false) { $("#info_box h6").css({ "background-color": color }); };
        if(color != false) { $("#info_box h6").addClass(color); };
        $("#info_box").fadeIn(250).delay(1250).fadeOut(1500, function() { $(this).remove(); });
    };
    
    // ==========================================
    //                   Table
    // ==========================================
    
    function tableHeadings() {
        $("div.table table").each(function() {
            n = -1;
            $(this).find("tr").first().find("td").each(function() {
                if(n > -1) { $(this).text( String.fromCharCode(65 + n) ); };
                n++;
            });
            n = 0;
            $(this).find("tr").each(function() {
                if(n > 0) { $(this).children("td").first().text(n); };
                n++;
            });
        });
    };
    tableHeadings();
    
	function initializeTable() {
        
        // Add table row extra button
        $(".add_table_row").unbind("click").click(function() {
            $row = $(this).closest("section").find("table." + lang + " tr").last();
            if($row.length) {
                num = $row.attr("class").split("_").pop();
                $row.after( $row.clone() );
                $row.next().find("td").text("");
                $row.next().attr("class", "num_" + ++num);
				tableHeadings();
				initializeTable();
            };
            $(this).blur();
            return false;
        });
        
        // Edit row
		$("table .edit_row").unbind("contextmenu").bind("contextmenu", function() {
			// ====== Variables ======
			$table = $(this).closest("table");
			row_id = $(this).closest("tr").attr("class").split(" ").shift();
			// ====== Row Highlight ======
			$("tr").css({ "background-color": "inherit" });
			$(this).closest("tr").css({ "background-color": "#ccffee" });
			// ====== Menu Popup ======
			html = "<ul id='table_edit'>" +
				"<li class='move_up'>Przesuń wiersz w górę</li>" +
				"<li class='move_down'>Przesuń wiersz w dół</li>" +
				"<li class='new_above'>Dodaj wiersz powyżej</li>" +
				"<li class='new_below'>Dodaj wiersz poniżej</li>" +
				"<li class='delete_row'>Usuń wiersz</li>" +
				"</ul>";
			xy = $(this).offset();
			x = xy.left + $(this).width() + (parseInt( $(this).css("padding-left") ) * 2);
			y = xy.top - $(window).scrollTop() + $(this).height() + (parseInt( $(this).css("padding-top") ) * 2);
			$("body").append(html);
			$("#table_edit").fadeIn(250).css({ "bottom": $(window).height() - y, "right": $(window).width() - x, "min-width": "auto" });
			// ====== Menu Actions ======
			// Hide on mouseleave
			$("#table_edit").unbind("mouseleave").mouseleave(function() {
				$(this).fadeOut(250, function() { $(this).remove(); });
				$("tr").css({ "background-color": "inherit" });
			});
			// Hide after click
			$("#table_edit li").unbind("click").click(function() {
				$("#table_edit").fadeOut(250, function() { $(this).remove(); });
				$("tr").css({ "background-color": "inherit" });
			});
			// Delete Row
			$("#table_edit .delete_row").click(function() {
				if($table.find("tr").length > 2) { // preserve the last data column!
					$table.find("tr." + row_id).fadeOut(250, function() {
						$(this).remove();
						tableHeadings();
					});
				}
				else {
					infoPopup("Nie można usunąć jedynego wiersza tabeli");
				};
			});
			// Move Row
			$("#table_edit .move_up, #table_edit .move_down").click(function() {
				$row = $table.find("tr." + row_id);
				if($(this).attr("class") == "move_up") {
					if( $row.prev().prev().length ) {
						$row.fadeOut(250, function() {
							$row.prev().before( $row.clone() );
							$row.prev().prev().fadeIn(250);
							$row.remove();
						});
					}
					else {
						infoPopup("Wyżej się już nie da");
					};
				}
				else {
					if( $row.next().length ) {
						$row.fadeOut(250, function() {
							$row.next().after( $row.clone() );
							$row.next().next().fadeIn(250);
							$row.remove();
						});
					}
					else {
						infoPopup("Niżej się już nie da");
					};
				};
				setTimeout(function() {
					tableHeadings();
					initializeTable();
				}, 500);
			});
			// New Row
			$("#table_edit .new_above, #table_edit .new_below").click(function() {
				// find new tr (class) id
				new_id = 0;
				$table.find("tr").each(function() {
					
					id = $(this).attr("class").split("_");
					if(id.length == 2 && parseInt(id[1]) >= new_id) { new_id = parseInt(id[1]) + 1; };
					
					//alert("id: " + $(this).attr("class") + " / " + new_id);
				});

				$row = $table.find("tr." + row_id);
				
				if($(this).attr("class") == "new_below") {
					$row.after( $row.clone() );
					$row.next().find("td").text("");
					$row.next().attr("class", "num_" + new_id);
				}
				else {
					$row.before( $row.clone() );
					$row.prev().find("td").text("");
					$row.prev().attr("class", "num_" + new_id);
				};
				tableHeadings();
				initializeTable();
			});
			return false; // block stock context menu
		});
    
		// Edit column
		$("table .edit_column").unbind("click").bind("contextmenu", function() {
			// ====== Variables ======
			$table = $(this).closest("table");
			col_id = $(this).attr("class").split(" ").shift();
			// ====== Column Highlight ======
			resetTableColumnColor();
			$table.find("td." + col_id).each(function() {
				if($(this).closest("tr").attr("class") != "start") {
					$(this).css({ "background-color": "#ccffee" });
				};
			});
			// ====== Menu Popup ======
			$("#table_edit").remove();
			html = "<ul id='table_edit'>" +
				"<li class='move_left'>Przesuń kolumnę w lewo</li>" +
				"<li class='move_right'>Przesuń kolumnę w prawo</li>" +
				"<li class='new_left'>Dodaj kolumnę po lewej</li>" +
				"<li class='new_right'>Dodaj kolumnę po prawej</li>" +
				"<li class='delete_column'>Usuń kolumnę</li>" +
				"</ul>";
			xy = $(this).offset();
			x = xy.left + $(this).width() + (parseInt( $(this).css("padding-left") ) * 2);
			y = xy.top - $(window).scrollTop() + $(this).height() + (parseInt( $(this).css("padding-top") ) * 2);
			wid = $(this).width() + (parseInt( $(this).css("padding-left") ) * 2);
			$("body").append(html);
			$("#table_edit").fadeIn(250).css({ "bottom": $(window).height() - y, "right": $(window).width() - x, "min-width": wid });
			
			// ====== Menu Actions ======
			// Hide on mouseleave
			$("#table_edit").unbind("mouseleave").mouseleave(function() {
				$("#table_edit").fadeOut(250, function() { $(this).remove(); });
				resetTableColumnColor();
			});
			// Hide after click
			$("#table_edit li").unbind("click").click(function() {
				$("#table_edit").fadeOut(250, function() { $(this).remove(); });
				resetTableColumnColor();
			});
			// Delete Column
			$("#table_edit .delete_column").click(function() {
				if($table.find("tr").first().find("td").length > 2) { // preserve the last data column!
					$table.find("td." + col_id).fadeOut(250, function() {
						$(this).remove();
						tableHeadings();
					});
				}
				else {
					infoPopup("Nie można usunąć jedynej kolumny tabeli");
				};
			});
			// Move Column
			$("#table_edit .new_right, #table_edit .new_left").click(function() {
				$column = $table.find("td." + col_id);
                dir = $(this).attr("class");
                // Find new id
                new_id = 0;
                $table.find("tr").first().find("td").each(function() {
                    id = $(this).attr("class").split("_");
                    if(id[0] == "num" && id[1].charCodeAt(0) >= new_id) { new_id = id[1].charCodeAt(0) + 1; };
                });
                new_id = "num_" + String.fromCharCode(new_id);
                // Move column
                $table.find("td." + col_id).each(function() {
                    if(dir == "new_left") {
                        $(this).before( $(this).clone() );
                        $new = $(this).prev();
                    }
                    else {
                        $(this).after( $(this).clone() );
                        $new = $(this).next();
                    };
                    $new.removeClass(col_id);
                    $new.attr("class", new_id + " " + $new.attr("class"));
                    $new.text("");
                    tableHeadings();
                    initializeTable();
                });
            });
            // New Column
			$("#table_edit .move_right, #table_edit .move_left").click(function() {
                $column = $table.find("td." + col_id);
                if($(this).attr("class") == "move_right") {
                    if($column.first().next().length) {
                        $column.each(function() {
                            $(this).next().after( $(this).clone() );
                            $(this).remove();
                        }); 
                    }
                    else {
                        infoPopup("Bardziej w prawo już się nie da");
                    };
                }
                else {
                    if($column.first().prev().prev().length) {
                        $column.each(function() {
                            $(this).prev().before( $(this).clone() );
                            $(this).remove();
                        }); 
                    }
                    else {
                        infoPopup("Bardziej w lewo już się nie da");
                    };
                };
                setTimeout(function() {
                    tableHeadings();
                    initializeTable();
                }, 500);
			});
            return false; // block stock context menu
		});
	};
	initializeTable();

    function resetTableColumnColor() {
        $("table").find("tr").each(function() {
            if($(this).attr("class") != "start") {
                $(this).children("td").each(function() {
                    if($(this).attr("class").split(" ").shift() != "heading") {
                        $(this).css({ "background-color": "inherit" });
                    };
                });
            };
        });
    };

    // ==========================================
    //             Text Style Editor
    // ==========================================

    var keepStyleButtons = false; // Keep style buttons (after style edit)
	
	$.fn.selectRange = function(start, end) {
	// --------------------------------
	// Set cursor/selection position in textarea
	// --------------------------------
		if(end === undefined) {
			end = start;
		}
		return this.each(function() {
			if('selectionStart' in this) {
				this.selectionStart = start;
				this.selectionEnd = end;
			} else if(this.setSelectionRange) {
				this.setSelectionRange(start, end);
			} else if(this.createTextRange) {
				var range = this.createTextRange();
				range.collapse(true);
				range.moveEnd('character', end);
				range.moveStart('character', start);
				range.select();
			}
		});
	};

    function initializeShowStyle() {
	// --------------------------------
	// Show style buttons on focus
	// --------------------------------
		$("textarea").focus(function() {
			$("textarea").unbind("focus");
            
            if($(this).prev().attr("id") == "style_buttons") {
                initializeHideStyle();
            }
            else if($("#style_buttons").length) {
                $("#style_buttons").remove();
                showStyleButtons($(this));
                initializeHideStyle();
            }
            else {
                showStyleButtons($(this));
                initializeHideStyle();
            };
            initializeHelp(); // For new style buttons
		});
    };
	initializeShowStyle();
    
    function initializeHideStyle() {
	// --------------------------------
	// Hide style buttons on blur / focus change
	// --------------------------------
		$("textarea").blur(function() {
			$("textarea").unbind("blur");
            initializeShowStyle();
		});
	};

	function showStyleButtons($textarea) {
        // ====== Show style buttons bar ======
        format = $textarea.closest("section").find("input.format").val();
        if(format != undefined && format != "") {
            html = generateStyleButtons(format);
            $textarea.before(html);
            $("#style_buttons").show(200);
        };
        // ====== Manual ======
        manual = [
                "<span class='title'>Formatowanie w standardzie BBCode</span>",
                "Aby dodać formatowanie, zaznacz tekst i naciśnij odpowiedni guzik.",
                "Aby usunąć znaczniki BBCode, zaznacz tekst i naciśnij guzik \"Usuń formatowanie\".",
                "",
                "<span class='title'>Przykłady:</span>",
                "<span class='code'>[b]</span>tekst<span class='code'>[/b]</span> -> <b>tekst</b>",
                "<span class='code'>[url=</span>http://google.com<span class='code'>]</span>link<span class='code'>[/url]</span> -> <a href='http://google.com' target='_blank'>link</a>",
                "<span class='code'>[*]</span>tekst -> &bull;tekst"
        ];
        $("#style_buttons p").attr("help", manual.join("<br>"));
        // ====== Long click fix ======
        $("#style_buttons span").mousedown(function() {
            keepStyleButtons = true;
        });
        // ====== Click style button ======
        $("#style_buttons span").unbind("click").click(function() {
            keepStyleButtons = true;
            applyStyle($textarea, $(this));
        });
    };
    
    function generateStyleButtons(format) {
	// --------------------------------
	// format = <string> buttons codes, divided by coma (eg. "b,i"):
	//     "*": all buttons
	//     "b": bold, "i": italic, "u": underline, "-": bullet, "l": external link, "s": in-site link, "m": mailto
	// --------------------------------
	// RETURNS: <string> Style buttons bar html according to format option(s)
	// --------------------------------
        // Check for formatting limitations
        if($("input#site_format").length) { valid = $("input#site_format").val().split(","); }
        else { valid = false; };
        // Check for libraries
        disabled = [];
        if($("#libraries_data .files_lib").length == 0) { disabled[disabled.length] = "a"; };
        if($("#libraries_data .images_lib").length == 0) { disabled[disabled.length] = "f"; };
        if($("#libraries_data .temp_lib").length == 0) { disabled[disabled.length] = "p"; };

        if(format != "") {
            html = "";
            format = format.split(",");
            buttons = {
                "b": "<span class='fi-bold help style_bold' help='Pogrubienie'></span>",
                "i": "<span class='fi-italic help style_italic' help='Kursywa'></span>",
                "u": "<span class='fi-underline help style_underline' help='Podkreślenie'></span>",
                "^": "<span class='help index style_superscript' help='Indeks górny'>x<sup>2</sup></span>",
                "v": "<span class='help index style_subscript' help='Indeks dolny'>x<sub>2</sub></span>",
                "-": "<span class='fi-list-bullet help style_list' help='Lista'></span>",
                "c": "<span class='fi-align-center help style_center' help='Wyśrodkowanie'></span>",
                ".": "<span class='help style_bullet' help='Kropka'>&bull;</span>",
                "_": "<span class='fi-minus help style_line' help='Pozioma linia'></span>",
				"m": "<span class='fi-mail help style_mail' help='Email'></span>",
                "l": "<span class='fi-link help style_link' help='Link'></span>",
                "a": "<span class='fi-page-filled help style_attachment' help='Plik z bazy plików'></span>",
                "f": "<span class='fi-photo help style_figure' help='Obrazek z bazy obrazków'></span>",
                "p": "<span class='fi-layout help style_page' help='Łącze do strony tymczasowej'></span>",
            };

            for(key in buttons) {
                // Check for fisabled or not valid first
                if(disabled.indexOf(key) < 0 && (valid == false || valid.indexOf(key) > -1)) {
                    if(format.indexOf("*") >= 0 || format.indexOf(key) >= 0) { html = html + buttons[key]; };
                };
            };

            html = html + "<span class='fi-prohibited help style_delete' help='Usuń formatowanie'></span>" // Delete BBCode
            html = html + "<span class='help style_preview' help='Podgląd formatowania'>Podgląd</span>" // Preview
            return "<div id='style_buttons'>" + html + "<p class='fi-info manual help' help='s'></p></div>"; // Show manual
        }
        else {
            return "";
        };
    };
    
    function applyStyle($textarea, $button) {
    // --------------------------------
    // $textarea = <object>
    // $button = <object>
    // --------------------------------
    // Apply button style to selected text in textarea
    // --------------------------------
        $("#confirm_popup button").unbind();
        style = $button.attr("class").split("_").pop();
        simple = [ "bold", "italic", "underline" ];
        word = [ "list", "center" ];
        popup = [ "link", "site", "mail"];
        index = [ "superscript", "subscript" ];
        // ====== Selected text ======
		text = $textarea.val();
		beg = $textarea[0].selectionStart;
		end = $textarea[0].selectionEnd;
        selected = text.substr(beg, (end - beg));
		strlen = text.length;
		scroll = $textarea.scrollTop();
        // ====== PREVIEW ======
        if(style == "preview") {
            text = BBCode( $textarea.val() );
            $("body").append( "<div id='page_fader'></div><div id='preview_popup'>Preview</div>" );
            xy = $textarea.offset();
            wid = $textarea.width() + (2 * parseInt($textarea.css("padding-left"))) + (2 * parseInt($textarea.css("border-left")));
            hei = $textarea.height() + (2 * parseInt($textarea.css("padding-top"))) + (2 * parseInt($textarea.css("border-top")));
            $("#preview_popup").css({ "top": xy.top, "left": xy.left, "width": wid, "min-height": hei });
            $("#preview_popup").html( "<p>" + text + "</p>" );
			keepStyleButtons = true;
            $("#page_fader").fadeIn(200);
            $("#style_buttons").css({ "opacity": "0.5" });
            $("#page_fader, #preview_popup").click(function() {
                $("#page_fader, #preview_popup").fadeOut(100, function() {
                    $("#style_buttons").css({ "opacity": "1" });
                    $(this).remove();
                    $textarea.focus();
                    initializeHideStyle();
                    keepStyleButtons = false;
                });
            });
            $("#preview_popup a").each(function() {
                href = $(this).attr("href");
                target = $(this).attr("target");
                if(href.substr(0,7) == "mailto:") {
                    href = "Email: <a>" + href.substr(7) + "</a>";
                }
                else {
                    href = "Link: <a>" + href + "</a>";
                };
                $(this).attr("help", href).addClass("help manual");
            });
            $("#preview_popup a").click(function() { return false; });
            initializeHelp();
        }
		// ====== BULLET ======
        else if(style == "bullet") {
			$textarea.val(text.substr(0, beg) + "[*]" + text.substr(beg));
            $textarea.focus();
			cursorBack($textarea, scroll, strlen, end);
		}
		// ====== HORIZONTAL LINE ======
        else if(style == "line") {
			$textarea.val(text.substr(0, beg) + "[hr]" + text.substr(beg));
            $textarea.focus();
			cursorBack($textarea, scroll, strlen, end);
		}
        // ====== ATTACHMENT / FIGURE / PAGE ======
        else if(style == "figure" || style == "attachment" || style == "page") {
            if(style == "figure") {
                showLibPopup("images_lib", false); // Image link (single)
            }
            else if(style == "attachment") {
                if(selected == "") { showLibPopup("files_lib", true); } // simple file -> link (multiple)
                else { showLibPopup("files_lib", false); }; // text content as link (single)
            }
			else if(style == "page") {
                if(selected == "") { showLibPopup("temp_lib", true); } // simple page link -> link (multiple)
                else { showLibPopup("temp_lib", false); }; // text content as page link (single)
			};
            keepStyleButtons = true;
			
            $(".popup_box button.confirm").click(function() {
                var items = [];
                $("#libraries").find("li .selected").each(function() {
                    val = $(this).parent().attr("value");
                    if(jQuery.type(val) == "string") {
                        items[items.length] = val;
                    };
                });
                    
                if(items.length > 0) {
                    // ====== FIGURE ======
                    if(style == "figure") {
                        input = items.shift();
                        // Link
                        href = $("#libraries").find("input#href").val();
                        if(href != undefined && href != "") {
                            if($("#libraries").find("input#new_window").prop("checked") == true) {
                                link_open = "[url|e=" + href + "]";
                                link_close = "[/url]";
                            }
                            else {
                                link_open = "[url|i=" + href + "]";
                                link_close = "[/url]";
                            };
                        }
                        else {
                            link_open = "";
                            link_close = "";
                        };
                        // Text side (if any)
                        if(selected != "") {
                            dir = $("#libraries").find("input.side:checked").val();
                            if(dir == "right") {
                                tag = "banner|r"
                            }
                            else {
                                tag = "banner|l";
                            };
                        }
                        else {
							size = $("#libraries").find("input.size:checked").val();
                            tag = "button|" + size;
                        }
                        // Apply attachment
                        $textarea.val(text.substr(0, beg) + "[" + tag + "]" + link_open + "[img=" + input + "]" + link_close + selected + "[/" + tag.split("|").shift() + "]" + text.substr(end));
                        hideLibPopup();
                    }

                    // ====== ATTACHMENT / PAGE ======
                    else {
						if(style == "page") { tag = "url|i"; }
						else { tag = "url|e"; };
                        // Multiple files
                        if(selected == "") {
                            for(i in items) {
                                href = items[i];
                                href = "[" + tag + "=" + href + "]" + href.path("basename") + "[/" + tag.split("|").shift() + "]";
                                items[i] = href;
                            };
                            $textarea.val(text.substr(0, beg) + items.join("\n") + text.substr(end));
                        }
                        // Single link
                        else {
                            input = items.shift();
                            $textarea.val(text.substr(0, beg) + "[" + tag + "=" + input + "]" + selected + "[/" + tag.split("|").shift() + "]" + text.substr(end));
                        };
                        hideLibPopup();
                    };

                    // ====== Back to textarea ======
                    if($("#style_buttons").length == 0) {
                        initializeHideStyle();
                        $textarea.focus();
                    }
                    else {
                        $textarea.focus();
                        initializeHideStyle();
                        keepStyleButtons = false;
                    };
					cursorBack($textarea, scroll, strlen, end);
                }
                else {
                    infoPopup("Nie zaznaczono żadnego pliku");
                };

            });

            $(".popup_box button.cancel").click(function() {
                hideLibPopup();
                $textarea.focus();
                initializeHideStyle();
                keepStyleButtons = true;
            });

            // Go back to textarea
            if($("#style_buttons").length == 0) {
                initializeHideStyle();
                $textarea.focus();
            }
            else {
                $textarea.focus();
                initializeHideStyle();
                keepStyleButtons = false;
            };
        }
		// ====== Lack of selection ======
        else if(selected == "") {
            infoPopup("Najpierw zaznacz tekst, który chcesz sformatować!");
            $textarea.focus();
        }
        // ====== WORD ======
        else if(word.indexOf(style) >= 0) {
            $textarea.val(text.substr(0, beg) + "[" + style + "]" + selected + "[/" + style + "]" + text.substr(end));
            $textarea.focus();
			cursorBack($textarea, scroll, strlen, end);
        }
        // ====== INDEX ======
        else if(index.indexOf(style) >= 0) {
            tag = style.substr(0, 3);
            $textarea.val(text.substr(0, beg) + "[" + tag + "]" + selected + "[/" + tag + "]" + text.substr(end));
            $textarea.focus();
			cursorBack($textarea, scroll, strlen, end);
        }
        // ====== SIMPLE ======
        else if(simple.indexOf(style) >= 0) {
            tag = style.substr(0, 1);
            $textarea.val(text.substr(0, beg) + "[" + tag + "]" + selected + "[/" + tag + "]" + text.substr(end));
            $textarea.focus();
			cursorBack($textarea, scroll, strlen, end);
        }
        // ====== POPUP ======
        else if (popup.indexOf(style) >= 0) {
            if(style == "link") { tag = "url"; title = "Link:"; icon = "fi-link"; }
            else if(style == "site") { tag = "url"; title = "Odsyłacz wewnętrzny:"; icon = "fi-arrow-right"; }
            else if(style == "mail") { tag = "email"; title = "E-mail:"; icon = "fi-mail"; }
            else { title = "?"; };
            showPopup(icon, title, "Zapisz,Anuluj", "Wklej adres");
            $("textarea").unbind("blur");
            // Button - confirm
            $("#popup_box .confirm").unbind("click").click(function() {
                input = $("#popup_box input").val();
                if(input == "") {
                    infoPopup("Pole adresu nie może być puste!");
                    $("#popup_box input").focus();
                }
                //else if((style == "mail" && (input = validateEmail(input)) == false) || (style == "link" && (input = validateUrl(input)) == false)){
                else if(style == "mail" && (input = validateEmail(input)) == false) {
                    infoPopup("Wpisz poprawny adres!");
                    $("#popup_box input").focus();
                }
                else if(style == "site") {
                    if(input.substr(0, 7) == "http://") { input = input.substr(7); }
                    else if(input.substr(0, 8) == "https://") { input = input.substr(8); };
                    input = input.split("/").pop();
                    if(input == "") { input = "index.php"; };
                    $textarea.val(text.substr(0, beg) + "[" + tag + "=" + input + "]" + selected + "[/" + tag + "]" + text.substr(end));
                    hidePopup();
                }
                else {
                    $textarea.val(text.substr(0, beg) + "[" + tag + "=" + input + "]" + selected + "[/" + tag + "]" + text.substr(end));
                    hidePopup();
                };
                if($("#style_buttons").length == 0) {
                    initializeHideStyle();
                    $textarea.focus();
                }
                else {
                    $textarea.focus();
                    initializeHideStyle();
                    keepStyleButtons = false;
                };
				cursorBack($textarea, scroll, strlen, end);
            });      
            // Button cancel
            $("#popup_box .cancel").unbind().click(function() {
                hidePopup();
				$textarea.focus();
                initializeHideStyle();
                keepStyleButtons = false;
            });
        }
        // ====== DELETE BBCode ======
        else if(style = "delete") {
            $textarea.val(text.substr(0, beg) + noBBCode(selected) + text.substr(end));
            $textarea.focus();
			cursorBack($textarea, scroll, strlen, end);
        };
    };
	
	function cursorBack($textarea, scroll, strlen, end) {
    // --------------------------------------------------
	// Set correct cursor & textarea scroll position
    // --------------------------------------------------
		$textarea.selectRange(end - (strlen - $textarea.val().length));
		$textarea.scrollTop(scroll);
        // Resize textarea to contet
        textareaResize($textarea, textarea_min);
	};
    
    function hideLibPopup() {
        $("#libraries, #page_fader").fadeOut(250, function() { $(this).remove(); });
        $("body").css({ "overflow": "auto" });
    };
    
    function showLibPopup(lib, multi) {
        // Hide previous popups (if any)
        $("#page_fader, #popup_container, .popup_box, #libraries").remove();
        // Get library data
        $lib = $("#libraries_data > div." + lib);
        if($lib.length) { lib_html = $lib.get(0).outerHTML; } else { lib = ""; };
        // Image link content
        if(lib.indexOf("image") > -1) {
            icon = "fi-photo";
            inputs = "<p class='label'>Link</p>\n" +
                "<input id='href' type='text' class='text' placeholder='Wklej adres'>\n" +
                "<label><input id='new_window' type='checkbox' class='checkbox' checked> Otwórz w nowym oknie</label>\n";
            // Add text side options for photo with text banner
            if(selected != undefined && selected != "") {
                title = "Obrazek z opisem";
                inputs = inputs +
                    "<p class='label'>Umieść opis</p>\n" +
                    "<label><input type='radio' class='side radio' name='side' value='left'> Po lewej</label>" +
                    "<label><input type='radio' class='side radio' name='side' value='right' checked> Po prawej</label>";
            }
			else {
				title = "Obrazek";
                inputs = inputs +
                    "<p class='label'>Wielkość obrazka</p>\n" +
                    "<label><input type='radio' class='size radio' name='size' value='1'> Mała</label>" +
                    "<label><input type='radio' class='size radio' name='size' value='2' checked> Średnia</label>" +
					"<label><input type='radio' class='size radio' name='size' value='3'> Duża</label>";
			};
			if(multi) { label = "Wybierz obrazki"; } else { label = "Wybierz obrazek"; };
        }
        // Page content
        else if(lib.indexOf("temp") > -1) {
            title = "Podstrona";
            icon = "fi-layout";
            inputs = "";
			if(multi) { label = "Wybierz strony"; } else { label = "Wybierz stronę"; };
        }
        // File content
        else {
            title = "Plik";
            icon = "fi-page-filled";
            inputs = "";
			if(multi) { label = "Wybierz pliki"; } else { label = "Wybierz plik"; };
        };
		
        // HTML
        html = "<div id='page_fader'></div>\n";
        html = html + "<div id='libraries'><div class='scrollable'><div class='popup_box'>\n";
        html = html + "<h6><span class='" + icon + "'></span></h6>\n";
        html = html + "<h3>" + title + "</h3>\n";
        html = html + "<p class='label'>" + label + "</p>\n";
        html = html + $lib.get(0).outerHTML;
        html = html + inputs;
        html = html + "<div class='buttons two'><button class='confirm'>Zapisz</button><button class='cancel'>Anuluj</button></div>\n";
        html = html + "</div></div></div>";
        // Show
        $("body").append(html);
        $("#libraries, #page_fader").fadeIn(250);
        $("#libraries").find(".folder").first().find("li").show();
        $("body").css({ "overflow": "hidden" });
        initializeHelp();
        // ====== User Actions ======
        $("#libraries .folder .name").click(function() {
			num = $(this).parent().find("li").length;
			fold_time = num * 50;
			if(fold_time < 250) { fold_time = 250; };
			if(fold_time > 750) { fold_time = 750; };
			if(num > 0) {
				if($(this).parent().find("li").css("display") == "none") {
					
					$(this).parent().find("li").show(fold_time);
				}
				else {
					$(this).parent().find("li").hide(fold_time);
				}
			};
        });
        if(multi) {
            $("#libraries li").click(function() {
                $item = $(this).children().first();
                if($item.attr("class") == undefined || $item.attr("class").indexOf("selected") < 0) {
                    $item.addClass("selected");
                }
                else {
                    $item.removeClass("selected");
                };
            });
        }
        else {
            $("#libraries li").click(function() {
                $("#libraries li .selected").removeClass("selected");
                $item = $(this).children().first();
                $item.addClass("selected");
            });
        };
    };
    
    function BBCode(text) {
	// --------------------------------
	// text = <string>
	// --------------------------------
	// RETURN: <string> Input text with BBCode applied
	// --------------------------------
        text = text.replace( /\n/ig, "<br>"); // line break(s)
        text = text.replace( /\\\[/g, "&#91;"); // "["
        text = text.replace( /\\\]/g, "&#93;"); // "]"
        
        // Banner
        text = text.replace( /\[banner(.*?)\[img=(.*?)\](.*?)\[\/banner\]/ig, "[banner$1<div class='figure' style='background-image:url(\"" + site_root + "/$2\")'></div><p>$3</p>[/banner]" ); // banner image
		text = text.replace( /\[\/banner\]<br>/ig, "[/banner]"); // enter fix 1
		text = text.replace( /<br>\[banner(.*?)\]/ig, "[banner$1]"); // enter fix 2
        text = text.replace( /\[banner\|r\](.*?)\[\/banner\]/ig, "</p><div class='banner text_right'>$1</div><p>"); // banner|r
        text = text.replace( /\[banner\|l\](.*?)\[\/banner\]/ig, "</p><div class='banner text_left'>$1</div><p>"); // banner|l
        text = text.replace( /\[banner\](.*?)\[\/banner\]/ig, "</p><div class='banner'>$1</div><p>"); // banner
        // Button
        text = text.replace( /\[\/button\]\[button(.*?)\]/ig, ""); // join touching buttons (unify size -> first)
        text = text.replace( /\[\/button\]<br>/ig, "[/button]"); // enter fix 1
		text = text.replace( /<br>\[button(.*?)\]/ig, "[button$1]"); // enter fix 2
        text = text.replace( /\[button\|(.*?)\](.*?)\[\/button\]/ig, "</p><div class='button size_$1'>$2</div><p>"); // sized button
        text = text.replace( /\[button\](.*?)\[\/button\]/ig, "</p><div class='button'>$1</div><p>"); // button
        
        text = text.replace( /\[img=(.*?)\]/ig, "<img src='" + site_root + "/$1'>"); // image
        
		text = text.replace( /\[hr\]<br>/ig, "[hr]"); // enter fix
		
        // ====== Main style ======
        // Forced link types
        text = text.replace( /\[url\|e=(.*?)\](.*?)\[\/url\]/ig, "<a href='$1' target='_blank'>$2</a>"); // external links
        text = text.replace( /\[url\|i=(.*?)\](.*?)\[\/url\]/ig, "<a href='$1'>$2</a>"); // internal links
        // Complex
        text = text.replace( /\[url=(.*?)\](.*?)\[\/url\]/ig, "<a href='$1' target='_blank'>$2</a>"); // links
        text = text.replace( /\[email=(.*?)\](.*?)\[\/email\]/ig, "<a href=\"mailto:$1\">$2</a>"); // mailto
        text = text.replace( /\[tel=(.*?)\](.*?)\[\/tel\]/ig, "<a href=\"tel:$1\">$2</a>"); // tel
        text = text.replace( /\[color=(.*?)\](.*?)\[\/color\]/ig, "<span class='color_$1'>$2</span>"); // color
        text = text.replace( /\[size=(.*?)\](.*?)\[\/size\]/ig, "<span class='size_$1'>$2</span>"); // size
        text = text.replace( /\[\*\]/ig, "&bull;"); // bullet character
        text = text.replace( /\[\hr\]/ig, "</p><div class='hr'><hr></div><p>"); // horizintal line
        // Simple
        text = text.replace( /\[b\](.*?)\[\/b\]/ig, "<b>$1</b>"); // bold
        text = text.replace( /\[i\](.*?)\[\/i\]/ig, "<i>$1</i>"); // italic
        text = text.replace( /\[u\](.*?)\[\/u\]/ig, "<u>$1</u>"); // italic
        text = text.replace( /\[sup\](.*?)\[\/sup\]/ig, "<sup>$1</sup>"); // superscript
        text = text.replace( /\[sub\](.*?)\[\/sub\]/ig, "<sub>$1</sub>"); // subscript
		text = text.replace( /\[center\](.*?)\[\/center\]/ig, "</p><p style='text-align:center'>$1</p><p>"); // center open

        // List
        if(lists = text.match(/\[list\](.*?)\[\/list\]/ig)) {
            for(i in lists) {
                list = lists[i];
				list = list.replace(/\[list\](.*?)\[\/list\]/ig, "$1");
                list = "</p><ul class='preview'><li>" + list.replace( /<br>/ig, "</li><li>" ) + "</li></ul><p>";
				list = list.replace(/<li>- /ig, "<li style='list-style:none'>- "); // sublist point
                list = list.replace(/<li><\/li>/ig, "<li style='list-style:none'>&nbsp;</li>"); // empty line -> no list bullet
                text = text.replace(lists[i], list);
            };
        };

        return text;
    };
    
    function noBBCode(text) {
	// --------------------------------
	// text = <string>
	// --------------------------------
	// RETURN: <string> Input text stripped of any BBCode tags
	// --------------------------------
        text = text.replace( /\n/ig, "<br>"); // line break(s)
        
        text = text.replace(/\[url\|(.*?)\](.*?)\[\/url\]/ig, "$2"); // link with option
        text = text.replace(/\[url=(.*?)\](.*?)\[\/url\]/ig, "$2");	// link
        text = text.replace(/\[email=(.*?)\](.*?)\[\/email\]/ig, "$2"); // mailto link
        text = text.replace(/\[tel=(.*?)\](.*?)\[\/tel\]/ig, "$2"); // tel link
        text = text.replace(/\[center\](.*?)\[\/center\]/ig, "$1"); // center
        text = text.replace(/\[list\](.*?)\[\/list\]/ig, "$1"); // list
        text = text.replace(/\[(.?)\]/ig, ""); // simple tag open
        text = text.replace(/\[\/(.?)\]/ig, ""); // simpl tag close
        text = text.replace(/\[\*\]/ig, ""); // bullet
        text = text.replace(/\[\hr\]/ig, ""); // horizontal line
        
        // Attachements - photo/file
        text = text.replace(/\[banner(.*?)\](.*?)\[\/banner\]/ig, "$2");
        text = text.replace(/\[button(.*?)\](.*?)\[\/button\]/ig, "$2");
        text = text.replace(/\[img=(.*?)\]/ig, "");
        
        text = text.replace( /<br>/ig, "\n"); // line break(s)
        return text;
    };

    // ==========================================
    //         Buttons & Article headers
    // ==========================================
    
    function headerTag(tag) {
        if(tag.substr(0, 6) == "multi_") { tag = tag.substr(6); };
        return translate( tag.replace(/_/g, " ").toUpperCase() );
    };

    function setButtons() {
	// --------------------------------
	// enable/disable post move up & down buttons + add post numbering
	// --------------------------------
        // ====== XML ======
        // Get all signle & multi article type
		single_tags = [];
		multi_tags = [];
        $("article").each(function() {
            tag = $(this).attr("class");
            if(tag.substr(0, 6) == "multi_") {
                if(multi_tags.indexOf(tag) < 0) { multi_tags[multi_tags.length] = tag; };
            }
            else {
                if(single_tags.indexOf(tag) < 0) { single_tags[single_tags.length] = tag; };
            };
        });
        // Single article type / headers
        for(i in single_tags) {
            tag = single_tags[i];
            $("form article." + tag).each(function() {
                $(this).find("h3").html("<span class='fi-comment'></span>" + headerTag(tag));
            });
        };
        // Multi article type / buttons
        for(i in multi_tags) {
            tag = multi_tags[i];
            $article_group = $("form article." + tag);
            last = $article_group.length; // Last item number (counted from 1)
            // Init enable/disable
            $article_group.children("div.buttons").find("button").prop("disabled", false).removeClass("disabled"); // enable: all
            //$article_group.children("div.buttons").find(".new").prop("disabled", true).addClass("disabled"); // disable: new
            // On/off first/last buttons
            if($article_group.length == 1) {
                $article_group.children("div.buttons").find(".up, .down, .delete").prop("disabled", true).addClass("disabled"); // disable: up, down, delete
            }
            else {
                $article_group.first().children("div.buttons").find(".up").prop("disabled", true).addClass("disabled"); // first - disable: up
                $article_group.last().children("div.buttons").find(".down").prop("disabled", true).addClass("disabled"); // last - disable: down
            };
            // Add new button on the end for Navigation/pages, and on the begining for all other
            if(current_path.path("filename") == "navigation" && tag == "multi_page") {
                $active_new = $article_group.last(); // enable: last new
            }
            else {
                $active_new = $article_group.first(); // enable: first new
            };
            $active_new.children("div.buttons").find(".new").prop("disabled", false).removeClass("disabled"); // enable: new
            // headers
            n = 0;
            $article_group.each(function() { $(this).find("h3").html("<span class='fi-comments'></span>" + headerTag(tag) + " " + ++n); });
        };
        // ====== ORDER ======
        n = 0;
        //alert($("article._order").length);
        $("article._order h3").each(function() {
                tag = $(this).attr("class").replace(/_/g, " ").toUpperCase();
                $(this).html("<span class='fi-list'></span><em>" + ++n + "</em> " + tag);
        });
        if($("article._order").length == 1) {
            $("article._order").children("div.buttons").find(".up, .down, .delete").prop("disabled", true).addClass("disabled"); // disable: up, down, delete
        }
        else {
             $("article._order").first().children("div.buttons").find(".up").prop("disabled", true).addClass("disabled"); // first - disable: up
             $("article._order").first().next().children("div.buttons").find(".up").prop("disabled", false).removeClass("disabled"); // second - enable: up
             $("article._order").last().children("div.buttons").find(".down").prop("disabled", true).addClass("disabled"); // last - disable: down
             $("article._order").last().prev().children("div.buttons").find(".down").prop("disabled", false).removeClass("disabled"); // pre-last - enable: down
        };
        // ====== CSV ======
        $("article._csv h3").html("<span class='fi-list-thumbnails'></span>CSV");
    };
	setButtons();

    // ==========================================
    //              File(s) upload
    // ==========================================

    function UploadCancelFix() {
        // Fix label crash after Cancel
        $("input.upload_file").unbind("click").click(function() {
            $uploadButton = $(this);
            $(document).focusin(function() {
                $(document).unbind("focusin");
                $("#help_popup").remove();
                // If no files selected
                setTimeout(function() { // wait for data
                    if($uploadButton.val() == "") {
                        //alert("nic");
                        $box = $uploadButton.closest(".upload");
                        $div = $box.parent();
                        box = $box.get(0).outerHTML
                        $box.remove();
                        $div.append(box); // rebuild upload button
                        initializeUpload();
                        initializeHelp();
                    }
                    else {
                        //alert("plik");
                    };
                }, 200);
            });
        });
    };
	
	function transparentBackground() {
		$("article .media .thumb").each(function() {
			img = $(this).css("background-image")
			if(jQuery.type( img ) == "string" && img != "" && img != "none") {
				img = img.replace(/url\(\"(.*?)\"\)/, "$1").path("basename");
				if(img.path("extension") == "png" || img.path("extension") == "svg") {
					$(this).css({ "background-color": "#eeeeee" });
				}
				else {
					$(this).css({ "background-color": "#ffffff" });
				};
			};
		});
	};
	transparentBackground();

    function initializeLinkUpdate() { // for embeded content
        $("article div.media label.update_link").click(function() {
            $thumb = $(this).parent();
            showPopup("fi-link", "Video", "Zapisz,Anuluj", "Wklej link YouTube");
            // Confirm
            $("#popup_box .confirm").unbind("click").click(function() {
                input = $("#popup_box input").val();
				
				if(iframe = input.match(/\<iframe(.*?)src=\"(.*?)\"/)) {
					//alert(iframe[2]);
					input = iframe[2];
				};
				input = input.replace(/autoplay=true/, "autoplay=false");
				
                if(input == "") {
                    infoPopup("Pole adresu nie może być puste!");
                    $("#popup_box input").focus();
                }
                else if(input.split(" ").length > 1 || input.split(".").length < 2){
                    infoPopup("Wpisz poprawny adres!");
                    $("#popup_box input").focus();
                }
                else {
                    if(input.substr(0 , 7) != "http://" && input.substr(0 , 8) != "https://") {
                        input = "http://" + input;
                    };
                    if(input.indexOf("youtube.com") > -1) {
                        input = input.split("&").shift();
                        input = input.replace("youtube.com/watch?v=", "youtube.com/embed/");
                    };
                    $thumb.closest("div.video").attr("value", input);
                    $thumb.find("iframe").fadeIn(200).attr("src", input);
                    $thumb.find("button").show(200);
                    hidePopup();
                };
            });
            // Cancel
            $("#popup_box .cancel").unbind("click").click(function() {
                hidePopup();
            });
        });
    };
    
    function initializeMediaPreview() {
	// --------------------------------
	// Initialize zoom or download button on media items
	// --------------------------------
        // Zoom
        $("article div.media .thumb .zoom").unbind("click").click(function() {
            bg = $(this).closest(".thumb").css("background-image");
            file = bg.replace(/url\(\"(.*?)\"\)/, "$1").path("basename");
            size = $(this).closest(".thumb").attr("size");
            if(jQuery.type(size) == "string" && size != "" && size != "x") {
                size = size.replace("x", " x ") + " px";
            }
            else {
                size = "";
            };
            html = "<div id='page_fader'>" + 
                "<figure id='zoom' style='background-image:" + bg + "'>" +
                "<p class='filename'>" + file + "<br><span class='details'>" + size + "</span></p>" +
                "<button' class='close'><span class='fi-x'></span></button>"
                "</figure>" +
                "</div>";
            //alert(html);
            $("body").append(html);
            $("#page_fader").fadeIn(250);
            // Close zoom on click (anywhere)
            $("#page_fader").click(function() {
                $(this).fadeOut(250, function() { $(this).remove(); });
            });
            // Close zoom on ESC key
            $(document).keyup(function(e) {
                if(e.keyCode == 27) {
                    $("#page_fader").fadeOut(250, function() { $(this).remove(); });
                };
            });
            // Blur focus outline
            $(this).blur();
            return false;
        });    
        // Download
        $("article div.media .thumb .download").unbind("click").click(function() {
            file = $(this).closest(".thumb").attr("path");
            window.open(site_root + "/" + file, '_blank');
            $(this).blur();
            return false;
        });
    };
	initializeMediaPreview();
    
    function fixFilename(filename) {
        filename = filename.replace(/ /g, "_");
        filename = filename.replace(/;/g, ",");
        filename = filename.replace(/\[/g, "(").replace(/\]/g, ")");
        filename = filename.replace(/\'|@/g, "");
        return filename;
    }
    
    function initializeUpload() {
	// --------------------------------
	// Initialize upload button action
	// --------------------------------
        initializeLinkUpdate();
        UploadCancelFix();
        $("input.upload_file").unbind("change").on("change", function() {
            folder = $(this).parent("div").parent("div").attr("value");
            media_type = $(this).parent("div").parent("div").attr("class");
            files = $(this).get(0).files;
            filenames = [];
            for(n = 0; n < files.length; n++) {
                filenames[filenames.length] = folder + "/" + files.item(n).name;
            };
            // ====== IMAGE or FILE ======
            if(media_type == "image" || media_type == "file") {
                //currentTime("yyyymmdd-homise");
                taken = $(this).parent(".upload").find(".taken_filenames").val().split(";");
                filename = fixFilename( filenames[0].path("basename") );
                // file overwrite protection -> rename file
                if(taken.indexOf(filename) > -1) {
                    filename =
                        filename.path("filename") + "_" +
                        currentTime("yyyymmdd-homise") + "." +
                        filenames[0].path("extension"); // due to js 2nd time used variable problem
                };
                path = folder + "/" + filename;
                filenames[0] = path;
                $(this).closest("div." + media_type).attr("value", path);
            }
            // ====== GALLERY or FILES ======
            else {
                folder = folder.split(";").shift().path("dirname");
                taken = $(this).parent(".upload").find(".taken_filenames").val().split(";");
                for(i in filenames) {
                    filename = fixFilename( filenames[i].path("basename") );
                    if(taken.indexOf(filename) > -1) {
                        filename =
                            filename.path("filename") + "_" +
                            currentTime("yyyymmdd-homise") + "." +
                            filenames[0].path("extension"); // due to js 2nd time used variable problem
                    };
                    path = folder + "/" + filename;
                    filenames[i] = path;
                };
                gallery_files = $(this).closest("div." + media_type).attr("value");
                if(gallery_files.split(";").shift().path("extension") != "" && gallery_files != "") {
                    $(this).closest("div." + media_type).attr("value", gallery_files + ";" + filenames.join(";"));
                }
                else {
                    $(this).closest("div." + media_type).attr("value", filenames.join(";"));
                };
            };
            // ====== Upload button / thumbnails ======
            // Update button from upload to images selected
            $upload = $(this).closest("div.upload");
            label = $upload.find("label").get(0).outerHTML;
            $media = $upload.parent("div");
            // Remove any "fake" upload images divs
            $media.find(".fake").remove();
            // Create upload image icon(s)
            for(i in filenames) {
                if(i == 0) { // Single image in existing upload div
                    $upload.find("p").text( filenames[i].path("basename") ); // text
                }
                else { // Further images in new fake upload divs
                    label = label.replace(/<p>(.*?)<\/p>/, "<p>" + filenames[i].path("basename") + "</p>");
                    $upload.parent("div").append("<div class='upload fake'>" + label + "</div>");
                };
            };
            if(media_type == "file") { icon = "fi-page"; } else { icon = "fi-photo"; };
            $media.find("label").removeClass("fi-upload").addClass(icon); // change icon to picture
            $media.find(".upload").css({ "background-color": "#ffffff" }); // non transparent bg color
            // ====== Add file(s) path(es) to upload list ======
            if(media_type != "gallery" && media_type != "files") { upload_filenames = "force:" + filenames.join(";"); }
            else { upload_filenames = "auto:" + filenames.join(";"); };
            //alert(upload_filenames);
            $(this).parent(".upload").find(".upload_filenames").val(upload_filenames);
            transparentBackground();

			//alert(filenames);
			for(i in filenames) {
				if(filenames[i].path("extension").indexOf("tif") > -1) {
					showPopup("fi-alert", "UWAGA<br>Plik typu TIFF może nie załadować się na serwer!", "OK", false);
                    $("#popup_box .confirm").click(function() { hidePopup(); });
				};
			};
        });
    };
    initializeUpload()
	
	function uploadHideShow() { // call in initializeDeleteMedia()
	// --------------------------------
	// Hide/Show upload image button
	// --------------------------------
        // Images & Files
		$("div.media div.image, div.media div.file").each(function() {
			src = $(this).attr("value");
            upload = $(this).find(".upload_filenames").val()
			if(src.path("extension") != "" && upload == "") { // Image exists -> hide Upload button
				$(this).find("div.upload").hide();
			}
			else { // No image -> show Upload button
				$(this).find("div.upload").show();
			};
		});
        // Links
        $("div.media div.video .embed").each(function() {
            $iframe = $(this).find("iframe");
            $delete_button = $(this).find("button.delete");
            src = $iframe.attr("src");
            if(src == "") {
                $iframe.hide();
                $delete_button.hide();
            }
            else {
                $iframe.show();
                $delete_button.show();
            };
        });
	};
	uploadHideShow();
	
    // ==========================================
    //                  Images
    // ==========================================
    
    function addToDeleteFiles(path) {
        files = $("input#delete_files").val();
        if(files == "") { files = []; } else { files = files.split(";"); };
        files[files.length] = path;
        $("input#delete_files").val( files.join(";") );
        //alert("addToDeleteFiles: " + path);
    };
    
    function addToDeleteFolders(path) {
        files = $("input#delete_folders").val();
        if(files == "") { files = []; } else { files = files.split(";"); };
        files[files.length] = path;
        $("input#delete_folders").val( files.join(";") );
    };
	//alert(site_root);
	function updateGallerySort($gallery) {
		images = [];
		$gallery.find("div.thumb").each(function() {
			images[images.length] = $(this).attr("path");
		});
        //alert(images);
		$gallery.attr("value", images.join(";"));
	};
	
	function initializeDeleteMedia() {
	// --------------------------------
	// Initialize Delete Image button actions & gallery images sorting change
	// --------------------------------
		$("div.media button.move_left").unbind("click").click(function() {
			$(this).blur();
			$("#help_popup").stop();
			$gallery = $(this).closest(".gallery, .files");
			$thumb = $(this).parent(".thumb");
			$prev = $thumb.prev();
			if($prev.length && $prev.attr("class") == "thumb") {
				$clone = $thumb.clone();
				$prev.before($clone);
				$prev.prev().hide();
				$thumb.hide(250, function() { $(this).remove(); });
				$prev.prev().show(250, function() {
					initializeDeleteMedia();
					updateGallerySort($gallery);
				});
			};
            initializeMediaPreview();
			return false;
		});
		$("div.media button.move_right").unbind("click").click(function() {
			$(this).blur();
			$("#help_popup").stop();
			$gallery = $(this).closest(".gallery, .files");
			$thumb = $(this).parent(".thumb");
			$next = $thumb.next();
			if($next.length && $next.attr("class") == "thumb") {
				$clone = $thumb.clone();
				$next.after($clone);
				$next.next().hide();
				$thumb.hide(250, function() { $(this).remove(); });
				$next.next().show(250, function() {
					initializeDeleteMedia();
					updateGallerySort($gallery);
				});
			};
            initializeMediaPreview();
			return false;
		});
	
		$("div.media button.delete").unbind("click").click(function() {
			$("#help_popup").stop();
			$img = $(this).parent("div");
			$div = $(this).parent("div").parent("div");
			media_type = $div.attr("class");
            if(media_type == "video") {
                $embed = $img;
                $embed.find("iframe").fadeOut(200).attr("src", "");
                $embed.closest("div.video").attr("value", "");
                $div.find("button.delete").hide();
            }
            else {
                if(media_type == "image" || media_type == "file") {
                    path = $div.attr("value");
                    folder = path.path("dirname");
                    file = path.path("basename");
                    $div.attr("value", folder); // Left the folder only
                }
                else if(media_type == "gallery" || media_type == "files") {
                    files = $div.attr("value").split(";"); // all files list
                    folder = files[0].path("dirname");
                    path = $img.attr("path");
                    path = decodeURI(path);
                    //alert("to delete: " + path);
                    files_mod = [];
                    for(i in files) {
                        file = files[i];
                        if(file != path) { files_mod[files_mod.length] = file; };
                    };
                    if(files_mod.length > 0) {
                        $div.attr("value", files_mod.join(";"));
                    }
                    else {
                        $div.attr("value", folder);
                    };
                };
                // ====== Add file path to Delete Files list ======
                addToDeleteFiles(path)
                // ====== Hide image & show Upload button ======
                $img.hide(500);
                uploadHideShow();
                // End
            };
			$(this).blur();
			return false;
		});
	};
	initializeDeleteMedia();
    
    function initializeSet() {
    // --------------------------------
    // Initialize set change trigger
    // --------------------------------
        $("div.set input").unbind("change").on("change", function() {
            // Change bacground color
            $(this).closest("section").find("label").removeClass("checked");
            $(this).closest("label").addClass("checked");
            set = $(this).val();
            $(this).closest("section").find("div.media").children("div").hide(200);
            $(this).closest("section").find("div.media").children("div." + set).show(200);
        });
    };
    initializeSet();
    
    function mediaHideShow() {
    // --------------------------------
    // Show only current set media box
    // --------------------------------
        $("div.set").each(function() {
            set = $(this).find("input:checked").val();
            $(this).find("label").removeClass("checked");
            $(this).find("input:checked").parent("label").addClass("checked");
            $(this).closest("section").find("div.media").children("div").hide();
            $(this).closest("section").find("div.media").children("div." + set).show();
        });
    };
    mediaHideShow();
    
    function singleSetHide() {
    // --------------------------------
    // Hide single media type set labels
    // --------------------------------
        $("section div.set").each(function() {
            $section = $(this).closest("section");
            if( $section.find("input.type").val() == "media") {
                if($section.find("div.media").children("div").length == 1) {
                   $(this).hide();
                };
            };
        });
    };
    singleSetHide();
    
    /*
    function updateMediaValue() {
        $("section > div.media").each(function() {
            $(this).children(".gallery, .files").each(function() {
                pathes = [];
                $(this).children(".thumb").each(function() {
                    if(path = $(this).attr("path") && jQuery.type(path) == "string" && path != "") {
                        pathes[pathes.length] = path;
                    };
                });
                $(this).attr("value", pathes.join(";"));
            });
        });
    };
    //updateMediaValue();
    */

    // ==========================================
    //           Add new article (post)
    // ==========================================
    
    function initializeNewPost() {
        $("article button.new").unbind("click").click(function() {
            $(this).unbind();
            tag = $(this).attr("class").split(" ").pop();
            $("#style_buttons").remove(); // Remove temporary elements if any
            // Add new post (not displayed)
            //alert(tag);
            html = $(this).closest("article").html();
            html = html.replace(/name=\"(.*?)\"/ig, "name=\"$1-new\""); // Change name(s) -> multiple files need fix!
            html = html.replace(/id=\"(.*?)\"/ig, "id=\"$1-new\""); // Change id(s)
            html = html.replace(/for=\"(.*?)\"/ig, "for=\"$1-new\""); // Change for
            // Add new article
            if(current_path.path("filename") == "navigation") { // if Nav -> add new article on the end
                $(this).closest("article").after("<article class='" + tag + "' style='display:none'>" + html + "</article>");
                $new_post = $(this).closest("article").next();
            }
            else {
                $(this).closest("article").before("<article class='" + tag + "' style='display:none'>" + html + "</article>");
                $new_post = $(this).closest("article").prev();
            };
            // Clear edit fields
            $new_post.children("section").each(function() {
                $(this).children("div.text").children("input, textarea").val(""); // Clear all text fields
                $(this).children("input.string").val(""); // Clear all string fields
                $(this).find("ul.checkbox li input").prop("checked", false); // Uncheck any checkbox options
                $(this).find("ul.radio").each(function() { // Select first radio option
                    $(this).find("li input").prop("checked", false);
                    $(this).find("li input").first().prop("checked", true);
                });
                // table
                $(this).find("table").find("td").each(function() {
                    id = $(this).attr("class");
                    if(id.split(" ").length == 1 && id.split("_").shift() == "num") {
                        $(this).text("");
                    };
                });
                // media
                $(this).find("div.set").each(function() {
                    // If "none" exists -> default
                    if($(this).closest("section").find("div.media").children("div").length == 1) {} // one set type
                    else if( $(this).find("[value=none]").length > 0 ) {
                        $(this).find("input").prop("checked", false);
                        $(this).find("[value=none]").prop("checked", true);
                    }
                    // If no "none", first -> default
                    else {
                        $(this).find("input").prop("checked", false);
                        $(this).find("input:first").prop("checked", true);
                    };
                });
                $(this).find(".upload label").removeClass("fi-photo").addClass("fi-upload");
                $(this).find(".upload").css({ "background-color": "transparent" });
                $(this).find("div.file label p").text("Dodaj plik");
                $(this).find("div.image label p").text("Dodaj plik");
                $(this).find("div.gallery label p").text("Dodaj pliki");
                $(this).find("div.files label p").text("Dodaj pliki");
                
                //$(this).find("input.upload_file").val(""); // ("name", name + "[]");
                // File
                $(this).children("div.media").children("div.file").each(function() {
                    src = $(this).attr("value");
                    $(this).attr("value", src.path("dirname"));
                    $(this).find(".thumb").hide().remove();
                    $(this).find(".upload").show();
                });
                // Image
                $(this).children("div.media").children("div.image").each(function() {
                    src = $(this).attr("value");
                    $(this).attr("value", src.path("dirname"));
                    $(this).find(".thumb").hide().remove();
                    $(this).find(".upload").show();
                });
                // Files
                $(this).children("div.media").children("div.files").each(function() {
                    $(this).find(".fake").remove();
                    src = $(this).attr("value").split(";").shift();
                    $(this).attr("value", src.path("dirname"));

                    $(this).find(".thumb").hide().remove();
                    // multiple file name fix
                    name = $(this).find("input.upload_file").attr("name");
                    name = name.replace("[]", "");
                    
                    $(this).find("input.upload_file").attr("name", name + "[]");
                });
                // Gallery
                $(this).children("div.media").children("div.gallery").each(function() {
                    $(this).find(".fake").remove();
                    src = $(this).attr("value").split(";").shift();
                    $(this).attr("value", src.path("dirname"));

                    $(this).find(".thumb").hide().remove();
                    // multiple file name fix
                    name = $(this).find("input.upload_file").attr("name");
                    name = name.replace("[]", "");
                    $(this).find("input.upload_file").attr("name", name + "[]");
                });
                // Video
                $(this).find("div.media div.video").attr("value", "");
                $(this).find("div.media div.video iframe").hide().attr("src", "");
                $(this).find("div.media div.video button.delete").hide();
                // File inputs
                /*
                $(this).find("input.upload_file").each(function() {
                    $(this).val("");
                });
                */
                
            });
            $("#help_popup").remove();
            // Display new post
            $new_post.show(500, function() {
                // reinitialize active elements on new DOM content
                initializeDectectChanges();
                initiateButtons();
                initializeUpload();
                mediaHideShow();
                //initializeSet();
                singleSetHide();
                setButtons();
                initializeShowStyle();
                initializeHelp();
                initializeNewPost();
                initializeSectionFold();
                transparentBackground();
                initializeDisabled();
                initializeTable();
                // Scroll to new post
                setTimeout(function() { $('html, body').animate({ scrollTop: $new_post.offset().top }, 500); }, 250);
                // End

                $(this).blur();
                initializeSet();
            });
            return false;
        });
    };
    initializeNewPost();
    
    // ==========================================
    //            Move or delete post
    // ==========================================
    
	function initiateButtons() {
	// --------------------------------
	// Initiate post buttons actions
	// --------------------------------
		// ====== Delete ======
		$("article .buttons button.delete").unbind("click").click(function() {
			$item = $(this).closest("article");
            $item.find("section > div.media").children("div").each(function() {
                attachments = [ "image", "gallery", "file", "files"];
                if(attachments.indexOf($(this).attr("class")) > -1) {
                    path = $(this).attr("value");
                    if(jQuery.type(path) == "string" && path != "") {
                        ext = path.path("extension");
                        if(jQuery.type(ext) == "string" && ext != "") {
                            addToDeleteFiles(path);
                        };
                    };
                };
            });
            
			$item.hide(500, function() {
				$(this).remove();
				setButtons();
			});
			return false; 
		});
        // ====== Move up ======
        $("article .buttons button.up").unbind("click").click(function() {
            $item = $(this).parent().parent();
            $item.hide(250, function() {
                $swapWith = $item.prev();
                $item.after($swapWith.detach());
                $item.show(250, function() {
                    setButtons();
                });
            });
            $(this).blur();
            return false;
        });
		
        // ====== Move Down ======
        $("article .buttons button.down").unbind("click").click(function() {
            $item = $(this).parent().parent();
            $item.hide(250, function() {
                $swapWith = $item.next();
                $item.before($swapWith.detach());
                $item.show(250, function() {
                    setButtons();
                });
            });
            $(this).blur();
            return false;
        });
		
	};
	initiateButtons();
    
    // ==========================================
    //                Main Title
    // ==========================================
    
    // Set main title by navigation label
    $("nav a").each(function() {
        //current_path
        href = $(this).attr("href");
        href = href.split("?path=").pop().split("&").shift();
        if(href == current_path) {
            title =  $(this).children("dd").text().replace("\u2022", "");
            $("h2").text( title );
        };
    });

    if($("h2").html().split("<span>").length > 1) {
        text = $("h2").text().split("/");
        $("h2").html( text[0] + "<span>/" + translate(text[1]) + "</span>" );
    };
    
    // ==========================================
    //           Section fold & expand
    // ==========================================

    function initializeSectionFold() {
        // Buttons
        $(".fold_expand span.fold").unbind("click").click(function() {
            //alert("fold");
            $buttons = $(this).parent("div.fold_expand");
            $(this).closest("article").find("section").hide(200, function() {
                $buttons.find("span.fold").hide();
                $buttons.find("span.expand").show();
                $(this).blur();
            });
        });
        $(".fold_expand span.expand").unbind("click").click(function() {
            //alert("expand");
            $buttons = $(this).parent("div.fold_expand");
            $(this).closest("article").find("section").show(200, function() {
                $buttons.find("span.fold").show();
                $buttons.find("span.expand").hide();
                $(this).blur();
            });
        });
    };
    initializeSectionFold();
    
    function autoSectionFold() {
        $(".fold_expand span.expand").hide();
        // Fold long content
        if($("form article").length > 5) {
            $("form article").each(function() {
                $article = $(this);
                if($(this).children("section").length > 5) {
                    $article.find(".fold_expand span.expand").show();
                    $article.find(".fold_expand span.fold").hide();
                    $article.find("section").hide(200);
                };
            });
        };
    };
    autoSectionFold();
    
    // ==========================================
    //           New Page on Template
    // ==========================================
    
    function createNewPage($button) {
        $("#page_fader, #popup_container").remove();
        templates = $button.attr("href").split(";");
        group = $button.closest(".group").find("dt").text();
        html = "<div id='page_fader'></div>" +
            "<div id='popup_container'><div id='popup_box'>" +
            "<p>Nazwa nowej strony</p>" +
            "<input type='text' taken='" + $button.attr("taken") + "'>" +
            "<p class='select'>Wybierz szablon:</p>" +
            "<select>";
        for(i in templates) {
            html = html + "<option path='" + templates[i] + "'>" + templates[i].path("basename") + "</option>";
        };
        html = html + "</select><br>";
        html = html +
            "<div class='buttons two'><button class='confirm'>OK</button><button class='cancel'>Anuluj</button></div>" +
            "</div></div>";
        $("body").append(html);
        $("#page_fader, #popup_container").fadeIn(200, function() {
            $(this).find("input").first().focus();
            $("#popup_container input").on('keypress', function (e) {
                if(e.which == 13) {
                    $(this).closest("#popup_container").find("button.confirm").trigger( "click" );
                };
            });
        });
        // Buttons actions
        $("#popup_box button.confirm").click(function() {
            template = $("#popup_box").find("option:selected").attr("path");
            filename = $("#popup_box").find("input").val().replace(/ /g, "_").toLowerCase() + ".xml";
            taken = $("#popup_box").find("input").attr("taken").split(";");
            if(filename == ".xml") {
                infoPopup("Wpisz nazwę strony");
                $("#popup_box").find("input").focus();
            }
            else if(taken.indexOf(filename) > -1) {
                infoPopup("Ta nazwa jest już zajęta");
                $("#popup_box").find("input").focus();
            }
            else {
                $("form").attr("action", "index.php?path=" + encodeURIComponent(template) + "&saveas=" + encodeURIComponent(filename) + "&group=" + encodeURIComponent(group)).submit();
            };
        });
        $("#popup_box button.cancel").click(function() { hidePopup(); });
    };
    
    $("nav dd.template").click(function() {
        $button = $(this);
        if(detectChanges()) {
            showPopup("fi-alert", "Nie zapisano zmian!<br>Czy mimo to chcesz kontynuować?", "Tak,Nie");
            $("#popup_box .confirm").click(function() { hidePopup(); createNewPage($button); });
            $("#popup_box .cancel").click(function() { hidePopup(); return false; });
        }
        else {
            createNewPage($button);
        };
    });
    
    // ==========================================
    //                   Search
    // ==========================================
    
    function goSearch($trigger) {
        find = $("#search input").val();
        if(find.length < 3) {
            infoPopup("Wpisana fraza jest za krótka");
            $trigger.blur();
        }
        else {
            //$("form").attr("action", "search.php?page=" + save_path + "&find=" + encodeURIComponent(find)).submit();
            location.href = "search.php?page=" + save_path + "&find=" + encodeURIComponent(find);
        };
    };

    $("#search button").click(function() { goSearch($(this)); });
    $('#search input').on('keypress', function (e) { if(e.which == 13) { goSearch($(this)); }; });
    
    // ==========================================
    //              Gerate XML code
    // ==========================================
    
    // Output test
	function updateOutput() {
        $("#style_buttons").remove(); // remove temp content
        if($("#output").attr("name") == "output|order") {
            order = makeOrder();
            $("#output").val(order);
        }
        else if($("#output").attr("name") == "output|csv") {
            csv = makeCsv();
            $("#output").val(csv);
        }
        else {
            xml = makeXml();
            $("#output").val(xml);
        };
        
        // ====== Memorize initial state ======
        if($("#detectChanges").val() == "-") {
            $("#detectChanges").val( $("#output").val() );
        };
        
	};
	updateOutput();
    
    // ====== Dynamic changes detector ======
    function detectChanges() {
        updateOutput();
        if($("#detectChanges").val() != $("#output").val()) {
            return true;
        }
        else {
            return false;
        };
    };

	// Preview box
	$("#outputs").prepend("<p><b>Podgląd kodu XML</B> / kliknij aby zamknąć</p>");
    $("#update_output").click(function() {
        updateOutput();
		$("#outputs").fadeIn(500);
        infoPopup("changed: " + detectChanges());
        $(this).blur();
		
		hei = $(window).height();
		$("#outputs input, #outputs p").each( function() { hei = hei - $(this).height(); });
		$("#outputs textarea").css({ "height": hei - 3 });
        return false;
    });
	$("#outputs").click(function() { $(this).fadeOut(250); });

    function getVal($id) {
    // --------------------------------
    // $id = jquery element ID
    // --------------------------------
    // RETURNS: <string> element text value
    // --------------------------------
        tag = $id.prop("tagName").toLowerCase();
        //alert(tag);
        if(tag == "textarea") { return $id.val().replace(/\n/g, "[br]"); }
        else if(tag == "input") { return $id.val(); }
        else if(tag == "p") { return $id.text(); }
        else if(tag == "img" || tag == "iframe" || tag == "embed") { return $id.attr("src"); }
        else { return $id.attr("value"); };
    };

    function makeXml() {
    // --------------------------------
    // RETURNS: <string> XML output made from HTML CMS form
    // --------------------------------
        //alert("update xml")
        xml = "<xable>\n";
        // Article
        $("#cms.xml article").each(function() {
            article_tag = $(this).attr("class");
            xml = xml + "\t<" + article_tag + ">\n";
            set = ""; // reset media set
            // Section
            $(this).children("section").each(function() {
                section_tag = $(this).attr("class");
                xml = xml + "\t\t<" + section_tag + ">\n";
                // Attributes
                $(this).children().each(function() {
                    if( (att_tag = $(this).attr("class")) != undefined ) {
                        // ====== Type ======
                        if( att_tag == "type") {
                            type = $(this).val();
                            xml = xml + "\t\t\t<type>" + type + "</type>\n";
                        }
                        // ====== File ======
                        else if( att_tag == "file" ) {
                            val = getVal( $(this) );
                            xml = xml + "\t\t\t<" + att_tag + ">" + val + "</" + att_tag + ">\n";
                        }
                        // ====== Table ======
                        else if( att_tag == "table" ) {
                            xml = xml + "\t\t\t<table>\n";
                            $(this).children("table").each(function() {
                                language = $(this).attr("class");
                                $(this).find("tr").each(function() {
                                    row = [];
                                    num = $(this).attr("class");
                                    if(num != "start") {
                                        $(this).find("td").each(function() {
                                            val = $(this).html().replace(/<br>/g, "[br]").replace(/\&nbsp;/g, " ").replace(/;/g, ",");
                                            $(this).html(val);
                                            val = $(this).text().trim(); // NO formatting!
                                            row[row.length] = val;
                                        });
                                        row.shift(); // delete row number
                                        xml = xml + "\t\t\t\t<" + language + ">" + row.join(";") + "</" + language + ">\n";
                                    };
                                });
                            });
                            xml = xml + "\t\t\t</table>\n";
                        }
                        // ====== media Set ======
                        else if( att_tag == "set" ) {
                            mode = $(this).find("input").attr("type");
                            $(this).find("input").each(function() {
                                if( $(this).prop("checked") == true ) { set = $(this).val(); };
                            });
                        }
                        // ====== Complex data ======
                        else if( $(this).children().length ) {
                            xml = xml + "\t\t\t<" + att_tag + ">\n";
                            $(this).children().each(function() {
                                children_tag = $(this).attr("class");
                                children_val = getVal( $(this) );
                                if((children_tag == undefined || children_val == undefined) && $(this).children().length) {
                                    children_tag = $(this).children().attr("class");
                                    children_val = getVal( $(this).children() );
                                };
                                //if(jQuery.type( children_val ) != "string") { children_val = $(this).attr("value"); };
                                if(att_tag == "text") { children_val = children_val.replace(/\n/g, "[br]"); }; // enter fix
                                
                                xml = xml + "\t\t\t\t<" + children_tag + ">" + children_val + "</" + children_tag + ">\n";
                            });
                            xml = xml + "\t\t\t</" + att_tag + ">\n";
                            if(type == "media") {
                                xml = xml + "\t\t\t<set>" + set + "</set>\n";
                            };
                            // ====== options Selected ======
                            if(type == "option") {
                                selected = [];
                                $(this).closest("section").find(".option input").each(function() {
                                    if( $(this).prop("checked") == true ) { selected[selected.length] = $(this).val(); };
                                });
                                if(selected.length > 0) { selected = selected.join(";"); } else { selected = ""; }; 
                                xml = xml + "\t\t\t<selected>" + selected + "</selected>\n";
                            };
                        }
                        // ====== Simple data ======
                        else {
                            att_val = getVal( $(this) );
                            //alert(att_tag + " -> " + att_val);
                            xml = xml + "\t\t\t<" + att_tag + ">" + att_val + "</" + att_tag + ">\n";
                        };
                    };
                });
                xml = xml + "\t\t</" + section_tag + ">\n";
            });
            xml = xml + "\t</" + article_tag + ">\n";
        });
        // ====== OUTPUT ======
        xml = xml + "</xable>\n";
        //alert("xml done")
        return xml;
    };
    
    // ==========================================
    //           Generate Order list
    // ==========================================
    
    function makeOrder() {
        order = [];
        $("#cms.order h3").each(function() { order[order.length] = $(this).attr("class"); });
        return order.join("\n");
    };
    
    // ==========================================
    //             Generate CSV text
    // ==========================================

    function makeCsv() {
    // ----------------------------------------------
    // RETURN: <array>
    // Gather all table data into a csv type strings
    // ----------------------------------------------
        var csv = new Array();
        $("table tr").each(function() {
            var row = new Array();
            $(this).children("td").each(function() {
                if($(this).attr("class") != "nr" && $(this).attr("class") != "manual") {
                    content = $(this).html();                               // Get full content with HTML formatting tags
                    content = content.replace(/<br>/gi, "[br]");            // Change line breaks into BBCode format
                    $(this).html(content);                                  // Put content back
                    content = $(this).text();                               // Get only text (with BBCode line breaks & without other HTML trash)
                    // ------ fix enter(s) at the begining ------
                    while(content.substr(0, 4) == "[br]") {                 // Leading [br]
                        content = content.substr(4);
                    };
                    // ------ fix enter(s) on the end ------
                    while(content.substr(content.length - 4) == "[br]") {
                        content = content.substr(0, content.length - 4);    // Trailing [br]
                    };
                    // Apply output
                    row[row.length] = content.replace(/;/g, ",");           // replace ";" with ",", due to csv format limitations
                    $(this).html(content.replace(/\[br\]/gi, "<br>"));      // change BBCode line breaks to HTML format for screen preview
                };
            });
            csv[csv.length] = row.join(";"); // Add
        });
        return csv.join("\n");
    };
    
    // ==========================================
    //             Menu fold & expand
    // ==========================================
    
    nav_item_padding_top = $("nav dd").css("padding-top");
    nav_item_padding_bottom = $("nav dd").css("padding-bottom");
    nav_item_height = $("nav dd").height();
    
    function foldExpandMenuItems($title) {
    // ----------------------------------------------
    // $title = <object> group title: dt
    // ----------------------------------------------
    // Folds or expands all items in group except the current
    // ----------------------------------------------
        time = 500;
        // ====== Expand ======
        if($title.attr("class") == "folded") {
            $title.closest(".group").find("a").each(function() {
                if($(this).attr("class") != "current") {
                    $(this).children("dd").animate({"height": nav_item_height, "padding-top": nav_item_padding_top, "padding-bottom": nav_item_padding_bottom}, time);
                }
            });
			$title.closest(".group").find(".template").animate({"height": nav_item_height, "padding-top": nav_item_padding_top, "padding-bottom": nav_item_padding_bottom}, time);
            $title.delay(time).removeClass("folded");
            $title.find(".expand").delay(time).removeClass("fi-list").addClass("fi-minus").attr("help", "Zwiń listę podstron");
        }
        // ====== Fold ======
        else {
            $title.closest(".group").find("a").each(function() {
                if($(this).attr("class") != "current") {
                    $(this).children("dd").animate({"height": 0, "padding-top": 0, "padding-bottom": 0}, 500);
                }
            });
			$title.closest(".group").find(".template").animate({"height": 0, "padding-top": 0, "padding-bottom": 0}, 500);
            $title.delay(time).addClass("folded");
            $title.find(".expand").delay(time).addClass("fi-list").removeClass("fi-minus").attr("help", "Rozwiń listę podstron");
        };
    };
    
    setTimeout(function() {
        current = -1;
        num = 0;
        if(jQuery.type( $("nav dt").eq(1).attr("help") ) == "string") { lib = 1; } else { lib = 0; };
        if(current_path.path("extension") == "template") { tmp = current_path; } else { tmp = false; };
        // Nav groups loop
        $("nav dt").each(function() {
            // Find current document
            if($(this).closest(".group").find("a.current").length) { current = num; };
            // Find current template
            if(tmp && tmp == $(this).closest(".group").find("dd.template").attr("href")) { current = num; };
            // ====== Fold ======
            if(num == 0) { } // Don't fold preferences
            else if(current == 0 && num == lib + 1) { } // Don't fold Pages list on Preferences selected
            else if($(this).closest(".group").find("dd").length < 3) {} // Dotn't fold if less than 3 items
            else if(current != num) { // Fold not-selected groups
                foldExpandMenuItems($(this));
            };
            num++;
        })
        
    }, 300);
    
    $("nav dt").click(function() {
        foldExpandMenuItems($(this));
    });
    
    // ==========================================
    //              CSV table edit
    // ==========================================

    function initializeCsvButtos() {
    // ----------------------------------
    // Initialize delete button functions
    // ----------------------------------
        $("td.nr").unbind();
        // Show delete button instead off row number
        $("td.nr").mouseenter(function() {
            $(this).find("span.id").hide();
            $(this).find("span.delete").show();
        });
        // Hide delete button & show row number
        $("td.nr").mouseleave(function() {
            $(this).find("span.id").show();
            $(this).find("span.delete").hide();
        });
        // Delete row
        $("td.nr").click(function() {
            $(this).closest("tr").hide(250, function() {
                $(this).remove();
                // Rows renumbering
                nr = 1;
                $("td.nr .id").each(function() { $(this).text(nr++); });
            });
        });
    };
    initializeCsvButtos();

    // Add new row
	$("button.add_row").click(function() {
		$("table tr").first().after( "<tr class='new edit' style='display:none'>\n" + $("table tr").last().html() + "</tr>\n" );
		$new = $("table tr.new").first();
		$new.children("td").each(function() { if($(this).attr("class") != "nr") { $(this).text(""); }; }); // delete previous content

        num = 1;
        $("table td.nr").each(function() {
            $(this).find(".id").text(num++);
            
            
        });

		$new.show(250, function(){ $(this).css({ "transition": "1s", "background-color": "#e1fff4" }, 250); });
        initializeCsvButtos();
        initializeDectectChanges();
        $(this).blur();
        return false;
	});
	
    // ==========================================
    //                 Disabled
    // ==========================================
	
	function initializeDisabled() {
		$("form section input.disabled").each(function() {
			$(this).closest("section").find("input, label").css({ "opacity": "0.5" }).unbind().focus(function() {
				infoPopup("Tego pola nie można edytować");
				//$(this).css({"opacity": "0.5"});
				$(this).blur();
				return false;
			});
		});
	};
	initializeDisabled();

    // ==========================================
    //               Initial Popup
    // ==========================================

    setTimeout(function() {
        if($("input#popup").length) {
            popup = $("input#popup").val().split("|");
            message = popup.shift(); // message text
            popup = popup.pop(); // icon type (if any)
            infoPopup(message, popup);
        };
    }, 500);
    
    // ==========================================
    //                Hide loader
    // ==========================================
    
    $("#loader").fadeOut(200);

});