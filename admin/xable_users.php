<?php
    // ======================================
    //          ><.able CMS - CREATOR
    //        (C)2016 maciejnowak.com
    //          v.2.0 build.20161004
    // ======================================
	// compatibile: php4 or higher

    require("modules/_session-start.php");

    $_SESSION['password_file'] = $ini_pathes['passwords'];
    $_SESSION['groups_file'] = "_users/.groups";

	// ====== Groups update ======
	$groups = array();
	foreach(array_map("trim", file($_SESSION['groups_file'])) as $group) {
		$group_name = array_shift(split(":", $group));
		$groups[$group_name] = $group;
	};
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
	

    //arrayList($_POST);
    function changeUserPassword($login, $password) {
        $pass_content = array_map("trim", file($_SESSION['password_file']));
        foreach(array_keys($pass_content) as $num) {
            if(array_shift(split(":", $pass_content[$num])) == $login) {
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
            if(array_shift(split(":", $pass_content[$num])) == $login) {
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
            $group_name = trim( array_shift(split(":", $groups[$num])));
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
            $group = split(":", $groups[$num]);
            $group_name = trim($group[0]);
            $users = array_map("trim", split(" ", trim($group[1])));
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
            $pass = split(":", $pass_content[$num]);
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
		removeUserPassword($_POST['login']);
		removeUserFromGroups($_POST['login']);
	}
	elseif($_GET['action'] == "change_group") {
		removeUserFromGroups($_POST['login']);
        if($_POST['group'] != "" && $_POST['group'] != "*none*") {
            addUserToGroup($_POST['login'], $_POST['group']);
        };
	}
    elseif($_GET['action'] == "change_password") {
        changeUserPassword($_POST['login'], $_POST['new_password']);
    }
    elseif($_GET['action'] == "test_password") {
        if(getUserPassword($_POST['login']) == crypt( $_POST['current_password'], "mn" )) {
            echo "<script> alert('Password is CORRECT :)'); </script>";
        }
        else {
            echo "<script> alert('WRONG password (:'); </script>";
        };
    };

?>

<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>X.able CMS / Users manager</title>
        <link href='http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900|Inconsolata:400,700|Audiowide&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="style/foundation-icons.css" />
        <link rel="stylesheet" type="text/css" href="style/xable_creator.css" />
        <link rel="stylesheet" type="text/css" href="style/xable_users.css" />
        
        <script src='script/jquery-3.1.0.min.js'></script>
        <script src='script/functions.js'></script>
	</head>
	<body>
        <main>
            <nav>
                <div id="menu_bar">
                    <label class='logo'>
                        <span>&gt;&lt;</span>
                    </label>
                    <label class='title menu'>
                        <p>Users</p>
                        <ul>
                            <li>Creator</li>
                            <li>Update</li>
                            <li>Explorer</li>
							<li class='separator'><hr></li>
                            <li>Quit</li>
                        </ul>
                    </label>
                </div>
            </nav>

            <?php
                $groups = array();
                $group_names = array();
                $groups_content = array_map("trim", file($_SESSION['groups_file']));
                sort($groups_content);
                foreach($groups_content as $group) {
                    $group = split(":", $group);
                    $group_name = trim($group[0]);
                    $group_users = split(" ", trim($group[1]));
                    foreach(split(" ", trim($group[1])) as $user) { $groups[$user] = $group_name; };
                    $group_names[] = $group_name;
                };
                $group_names[] = "*none*";

                echo "<input type='hidden' id='users' value='".join(" ", array_keys($groups))."'>\n";
                //echo "<input type='hidden' id='updated_login' value='".$_POST['login']."'>\n";

                //arrayList($groups);
                $pass_content = array_map("trim", file($_SESSION['password_file']));
                sort($pass_content);
                echo "<table>\n";
                foreach($pass_content as $user) {
                    $user = split(":", $user);
                    $user_login = trim($user[0]);
                    $user_pass = trim($user[1]);
                    $user_group = $groups[$user_login];
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
                        "\t<td><button class='change_password'>Password</button></td>\n".
                        "\t<td><button class='delete_user'>Delete</button></td>\n".
                        "</tr>\n";
                };
                echo "</table>\n"                
            ?>

            <button class='new_article new_user'>+</button>
                

            <div id='popup_container'>
                <div id='new_user' class='popup'>
                    <nav>
                        <p>New user</p>
                        <div class='buttons'>
                            <button class='cancel'><span class='fi-x'></span></button>
                        </div>
                    </nav>
                    <form method='post'>
                        <p>Login:</p>
                        <input type='text' class='login' >
                        <p>Group:</p>
                        <select class='group'>
                        <?php foreach($group_names as $group) { echo "<option value='$group'>$group</option>"; }; ?>
                        </select>
                        <p>Password:</p>
                        <input type='password' class='password'>
                        <p>Repeat password:</p>
                        <input type='password' class='repeat'>
                        <button class='confirm'>Create</button>
                    </form>
                </div>
                <div id='change_password' class='popup'>
                    <nav>
                        <p>Password</p>
                        <div class='buttons'>
                            <button class='cancel'><span class='fi-x'></span></button>
                        </div>
                    </nav>
                    <form method='post'>
                        <p>Login:</p>
                        <input type='text' class='login' disabled>
                        <hr>
                        <p>Password:</p>
                        <input type='password' class='current_password'>
                        <button class='test'>Test password</button>
                        <hr>
                        <p>New password (min. 6 characters):</p>
                        <input type='password' class='new_password'>
                        <p>Repeat new password:</p>
                        <input type='password' class='new_repeat'>
                        <button class='confirm'>Change password</button>
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
                        echo preg_replace("/:(.*?)$/", "<span class='tag'>:$1</span>", $pass);
                        echo "<br>\n";
                    };
                    echo "<br><span class='flag'><hr>.groups<hr></span><br>\n";
                    foreach($groups_content as $group) {
                        echo preg_replace("/:(.*?)$/", "<span class='tag'>:$1</span>", $group);
                        echo "<br>\n";
                    };
                    echo "<br><span class='flag'><hr>.ini files in '_users' folder (click to edit)<hr></span><br>\n";
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
					echo "<span class='flag'>+ .</span> <span class='ini' value='nowa_grupa.ini'>[nowy]</span><br>\n";
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

        <script src='script/xable_users.js'></script>
            
        <?php echo "<input id='logged_group' type='hidden' value='".$_SESSION['logged_group']."'>\n"; ?>
        
	</body>
</html>

    

