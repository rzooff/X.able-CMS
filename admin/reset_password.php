<?php
    session_start();

    $pass_path = "../.ps/admin/.htpasswd";
    $reset_path = "_users/reset_password.log";
    $localization_folder = "localization";
    
    // ====== Load Functions Libraries ======

    require "script/functions.php";
    require "script/cms.php";
    require "script/xml.php";

    $settings = loadXml("../settings.xml");
    $mailer_email = readXml($settings, "mailer email");

    loadLocalization($localization_folder);

    // ===================================
    //             Functions
    // ===================================

    function loadUsers($pass_path) {
    // -------------------------------------
    // $pass_path = <string> Password file path
    // -------------------------------------
    // RETURN: <array> users -> password hashes
    // -------------------------------------
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
    $users = loadUsers($pass_path);

    function changePassword($login, $pass, $pass_path) {
    // -------------------------------------
    // $login = <string> User login
    // $pass = <string> New password
    // $pass_path = <string> Password file path
    // -------------------------------------
    // RETURN: <array> users -> password hashes
    // -------------------------------------
        $done_flag = false;
        $file = array_map("trim", file($pass_path));
        arrayList($file);
        foreach(array_keys($file) as $n) {
            $user = explode(":", $file[$n]);
            if($user[0] == $login) {
                $user[1] = crypt($pass, substr($user[1], 0 , 2));
                $file[$n] = join(":", $user);
                $done_flag = true;
            }
        }
        if($done_flag && safeSave($pass_path, join("\n", $file))) {
            return $login;
        }
        else {
            return false;
        }
    };

    function findToken($id, $reset_path, $status) {
    // -------------------------------------
    // $id = <string> ID string to find
    // $reset_path = <string> Reset password log file path
    // $status = <string> New status to change or <false>
    // -------------------------------------
    // RETURNS: <array> found data [ <id>, <login>, <init_date>, <status> ] or <string> "expired"/"used" or <false> if not found
    // -------------------------------------
        $current_date = date("Y-m-d H:i:s");
        $log = array_map("trim", file($reset_path));
        $data = false;
        $save_flag = false;
        
        foreach(array_keys($log) as $n) {
            $item = explode(";", $log[$n]);
            // Check timeout
            if( $item[3] == "pending" && ((strtotime($current_date) - strtotime($item[2])) / 3600) > 24 ) {
                $item[3] = "expired";
                $log[$n] = join(";", $item);
                $save_flag = true;
            }
            // Found / status change
            if($item[0] == $id) {
                if($status) {
                    $item[3] = $status;
                    $log[$n] = join(";", $item);
                    $save_flag = true;
                }
                $data = $item;
            }
        };
        
        // Save if any status changed
        if($save_flag) { safeSave($reset_path, join("\n", $log)); };

        // Return
        if(!is_array($data)) {
            return false;
        }
        elseif(end($data) != "pending") {
            return end($data);
        }
        else {
            return $data;
        }
    };

?>


<!doctype html>
<html>
	<head>
        <style><?php include "style/loader.css"; ?></style>

		<meta charset="UTF-8">
		<title><?php echo localize("reset-password"); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
		<link rel="stylesheet" type="text/css" href="style/index.css" />
		<link rel="stylesheet" type="text/css" href="style/cms.css" />
        <link rel="stylesheet" type="text/css" href="style/colors.css" />
        <link rel="stylesheet" type="text/css" href="style/password.css" />
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
        
        <form id="password" method='post' action='reset_password.php?action=save&id=<?php echo $_GET["id"]; ?>'>
            <div id="page_fader"></div>
            <div id="popup_container">
                
                <div id="popup_box" class='form'>
                    <h6><span class="fi-torso-business"></span></h6>
                    <h3><?php echo localize("reset-password"); ?></h3>
                    
                    <div class='popup_info'>
                        <p>Info text</p>
                    </div>
                    
                    <div class='inputs'>
                        <p class='label'><?php echo localize("login-label"); ?></p>
                        <input type='text' id='login' class='string' value='' disabled>
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
                        <button class='cancel' href='index.php?page=<?php echo $_GET['page']; ?>'><?php echo localize("cancel-label"); ?></button>
                        <button class='goto_login'><?php echo localize("login-button"); ?></button>
                    </div>
                </div>
            </div>
        </form>

<?php
    
    // ====== No server email defined ======
    if(!is_string($mailer_email) || trim($mailer_email) == "" || count(explode("@", $mailer_email)) != 2) {
        //$popup = "ERROR! No server email specified site settings"; //BŁĄD! Nie wpisano w ustawieniach emaila do obsługi przez serwer
        $popup = localize("no-server-email-html"); //BŁĄD! Nie wpisano w ustawieniach emaila do obsługi przez serwer
    }
    // ====== Reset Password ======
    elseif($_GET['action'] == "reset") {
        if(!is_string($_POST['login']) || trim($_POST['login']) == "") {
            $popup = localize("no-email"); //Brak emaila
        }
        if(!is_string($users[$_POST['login']])) {
            $popup = localize("unknown-login"); //Błędny login
        }
        else {
            // ====== Generate email link ======
            $current_date = date("Y-m-d H:i:s"); // Current -> init
            $id = hash("md5", $current_date.$_POST['login']);
            
            if(file_exists($reset_path)) {
                $log = array_map("trim", file($reset_path));
            }
            else {
                $log = array();
            }
            
            $data = join(";", array($id, $_POST['login'], $current_date, "pending"));
            $log[] = $data;
            
            $href = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?action=change&id=".$id;
            
            // ====== Send link ======
            $to = $_POST['login'];
            $from = $mailer_email;
            $subject = localize("reset-password-subject");
            $message = str_replace("@reset_password_link", $href, localize("reset-password-message-html"));
            
            $header = "";
            $header .= "Content-type: text/html; charset=utf-8\r\n";
            $header .= "Content-Transfer-Encodin: 8bitr\n";
            
            if(safeSave($reset_path, join("\n", $log)) && mail($to, $subject, $message, $header."Reply-to: ".$from, "-f ".$from)) {
                $popup = localize("reset-password-link-sent-html");
            }
            else {
                $popup = localize("message-send-failed-html");
            }

        }
    }
    // ====== Change Password ======
    elseif($_GET['action'] == "change") {
        
        $data = findToken($_GET['id'], $reset_path, false);
        
        if($data == "used") {
            $popup = localize("link-used");
        }
        elseif($data == "expired") {
            $popup = localize("link-expired");
        }
        elseif(!is_array($data)) {
            $popup = localize("link-invalid");
        }
        else {
            // Change password
            echo "<input type='hidden' id='action' value='change'>\n";
            echo "<input type='hidden' id='email' value='".$data[1]."'>\n";
            $popup = "";
        }
        
    }
    // ====== Save New Password ======
    elseif($_GET['action'] == "save") {
        echo "<input type='hidden' id='action' value='save'>\n";
        
        $data = findToken($_GET['id'], $reset_path, false);

        if($data == "used") {
            $popup = localize("link-used");
        }
        elseif($data == "expired") {
            $popup = localize("link-expired");
        }
        elseif(!is_array($data)) {
            $popup = localize("link-invalid");
        }
        elseif(is_string($_POST['new_password']) && is_string($_POST['confirm_password']) &&
           $_POST['new_password'] != "" && $_POST['new_password'] == $_POST['confirm_password'] &&
           changePassword($data[1], $_POST['new_password'], $pass_path)
          ) {
            echo "<input type='hidden' id='show_goto_login' value='true'>\n";
            $popup = localize("password-changed");
            findToken($_GET['id'], $reset_path, "used");
        }
        else {
            $popup = localize("password-change-failed-html");
        }
        
    }

    echo "<input type='hidden' id='popup' value='$popup'>\n";

?>
        
        <?php exportLocalization() ?>
        <script src='script/jquery-3.1.0.min.js'></script>
        <script src='script/functions.js'></script>
        <script src='script/reset_password.js'></script>
	</body>
</html>