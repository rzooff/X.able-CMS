<?php

    require("modules/_session-start.php");

    //echo "<hr><h3>GET</h3><hr>";
    //arrayList($_GET);
    //echo "<hr><h3>POST</h3><hr>";
    //arrayList($_POST);
    //echo "<hr>\n";

    $protected = array("$root/settings.xml", "$root/navigation.xml");

    $errors = 0;
?>

<html>
    <head>
		<meta charset='utf-8'>
        <title>X.able CMS / Remove page</title>
        <link href='http://fonts.googleapis.com/css?family=Inconsolata:400,700&subset=latin,latin-ext' rel='stylesheet' type='text/css'>

        <style>
            /* ================================== */
            /*               Loader               */
            /* ================================== */

            <?php include "style/loader.css"; ?>

            /* ================================== */
            /*              Console               */
            /* ================================== */
            
            body {
                width: 100%;
                height: 100%;
                padding: 0 20px;
                font-family: 'Inconsolata';
                font-size: 14px;
                font-weight: normal;
            }

            article{ display: none; }
            * { margin: 0; padding: 0; }
            h2 { padding-top: 20px; font-size: 18px; font-weight: bold; }
            p { padding-top: 1px; }
            p span { margin-right: 10px; }
            .info { font-size: 11px; }
            .done { color: #22aa22; }
            .log { color: #999999; }
            .error { color: #ff0000; font-weight: bold; }
            .error:before { content: "ERROR! "; }
            .warning { color: #ffa800; font-weight: bold; }
            textarea { width: 700px; height: 500px; }
            button { margin-top: 20px; padding: 10px; }
            
        </style>
    </head>
    <body>
        
        <div id='loader'>
            <div id="loadingProgressG">
                <div id="loadingProgressG_1" class="loadingProgressG"></div>
            </div>
        </div>
        
        <article>
            <?php
                $delete_path = $_GET['path'];
                $save_path = $_POST['save_path'];

                // ======================================
                //             Main variable
                // ======================================

                function deleteMedia($file, $root) {
                    echo "<h2>DELETE ATTACHED FILES</h2>\n";
                    $folders = array();
                    $xml = loadXml($file);
                    if(is_array($xml)) {
                        //arrayList($xml);
                        foreach(array_keys($xml) as $article_name) {
                            $article_group = $xml[$article_name];
                            foreach(array_keys($article_group) as $article_num) {
                                $article = $article_group[$article_num];
                                foreach(array_keys($article) as $section_name) {
                                    $section_group = $article[$section_name];
                                    foreach(array_keys($section_group) as $section_num) {
                                        $item = $section_group[$section_num];
                                        if($item['type'][0] == "media") {
                                            foreach(array_keys($item['media'][0]) as $media_type) {
                                                $media = $item['media'][0][$media_type][0];
                                                if($media_type == "video" || $media_type == "none" || $media == "") {
                                                    // no need to delete files
                                                }
                                                else if(is_dir("$root/$media")) {
                                                    $folder = "$root/$media";
                                                    if(!in_array($folder, $folders)) { $folders[] = $folder; };
                                                }
                                                else {
                                                    foreach(split(";", $media) as $media_path) {
                                                        if(file_exists("$root/$media_path")) {
                                                            unlink("$root/$media_path"); // DELETE FILE!
                                                            echo "<p class='done'>Deleted media file: <a href='$root/$media_path'>$root/$media_path</a></p>\n";
                                                        }
                                                        else {
                                                            echo "<p class='warning'>File not found: <a href='$root/$media_path'>$root/$media_path</a></p>\n";
                                                            //$errors++;
                                                        };
                                                    };
                                                    $folder = $root."/".path(array_shift(split(";", $media)), "dirname");
                                                    if(!in_array($folder, $folders)) { $folders[] = $folder; };
                                                };
                                            };
                                        };
                                    };
                                };
                            };
                        };
                        
                    };
                    
                    // Delete empty folders
                    echo "<h2>DELETE EMPTY FOLDERS</h2>\n";
                    foreach($folders as $folder) {
                        if(count(listDir($folder, "*")) == 0) {
                            removeDir($folder);
                            echo "<p class='done'>Deleted empty folder: <a href='$folder'>$folder</a></p>\n";
                        }
                        else {
                            echo "<p class='log'>Folder is not empty: <a href='$folder'>$folder</a></p>\n";
                        };
                    };
                };
                
                function deleteFromOrder($file_path) {
                    echo "<h2>REMOVE FROM ORDER LIST</h2>\n";
                    //$delete_item = array_shift(split(".xml", path($file_path, "basename")));
                    $delete_item = path($file_path, "filename");
                    $dir = path($file_path, "dirname");
                    $file = $dir."/".path($dir, "filename").".order";
                    if(file_exists($file)) {
                        $file_content = array_map("trim", file($file));
                        $new_content = array();
                        foreach($file_content as $item) {
                            echo "> item: $item / $delete_item<br>\n";
                            if($item != $delete_item) { $new_content[] = $item; };
                        };
                        // Save changes
                        if(count($new_content) < count($file_content) && safeSave($file, join("\n", $new_content))) {
                            echo "<p class='done'>Item <a href='$delete_item'>$delete_item</a> removed from order file:<a href='$file'>$file</a></p>\n";  
                        }
                        else {
                            echo "<p class='log'>Item <a href='$delete_item'>$delete_item</a> already not on list:<a href='$file'>$file</a></p>\n";
                            //$errors++;
                        };
                    }
                    else {
                         echo "<p class='log'>Order file not found: <a href='$folder'>$folder</a></p>\n";
                    }
                };
            
                // ======================================
                //         Remove page & content
                // ======================================
            
                $delete_success = false;
        
                if(is_string($delete_path) && $delete_path != "" && path($delete_path, "extension") != "") {
                    foreach(array("", ".draft", ".prev") as $ext) {
                        $file_path = $delete_path.$ext;
                        echo "Document: $file_path\n";
                        if($file_path != "" && file_exists($file_path) && !is_dir($file_path)) {
                            
                            deleteMedia($file_path, $root);
                            
                            echo "<h2>DELETE XABLE DOCUMENT</h2>\n";
                            
                            if(safeDelete($file_path)) {
                                echo "<p class='done'>Deleted document: <a href='$file_path'>$file_path</a></p>\n";
                                $delete_success = true;
                                // delete bak if not main xml document
                                if(path($file_path, "extension") != "xml") {
                                    unlink("$file_path.bak");
                                };
                            }
                            else {
                                echo "<p class='error'>Failed to delete: <a href='$file_path'>$file_path</a></p>\n";
                                $errors++;
                            };
                            
                        }
                        else {
                            echo "<p class='log'>Document not found: <a href='$file_path'>$file_path</a></p>\n";
                        };
                        echo "<hr><br>\n";
                    };
                    deleteFromOrder($delete_path, $root);

                };
    
                if($delete_success == false) {
                    echo "<p class='error'>Not found any version of document: <a href='$delete_path'>$delete_path</a></p>\n";
                    $errors++;
                };
            
                // ======================================
                //                 ERRORS
                // ======================================

                if($errors > 0) {
                    echo "<p class='error'>Page not removed due to previous errors ($errors)</p>\n";
					$get = "?path=$delete_path&popup=".urlencode("Nie udało się usunąć strony|error");
                    echo "<input type='hidden' id='back_path' value='$save_path'>\n";
                }
                else {
                    if($save_path == $delete_path) { $save_path = "$root/settings.xml"; };
                    
                    echo "<p class='done'>Page removed: <a href='$delete_path'>$delete_path</a></p>\n";
					$get = "?popup=".urlencode("Strona została usunięta|done");
                    echo "<input type='hidden' id='back_path' value='$save_path'>\n";
                    addLog("page removed", $delete_path);
				};
				echo "<a href='index.php$get'><button>Back to editor</button></a>\n";
                // Errors count output for js action
                echo "<input type='hidden' id='errors' value='$errors'>\n";
            
            ?>
        </article>
        <script src='script/jquery-3.1.0.min.js'></script>
        <script>
            $(document).ready(function() {
                errors = parseInt( $("#errors").val() );
                if(errors == 0) {
                    path = $("#back_path").val();
                    setTimeout(function() {
                        location.href = "index.php?path=" + encodeURIComponent(path) + "&popup=" + encodeURIComponent("Strona została usunięta|done");
                    }, 1000);
                    //$("article").show();
                    //$("#loader").fadeOut(200);
                }
                else {
                    $("article").show();
                    $("#loader").fadeOut(200);
                };
            });
        </script>
        
    </body>
</html>