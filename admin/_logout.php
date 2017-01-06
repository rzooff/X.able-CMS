<?php
    $_SESSION['logged_user'] = false;
    session_start();
    session_destroy();
    unset($_SESSION);
    header("Location: index.php");
?>