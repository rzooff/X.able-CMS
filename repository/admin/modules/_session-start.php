<?php
    //error_reporting(0);

    session_start();

    require "script/functions.php";
    require "script/cms.php";
    require "script/xml.php";

    // Login test    
    if(
        !is_string($_SESSION['logged_user']) ||
        $_SESSION['logged_user'] == "" ||
        !is_string($_SESSION['ini_file']) ||
        $_SESSION['ini_file'] == "" ||
        $_SESSION["cms_url"] != getCmsUrl() ||
        $_SESSION['logged_ip'] != $_SERVER['REMOTE_ADDR']
    ) {
        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $_SESSION["login_url"] = $actual_link;
        header("Location: login.php");
        exit();
    };

    $ini_file = $_SESSION['ini_file']; // user_ini
    $ini_pathes = loadIni("xable.ini", "pathes");
    $ini_enable = loadIni($ini_file, "enable");
    $root = $ini_pathes['root'];
    $settings = loadXml($ini_pathes['settings'], "draft");
    $nav_documents = loadIni($ini_file, "navigation");

    $_SESSION["admin_root"] = $root;
    $_SESSION["ini_site_options"] = loadIni("xable.ini", "options");

?>