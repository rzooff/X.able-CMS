<?php
    // build: 20200306

    // ====================================
    //            Preview mode
    // ====================================

    echo "\n<!-- ====== PREVIEW MODE ====== -->\n";
    if(is_string($_SESSION['preview_mode']) &&  $_SESSION['preview_mode'] != "") {
        echo "\t\t<div id='preview_mode_container'>\n";
        // Check for draft
        if(!$current_page || $current_page == "") {
            $pages_list = [];
            foreach(listDir($pages, "xml,draft,prev") as $file) {
                $pagename = array_shift(explode(".", $file));
                if(!in_array($pagename, $pages_list)) {
                    $pages_list[] = $pagename;
                }
            }
        }
        else {
            $pages_list = [ $current_page ];
        }
        //arrayList($pages_list);
        $edit_status = [];
        foreach($pages_list as $pagename) {
            $exists = [];
            foreach(["xml", "xml.draft", "xml.prev"] as $ext) {
                if(file_exists("$pages/$pagename.$ext")) { $exists[] = array_pop(explode(".", $ext)); };
            }
            if(!in_array("xml", $exists) && in_array("draft", $exists)) {
                $edit_status[] = "unpublished";
            }
            elseif(in_array("xml", $exists) && in_array("draft", $exists)) {
                $edit_status[] = "edited";
            }
            elseif(in_array("xml", $exists) && !in_array("draft", $exists)) {
                $edit_status[] = "published";
            }
        };
        //arrayList($exists);
        if(count($edit_status) > 0) {
            echo "\t\t\t<input type='hidden' id='preview_mode_status' value='".join(";", $edit_status)."'>\n";
        };
        echo "\t\t\t<input type='hidden' id='preview_mode' value='".$_SESSION['preview_mode']."'>\n";
        echo "\t\t\t<script src='".$root."script/preview_mode.js?v=$updated'></script>\n";
        echo "\t\t\t<link class='preview_mode_style' rel='stylesheet' href='".$root."style/preview_mode.css?v=$updated'>\n";
        echo "\t\t</div>\n";
    };

    // ====================================
    //             Load plugins
    // ====================================

    echo "\n<!-- ====== PLUGINS ====== -->\n";
    foreach(listDir($plugins_folder, "/") as $plugin) {
        echo "\t\t<div class='plugin_container plugin-".$plugin."'>\n";
        $files_groups = [];
        foreach(listDir("$plugins_folder/$plugin", ".,?") as $file) {
            $ext = path($file, "extension");
            $files_groups[$ext][] = $file;
        };
        foreach($files_groups["php"] as $file) {
            include $file;
        };
        foreach($files_groups["css"] as $file) {
            echo "\t\t\t<link class='plugin_style' rel='stylesheet' href='".$root.$file."'>\n";
        };
        foreach($files_groups["js"] as $file) {
            echo "\t\t\t<script src='".$root.$file."'></script>\n";
        };
        echo "\t\t</div>\n";
    };
?>