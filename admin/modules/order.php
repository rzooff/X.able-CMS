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
    echo "\t\t\t\t<div id='lang' ><span class='fi-web'></span><p>Język:&nbsp;&nbsp;&nbsp;-</p></div>\n";

    // ====== List items ======
    $list = array_map("trim", file($path));
    echo "\t\t\t<h2>".path($path, "basename")."</h2>\n";
    foreach($list as $item) {
        echo "\t\t\t<article class='_order'>\n";
        echo "\t\t\t\t<h3 class='$item'>$label</h3>\n";
        echo
            "\t\t\t\t<div class='buttons'>\n".
            //"\t\t\t\t\t<button class='delete' help='Usuń wpis'><span class='fi-x'></span></button>\n".
            "\t\t\t\t\t<button class='down' help='Przesun w dół'><span class='fi-arrow-down'></span></button>\n".
            "\t\t\t\t\t<button class='up' help='Przesuń w górę'><span class='fi-arrow-up'></span></button>\n".
            //"\t\t\t\t\t<button class='new' help='Nowy wpis'><span class='fi-plus'></span></button>\n".
            "\t\t\t\t</div>\n";
        echo "\t\t\t</article>\n";
    };

    // Outupt data fields
    echo "\t\t\t<div id='outputs'>\n";
    echo "\t\t\t\t<input type='text' name='edit_path' id='edit_path' value='$path'>\n";
    echo "\t\t\t\t<textarea name='output|order' id='output'></textarea>\n";
    echo "\t\t\t</div'>\n";
    echo "\t\t\t</main>\n";
    echo "\t\t</form>\n"; // #cms
?>