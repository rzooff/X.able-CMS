<?php
    //error_reporting(E_ALL);

    require("modules/_session-start.php");

    $pass_path = $ini_pathes['passwords'];

    // ====== Variables ======
    $popup = false; // Show info popup
    $redirect = false; // Redirect to other page

    
    $login = $_SESSION['logged_user'];
    $pass = false;
    // ======= Get htpasswd data ======
    if(strlen($login) > 1) {
        $passwords = array();
        $pass_file = array_map("trim", file($pass_path));
        foreach($pass_file as $user) {
            if(substr($user, 0, 1) != ";" && strlen($user) > 3 && count(explode(":", $user)) == 2) {
                list($user_login, $user_pass) = explode(":", $user);
                $passwords[$user_login] = $user_pass;
            };
        };
        $pass = $passwords[$login];
    };
    if(!is_string($login) || !is_string($pass)) { echo "<script> alert('Login/password data error!') ; location.href = 'index.php'; </script>"; };
    //echo "login: $login / pass: $pass<br>";
    //arrayList($_SESSION);
    // ======= Check for POST input ======
    if(is_array($_POST) && is_string($_POST['current_password']) && is_string($_POST['new_password']) && is_string($_POST['confirm_password'])) {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];
        if($pass != crypt($current_pass, substr($pass, 0, 2))) {
            $popup = localize("invalid-current-password")."|error"; //"Błędne aktualne hasło|error";
        }
        elseif($new_pass != $confirm_pass) {
            $popup = localize("different-passwords")."|error"; //Podane hasła różnią się
        }
        elseif($new_pass != $confirm_pass) {
            $popup = localize("no-password")."|error"; //Nie podano hasła
        }
        else {
            $pass_file = array();
            $passwords[$login] = crypt($new_pass, substr($pass, 0, 2)); // update password array
            foreach(array_keys($passwords) as $user_login) { $pass_file[] = $user_login.":".$passwords[$user_login]; }; // array -> file content format
            safeSave($pass_path, join("\n", $pass_file));
            $popup = localize("password-changed")."|done"; // Hasło zostało zmienione
            $redirect = "index.php";
            addLog("password changed", path($pass_path, "basename"));
        };
    };

?>

<!doctype html>
<html>
	<head>
        <style><?php include "style/loader.css"; ?></style>

		<meta charset="UTF-8">
		<title><?php echo localize("xable-password"); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
		<link rel="stylesheet" type="text/css" href="style/index.css" />
		<link rel="stylesheet" type="text/css" href="style/cms.css" />
        <link rel="stylesheet" type="text/css" href="style/colors.css" />
        <link rel="stylesheet" type="text/css" href="style/password.css" />
        <link rel="stylesheet" type="text/css" href="style/_responsive.css" />
		<link rel="stylesheet" type="text/css" href="style/foundation-icons.css" />
		<link href='https://fonts.googleapis.com/css?family=Lato:100,300,400,700,900|Inconsolata:400,700|Audiowide&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
		
        <script src='script/jquery-3.1.0.min.js'></script>
        <script src='script/functions.js'></script>
	</head>
    
	<body>
        
        <div id='loader'>
            <div id="loadingProgressG">
                <div id="loadingProgressG_1" class="loadingProgressG"></div>
            </div>
        </div>
        
        <form id="password" method='post' action='password.php'>
            <div id="page_fader"></div>
            <div id="popup_container">
                <div id="popup_box">
                    <h6><span class="fi-torso-business"></span></h6>
                    <h3><?php echo localize("user-password-change"); ?></h3>
                    <div class='inputs'>
                        <p class='label'><?php echo localize("login-label"); ?></p>
                        <input type='text' class='string' value='<?php echo $login; ?>' disabled>
                        <div class='text'>
                            <p class='label'><?php echo localize("current-password"); ?></p>
                            <input type='password' id='current_password' name='current_password' value=''>
                        </div>
                        <div class='text'>
                            <p class='label'><?php echo localize("new-password"); ?></p>
                            <p class='description'><?php echo localize("password-requirements"); ?></p>
                            <input class='text' type='password' id='new_password' name='new_password' value=''>
                            <p class='label'><?php echo localize("new-password-confirm"); ?></p>
                            <input type='password' id='confirm_password' name='confirm_password' value=''>
                        </div>
                    </div>
                    <div class='buttons'>
                        <button class='confirm'><?php echo localize("ok-label"); ?></button>
                        <button class='cancel' href='index.php?page=<?php echo urlencode($_GET['page']); ?>'><?php echo localize("cancel-label"); ?></button>
                    </div>
                </div>
            </div>
        </form>
        
        <?php 
            if(is_string($popup)) { echo "<input id='popup' value='$popup'>\n"; };
            if(is_string($redirect)) { echo "<input id='redirect' value='$redirect'>\n"; };
            echo "<input type='hidden' id='saveas' value='".$_GET['page']."'>\n";
        
            foreach(array_keys($ini_enable) as $key) {
                echo "<input type='hidden' id='enable_$key' value='".$ini_enable[$key]."'>\n";
            };
        
            exportLocalization();
        ?>
        
        <script src='script/footer.js'></script>
        <script src='script/password.js'></script>
        
	</body>
</html>

