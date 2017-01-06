<?php
    echo "\n";

    echo "\t\t<nav>\n";
    echo "\t\t\t<h1><strong>><</strong>.able<span>CMS</span></h1>\n";
    echo "\t\t\t<aside id='search'>\n";
    echo "\t\t\t\t<input type='text' name='search' placeholder='Szukaj...' value=''>\n";
    echo "\t\t\t\t<button type='submit'><span class='fi-magnifying-glass'></span></button>\n";
    echo "\t\t\t</aside>\n";

    // ======================================
    //        Define editable content
    // ======================================

    function getNavLabels($path, $admin_lang) {
    // --------------------------------
    // $path = <string> menu xml file path
    // $admin_lang = <string> lang code
    // --------------------------------
    // RETURNS: <array> Page titles associatet with target links
    // --------------------------------
        $nav_xml = loadXml($path);
        $nav_tag = "multi_page";
        $navigation = array(); // links & titles
        foreach($nav_xml[$nav_tag] as $page) {
            //arrayList($page);
            $href = $page['href'][0]['string'][0];
            $label = $page['title'][0]['text'][0][$admin_lang][0];
            $hash_link = split("#", $href);
			$file_link = split("\.", $href);
            if(count($hash_link) == 2) { // #hash
                $key = $hash_link[1];
            }
			elseif(count($file_link) == 2) { // page.ext
				$key = $file_link[0];
			}
            else { // page
                $key = $href;
            };
			// Add to list
            if(substr($href, 0, 7) != "http://" && substr($href, 0, 8) != "https://" && !$navigation[$key]) { // ignore external links & not already defined
                $navigation[$key] = $label;
            };
        };
        return $navigation;
    };

    function checkVersion($label, $document_path, $site_options) {
    // --------------------------------
    // $label = <string> document title
    // $document_path = <string> document path
    // $site_options = <array> xable.ini site options
    // --------------------------------
    // RETURNS: <string> Document title label with version status indicatior (if needed)
    // --------------------------------
        $draft_ext = "draft";
        if($site_options['draft_supprt'] != "false" && file_exists("$document_path.$draft_ext")) {
            if(file_exists($document_path)) {
                $label = $label."<b class='saved-draft manual' help='Edytowany'>&nbsp;&nbsp;&bull;</b>";
            }
            else {
                $label = $label."<b class='new-draft manual' help='Edytowany, bez publikacji'>&nbsp;&nbsp;&bull;</b>";
            };
        };
        return $label;
    };

    // ======================================
    //          generate Navigation
    // ======================================

	$nav_list = array();
    $nav_pathes_list = array();

    echo "\t\t\t<dl>\n";



    foreach(array_keys($nav_documents) as $title) {
        $data = split("@", $nav_documents[$title]);
        
        //echo $title."<br>\n";
        //arrayList($data);
        $items = split(";", $data[1]);
        $icon = $data[0];
        if($icon == "") { $icon = "fi-page"; };
        
        // Manual
        $manual = $root."/".array_shift(split("\|", $items[0]));
        $manual_html = "";
        if(in_array($manual, $ini_libraries)) {
            $manual = path(path($manual, "dirname"), "basename");
            if(substr($manual, 0, 1) == "_") { $manual = substr($manual, 1); };
            if(file_exists("$manual_folder/$manual.manual")) {
                $manual_html = array_map("trim", file(("$manual_folder/$manual.manual")));
                $manual_html = "help='".join("<br>", $manual_html)."'";
            };
        };
        
		$nav_group = array();
        $nav_group[] = "\t\t\t\t<div class='group'>\n";
        $nav_group[] = "\t\t\t\t\t<dt $manual_html>$title<span class='expand fi-minus manual' help='Zwiń listę podstron'></span></dt>\n";
        
        foreach($items as $item) {
            // Content analyze
            $item = split("\|", $item);
            $navigation = false;
            // second value: xml navigation file path
            if(is_string($item[1]) && $item[1] != "" && file_exists($root."/".$item[1])) {
                $navigation = getNavLabels($root."/".$item[1], $admin_lang);
                $label = "*";
            }
            // second value: menu label 
            elseif(is_string($item[1]) && $item[1] != "") {
                $label = $item[1];
            }
            // no second value
            else {
                $label = path($item[0], "filename");
            };
            $document_path = $root."/".$item[0];
            // ====== List all files in folder ======
            if($label == "*") {
                // Template -> Add page button
				if($ini_enable['template'] != "false") {
					if(count($templates = listDir( path($document_path, "dirname"), "template,?" )) >0) {
                        // Already taken xml names
						$taken_names = array_map("strtolower", listDir( path($document_path, "dirname"), "xml,draft" ));
                        // Draft names fix
                        foreach(array_keys($taken_names) as $i) {
                            $item = $taken_names[$i];
                            if(path($item, "extension") == "draft") { $item = path($item, "filename"); };
                            $taken_names[$i] = $item;
                        };
                        // Output
						$nav_group[] = "\t\t\t\t\t<dd class='template' taken='".join(";", $taken_names)."' href='".join(";", $templates)."'><span class='fi-plus'></span>Dodaj stronę</dd>\n";
					};
				};
                // Pages list
                $group_by_nav = array();
                $group_by_name = array();
                
                // ====== Order ======
                $folder_path = path($document_path, "dirname");
                $file_extension = path($document_path, "extension");
                
                if($order = array_shift(listDir($folder_path, "order"))) {
                    $pathes_list = array();
                    foreach(array_map("trim", file("$folder_path/$order")) as $file_name) {
                        //echo "order -> $file_name\n";
                        $file_path = "$folder_path/$file_name.$file_extension";
                        if(file_exists($file_path) || file_exists("$file_path.$draft_ext")) {
                            $pathes_list[] = "$folder_path/$file_name.$file_extension";
                        };
                    };
                    //arrayList($pathes_list);
                    $sorted_flag = true;
                }
                // ====== Non-Order ======
                else {
                    $pathes_list = array();
                    foreach(listDir( $folder_path, ".,?" ) as $item) {
                        // Draft without published version
                        if(path($item, "extension") == "draft") {
                            $published = path($item, "dirname")."/".path($item, "filename");
                            if(!file_exists($published) && path($published, "extension") == $file_extension) {
                                //echo "> $item -> ".path($item, "filename")."<br>\n";
                                $pathes_list[] = $published;
                            };
                        }
                        // Published
                        elseif(path($item, "extension") == $file_extension) {
                            $pathes_list[] = $item;
                        };
                    };
                    $sorted_flag = false; // Keep sorted by filename
                };
                //arrayList($pathes_list);
                // ====== Navigation list ======
                foreach($pathes_list as $document_path) {
					//echo "> $document_path<br>\n";
                    if(file_exists($document_path) || file_exists("$document_path.$draft_ext")) {
                        $label = path($document_path, "filename");
                        if(is_array($navigation) && is_string($navigation[$label])) {
							$label = $navigation[$label];
							$html = "\t\t\t\t\t<a href='index.php?path=$document_path'><dd><span class='$icon'></span>".checkVersion($label, $document_path, $site_options)."</dd></a>\n";
							$group_by_nav[strtolower($label)] = $html;
						}
						else {
							$label = ucfirst(str_replace("_", " ", $label));
							$html = "\t\t\t\t\t<a href='index.php?path=$document_path'><dd><span class='$icon'></span>".checkVersion($label, $document_path, $site_options)."</dd></a>\n";
							$group_by_name[strtolower($label)] = $html;
						};
                        $nav_pathes_list[] = $document_path;
                    }
                    else {
                        $label = $document_path;
                        $html = "<dd style='color:#fd585e'><span class='$icon'></span>Not found: '$label'</dd>\n";
						$group_by_name[strtolower($label)] = $html;
                    };
                };

                if(!$sorted_flag) { ksort($group_by_name); };
				foreach($navigation as $label) {
					if($item = $group_by_nav[strtolower($label)]) { $nav_group[] = $item; };
				};
				foreach($group_by_name as $item) { $nav_group[] = $item; };
            }
            // ====== Single file ======
            elseif(file_exists($document_path) || file_exists("$document_path.$draft_ext")) {
                if(is_array($navigation) && is_string($navigation[$label])) { $label = $navigation[$label]; };
                $label = ucfirst(str_replace("_", " ", $label));
                if(path($document_path, "extension") == "order") { $item_icon = "fi-list"; } else { $item_icon = $icon; }; // Order icon override -> list
                $nav_group[] = "\t\t\t\t\t<a href='index.php?path=$document_path'><dd><span class='$item_icon'></span>".checkVersion($label, $document_path, $site_options)."</dd></a>\n";
                $nav_pathes_list[] = $document_path;
            }
            else {
                $nav_group[] = "<dd style='color:#fd585e'><span class='$icon'></span>Not found: '$document_path'</dd>\n";
            };
        };
		// No files found -> set correct label name!
		// ====== OUTPUT ======
        $nav_group[] = "\t\t\t\t</div>\n";
		$nav_list[] = join("", $nav_group);
		
		//echo "-----------------------------\nNAV: [$title]\n-----------------------------\n";
		//arrayList($nav_group);
    };
	echo join("", $nav_list);
    echo "\t\t\t</dl>\n";

    $_SESSION['nav_pathes_list'] = $nav_pathes_list;


    // ====================================
    //          Preview page button
    // ====================================

    $domain = $settings['page'][0]['domain'][0]['string'][0]; // not-used
    $link = $root; // go back to site root

    // Use link pattern
    $link_pattern = $site_options['link_pattern'];
    if(is_string($link_pattern) && $link_pattern != "") {
        $preview = array();
        $relative_path = substr($path, strlen($root) + 1);
        $preview['folder'] = path($relative_path, "dirname");
        $preview['filename'] = path($relative_path, "filename");
        //arrayList($preview);
        if($preview['folder'] != "" && path($preview['folder'], "filename") != "_repository") { // disable link to root & repository
            // subpages support
            if(path($preview['folder'], "dirname") != "") {
                if($site_options['link_subpages'] == "true") {
                    $preview['filename'] = path($preview['folder'], "filename")."/".$preview['filename'];
                }
                else {
                    $preview['filename'] = path($preview['folder'], "basename");
                };
            };
            foreach(array_keys($preview) as $key) {
                $link_pattern = str_replace("@".$key, $preview[$key], $link_pattern);
            };
            $link = $link."/".$link_pattern;
        };
    };

    // Query data (GET) support
    $link = split("\?", $link);
    if(count($link) == 2) {
        $query = split("&", $link[1]);
        $link = $link[0];
    }
    else {
        $query = array();
        $link = $link[0];
    };

    // Add draft preview trigger
    if($site_options['draft_support'] != "false") { $query[] = "preview=draft"; };

    // Hash data support
    $link = split("#", $link);
    if(count($link) == 2) { $hash = $link[1]; } else { $hash = false; };
    $link = $link[0];
    
    // Complete link URL with queries & hash
    if(count($query) > 0) { $link = $link."?".join("&", $query); };
    if($hash) { $link = $link."#".$hash; };

    // ====== BUTTON ======
    echo "\t\t\t<div class='buttons'><a href='$link' target='_blank'><button id='page_preview' class='manual' help='Podgląd zapisanych zmian'>Podgląd strony</button></a></div>\n";

    echo "\t\t</nav>\n";
?>