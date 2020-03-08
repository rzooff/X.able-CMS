$(document).ready(function() {

    // Disable 'submit' action by Enter inside a form field
    $('form main input').on('keypress', function (e) { if(e.which == 13){ return false; }; });
    
    // Layout - Nav/Form resize
    var resizingNow = false;
    function formResize(delay_time) {
        if(!resizingNow) {
            resizingNow = true;
            setTimeout(function() {
                $form = $("form#cms");
                $form.css({ "min-height": "100vh" });
                win_hei = $(document).height();
                if(win_hei > $form.height()) {
                    $form.css({ "min-height": win_hei });
                };
                setTimeout(function() {
                    resizingNow = false;
                }, 250)
                
            }, delay_time + 10);
        };
    };
    $(window).resize(function() { formResize(250); });
    
    function scrollTo($element) {
        var scroll = $element.offset().top;
        var padding_top = 100;
        $("html, body").animate({ scrollTop: scroll - padding_top }, 500);
    };

    // ==========================================
    //             Global Variables
    // ==========================================
    
    ANIMATION_TIME = 250;
    
    var site_root = $("input#root").val();
    var current_path = $("input#path").val();
    var edit_path = $("input#edit_path").val();
    var save_path = $("input#save_path").val();
    var languages = $("input#languages").val().split(",");
    var lang = $("input#edit_lang").val();
    var admin_lang = $("input#admin_lang").val();
    
    if($("#site_new_below").length) {
        var site_new_below = $("#site_new_below").val().split(",");
    }
    else {
        var site_new_below = "multi_page";
    }
    
    var textarea_min = 120; // Minimal textarea height

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
        
        // Fix line breaks in table cells
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
        
        // Date update
        $("section .date_box input").unbind("change").change(function() {
            var $section = $(this).closest("section");
            saveDate($section);
            scrollToSorted($section);
        });

        // Date update
        $("section.string input.string").unbind("change").change(function() {
            var $section = $(this).closest("section");
            updateString($section, true);
        });
    };
    
    function updateString($section, input_focus) {
        $string = $section.find("input.string");
        var string = $string.val();
        // ====== Get options ======
        var options = $section.find("input.options").val();
        if(options.indexOf("default=") > -1) {
            default_value = options.split("default=").pop().split(";").shift();
        }
        else {
            default_value = false;
        };
        if(options.indexOf("type=") > -1) {
            value_type = options.split("type=").pop().split(";").shift();
        }
        else {
            value_type = false;
        };
        options = options.split(";");
        // ====== Check for options to apply ======
        // Lowercase/Uppercase/Capitalize
        if(string != "" && options.indexOf("lowercase") > -1) {
            string = string.toLowerCase();
            $string.val(string);
        }
        else if(string != "" && options.indexOf("upercase") > -1) {
            string = string.toUpperCase();
            $string.val(string);
        }
        else if(string != "" && options.indexOf("capitalize") > -1) {
            string = string.capitalize("title");
            $string.val(string);
        };
        //alert("string: " + string + "\noptions: " + options.join(", ") + "\ntype: " + value_type + "\nfocus: " + input_focus);
        // Default
        if(string == "" && default_value && input_focus != true) {
            $string.val(default_value);
        }
        // Required
        else if(string == "" && options.indexOf("required") > -1) {
            if(input_focus == true) {
                infoPopup(LOCALIZE["empty-input-alert"]);
                $string.focus();
            }
        }
        // Type
        else if(string != "" && value_type) {
            if(value_type == "email" && !validateEmail(string)) {
                if(input_focus == true) {
                    infoPopup(LOCALIZE["enter-email"]);
                    $string.focus();
                }
                else {
                    $string.val("");
                }
            }
            else if(value_type == "url" && !validateUrl(string)) {
                if(input_focus == true) {
                    infoPopup(LOCALIZE["enter-url"]);
                    $string.focus();
                }
                else {
                    $string.val("");
                }
            }
            else if(value_type == "tag") {
                if(string.replace(/[^a-z0-9|^\-|^_]/gi, "") != string) {
                    if(input_focus == true) {
                        infoPopup(LOCALIZE["enter-tag"]);
                        $string.focus();
                    }
                    else {
                        $string.val("");
                    }
                }
            }
            else if(value_type == "letter" && string.length > 0) {
                if(string.replace(/[^a-z]/gi, "") != string || string.length > 1) {
                    if(input_focus == true) {
                        infoPopup(LOCALIZE["enter-letter"]);
                        $string.focus();
                    }
                    else {
                        $string.val("");
                    }
                }
            }
            else if(value_type == "number" && parseFloat(string) != string) {
                if(input_focus == true) {
                    infoPopup(LOCALIZE["enter-number"]);
                    $string.focus();
                }
                else {
                    $string.val("");
                }
            }
            else if(value_type == "integer" && parseInt(string) != string) {
                if(input_focus == true) {
                    infoPopup(LOCALIZE["enter-integer"]);
                    $string.focus();
                }
                else {
                    $string.val("");
                }
            }
            else if(value_type == "float") {
                if(parseInt(string) == string) {
                    string = string + ".0";
                    $string.val(string);
                }
                else if(parseFloat(string) != string) {
                    if(input_focus == true) {
                        infoPopup(LOCALIZE["enter-number"]);
                        $string.focus();
                    }
                    else {
                        $string.val("");
                    }
                }
            }
        }
    };
    $("form article section.string").each(function() { updateString($(this), false); });
    
    initializeDectectChanges();

    // ==========================================
    //           Float HEADER Buttons
    // ==========================================

    // Add float buttons copy
    $("header .buttons").after($("header .buttons")[0].outerHTML);
    $("header .buttons").last().addClass("float_buttons");

    function showButtons($buttons) {
        if(parseInt($buttons.css("margin-top")) < 50) {
            $buttons.addClass("float_buttons").stop().animate({ "margin-top": "-15px" }, ANIMATION_TIME);
        };
    };
    
    function hideButtons($buttons) {
        $buttons.stop().animate({ "margin-top": "-100px", "opacity": 0 }, ANIMATION_TIME / 2, function() {
            $buttons.attr("style", "");
        });
    };
    
    var SCROLL_MEMO = $(window).scrollTop();
    
    function floatButtons() {
        $buttons = $("header .float_buttons");
        scroll = $(window).scrollTop();
        if(scroll < SCROLL_MEMO && scroll < ($(window).height() * 1.1)) {
            hideButtons($buttons);
        }
        else if(scroll > SCROLL_MEMO && scroll > ($(window).height() * 0.5)) {
            showButtons($buttons);
        };
        SCROLL_MEMO = scroll;
    };
    
    $(window).scroll(function() { floatButtons(); });
    setTimeout(function() { SCROLL_MEMO = 0; floatButtons(); }, 500);
    
    // ==========================================
    //   Browse file + Navigation Title autofill
    // ==========================================

    function getBrowseData() {
        var mediaFolders = $("#media_folders").val().split(";");
        var pagesFiles = $("#pages_files").val().split(";");
        var pages_path = "pages/";
        for(i in pagesFiles) {
            file = pagesFiles[i];
            dir = file.path("dirname").substr(pages_path.length);
            if(dir != "") { file = dir + "/" + file.path("filename"); }
            else { file = file.path("filename"); };
            if(file != "") { pagesFiles[i] = file; }
        };
        return { "media": mediaFolders, "pages": pagesFiles };  
    };
    var browseData = getBrowseData();
    
    function initializeBrowseInput() {
        // Load date(s) from php
        $("form section .browse_box").each(function() {
            $section = $(this).closest("section");
            href = $section.find("input.string").val();
            $section.find(".browse_box input").val(href);
        });
        
        // Auto update Title on change
        $("form section .browse_box input").unbind("change").change(function() {
            $section = $(this).closest("section");
            href = $(this).val();
            $section.find("input.string").val(href);
            
            $title = $section.closest("article").find("section.title div.text input");
            updateNavigationTitles($title);
            checkNavigationDuplications();
        })
        
        // ====== Button ======
        $("form section .browse_box button.browse").unbind("click").click(function() {
            $section = $(this).closest("section");
            section_tag = $section.attr("class");
            browse_pages_tags = [ "href", "href_404" ];
            if(browse_pages_tags.indexOf(section_tag) > -1) {
                pathes_list = browseData["pages"];
                showPopup("fi-folder", LOCALIZE["browse-file-info"], LOCALIZE["apply-cancel-buttons"], pathes_list);
            }
            else {
                pathes_list = browseData["media"];
                showPopup("fi-folder", LOCALIZE["browse-folder-info"], LOCALIZE["apply-cancel-buttons"], pathes_list);
            };
            
            href = $section.find(".browse_box input").val();
            if(href != "" && pathes_list.indexOf(href) > -1) {
                $("#popup_box select").val(href);
            }
            
            $("#popup_box .confirm").click(function() {
                href = $("#popup_box select").val();
                if(href != "") {
                    $section.find(".browse_box input").val(href);
                    $section.find("input.string").val(href);
                    
                    $title = $section.closest("article").find("section.title div.text input");
                    updateNavigationTitles($title);
                    checkNavigationDuplications();
                    
                    hidePopup();
                }
                else {
                    infoPopup(LOCALIZE["required-select-option"]);
                };
            });
            $("#popup_box .cancel").click(function() { hidePopup(); });
            return false;
        })
    };
    initializeBrowseInput();
    
    // ====== Navigation autofill ======
    
    function checkNavigationDuplications() {
        // ====== Check for duplicated href ======
        href_list = [];
        duplicated = [];
        $("form article.multi_page").each(function() {
            href = $(this).find("section.href input.string").first().val();
            //alert("href: " + href);
            if(href_list.indexOf(href) > -1) { duplicated[duplicated.length] = href; };
            href_list[href_list.length] = href;
        });
        if(duplicated.length) {
            setTimeout(function() {
                showPopup("fi-alert", LOCALIZE["navigation-href-duplicated"] + "<br>" + duplicated.join(", "), LOCALIZE["ok-label"]);
                $("#popup_box .confirm").click(function() { hidePopup(); });
            }, 500)
        };
    };
    
    setTimeout(function() {
        checkNavigationDuplications();
    }, 1000);

    function updateNavigationTitles($title) {
        if(typeof(navigationTitles) == "object") {
            // Update title
            var updated = false;
            $article = $title.closest("article");
            href = $article.find("section.href input.string").first().val();
            titles = navigationTitles[ site_root + "/pages/" + href + ".xml" ];
            if(typeof(titles) == "object") {
                for(language in titles) {
                    $title = $article.find("section.title div.text input." + language);
                    if($title.length) {
                        $title.val( titles[language] );
                        var updated = true;
                    }
                }
            };
            // Disable/enable edit
            $title = $article.find("section.title div.text input");
            if(updated) {
                $title.prop("disabled", true);
                $title.closest("div.text").click(function() {
                    infoPopup(LOCALIZE["navigation-title-autofill-info"]);
                    return false;
                })
            }
            else {
                $title.prop("disabled", false);
                $title.closest("div.text").unbind("click");
            };
        };
    };
    
    // ====== Launch update on navigation + option=true ======
    if(save_path == site_root + "/navigation.xml" && $("#site_auto_navigation_title").val() == "true") {
        var navigationTitles = {};
        $("nav dd").each(function() {
            if($(this).attr("data-type") == "page") {
                $label = $(this).find(".item_label").first();
                if($label.find(".lang_title").length) {
                    href = $label.attr("href");
                    //alert(href);
                    titles = {};
                    $label.find(".lang_title").each(function() {
                        titles[ $(this).attr("class").split(" ").pop() ] = $(this).text();
                    })
                    navigationTitles[href] = titles;
                }
            }
        });

        $("form article.multi_page").each(function() {
            $title = $(this).find("section.title div.text input");
            updateNavigationTitles($title);
        });

    }
    else {
        var navigationTitles = false;
    };

    // ==========================================
    //              Sort Articles
    // ==========================================
    
    function sortByInput(article_tag, sort_tag) {
        $articles = $("form article." + article_tag);
        $previous = $articles.prev();
        if($articles.length) {
            var posts = {};
            var n = 0;
            $articles.each(function() {
                date = $(this).find("section." + sort_tag + " input.date").first().val();
                id = date + "_[" + ($articles.length - n) + "]";
                posts[id] = $(this);
                n++;
                
                if($(this).find("div.buttons button.up").length) {
                    //$(this).find("div.buttons button.up,div.buttons button.down").removeAttr("help").removeClass("down up").addClass("disabled").css({ "opacity": 0.3 });
                    $(this).find("div.buttons button.up,div.buttons button.down").remove();
                };
            });
            $articles.remove();
            sorted_keys = Object.keys(posts).sort();
            for(i in sorted_keys) {
                id = sorted_keys[i];
                $article = posts[id];
                $previous.after($article.clone());
            }
            reinitializePostActiveElements();
            if(Object.keys(posts).join(";") != sorted_keys.reverse().join(";")) { return true; }
        }
    };
    
    function scrollToSorted($section) {
        if($("#site_sort_by_date").val() == "true") {
            var section_tag = $section.attr("class");
            var article_tag = $section.closest("article").attr("class");
            $section.attr("data-sort", "updated");
            if(sortByInput(article_tag, section_tag) == true) {
                var $moved = false;
                $("form article section." + section_tag).each(function() {
                    if($(this).attr("data-sort") == "updated") {
                        $moved = $(this);
                    }
                });
                if($moved.length) {
                    scrollTo($moved);
                };
            };
            $("form article section").removeAttr("data-sort");
        };
    };

    // ==========================================
    //              Date / Calendar
    // ==========================================
    
    var $date_section = $("form article section.date").first();
    var article_tag = $date_section.closest("article").attr("class");
    
    if($date_section.length &&
        $date_section.closest("article").attr("class").indexOf("multi_") == 0 &&
        $date_section.find("input.type").val() == "date" &&
        $("#site_sort_by_date").val() == "true"
    ) {
        setTimeout(function() {
            if(sortByInput(article_tag, "date") == true) {
                //showPopup(LOCALIZE["post-sorted-by-date"]);
                showPopup("fi-calendar", LOCALIZE["post-sorted-by-date"], LOCALIZE["ok-label"]);
                $("#popup_box .confirm").click(function() { hidePopup(); });
            }
        }, 500)
    };
    
    function dateToFormat(date, format) {
    // -------------------------------------------
    // date = <string> eg. "1977-07-29"
    // format = <string> eg. "dd-mm-yyyy"
    // -------------------------------------------
    // RETURN: <string> Date converted from Xable standard yyyy-mm-dd to user format
    // -------------------------------------------
        if(date && date.length >= 8 && date.length == format.length) {
            date = date.split("-") // 0=year, 1=month, 2=day
            if(date.length == 3) {
                return format.replace("yyyy", date[0]).replace("mm", date[1]).replace("dd", date[2]);
            }
            else {
                return false;
            }
        }
        else {
            return false;
        };
    };

    function formatToDate(date, format) {
    // -------------------------------------------
    // date = <string> eg. "29-07-1977"
    // format = <string> eg. "dd-mm-yyyy"
    // -------------------------------------------
    // RETURN: <string> Date converted from user format to Xable standard yyyy-mm-dd
    // -------------------------------------------
        if(date.length >= 8 && date.length == format.length) {
            date = [
                        date.substr(format.indexOf("yyyy"), 4),
                        date.substr(format.indexOf("mm"), 2),
                        date.substr(format.indexOf("dd"), 2)
            ];
        };
        if(date && !isNaN(date[0]) && !isNaN(date[1]) && !isNaN(date[2])) {
            return date.join("-");
        }
        else {
            return false;
        };
    };

    function saveDate($section) {
    // -------------------------------------------
    // $section = <object> Section containing date input field
    // -------------------------------------------
    // Validate date format & put it to input.date in format yyyy-mm-dd
    // -------------------------------------------
        $input = $section.find(".date_box input").first();
        var options = $section.find("input.options").first().val().split(";");
        var date = $input.val();
        var format = $("#site_date_format").val();
        if(date == "") {
            if(options.indexOf("required") < 0) {
                $section.find("input.date").val(""); // Allow empty input
            }
            else {
                showPopup("fi-alert", LOCALIZE["required-date-alert"], "OK");
                $("#popup_box .confirm").click(function() { hidePopup(); loadData($section); $input.focus(); });
            }
        }
        else if(date = formatToDate(date, format)) {
            $section.find("input.date").val(date);
            $input.val(dateToFormat(date, format)); // Fix spacer characters
            $input.blur();
        }
        else {
            showPopup("fi-alert", LOCALIZE["invalid-date-format"] + ":<br>" + format, "OK");
            $("#popup_box .confirm").click(function() { hidePopup(); loadData($section); $input.focus(); });
        }
    };

    function loadData($section) {
    // -------------------------------------------
    // $section = <object> Section containing date input field
    // -------------------------------------------
    // Get data from input.date and show it in user format in input with calendar
    // -------------------------------------------
        var options = $section.find("input.options").first().val().split(";");
        var date = $section.find("input.date").val();
        var format = $("#site_date_format").val();
        if(date.length >= 8 && format.length >= 8) {
            date = date.split("-") // 0=year, 1=month, 2=day
            format = format.replace("yyyy", date[0]).replace("mm", date[1]).replace("dd", date[2]);
            $section.find(".date_box input").first().val(format);
        }
    };
    
    // ====== Calendar ======
    
    var calendarNames = {
        month: {
            en: [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ],
            pl: [ "Styczeń", "Luty", "Marzec", "Kwiecień", "Maj", "Czerwiec", "Lipiec", "Sierpień", "Wrzesień", "Październik", "Listopad", "Grudzień" ]
        },
        dayname: {
            en: [ "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday" ],
            pl: [ "Poniedziałek", "Wtorek", "Środa", "Czwartek", "Piątek", "Sobota", "Niedziela" ]
        },
        shortDayname: {
            en: [ "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun" ],
            pl: [ "Pn", "Wt", "Śr", "Cz", "Pt", "So", "Nd" ],
        }
    };
    
    function initializeCalendar($date) {
    // -------------------------------------------
    // $date = <object> date input
    // -------------------------------------------
    // (re)INITIALIZE: calendar buttons actions
    // -------------------------------------------
        $calendar = showCalendarPopup();
        updateWeekdays($calendar);

        date_string = $date.val();
        if(date_string == "") { date_string = currentTime("yyyy-mm-dd"); };
        date = new Date(date_string);

        updateMonth($calendar, date);
        updateDays($calendar, date);
        updateSelectedDate($calendar, $date);
        initializeSetDate($calendar, $date);

        // ====== Month Change ======
        $calendar.find(".nav button").unbind("click").click(function() {
            $(this).blur();

            $tables = $calendar.find(".tables");
            if($(this).attr("class") == "prev_month") {
                date = new Date($calendar.find("table.days .in_month").first().attr("data-date"));
                date.setDate(date.getDate() - 1);
                $tables.animate({ "left": "100%" }, ANIMATION_TIME, function() { $(this).css({ "left": "-100%" })});
            }
            else { // next_month
                date = new Date($calendar.find("table.days .in_month").last().attr("data-date"));
                date.setDate(date.getDate() + 1);
                $tables.animate({ "left": "-100%" }, ANIMATION_TIME, function() { $(this).css({ "left": "100%" })});
            };
            
            updateDays($calendar, date); // To match box height
            setTimeout(function () { // wait for slide animation
                $tables.animate({ "left": "0" }, ANIMATION_TIME / 2);
                updateMonth($calendar, date);
                updateSelectedDate($calendar, $date);
                initializeSetDate($calendar, $date);
            }, ANIMATION_TIME);
        });

        // ====== Close Button ======
        $calendar.find("button.close").click(function() { hideCalendarPopup(); });
        $(document).keyup(function(e) { if (e.keyCode === 27) { hideCalendarPopup(); }; });  
    };
    
    function showCalendarPopup() {
    // -------------------------------------------
    // $date = <object> date input
    // -------------------------------------------
    // Show: Calendar popup
    // Returns: <object> Calendar DOM object
    // -------------------------------------------
        html = "";
        // ====== Month HEADER ======
        html = html +
            "<div class='header'>" +
                "<p class='title'><span class='fi-calendar'></span></p>" +
                "<button class='close'><span class='icon fi-x'></span></button>" +
            "</div>";
        // ====== Month HEADER ======
        html = html +
            "<div class='nav'>" +
                "<p class='month'>-</p>" +
                "<button class='prev_month'><span class='icon fi-arrow-left'></span></button>" +
                //"<button class='prev_month'><span class='icon'>&lt;</span></button>" +
                "<button class='next_month'><span class='icon fi-arrow-right'></span></button>" +
                //"<button class='next_month'><span class='icon'>&gt;</span></button>" +
            "</div>";
        // ====== Daynames & days Tables ======
        html = html +
            "<div class='tables'>" +
                "<table class='daynames'></table>" +
                "<table class='days'></table>" +
            "</div>";
        // ====== Show POPUP ======
        $("body").find(".calendar_box").remove();
        $("body").append("<div id='page_fader'></div>" + "<div id='calendar_box'>" + html + "</div>");
        $("#page_fader, #calendar_box").fadeIn(ANIMATION_TIME);

        return $("#calendar_box");

    };

    function hideCalendarPopup() {
    // -------------------------------------------
    // Hide & remove calendar popup
    // -------------------------------------------
        $("#page_fader, #calendar_box").fadeOut(ANIMATION_TIME / 2, function() { $(this).remove(); });
        $(document).unbind("keyup");
    };
    
    function updateWeekdays($calendar) {
    // -------------------------------------------
    // $calendar = <object> div.calendar DOM object
    // -------------------------------------------
    // UPDATES: Displayed seekday names list
    // -------------------------------------------
        html = "";
        daynames = calendarNames["shortDayname"][admin_lang];
        for(i in daynames) {
            i = parseInt(i);
            html = html + "<td class='weekday_" + (i + 1) + "'><span>" + daynames[i] + "</span></td>"
        }
        $table = $calendar.find("table.daynames");
        $table.find("tr").remove();
        $table.append("<tr>" + html + "</tr>");
    };

    function updateMonth($calendar, date) {
    // -------------------------------------------
    // $calendar = <object> div.calendar DOM object
    // date = <object> Date
    // -------------------------------------------
    // UPDATES: Displayed Month & year in calendar
    // -------------------------------------------
        html = calendarNames["month"][admin_lang][date.getMonth()] +
            " <span class='year'>" + date.getFullYear() + "</span>";
        $month = $calendar.find(".month");
        $month.html(html);
    };

    function updateDays($calendar, date) {
    // -------------------------------------------
    // $calendar = <object> div.calendar DOM object
    // date = <object> Date
    // -------------------------------------------
    // UPDATES: Days in calendar
    // -------------------------------------------
        var monthDays = getMonthDays(date, "week");
        var month = dateToString("mm", date);
        var today = new Date();
        var rows = [];
        var html = "";

        for(i in monthDays) {
            day = monthDays[i];

            //alert(dateToString("mm-dd / W", day));
            weekday = parseInt(dateToString("W", day));
            dayString = dateToString("yyyy-mm-dd", day);
            attr = [];
            attr[attr.length] =  "weekday_" + weekday;
            attr[attr.length] =  "date_" + dayString;

            if(dateToString("mm", day) == month) {
                attr[attr.length] = "in_month";
                if(day.toDateString() == today.toDateString()) { attr[attr.length] = "current_day"; }; // not mark on outside month view
            }
            else {
                attr[attr.length] = "outside_month";
            };

            html = html + "<td class='" + attr.join(" ") + "' data-date='" + dayString + "'><span>" + dateToString("D", day) + "</span></td>\n";
            if(weekday == 7 && html != "") {
                rows[rows.length] = "<tr>" + html + "</tr>";
                html = "";
            };
        }

        $table = $calendar.find("table.days");
        $table.find("tr").remove();
        $table.append(rows.join("\n"));
    };

    function initializeSetDate($calendar, $date) {
    // -------------------------------------------
    // $calendar = <object> div.calendar DOM object
    // $date = <object> date input
    // -------------------------------------------
    // Initialize: Set date by clicking a day & close popup
    // -------------------------------------------
        $calendar.find("table.days td.in_month").click(function() {
            $calendar.find("table.days td.in_month").unbind("click");

            date = $(this).attr("class").split("date_").pop().split(" ").shift();
            $date.val(date);
            loadData( $date.closest("section") );

            updateSelectedDate($calendar, $date);
            setTimeout(function() {
                hideCalendarPopup();
                scrollToSorted($date.closest("section"));
            }, ANIMATION_TIME);
        })
    };

    function updateSelectedDate($calendar, $date) {
    // -------------------------------------------
    // $calendar = <object> div.calendar DOM object
    // $date = <object> date input
    // -------------------------------------------
    // UPDATES: Selected cell highliht update
    // -------------------------------------------
        selected_date = $date.val();

        $calendar.find("td i.marker").remove();
        $calendar.find(".days .selected").removeClass("selected");

        $selected = $calendar.find(".date_" + selected_date);
        if($selected.length && $selected.attr("class").indexOf("in_month") > -1) {

            $selected.addClass("selected");
            $selected.append("<i class='marker'></i>");
        }
    };

    function initializeDateInput() {
        // Load date(s) from php
        $("form section .date_box").each(function() {
            $section = $(this).closest("section");
            var date = $section.find("input.date").val();
            var options = $section.find("input.options").val().split(";");
            var format = $("#site_date_format").val();

            // Options
            if(options.indexOf("required") > -1) {
                if(date.length < format.length) {
                    date = currentTime("yyyy-mm-dd");
                    $section.find("input.date").val(date);
                };
            };

            // Show date in user format
            if(date = dateToFormat(date, format)) {
                $section.find(".date_box input").first().val(date);
            }
        });
        
        // Calendar button
        $("form section .date_box button").unbind("click").click(function() {
            $section = $(this).closest("section");
            if($section.find(".date_box input").prop("disabled") != true) {
                initializeCalendar( $section.find("input.date") );
            };
            $(this).blur();
            return false;
        });
    };
    initializeDateInput();

    // ==========================================
    //               Action Button
    // ==========================================
    
    function actionButton($button) {
        href = $button.attr("href");
        href = href.replace("@root", $("input#root").val(), href);
        href = href.replace("@edit_path", $("input#edit_path").val(), href);
        href = href.replace("@filename", $("input#edit_path").val().path("filename"), href);
        
        if(href.substr(0, 5) == "form:") {
            href = href.substr(5);

            // Copy input values to new form (text, textarea, string, code)
            $("body").append("<form id='active_form' method='post' action='" + href + "'>\n</form>");
            $button.closest("article").find("section").each(function() {
                name = $(this).attr("class");
                $(this).find("input, textarea").each(function() {
                    if($(this).css("display") != "none" && $(this).attr("class") != "type") {
                        input = "\t<input type='hidden' name='" + name + "' value='" + $(this).val() + "'>\n";
                        $("#active_form").append(input);
                    }
                })
            })
            //alert("FORM:\n" + $("#active_form").html() );
            $("#active_form").submit();
            // Please wait info
            $("#upload_info p").text(LOCALIZE["please-wait"]);
            $("#upload_info").fadeIn(500);
        }
        else if(href.substr(0, 5) == "link:") {
            window.open(href.substr(5));
            $button.blur();
        }
        else if(href.substr(0, 5) == "href:") {
            location.href = href.substr(5);
        }
        else {
            location.href = href;
        }
    };

    function initializeActionButtons() {
        $("a.active_link, button.action_button, footer .unsaved").unbind("click").click(function() {
            $button = $(this);
            if(detectChanges()) {
                showPopup("fi-alert", LOCALIZE["unsaved-changes-alert"], LOCALIZE["yes-no-buttons"]);
                $("#popup_box .confirm").click(function() { hidePopup(); actionButton($button); });
                $("#popup_box .cancel").click(function() { hidePopup(); return false; });
            }
            else {
                actionButton($button);
            }
            return false;
        })
    };
    
    initializeActionButtons();
    
    // ==========================================
    //                Navigation
    // ==========================================
    
    if(edit_path.path("extension") == "template") {
        
        var group_folder = save_path.path("dirname");
        var group = false;
        
        $("nav dd.add_page").each(function() {
            $dd = $(this).closest("dl").children("dd").first();
            folder = $dd.children("div").find(".item_label").attr("href").path("dirname");

            if(group_folder == folder) {
                if($dd.closest("dd.folder").length) {
                    $parent = $dd.closest("dd.folder");
                    group = $parent.children("div").find(".item_label p").html();
                }
                else if($dd.closest("dl").children("dt").length) {
                    $parent = $dd.closest("dl").children("dt");
                    group = $parent.children("div").find(".item_label p").html();
                }
                else {
                    group = "-";
                }
                
                // Mark Add Page button as Current
                //$parent.children("div").addClass("current");
                $parent.children("dl").children("dd.add_page").children("div").addClass("current");
                // Put group name to the title
                title = $("main h2").html().replace("@group", group);
                $("main h2").html(title);
                $("main h2 i").remove();
            }
            else {
                $(this).children(".current").removeClass("current").addClass("active_item");
                $(this).find(".item_label").css({ "opacity": 0.4 });
            }
        })
        // New group
        if(!group) {
            $("nav dd").each(function() {
                href = $(this).children("div").find(".item_label").attr("href");
                if((group_folder + ".xml") == href) {
                    // Mark parent item as Current
                    $(this).children("div").addClass("current");
                    // Put group name to the title
                    group = $(this).children("div").find(".item_label p").html();
                    title = $("main h2").html().replace("@group", group);
                    $("main h2").html(title);
                    $("main h2 i").remove();
                }
            })
        }
    };
    
    // Protected files - disable page remove option
    protected_files = [];
    $("nav").find("dl").first().children("dd").each(function() {
        href = $(this).children("div").find(".item_label").attr("href");
        if(jQuery.type(href) == "string" && href != "") {
            protected_files[protected_files.length] = href;
        }
    });

    // Resize clicable labels
    $("nav dd div.nav_item").each(function() {
        wid = $(this).width(); // + parseInt($(this).css("padding-left")) + parseInt($(this).css("padding-right"));
        $indent = $(this).find(".item_indent");
        wid = wid - ( $indent.width() * $indent.length ) - 25;
        $(this).find(".item_label").css({ "width": wid });
    })
    
    // Resize long titles
    $("nav dd > .nav_item").each(function() {
        $title = $(this).find(".item_label span." + lang).first();
        //$title.css({ "font-size": "inherit"});
        //$title.closest("button").css({ "line-height": "inherit" });
        if($title.length) {
            hei = Math.round( $title.height() / 51.0 );
            text = $title.text();
            if(hei > 1) {
                //alert($title.text() + " / " + $title.height());
                $title.css({ "font-size": "0.85em"});
                $title.closest("button").css({ "line-height": "0.9" });
            }
        }
    })

    // Hide subfolders
    $("nav dd.folder").children("dl").hide();
    
    // Add help text
    $("nav dd.folder").children("div").find("button.item_icon").addClass("manual").attr("help", LOCALIZE["expand-subpages"]);
    
    // Un-hide current tree brench
    $dl = $("nav div.current").closest("dl");
    while($dl.parent().closest("dl").length) {
        $dl.show();
        $dl.parent("dd.folder").children("div").find("button.item_icon").addClass("manual").attr("help", LOCALIZE["fold-subpages"]);
        $dl = $dl.parent().closest("dl");
    };
    
    // Fold expand subfolders list
    $("nav dd.folder").children("div").find("button.item_icon").click(function() {
        $dd = $(this).closest("dd");
        $dl = $dd.find("dl").first();
        if($dl.css("display") == "none") {
            $dl.stop().slideDown(250);
            $(this).attr("help", LOCALIZE["fold-subpages"]);
        }
        //else if(!$dd.find(".current").length) { // Do not fold current group
        else {
            $dl.stop().slideUp(250);
            $(this).attr("help", LOCALIZE["expand-subpages"]);
        }
        
        formResize(250);
    });
    
    // ====== Delete Page ======
    function deletePage(path) {
        showPopup("fi-trash", LOCALIZE["page-delete-confirm"] + "<br>\"" + path.path("basename") + "\"<br><br>" + LOCALIZE["page-delete-alert-html"], LOCALIZE["yes-no-buttons"]);
        $("#popup_box .confirm").click(function() {
            $("#cms").attr("action", "_remove.php?path=" + path).submit(); // output
            // Please wait info
            $("#upload_info p").text(LOCALIZE["please-wait"]);
            $("#upload_info").fadeIn(500);
        });        
        $("#popup_box .cancel").click(function() {
            hidePopup();
        });
    };

    $("nav .action-remove-page").click(function() {
        $dd = $(this).closest("dd");
        path = $dd.find(".item_label").attr("href");

        // Check protected files list
        if(protected_files.indexOf(path) > -1 || path.path("extension") == "order") {
            infoPopup(LOCALIZE["file-protected"]);
        }
        // Remove
        else {
            if(detectChanges()) {
                showPopup("fi-alert", LOCALIZE["unsaved-changes-alert"], LOCALIZE["yes-no-buttons"]);
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
    
    // Click -> edit document
    $("nav div.active_item .item_label").click(function() {
        $dd = $(this).closest("dd");
        // Add Page
        if($dd.attr("class") == "add_page") {
            if(detectChanges()) {
                showPopup("fi-alert", LOCALIZE["unsaved-changes-alert"], LOCALIZE["yes-no-buttons"]);
                $("#popup_box .confirm").click(function() { hidePopup(); createNewPage($dd); });
                $("#popup_box .cancel").click(function() { hidePopup(); return false; });
            }
            else {
                createNewPage($dd);
            };

        }
        // Go to page
        else {
            href = "index.php?path=" + $(this).attr("href") + "&lang=" + lang;
            
            if($(this).find("p .error").length) {
                infoPopup(LOCALIZE["file-folder-not-found"]);
            }
            else if(detectChanges()) {
                showPopup("fi-alert", LOCALIZE["unsaved-changes-alert"], LOCALIZE["yes-no-buttons"]);
                $("#popup_box .confirm").click(function() { location.href = href; });
                $("#popup_box .cancel").click(function() { hidePopup(); return false; });
                return false;
            }
            else {
                //alert(href);
                location.href = href;
            };
        };
    });
    
    $("nav .action-change-subpages-order").click(function() {
        href = "index.php?path=" + $(this).attr("value");
        if(detectChanges()) {
            showPopup("fi-alert", LOCALIZE["unsaved-changes-alert"], LOCALIZE["yes-no-buttons"]);
            $("#popup_box .confirm").click(function() { location.href = href; });
            $("#popup_box .cancel").click(function() { hidePopup(); return false; });
            return false;
        }
        else {
            location.href = href; 
        };
    });
    
    $("nav .action-add-subpage").click(function() {
        $button = $(this);

        if(detectChanges()) {
            showPopup("fi-alert", LOCALIZE["unsaved-changes-alert"], LOCALIZE["yes-no-buttons"]);
            $("#popup_box .confirm").click(function() { hidePopup(); createNewPage($button); });
            $("#popup_box .cancel").click(function() { hidePopup(); return false; });
        }
        else {
            createNewPage($button);
        }
    });
    
    // ==========================================
    //           New Page on Template
    // ==========================================
    function getFilename(file_path) {
        while(file_path.path("extension") != "") {
            file_path = file_path.path("filename");
        }
        return file_path;
    }

    function createNewPage($button) {
        // Get variables
        templates = false;
        if($button.attr("class") == "add_page") {
            templates = $button.find(".item_label").attr("href").split(";");
            taken = $button.find(".taken_filenames").val().split(";");
            folder = $button.find(".folder_path").val();
        }
        else {
            templates = $button.attr("value").split(";");
            $item = $button.closest("dd").children("div").find(".item_label");
            href = $item.attr("href");
            folder = href.path("dirname") + "/" + getFilename(href);
            taken = [];
        }

        // Show popup
        if(templates) {
            $("#page_fader, #popup_container").remove();

            html = "<div id='page_fader'></div>" +
                "<div id='popup_container'><div id='popup_box'>" +
                "<p>" + LOCALIZE["new-page-name"] + "</p>" +
                "<input type='text' taken='" + taken.join(";") + "' folder='" + folder + "'>" +
                "<p class='select'>" + LOCALIZE["template-select"] + ":</p>" +
                "<select>";
            for(i in templates) {
                html = html + "<option path='" + templates[i] + "'>" + templates[i].path("filename").replace(/_/g, " ") + "</option>";
            };
            html = html + "</select><br>";
            html = html +
                "<div class='buttons two'><button class='confirm'>" + LOCALIZE["ok-label"] + "</button><button class='cancel'>" + LOCALIZE["cancel-label"] + "</button></div>" +
                "</div></div>";
            $("body").append(html).css({ "overflow-y": "hidden" });
            
            $("#page_fader, #popup_container").fadeIn(200, function() {
                $(this).find("input").first().focus();
                // Confirm on enter
                $("#popup_container input").on('keypress', function (e) {
                    if(e.which == 13) {
                        $popup_confirm = $(this).closest("#popup_container").find("button.confirm");
                        if($popup_confirm.prop("disabled") != true) {
                            $popup_confirm.trigger( "click" );
                        }
                    };
                });
            });

            // Buttons actions
            $("#popup_box button.confirm").click(function() {
                // Filename
                user_filename = $("#popup_box").find("input").val();
                filename = user_filename.replace(/@/g, "_-_").replace(/\./g, "_").makeId();
                template = $("#popup_box").find("option:selected").attr("path");
                //alert(filename);

                if(filename == "") {
                    infoPopup(LOCALIZE["page-name-input"]);
                    $("#popup_box").find("input").focus();
                }
                else if(taken.indexOf(filename + ".xml") > -1 || taken.indexOf(filename + ".xml.draft") > -1) {
                    infoPopup(LOCALIZE["name-taken-alert"]);
                    $("#popup_box").find("input").focus();
                }
                else {
                    $form = $("form#cms");
                    new_path = folder + "/" + filename + ".xml";                    
                    //$("form").append("<textarea name='session_temp'><new_page><user_filename>" + user_filename + "</user_filename></new_page></textarea>");
                    link = "index.php?path=" + encodeURIComponent(template) +
                        "&saveas=" + encodeURIComponent(new_path) +
                        "&user_filename=" + encodeURIComponent(user_filename);
                    location.href = link;
                };

            });
            $("#popup_box button.cancel").click(function() { hidePopup(); });
        }
        else {
            alert("createNewPage() data error!")
        }
    };

    // ==========================================
    //               Save / Cancel
    // ==========================================
    
    function deleteEmptyUploads() {
    // ----------------------------
    // Remove unused upload inputs due to max 24 input limits may be used simultaneously
    // ----------------------------
    // RETURNS: <true> if done / <false> if too many uploads detected & show Upload popup
    // ----------------------------
        var $empty_fields = $();
        var $upload_fields = $();
        $("input.upload_file").each(function() {
            if($(this).val()) {
                $upload_fields = $upload_fields.add($(this));
            }
            else {
                $empty_fields = $empty_fields.add($(this));
            }
        });
        //alert("upload: "+ $upload_fields.length + "\nempty: " + $empty_fields.length);
        if($upload_fields.length <= 24) {
            $empty_fields.remove();
            if($upload_fields.length) { $("#upload_info").fadeIn(500); }
            $upload_fields.prop('disabled', false); // Unblock blocked by UploadCancelFix()
            return true;
        }
        else {
            alert(LOCALIZE["upload-inputs-limit-html"])
            return false;
        };
    };
    
    function requireTitle(tags) {
    // ----------------------------
    // tags = <array> or <string> section(s) reuired title tag in Header
    // ----------------------------
    // CHECK: if title was filled by user and disable publish/save if wasn't
    // ----------------------------
        var $title = $();
        titles = [ "title", "name" ];
        if(typeof tags == "string") { titles = titles.concat(tags.split(",")); }
        else if(typeof tags == "object") { titles = titles.concat(tags); };
        // Find title
        for(i in titles) {
            tag = titles[i];
            $section = $("main article.header section." + tag);
            if(!$title.length && $section.length) {
                $title = $section.find("input." + lang);
            }
        };
        // Check title content
        if($title.length) {
            title = $title.val();
            if(typeof title != "string" || title.trim() == "") {
                infoPopup(LOCALIZE['title-required']);
                $title.focus();
                return false;
            }
            else {
                return true;
            }
        }
        // No title found
        else {
            return true;
        }
    };
    
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
            showPopup("fi-refresh", LOCALIZE["cancel-changes-alert"], LOCALIZE["yes-no-buttons"]);
            $("#popup_box .confirm").click(function() {
                if(edit_path.path("extension") == "template") {
                    location.href = "index.php";
                }
                else {
                    location.href = "index.php?" + get;
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
            infoPopup(LOCALIZE['no-changes-done']);
            $(this).blur();
            return false;
        };
	});

    $("header button.publish").click(function () {
        if(!requireTitle()) {
            $(this).blur();
            return false;
        }
        else if(detectChanges() || edit_path.path("extension") == "draft") {
            updateOutput();
            deleteEmptyUploads();
            return true;
        }
        else {
            infoPopup(LOCALIZE['nothing-to-publish']);
            $(this).blur();
            return false;
        };
    });
    
    $("header button.save").click(function () {
        if(!requireTitle()) {
            $(this).blur();
            return false;
        }
        else if(detectChanges()) {
            updateOutput();
            deleteEmptyUploads();
            return true;
        }
        else {
            infoPopup(LOCALIZE['no-changes-done']);
            $(this).blur();
            return false;
        };
    });
    
    $("header button.discard").click(function () {
        if($("input#published_version").val() != "true") {
            infoPopup(LOCALIZE["draft-only-info"]);
            $(this).blur();
            return false;
        }
        else if(edit_path.path("extension") != "draft") {
            infoPopup(LOCALIZE["nothing-to-discard"]);
            $(this).blur();
            return false;
        }
        else {
            $form = $(this).closest("form");
            updateOutput();
            showPopup("fi-trash", LOCALIZE["discard-changes-confirm"], LOCALIZE["yes-no-buttons"]);
            $("#popup_box .confirm").click(function() {
                name = $button.attr("name");
                val = $button.val();
                $form.prepend("<input name='" + name + "' value='" + val + "'>").submit();
                // Please wait info
                $("#upload_info p").text(LOCALIZE["please-wait"]);
                $("#upload_info").fadeIn(500);
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
            showPopup("fi-prohibited", LOCALIZE["unpublish-changes-confirm"], LOCALIZE["yes-no-buttons"]);
            $("#popup_box .confirm").click(function() {
                name = $button.attr("name");
                val = $button.val();
                $form.prepend("<input name='" + name + "' value='" + val + "'>").submit();
                // Please wait info
                $("#upload_info p").text(LOCALIZE["please-wait"]);
                $("#upload_info").fadeIn(500);
            });        
            $("#popup_box .cancel").click(function() {
                hidePopup();
                $(this).unbind();
            });
            return false;
        }
        else {
            infoPopup(LOCALIZE["nothing-to-unpublish"]);
            $(this).blur();
            return false;
        };
    });
    
    $("header button.revert").click(function () {
        if($("input#previous_version").val() == "true") {
            $form = $(this).closest("form");
            updateOutput();
            showPopup("fi-rewind", LOCALIZE["revert-to-previous-confirm"], LOCALIZE["yes-no-buttons"]);
            $("#popup_box .confirm").click(function() {
                name = $button.attr("name");
                val = $button.val();
                $form.prepend("<input name='" + name + "' value='" + val + "'>").submit();
                // Please wait info
                $("#upload_info p").text(LOCALIZE["please-wait"]);
                $("#upload_info").fadeIn(500);
            });
            $("#popup_box .cancel").click(function() {
                hidePopup();
                $(this).unbind();
            });
        }
        else {
            infoPopup(LOCALIZE["nothing-to-revert"]);
            $(this).blur().css("outline", "none");
        }
        return false;
    });
    
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
    
    $("#menu_user .user_logout").click(function() { hrefAction("_logout.php?info=" + encodeURI(LOCALIZE["logout-info"])); });
    $("#menu_user .user_password").click(function() { hrefAction("password.php?page=" + save_path); });
    $("#menu_notifications .old_backup").click(function() { hrefAction("backup.php?page=" + save_path); });
    $("#menu_notifications .new_edit").click(function() { hrefAction("index.php?path=" + $(this).attr("path")); });

    function hrefAction(href) {
        if(detectChanges()) {
            showPopup("fi-alert", LOCALIZE["unsaved-changes-alert"], LOCALIZE["yes-no-buttons"]);
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
            showPopup("fi-alert", LOCALIZE["unsaved-changes-alert"], LOCALIZE["yes-no-buttons"]);
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
        $("#page_fader_trans").fadeOut(ANIMATION_TIME, function() { $(this).remove() });
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
        
        // Navigation & order titles
        $("span.lang_title").each(function() {
            item_lang = $(this).attr("class").split(" ").pop();
            if(lang == item_lang) { $(this).show(); } else { $(this).hide(); };
        })
        
        // Update output -> back from publish
        $("input#language_set").val(lang);
        
        urlQuery("+lang=" + lang);
        
        // Update HTML lang data
        $("input#current_edit_lang").val(lang);
        
        //hideShowLangMenu();
        initializeDectectChanges();
    };
    changeLanguage();

    // Initialize language menu (if more than one language exists)
    if(languages.length > 1) {
        $("#lang ul").mouseenter(function() {
            $("#lang li").stop().show(200);
            $("body").append("<div id='page_fader_trans'></div>");
            $("#page_fader_trans").fadeIn(ANIMATION_TIME);
            // Mobile hide by click
            $("#page_fader_trans").click(function() {
                hideShowLangMenu(200);
            });
        })
        $("#lang ul").mouseleave(function() {
            hideShowLangMenu(200);
            
        });

        $("#lang li").click(function() {
            lang = $(this).attr("value");
            changeLanguage(200);
            //setTimeout(function() { hideShowLangMenu(200); }, 300);
            //urlQuery("lang=" + lang);
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
				if(help != undefined && help.trim() != "") {
                    // Maual type help class
                    if($button.attr("class").split(" ").indexOf("manual") > -1) {
                        $("#help_popup").addClass("black_bubble").removeClass("white_bubble");
                    }
                    // Standard type help class
                    else {
                        $("#help_popup").removeClass("black_bubble").addClass("white_bubble");
                    };
					// ====== Set popup position & direction ======
                    xy = $button.offset();
					win_top = $("body").scrollTop();
					win_bot = win_top + $(window).height();
                    // Fixed buttons div position
                    if($button.closest("div").length && $button.closest("div").css("position") == "fixed") {
                        margin_top = parseInt($button.css("margin-top")) + parseInt($button.closest("div").css("margin-top")) + parseInt($button.closest("div").css("padding-top")) + parseInt($button.closest("div").css("top"));
                        if(margin_top > ($(window).height() / 2)) { dir = "up"; } else { dir = "down"; };
                    }
                    // Standard position
                    else {
					   if(parseInt( xy.top ) - win_top > (win_bot - parseInt( xy.top )) * 3) { dir = "up"; } else { dir = "down"; }; // 3/1
                    };
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
    // icons = <string>
    // text = <string>
    // buttons = <string> BUTTONS labels divided with comas
    // input = optional: <boolean> show INPUT; or <string> input placeholder content
    // --------------------------------
    // Adds & show page fader & popup box
    // --------------------------------
        $("#page_fader, #popup_container").remove();
        html = "<div id='page_fader'></div>";
        html = html + "<div id='popup_container'><div id='popup_box'>";
        html = html + "<h6><span class='" + icon + "'></span></h6>";
        html = html + "<p class='info'>" + text.replace(/\n/g, "<br>") + "</p>";
        if(input != undefined && typeof(input) == "object") {
            html = html + "<select>";
            for(i in input) {
                html = html + "<option>" + input[i] + "</option>";
            }
            html = html + "</select>";
        }
        else if(input != undefined && input != false) {
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
        $("body").append( html ).css({ "overflow-y": "hidden" });
        
        $("#page_fader").stop().fadeIn(500);
        $("#popup_container").fadeIn(250, function() {
            // Focus on firt input
            if( $("#popup_container input").length ) { $("#popup_container input").first().focus(); };
            // Enter for confirm
            $("#popup_box input").on('keypress', function (e) {
                if(e.which == 13) {
                    $(this).closest("#popup_box").find("button.confirm").trigger( "click" );
                };
            });
            // ESC key
            $(document).unbind("keyup").keyup(function(e) { if (e.keyCode === 27) { hidePopup(); }; });
        });
    };
    
    function hidePopup() {
    // --------------------------------
    // Hide & remove page fader & popup box
    // --------------------------------
        $("#page_fader, #popup_container").fadeOut(250, function() { $(this).remove(); });
        $("body").css({ "overflow-y": "scroll" });
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
        html = html + "<p class='info'>" + text.replace(/\n/g, "<br>") + "</p>";
        html = html + "</div>"; // close popup box & container
        $("body").append( html );
        //if(color != false) { $("#info_box h6").css({ "background-color": color }); };
        if(color != false) { $("#info_box h6").addClass(color); };
        $("#info_box").fadeIn(250).delay(2000).fadeOut(1500, function() {$(this).remove(); });
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
				"<li class='move_up'>" + LOCALIZE["move-row-up"] + "</li>" +
				"<li class='move_down'>" + LOCALIZE["move-row-down"] + "</li>" +
				"<li class='new_above'>" + LOCALIZE["add-row-above"] + "</li>" +
				"<li class='new_below'>" + LOCALIZE["add-row-below"] + "</li>" +
				"<li class='delete_row'>" + LOCALIZE["delete-row"] + "</li>" +
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
					infoPopup(LOCALIZE["only-row-alert"]);
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
						infoPopup(LOCALIZE["cant-go-further"]);
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
						infoPopup(LOCALIZE["cant-go-further"]);
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
				"<li class='move_left'>" + LOCALIZE["move-column-left"] + "</li>" +
				"<li class='move_right'>" + LOCALIZE["move-column-right"] + "</li>" +
				"<li class='new_left'>" + LOCALIZE["add-column-left"] + "</li>" +
				"<li class='new_right'>" + LOCALIZE["add-column-right"] + "</li>" +
				"<li class='delete_column'>" + LOCALIZE["delete-column"] + "</li>" +
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
					infoPopup(LOCALIZE["only-column-alert"]);
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
                        infoPopup(LOCALIZE["cant-go-further"]);
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
                        infoPopup(LOCALIZE["cant-go-further"]);
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
        $("#style_buttons p").attr("help", LOCALIZE["bbcode-style-html"]);
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
                "b": "<span class='fi-bold help style_bold' help='" + LOCALIZE["style-bold"] + "'></span>",
                "i": "<span class='fi-italic help style_italic' help='" + LOCALIZE["style-italic"] + "'></span>",
                "u": "<span class='fi-underline help style_underline' help='" + LOCALIZE["style-underline"] + "'></span>",
                "^": "<span class='help index style_superscript' help='" + LOCALIZE["style-superscript"] + "'>x<sup>2</sup></span>",
                "v": "<span class='help index style_subscript' help='" + LOCALIZE["style-subscript"] + "'>x<sub>2</sub></span>",
                "-": "<span class='fi-list-bullet help style_list' help='" + LOCALIZE["style-list"] + "'></span>",
                "c": "<span class='fi-align-center help style_center' help='" + LOCALIZE["style-center"] + "'></span>",
                ".": "<span class='help style_bullet' help='" + LOCALIZE["style-bullet"] + "'>&bull;</span>",
                "_": "<span class='fi-minus help style_line' help='" + LOCALIZE["style-line"] + "'></span>",
				"m": "<span class='fi-mail help style_mail' help='" + LOCALIZE["style-mail"] + "'></span>",
                "l": "<span class='fi-link help style_link' help='" + LOCALIZE["style-link"] + "'></span>",
                "a": "<span class='fi-page-filled help style_attachment' help='" + LOCALIZE["style-library-attachment"] + "'></span>",
                "f": "<span class='fi-photo help style_figure' help='" + LOCALIZE["style-library-figure"] + "'></span>",
                "p": "<span class='fi-layout help style_page' help='" + LOCALIZE["style-library-page"] + "'></span>",
            };

            for(key in buttons) {
                // Check for fisabled or not valid first
                if(disabled.indexOf(key) < 0 && (valid == false || valid.indexOf(key) > -1)) {
                    if(format.indexOf("*") >= 0 || format.indexOf(key) >= 0) { html = html + buttons[key]; };
                };
            };

            html = html + "<span class='fi-prohibited help style_delete' help=" + LOCALIZE["style-delete"] + "'></span>" // Delete BBCode
            html = html + "<span class='help style_preview' help='" + LOCALIZE["style-preview"] + "'>" + LOCALIZE["style-preview-label"] + "</span>" // Preview
            return "<div id='style_buttons'>" + html + "<p class='fi-info manual help' help='-'></p></div>"; // Show manual
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
            //alert(text);
            $("body").append( "<div id='page_fader'></div><div id='preview_popup'>" + LOCALIZE["style-preview-label"] + "</div>" );
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
                    infoPopup(LOCALIZE["file-not-selected"]);
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
            infoPopup(LOCALIZE["text-not-selected"]);
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
            if(style == "link") {
                tag = "url"; title = LOCALIZE["style-link"] + ":";
                icon = "fi-link";
                info = LOCALIZE['link-input'];
            }
            else if(style == "site") {
                tag = "url"; title = LOCALIZE["style-link-internal"] + ":";
                icon = "fi-arrow-right";
                info = LOCALIZE['link-input'];
            }
            else if(style == "mail") {
                tag = "email"; title = LOCALIZE["style-mail"] + ":";
                icon = "fi-mail";
                info = LOCALIZE['mail-input'];
            }
            else { title = "?"; };
            showPopup(icon, title,  LOCALIZE["save-cancel-buttons"], info);
            $("textarea").unbind("blur");
            // Button - confirm
            $("#popup_box .confirm").unbind("click").click(function() {
                input = $("#popup_box input").val();
                if(input == "") {
                    infoPopup(LOCALIZE["empty-input-alert"]);
                    $("#popup_box input").focus();
                }
                //else if((style == "mail" && (input = validateEmail(input)) == false) || (style == "link" && (input = validateUrl(input)) == false)){
                else if(style == "mail" && (input = validateEmail(input)) == false) {
                    infoPopup(LOCALIZE["valid-address-alert"]);
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
                "<input id='href' type='text' class='text' placeholder='" + LOCALIZE['link-input'] + "'>\n" +
                "<label><input id='new_window' type='checkbox' class='checkbox' checked> " + LOCALIZE['new-window'] + "</label>\n";
            // Add text side options for photo with text banner
            if(selected != undefined && selected != "") {
                title = LOCALIZE['image-description-label'];
                inputs = inputs +
                    "<p class='label'>" + LOCALIZE['image-description-position'] + "</p>\n" +
                    "<label><input type='radio' class='side radio' name='side' value='left'> " + LOCALIZE['position-left'] + "</label>" +
                    "<label><input type='radio' class='side radio' name='side' value='right' checked> " + LOCALIZE['position-right'] + "</label>";
            }
			else {
				title = LOCALIZE['image-label'];
                inputs = inputs +
                    "<p class='label'>" + LOCALIZE['image-size'] + "</p>\n" +
                    "<label><input type='radio' class='size radio' name='size' value='1'> " + LOCALIZE['size-small'] + "</label>" +
                    "<label><input type='radio' class='size radio' name='size' value='2' checked> " + LOCALIZE['size-medium'] + "</label>" +
					"<label><input type='radio' class='size radio' name='size' value='3'> " + LOCALIZE['size-big'] + "</label>";
			};
			if(multi) { label = LOCALIZE['select-images']; } else { label = LOCALIZE['select-image']; };
        }
        // Page content
        else if(lib.indexOf("temp") > -1) {
            title = LOCALIZE['subpage-label'];
            icon = "fi-layout";
            inputs = "";
			if(multi) { label = LOCALIZE['select-pages']; } else { label = LOCALIZE['select-page']; };
        }
        // File content
        else {
            title = LOCALIZE['file-label'];
            icon = "fi-page-filled";
            inputs = "";
			if(multi) { label = LOCALIZE['select-files']; } else { label = LOCALIZE['select-file']; };
        };
		
        // HTML
        html = "<div id='page_fader'></div>\n";
        html = html + "<div id='libraries'><div class='scrollable'><div class='popup_box'>\n";
        html = html + "<h6><span class='" + icon + "'></span></h6>\n";
        html = html + "<h3>" + title + "</h3>\n";
        html = html + "<p class='label'>" + label + "</p>\n";
        html = html + $lib.get(0).outerHTML;
        html = html + inputs;
        html = html + "<div class='buttons two'><button class='confirm'>" + LOCALIZE['save-label'] + "</button><button class='cancel'>" + LOCALIZE['cancel-label'] + "</button></div>\n";
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
        
        text = text.replace( / +/g, " "); // multiple spaces
        text = text.replace( / (.?) /g, " $1&nbsp;"); // widow letters

        // List
        if(lists = text.match(/\[list\](.*?)\[\/list\]/ig)) {
            for(i in lists) {
                list = lists[i];
				list = list.replace(/\[list\](.*?)\[\/list\]/ig, "$1");
                list = "</p><ul class='preview'><li>" + list.replace( /<br>/ig, "</li><li>" ) + "</li></ul><p>";
				list = list.replace(/<li>-/ig, "<li style='list-style:none'>-"); // sublist point
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
        //return translate( tag.replace(/_/g, " ").toUpperCase() );
        return translate(tag).replace(/_/g, " ").toUpperCase();
    };

    function setButtons() {
	// --------------------------------
	// enable/disable post move up & down buttons + add post numbering
	// --------------------------------
        // ====== XML ======
        // Get all signle & multi article type
		single_tags = [];
		multi_tags = [];
        
        //$("article div.buttons .up, article div.buttons .down").removeClass("disabled"); // Refresh disabled
        
        $("article").each(function() {
            tag = $(this).attr("class");
            if(tag.substr(0, 6) == "multi_") {
                if(multi_tags.indexOf(tag) < 0) { multi_tags[multi_tags.length] = tag; };
            }
            else {
                if(single_tags.indexOf(tag) < 0) { single_tags[single_tags.length] = tag; };
            };
        });
        if(edit_path.path("extension") != "order") {
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
        };
        
        // ====== ORDER ======

        //alert($("article._order").length);
        /*
        $("article._order h3").each(function() {
                label = $(this).attr("class");
                //$(this).html("<span class='fi-list'></span>" + label);
        });
        */
        if($("article._order").length == 1) {
            $("article._order").children("div.buttons").find(".up, .down, .delete").prop("disabled", true).addClass("disabled"); // disable: up, down, delete
        }
        else {
            $("article._order").first().children("div.buttons").find(".up").prop("disabled", true).addClass("disabled"); // first - disable: up
            $("article._order").first().next().children("div.buttons").find(".up").prop("disabled", false).removeClass("disabled"); // second - enable: up
            $("article._order").last().children("div.buttons").find(".down").prop("disabled", true).addClass("disabled"); // last - disable: down
            $("article._order").last().prev().children("div.buttons").find(".down").prop("disabled", false).removeClass("disabled"); // pre-last - enable: down
            //alert($("article._order").last().prev().children("div.buttons").html());
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
                        $($uploadButton).prop('disabled', true); // Prevent delete input content by next click & cancel. Must be unblocked -> deleteEmptyUploads()
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
            showPopup("fi-link", LOCALIZE["video-label"], LOCALIZE["save-cancel-buttons"], LOCALIZE["video-input"]);
            // Confirm
            $("#popup_box .confirm").unbind("click").click(function() {
                input = $("#popup_box input").val();
				
				if(iframe = input.match(/\<iframe(.*?)src=\"(.*?)\"/)) {
					//alert(iframe[2]);
					input = iframe[2];
				};
				input = input.replace(/autoplay=true/, "autoplay=false");
                
                if(input == "") {
                    infoPopup(LOCALIZE["empty-input-alert"]);
                    $("#popup_box input").focus();
                }
                else if(input.split(" ").length > 1 || input.split(".").length < 2){
                    infoPopup(LOCALIZE["valid-address-alert"]);
                    $("#popup_box input").focus();
                }
                else {
                    if(input.substr(0 , 7) != "http://" && input.substr(0 , 8) != "https://") {
                        input = "http://" + input;
                    };
                    
                    // ====== YouTube ======
                    if(input.indexOf("youtube.com") > -1) {
                        input = input.split("&").shift();
                        input = input.replace("youtube.com/watch?v=", "youtube.com/embed/");
                    }
                    else if(input.indexOf("youtu.be") > -1) {
                        input = input.split("?").shift();
                        input = input.replace("youtu.be", "youtube.com/embed/");
                    };
                    
                    // ====== Facebook ======
                    if(input.indexOf("facebook.com") > -1) {
                        if(input.indexOf("/videos/") == -1) { input = decodeURIComponent(input); }
                        id = input.split("/videos/").pop().split("/").shift();
                        input = "http://www.facebook.com/video/embed?video_id=" + id;
                    };
                    
                    // ====== vimeo ======
                    if(input.indexOf("vimeo.com") > -1) {
                        id = input.split("/").pop().split("?").shift();
                        input = "https://player.vimeo.com/video/" + id;
                    }
                    
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
    
    function MediaPreviewUpdate($slide, slide_num) {
        size = $slide.attr("size").split("x");

        // Update image
        src = $slide.css("background-image").match(/"(.*?)"/).pop();
        $("#zoom img.image").attr("src", src);
        // Update text
        path = $slide.attr("path");
        filesize = $slide.attr("filesize");
        
        $("#zoom .slide_num").html(slide_num);
        $("#zoom .filename").text(path.path("basename"));
        $("#zoom .dirname").text(path.path("dirname") + "/");
        $("#zoom .size").text( size.join(" x ") + " px" );
        $("#zoom .filesize").text( filesize + " kB" );
        $("#zoom .download").attr( "href", site_root + "/" + path );

    }
    
    function initializeMediaPreview() {
	// --------------------------------
	// Initialize zoom or download button on media items
	// --------------------------------

        // ====== Image(s) Zoom ======
        $("article div.media .thumb .zoom").unbind("click").click(function() {
            $slide = $(this).closest(".thumb");
            $gallery = $(this).closest("div.gallery");

            html = "<div id='page_fader'>\n" +
                "\t<div id='zoom'>\n" +
                
                    "\t\t<div class='image_box'>\n" +
                        "\t\t\t\t<img class='image' src=''>\n" +
                        "\t\t\t\t<button class='toggle_slide prev'><span class='fi-arrow-left'></span></button>\n" +
                        "\t\t\t\t<button class='toggle_slide next'><span class='fi-arrow-right'></span></button>\n" +
                    "\t\t</div>\n" +
                    
                    "\t\t<div class='text_box'>\n" +
                        "\t\t\t<p class='title'><span class='slide_num'>1 / n</span></p>\n" +
                        "\t\t\t<p class='filename'>filename.ext</p>\n" +
                        "\t\t\t<p><i class='fi-folder'></i><span class='dirname'>dirname/folder/</span></p>\n" +
                        "\t\t\t<p><i class='fi-photo'></i><span class='size'>100 x 100 px</span></p>\n" +
                        "\t\t\t<p><i class='fi-info'></i><span class='filesize'>1.00 Mb</span></p>\n" +
                        "\t\t\t<p><a class='download button' href='' download>" + LOCALIZE["download-label"] + "</a></p>\n" +
                        "\t\t\t<button class='close'><span class='fi-x'></span></button>\n" +
                    "\t\t</div>\n" +
                
                "\t</div>\n" +
                "</div>\n";

            $("body").append(html).css({ "overflow-y": "hidden" });
            $("#page_fader").fadeIn(250);

            // Gallery slides
            if($gallery.length) {
                // Check for current slide number
                $prev = $slide;
                slide_num = 1;
                while($prev.prev(".thumb").length) {
                    $prev = $prev.prev(".thumb");
                    slide_num++;
                }
                slides_total = $gallery.find(".thumb").length;
                MediaPreviewUpdate($slide, slide_num + " / " + slides_total);
                
                // ====== Change slide ======
                $("#page_fader .toggle_slide").click(function() {
                    $(this).blur();
                    dir = $(this).attr("class").split(" ").pop();
                    if(dir == "prev") {
                        $next = $slide.prev("div.thumb");
                        slide_num--;
                        if(!$next.length) {
                            $next = $gallery.find("div.thumb").last();
                            slide_num = slides_total;
                        };
                        dir = 110;
                    }
                    else {
                        $next = $slide.next("div.thumb");
                        slide_num++;
                        if(!$next.length) {
                            $next = $gallery.find("div.thumb").first();
                            slide_num = 1;
                        };
                        dir = -110;
                    }
                    $("#zoom img.image").animate({ "left": dir + "%" }, 250, function() {
                        $image = $(this);
                        $image.attr("src", ""); // Prevent showing previous image before load the new one
                        MediaPreviewUpdate($next, slide_num + " / " + slides_total);
                        $slide = $next;
                        setTimeout(function() {
                            $image.css({ "left": (dir * -1) + "%" }).animate({ "left": "0" }, 250);
                        }, 100);
                    });
                });
            }
            // Image - single slide
            else {
                MediaPreviewUpdate($slide, "&nbsp");
                $("#zoom .toggle_slide").remove(); // Hide change slide buttons
            }
            
            // Close zoom button by X button
            $("#page_fader .close").click(function() {
                $("#page_fader").fadeOut(250, function() { $(this).remove(); });
                $("body").css({ "overflow-y": "scroll" });
                $("body").unbind("keyup");
            });
            // Close & navigate by key
            $("body").keyup(function(e) {
                code = e.keyCode;
                if(code == 27) { // ESC
                    $("#page_fader").fadeOut(250, function() { $(this).remove(); });
                    $("body").css({ "overflow-y": "scroll" });
                    $("body").unbind("keyup");
                }
                else if(code == 37) { // <-
                    $("#zoom .image_box button.prev").click();
                }
                else if(code == 39) { // ->
                    $("#zoom .image_box button.next").click();
                }
            });
            // Blur focus outline
            $(this).blur();
            return false;
        });
        
        // ====== File Download ======
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
    
    function uploadLimitsTest() {
        var num = 0;
        $("input.upload_file").each(function() {
            val = $(this).val();
            if(val) { num++ };
        })
        if(num >= 24) {
            showPopup("fi-alert", LOCALIZE["upload-inputs-limit-html"], LOCALIZE["ok-label"], false);
            $("#popup_box .confirm").click(function() { hidePopup(); });
        }
    };
    
    function initializeUpload() {
	// --------------------------------
	// Initialize upload button action
	// --------------------------------
        initializeLinkUpdate();
        UploadCancelFix();
        $("input.upload_file").unbind("change").on("change", function() {
            
            uploadLimitsTest();
            
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

			for(i in filenames) {
				if(filenames[i].path("extension").indexOf("tif") > -1) {
					showPopup("fi-alert", LOCALIZE["tiff-alert-html"], LOCALIZE["ok-label"], false);
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
                $img.hide(500, function() { $(this).remove(); });
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
            
            // ====== Add new article ======
            // Below
            //if(current_path.path("filename") == "navigation") { // if Nav -> add new article on the end
            if(site_new_below.indexOf(tag) > -1) {
                $(this).closest("article").after("<article class='" + tag + "' style='display:none'>" + html + "</article>");
                $new_post = $(this).closest("article").next();
            }
            // Above
            else {
                $(this).closest("article").before("<article class='" + tag + "' style='display:none'>" + html + "</article>");
                $new_post = $(this).closest("article").prev();
            }

            // Clear edit fields
            $new_post.children("section").each(function() {
                $(this).children("div.text").children("input, textarea").val(""); // Clear all text fields
                $(this).children("input.string, input.code, textarea.code, input.date").val(""); // Clear all string & code fields
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
                $(this).find("div.file label p").text(LOCALIZE["add-file"]);
                $(this).find("div.image label p").text(LOCALIZE["add-image"]);
                $(this).find("div.gallery label p").text(LOCALIZE["add-images"]);
                $(this).find("div.files label p").text(LOCALIZE["add-files"]);
                $(this).find("div.upload input.upload_file").prop("disabled", false); // Unblock label(s) disabled by cancelFix

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
            // unique ID
            if($new_post.find("._id").length) {
                $id = $new_post.find("._id input.string");
                date = currentTime("yyyymmdd");
                var id_max = 0;
                $("section._id").each(function() {
                    val = $(this).find("input.string").val().split("-");
                    if(val.length == 2 && val[0] == date) {
                        if(val[1] > id_max) {
                            id_max = val[1];
                        };
                    };
                })
                $id.val(date + "-" + ++id_max);
            };
            // auto DATE
            $new_post.find("._date input.string").val(currentTime("yyyy-mm-dd"));
            
            // Hide help popup
            $("#help_popup").remove();
            // Display new post
            $new_post.show(500, function() {
                $(this).blur();
                // reinitialize active elements on new DOM content
                reinitializePostActiveElements();
                // Scroll to new post
                setTimeout(function() { $('html, body').animate({ scrollTop: $new_post.offset().top }, 500); }, 250);
                //Auto focus on first input
                $new_post.find("input.string, input." + lang + ", textarea." + lang).first().focus();
            });

            if(save_path.path("basename") == "navigation.xml") {
                infoPopup(LOCALIZE["nav-item-add-info"]);
            }
            
            return false;
        });
    };
    initializeNewPost();
    
    function reinitializePostActiveElements() {
        initializeDectectChanges();
        initializeUpload();
        mediaHideShow();
        singleSetHide();
        setButtons();
        initiateButtons();
        initializeShowStyle();
        initializeHelp();
        initializeNewPost();
        initializeSectionFold();
        transparentBackground();
        initializeDisabled();
        initializeTable();
        initializeActionButtons();
        initializeDateInput();
        initializeBrowseInput();
        initializeSet();
        
        $("form article section.string").each(function() { updateString($(this), false); });
    }

    // ==========================================
    //            Move or delete post
    // ==========================================

	function initiateButtons() {
	// --------------------------------
	// Initiate post buttons actions
	// --------------------------------
        $("article .buttons").find(".delete, .disabled, .down, .up").unbind("click");

		// ====== Delete ======
		$("article .buttons button.delete").click(function() {
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
            
            // Navigation item remove info
            if(save_path.path("basename") == "navigation.xml") {
                infoPopup(LOCALIZE["nav-item-remove-info"]);
            };
            
			$item.hide(500, function() {
				$(this).remove();
				setButtons();
			});
            
            formResize(550);
			return false; 
		});
        // ====== Move up ======
        $("article .buttons button.up").click(function() {
            $item = $(this).parent().parent();
            $item.hide(250, function() {
                $swapWith = $item.prev();
                $item.after($swapWith.detach());
                $item.show(250, function() {
                    setButtons();
                    initiateButtons();
                });
            });
            $(this).blur();
            return false;
        });
		
        // ====== Move Down ======
        $("article .buttons button.down").click(function() {
            $item = $(this).parent().parent();
            $item.hide(250, function() {
                $swapWith = $item.next();
                $item.before($swapWith.detach());
                $item.show(250, function() {
                    setButtons();
                    initiateButtons();
                });
            });
            $(this).blur();
            return false;
        });
		
        // ====== Disabled ======
        $("article .buttons button.disabled").click(function() {
            alert("dis")
            $(this).blur();
            return false;
        })
        
	};
	initiateButtons();
    
    // ==========================================
    //                Main Title
    // ==========================================

    // Set main title by navigation label
    if($("nav dd div.current").length && edit_path.path("extension") != "template") {
        title = $("nav dd div.current").first().find(".item_label p").html();
        $("h2").html( title );
        $("h2 i").remove();
    };
    
    // ==========================================
    //           Section fold & expand
    // ==========================================

    function initializeSectionFold() {
        // Buttons
        $(".fold_expand span.fold").unbind("click").click(function() {
            $(this).blur();
            
            $buttons = $(this).parent("div.fold_expand");
            $buttons.find("span.fold").hide();
            $buttons.find("span.expand").show();
            $(this).closest("article").find("section").hide(200);
            formResize(250);
        });
        $(".fold_expand span.expand").unbind("click").click(function() {
            $(this).blur();
            $buttons = $(this).parent("div.fold_expand");
            $buttons.find("span.expand").hide();
            $buttons.find("span.fold").show();
            $(this).closest("article").find("section").show(200);
            //formResize(250);
        });        
    };
    initializeSectionFold();

    function autoSectionFold() {
        $(".fold_expand span.expand").hide();
        // Fold long content
        n = 1;
        fold_big_from = 9;
        fold_all_from = 9;
        big_size = $(window).height() * 0.5;
        $("form article").each(function() {
            n++;
            $article = $(this);
            if((n > fold_big_from && $article.height() > big_size) || (n > fold_all_from)) {
                $article.find(".fold_expand span.expand").show();
                $article.find(".fold_expand span.fold").hide();
                $article.find("section").hide();
            }
        });
    };
    autoSectionFold();
    formResize(750);
    
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
    
    // ====== Preview box ======
    $("#update_output").click(function() {
        $(this).blur();
        
        updateOutput();
        html = $("#output").val();
        html = html.replace(/<\?(.*?)\?>/g, "[?$1?]");
        html = html.replace(/<(.*?)>/g, "<span class='tag'>&lt;$1&gt;</span>");
        html = html.replace(/\[\?(.*?)\?\]/g, "<span class='flag'>&lt;?$1?&gt;</span>");
        html = html.replace(/\n/g, "<br>");
        html = html.replace(/\t/g, "&nbsp;&nbsp;&nbsp;&nbsp;");
        $("#outputs .xml_preview").html(html);
        
        $("#outputs").find("input").each(function() {
            $group = $(this).prev("p.label").add( $(this) );
            if($(this).val() != "") { $group.show(); }
            else { $group.hide(); };
        })
        
        if(detectChanges()) {
            $("#outputs .header .changed").text(" (changed)");
        }
        else {
            $("#outputs .header .changed").text("");
        };
        
		$("#outputs").fadeIn(500);       
        $(document).keyup(function(e) { if (e.keyCode === 27) { hideOutputPreview(); }; });    
        return false;
    });

    function hideOutputPreview() {
        $("#outputs").fadeOut(250);
        $(document).unbind("keyup");
    };
    
    $("#outputs .close").click(function() {  hideOutputPreview(); return false; });

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
                            //alert($(this).html());
                            
                            $(this).children("table").each(function() {
                                
                                language = $(this).attr("class");
                                //if(language == "pl") { alert($(this).get(0).outerHTML); };
                                
                                $(this).find("tr").each(function() {
                                    
                                    row = [];
                                    num = $(this).attr("class");
                                    if(num != "start") {
                                        $(this).find("td").each(function() {
                                            // Strip off all formating except <br>
                                            val = $(this).html().replace(/\&nbsp;/g, " ").replace(/<br>/g, "[br]");
                                            $(this).html(val); // put back modified content
                                            val = $(this).text(); // get pure text
                                            $(this).html(val.replace(/\[br\]/g, "<br>")); // RESTORE <BR>
                                            // XML outupt
                                            val = val.replace(/;/g, "[semi]"); // change semicolons to bbcode
                                            row[row.length] = val;
                                        });
                                        row.shift(); // delete row numbers
                                        xml = xml + "\t\t\t\t<" + language + ">" + row.join(";") + "</" + language + ">\n";
                                    };
                                });
                            });
                            xml = xml + "\t\t\t</table>\n";
                        }
                        // ====== media Set ======
                        else if( att_tag == "set" ) {
                            mode = $(this).find("input").attr("type");
                            set = $(this).find("input").first().val(); // fix nothing selected
                            $(this).find("input").each(function() {
                                if( $(this).prop("checked") == true ) { set = $(this).val(); };
                            });
                        }
                        // ====== Complex data / <i></i> tag will be ignored ======
                        else if( $(this).children().length && $(this).children().prop("tagName") != "I") {
                            xml = xml + "\t\t\t<" + att_tag + ">\n";

                            $(this).children().each(function() {
                                children_tag = $(this).attr("class");
                                children_val = getVal( $(this) );
                                
                                if((children_tag == undefined || children_val == undefined) && $(this).children().length) {
                                    children_tag = $(this).children().attr("class");
                                    children_val = getVal( $(this).children() );
                                };
                                if(att_tag == "text") {
                                    children_val = children_val.replace(/\n/g, "[br]"); // enter
                                    children_val = children_val.replace(/–/g, "-"); // dash -> minus
                                    children_val = children_val.replace(/"/g, "&quot;"); // quotes
                                    children_val = children_val.replace(/</g, "&lt;").replace(/>/g, "&gt;"); // html tags & comments
                                }; // text fix
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
                            att_val = att_val.replace(/</g, "&lt;").replace(/>/g, "&gt;"); // html tags & comments
                            //if(type == "code") { att_val = att_val.replace(/\[/g, "&#91;").replace(/\]/g, "&#93;"); };
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
    
    function sortByTag(tag, dir) {
    // ----------------------------------------------
    // tag = <string> order data element TAG
    // dir = <number> order DIRection: 1 or -1
    // ----------------------------------------------
    // Sort articles by specified data
    // ----------------------------------------------
        if(dir != -1) { dir = 1; }; // order direction
        time = 100;
        delay = 50;
        transition = 200;
        error = 0;
        articles = {};
        $articles = $("#cms.order article._order");
        $articles.slideUp(transition);
        setTimeout(function() {
            $("#cms.order article._order").each(function() {
                $tag = $(this).find(tag);
                if($tag.length) {
                    
                    if($tag.prop("tagName") == "INPUT") { key = $tag.val(); }
                    else { key = $tag.text().makeId(); };
                    
                    if(typeof(key) == "string" && key != "") {
                        articles[key] = $(this);
                    }
                    else {
                        alert("ALERT!\n\"" + tag + "\" tag is empty or missing in: " + $(this).find("h3").attr("path"));
                        error++;
                    }
                }
                else {
                    alert("ERROR!\n\"" + tag + "\" tag not found in: " + $(this).find("h3").attr("path"));
                    error++;
                };
            });
            if(error == 0) {
                sorted_keys = Object.keys(articles).sort();
                if(dir == -1) { sorted_keys.reverse(); };
                for(i in sorted_keys) {
                    $article = articles[ sorted_keys[i] ];
                    $("main").append($article.clone());
                    time = time + delay;
                    $("#cms.order article._order").last().delay(time).slideDown(transition / 2);
                };
                $articles.remove();
                reinitializePostActiveElements();
            }
            else {
                $articles.show();
            }
        }, transition + time)
    };
    
    function getOrderOptions() {
    // ----------------------------------------------
    // RETURN: <array>
    // Get all order options from HTML
    // ----------------------------------------------
        var order_options = [];
        $("#cms.order ._order-options li").each(function() {
            $button = $(this).find("button");
            $input = $(this).find("input");
            if($input.length) {
                typ = $input.attr("type");
                key = $input.attr("class").split(" ").shift();
                if(typ == "checkbox") { val = $input.prop("checked"); }
                else { val = $input.val(); }
            }
            else if($button.length) {
                key = $button.attr("class").split(" ").shift();
                val = true;
            }
            else {
                val = false;
            };
            if(val == true || val == "true") { order_options[order_options.length] = key; };
        });
        return order_options;
    };

    function makeOrder() {
        order = [];
        order[order.length] = "<_order>\n" +
            "\t<title>" + $("#cms.order input._order-title").val() +"</title>\n" +
            "\t<options>" + getOrderOptions().join(";") +"</options>\n" +
            "</_order>";
        $("#cms.order h3").each(function() {
            item_xml = $(this).find(".value").html().replace(/\&lt;/g, "<").replace(/\&gt;/g, ">");
            order[order.length] = "<multi_item>\n" + item_xml + "</multi_item>";
        });
        return order.join("\n");
    };

    // Initialize
    if(getOrderOptions().indexOf("auto") > -1) { $("#cms.order article._order .buttons").hide(); };
    
    $("#cms.order ._order-options li input.auto").change(function() {
        auto = $(this).prop("checked");
        if(auto) {
            sortByTag("h3 span." + admin_lang, 1);
            $("#cms.order article._order .buttons").fadeOut(200);
        }
        else {
            $("#cms.order article._order .buttons").fadeIn(200);
        };
    });
    
    $("#cms.order ._order-options button.date").click(function() {
        $(this).blur();
        sortByTag(".header-date", -1);
        return false;
    })

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
                    row[row.length] = content.replace(/;/g, "[semi]");           // replace ";" with ",", due to csv format limitations
                    $(this).html(content.replace(/\[br\]/gi, "<br>"));      // change BBCode line breaks to HTML format for screen preview
                };
            });
            csv[csv.length] = row.join(";"); // Add
        });
        return csv.join("\n");
    };
    
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
    //             Disabled & Hidden
    // ==========================================
	
	function initializeDisabled() {
		$("form section input.disabled").each(function() {
            $section = $(this).closest("section");
			$section.find("input, label").css({ "opacity": "0.5" }).unbind().focus(function() {
				infoPopup(LOCALIZE["disabled-input-alert"]);
				$(this).blur();
				return false;
			});
            $section.find(".media .delete, .media .sort, .media .upload").hide();
		});
        $("form section .hide_label").each(function() {
            $(this).closest("section").find("p.label, p.description").hide();
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
        
        //alert($("#upload_info").offset().top);
    }, 500);
    
    // ==========================================
    //       Hide loader / Logout on timeout
    // ==========================================

    //var SESSION_START = parseInt( new Date().getTime() / 1000 );
    var LOGOUT_WAIT = 30; // seconds for user reaction
    var LOGOUT_FLAG = false;
    
    if($("input#site_logout_time").length && $("input#site_logout_time").val() != "") {
        var LOGOUT_TIME = parseInt( $("input#site_logout_time").val() ); // [s]
    }
    else {
        var LOGOUT_TIME = 1800; // [s] default 30 minutes
    };
    
    function countDown() {
        setTimeout(function() {
            count = parseInt( $("#countdown").text() );
            if(LOGOUT_FLAG) {
                if(count > 0) {
                    $("#countdown").text(--count);
                    countDown();
                }
                else {
                    location.href = "_logout.php?info=" + encodeURI(LOCALIZE["session-expired"]) + "&back_url=" + encodeURI(location.href);
                }
            }
            else {
                 autoLogout();
            };
        }, 1000)
    };
    
    function autoLogout() {
        setTimeout(function() {
            LOGOUT_FLAG = true;
            info = LOCALIZE["session-timeout-html"];
            info = info.replace("@session_max", Math.round(LOGOUT_TIME / 60));
            info = info.replace("@countdown", LOGOUT_WAIT);
            showPopup("fi-clock", info, LOCALIZE["timeout-reset-button"]);
            
            $("#popup_box .confirm").click(function() {
                LOGOUT_FLAG = false;
                hidePopup();
            });
            countDown();
        }, (LOGOUT_TIME * 1000) );
    };
    
    setTimeout(function() {
        $("#loader").fadeOut(200, function() {
            if(LOGOUT_TIME > 0) { autoLogout(); };
        });
    }, 500);

    //urlQuery("path=" + edit_path);
    urlQuery("-popup");
    //urlQuery("-lang");
    
});