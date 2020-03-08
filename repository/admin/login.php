<?php
    //error_reporting(E_ALL);

    session_start();

    // ====== Variables ======
    $root = "..";
    $users_folder = "_users";
    $groups_path = "$users_folder/.groups";
    $localization_folder = "localization";
    $login_error = false;

    $xable_version = trim(array_shift(file("doc/version.txt")));
    $xable_date = array_pop(explode(";", $xable_version));
    $last_update = array_pop(explode(";", $xable_version));
    //echo $xable_version;

    // ====== Load Functions Libraries ======

    require "script/functions.php";
    require "script/cms.php";
    require "script/xml.php";

    $site_options = loadIni("xable.ini", "options");
    $settings = loadXml("$root/settings.xml");

    $ini_pathes = loadIni("xable.ini", "pathes");
    $pass_path = $ini_pathes["passwords"];

    // Get Back URL if any
    if(isset($_GET["back_url"]) && $_GET["back_url"] != "") {
        $_SESSION["login_url"] = $_GET["back_url"];
    };

    // ===================================================
    //                    LOCALIZATION
    // ===================================================

    // ====== LANGUAGES ======
    // Set admin master language
    if(is_string($site_options['admin_lang'])) {
        $_SESSION['admin_lang'] = $site_options['admin_lang'];
    }
    else {
        $_SESSION['admin_lang'] = "pl"; // default
    }

    loadLocalization($localization_folder);

    // ===================================================
    //                      FUNCTIONS
    // ===================================================

    function goToPanel() {
        if(isset($_SESSION["login_url"]) && $_SESSION["login_url"] != "") {
            $location = array_shift(explode("&popup=", $_SESSION["login_url"]));
            unset($_SESSION["login_url"]);
            header("Location: ".$location);
        }
        else {
            header("Location: index.php");
        }
        exit();
    };

    function getAdminFolder() {
        $admin_folder = false;
        $url = $_SERVER['REQUEST_URI'];
        if(substr($url, strlen($url) - 1) == "/") { $url = substr($url, 0, strlen($url) - 1); };
        if(!is_string(path($url, "extension")) || path($url, "extension") == "") {
            $admin_folder = array_pop(explode("/", $url));
        }
        else {
            $admin_folder = array_pop(explode("/", path($url, "dirname")));
        };
        return $admin_folder;
    };

    function loadUsers($pass_path) {
        $users = array();
        $file = file($pass_path);
        foreach($file as $user) {
            
            $user = explode(":", $user);
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
            $group = explode(":", $group);
            $group = array_map("trim", $group);
            $group_name = $group[0];
            $group_users = array_map("trim", explode(" ", $group[1]));
            if(in_array($user, $group_users)) { $user_group = $group_name; };
        };
        return $user_group;
    };

    function postInstall($root) {
        $install_folder = "$root/install";
        if(file_exists("$install_folder/install.php")) {
            removeDir("$root/install");
            $_SESSION['popup'] = localize("xable-welcome-info")."|done";
            addLog("INSTALL", "Xable-CMS");
        };
    };

    // ====== CHECK LOGIN / POST DATA ======

    // Already loaded
    if(
        is_string($_SESSION['logged_user']) &&
        $_SESSION['logged_user'] != "" &&
        is_string($_SESSION['ini_file']) &&
        $_SESSION['ini_file'] != "" &&
        $_SESSION['cms_url'] == getCmsUrl() &&
        $_SESSION['logged_ip'] == $_SERVER['REMOTE_ADDR']
    ) {
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
            $_SESSION['logged_ip'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['ini_file'] = "$users_folder/$group.ini";
            $_SESSION['password_file'] = $pass_path;
            $_SESSION['groups_file'] = $groups_path;
            $_SESSION['admin_folder'] = getAdminFolder();
            $_SESSION['cms_url'] = getCmsUrl();

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
        <?php
            echo "\n".
                "\t\t<!-- ======================================\n".
                "\t\t               ><.able CMS"."\n".
                "\t\t      (c)".substr($xable_date, 0, 4)." maciej@maciejnowak.com"."\n".
                "\t\t         v.".str_replace(";", ", build.", $xable_version)."\n".
                "\t\t====================================== -->\n";
        ?>
        
        <!-- Loader Style -->
        <style><?php include "style/loader.css"; ?></style>
        
        <!-- Loader Style / end -->
		<meta charset="UTF-8">
		<title><?php echo localize("xable-login"); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
		<link rel="stylesheet" type="text/css" href="style/index.css" />
		<link rel="stylesheet" type="text/css" href="style/cms.css" />
        <link rel="stylesheet" type="text/css" href="style/colors.css" />
        <link rel="stylesheet" type="text/css" href="style/login.css" />
        <link rel="stylesheet" type="text/css" href="style/_responsive.css" />
		<link rel="stylesheet" type="text/css" href="style/foundation-icons.css" />
		<link href='https://fonts.googleapis.com/css?family=Lato:100,300,400,700,900|Inconsolata:400,700|Audiowide&subset=latin,latin-ext' rel='stylesheet' type='text/css'>


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
                        <h2>&copy;<?php echo substr($xable_date, 0, 4); ?> by <a href='mailto:maciej@maciejnowak.com'>maciej@maciejnowak.com</a></h2>
                    </header>
                    <div id="form">
                        <div class='info'>
                            <?php
                                echo "\n";
                                if($login_error == true) {
                                    echo "<p class='error'>".localize("login-fail")."</p>\n";
                                }
                                elseif(is_string($_GET['info'])) {
                                    echo "<p>".$_GET['info']."</p>\n";
                                };
                            ?>
                        </div>
                        <div class='inputs'>
                            <ul>
                                <li class='login'>
                                    <i class='icon fi-torso'></i>
                                    <input type='text' class='string' id='login' name='login' required>
                                </li>
                                <li class='password'>
                                    <i class='icon fi-unlock'></i>
                                    <input type='password' class='string' id='password' name='password' value='' required>
                                </li>
                            </ul>
                        </div>

                        <div class='buttons'>
                            <button class='confirm' type='submit'><?php echo localize("login-button"); ?></button>
                            <!-- <button class='cancel'><?php echo localize("cancel-label"); ?></button> -->
                        </div>
                        
                        <div class='links'>
                            <!-- Reset Password -->
                            <?php
                                // Reset password
                                echo "\n";
                                $mailer_email = readXml($settings, "mailer email");
                                if($site_options["reset_password"] == "true" && is_string($mailer_email) && count(explode("@", $mailer_email)) == 2) {
                                    echo "<p class='reset_password'>".localize("forgot-password")."</p>\n";
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
        <?php 
            if(is_string($popup)) { echo "<input id='popup' value='$popup'>\n"; };
            if(is_string($redirect)) { echo "<input id='redirect' value='$redirect'>\n"; };
            postInstall($root);
            exportLocalization();
        ?>
        
        <script src='script/jquery-3.1.0.min.js'></script>
        <script src='script/functions.js'></script>
        <script src='script/login.js'></script>
        
	</body>
</html>
