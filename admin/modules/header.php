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
        list($time, $doc, $action, $user) = split(";", $log);
        $log_check[$doc] = $log;
    };

    foreach($log_check as $log) {
        list($time, $doc, $action, $user) = split(";", $log);
        $title = str_replace("_", " ", ucfirst(path($doc, "filename")));
        $time = str_replace(" ", ", ", $time);
        if($action == "draft saved" && $user != $current_user) {
            $notifications[$time] = "<li class='action new_edit manual' path='$doc' help='<span class=\"small_info\">$time</span><br><b>$doc</b><br>$user'>Edytowany: &quot;$title&quot;</li>\n";
        }
        elseif($site_options['draft_support'] == "false" && $action == "published" && $user != $current_user) {
            $notifications[$time] = "<li class='action new_edit manual' path='$doc' help='<span class=\"small_info\">$time</span><br><b>$doc</b><br>$user'>Edytowany: &quot;$title&quot;</li>\n";
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
		$zip_date = array_pop(split("_", $zip_name));
		$zip_date = substr($zip_date, 0, 8);
		if(intval($zip_date) > $backup_date) { $backup_date = intval($zip_date); };
	};
	
	if($backup_date < 0) {
		echo "\n<!-- backup_age: NONE -->\n";
        $notifications['backup'] =  "<li class='action old_backup manual' help='<span class=\"light_info\">Ostatnia archiwizacja:</span> <b>BRAK</b>'>Strona nie ma jeszcze backupu</li>";
	}
	else {
		$backup_age = dateToDays($current_date) - dateToDays($backup_date);
		echo "\n<!-- backup_age: ".$backup_age." days -->\n";
		if($backup_age > 31) {
            $backup_date = substr($backup_date, 0, 4)."-".substr($backup_date, 4, 2)."-".substr($backup_date, 6, 2);
            $notifications['backup'] = "<li class='action old_backup manual' help='<span class=\"light_info\">Ostatnia archiwizacja:</span> <b>$backup_date</b>'>Backup jest starszy niż 30 dni</li>";
		};
	};
    
    // ====== Info ======
    echo "\t\t\t\t<div id='menu_notifications' class='pull_down'><span class='fi-calendar'></span><ul>";
    if(count($notifications) > 0) { echo "<p class='count_marker'>".count($notifications)."</p>"; };
    echo "<li class='user_info show_log'><em>Powiadomienia: </em> ".path($log_file, "basename")."</li>";
    foreach($notifications as $log) { echo $log; };
    echo "</ul></div>\n";

    //echo "<p id='check_notifications></p>\n";


    // ===============================
    //            User menu
    // ===============================

    echo "\t\t\t\t<div id='menu_user' class='pull_down'><span class='fi-torso-business'></span><ul>".
        "<li class='user_info'><em>Użytkownik:</em> ".$_SESSION['logged_group'].".".$_SESSION['logged_user']."</li>".
        "<li class='action user_password'>Zmień hasło</li>".
        "<li class='action user_logout'>Wyloguj</li>".
        "</ul></div>\n";


    // ===============================
    //          Edit Buttons
    // ===============================

    if($site_options['draft_support'] == "false") { $edit_buttons = "basic"; }; // simple buttons mode only

    echo "\t\t\t\t<div class='buttons'>\n";
    if($edit_buttons == "full") {
        echo "\t\t\t\t\t<button class='cancel' help='Anuluj aktualne zmiany'>Anuluj</button>\n";
        echo "\t\t\t\t\t<button class='save draft_save' name='save_mode' value='draft' type='submit' help='Zapisz zmiany'>Zapisz</button>\n";
        echo "\t\t\t\t\t<button class='publish icon publisher_only' name='save_mode' value='publish' type='submit' help='Publikuj'><span class='fi-check'></span></button>\n";
        echo "\t\t\t\t\t<button class='discard icon publisher_only' name='save_mode' value='discard' type='submit' help='Odrzuć nieopublikowane zmiany'><span class='fi-trash'></span></button>\n";
        echo "\t\t\t\t\t<button class='unpublish icon publisher_only' name='save_mode' value='unpublish' type='submit' help='Cofnij publikację'><span class='fi-prohibited'></span></button>\n";
        echo "\t\t\t\t\t<button class='revert icon publisher_only' name='save_mode' value='revert' type='submit' help='Przywróć poprzednią wersję'><span class='fi-rewind'></span></button>\n";
        
        // info-spot
        $help = array(
            "<span class=\"title fi-rewind\"> Przywróć poprzednią wersję</span><br>Poprzednia wersja stanie się na powrót wersją opublikowaną.<br>Aktualna wersja publiczna stanie się wersją roboczą (widoczną w edytorze).<br>Jeśli wcześniej istniała już wersja robocza, to zostanie ona nadpisana.<br>Niezapisane zmiany zostaną anulowane.",
            "<span class=\"title fi-prohibited\"> Cofnij publikację</span><br>Aktualna wersja publiczna stanie się wersją roboczą (widoczną w edytorze).<br>Jeśli wcześniej istniała już wersja robocza, to zostanie ona nadpisana.<br>Dokument nie będzie posiadał wersji widocznej na stronie.<br>Niezapisane zmiany zostaną anulowane.",
            "<span class=\"title fi-trash\"> Odrzuć nieopublikowane zmiany</span><br>Usunięta zostanie wersja robocza, wraz z ewentualnymi nowymi plikami.<br>Niezapisane zmiany zostaną anulowane.",
            "<span class=\"title fi-check\"> Publikuj</span><br>Zapisz / zatwierdź wszystkie zmiany i publikuj na stronie.",
            "<span class=\"title\">Zapisz</span><br>Zmiany zostaną zapisane w wersji roboczej, niewidocznej na stronie.",
            "<span class=\"title\">Anuluj</span><br>Odrzuć wszystkie niezapisane zmiany.",
            "<d>Dostęp do poszczególnych opcji zależy od uprawnień danego użytkownika</d>"
            );
        echo "\t\t\t\t\t<i class='fi-info manual help publisher_only' help='".join("<br><br>", $help)."'></i>\n";

    }
    elseif($edit_buttons == "essential") {
        echo "\t\t\t\t\t<button class='cancel' help='Anuluj niezapisane zmiany'>Anuluj</button>\n";
        echo "\t\t\t\t\t<button class='save hard_save' name='save_mode' value='publish' type='submit' help='Zapisz i publikuj  zmiany'>Publikuj</button>\n";
        echo "\t\t\t\t\t<button class='undo icon publisher_only' name='save_mode' value='swap' type='submit' help='Przywróć poprzednią wersję'><span class='fi-rewind'></span></button>\n";
        
        // info-spot
        $help = array(
            "<span class=\"title fi-rewind\"> Przywróć poprzednią wersję</span><br>Poprzednia wersja stanie się na powrót wersją opublikowaną.<br>Aktualna wersja publiczna stanie się wersją roboczą (widoczną w edytorze).<br>Jeśli wcześniej istniała już wersja robocza, to zostanie ona nadpisana.",
            "<span class=\"title\">Publikuj</span><br>Zapisz zmiany i publikuj na stronie.",
            "<span class=\"title\">Anuluj</span><br>Odrzuć wszystkie niezapisane zmiany.",
            "<d>Dostęp do poszczególnych opcji zależy od uprawnień danego użytkownika</d>"
            );
        echo "\t\t\t\t\t<i class='fi-info manual help publisher_only' help='".join("<br><br>", $help)."'></i>\n";
    }
    else { // basic
        echo "\t\t\t\t\t<button class='cancel' help='Anuluj niezapisane zmiany'>Anuluj</button>\n";
        echo "\t\t\t\t\t<button class='save hard_save' name='save_mode' value='publish' type='submit' help='Zapisz i publikuj  zmiany'>Publikuj</button>\n";
        $help = array(
            "<span class=\"title\">Publikuj</span><br>Zapisz zmiany i publikuj na stronie.",
            "<span class=\"title\">Anuluj</span><br>Odrzuć wszystkie niezapisane zmiany."
            );
        //echo "\t\t\t\t\t<i class='fi-info manual help' help='".join("<br><br>", $help)."'></i>\n";
    };
    echo "\t\t\t\t</div>\n";

    // ========================

    echo "\t\t\t</header>\n";

?>