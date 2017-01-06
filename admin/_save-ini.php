<?php
    // Called in users.js
    session_start();
	require "script/functions.php";
	require "script/xml.php";

    //arrayList($_SESSION);
	
	$ini_folder = "_users"; // docelowo z danych w $SESSION

    // ====== Delete Group ======
	if(is_string($_GET['delete_group']) && $_GET['delete_group'] != "") {
        $delete = path($_GET['delete_group'], "filename");
        $groups = array();
        foreach(array_map("trim", file($_SESSION['groups_file'])) as $group) {
            $group_name = array_shift(split(":", $group));
            if($group_name != $delete) {$groups[$group_name] = $group; }
            else {
                echo "to delete -> $group_name<br>\n";
            };
        };
        arrayList($groups);
        echo "groups_file: ".$_SESSION['groups_file']."<br>\n";
        echo "ini_file: ".path($_SESSION['groups_file'], "dirname")."/".$_GET['delete_group']."<br>\n";
        
        if(safeSave($_SESSION['groups_file'], join("\n", $groups))) {
            unlink(path($_SESSION['groups_file'], "dirname")."/".$_GET['delete_group']);
            header("Location: xable_users.php");
        }
        else {
			echo "SAVING ERROR!<hr>\n";
			arrayList($_POST);
        }
        
	}
    // ====== Create Group / Save new INI ======
	elseif($_POST['accept'] == "accept" && $_POST['file'] != "" && $_POST['content'] != "") {
		$content = $_POST['content'];
		$file = $_POST['file'];
        $file = str_replace(" ", "_", $file);
		if(safeSave("$ini_folder/$file", $content)) {
			header("Location: xable_users.php");
		}
		else {
			echo "SAVING ERROR!<hr>\n";
			arrayList($_POST);
		};
	}
	else {
		echo "DATA ERROR!<hr>\n";
		arrayList($_POST);
	};
    echo "<a href='xable_users.php'>USERS</a>\n";
?>