<div class='fake_cover'></div>

<div id='working_info'>
    <div class='popup'>
        <p><?php echo localize("job-in-progress"); ?>...</p>
        <div class="uploader"><?php echo localize("working"); ?>...</div>
    </div>
</div>

<nav>
    <div id="menu_bar">
        <label class='logo'>
            <span>&gt;&lt;</span>
        </label>

        <?php
        
            $nav_array = [
                "title" => [
                    "label" => $panel_label,
                    "items" => [
                        "creator" => localize("creator-label"),
                        "update" => localize("update-label"),
                        "users" => localize("users-groups-label"),
                        "explorer" => localize("file-explorer-label"),
                        "separator" => "<hr>",
                        "quit" => localize("quit-label")
                    ]
                ]
            ];

            if($panel_name == "creator") {
                $nav_array["file"] = [
                    "label" => localize("file-label"),
                    "items" => [
                        "new" => localize("new-label"),
                        "open" => localize("open-label"),
                        "reload;active" => localize("reload-label"),
                        "save;active" => localize("save-label"),
                        "save_as" => localize("save-as-label"),
                    ]
                ];
                $nav_array["tools"] = [
                    "label" => localize("tools-label"),
                    "items" => [
                        "fill_languages" => localize("fill-languages"),
                        "delete_unused_languages" => localize("delete-unused-languages"),
                        "unify_sections" => localize("unify-sections"),
                        "change_pathes" => localize("change-pathes"),
                        "make_template" => localize("make-template"),
                    ]
                ];
            }
            /*
            elseif($panel_name == "explorer") {
                $nav_array["tools"] = [
                    "label" => localize("tools-label"),
                    "items" => [
                        "clean_files" => localize("clean-files"),
                    ]
                ];
            }
            */
            //arrayList($nav_array);


            echo "\n";
            foreach(array_keys($nav_array) as $key) {
                $nav_group = $nav_array[$key];

                if($key == "title") { $class = "title menu"; } else { $class = "menu"; };

                echo "<label class='$class'>\n";
                echo "\t<p>".nbsp($nav_group["label"])."</p>\n";
                echo "\t<ul>\n";

                foreach(array_keys($nav_group["items"]) as $item_key) {
                    $item_label = $nav_group["items"][$item_key];
                    $item_key = explode(";", $item_key);
                    if(count($item_key) == 2) { $item_class = $item_key[1]; } else { $item_class = ""; };
                    $item_key = $item_key[0];
                    if($item_key == "separator") { $item_class = $item_key; $item_key = ""; };
                    if($item_key != $panel_name) {
                        echo "\t\t<li class='$item_class' value='$item_key'>".nbsp($item_label)."</li>\n";
                    };
                }

                echo "\t</ul>\n";
                echo "</label>\n";
            }
        ?>
        <button id='view_switch'><span class='fi-monitor'></span></button>
        <!-- <button id='view_switch'>&lt;&gt;</button> -->
    </div>
</nav>