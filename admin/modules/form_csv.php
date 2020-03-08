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
    $csv = array_map("trim", file($path));
    // Sort it
    $title_row = array_shift($csv);
    $row_length = count( explode(";", $title_row) );
    setlocale(LC_COLLATE, 'pl_PL');
    sort($csv, SORT_LOCALE_STRING);
    array_unshift($csv, $title_row);
    // ====== Edit Buttons ======
    echo "\t\t\t\t<div class='buttons'>\n";
    echo "\t\t\t\t\t<button class='cancel' help='".localize("cancel-changes")."'>".localize("cancel-label")."</button>\n";
    echo "\t\t\t\t\t<button class='save' name='save_mode' value='publish' type='submit' help='".localize("publish-changes")."'>".localize("publish-label")."</button>\n";
    echo "\t\t\t\t</div>\n";
    echo "\t\t\t</header>\n";
    echo "\t\t\t<h2>".path($path, "basename")."</h2>\n";


    echo "\t\t\t<article class='_csv'>\n";
    echo "\t\t\t\t<h3 class='_csv'>CSV</h3>\n";

    echo
        "\t\t\t\t<div class='buttons'>\n".
        "\t\t\t\t\t<button class='add_row' help='".localize("add-table-row")."'><span class='fi-plus'></span></button>\n".
        "\t\t\t\t</div>\n";

    echo "<table>\n";
    $row_id = 0;
    foreach($csv as $row) {
		if(trim($row) != "") {
			$row = explode(";", $row);
            if($row_id == 0) {
                echo "\t<tr class='title'>\n";
                echo "\t\t<td class='manual' help='".localize("table-csv-html")."'><span class='fi-info'></span></td>\n";
            }
            else {
                echo "\t<tr class='edit'>\n";
                echo "\t\t<td class='nr'><span class='id'>$row_id</span><span class='delete fi-x'></span></td>\n";
            };
            foreach(array_keys($row) as $column_id) {
                $cell = BBCode( $row[$column_id] );
                echo "\t\t<td class='editable' contentEditable='true'>$cell</td>\n";
            };
            echo "\t</tr>\n";
            $row_id++;
		};
    };
    echo "</table>\n";

    echo "\t\t\t</article>\n";

    // Outupt data fields
    echo "\t\t\t<div id='outputs'>\n";
    echo "\t\t\t\t<input type='text' name='edit_path' id='edit_path' value='$path'>\n";
    echo "\t\t\t\t<textarea name='output|csv' id='output'></textarea>\n";
    echo "\t\t\t</div>\n";

    echo "\t\t</form>\n"; // #cms

    echo "\t\t<div id='show_nav'><span class='fi-indent-more'></span></div>\n";

    echo "<script src='script/csv.js'></script>\n"
?>