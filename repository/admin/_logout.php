<?php

    $_SESSION['logged_user'] = false;
    session_start();
    session_destroy();
    
    if(is_string($_GET['info']) && is_string($_GET['back_url'])) {
        header("Location: login.php?info=".urlencode($_GET['info'])."&back_url=".urlencode($_GET["back_url"]));
    }
    elseif(is_string($_GET['info'])) {
        header("Location: login.php?info=".urlencode($_GET['info']));
    }
    else {
        header("Location: login.php");
    };

?>