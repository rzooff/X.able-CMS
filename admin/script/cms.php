<?php
// ===================================================
//               X.able PHP CMS module
//           (C)2016 maciej@maciejnowak.com
// ===================================================
// compatibility: php4+
// build: 20161006
// ===================================================

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
            $log_list = array();
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
            $ini = array();
            $label = false;
            foreach(array_keys($file) as $n) {        
                $line = $file[$n];
                // ====== Navigation ======
                if($type == "navigation") {
                    if(substr($line, 0, 1) == ";") {} // comments;
                    elseif(substr($line, 0, 5) == "[nav:" && substr($line, strlen($line) -1, 1) == "]") { // section
                        if(count($items) > 0) {
                            $ini[$label] = $icon."@".join(";", $items);
                        };
                        $label = substr($line, 5, strlen($line) - 6);
                        $items = array();
                        $icon = "";
                    }
                    elseif($label != false && count(split("=", $line)) > 1) { // properties
                        $line = array_map("trim", split("=", $line));
                        $key = array_shift($line);
                        $val = join("=", $line);
                        if($key == "icon") { $icon = $val; }
                        elseif($key == "item") { $items[] = $val; };
                    };
                    // last line fix
                    if($n == (count($file) - 1) && $label != false && $label != "") {
                        $ini[$label] = $icon."@".join(";", $items);
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
                    elseif($label != false && count(split("=", $line)) > 1) {
                        $item = array_map("trim", split("=", $line));
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

    function getFirstPath($nav_documents, $root) {
    // --------------------------------
    // $nav_documents = <array> Navigation data from xable.ini
    // $root = <string> Site root path
    // --------------------------------
    // RETURNS: <string> First valid path from xable.ini navigation data
    // --------------------------------
        $first_path = false;
        foreach($nav_documents as $nav) {
            //echo "nav: $nav<br>\n";
            $nav = split("@", $nav);
            array_shift($nav); // Cut off icon data
            foreach(split(";", join("@", $nav)) as $dat) {
                if($first_path == false) {
                    $dat = $root."/".array_shift(split("\|", $dat));
                    if(path($dat, "filename") == "*" && file_exists(path($dat, "dirname")) && $listDir = listDir(path($dat, "dirname"), path($dat, "extension").",?")) {
                        $first_path = array_shift($listDir);
                        //echo "first: $first_path<br>\n";
                    }
                    elseif(file_exists($dat)) {
                        $first_path = $dat;
                        //echo "first: $first_path<br>\n";
                    };
                };
            };
        };
        //echo "first OUT: $first_path<br>\n";
        return $first_path;
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
            $_SESSION['admin_folder'] = array_pop(split("/", $url));
        }
        else {
            $_SESSION['admin_folder'] = array_pop(split("/", path($url, "dirname")));
        };
        // ====== Check USER / GROUP authorization ======
		$users_folder = "_users";
        $logged_user = $_SERVER['PHP_AUTH_USER'];
        if(is_string($logged_user)) {
            $groups = array_map("trim", file("$users_folder/.groups"));
            foreach($groups as $group) {
                if(substr($group, 0, 1) != ";" && count($group = split(":", $group)) == 2) {
                    $group_name = $group[0];
                    $group_users = split(" ", trim($group[1]));
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