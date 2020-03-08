<?php
    //$cookies_settings = loadXml()
    $plugin_settings = loadXml( path($file, "dirname")."/".path($file, "filename").".xml", "draft");
        
    $cookies = $plugin_settings["cookies_alert"][0];
    if(is_array($cookies) && readXml($cookies, "active") != "" && readXml($cookies, "text") != "") {
        // Close button
        if(($icon = readXml($cookies, "close_icon")) != "") {
            $close_button = "<ion-icon name='close'></ion-icon>";
        }
        elseif(($label = readXml($cookies, "close_label")) != "") {
            $close_button = $label;
        }
        else {
            $close_button = "OK";
        }
        // Add info html
        echo "\t\t\t<div id='cookies_popup' style='display:none'>\n".
            "\t\t\t\t<p class='vertical_center'>".BBCode(readXml($cookies, "text"), true)."<button>$close_button</button></p>\n".
            "\t\t\t</div>\n";
        
    }
?>