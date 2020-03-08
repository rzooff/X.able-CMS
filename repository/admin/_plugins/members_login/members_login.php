<?php

    function removeUserPassword($passwords_file, $login) {
    // ----------------------------------------
    // $passwords_file = <string> Passwords file path
    // $login = <string> User login
    // ----------------------------------------
    // UPDATE: .htpasswds file - delete user data
    // ----------------------------------------
        $passwords = array();
        foreach(array_map("trim", file($passwords_file)) as $user) {
            $user = explode(":", $user);
            if($user[0] != $login) { $passwords[ $user[0] ] = join(":", $user); };
        }
        safeSave($passwords_file, join("\n", $passwords));
    }

    // ===================================================
    //                     VARIABLES
    // ===================================================

    $login_folder = $root."/.login";
    $passwords_file = "$login_folder/.htpasswd";
    $users_folder = "$login_folder/users";

    $login_settings = loadXml("$login_folder/users.xml");
    //$group_xml = $xml["user"][0]["_category"][0];

    if($login_settings) {

        echo "<div id='members_login-mailer' style='display:none'>\n".
            "\t<div class='form' method='post' action='_mailer.php'>\n".
            "\t\t<input class='back_url' name='back_url' value='index.php?path=$path'>\n".
            // Admin
            "\t\t<input class='from' name='from' value='".readXml($login_settings, "admin email", $_SESSION["admin_lang"])."'>\n".
            "\t\t<input class='admin_subject' value='".readXml($login_settings, "admin default_subject", $_SESSION["admin_lang"])."'>\n".
            "\t\t<textarea class='admin_signature'>".readXml($login_settings, "admin signature", $_SESSION["admin_lang"])."</textarea>\n".
            // Groups
            "\t\t<input class='group_subject' value='".readXml($login_settings, "groups user_subject", $_SESSION["admin_lang"])."'>\n".
            "\t\t<textarea class='group_message'>".readXml($login_settings, "groups user_message", $_SESSION["admin_lang"])."</textarea>\n".
            // Delete
            "\t\t<input class='remove_subject' value='".readXml($login_settings, "remove user_subject", $_SESSION["admin_lang"])."'>\n".
            "\t\t<textarea class='remove_message'>".readXml($login_settings, "remove user_message", $_SESSION["admin_lang"])."</textarea>\n".

            "\t</div>\n".
            "</div>\n";

    };

    //arrayList($_SESSION["session_temp"]);

    // ===================================================
    //                   REMOVE USER
    // ===================================================

    if(is_array($_SESSION["session_temp"]["_remove_user"])) {
        $remove = $_SESSION["session_temp"]["_remove_user"][0];
        $path = readXml($remove, "path");
        if(is_string($path) && $path != "" && !file_exists($path)) {
            removeUserPassword($passwords_file, readXml($remove, "email"));
            echo "<script> alert('User deleted!'); </script>\n";
        }
        else {
            echo "<script> alert('Deleting user error'); </script>\n";
        }
        unset($_SESSION["session_temp"]["_remove_user"]);
    }

    // ===================================================
    //                   SEND MAIL
    // ===================================================

    if(is_array($_SESSION["session_temp"]["_mailer"])) {
        $mailer = $_SESSION["session_temp"]["_mailer"][0];
        echo "<!-- Mailer: ".readXml($mailer, "to")." -->\n";
        
        if(sendMail(readXml($mailer, "to"), readXml($mailer, "from"), readXml($mailer, "subject"), BBCode(readXml($mailer, "message")), readXml($mailer, "from")))  {
            echo "<script> alert('Message sent to: ".readXml($mailer, "to")."'); </script>\n";
        }
        else {
            echo "<script> alert('Sending email error'); </script>\n";
        };
        unset($_SESSION["session_temp"]["_mailer"]);
    };

?>
