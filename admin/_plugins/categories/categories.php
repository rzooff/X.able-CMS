<?php
    
    echo "\n";
    //arrayList($xml);

    foreach(array_keys($xml) as $article_name) {
        // ====== Find category section ======
        if(is_array($xml[$article_name][0]["_category"])) {

            $cat_file = $xml[$article_name][0]["_category"][0]["description"][0];
            $cat_file = trim(array_pop(explode(":", $cat_file)));
            //echo "file: $root$cat_file\n";
            
            // ====== Find categories definitions =======
            $cat_xml = loadXml($root."/".$cat_file);
            foreach(array_keys($cat_xml) as $section_name) {
                if(is_array($cat_xml[$section_name][0]["_categories_list"])) {

                    $cat_multi = ($cat_xml[$section_name][0]["_categories_multi"][0]["selected"][0] != "");
                    $cat_list = $cat_xml[$section_name][0]["_categories_list"][0]["table"][0][$_SESSION['edit_lang']];
                    
                    $cat_labels = $cat_xml[$section_name][0]["_categories_list"][0]["table"][0];
                }
            };
            
            echo "<script>\n";
            // Categories list
            echo "\tvar CATEGORIES_LIST = {\n";

            foreach($cat_list as $cat_row) {

                list($cat_id, $cat_name) = explode(";", $cat_row);
                echo "\t\t\"key_".$cat_id."\": \"".$cat_name."\",\n";
            }
            echo "\t};\n";
            // Multi options
            if($cat_multi) {
                echo "\tvar CATEGORIES_MULTI = true;\n";
            }
            else {
                echo "\tvar CATEGORIES_MULTI = false;\n";
            };
            echo "</script>\n";    
            // Labels list
            echo "<div id='categories_labels' style='display:none;'>\n".
                arrayToXml($cat_labels, 1).
                "</div>\n";
        }
    }

?>