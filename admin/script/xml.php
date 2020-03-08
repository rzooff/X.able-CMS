<?php

// ===================================================
//               X.able PHP XML module
//           (C)2017 maciej@maciejnowak.com
// ===================================================
// compatibility: php6+
// build: 20200305
// ===================================================

    // ====== array to XML FILE CONTENT / begin ======
    function XmlFileContent($xml) {
    // -------------------------------------------
    // $xml = <array> Xable XML array
    // -------------------------------------------
    // RETURNS: <string> Ready to save xml file content
    // -------------------------------------------
        if(is_array($xml) && count($xml) > 0) {
            $xml = arrayToXml($xml, 1);
            return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<xable>\n".$xml."</xable>";
        };
    }
    // ====== array to XML FILE CONTENT / end ======

    // ====== GET FILENAME / begin ======
    function getFilename($file_path) {
    // -------------------------------------------
    // $path = <string> Xable document path
    // -------------------------------------------
    // RETURNS: <string> Filename, also for multiple extensions cases
    // -------------------------------------------
        $file_path = path($file_path, "filename");
        while(path($file_path, "extension") != "") {
            $file_path = path($file_path, "filename");
        }
        return $file_path;
    };
    // ====== GET FILENAME / end ======

    // ====== XML document EXISTS test / begin ======
    function xmlExists($path) {
    // -------------------------------------------
    // $path = <string> Xable document path
    // -------------------------------------------
    // RETURNS: <array> Document path & active mode ("published", "draft", "unpublished"),
    // eg: [ ../pages/file.xml, "published" ],
    // or <false> if file not exist
    // -------------------------------------------
        $filename = path($path, "filename");
        if(path($filename, "extension") == "xml") { $filename = path($path, "filename"); }
        
        $folder = path($path, "dirname");

        
        $ext_list = [ "xml", "xml.draft" ];
        $exists = [];
        
        foreach($ext_list as $ext) {
            if(file_exists("$folder/$filename.$ext")) { $exists[] = $ext; }
        };
        
        if(in_array("xml.draft", $exists) && in_array("xml", $exists)) {
            return [ "$folder/$filename.xml.draft", "draft" ];
        }
        elseif(!in_array("xml.draft", $exists) && in_array("xml", $exists)) {
            return [ "$folder/$filename.xml", "published" ];
        }
        elseif(in_array("xml.draft", $exists) && !in_array("xml", $exists)) {
            return [ "$folder/$filename.xml.draft", "unpublished" ];
        }
        else {
            return false;
        }
    };
    // ====== XML document EXISTS test / end ======

    // ====== get XML all LANGUAGES TITLES / begin ======
    function xmlLanguageTitles($xml_item) {
    // -------------------------------------------
    // $xml_item = <array> Item xable array
    // -------------------------------------------
    // RETURNS: <string> Titles in all languages
    // -------------------------------------------
        $item_title = "";
        foreach(array_keys($xml_item["title"][0]["text"][0]) as $language) {
            $title = $xml_item["title"][0]["text"][0][$language][0];
            $item_title = $item_title."<span class='lang_title $language'>$title</span>";
        }
        return $item_title;
    };
    // ====== get XML all LANGUAGES TITLES / end ======

    // ====== get XML document TITLE / begin ======
    function xmlTitle($path, $language = false, $draft = false) {
    // -------------------------------------------
    // $path = <string> Xable document path
    // $all_langs = <string> for specified language Title, "*": for all languages, or <false> for Admin Language
    // -------------------------------------------
    // RETURNS: <string> Document title based on header-title or filename
    // -------------------------------------------
        if(!is_string($language)) { $language = $_SESSION["admin_lang"]; };
        // Load draft support
        if($draft && file_exists("$path.$draft")) {
            $path = "$path.$draft";
        };
        // Variables
        $xml = loadXml($path, "draft");
        $headers = [ "header", "user", "person" ];
        $title = false;
        // Find title section
        foreach($headers as $section_name) {
            if(!$title && is_array($xml[$section_name])) {
                if($xml[$section_name][0]["title"]) {
                    // All languages
                    if($language == "*") {
                        $title = xmlLanguageTitles($xml[$section_name][0]);
                    }
                    // Single language
                    else {
                        $title = readXml($xml, "header title", $language);
                    };
                }
                else {
                    // User name
                    $key = array_shift(array_keys($xml["user"][0]));
                    $title = readXml($xml, "user $key", $language);
                }
            }
        };
        // No title -> Filename
        if(!is_string($title) || trim($title) == "") {
            $title = $path;
            while(path($title, "extension") != "") {
                $title = ucwords(path($title, "filename"));
            }
        }
        return $title;
    };
    // ====== get XML document TITLE / end ======

// ===================================================
//                 Array <-> XML file
// ===================================================

    // ====== load XML data to array / begin ======
    function loadXml($file, $preview = false, $force_preview = false) {
    // ----------------------------------------
    // $file = <string> File path
    // $preview = <string> Preview file extension or <false/undefined> for no preview
    // $force_preview = <boolean> Force to load preview, if any (needed for CMS)
    // ----------------------------------------
    // RETURNS: <array> XML data transformed into an array ([key] => ([0] => "val_1", [1] => "val_2)...)
    // ----------------------------------------
        if(is_string($preview) && $preview != "" && ($force_preview || $_SESSION['preview_mode'] == $preview) && file_exists("$file.$preview")) {
            $file = "$file.$preview";
            if(!$force_preview) { $_SESSION['preview_loaded'] = true; };
        };
        $xml = join(" ", array_map("trim", file($file)));
        $xml = xmlToArray($xml);
        if(array_shift(array_keys($xml)) == "xable") { $xml = $xml['xable'][0]; };
        return $xml;
    }; // ====== load XML data to array / end ======

    // ====== save array to XML file / begin ======
    function saveXml($file, $array) {
    // ----------------------------------------
    // $file = <string> File path
    // $array = <array> Data array, xml compatibile: ([key] => ([0] => "val_1", [1] => "val_2)...)
    // ----------------------------------------
    // Saves input data in XML format file
    // ----------------------------------------
        if(file_exists($file)) { rename($file, "$file.bak"); };
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= arrayToXml($array, 0);
        file_put_contents($file, $xml);
        return $xml;
    }; // ====== save array to XML file / end ======

    // ====== XML to Array / begin ======
    function xmlToArray($xml) {
    // ----------------------------------------
    // $xml = <string> XML contents
    // ----------------------------------------
    // RETURNS: <array> XML data transformed into an array ([key] => ([0] => "val_1", [1] => "val_2)...)
    // ----------------------------------------
        $cmt_tags = array("<!--", "-->");
        unset($array);
        $n = 0;
        $text = "";         // text content
        $cmt = false;       // comments flag
        $tag = false;       // part of tag name
        $key = false;       // tag name = array key
        $sub = array();     // sub tags (will be ignored)
        while($n < strlen($xml)) {
            //echo "INPUT: $xml<br>";
            $ch = substr($xml, $n, 1);
            if($n < (strlen($xml) - 1)) { $next = substr($xml, ($n +1), 1); } else { $next = ""; };
            // ====== Ignore comments ======
            if($cmt != false) {
                if(substr($xml, $n, strlen($cmt_tags[1])) == $cmt_tags[1]) {
                    $cmt = false;
                    $n = $n + strlen($cmt_tags[1]) - 1;
                };
            }
            elseif(substr($xml, $n, strlen($cmt_tags[0])) == $cmt_tags[0]) { $cmt = true; }
            // ====== XML analyse ======
            elseif($ch == "<" && !is_numeric($next) && !in_array($next, array("", " ", "!", "?", ".", ",", ";")))  { // tag begin
                if(is_string($tag)) { $text .= "<".$tag; }; // previous "<" character was non-tag
                $tag = "";
            }
            elseif(is_string($tag) && $ch == ">") { // tag end
                if($key == false) { // new begin tag -> key
                    $key = $tag;
                    list($tag, $text) = array(false, "");
                }
                elseif($tag == "/".end($sub)) { // closing the last of sub tags
                    array_pop($sub);
                    $text .= "<".$tag.">";
                    $tag = false;
                }                    
                elseif($tag == "/".$key) { // matched key/tag begin & end                    
                    $text = trim($text);
                    $dat = $array[$key];
                    if(($sub = xmlToArray($text)) != false) { // recurence -> sub tags arrays
                        $dat[] = $sub;
                    }
                    else { // no sub tags -> string value
                        $dat[] = $text;
                    };
                    $array[$key] = $dat;
                    list($key, $tag, $text, $sub) = array(false, false, "", array()); // reset variables after adding complete tag data
                }
                else {
                    if(substr($tag, 0, 1) != "/") { $sub[] = $tag; }; // opening of the sub tag
                    $text .= "<".$tag.">";
                    $tag = false;
                };
            }
            elseif(is_string($tag)) { $tag .= $ch; }
            else {$text .= $ch; };
            $n++;
        };
        return $array;
    }; // ====== XML to Array / end ======

    // ====== Array to XML / begin ======
    function arrayToXml($array, $depth = 0) {
    // ----------------------------------------
    // $array = <array> Data array, xml compatibile: ([key] => ([0] => "val_1", [1] => "val_2)...)
    // $depth = <integer> array/xml level depth (optional)
    // ----------------------------------------
    // RETURNS: <string> data in XML string
    // ----------------------------------------
        if(!is_numeric($depth)) { $depth = 0; };
        $xml = "";
        foreach(array_keys($array) as $key) {
            $n = 0;
            $marg = "";
            while($n++ < $depth) { $marg .= "\t"; };
            $sub = $array[$key];
            foreach($sub as $val) {
                $xml .= "$marg<$key>";
                if(is_array($val)) {
                    $xml .= "\n";
                    $xml .= arrayToXml($val, ($depth + 1));
                    $xml .= "$marg</$key>\n";
                }
                else { $xml .= "$val</$key>\n"; };
            };
        };
        return $xml;
    }; // ====== Array to XML / end ======

// ===================================================
//                    Read XML data
// ===================================================

    // ====== getCode text / begin ======
    function getCode($code) {
    // ----------------------------------------
    // $code = <string> code from XML
    // ----------------------------------------
    // RETURNS = <string> code restored to original source code
    // ----------------------------------------
        $code = str_replace("&lt;", "<", $code);
        $code = str_replace("&gt;", ">", $code);
        $code = str_replace("[br]", "\n", $code);
        return $code;
    }
    // ====== getCode text / begin / end ======

    // ====== convert string to ID / begin ======
    function getId($string) {
    // ----------------------------------------
    // $string = <string>
    // ----------------------------------------
    // RETURNS = <string> Lowercase without spaces & special characters
    // ----------------------------------------
        $string = strtolower(killPl($string));
        $string = preg_replace("/ |\n|\t/", "_", $string);
        $string = preg_replace("/\||=|\^|\/|\\\|\*|'|\"|%|\\$|@|#|&|\\.|,|:|;|\?|!/", "", $string);
        $string = preg_replace("/<|>|\[|\]|\(|\)|{|}/", "", $string);
        $string = preg_replace("/___|__/", "_", $string);
        return $string;
    };
    // ====== convert string to ID / begin / end ======

    // ====== easy GET values from zable article / begin ======
    function getVal($article) {
    // ----------------------------------------
    // $article = <array> xable article array
    // ----------------------------------------
    // RETURNS = <array> All article sections values
    // ----------------------------------------
      $val = array();
      foreach(array_keys($article) as $key) {
          $val[$key] = readXml($article, $key);
      };
      return $val;
    };
    // ====== easy GET values from zable article / end ======

    // ====== easy READ XML data / begin ======
    function readXml($xml, $tags, $language = false) {
    // ----------------------------------------
    // $xml = <array> XML data loaded into an array with loadXml()
    // $tags = <string> TAGS list, divided with spaces, eg: "article section"
    // $language = <string> force language code, optional
    // ----------------------------------------
    // RETURNS: <string> data specified by tags or <array> if multiple data found
    // ----------------------------------------
        // Language
        if(!is_string($language)) { $language = $_SESSION['lang']; }; // By user session
        if(!is_string($language)) { $language = "pl"; } // Default
        // XML Array read
        $tags = explode(" ", $tags);
        foreach(array_keys($tags) as $n) {
            // ====== LOOP ======
            $tag = $tags[$n];
            if(($n + 1) < count($tags)) {
                $xml = $xml[$tag][0];
            }
            // ====== OUTPUT ======
            elseif(count($xml[$tag]) > 1) {
                // Multiple -> Array output
                return $xml[$tag];
            }
            else {
                // Unknown -> Guess output
                if(!is_array($xml[$tag][0]) && !is_string($xml[$tag][0])) {
                    if(is_string($xml['media'][0][$tag][0])) {
                        return $xml['media'][0][$tag][0];
                    }
                    elseif(is_array($xml['radio'][0][$tag])) {
                        return $xml['radio'][0][$tag];
                    }
                    elseif(is_array($xml['checkbox'][0][$tag])) {
                        return $xml['radio'][0][$tag];
                    };
                }
                // Single -> String output
                else {
                    $xml = $xml[$tag][0];
                    // Section data
                    if(is_array($xml['type']) && is_string($type = $xml['type'][0])) {
                        if($type == "text" || $type == "textarea") {
                            $text = $xml['text'][0][$language][0];
                            //if($xml['format'] == "") { return $text; } else { return BBCode($text); };
                            return $text;
                        }
                        elseif($type == "option") {
                            return $xml['selected'][0];
                        }
                        elseif($type == "media") {
                            return $xml[$type][0][ $xml['set'][0] ][0];
                        }
                        elseif($type == "button") {
                            return $xml['action'][0];
                        }
                        elseif($type == "table") {
                            return $xml[$type][0][$language];
                        }
                        else {
                            return $xml[$type][0];
                        };
                    }
                    // Specified options array
                    elseif(is_array($xml['option']) && ($tag == "radio" || $tag == "checkbox")) {
                        return ($xml['option']);
                    }
                    // Other data
                    else {
                        return $xml;
                    };
                };
            };
        };
    };
    // ====== easy READ XML data / begin ======

    // ====== TEST if MEDIA data is filled / begin ======
    function testMedia($val) {
    // ----------------------------------------
    // $val = <string> media content
    // ----------------------------------------
    // RETURNS = <string> media content or <false> if media is empty
    // ----------------------------------------
        $file = array_shift(explode(";", $val));
        if(count(explode("://", $val)) == 2 || ($file != "" && !is_dir($file) && file_exists($file))) {
            return $val;
        }
        else {
            return false;
        }
    };
    // ====== TEST if MEDIA data is filled / begin ======

// ===================================================
//                   URL pattern
// ===================================================

    function getLinkData($link, $pattern, $path_key = "@path") {
    // ----------------------------------------
    // $link = <string> Website URL link
    // $pattern = <string> Website link pattern, eg: @language/@path/
    // $path_key = <string> Document path key in pattern, default: "@path"
    // ----------------------------------------
    // RETURNS: <array> Read link data: [ "key" => "value", ... ]
    // ----------------------------------------
        $keys = array_flip(array_filter(explode("/", $pattern)));
        $values = array_filter(explode("/", $link));
        $found = [];
        $used = [];
        while(count($values) < count($keys)) { $values[] = ""; }; // Missing values
        // Get non path data
        foreach(array_keys($keys) as $key) {
            if($key != $path_key) {
                $i = $keys[$key];
                if($i > $keys[$path_key]) {
                    $i = count($values) - count($keys) + $i;
                }
                $found[$key] = $values[$i];
                $used[] = $i;
            }
        }
        // Add path
        if(isset($keys[$path_key])) {
            $i = $keys[$path_key];
            $path = [];
            while(!in_array($i, $used) && $i < count($values)) {
                $path[] = $values[$i++];
            };
            $found[$path_key] = join("/", $path);
        }
        // Sort, verify & output
        foreach(array_keys($keys) as $key) { $keys[$key] = $found[$key]; }
        if(join("/", $keys) == join("/", $values)) {
            return $keys;
        }
    };

    function makeLink($link_data, $pattern) {
    // ----------------------------------------
    // $link_data = <array> Read link data: [ "key" => "value", ... ]
    // $pattern = <string> Website link pattern, eg: @language/@path/
    // ----------------------------------------
    // RETURNS: <string> Link maching the pattern
    // ----------------------------------------
        if(!isset($link_data["@language"])) { $link_data["@language"] = $_SESSION["lang"]; };
        foreach(array_keys($link_data) as $key) {
            $pattern = preg_replace("/".$key."/", $link_data[$key], $pattern);
        };
        return $pattern;
    };

// ===================================================
//                      Sitemap
// ===================================================

    // ====== Get Sitemap / begin ======
    function getSitemap($navigation, $pages, $extension, $draft_extension = false) {
    // -------------------------------------------
    // $navigation = <array> Navigation XML array
    // $pages = <string> Pages folder path
    // $extension = <string> document type extension
    // $draft_extension = <string> draft file extension (optional)
    // -------------------------------------------
    // RETURNS: <array> Full site map tree
    // -------------------------------------------
        $pages_list = [];
        foreach($navigation["multi_page"] as $page) {
            $val = getVal($page);
            if($subfolder = getSitemapSubpages($val["href"], $pages, $extension, $draft_extension)) {
                $val["subfolder"] = $subfolder;
            }
            $pages_list[] = $val;
        };
        if(count($pages_list) > 0) { return $pages_list; };
    };
    // ====== Get Sitemap / end ======

    // ====== Get Sitemap Subpages (reqursive) / begin ======
    function getSitemapSubpages($folder_path, $pages, $extension, $draft_extension = false) {
    // -------------------------------------------
    // $folder_path = <string> Folder path
    // $pages = <string> Pages folder path
    // $extension = <string> document type extension
    // $draft_extension = <string> draft file extension (optional)
    // -------------------------------------------
    // RETURNS: <array> List of files based on .order file & specified extension
    // -------------------------------------------
        if(file_exists("$pages/$folder_path") && is_dir("$pages/$folder_path")) {
            $title_keys = "header title";
            $pages_list = [];
            // List by .order file
            $order_path = "$pages/$folder_path/.order";
            if($order = loadXml($order_path)) {
                foreach($order["multi_item"] as $item) {
                    $val = getVal($item);
                    $filename = getFilename($val["path"]);
                    $href = $folder_path."/".$filename;
                    if($file_path = getSitemapSubpagePath($filename, $folder_path, $pages, $extension, $draft_extension)) {
                        $val["href"] = $href;
                        if($subfolder = getSitemapSubpages($href, $pages, $extension, $draft_extension)) {
                            $val["subfolder"] = $subfolder;
                        }
                        $pages_list[] = $val;
                    }
                }
            }
            // List by directory
            else {
                $filenames = [];
                $extensions = [ $extension ];
                if($draft_extension) { $extensions[] = $draft_extension; }
                // By file
                foreach(listDir($folder_path, join(",", $extensions)) as $file) {
                    $filename = getFilename($file);
                    $href = "$folder_path/$filename";
                    if(
                        !in_array($filename, $filenames) &&
                        ($file_path = getSitemapSubpagePath($filename, $folder_path, $pages, $extension, $draft_extension)) &&
                        ($file_xml = loadXml($file_path, $draft_extension))
                    ) {
                        $title = readXml($file_xml, $title_keys);
                        if(!$title) { $title = capitalize(str_replace("_", " ", $filename)); };
                        $val = [
                            "href" => $href,
                            "title" => $title
                        ];
                        if($subfolder = getSitemapSubpages($href, $pages, $extension, $draft_extension)) {
                            $val["subfolder"] = $subfolder;
                        }
                        $pages_list[] = $val;
                        $filenames[] = $filename;
                    }
                }
            }
            if(count($pages_list) > 0) { return $pages_list; };
        }
    };
    // ====== Get Sitemap Subpages (reqursive) / end ======

    // ====== Get Sitemap Subpages Path / begin ======
    function getSitemapSubpagePath($filename, $folder_path, $pages, $extension, $draft_extension = false) {
    // -------------------------------------------
    // $filename = <string> Filename (no extension(s)!)
    // $folder_path = <string> Folder path
    // $pages = <string> Pages folder path
    // $extension = <string> document type extension
    // $draft_extension = <string> draft file extension (optional)
    // -------------------------------------------
    // RETURNS: <array> List of files based on .order file & specified extension
    // -------------------------------------------
        $file_path = "$pages/$folder_path/$filename.$extension";
        if($_SESSION['preview_mode'] && $draft_extension) {
            $draft_file = "$pages/$folder_path/$filename.$extension.$draft_extension";
            if(file_exists($draft_file)) { $file_path = $draft_file; };
        }
        // Output
        if($file_path && file_exists($file_path) && !is_dir($file_path)) {
            return $file_path;
        }
    };

    // ====== Get Out Map / begin ======
    function getOutmap($navigation, $pages, $extension, $draft_extension = false) {
    // -------------------------------------------
    // $navigation = <array> Navigation XML array
    // $pages = <string> Pages folder path
    // $extension = <string> document type extension
    // $draft_extension = <string> draft file extension (optional)
    // -------------------------------------------
    // RETURNS: <array> Outside site (navigation) pages tree
    // -------------------------------------------
        $title_tag = "header title";
        // Get in navigation list
        $in_navigation = [];
        foreach($navigation["multi_page"] as $page) { $in_navigation[] = readXml($page, "href"); };
        $offmap_list = [];
        $filenames = [];
        $extensions = [ $extension ];
        if($draft_extension) { $extensions[] = $draft_extension; };
        foreach(listDir($pages, join(",", $extensions)) as $file) {
            $filename = getFilename($file);
            $href = $filename;
            if(!in_array($filename, $in_navigation) && !in_array($filename, $filenames) && $file_xml = loadXml("$pages/$href.xml", $draft_extension)) {
                $title = readXml($file_xml, $title_tag);
                if(!$title) { $title = capitalize(str_replace("_", " ", $filename)); };
                $val = [
                    "href" => $href,
                    "title" => $title
                ];
                if($subfolder = getSitemapSubpages($href, $pages, $extension, $draft_extension)) {
                    $val["subfolder"] = $subfolder;
                }
                $offmap_list[] = $val;
                $filenames[] = $filename;
            }
        }
        return $offmap_list;
    };
    // ====== Get Out Map / end ======

    // ====== Get Pages Index (reqursive) / begin ======
    function getPagesIndex($sitemap) {
    // -------------------------------------------
    // $sitemap = <array> Full site map tree
    // -------------------------------------------
    // RETURNS: <array> Index of all site pages & subpages
    // -------------------------------------------
        $index = [];
        foreach(array_keys($sitemap) as $i) {
            $val = $sitemap[$i];
            $href = $val["href"];
            $index[$href] = $val;
            if(isset($val["subfolder"])) {
                $subfolder = $val["subfolder"];
                unset($index[$href]["subfolder"]);
                $index = array_replace($index, getPagesIndex($subfolder));
            }
        }
        return $index;
    };
    // ====== Get Sitemap Index (reqursive) / end ======

// ===================================================
//                Debuggging Tools
// ===================================================

    // ====== Array Listing / begin ======
    function arrayList($array, $depth = 0) {
    // ----------------------------------------
    // $array = <array>
    // $depth = <integer> display array level depth (optional)
    // ----------------------------------------
    // Display all array contents (with echo) for debugging
    // ----------------------------------------
        if(!is_numeric($depth)) { $depth = 0; };
        foreach(array_keys($array) as $k) {
            $n = 0;
            while($n++ < $depth) { echo "> "; };
            $val = $array[$k];
            if(is_array($val)) {
                echo "[$k] ...<br>\n";
                arrayList($val, ($depth + 1));
            }
            else {
                echo "[$k] : '$val'<br>\n";
            };
        };
    }; // ====== Array Listing / end ======

    // ====== xml list in html / begin ======
    function xmlList($xml) {
    // ----------------------------------------
    // $xml = <string> XML contents
    // ----------------------------------------
    // Shows XML contents
    // ----------------------------------------
        $xml = str_replace("<", "&#60;", $xml);
        $xml = str_replace(">", "&#62;", $xml);
        $xml = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $xml);
        foreach(explode("\n", $xml) as $txt) { echo "$txt<br>"; };
    }; // ====== xml list in html / end ======

?>