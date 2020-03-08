$(document).ready(function() {

    ANIMATION_TIME = 250;
    
    var edit_path = $("input#edit_path").val();
    var save_path = $("input#save_path").val();
    var site_root = $("input#root").val();
    var edit_lang = $("input#edit_lang").val();
    var languages = $("#languages").val().split(",");
    
    // ==========================================
    //         Update path changed by php
    // ==========================================

    if(edit_path.path("filename").path("extension") == "xml") {
        php_path = edit_path.path("dirname") + "/" + edit_path.path("filename");
    }
    else {
        php_path = edit_path;
    };
    path = urlQuery("path");
    if(path == undefined || !path || path == "" || decodeURIComponent(path) != php_path) {
        urlQuery("path=" + encodeURIComponent(php_path));
    };
    
    // ==========================================
    //             Responsive layout
    // ==========================================
    
    function initializeMobile() {
        $(".mobile h1").click(function() {
            toggleMenu();
        })

        $(".mobile .item_option").click(function() {
            toggleMenu();
        });
    };

    function resposive() {
        win_wid = $(window).width();
        if(win_wid >= 1000) {
            //desktop
            $("nav").attr("style", "").removeClass("mobile");
            $("#toogle_mobile_menu").removeClass("visible");
            $("#page_fader").fadeOut(ANIMATION_TIME, function() { $(this).remove() });
            $("footer").fadeIn(ANIMATION_TIME);
        }
        else {
            if($("#toogle_mobile_menu").attr("class") != "visible") {
                $("nav").addClass("mobile").css({ "left": (-1 * $("nav h1").width()) });
            }
        }
    };

    resposive();
    $(window).resize(function() { resposive() });
    
    function toggleMenu() {
        $button = $("#toogle_mobile_menu");
        // Hide
        if($button.attr("class") != undefined && $button.attr("class").indexOf("visible") > -1) {
            $button.removeClass("visible");
            $("nav .nav_container").removeClass("scrolable");
            $("#page_fader").fadeOut(ANIMATION_TIME, function() { $(this).remove() });
            $("nav").stop().animate({ "left": (-1 * $("nav h1").width()) }, ANIMATION_TIME);
            $("footer").fadeIn(ANIMATION_TIME);
            $("nav").css({ "height": "auto" });
            
        }
        // Show
        else {
            $("body").append("<div id='page_fader'></div>");
            $("#page_fader").fadeIn(ANIMATION_TIME);
            $("nav .nav_container").addClass("scrolable");
            $button.addClass("visible");
            $("nav").stop().animate({ "left": 0 }, ANIMATION_TIME );
            
            $("#page_fader").click(function() { toggleMenu(); });
            $("footer").fadeOut(ANIMATION_TIME);
            $("nav").css({ "height": $(document).height() });
            initializeMobile();
        };
    }
    
    $("#toogle_mobile_menu").click(function() {
        $(this).blur();
        toggleMenu();
    })

    // ==========================================
    //                 Navigation
    // ==========================================
    
    function showTreeBrench($item) {
        $dl = $item.closest("dl");
        while($dl.parent().closest("dl").length) {
            $dl.show();
            $dl = $dl.parent().closest("dl");
        };
    }
    
    text_path = edit_path.substr(site_root.length).replace(/\//g, "<span>/</span>");
    
    $("#path_info").html("<a href='" + edit_path + "' target='_blank'>" + text_path + "</a>");
    //$("#path_info").html("<a href='" + save_path + "'>" + save_path + "</a>");
    
    // Tree brenches icons layout
    $("nav dl").each(function() {
        $(this).find("dd").each(function() {
            $dd = $(this);
            
            if($dd.next("dd").length) {
                $dd.children("div").find(".item_indent").last().addClass("brench-t");
            }
            else {
                $dd.children("div").find(".item_indent").last().addClass("brench-l");
            };
            
            $folder = $(this);
            if($folder.length) {
                depth = 1;
                while($folder.parent().closest("dd.folder").length) {
                    depth++;
                    if($folder.parent().closest("dd.folder").next("dd").length) {
                        $indent_icons = $dd.children("div").find(".item_indent");
                        $icon = $dd.children("div").find(".item_indent").eq($indent_icons.length - depth);
                        if($icon.length) { $icon.addClass("brench-i"); };
                    }
                    $folder = $folder.parent().closest("dd.folder");
                };
            };
        });
    });
    
    if(edit_path.path("extension") == "order") {
        $("nav .action-change-subpages-order").each(function() {
            if($(this).attr("value") == edit_path) {

                $folder = $(this).closest("dd.folder");
                
                showTreeBrench($folder.children("dl"))

                $folder.children("dl").children("dd").find(".nav_item").addClass("current");
                $folder.children("dl").children("dd.add_page").find(".nav_item").removeClass("current");
                
                folder = $folder.children("div").find(".item_label .lang_title").html();
                folder = [];
                
                $folder.children("div").find(".item_label .lang_title").each(function() {
                    folder[folder.length] = $(this)[0].outerHTML;
                })
                
                $("h2").html(LOCALIZE["subpages-order"] + " <span>/ " + LOCALIZE["in-group"] + " " + folder.join("") + "</span>");
            }
        })
    }
    
    $("nav p .error").closest("div").find(".item_options_box").remove();

    // ====== Nav main group fold / expand ======
    $("nav dl dt > .nav_group_title .item_icon, nav dt > .nav_group_title .item_label").click(function() {
        
        $dl = $(this).closest("dl");
        $dd = $dl.children("dd");
        
        if($dd.css("display") == "none") {
            $dd.slideDown(ANIMATION_TIME, function() {
                //longTitles();
            });
        }
        //else if(!$dd.find(".current").length) { // Do not fold current group
        else {
            $dd.slideUp(ANIMATION_TIME);
        }
    })

    // ====== Fold Nav groups if not all fits in a browser window ======
    var NAV_NOFOLD = [];
    if($("#site_nav_nofold").length) { var NAV_NOFOLD = $("#site_nav_nofold").val().split(","); };
    
    function lastVisible() {
        $last = $("#page_preview");
        last_bottom = $last.offset().top + $last.height() + (parseInt($last.css("padding-top")) * 2);
        win_hei = $(window).height();
        if(win_hei >= (last_bottom + 5)) {
            return true;
        }
        else {
            return false;
        }
    };
    
    setTimeout(function() {
        if(!lastVisible()) {
            if($("nav dd div.current").length) {
                $("nav dt").each(function() {
                    label = $(this).find(".item_label p").html().split("<").shift();
                    if(NAV_NOFOLD.indexOf(label) < 0) {
                        $dl = $(this).closest("dl");
                        if(!$dl.find("dd div.current").length) {
                            $dl.children("dd").slideUp(ANIMATION_TIME);
                        }
                    };
                })
            }
        }
    }, ANIMATION_TIME);

    // ====== Fold Nav groups specified in ini_fold ======
    setTimeout(function() {
        $("nav dt input.ini_fold").each(function() {
            $dl = $(this).closest("dl");
            $dd = $dl.children("dd");
            if(!$dl.find("dd").children("div.current").length) { // Do not fold current group
                $dd.slideUp(ANIMATION_TIME);
            }
        })
    }, ANIMATION_TIME);
    
    // ==========================================
    //            Subpages count info
    // ==========================================
    
    function depthInfo($dd) {
        $label = $dd.children(".nav_group_title").find(".item_label p");
        $dl = $dd.children("dl");
        count = $dl.children("dd").length;
        if($dl.children("dd.add_page").length) { count--; };
        $label.find(".active_mode").first().before("<small class='pages_count'>(" + count + ")</small>");
        $dl.children("dd.folder").each(function() { depthInfo($(this)); });
    }
    
    $("nav dt").each(function() {
        $label = $(this).find(".item_label p");
        label = $label.html();
        $dl =  $(this).closest("dl");
        count = $dl.children("dd").length;
        if($dl.children("dd.add_page").length) { count--; };
        $label.html(label + "<small class='pages_count'>(" + count + ")</small>");
        $dl.children("dd.folder").each(function() { depthInfo($(this)); });
    })

    // ==========================================
    //            Page preview button
    // ==========================================
    
    if(
        // Non page document selected
        $("nav dd > div.current").length &&
        $("nav dd > div.current").closest("dd").attr("data-type") == "page"
    ) {
        // Update link if Page is selected
        $a = $("#page_preview").closest("a");
        href = $a.attr("href");
        link = $a.attr("data-link");
        $a.attr("data-link", href + link);
        // Update language on click
        $a.click(function() {
            $("#lang li").each(function() { if($(this).css("display") != "none") { lang = $(this).attr("value"); }; });
            link = $(this).attr("data-link");
            link = link.replace(/@language/, lang); // add laguage if exists in pattern
            $(this).attr("href", link);
        })
    }
    else {
        $("#page_preview").closest("a").attr("data-link", "");
    }
    
    // ==========================================
    //                  Search
    // ==========================================
    
    function getAllDocuments() {
        var documents_list = [];
        var text_files = [ "csv", "txt", "xml" ];

        $("nav dd .nav_item .item_label").each(function() {
            href = $(this).attr("href");
            if(href != undefined && href != "" && text_files.indexOf(href.path("extension")) > -1) {
                documents_list[documents_list.length] = href;
            }
        })
        return documents_list;
    };
    
    function getCurrentLanguage() {
        var lang = $("#edit_lang").val();
        $("#lang li").each(function() {
            if($(this).css("display") != "none") {
                lang = $(this).attr("value");
            }
        })
        return lang;
    }
    
    function goSearch($trigger) {
        find = $(".search_box input").first().val();
        if(find.length < 3) {
            infoPopup(LOCALIZE["short-text-alert"]);
            $trigger.blur();
        }
        else {
            html = "<form action='search.php' method='post' style='display:none;'>\n" +
                    "<input name='find' value='" + find + "'>\n" +
                    "<input name='language' value='" + getCurrentLanguage() + "'>\n" +
                    "<input name='back_url' value='" + save_path + "'>\n" +
                    "<textarea name='documents'>" + getAllDocuments().join("\n") + "</textarea>\n" +
                "</form>\n";
            $(".search_box").append(html);
            
            $(".search_box form").submit();

        };
    };

    $(".search_box button").click(function() { goSearch($(this)); });
    $('.search_box input').first().on('keypress', function (e) { if(e.which == 13) { goSearch($(this)); }; });
    
    // ==========================================
    //              Scroll to search
    // ==========================================

    if($("input#search-found").length) {
        setTimeout(function() {
            found = $("input#search-found").val().split(" ");

            $section = $("main article." + found.shift()).eq(found.shift()).find("section." + found.shift());
            
            if($section.length) {
                // ====== Indicate found item ======
                // Multiple content (files / table cells)
                if(found.length) {
                    type = $section.find("input.type").val();
                    if(type == "media") {
                        $section.find("div.media .thumb").eq(found.shift()).css({ "outline": "5px solid #ffff55" });
                    }
                    else if(type == "table") {
                        $row = $section.find("table." + getCurrentLanguage() + " tr").eq(parseInt(found.shift()) + 1);
                        $cell = $row.find("td").eq(parseInt(found.shift()) + 1);
                        $cell.css({ "background-color": "#ffff88" });
                    }
                }
                // Single content
                else {
                    $section.find("input, textarea").css({ "background-color": "#ffff88" });
                    $section.find("div.image .thumb").css({ "outline": "10px solid #ffff55" });
                }
                
                // ====== Expand folderd article ======
                $article = $section.closest("article");
                if($article.find(".fold_expand span.fold").css("display") == "none") {
                    $buttons = $article.find("div.fold_expand");
                    $article.find("section").show(100, function() {
                        $buttons.find("span.fold").show();
                        $buttons.find("span.expand").hide();
                    });
                }
                
                // ====== Scroll to item ======
                setTimeout(function() {
                    search_scroll = $section.offset().top;
                    $("html, body").animate({ scrollTop: search_scroll - 30 }, 500);
                }, 200)

            };
        }, 1000); // wait for article auto-fold complete
    };

});