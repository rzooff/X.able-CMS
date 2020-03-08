$(document).ready(function() {

    // ==========================================
    //                 Variables
    // ==========================================
    
    var ANIMATION_TIME = 500;
    var ADMIN_LANG = $("input#admin_lang").val();
    var EDIT_LANG = $("input#edit_lang").val();
    
    function getCurrentLang() {
    // ------------------------------------
    // Get current edit lang
    // ------------------------------------
        var lang = EDIT_LANG;
        $("main #lang ul li").each(function() {
            val = $(this).attr("value");
            if($(this).css("display") != "none" && jQuery.type(val) == "string" && val != "") {
                lang = $(this).attr("value");
            }
        })
        return lang;
    };
    
    // ==========================================
    //          Get categories labels
    // ==========================================
    
    function getCategories(lang) {
        list = {};
        $("#categories_labels").find(lang).each(function() {
            row = $(this).text().split(";");
            key = "key_" + row[0];
            label = row[1];
            list[key] = label;
        })
        return list;

    }
    var CATEGORIES_LIST = getCategories( getCurrentLang() );
    
    $("main #lang ul li").click(function() {
        lang = $(this).attr("value");
        setTimeout(function() {
            CATEGORIES_LIST = getCategories(lang);
            updateCategoriesButtons();
        }, ANIMATION_TIME / 2);
    })
    
    // ==========================================
    //              Localization
    // ==========================================

    // Add to localization array
    if(ADMIN_LANG == "pl") {
        LOCALIZE["categories-label"] = "Kategorie";
    }
    else if(ADMIN_LANG == "en") {
        LOCALIZE["categories-label"] = "Categories";
    }
    
    // ==========================================
    // ==========================================
    // ==========================================
    //         Categories gallery page
    // ==========================================
    // ==========================================
    // ==========================================

    function initializeCategoriesList($table) {
    // ------------------------------------
    // $table = <object> Categories table
    // ------------------------------------
    // (Re)associate actions to edit buttons
    // ------------------------------------
        updateLanguages($table);
        
        $table.find("table tr.start .num_B").text(LOCALIZE["style-list"]);
        // Add button
        $button = $table.find("button.add_table_row");
        $button.click(function() {
            $table = $(this).closest("div.table");
            setTimeout(function() { updateCategoriesKeys($table); }, 100);
            setTimeout(function() { initializeCategoriesList($table); }, ANIMATION_TIME + 100);
        });
        // Add remove from context menu
        $context_button = $table.find(".edit_row");
        $context_button.bind("contextmenu", function() {
            $table = $(this).closest("div.table");
            setTimeout(function() {
                $("#table_edit li").click(function() {
                    setTimeout(function() { updateCategoriesKeys($table); }, 100);
                    setTimeout(function() { initializeCategoriesList($table); }, ANIMATION_TIME + 100);
                })
            }, ANIMATION_TIME);
        });
    };

    function updateCategoriesKeys($table) {
    // ------------------------------------
    // $table = <object> Categories table
    // ------------------------------------
    // Update key column values
    // ------------------------------------
        var max_key = CATEGORIES_KEY_MAX; // Get initial max
        var lang = getCurrentLang();
        var $new_row = $();
        
        $table.find("table." + lang + " tr").each(function() {
            key = $(this).find("td.num_A").text();
            // New category row -> add to selection
            if(key.trim() == "") {
                $new_row = $new_row.add($(this));
            }
            // Category row -> check for max
            else if(!isNaN(parseInt(key))) {
                key = parseInt( key );
                if(key >= max_key) { max_key = key + 1; };
            }
        })
        // Update new rows
        if($new_row.length) {
            $new_row.each(function() {
                $(this).find("td.num_A").text(max_key++);
            })
        }
        // Write down current max
        $max.val(max_key - 1);
    };
    
    function updateLanguages($table) {
    // ------------------------------------
    // $table = <object> Categories table
    // ------------------------------------
    // Update other language categories based on current
    // ------------------------------------
        var lang = getCurrentLang();
        // Get current language content
        var current_items = {};
        $current_table = $table.find("table." + lang);
        $current_table.find("tr").each(function() {
            key = $(this).find(".num_A").text();
            if(key != "" && !isNaN(parseInt(key))) {
                current_items["key_" + key] = $(this).clone();
            }
        })

        // Find other languages tables
        $table.find("table").each(function() {
            table_lang = $(this).attr("class");
            if($(this).attr("class").indexOf(lang) < 0) {
                $lang_table = $(this);
                // Get other language values & remove objects
                labels = {};
                $(this).find("tr").each(function() {
                    key = $(this).find(".num_A").text();
                    if(key != "" && !isNaN(parseInt(key))) {
                        label = $(this).find(".num_B").text();
                        labels["key_" + key] = label;
                        $(this).remove();
                    }
                });
                // Restore content based on current lang.
                for(key in current_items) {
                    $lang_table.append(current_items[key].clone());
                    if(labels[key]) {
                        text = labels[key];
                    }
                    else {
                        text = "&nbsp;"; // New item, empty
                    }
                    $lang_table.find("tr").last().find(".num_B").text( text );
                }
            }
        })
    };  
    
    // ==========================================
    // ==========================================
    // ==========================================
    //         Categories List (gallery)
    // ==========================================
    // ==========================================
    // ==========================================
    
    var $categories = $("article ._categories_list");
    
    function categoriesTableInitialize($table) {
        $max = $table.closest("article").find("._categories_max input.string");
        $max.val(0);

        $table.find("table").each(function() {
            $(this).find("tr").each(function() {
                $tr = $(this);
                var max = 0;
                if($tr.find("td").length < 3) {
                    $tr.append( $tr.find("td").last().clone() );
                    
                    class_attr = $tr.find("td").last().attr("class").replace("num_A", "num_B");
                    $tr.find("td").last().attr("class", class_attr);
                    
                    if($tr.attr("class").indexOf("start") == -1) {
                        $tr.find("td").eq(1).text( max );

                        if(max > $max.val()) { $max.val(max); };
                        max++;
                    };
                };
                
                // Header title
                if($tr.attr("class").indexOf("start") > -1) {
                    $tr.find("td").last().text(LOCALIZE["style-list"]);
                };
            })
        })
    };
    
    
    if($categories.length) {
        // Variables
        $table = $categories.find("div.table");
        $max = $table.closest("article").find("._categories_max input.string");
        
        // Init new categories object
        if(isNaN(parseInt($max.val()))) {
            $max.val(0);
            categoriesTableInitialize($table)
        }

        var CATEGORIES_KEY_MAX = $max.val();
        // Initialize
        initializeCategoriesList( $table );

    }

    // ==========================================
    // ==========================================
    // ==========================================
    //           Category project page
    // ==========================================
    // ==========================================
    // ==========================================

    var $category = $("article ._category").closest("article");
    
    function updateCategoriesButtons() {
    // --------------------------------
    // Shows selected category items
    // --------------------------------
        $input = $category.find("._category input.string");
        selected = $input.val().split(";");
        $section = $category.find("._category");
        $section.find(".category_items").remove();
        // Build HTML
        html = "";
        html = html + "<div><div class='category_items'>";
        if($input.val().trim() != "") {
            for(i = 0; i < selected.length; i++) {
                key = selected[i];
                if(CATEGORIES_LIST[key] == "") { label = key; }
                else { label = CATEGORIES_LIST[key]; }
                html = html + "<label class='" + key + "'>" + label + "</label>";
            }
        }
        else {
            html = html + "<label class='add'>+</label>";
        }
        html = html + "</div></div>";
        // Show categories
        $section.append(html);
        // Click - show list action
        $section.find(".category_items").unbind("click").click(function() {
            selectCategoryPopup($(this));
        })
    };
    
    function selectCategoryPopup($input) {
    // --------------------------------
    // $input = <object> category input field
    // --------------------------------
    // Adds & show page fader & show categories popup box
    // --------------------------------
        $category = $input.closest("article");
        $input = $category.find("._category input.string"); // if not input
        
        // Get popup title from section Label
        $title = $input.closest("section").find("p.label");
        if($title.length && $title.text().trim() != "") {
            title = $title.text().trim();
        }
        else {
            title = LOCALIZE["categories-label"];
        };

        selected = $input.val().split(";");
        // Remove previous (if any)
        $("#page_fader, #popup_container").remove();
        // Build HTML
        html = "<div id='page_fader'></div>";
        html = html + "<div id='popup_container'><div id='popup_box'>";
        html = html + "<h3>" + title + "</h3>";
        // Categories
        html = html + "<div class='categories'>";
        for(i in CATEGORIES_LIST) {
            val = CATEGORIES_LIST[i];
            if(selected.indexOf(i) > -1) {
                checked = "checked";
            }
            else {
                checked = "";
            }
            
            if(CATEGORIES_MULTI) {
                html = html + "<label><input type='checkbox' name='category[]' class='" + i + "' value='" + val + "'" + checked + ">" + val + "</label>";
            }
            else {
                html = html + "<label><input type='radio' name='category' class='" + i + "' value='" + val + "'" + checked + ">" + val + "</label>";
            }
        }
        html = html + "</div>";
        // Buttons
        html = html + "<div class='buttons two'><button class='confirm'>" + LOCALIZE["ok-label"] + "</button><button class='cancel'>" + LOCALIZE["cancel-label"] + "</button></div>";
        // Close popup box & container
        html = html + "</div></div>";
        // Show popup
        $("body").append( html );
        $("#page_fader").fadeIn(500);
        $("#popup_container").fadeIn(250, function() {
            // ====== Actions ======
            // OK
            $("#popup_container .confirm").click(function() {
                selected = [];
                $("#popup_container .categories input").each(function() {
                    if($(this).prop("checked") == true) {
                        selected[selected.length] = $(this).attr("class");
                    }
                })
                $input.val( selected.join(";") );
                updateCategoriesButtons();
                $("#page_fader, #popup_container").fadeOut(250, function() { $(this).remove(); });
            })
            // Cancel
            $("#popup_container .cancel").click(function() {
                $("#page_fader, #popup_container").fadeOut(250, function() { $(this).remove(); });
            })
        });
    };
    
    // ====== Launch category project page ======
    if($category.length && CATEGORIES_LIST) {
        updateCategoriesButtons();
        $category.find("._category input.string").click(function() {
            selectCategoryPopup($(this));
        })
    }
    
})