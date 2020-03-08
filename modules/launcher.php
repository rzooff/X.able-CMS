<?php
    // build: 20200306

    error_reporting(0);
	session_start();
	require "script/functions.php";
	require "script/xml.php";

    // ====================================
    // ====================================
    // ====================================
    //           Global Variables
    // ====================================
    // ====================================
    // ====================================


    // Master documents
	$settings = loadXml("settings.xml", "draft");
	$navigation = loadXml("navigation.xml", "draft");

    // Patches
	$pages = "pages";
    $error_404 = readXml($settings, "links href_404");
    $plugins_folder = "_plugins";
    $link_pattern = readXml($settings, "links link_pattern");

    // Get all active languages
    $languages = [];
    foreach($settings["multi_language"] as $language) {
        if(readXml($language, "active") != "") { $languages[] = readXml($language, "id"); };
    };
    // Set default (first) language in none
    if(!isset($_SESSION["lang"]) || !in_array($_SESSION["lang"], $languages)) { $_SESSION["lang"] = $languages[0]; };

    // RewriteEngine & ROOT
    if(!isset($_GET["page"])) { $_GET["page"] = ""; };
    if($_GET['page'] != "") { $root = str_repeat("../", count(explode("/", $_GET['page']))); }
    else { $root = ""; };

    // ====================================
    //           Load statistics
    // ====================================

    $statistics_xml = loadXml("statistics.xml", "draft");
    $statistics = [];
    foreach($statistics_xml["multi_statistics"] as $stats) {
        $val = getVal($stats);
        if($val["active"] != "") {
            if(trim($val["code"]) != "" && $val['location'] != "") {
                if(!isset($statistics[$val['location']])) { $statistics[$val['location']] = []; };
                $statistics[$val['location']][] = "<!-- Statistics: ".$val['name']." -->\n".getCode($val['code']);
            }
        }
    };

    function pasteStatistics($tag) {
        foreach($statistics[$tag] as $code) { echo "\n".$code."\n"; };
    };

    // ====================================
    // ====================================
    // ====================================
    //         Draft preview support
    // ====================================
    // ====================================
    // ====================================

    $start_preview = "xable-preview";
    $stop_preview = "xable-nopreview";

    $path = explode("/", $_GET["page"]);
    if(in_array( $path[0] , [ $start_preview, $stop_preview ])) {
        if($path[0] == $start_preview) { $_SESSION['preview_mode'] = "draft"; }
        else { $_SESSION['preview_mode'] = false; };
        array_shift($path);
        $_GET["page"] = join("/", $path);
        if($_GET["page"] == "") { $redirect = $root; }
        else { $redirect = $root.$_GET["page"]."/"; }
        //echo "<!-- Preview status changed\n\tRedirect: \"".$redirect."\"\n-->\n";
        header("Location: ".$redirect); exit();
    }
    //echo "<!-- [Xable data]\n\tPAGE: \"".$_GET["page"]."\"\n\troot: \"".$root."\"\n\tlink_pattern: \"$link_pattern\"\n\tpreview_mode: \"".$_SESSION['preview_mode']."\"\n\tlang: \"".$_SESSION["lang"]."\"\n-->\n";

    // ====================================
    // ====================================
    // ====================================
    //         URL language & path
    // ====================================
    // ====================================
    // ====================================

    $path_key = "@path";
    $folder_key = "@folder";
    $lang_key = "@language";
    if(strstr($link_pattern, $folder_key)) {
        $link_data = getLinkData($_GET["page"], $link_pattern, "@folder");
    }
    else {
        $link_data = getLinkData($_GET["page"], $link_pattern, $path_key);
    };
    // Check path and language input
    foreach(array_filter(explode("/", $link_pattern)) as $key) {
        $val = $link_data[$key];
        if($key == $path_key && $val == "") {
            $link_data[$key] = ReadXml($navigation["multi_page"][0], "href");
        }
        elseif($key == $lang_key) {
            if(in_array($val, $languages)) {
                $_SESSION["lang"] = $val;
            }
            else {
                $link_data[$key] = $_SESSION["lang"];
            }
        };
    };
    //arrayList($link_data);

    // ====================================
    //               Sitemap
    // ====================================

    $sitemap_tree = getSitemap($navigation, $pages, "xml", "draft");
    $offsite_tree = getOutmap($navigation, $pages, "xml", "draft");
    //echo "<!-- [Sitemap]\n"; arrayList($sitemap_tree); echo "-->\n";
    //echo "<!-- [Outside]\n"; arrayList($offsite_tree); echo "-->\n";
    $pages_index = getPagesIndex(array_merge($sitemap_tree, $offsite_tree));
    $site_index = getPagesIndex($sitemap_tree);
    $off_index = getPagesIndex($offsite_tree);
    //echo "<!-- [Index]\n"; arrayList($pages_index); echo "-->\n";
    //echo "<!-- [Off Index]\n"; arrayList($off_index); echo "-->\n";

    // ====================================
    //              Redirects
    // ====================================

    if(isset($settings["links"]) && $redirects = readXml($settings, "links redirects")) {
        //echo "Input link: ".$_GET["page"]."\n";
        $redirects = preg_replace("/ : | :|: /", ":", $redirects);
        $redirects = str_replace("&#39;", "':'", $redirects);
        $redirects = str_replace($lang_key, $_SESSION["lang"], $redirects);
        $redirects = explode("[br]", $redirects);
        foreach($redirects as $item) {
            list($from, $to) = explode(":", $item);
            if($from == $_GET["page"] || $from == $_GET["page"]."/") {
                $redirect = $root.$to;
                //echo "<!--\n\t[Redirect link: \"$from\" -> \"$to\"]\n\tRedirect: \"".$redirect."\"\n-->\n";
                header("Location: ".$redirect); exit();
            }
        };
    };

    // ====================================
    //            Verify patch
    // ====================================

    // URL(pattern = ''): <domain>
    if($link_pattern == "" && $_GET["page"] == "" || $link_pattern == $lang_key && $_GET["page"] == $link_data[$lang_key]) {
        echo "<!-- [Valid link: Empty URL ] -->\n";
    }
    // URL (pattern = '*@language*'): <domain>/* missing language
    elseif(
        isset($link_data[$lang_key]) &&
        $link_data[$lang_key] != "" &&
        $_GET["page"] != $link_data[$lang_key] &&
        !strstr($_GET["page"], $link_data[$lang_key]."/") &&
        !strstr($_GET["page"], "/".$link_data[$lang_key])
        )
    {
        if(isset($link_data["@folder"])) { $link_data["@folder"] = $_GET["page"]; }
        elseif(isset($link_data["@folder"])) { $link_data["@path"] = $_GET["page"]; };
        $redirect = $root.join("/", array_filter($link_data))."/";
        //echo "<!--\n\t[Fixed link: added missing language]\n\tRedirect: \"".$redirect."\"\n-->\n";
        header("Location: ".$redirect); exit();
    }
    // URL(pattern = '@lang/@folder'): <domain>/<folder>/ -> <domain>/<lang>/<folder>/
    elseif(isset($link_data["@folder"]) && ($link_data["@folder"] == "" || is_dir("$pages/".$link_data["@folder"]))) {
        $link_get = join("/", array_filter(explode("/", $_GET["page"])));
        $link_test = join("/", array_filter($link_data));
        // Check for language in link
        if($link_get != $link_test) {
            $redirect = $root.$link_test."/";
            //echo "<!--\n\t[Fixed link: Added langue to folder]\n\tRedirect: \"".$redirect."\"\n-->\n";
            header("Location: ".$redirect); exit();
        }
        else {
            echo "<!-- [Valid link: Language & Folder] -->\n";
        }
    }
    // URL(pattern = '@lang/@path'): <domain>/<path>/ -> <domain>/<lang>/<path>/
    elseif(isset($link_data["@path"]) && isset($pages_index[$link_data[$path_key]])) {
        $link_get = join("/", array_filter(explode("/", $_GET["page"])));
        $link_test = join("/", $link_data);
        // Check for language in link
        if($link_get != $link_test) {
            $redirect = $root.$link_test."/";
            //echo "<!--\n\t[Fixed link: Added langue to path]\n\tRedirect: \"".$redirect."\"\n-->\n";
            header("Location: ".$redirect); exit();
        }
        else {
            echo "<!-- [Valid link: Language & Path] -->\n";
        }
    }
    // Page not found :(
    else {
        $redirect = $root.str_replace("@language", $_SESSION["lang"], $error_404);
        //echo "<!--\n\t[ERROR 404 - Page not found]\n\tRedirect: \"".$redirect."\"\n-->\n";
        header("Location: ".$redirect); exit();
    };

    // ====================================
    //             PAGE(S) LIST
    // ====================================

    $pages_list = [];
    $force_onepage = readXml($settings, "links force_onepage");
    // ====== One page style ======
    if(isset($link_data[$folder_key]) || $link_pattern == "" || (isset($force_onepage) && $force_onepage != "")) {
        echo "<!-- One page mode -->\n";
        if(isset($link_data[$folder_key])) {
            $folder = $link_data[$folder_key];
        }
        else {
            $folder = "";
        };
        foreach(array_keys($site_index) as $page_link) {
            if(
                ($folder == "" && !strstr($page_link, "/")) ||
                ($folder != "" && substr($page_link, 0, strlen($folder) + 1) == $folder."/")
                )
            {
                $active = $pages_index[$page_link]["active"];
                $href = $pages_index[$page_link]["href"];
                $title = $pages_index[$page_link]["title"];
                if(isset($title) && (!isset($title) || $title != "")) {
                    $pages_list[] = $href;
                }
            }
        }
    }
    // ====== Multipage style ======
    else {
        echo "<!-- Multi page mode -->\n";
        if(strstr($link_data[$path_key], "/")) {
            echo "<!-- SUBPAGE: ".$link_data[$path_key]." -->\n";
            $pages_list[] = $link_data[$path_key];
        }
        else {
            echo "<!-- MAIN PAGE: ".$link_data[$path_key]." -->\n";
            foreach($navigation['multi_page'] as $page) {
                $active = readXml($page, "active");
                $href = readXml($page, "href");
                $title = readXml($page, "title");
                $page_path = "$pages/$href.xml";
                if($active != "" && $title != "" && file_exists($page_path)) {
                    $pages_list[] = $href;
                }
            }
        };
    };



?>