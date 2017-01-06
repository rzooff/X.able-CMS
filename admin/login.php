<?php
    session_start();

    // ====== Variables ======

	$pass_path = "../.ps/admin/.htpasswd";
    $users_folder = "_users";
    $groups_path = "$users_folder/.groups";
    $login_error = false;

    // ====== Functions ======

    require "script/functions.php";
    require "script/cms.php";
    require "script/xml.php";

    function goToPanel() {
        header("Location: index.php");
        break;
    };

    function getAdminFolder() {
        $admin_folder = false;
        $url = $_SERVER['REQUEST_URI'];
        if(substr($url, strlen($url) - 1) == "/") { $url = substr($url, 0, strlen($url) - 1); };
        if(!is_string(path($url, "extension")) || path($url, "extension") == "") {
            $admin_folder = array_pop(split("/", $url));
        }
        else {
            $admin_folder = array_pop(split("/", path($url, "dirname")));
        };
        return $admin_folder;
    };

    function loadUsers($pass_path) {
        $users = array();
        $file = file($pass_path);
        foreach($file as $user) {
            
            $user = split(":", $user);
            if(count($user) == 2) {
                $user = array_map("trim", $user);
                $users[ $user[0] ] = $user[1];
            };
        };
        return $users;
    };

    function loadUserGroup($user, $groups_path) {
        $user_group = false;
        $file = file($groups_path);
        foreach($file as $group) {
            $group = split(":", $group);
            $group = array_map("trim", $group);
            $group_name = $group[0];
            $group_users = array_map("trim", split(" ", $group[1]));
            if(in_array($user, $group_users)) { $user_group = $group_name; };
        };
        return $user_group;
    };

    // ====== CHECK LOGIN / POST DATA ======

    // Already loaded
    if(is_string($_SESSION['logged_user']) && $_SESSION['logged_user'] != "" && is_string($_SESSION['ini_file']) && $_SESSION['ini_file'] != "") {
        //arrayList($_SESSION);
        goToPanel();
    }
    // Login & pass
    else if(is_string($login = $_POST['login']) && is_string($pass = $_POST['password'])) {
        $users = loadUsers($pass_path);
        // Correct login & password
        if($users[$login] == crypt($pass, "mn")) {
            $group = loadUserGroup($login, $groups_path);
            if(!is_string($group)) { $group = "*none*"; };
            // Set session variables
            $_SESSION['logged_user'] = $login;
            $_SESSION['logged_group'] = $group;
            $_SESSION['ini_file'] = "$users_folder/$group.ini";
            $_SESSION['password_file'] = $pass_path;
            $_SESSION['groups_file'] = $groups_path;
            $_SESSION['admin_folder'] = getAdminFolder();
            // Redirect to panel
            goToPanel();
        }
        // Wrong login or password
        else {
            $_SESSION['logged_user'] = false;
            $login_error = true;
        };
    };

?>

<!doctype html>
<html>
	<head>
        <!-- Loader Style -->
        <style><?php include "style/loader.css"; ?></style>
        
        <!-- Loader Style / end -->
		<meta charset="UTF-8">
		<title>X.able CMS / Login</title>
        
		<link rel="stylesheet" type="text/css" href="style/index.css" />
		<link rel="stylesheet" type="text/css" href="style/cms.css" />
        <link rel="stylesheet" type="text/css" href="style/colors.css" />
        <link rel="stylesheet" type="text/css" href="style/login.css" />
		<link rel="stylesheet" type="text/css" href="style/foundation-icons.css" />
		<link href='http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900|Inconsolata:400,700|Audiowide&subset=latin,latin-ext' rel='stylesheet' type='text/css'>

        <script src='script/jquery-3.1.0.min.js'></script>
        <script src='script/functions.js'></script>
	</head>

	<body>
        
        <div id='loader'>
            <div id="loadingProgressG">
                <div id="loadingProgressG_1" class="loadingProgressG"></div>
            </div>
        </div>

        <form id="password" method='post' action='login.php'>
            <div id="page_fader"></div>
            <div id="popup_container">
                <div id="popup_box">
                    <header>
                        <h1><strong>&gt;&lt;</strong>.able<span>CMS</span></h1>
                        <h2>&copy;2016 by <a href='mailto:maciej@maciejnowak.com'>maciej@maciejnowak.com</a></h2>
                    </header>
                    <div id="form">
                        <?php
                            if($login_error == true) {
                                echo "<p class='error'>Błędny login lub hasło</p>\n";
                            };
                        ?>
                        <div class='inputs'>
                            <div class='text'>
                                <p class='label'>Login</p>
                                <input type='text' class='string' id='login' name='login' >
                                <p class='label'>Hasło</p>
                                <input type='password' id='password' name='password' value=''>
                            </div>
                        </div>
                        <div class='buttons'>
                            <button class='confirm'>Zaloguj</button>
                            <button class='cancel'>Anuluj</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
        <?php 
            if(is_string($popup)) { echo "<input id='popup' value='$popup'>\n"; };
            if(is_string($redirect)) { echo "<input id='redirect' value='$redirect'>\n"; };
        ?>
        
        <script>
            $(document).ready(function() {
                $("#loader").fadeOut(200);
                $("#popup_container").delay(200).fadeIn(200);
                
                $("button.cancel").click(function() { location.href = "../"; return false; });
                $("button.confirm").click(function() {
                    login = $("input#login").val();
                    pass = $("input#password").val();
                    if( $("input#login").val() == "" ) {
                        alert("Wpisz nazwę użytkownika.");
                        $("input#login").focus();
                        return false;
                    }
                    else if( $("input#password").val() == "" ) {
                        alert("Podaj hasło.");
                        $("input#password").focus();
                        return false;
                    }
                    else {
                        return true;
                    };
                });
            });
        </script>
        
	</body>
</html>

