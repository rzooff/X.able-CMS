<?php
    echo "\n";

    // ======================================
    //               Build HTML
    // ======================================

    $edit_buttons = "basic";

    echo "\n\t\t<form id='cms' class='order' method='post' action='_publish.php' enctype='multipart/form-data'>\n";
    require("modules/header.php"); // User & Notifications panel

    echo "\t\t\t<main>\n";
    
    // ====== Languages ======
    if(count($languages) > 0) {
        echo "\t\t\t\t<div id='lang'><span class='fi-web manual' help='".localize("language-change")."'></span><p>".localize("language-label").":</p><ul>\n";
        foreach($languages as $language) { echo "\t\t\t\t\t<li value='$language'>".strtoupper($language)."</li>\n"; };
        echo "\t\t\t\t</ul></div>\n";
    }
    else {
        echo "\t\t\t\t<div id='lang' ><span class='fi-web'></span><p>".localize("language-label").":&nbsp;&nbsp;&nbsp;-</p></div>\n";
    }

    // ====== Path info ======
    echo "<p id='path_info'>path_info</p>\n";

    // ========================
    //        List items
    // ========================

    $orders_xml = loadXml($path);
    $load_tag = false;
        
    $list = array_map("trim", file($path));
    echo "\t\t\t\t<h2>".$path."</h2>\n";

    echo "\t\t\t\t<input type='hidden' class='_order-title' value='".readXml($orders_xml, "_order title")."'>\n";

    // ====== Options ======
    if($options = readXml($orders_xml, "_order options")) { $options = explode(";", $options); }
    else { $options = []; };
    echo "\t\t\t\t<article class='_order-options'>\n";
    echo "\t\t\t\t\t<ul class='checkbox'>\n";
    // Date sort button
    if(in_array("date", $options)) {
        echo "\t\t\t\t\t\t<li><button class='date help' help='".localize("date-sort")."'><span class='fi-calendar'></span></button></li>\n";
        $load_tag = "header date";
    }
    // Auto sort checkbox
    else {
        if(in_array("auto", $options)) { $auto = "checked"; } else { $auto = ""; };
        echo "\t\t\t\t\t\t<li><label class='option'><input type='checkbox' class='auto' $auto><span>".localize("auto-sort")."</span></label></li>\n";
    }
    // Hiiden options
    foreach($options as $option) {
        if(!in_array($option, [ "auto", "date" ])) {
            echo "\t\t\t\t\t\t<li style='display:none'><input type='hidden' class='".$option."' value='true'></li>\n";
        }
    };
    echo "\t\t\t\t\t</ul>\n";
    echo "\t\t\t\t</article>\n";

    foreach($orders_xml["multi_item"] as $item) {
        
        $item_path = readXml($item, "path");
        $item_label = readXml($item, "title", $_SESSION["edit_lang"]);
        
        
        $item_label = xmlLanguageTitles($item);
        
        //list($item_path, $item_label) = explode("|", $item);
        $item_xml = "\n".arrayToXml($item, 1);
        $item_xml = str_replace("<", "&lt;", $item_xml);
        $item_xml = str_replace(">", "&gt;", $item_xml);
        
        echo "\t\t\t\t<article class='_order'>\n";
        echo "\t\t\t\t\t<h3 path='$item_path'><span class='fi-list'></span>&nbsp;$item_label<textarea class='value' style='display:none;'>".$item_xml."</textarea></h3>\n";
        echo
            "\t\t\t\t\t<div class='buttons'>\n".
            //"\t\t\t\t\t<button class='delete' help='Usuń wpis'><span class='fi-x'></span></button>\n".
            "\t\t\t\t\t\t<button class='down' help='".localize("move-down")."'><span class='fi-arrow-down'></span></button>\n".
            "\t\t\t\t\t\t<button class='up' help='".localize("move-up")."'><span class='fi-arrow-up'></span></button>\n".
            //"\t\t\t\t\t<button class='new' help='Nowy wpis'><span class='fi-plus'></span></button>\n".
            "\t\t\t\t\t</div>\n";
        
        if(is_string($load_tag) && $load_tag != "") {
            if($item_xml = loadXml(path($path, "dirname")."/".$item_path, "draft", true)) {
                $tag = readXml($item_xml, $load_tag);
                echo "\t\t\t\t\t<input type='hidden' class='".str_replace(" ", "-", $load_tag)."' value='".$tag."'>\n";
            }
        };
        
        echo "\t\t\t\t</article>\n";
    };

    echo "\t\t\t</main>\n";


    // ====== Outupt data fields ======
    echo "\t\t\t<div id='outputs'>\n";
    echo "\t\t\t<p class='header'>".localize("xml-preview")."<span class='changed'></span></p>\n";

    echo "\t\t\t\t<p class='label'>Open path</p>\n";
    echo "\t\t\t\t<input type='text' name='edit_path' id='edit_path' value='$path'>\n";
    echo "\t\t\t\t<p class='label'>Save path</p>\n";
    echo "\t\t\t\t<input type='text' name='save_path' id='save_path' value='$saveas'>\n";
    echo "\t\t\t\t<p class='label'>Files to delete</p>\n";
    echo "\t\t\t\t<input type='text' name='delete_files' id='delete_files' value=''>\n";
    echo "\t\t\t\t<input type='hidden' name='current_edit_lang' id='current_edit_lang' value='".$_SESSION['edit_lang']."'>\n";
    //foreach(array_keys($file_manager) as $key) {
    //    $files = checkFiles($root, explode(";", $file_manager[$key]));
    //    echo "\t\t\t\t<p class='label'>File manager: $key</p>\n";
    //    echo "\t\t\t\t<input type='text' name='file_manager|$key' id='file_manager|$key' value='".join(";", $files)."'>\n";;
    //};
    echo "\t\t\t\t<p class='label'></p>\n";
    echo "\t\t\t\t<textarea name='output|order' id='output'></textarea>\n";
    echo "\t\t\t\t<div class='scroll_box'><div class='xml_preview' contenteditable='true'></div></div>\n";

    echo "\t\t\t\t<button class='close fi-x'></button>\n";
    echo "\t\t\t</div>\n";


    echo "\t\t</form>\n"; // #cms
?>