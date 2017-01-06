<?php
// ===================================================
//               X.able PHP XML module
//           (C)2016 maciej@maciejnowak.com
// ===================================================
// compatibility: php4+
// build: 20160817
// ===================================================

    // ====== LIST files based on ORDER file / begin ======
    function listOrder($folder_path, $extension, $draft_extension) {
    // -------------------------------------------
    // $folder_path = <string>
    // $extension = <string>
    // $draft_extension = <string> draft file extension (optional)
    // -------------------------------------------
    // RETURNS: <array> List of files based on .order file & specified extension
    // -------------------------------------------
        $list = array();
        $name = array_pop(split("/", $folder_path));
        $file = "$folder_path/$name.order";
        if(file_exists($file)) {
            foreach(array_map("trim", file($file)) as $item) {
                if(
                    file_exists("$folder_path/$item.$extension") ||
                    ($_SESSION['preview_mode'] && file_exists("$folder_path/$item.$extension.$draft_extension"))
                  ) {
                    $list[] = "$item.$extension";
                };
            };
        };
        return $list;
    };
    // ====== LIST files based on ORDER file / end ======

// ===================================================
//                 Array <-> XML file
// ===================================================

    // ====== load XML data to array / begin ======
    function loadXml($file, $preview) {
    // ----------------------------------------
    // $file = <string> File path
    // $preview = <string> Preview file extension or <false/undefined> for no preview
    // ----------------------------------------
    // RETURNS: <array> XML data transformed into an array ([key] => ([0] => "val_1", [1] => "val_2)...)
    // ----------------------------------------
        if(is_string($preview) && $preview != "" && $_SESSION['preview_mode'] == $preview && file_exists("$file.$preview")) {
            $file = "$file.$preview";
            $_SESSION['preview_loaded'] = true;
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
    function arrayToXml($array, $depth) {
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

    // ====== easy READ XML data / begin ======
    function readXml($xml, $tags) {
    // ----------------------------------------
    // $xml = <array> XML data loaded into an array with loadXml()
    // $tags = <string> TAGS list, divided with spaces, eg: "article section"
    // ----------------------------------------
    // RETURNS: <string> data specified by tags or <array> if multiple data found
    // ----------------------------------------
        // Language
        
        $language = $_SESSION['lang'];
        if(!is_string($language)) { $language = "pl"; } // if no global lang setting
        // XML Array read
        $tags = split(" ", $tags);
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
                        elseif($type == "checkbox" || $type == "radio") {
                            //return $xml['selected'][0];
                            
                        }
                        elseif($type == "media") {
                            return $xml[$type][0][ $xml['set'][0] ][0];
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

// ===================================================
//                Debuggging Tools
// ===================================================

    // ====== Array Listing / begin ======
    function arrayList($array, $depth) {
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
        foreach(split("\n", $xml) as $txt) { echo "$txt<br>"; };
    }; // ====== xml list in html / end ======

?>