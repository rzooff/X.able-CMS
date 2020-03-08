<?php

    require "modules/_session-start.php";

?>

<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta charset="UTF-8">
        <link rel="Stylesheet" type="text/css" href="style/login.css">
        
        <link href="iconfont/ionicons-2.0.1/css/ionicons.css" rel="stylesheet" type="text/css" />
        <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,600,700,800&amp;subset=latin-ext" rel="stylesheet">

        <title>_MAILER</title>
    </head>
    
    <body>
        
        <?php
        

        
            if($_POST["to"] && $_POST["from"] && $_POST["subject"] && $_POST["message"]) {
                //arrayList($_POST);
                sendMail($_POST["to"], $_POST["from"], $_POST["subject"], BBCode($_POST["message"]));
            }
        
            if($_POST["back_url"]) {
                echo "<script> location.href = \"".$_POST["back_url"]."\"; </script>\n";
            }        
        
        ?>
        
    </body>
</html>