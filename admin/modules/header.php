<?php

    echo "\t\t\t<header>\n";

    // ===============================
    //          Notifications
    // ===============================

    $notifications = array();

    // ====== Not published documents ======
    $log_file = $ini_pathes['log'];
    $log_list = array_map("trim", file($log_file));
    $log_titles = array_shift($log_list);
    $current_user = $_SESSION['logged_group'].".".$_SESSION['logged_user'];

    $log_check = array();
    foreach($log_list as $log) {
        list($time, $doc, $action, $user) = explode(";", $log);
        $log_check[$doc] = $log;
    };

    foreach($log_check as $log) {
        list($time, $doc, $action, $user) = explode(";", $log);
        
        if(path($doc, "extension") == "order") {
            $done = localize("order-change");
            $title = path(path($doc, "dirname"), "filename");
        }
        else {
            $done = localize("status-edited");
            $title = path($doc, "filename");
        };
        $title = str_replace("_", " ", ucfirst($title));
        
        $time = str_replace(" ", ", ", $time);
        if($action == "INSTALL") {} // No install/update info!
        elseif($action == "draft saved" && $user != $current_user) {
            $notifications[$time] = "<li class='action new_edit manual' path='$doc' help='<span class=\"small_info\">$time</span><br><b>$doc</b><br>$user'>".$done.": &quot;$title&quot;</li>\n";
        }
        elseif($_SESSION['ini_site_options']['draft_support'] == "false" && $action == "published" && $user != $current_user) {
            $notifications[$time] = "<li class='action new_edit manual' path='$doc' help='<span class=\"small_info\">$time</span><br><b>$doc</b><br>$user'>".$done.": &quot;$title&quot;</li>\n";
        };
    };
    krsort($notifications); // newest changes on top

    // ====== Valid Backup ======
    $to_backup == false;
	function dateToDays($date) {
		$Y = intval(substr($date, 0, 4));
		$m = intval(substr($date, 4, 2));
		$d = intval(substr($date, 6, 2));
		return ($Y * 12 + $m) * 30 + $d;
	};

	$current_date = date("Ymd");
	$backup_folder = $ini_pathes['backup'];
	$backup_date = -1;
	foreach(listDir($backup_folder, "zip,?") as $zip_path) {
		$zip_name = path($zip_path, "filename");
		$zip_date = array_pop(explode("_", $zip_name));
		$zip_date = substr($zip_date, 0, 8);
		if(intval($zip_date) > $backup_date) { $backup_date = intval($zip_date); };
	};
	
	if($backup_date < 0) {
		echo "\n<!-- backup_age: NONE -->\n";
        $notifications['backup'] =  "<li class='action old_backup manual' help='<span class=\"light_info\">".localize("last-backup").":</span> <b>".localize("none-label")."</b>'>".localize("no-backup")."</li>";
	}
	else {
		$backup_age = dateToDays($current_date) - dateToDays($backup_date);
		echo "\n<!-- backup_age: ".$backup_age." days -->\n";
		if($backup_age > 31) {
            $backup_date = substr($backup_date, 0, 4)."-".substr($backup_date, 4, 2)."-".substr($backup_date, 6, 2);
            $notifications['backup'] = "<li class='action old_backup manual' help='<span class=\"light_info\">".localize("last-backup").":</span> <b>$backup_date</b>'>".localize("30-days-without-backup")."</li>";
		};
	};
    
    // ====== Info ======
    
    echo "\t\t\t\t<div id='menu_notifications' class='pull_down'><span class='fi-calendar'></span><ul>";
    if(count($notifications) > 0) { echo "<p class='count_marker'>".count($notifications)."</p>"; };
    echo "<li class='user_info show_log'><em>".localize("notifications-label").": </em> ".path($log_file, "basename")."</li>";
    foreach($notifications as $log) { echo $log; };
    echo "</ul></div>\n";

    // ===============================
    //            User menu
    // ===============================

    echo "\t\t\t\t<div id='menu_user' class='pull_down'><span class='fi-torso-business'></span><ul>".
        "<li class='user_info'><em>".localize("user-label").":</em> ".$_SESSION['logged_group'].".".$_SESSION['logged_user']."</li>".
        "<li class='action user_password'>".localize("password-change")."</li>".
        "<li class='action user_logout'>".localize("logout-label")."</li>".
        "</ul></div>\n";

    // ===============================
    //          Edit Buttons
    // ===============================

    if($_SESSION['ini_site_options']['draft_support'] == "false") { $edit_buttons = "basic"; }; // simple buttons mode only

    echo "\t\t\t\t<div class='buttons'>\n";
    if($edit_buttons == "full") {
        echo "\t\t\t\t\t<button class='cancel' help='".localize("cancel-changes")."'>".localize("cancel-label")."</button>\n";
        echo "\t\t\t\t\t<button class='save draft_save' name='save_mode' value='draft' type='submit' help='".localize("save-changes")."'>".localize("save-label")."</button>\n";
        echo "\t\t\t\t\t<button class='publish icon publisher_only' name='save_mode' value='publish' type='submit' help='".localize("publish-changes")."'><span class='fi-check'></span></button>\n";
        echo "\t\t\t\t\t<button class='discard icon publisher_only' name='save_mode' value='discard' type='submit' help='".localize("discard-changes")."'><span class='fi-trash'></span></button>\n";
        echo "\t\t\t\t\t<button class='unpublish icon publisher_only' name='save_mode' value='unpublish' type='submit' help='".localize("unpublish-changes")."'><span class='fi-prohibited'></span></button>\n";
        echo "\t\t\t\t\t<button class='revert icon publisher_only' name='save_mode' value='revert' type='submit' help='".localize("revert-previous")."'><span class='fi-rewind'></span></button>\n";
        
        // info-spot
        echo "\t\t\t\t\t<i class='fi-info manual help publisher_only' help='".localize("preview-mode-full-html")."'></i>\n";

    }
    elseif($edit_buttons == "essential") {
        echo "\t\t\t\t\t<button class='cancel' help='".localize("cancel-changes")."'>".localize("cancel-label")."</button>\n";
        echo "\t\t\t\t\t<button class='save hard_save' name='save_mode' value='publish' type='submit' help='".localize("publish-changes")."'>".localize("publish-label")."</button>\n";
        echo "\t\t\t\t\t<button class='undo icon publisher_only' name='save_mode' value='swap' type='submit' help='".localize("revert-previous")."'><span class='fi-rewind'></span></button>\n";
        
        // info-spot
        echo "\t\t\t\t\t<i class='fi-info manual help publisher_only' help='".localize("preview-mode-simple-html")."'></i>\n";
    }
    else { // basic
        echo "\t\t\t\t\t<button class='cancel' help='".localize("cancel-changes")."'>".localize("cancel-label")."</button>\n";
        echo "\t\t\t\t\t<button class='save hard_save' name='save_mode' value='publish' type='submit' help='".localize("publish-changes")."'>".localize("publish-label")."</button>\n";
        // info-spot
        //echo "\t\t\t\t\t<i class='fi-info manual help' help='".localize("basic-mode-html")."'></i>\n";
    };
    echo "\t\t\t\t</div>\n";

    // ========================

    echo "\t\t\t</header>\n";

?>