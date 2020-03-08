<?php
    echo "\n";

    echo "\t\t<nav><div class='nav_container'>\n";
    // Logo
    echo "\t\t\t<h1><strong>&gt;&lt;</strong>.able<span>CMS</span></h1>\n";
    // Search box
    echo "\t\t\t<aside id='search' class='search_box'>\n";
    echo "\t\t\t\t<input type='text' name='search' placeholder='".localize("search-label")."...' value=''>\n";
    echo "\t\t\t\t<button type='submit'><span class='fi-magnifying-glass'></span></button>\n";
    echo "\t\t\t</aside>\n";

    // ======================================
    //        Define editable content
    // ======================================

    function getIcon($type) {
    // -------------------------------------------
    // $type = <string> Item type name
    // -------------------------------------------
    // RETURNS: <string> Icons class
    // -------------------------------------------
        $icons = [
            "page" => "fi-page",
            "attachement" => "fi-paperclip",
            "blog" => "fi-comments",
            "file" => "fi-page",
            "files" => "fi-page-copy",
            "image" => "fi-photo",
            "user" => "fi-torso",
            "users" => "fi-torsos",
            "link" => "fi-link",
            "list" => "fi-list",
            "contact" => "fi-mail",
            "gallery" => "fi-thumbnails",
            "options" => "fi-checkbox",
            "quiz" => "fi-list-thumbnails",
            "plugin" => "fi-puzzle",
            "settings" => "fi-widget",
            "statistics" => "fi-graph-bar",
            "time" => "fi-clock",
            "tools" => "fi-wrench",
            "trash" => "fi-trash",
            "website" => "fi-web",
        ];
        if($icons[$type]) {
            return $icons[$type];
        }
        else {
            return reset($icons);
        }
    };

    //arrayList($_SESSION["ini_site_options"]);

    function pagesTree($path, $indent_depth = 0, $nav_file = false, $type = false, $templates = false) {
    // -------------------------------------------
    // $path = <string> Folder path
    // $indent_depth = <integer> Brench depth
    // $nav_file = <string> Navigation file path, optional
    // $type = <string> Files icon type
    // $templates = <array> Templates list
    // -------------------------------------------
    // GENERATES: Files / Folders / Subfolders tree
    // -------------------------------------------
        
        $html = array();
        $taken = listDir($path, "xml,draft");
        if(!$templates) { $templates = listDir($path, "template,?"); };

        $pages = pagesOrder($path, $nav_file);
        $n = 0; // count to last
        
        $default_icon = getIcon($type);
        
        foreach(array_keys($pages) as $item_path) {
            $title = $pages[$item_path];

            //echo "> ".join(",", $indent)." @ $title<br>\n";
            $subfolder = path($item_path, "dirname")."/".path($item_path, "filename");
            if(is_dir($subfolder)) {
                
                if(file_exists("$subfolder/.order")) {
                    $options = [ "change-subpages-order" => "$subfolder/.order" ];
                };
                
                $folder_icon = "fi-folder";
                echo "\t<dd class='folder' data-type='$type'>\n";
                navItem($item_path, $indent_depth, $folder_icon, $title, $options);
                
                echo "\t\t<dl>\n";

                pagesTree($subfolder, $indent_depth + 1, false, $type, $templates);
                echo "\t\t</dl>\n";
                
                echo "\t</dd>\n";
            }
            else {
                
                $max_depth = $_SESSION["ini_site_options"]["subpages_depth"];
                
                if(!isset($max_depth) || intval($max_depth) >= $indent_depth) {
                    $options = [ "add-subpage" => join(";", $templates), "remove-page" => $item_path ];
                }
                else {
                    $options = [ "remove-page" => $item_path ];
                };
                
                echo "\t<dd data-type='$type'>\n";
                navItem($item_path, $indent_depth, $default_icon, $title, $options);
                echo "\t</dd>\n";
            }
        }

        // ====== Add page button ======
        if($templates && count($templates) > 0) {
            if($default_icon == "fi-torso") { $label = localize("add-user"); }
            else { $label = localize("add-page"); };
            
            echo "\t<dd class='add_page'>\n";
            echo "\t\t<input type='hidden' class='taken_filenames' value='".join(";", $taken)."'>\n";
            echo "\t\t<input type='hidden' class='folder_path' value='".$path."'>\n";
            navItem(join(";", $templates), $indent_depth, "fi-plus", $label, false);
            echo "\t</dd>\n";
        };

    };

    function pagesOrder($path, $nav_file = false) {
    // -------------------------------------------
    // $path = <string> Folder path
    // $nav_file = <string> Navigation file path, optional
    // -------------------------------------------
    // RETURNS: <array> Pages list [ <path> => <title>, (...) ]
    // -------------------------------------------
        $pages = array();
        
        // ====== by Xml Order file ======
        if($order = loadXml("$path/.order")) {
            foreach($order["multi_item"] as $item) {
                $item_path = $path."/".readXml($item, "path");
                $item_title = xmlLanguageTitles($item);
                //$item_title = readXml($item, "title", $_SESSION["edit_lang"]);
                if(xmlExists($item_path)) { $pages[$item_path] = $item_title; };
            }
        }
        // ====== by Order file ======
        elseif($order = array_shift(listDir($path, "order,?"))) {
            foreach(array_map("trim", file($order)) as $item) {
                $item = explode("|", $item);
                $item_path = $path."/".array_shift($item);

                if(is_array($item)) { $title = join("\|", $item); }
                else { $title = false; };
                
                if(strlen(path($item_path, "extension")) < 2) { $item_path = "$item_path.xml"; };
                
                if(xmlExists($item_path)) {
                    //$title = readXml(loadXml($item_path), "header title");
                    if(!is_string($title) || trim($title) == "") { $title = ucwords(path($item_path, "filename")); };
                    $pages[$item_path] = $title;
                };
            };
        }
        // ====== by Navigation file ======
        elseif(file_exists($nav_file)) {
            $nav_xml = loadXml($nav_file, "draft", true);
            
            //arrayList($nav_xml);
            foreach($nav_xml["multi_page"] as $page) {
                $title = readXml($page, "title", $_SESSION['edit_lang']);
                $href = readXml($page, "href");
                
                if(substr($href, 0, 1) == "#") { $item = substr($href, 1); } // #link
                else { $item = $href; };

                $item_title = xmlLanguageTitles($page);

                if(strstr($href, "://")) {
                    // External links
                    $pages[$href] = "<span class='external'>".$item_title."</span><i class='active_mode color_outside fi-info manual' help='".localize("style-link-external").":<br><span class=\"code\">".$href."</span>'></i>"; // v1
                    //$pages[$href] = "<span class='external manual' help='".localize("style-link-external").":<br><span class=\"code\">".$href."</span>'>".$item_title."</span>"; // v2
                }
                else {
                    // Document subpage
                    $item_path = "$path/$item.xml";
                    if(xmlExists($item_path)) {
                        if(!is_string($item_title) || trim($item_title) == "") { $item_title = $item; };
                        $pages[$item_path] = $item_title;
                    }
                    else {
                        $pages[$item_path] = "<span class='error'>".$item_title."</span><i class='active_mode color_outside fi-alert manual' help='".localize("not-found").":<br><span class=\"code\">".$item_path."</span>'></i>";
                    }
                }
            }
            // Show items outside NAV
            foreach(listDirXml($path, "?") as $file) {
                if(!$pages[$file]) {
                    // Format to match "link" field in navigation.xml
                    $link = explode("/", $file);
                    array_shift($link); // -root
                    array_shift($link); // -pages
                    $basename = array_pop($link);
                    if(count($link) > 0) { $link = join("/", $link)."/"; } else { $link = ""; };
                    $link = $link.path($basename, "filename");
                    // Add info                    
                    $pages[$file] = xmlTitle($file, "*", "draft")." <i class='active_mode color_outside fi-alert manual' help='".localize("not-in-navigation").":<br><span class=\"code\">".$link."</span>'></i>";
                }
            }
        }
        // ====== by Filenames ======
        else {
            foreach(listDirXml($path, "?") as $item_path) {
                $pages[$item_path] = xmlTitle($item_path, "*", "draft");
            }
        }
        return $pages;
    };

    function listDirXml($path, $options = false) {
    // -------------------------------------------
    // $path = <string> Xable document path
    // $options = <string> based on listDir(), eg: "?", optional
    // -------------------------------------------
    // RETURNS: <array> "Xml" or "draft" documents patches with xml extension
    // -------------------------------------------
        $files = array();
        if(is_string($options) && $options != "") { $options = ",$options"; };
        foreach(listDir($path, "xml,draft".$options) as $file_path) {
            $filename = $file_path;
            while(path($filename, "extension") != "") {
                $filename = path($filename, "filename");
            };
            $folder = path($file_path, "dirname");
            if(!in_array($filename, $files)) {
                $files[] = "$folder/$filename.xml";
            };
        }
        return $files;
    };

    function navItem($href, $indent_depth, $icon, $label, $options) {
    // -------------------------------------------
    // $href = <string> Document path
    // $indent_depth = <integer> Indent level depth
    // $icon = <string> Document icon class
    // $label = <string> Document name
    // $options = <array> Options button actions: [ "key" => "value" ]
    // -------------------------------------------
    // GENERATE: navigation item row
    // -------------------------------------------        
        if(!is_string($icon)) { $icon = "fi-page-filled"; };

        if(!is_string($href) || $href == "") { $class = ""; }
        elseif(strstr($href, "://")) { $class = "external_item"; $icon = "fi-link"; }
        elseif($href == $_SESSION["edit_path"]) { $class = "current"; }
        elseif(array_pop(explode(".", $href)) == "template") { $class = "add_item active_item"; }
        else { $class = "active_item"; }

        echo "\t\t<div class='nav_item nav_group_title $class'>\n";
        
        $indent_html = [];
        while(count($indent_html) < $indent_depth) {
            $indent_html[] = "<button class='item_indent'></button>";
        }
        echo "\t\t\t".join("", $indent_html)."\n";
        

        echo "\t\t\t<button class='item_icon'><i class='icon ".$icon."'></i></button>\n";
        
        // ====== Link ======
        if($mode = array_pop(xmlExists($href))) {
            $modes = [ "draft" => "edited", "published" => "published", "unpublished" => "unpublished" ];
            $help = localize("status-".$modes[$mode]);
            $mode = "<i class='active_mode color_$mode manual fi-flag' help='$help'></i>";
        }
        else {
            $mode = "";
        };
        
        echo "\t\t\t<button class='item_label' href='$href'><p>".BBCode($label).$mode."</p></button>\n";
        
        // Help
        if(is_string($options["help"])) {
            echo "\t\t\t<button class='item_options manual' help='".$options["help"]."'><i class='icon fi-info'></i></button>\n";
        }
        elseif(is_string($options["order"])) {
            echo "\t\t\t<button class='item_options manual change-subpages-order' value='".$options["order"]."' help='".localize("change-subpages-order")."'><i class='icon fi-list'></i></button>\n";
        }
        elseif(is_array($options)) {
            
            if(path($href, "dirname") == $_SESSION["admin_root"]) {
                unset($options["remove-page"]);
            }
            
            echo "\t\t\t<div class='item_options_box'>\n";
            
            foreach(array_keys($options) as $key) {
                $val = $options[$key];
                $icons = [
                    "add-subpage" => "fi-folder-add",
                    "change-subpages-order" => "fi-list-thumbnails",
                    "remove-page" => "fi-x",
                ];
                
                echo "\t\t\t\t<button class='item_option action-$key manual' value='$val' help='".localize($key)."'><i class='icon ".$icons[$key]."'></i></button>\n";
            };
            //echo "\t\t\t<button class='item_options'><i class='icon more'>&bull;&bull;&bull;</i></p>\n";
            echo "\t\t\t</div>\n";
        }
        echo "\t\t</div>\n";
    };

    // ======================================
    // ======================================
    // ======================================
    //        Define editable content
    // ======================================
    // ======================================
    // ======================================

    foreach(array_keys($nav_documents) as $group_label) {
        $nav_group = $nav_documents[$group_label];

        echo "<dl>\n";
        
        // Help / manual
        if(is_string($nav_group["help"]) && $nav_group["help"] != "") {
            $options = [ "help" => localize($nav_group["help"]) ];
        }
        else {
            //$options = [ "order" => ".order" ];
            $options = false;
        };
            
        echo "\t<dt>\n";
        
        // ====== Add change order button form Navigation file ======
        if(count($nav_group["items"]) == 1) {
            $nav_file = array_pop(explode("|", $nav_group["items"][0]));
            if(path($nav_file, "extension") == "xml" && file_exists("$root/$nav_file")) {
                echo "<!-- Group label: $group_label -->\n";
                $options = [ "change-subpages-order" => "$root/$nav_file" ];
            }
        };
        
        navItem(false, 0, $nav_group["icon"], $group_label, $options, false);
        if(isset($nav_group["fold"])) {
            echo "\t\t<input type='hidden' class='ini_fold' value='".$nav_group["fold"]."'>\n";
        }
        
        echo "\t</dt>\n";
        
        $items = $nav_group["items"];
        
        foreach(array_keys($items) as $n) {
            
            // Variables
            $item = $items[$n];
            $indent_level = 1;
            list($item_path, $item_option) = explode("|", $item);
            $item_path = $root."/".$item_path;
            
            // ====== Get type & Icon ======
            if(is_string($nav_group["types"][$n]) && $nav_group["types"][$n] != "") {
                $type = $nav_group["types"][$n];
            }
            elseif($nav_group["types"][0]) {
                $type = $nav_group["types"][0];
            }
            else {
                $type = "page";
            };
            
            // Single file(s)
            if(xmlExists($item_path)) {
                // Options
                $options = [ "remove-page" => $item_path ];
                
                if(($n + 1) == count($items)) { $last_flag = true; } else { $last_flag = false; };
                if(!is_string($item_option) || trim($item_option) == "") {
                    $item_label = ucwords(path($item_path, "filename"));
                }
                else {
                    $item_label = $item_option;
                }
                // HTML output
                echo "\t<dd data-type='$type'>\n";
                navItem($item_path, $indent_level, getIcon($type), $item_label, $options, $last_flag);
                echo "\t</dd>\n";
            }
            
            // Multiple files
            elseif(path($item_path, "filename") == "*") {
                $folder_path = path($item_path, "dirname");
                
                // templates
                if(is_string($nav_group["template"]) && is_dir($root."/".$nav_group["template"])) {
                    $templates = listDir($root."/".$nav_group["template"], "template,?");
                }
                else {
                    $templates = false;
                };
                
                $nav_file = "$root/$item_option";
                if(!file_exists($nav_file) || is_dir($nav_file)) { $nav_file = false; };

                pagesTree($folder_path, $indent_level, $nav_file, $type, $templates);
            }
        }
        
        echo "</dl>\n";
    }

    //arrayList($nav_documents);

    // ======================================
    //            Preview Button
    // ======================================
    
    $link_pattern = readXml($settings, "links link_pattern");
    // Backward compatibility - link from xable.ini
    if(!is_string($link_pattern)) {
        $link_pattern = $_SESSION["ini_site_options"]["link_pattern"];
        $link_pattern = str_replace("@dirname/@filename", "@path", $link_pattern);
    };
    // Get page href (url path)
    $href = explode("/", path($path, "dirname")."/".path($path, "filename"));
    array_shift($href); // - root
    array_shift($href); // - pages
    $href = join("/", $href);
    // Get page folder
    if(strstr($href, "/")) {
        $folder = explode("/", $href);
        array_pop($folder);
        $folder = join("/", $folder);
    }
    else {
        $folder = "";
    };

    $patterns = [
        "@path" => $href,
        "@folder" => $folder,
        "@dirname" => path($current_path, "dirname"), // obsolete
        "@filename" => path($current_path, "filename"), // obsolete
        //"@language" => $_SESSION["admin_lang"], // off -> changable in js
    ];
    $link = $link_pattern;
    foreach(array_keys($patterns) as $key) { $link = preg_replace("/".$key."/", $patterns[$key], $link); };
    // Add preview trigger
    if($_SESSION["ini_site_options"]["draft_support"] == "true") { $link = "xable-preview/".$link; };
    // ====== Button ======
    echo "\t\t\t<div class='buttons'><a href='$root/' data-link='$link' target='_blank'><button id='page_preview' class='manual' help='".localize("changes-preview")."'>".localize("page-preview")."</button></a></div>\n";
    // Show menu mobile
    echo "\t\t\t<button id='toogle_mobile_menu'><i class='icon fi-list'></i></button>\n";
    
    echo "\t\t</div></nav>\n";
?>
