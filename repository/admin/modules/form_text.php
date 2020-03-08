<?php
    echo "\n";

    // ======================================
    //               Build HTML
    // ======================================

    echo "\n\t\t<form id='cms' class='order' method='post' action='_publish.php' enctype='multipart/form-data'>\n";
    echo "\t\t\t<header>\n";
    // ====== Languages ======
    
    echo "\t\t\t\t<div id='lang' ><span class='fi-web'></span><p>".localize("language-label").":&nbsp;&nbsp;&nbsp;-</p></div>\n";

    // Load file content
    $text = array_map("trim", file($path));
    // ====== Edit Buttons ======
    echo "\t\t\t\t<div class='buttons'>\n";
    echo "\t\t\t\t\t<button class='cancel' help='".localize("cancel-changes")."'>".localize("cancel-label")."</button>\n";
    echo "\t\t\t\t\t<button class='save' name='save_mode' value='publish' type='submit' help='".localize("publish-changes")."'>".localize("publish-label")."</button>\n";
    echo "\t\t\t\t</div>\n";
    echo "\t\t\t</header>\n";
    echo "\t\t\t<h2>".path($path, "basename")."</h2>\n";

    // ====== List items ======
    echo "\t\t\t<article class='_text'>\n";
    echo "\t\t\t\t<h3 class='_text'>Text</h3>\n";
    echo "\t\t\t\t<section>\n";
    echo "\t\t\t\t\t<div class='text'><textarea name='output_text'>".join("\n", $text)."</textarea></div>\n";
    echo "\t\t\t\t</section>\n";
    echo "\t\t\t</article>\n";

    // Outupt data fields
    echo "\t\t\t<div id='outputs'>\n";
    echo "\t\t\t\t<input type='text' name='edit_path' id='edit_path' value='$path'>\n";
    echo "\t\t\t</div>\n";

    echo "\t\t</form>\n"; // #cms
?>