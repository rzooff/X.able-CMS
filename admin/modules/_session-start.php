<?php

    session_start();
    if(!is_string($_SESSION['logged_user']) || $_SESSION['logged_user'] == "" || !is_string($_SESSION['ini_file']) || $_SESSION['ini_file'] == "") {
        header("Location: login.php");
        break;
    };

    require "script/functions.php";
    require "script/cms.php";
    require "script/xml.php";

    $ini_file = $_SESSION['ini_file'];
    $ini_pathes = loadIni("xable.ini", "pathes");
    $ini_enable = loadIni($ini_file, "enable");
    $root = $ini_pathes['root'];
    $settings = loadXml($ini_pathes['settings']);
    $nav_documents = loadIni($ini_file, "navigation");
    $site_options = loadIni("xable.ini", "options");

    //arrayList($site_options);

?>