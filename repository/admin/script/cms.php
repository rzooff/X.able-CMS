<?php
// ===================================================
//               X.able PHP CMS module
//           (C)2016 maciej@maciejnowak.com
// ===================================================
// compatibility: php4+
// build: 20161006
// ===================================================

    function getCmsUrl() {
    // -----------------------------------
    // RETURNS: <string> CMS directory URL
    // -----------------------------------
        return $_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF'])."/";
    };

// ===================================================
//                     LOCALIZE
// ===================================================
        
    function localize($key) {
    // -----------------------------------
    // $key = <string>
    // -----------------------------------
    // RETURNS: <string> Localized text
    // -----------------------------------
        $val = $_SESSION['localize'][$key];
        if(is_string($val) && $val != "") {
            return $val;
        }
        else {
            return $key;
        }
    };

    function localizeJs($key) {
    // -----------------------------------
    // $key = <string>
    // -----------------------------------
    // RETURNS: <string> With special chars converted from HTML/BBCode to UTF-8
    // -----------------------------------
        if(is_string($key)) {
            return str_replace("&#39;", "\u0027", localize($key));
        }
    };

    function exportLocalization() {
    // -----------------------------------
    // Export localization data to js
    // -----------------------------------
        echo "\n";
        echo "\t\t<script>\n";
        echo "\t\t\tvar LOCALIZE = { ";
        foreach(array_keys($_SESSION['localize']) as $key) {
            $translation = $_SESSION['localize'][$key];
            $translation = str_replace("\"", "\\\"", $translation);
            $translation = str_replace("&#39;", "\u0027", $translation);
            //echo "\t\t\t\t\"".$key."\": \"".$translation."\",\n";
            echo "\"".$key."\": \"".$translation."\", ";
        }
        echo "\t\t\t};\n";
        echo "\t\t</script>\n";
    };

    function loadLocalization($folder) {
    // -----------------------------------
    // $folder = <string> localization folder path
    // -----------------------------------
    // Reset & Load localization data to $_SESION variables
    // -----------------------------------
        $_SESSION['localize'] = [];
        $_SESSION['dictionary'] = [];

        foreach(listDir($folder, "xml") as $file) {
            $xml = loadXml("$folder/$file");
            foreach($xml['multi_sentence'] as $sentence) {
                $key = $sentence['key'][0]['string'][0];
                $translation = $sentence['translation'][0]['text'][0][ $_SESSION['admin_lang'] ][0];
                if(strstr($key, "html")) {
                    $translation = str_replace("&gt;", ">", $translation);
                    $translation = str_replace("&lt;", "<", $translation);
                    $translation = str_replace("\\\"", "\"", $translation);
                }
                $translation = str_replace("'", "&#39;", $translation);
                $translation = str_replace("[br]", "<br>", $translation);
                if(substr($file, 0 , 1) == "_") {
                    if(trim($translation) == "") { $translation = $key; };
                    $_SESSION['dictionary'][] = $key.";".$translation;
                }
                else {
                    $_SESSION['localize'][$key] = $translation;
                };
            };
        };
    };

// ===================================================
//                        LOG
// ===================================================

    function addLog($action, $filepath) {
    // -----------------------------------
    // $action = <string>
    // $filepath = <string>
    // -----------------------------------
    // Add saving changes info to zable.log file -> time, action, document path, group.user
    // -----------------------------------
        $ini_pathes = loadIni("xable.ini", "pathes");
        $log_file = $ini_pathes['log'];
        $log = array(
                date("Y.m.d G:i:s"),    
                $filepath,
                $action,
                $_SESSION['logged_group'].".".$_SESSION['logged_user']
            );
        if(file_exists($log_file)) {
            $log_list = array_map("trim", file($log_file));
            $title_row = array_shift($log_list);
        }
        else {
            $log_list = [];
            $title_row = "Czas;Dokument;Akcja;Użytkownik";
        };
        // Log max length
        $ini_pathes = loadIni("xable.ini", "options");
        $log_max = $ini_pathes['log_max'];
        if(!is_numeric($log_max) && count($log_list) > $log_max) {
            $log_list = array_reverse(array_slice(array_reverse($log_list), 0, $log_max));
        };
        // Output
        $log_list[] = join(";", $log);
        array_unshift($log_list, $title_row);
        safeSave($log_file, join("\n", $log_list));
    };

// ===================================================
//                        INI
// ===================================================

    function loadIni($path, $type) {
    // --------------------------------
    // $path = <string> ini file path
    // $type = <string> data type to read: "navigation", "pathes"
    // --------------------------------
    // RETURNS" <array> CMS content data
    // --------------------------------
        if(file_exists($path)) {
            
            $file = array_map("trim", file($path));
            $ini = [];
            $label = false;
            foreach(array_keys($file) as $n) {        
                $line = trim($file[$n]);
                // ====== Navigation ======
                if($type == "navigation") {
                    if(substr($line, 0, 1) == ";" || $line == "") {} // comments;
                    elseif(preg_match("/^\[nav:(.*?)\]$/i", $line, $match)) { // "[nav:<label>]"
                        $label = $match[1];
                        $ini[$label] = [];
                    }
                    elseif(is_array($ini[$label]) && count(explode("=", $line)) > 1) {
                        $line = array_map("trim", explode("=", $line));
                        $key = array_shift($line);
                        $val = join("=", $line);
                        if(in_array($key, array( "item", "type" ))) {
                            $keys = $key."s";
                            if(!is_array($ini[$label][$keys])) { $ini[$label][$keys] = []; };
                            $ini[$label][$keys][] = $val;
                        }
                        else {
                            $ini[$label][$key] = $val;
                        }
                    };
                }
                // ====== Other ======
                else {
                    if(substr($line, 0, 1) == ";") {} // comments;
                    elseif(strtolower($line) == "[".strtolower($type)."]") { // matching section
                        $label = true;
                    }
                    elseif(substr($line, 0, 1) == "[" && substr($line, strlen($line) -1, 1) == "]") { // other section label
                        $label = false;
                    }
                    elseif($label != false && count(explode("=", $line)) > 1) {
                        $item = array_map("trim", explode("=", $line));
                        $key = array_shift($item);
                        $val = join("=", $item);
                        if($type == "hidden") {
                            $ini[] = $val;
                        }
                        else {
                            $ini[$key] = $val;
                        };
                    };
                };
                
            };
            //arrayList($ini);
            return $ini;
        }
        else {
            return false;
        };
    };

// ===================================================
//                    AUTHORIZATION
// ===================================================

    // ====== USER & GROUP authorization / begin ======
    function authorizedIniFile() {
    // ---------------------------------------------
    // Check loggen user authorization & group membership.
    // Put Logged user, group & ini file path into a $_SESSION array
    // ---------------------------------------------
    // RETURNS: <string> valid ini file path or empty string in none found
    // ---------------------------------------------
        // ====== Xable admin panel folder ======
        $url = $_SERVER['REQUEST_URI'];
        if(substr($url, strlen($url) - 1) == "/") { $url = substr($url, 0, strlen($url) - 1); };
        if(!is_string(path($url, "extension")) || path($url, "extension") == "") {
            $_SESSION['admin_folder'] = array_pop(explode("/", $url));
        }
        else {
            $_SESSION['admin_folder'] = array_pop(explode("/", path($url, "dirname")));
        };
        // ====== Check USER / GROUP authorization ======
		$users_folder = "_users";
        $logged_user = $_SERVER['PHP_AUTH_USER'];
        if(is_string($logged_user)) {
            $groups = array_map("trim", file("$users_folder/.groups"));
            foreach($groups as $group) {
                if(substr($group, 0, 1) != ";" && count($group = explode(":", $group)) == 2) {
                    $group_name = $group[0];
                    $group_users = explode(" ", trim($group[1]));
                    if(in_array($logged_user, $group_users)) { $logged_group = $group_name; };
                };
            };
            // ====== OUTPUT ======
            if(file_exists("$users_folder/$logged_group.ini")) {
                $_SESSION['logged_user'] = $logged_user;
                $_SESSION['logged_group'] = $logged_group;
                $_SESSION['ini_file'] = "$users_folder/$logged_group.ini";
            }
            else {
                $_SESSION['logged_user'] = $logged_user;
                $_SESSION['logged_group'] = "none";
                $_SESSION['ini_file'] = "xable.ini";
            };
        }
        else {
            $_SESSION['logged_user'] = "unauthorized";
            $_SESSION['logged_group'] = "none";
            $_SESSION['ini_file'] = "";
        };
        return $_SESSION['ini_file'];
    };
    // ====== USER & GROUP authorization / end ======

?>