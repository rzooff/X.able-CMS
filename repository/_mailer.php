<?php
    session_start();

    require "script/functions.php";
    require "script/xml.php";
?>

<!doctype html>
<html>
    <head>
        <meta charset="UTF-8">

    </head>
    <body>
        <?php
        
            // ====== Check input data ======
            //arrayList($_POST);
            //arrayList($_SESSION);
            //echo "<hr>\n";
            $flag = true;
            foreach(array_keys($_POST) as $key) {
                if($_POST[$key] == "") {
                    echo "<!-- Missing data: \"$key\" -->\n";
                }
            }

            // ====== Send ======
            if($flag) {
                $server_name = "KEREN WWW";
                $server_email = $_POST["form_email"];
                $server_title = "=?UTF-8?B?".base64_encode($_POST["user_title"])."?=";
                $confirm_title = "=?UTF-8?B?".base64_encode($_POST["confirm_title"])."?=";
                $user_name = $_POST["user_name"];
                $user_email = $_POST["user_email"];
                $user_message = "$user_name ($user_email), napisał(a):<br>-<br>".str_replace("\n", "<br>", $_POST['message']);
                $confirm_message = str_replace("[br]", "<br>", $_POST['confirm_message']);

                $header = "";
                $header .= "Content-type: text/html; charset=utf-8\r\n";
                $header .= "Content-Transfer-Encodin: 8bitr\n";

                $user_header = $header;
                //$user_header .= "To: ".$server_email."\r\n";
                $user_header .= "From: $user_name <".$user_email.">\r\n";
                $user_header .= "Reply-to: ".$user_email."\r\n";

                $confirm_header = $header;
                //$confirm_header .= "To: ".$user_email."\r\n";
                $confirm_header .= "From: $server_name <".$server_email.">\r\n";
                $confirm_header .= "Reply-to: ".$server_email."\r\n";

                if(
                    //mail($server_email, $server_title, $user_message, $user_header, "-f ".$user_email) &&
                    mail($user_email, $confirm_title, $confirm_message, $confirm_header, "-f ".$server_email)
                ) {
                    $_SESSION['popup'] = "Wiadomość wysłana poprawnie";
                }
                else {
                    $_SESSION['popup'] = "Wysyłanie nie powiodło się";
                }
            }

            // ====== Move back ======
            //echo "POPUP: ".$_SESSION['popup']."<br>\n";
            //echo "Back: ".$_SESSION['lang']."/".$_POST['back_href']."<br>\n";
            echo "<meta http-equiv='Refresh' content='0; url=".$_SESSION['lang']."/".$_POST['back_href']."' />";
        ?>
    </body>
</html>