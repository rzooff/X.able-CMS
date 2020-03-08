<?php
    // ======================================
    //              ><.able CMS
    //      (C)2015-2019 maciejnowak.com
    // ======================================
    // compatibile: php5.4+ or higher

    //error_reporting(E_ALL);

    require("modules/_session-start.php");

    $panel_name = "users";
    $panel_label = localize("users-groups-label");

    $_SESSION['password_file'] = $ini_pathes['passwords'];
    $_SESSION['groups_file'] = "_users/.groups";

	// ====== Groups update ======
	$groups = array();
	foreach(array_map("trim", file($_SESSION['groups_file'])) as $group) {
		$group_name = array_shift(explode(":", $group));
		$groups[$group_name] = $group;
	};

    // Auto update
	$update_flag = false;
	foreach(listDir(path($_SESSION['groups_file'], "dirname"), "ini") as $ini_file) {
		$group = path($ini_file, "filename");
		if(!$groups[$group]) {
			//echo "->brakuje: $group<br>\n";
			$groups[$group] = $group.":";
			$update_flag = true;
		};
	};
	if($update_flag) {
        ksort($groups);
		safeSave($_SESSION['groups_file'], join("\n", $groups));
	};
	
    function changeUserPassword($login, $password) {
        $pass_content = array_map("trim", file($_SESSION['password_file']));
        foreach(array_keys($pass_content) as $num) {
            if(array_shift(explode(":", $pass_content[$num])) == $login) {
                $pass_content[$num] = $login.":".crypt( $password, "mn");
            };
        };
        return safeSave($_SESSION['password_file'], join("\n", $pass_content));
    };

	function addUserPassword($login, $password) {
        $pass_content = array_map("trim", file($_SESSION['password_file']));
        $pass_content[] = $login.":".crypt( $password, "mn");
        return safeSave($_SESSION['password_file'], join("\n", $pass_content));
    };

    function removeUserPassword($login) {
        $pass_content = array_map("trim", file($_SESSION['password_file']));
        foreach(array_keys($pass_content) as $num) {
            if(array_shift(explode(":", $pass_content[$num])) == $login) {
                unset($pass_content[$num]);
            };
        };
        return safeSave($_SESSION['password_file'], join("\n", $pass_content));
    };

    function addUserToGroup($login, $group) {
        $user_added = false;
        $groups = array_map("trim", file($_SESSION['groups_file']));
        foreach(array_keys($groups) as $num) {
            $group_data = $groups[$num];
            $group_name = trim( array_shift(explode(":", $groups[$num])));
            if($group == $group_name) {
                $groups[$num] = $group_data." ".$login;
                $user_added = true;
            };
        };
        return (safeSave($_SESSION['groups_file'], join("\n", $groups)) && $user_added);
    };

    function removeUserFromGroups($login) {
        $user_deleted = false;
        $groups = array_map("trim", file($_SESSION['groups_file']));
        foreach(array_keys($groups) as $num) {
            $group = explode(":", $groups[$num]);
            $group_name = trim($group[0]);
            $users = array_map("trim", explode(" ", trim($group[1])));
            foreach(array_keys($users) as $i) {
                if($users[$i] == $login) {
                    unset($users[$i]);
                    $user_deleted = true;
                };
            };
            $groups[$num] = $group_name.": ".join(" ", $users);
        };
        return (safeSave($_SESSION['groups_file'], join("\n", $groups)) && $user_deleted);
    };

    function loginExists($login) {
        $login_exists = false;
        foreach(array_map("trim", file($_SESSION['password_file'])) as $login_data) { if(substr($login_data, 0, strlen($_POST['login'])) == $_POST['login']) { $login_exists = true; }; };
        return $login_exists;
    };

    function getUserPassword($login) {
        $password = false;
        $pass_content = array_map("trim", file($_SESSION['password_file']));
        foreach(array_keys($pass_content) as $num) {
            $pass = explode(":", $pass_content[$num]);
            if($pass[0] == $login) {
                $password = $pass[1];
            };
        };
        return $password;
    };
	
	if($_GET['action'] == "new_user") {
        if(!loginExists($_POST['login'])) {
            addUserPassword($_POST['login'], $_POST['password']);
            if($_POST['group'] != "" && $_POST['group'] != "*none*") {
                addUserToGroup($_POST['login'], $_POST['group']);
            };
        };
	}
	elseif($_GET['action'] == "delete_user") {
        $user = $_POST['login'];
        $dev_group = explode(" ", $groups['dev']);
        array_shift($dev_group);
        // Last user protection
        if(in_array($user, $dev_group) && count($dev_group) == 1) {
            echo "<script> alert(".localizeJs("last-dev-alert")."); </script>";
        }
        else {
            removeUserPassword($_POST['login']);
            removeUserFromGroups($_POST['login']);
        };
	}
	elseif($_GET['action'] == "change_group") {
		removeUserFromGroups($_POST['login']);
        if($_POST['group'] != "" && $_POST['group'] != "*none*") {
            addUserToGroup($_POST['login'], $_POST['group']);
        };
        if($_POST['login'] == $_SESSION['logged_user']) {
            echo "<script> alert('".localizeJs("group-change-info")."'); </script>\n";
        }
	}
    elseif($_GET['action'] == "change_password") {
        changeUserPassword($_POST['login'], $_POST['new_password']);
    }
    elseif($_GET['action'] == "test_password") {
        $pass = getUserPassword($_POST['login']);
        if(getUserPassword($_POST['login']) == crypt( $_POST['current_password'], "mn" )) {
            echo "<script> alert('".localizeJs("password-corrent-info")." :)'); </script>";
        }
        else {
            echo "<script> alert('".localizeJs("password-wrong-info")."'); </script>";
        };
    };

?>

<!doctype html>
<html>
	<?php require("modules/xable_head.php"); ?>
	<body>
        <main class='<?php echo $panel_name; ?>'>

            <?php
    
                require("modules/xable_nav.php");
            
                $users_groups = array();
                $group_names = array();
                $groups_content = array_map("trim", file($_SESSION['groups_file']));
                sort($groups_content);
                foreach($groups_content as $group) {
                    $group = explode(":", $group);
                    $group_name = trim($group[0]);
                    $group_users = explode(" ", trim($group[1]));
                    foreach(explode(" ", trim($group[1])) as $user) { $users_groups[$user] = $group_name; };
                    $group_names[] = $group_name;
                };
                $group_names[] = "*none*";

                echo "<input type='hidden' id='users' value='".join(" ", array_keys($users_groups))."'>\n";
                //echo "<input type='hidden' id='updated_login' value='".$_POST['login']."'>\n";

                //arrayList($users_groups);
                $pass_content = array_map("trim", file($_SESSION['password_file']));
                sort($pass_content);
                echo "<table>\n";
                foreach($pass_content as $user) {
                    $user = explode(":", $user);
                    $user_login = trim($user[0]);
                    $user_pass = trim($user[1]);
                    $user_group = $users_groups[$user_login];
                    if(!is_string($user_group)) { $user_group = "*none*"; };
                    if($user_login == $_POST['login']) { $updated = "class='updated'"; } else { $updated = ""; };
                    
                    echo "<tr $updated>\n".
                        "\t<td><span class='icon fi-torso-business'></span><span class='login'>$user_login</span></td>\n".
                        //"\t<td><input type='hidden' class='password' value='$user_pass'></td>\n".
                        "\t<td><select class='group'>";
                    foreach($group_names as $group) {
                        if($group == $user_group) { $selected = "selected"; } else { $selected = ""; };
                        echo "<option value='$group' $selected>$group</option>";
                    };
                    echo "</select></td>\n".
                        "\t<td><button class='change_password'>".localize("password-label")."</button></td>\n".
                        "\t<td><button class='delete_user'>".localize("delete-label")."</button></td>\n".
                        "</tr>\n";
                };
                echo "</table>\n"                
            ?>

            <button class='new_article new_user'>+</button>

            <div id='popup_container'>
                <div id='new_user' class='popup'>
                    <nav>
                        <p><?php echo localize("new-user"); ?></p>
                        <div class='buttons'>
                            <button class='cancel'><span class='fi-x'></span></button>
                        </div>
                    </nav>
                    <form method='post'>
                        <p><?php echo localize("login-label"); ?>:</p>
                        <input type='text' class='login' >
                        <p><?php echo localize("group-label"); ?>:</p>
                        <select class='group'>
                        <?php foreach($group_names as $group) { echo "<option value='$group'>$group</option>"; }; ?>
                        </select>
                        <p><?php echo localize("password-label"); ?>:</p>
                        <input type='password' class='password'>
                        <p><?php echo localize("repeat-password"); ?>:</p>
                        <input type='password' class='repeat'>
                        <button class='confirm'><?php echo localize("create-label"); ?></button>
                    </form>
                </div>
                <div id='change_password' class='popup'>
                    <nav>
                        <p><?php echo localize("password-label"); ?></p>
                        <div class='buttons'>
                            <button class='cancel'><span class='fi-x'></span></button>
                        </div>
                    </nav>
                    <form method='post'>
                        <p><?php echo localize("login-label"); ?>:</p>
                        <input type='text' class='login' disabled>
                        <hr>
                        <p><?php echo localize("password-label"); ?>:</p>
                        <input type='password' class='current_password'>
                        <button class='test'><?php echo localize("password-test"); ?></button>
                        <hr>
                        <p><?php echo localize("new-password")." (".localize("password-requirements").")"; ?>:</p>
                        <input type='password' class='new_password'>
                        <p><?php echo localize("new-password-confirm"); ?>:</p>
                        <input type='password' class='new_repeat'>
                        <button class='confirm'><?php echo localize("password-change"); ?></button>
                    </form>
                </div>
				<form id='delete_user' action='xable_users.php?action=delete_user' method='post'>
					<input type='hidden' class='login'>
				</form>
				<form id='change_group' action='xable_users.php?action=change_group' method='post'>
					<input type='hidden' class='login'>
					<input type='hidden' class='group'>
				</form>
            </div>
			
        </main>
        <aside>
            <div id='code'>
                <?php
                    $main_ini = "../xable.ini";
                    echo "<span id='main_ini' class='ini fi-home' value='$main_ini'></span><br>\n";
                
					$ini_content;
                    echo "<br><span class='flag'><hr>.htpasswd<hr></span><br>\n";
                    foreach($pass_content as $pass) {
                        $pass = explode(":", $pass);
                        if($_SESSION['logged_user'] == "rzooff") {
                            echo $pass[0]."<span class='tag'>#:".substr($pass[1], 2)."</span>";
                        }
                        else {
                            echo $pass[0]."<span class='tag'>:&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;</span>";
                        };
                        echo "<br>\n";
                    };
                    echo "<br><span class='flag'><hr>.groups<hr></span><br>\n";
                    foreach($groups_content as $group) {
                        echo preg_replace("/:(.*?)$/", "<span class='tag'>:$1</span>", $group);
                        echo "<br>\n";
                    };
                    echo "<br><span class='flag'><hr>".localize("files-in-users")." (".localize("click-to-edit").")<hr></span><br>\n";
                    echo "<span class='tag'>\n";
					$n = 0;
                    $ini_list = listDir("_users", "ini");
                    $ini_list[] = $main_ini;
                    foreach($ini_list as $file) {
                        if($file != $main_ini) {
						  echo "<span class='group'><span class='flag'>".++$n." .</span> <span class='ini' value='$file'>$file</span> <span class='delete'>[x]</span></span><br>\n";
                        };
                        $content = array_map("trim", file("_users/$file"));
                        foreach(array_keys($content) as $i) {
                            $line = $content[$i];
                            $line = str_replace("<", "&lt;", $line);
                            $line = str_replace(">", "&gt;", $line);
                            $content[$i] = $line;
                        };
						$ini_content[$file] = $content;
					};
					echo "<span class='flag'>+ .</span> <span class='ini' value='".localize("new-group-filename").".ini'>[".localize("new-label")."]</span><br>\n";
                    echo "</span>\n";
					
					echo "<div id='ini_content'>\n";
					foreach(array_keys($ini_content) as $file) {
						$content = $ini_content[$file];
						echo "\t<textarea id='".path($file, "filename")."'>".join("\n", $content)."</textarea>\n";
					};
					echo "</div>\n";
                ?>
            </div>
        </aside>
            
        <?php
            echo "\n";
            echo "\t\t<input id='logged_user' type='hidden' value='".$_SESSION['logged_user']."'>\n";
            echo "\t\t<input id='logged_group' type='hidden' value='".$_SESSION['logged_group']."'>\n";

            $dev_group = explode(" ", $groups['dev']);
            array_shift($dev_group);
            echo "\t\t<input id='dev_group' type='hidden' value='".join(";", $dev_group)."'>\n";
        ?>
        
	</body>
</html>

    

