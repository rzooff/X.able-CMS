<?php

    require "modules/_session-start.php";
    require "script/upload_images.php";

    $debug_mode = false; // <true> will not redirect back to index & show console log
    $errors = 0; // Errors number - if any, saving changes will be blocked

    $title_tag = "header title";

    //echo "<hr><h3>SESSION</h3><hr>";
    //arrayList($_SESSION);	
    //echo "<hr><h3>GET</h3><hr>";
    //arrayList($_GET);
    //echo "<hr><h3>POST</h3><hr>";
    //arrayList($_POST);
    //echo "<hr><h3>FILES</h3><hr>";
	//arrayList($_FILES);
	//echo "<hr><h3>XML</h3><hr>";
	//echo "\n<textarea cols=100 rows=50>".$_POST['output|xml']."</textarea>\n";
    //echo "<hr>\n";

    // ====== SESSION RESET ======
    function sessionReset($site_options) {
        echo "<h2>RESET</h2>\n";;
        
        if(is_string($site_options['session_reset']) && $site_options['session_reset'] != "") {
            $session_reset = explode(",", $site_options['session_reset']);
            foreach($session_reset as $key) {
                //echo "reset> $key<br>\n";
                unset($_SESSION[$key]);
                echo "Unset: \$_SESSION[\"".$key."\"]<br>\n";
            };
        };
        
        if(is_string($site_options['publish_plugin']) && $site_options['publish_plugin'] != "") {
            $_SESSION["xable-publish_plugin"] = $site_options['publish_plugin'];
            echo "External plugin: ".$_SESSION["xable-publish_plugin"]."<br>\n";
        }
    };

    function managerAdd($file_manager, $key, $files) {
    // -----------------------------------------------
    // $file_manager = <array>
    // $key = <string> file_manager array KEY
    // $files = <string> file or files divided with semicolons
    // -----------------------------------------------
    // RETURNS: <array> with specified files added to array
    // -----------------------------------------------
        echo "# managerAdd: $key -> $files<br>\n";
        $list = $file_manager[$key];
        if(is_array($list) && count($list) > 1 || $list[0] != "") {
            $list = array_merge($list, explode(";", $files));
            $file_manager[$key] = $list;
        }
        else {
            $file_manager[$key] = explode(";", $files);
        };
        arrayList($file_manager);
        return $file_manager;
    };

    function managerDelete($file_manager, $key, $file) {
    // -----------------------------------------------
    // $file_manager = <array>
    // $key = <string> file_manager array KEY
    // $file = <string> file
    // -----------------------------------------------
    // RETURNS: <array> with specified file deleted from array (if any);
    // -----------------------------------------------
        $list = array();
        foreach($file_manager[$key] as $item) {
            if($file != $item) { $list[] = $item; };
        };
        $file_manager[$key] = $list;
        return $file_manager;
    };

    function validFilesOnly($root, $files) {
    // -----------------------------------------------
    // $root = <string> site root
    // $files = <array> files pathes array
    // -----------------------------------------------
    // RETURNS: <array> existing files/folders from input array
    // -----------------------------------------------
        $checked = array();
        foreach($files as $file) {
            if(file_exists("$root/$file")) { $checked[] = $file; };
        };
        return $checked;
    };

?>

<html>
    <head>
		<meta charset='utf-8'>
        <title>X.able CMS / Publish</title>
        <link href='http://fonts.googleapis.com/css?family=Inconsolata:400,700&subset=latin,latin-ext' rel='stylesheet' type='text/css'>

        <style>
            /* ================================== */
            /*               Loader               */
            /* ================================== */

            <?php include "style/loader.css"; ?>

            /* ================================== */
            /*           Console style            */
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

                // ======================================
                //            Main variables
                // ======================================

                // File path
                $edit_path = $_POST['edit_path'];
                $save_path = $_POST['save_path'];

                if(!is_string($save_path) || $save_path == "") { $save_path = $edit_path; };
            
                // Get edited file content
                $file_type = path($save_path,"extension");
                $file_content = $_POST["output|$file_type"];
                // Media size
                $media_size = $settings['media'][0]['multimedia_size'][0]['string'][0];
            
                // File manager array
                $file_manager = array();
                $file_manager['upload'] = validFilesOnly($root, explode(";", $_POST['file_manager|upload']));
                $file_manager['delete'] = validFilesOnly($root, explode(";", $_POST['file_manager|delete']));
            
                // ======================================
                //        FILES DELETE / MANAGER
                // ======================================
            
                // Disable upload / delete actions if needed
                if(in_array($_POST['save_mode'], array("unpublish", "revert", "discard", "undo"))) {
                    $_POST['delete_files'] = "";
                    $_FILES = false;
                    echo "<p class='log'>Disable upload / delete actions</p>\n";
                };

                echo "<h2>DELETE FILES</h2>\n";
                // Files
                if($_POST['delete_files'] != "") {
                    foreach(explode(";", $_POST['delete_files']) as $file) {
                        $file = urldecode($file);
                        if(file_exists("$root/$file")) {
                            $file_manager = managerAdd($file_manager, "new_delete", $file); // add file to DELETED
                            //$file_manager = managerDelete($file_manager, "upload", $file); // remove deleted from uploaded list (cosmetic)
                            echo "<p class='done'>File marked as deleted: <a href='$root/$file'>$root/$file</a></p>\n";
                        }
                        else {
                            echo "<p class='log'>File not found: <a href='$root/$file'>$root/$file</a></p>\n";
                            // no error -> ignore upload error
                        };
                    };
                }
                else {
                    echo "<p class='log'>No files to mark as delete found</p>\n";
                };

                // ======================================
                //         FILE UPLOAD / MANAGER
                // ======================================

                echo "<h2>FILE UPLOADS</h2>\n";
                echo "<p class='info'>Max media size: $media_size px</p>\n";

                foreach(array_keys($_POST) as $key) {
                    // ====== File UPLOAD ======
                    $prefix = "upload|";
                    if(substr($key, 0, strlen($prefix)) == $prefix) {
                        $id = substr($key, strlen($prefix));
                        $val = $_POST[$key];
                        if($val != "") {
                            echo "> id: $id, val: $val<br>";
                            // Extract value data, eg: "mode:name_1.ext;name_2.ext"
                            //echo "val: $val<br>\n";
                            $val = explode(":", $val);
                            $mode = array_shift($val);
                            $names = join(":", $val);
                            //echo "names: $names<br>\n";
                            // Set mode variable for uploadRenameImages()
                            //if($mode == "force") { $mode = "force:".path($names, "filename"); };
                            $mode = array();
                            foreach(explode(";", $names) as $rename) {
                                $mode[] = path($rename, "filename");
                            };
                            $mode = "force:".join(";", $mode);
                            // Find uploaded files
                            if(is_array($_FILES) && is_array($file_data = $_FILES[$id])) {
                                // Destination folder (create if needed)
                                $folder = $root."/".path( array_shift( explode(";", $names) ), "dirname" );
                                //echo "folder -> $folder<br>\n";
                                //echo "mode -> $mode<br>\n";
                                
                                if(makeDir($folder)) {
                                    if(uploadRenameImages($id, $folder, "max:$media_size", $mode)) {
                                        echo "<p class='done'>[$id] File(s) uploaded to: <a href='$folder'>$folder</a></p>\n";
                                        $file_path = array_pop(explode(":", $mode));
                                        $file_manager = managerAdd($file_manager, "new_upload", $names);
                                    }
                                    else {
                                        echo "<p class='error'>[$id] Failed to upload to: <a href='$folder'>$folder</a></p>\n";
                                        //$errors++; cause of 'Cancel' in file select window problem
                                    };
                                }
                                else {
                                    echo "<p class='error'>[$id] Failed to access to: <a href='$folder'>$folder</a></p>\n";
                                    $errors++;
                                };
                                
                            };
                        }
                        else {
                            echo "<p class='log'>[$id] Nothing to upload</p>\n";
                        };
                    };
                };
            
                // ======================================
                //         FILE MANAGER ACTIONS
                // ======================================
            
                function deleteFiles($root, $files) {
                    foreach(explode(";", $files) as $file) {
                        unlink("$root/$file");
                        if(!file_exists("$root/$file")) {
                            echo "<p class='done'>File permamently deleted: <a href='$root/$file'>$root/$file</a></p>\n";
                        }
                        else {
                            echo "<p class='error'>Failed to delete: <a href='$root/$file'>$root/$file</a></p>\n";
                            $errors++;
                        };
                    };
                };
                
                // Clean UPLOADS on new draft!
                if(($_POST['save_mode'] == "publish" || $_POST['save_mode'] == "draft") && !file_exists($save_path.".draft")) {
                    echo "# Clear previous 'upload' & 'delete' lists on NEW DRAFT<br>\n";
                    $file_manager['upload'] = array();
                    $file_manager['delete'] = array();
                };
            
                // Delete (last) UPLOADS on draft discard / revert / undo / unpublish
                if(in_array($_POST['save_mode'], array("discard", "revert", "undo", "unpublish")) && file_exists($save_path.".draft")) {
                    echo "# Delete 'upload' on DISCARD or REVERT or UNPUBLISH<br>\n";
                    $xml = loadXml($save_path.".draft");
                    $files = readXml($xml, "_file_manager upload");
                    if(is_string($files) && $files != "") {
                        deleteFiles($root, $files);
                    };
                };
                    
                // Delete files marked as delete in prevoius version on publish
                if($_POST['save_mode'] == "publish" && file_exists($save_path.".prev")) {
                    echo "# Delete .prev 'deleted' on PUBLISH<br>\n";
                    $xml = loadXml($save_path.".prev");
                    $files = readXml($xml, "_file_manager delete");
                    if(is_string($files) && $files != "") {
                        deleteFiles($root, $files);
                    };
                }; 
                
                // ======================================
                //        ADD FILE MANAGER TO XML
                // ======================================
                
                // Merge new & previous uploads
                if(count($file_manager['new_upload']) > 0) {
                    if(count($file_manager['upload']) > 1 || $file_manager['upload'][0] != "") {
                        $file_manager['upload'] = array_merge($file_manager['upload'], $file_manager['new_upload']);
                    }
                    else {
                        $file_manager['upload'] = $file_manager['new_upload'];
                    };
                };
                unset($file_manager['new_upload']);
            
                // Merge new & previous (to) delete
                if(count($file_manager['new_delete']) > 0) {
                    if(count($file_manager['delete']) > 1 || $file_manager['delete'][0] != "") {
                        $file_manager['delete'] = array_merge($file_manager['delete'], $file_manager['new_delete']);
                    }
                    else {
                        $file_manager['delete'] = $file_manager['new_delete'];
                    };
                };
                unset($file_manager['new_delete']);
            
                // Create XML code
                $manager_content = array();
                $manager_content[] = "\t<_file_manager>";
                foreach(array_keys($file_manager) as $key) {
                    //echo "# $key : ".join(";", $file_manager[$key])."<br>\n";
                    $manager_content[] = "\t\t<$key>";
                    $manager_content[] = "\t\t\t<type>media</type>";
                    $manager_content[] = "\t\t\t<media>";
                    $manager_content[] = "\t\t\t\t<files>".join(";", $file_manager[$key])."</files>";
                    $manager_content[] = "\t\t\t</media>";
                    $manager_content[] = "\t\t\t<set>files</set>";
                    $manager_content[] = "\t\t</$key>";
                };
                $manager_content[] = "\t</_file_manager>";
                
                $file_content = explode("\n", $file_content);
                if(trim($file_content[0]) == "<xable>") { $file_content[0] = "<xable>\n".join("\n", $manager_content); };
                $file_content = join("\n", $file_content);
            
                // ======================================
                //             NEW SUBFOLDER
                // ======================================
            
                echo "<h2>SUBFOLDER</h2>\n";
                
                $subfolder = path($_POST["save_path"], "dirname");
                $folder = path($subfolder, "dirname");
            
                if($errors == 0 && is_string($folder) && $folder != "" && file_exists($folder) && (!file_exists($subfolder) || !listDir($subfolder, "xml,draft"))) {

                    // empty order content
                    $updated_order = [];
                    $updated_order["_order"][0]["title"][0] = $title_tag;
                    if(file_exists("$folder/.order")) {
                        $order_xml = loadXml("$folder/.order");
                        $order_options = readXml($order_xml, "_order options");
                        $updated_order["_order"][0]["options"][0] = $order_options;
                    }
                    else {
                        $updated_order["_order"][0]["options"][0] = "";
                    };
                    $updated_order = XmlFileContent($updated_order);
                    mkdir($subfolder);
                    foreach(listDir($folder, "template") as $template) {
                        copy("$folder/$template", "$subfolder/$template");
                    }
                    if(safeSave("$subfolder/.order", $updated_order)) {
                        echo "<p class='done'>New subfolder created: <a href='$subfolder.daft'>$subfolder</a></p>\n";
                    }
                };
            
                // ======================================
                //             SAVE CMS FILE
                // ======================================

                echo "<h2>SAVE CMS FILE</h2>\n";
                if($errors > 0) {
                    echo "<p class='error'>File not saved due to previous errors ($errors)</p>\n";
                }
                elseif(is_string($file_content) && $file_content != "") {
                    // Add XML declaration to .xml file
                    if($file_type == "xml") { $file_content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".$file_content; };
                    // Save file content
					echo "<h2>FILE CONTENT PREVIEW</h2>\n<textarea>".$file_content."</textarea><br>\n";
                    if($debug_mode != true) {
                        
                        // ====== MODES ======
                        
                        if($_POST['save_mode'] == "unpublish") {
                            rename($save_path, $save_path.".draft");
                            echo "<p class='done'>Unpublished (saved as draft) done successfully: <a href='$save_path.daft'>$save_path.draft</a></p>\n";
                            addLog("unpublished", $save_path);
                        }
                        elseif($_POST['save_mode'] == "discard") {
                            if(safeDelete($save_path.".draft")) {
                                echo "<p class='done'>Draft deleted successfully: <a href='$save_path.draft'>$save_path.draft</a></p>\n";
                                addLog("draft discarded", $save_path);
                            }
                            else {
                                echo "<p class='done'>Failed to delete draft: <a href='$save_path.draft'>$save_path.draft</a></p>\n";
                                $errors++;
                            };
                        }
                        elseif($_POST['save_mode'] == "revert" || $_POST['save_mode'] == "undo") {
                            if(file_exists($save_path.".prev")) {
                                if(file_exists($save_path)) {
                                    rename($save_path, $save_path.".bak"); // published -> bak
                                }
                                else {
                                    unlink($save_path.".bak");
                                };
                                rename($save_path.".prev", $save_path); // previous -> published
                                //rename($save_path.".bak", $save_path.".previous"); // bak -> previous
                                if($_POST['save_mode'] == "revert") {
                                    safeDelete($save_path.".draft");
                                    if(file_exists($save_path.".bak")) {
                                        rename($save_path.".bak", $save_path.".draft"); // bak -> draft (published become a new draft)
                                    };
                                };
                                echo "<p class='done'>Revert to previous done successfully: <a href='$save_path'>$save_path</a></p>\n";
                                addLog("undo/revert to previous", $save_path);
                            }
                            else {
                                echo "<p class='error'>Failed to revert to previous: <a href='$save_path'>$save_path</a></p>\n";
                                $errors++; 
                            };
                        }
                        elseif($_POST['save_mode'] == "draft") {
                            if(safeSave($save_path.".draft", $file_content)) {
                                echo "<p class='done'>Draft saved successfully: <a href='$save_path.draft'>$save_path.draft</a></p>\n";
                                addLog("draft saved", $save_path);
                            }
                            else {
                                echo "<p class='done'>Failed to save draft: <a href='$save_path.draft'>$save_path.draft</a></p>\n";
                                $errors++;
                            };
                        }
                        else { // Publish
                            safeDelete($save_path.".prev");
                            if(file_exists($save_path)) { rename($save_path, $save_path.".prev"); }; // make previous
                            if(safeSave($save_path, $file_content)) {
                                echo "<p class='done'>File published successfully: <a href='$save_path'>$save_path</a></p>\n";
                                safeDelete($save_path.".draft");
                                addLog("published", $save_path);
                            }
                            else {
                                echo "<p class='done'>Failed to publish file: <a href='$save_path'>$save_path</a></p>\n";
                                $errors++;
                            };
                        };
                    }
                    else {
                        echo "<p class='log'>DEBUG MODE -> file not saved: <a href='$save_path'>$save_path</a></p>\n";
                        $errors = -1;
                    };
                    
                }
                else {
                    echo "<p class='error'>Lack of file content to save!</p>\n";
                    $errors++;
                };
                
                // ======================================
                //         UPDATE MAIN NAVIGATION
                // ======================================
            
                //echo "\nauto_navigation_title: ".$_SESSION["ini_site_options"]["auto_navigation_title"]."<br>\n";
            
                if($_SESSION["ini_site_options"]["auto_navigation_title"] == true) {
                    
                    if(($debug_mode == true || $errors == 0) && path($save_path, "dirname") == "$root/pages" && in_array(path($save_path, "extension"), [ "xml", "draft" ])) {

                        // Get page title & filename
                        $new_xml = xmlToArray( $_POST["output|xml"] );
                        if($new_xml["xable"]) { $new_xml = $new_xml["xable"][0]; };
                        $title = $new_xml["header"][0]["title"][0]["text"][0];
                        $file = path($save_path, "filename");

                        if(isset($title) && is_array($title)) {

                            // Load navigation content
                            $navigation_file = "$root/navigation.xml";
                            $navigation = loadXml($navigation_file);

                            // Check for existing item
                            foreach(array_keys($navigation["multi_page"]) as $item_num) {
                                $item = $navigation["multi_page"][$item_num];
                                $href = $item["href"][0]["string"][0];

                                if($file == $href) {
                                    // Update Title
                                    $item["title"][0]["text"][0] = $title;
                                    $navigation["multi_page"][$item_num] = $item;
                                    $title = false;
                                };
                            };

                            // Add item for new page
                            if($title && path($edit_path, "extension") == "template") {
                                $item = $navigation["multi_page"][0];
                                $option_name = $item["active"][0]["checkbox"][0]["option"][0];
                                $item["active"][0]["selected"][0] = $option_name; // Turn on active
                                //$item["active"][0]["selected"][0] = ""; // Trun off active
                                $item["href"][0]["string"][0] = $file;
                                $item["title"][0]["text"][0] = $title;
                                $navigation["multi_page"][] = $item;
                                $title = false;
                            };

                            // Save navigation
                            if(!$title) {
                                //arrayList($navigation);
                                if(safeSave($navigation_file, XmlFileContent($navigation))) {
                                    echo "<p class='done'>item updated: <a href='$save_path'>$file</a> in navigation file: <a href='$navigation_file'>$navigation_file</a></p>\n";
                                }
                                else {
                                    echo "<p class='error'>Faild to update navigation: <a href='$save_path'>$file</a> in order file: <a href='$navigation_file'>$navigation_file</a></p>\n";
                                    $errors++;
                                };
                            }
                        }
                    }
                    
                };
            
                // ======================================
                //          UPDATE .ORDER FILE
                // ======================================
            
                function orederAuto($items) {
                    $updated_items = [];
                    foreach($items as $item) {
                        $key = lowercase(makeId(readXml($item, "title", $_SESSION["admin_lang"])));
                        $updated_items[$key] = $item;
                    }
                    ksort($updated_items);
                    return array_values($updated_items);
                };

                if(($debug_mode == true || $errors == 0) && in_array(path($save_path, "extension"), [ "xml", "draft" ])) {
                    
                    $folder = path($save_path, "dirname");
                    $order_file = "$folder/.order";

                    if($order = loadXml($order_file)) {
                        
                        $title_tag = readXml($order, "_order title");
                        list($article_name, $section_name) = explode(" ", $title_tag);
                        
                        // Get new / update current page data
                        $new_xml = xmlToArray( $_POST["output|xml"] );
                        if($new_xml["xable"]) { $new_xml = $new_xml["xable"][0]; };
                        
                        $new_path = path($save_path, "basename");
                        
                        $new_item = [];
                        $new_item["path"][0]["type"][0] = "string";
                        $new_item["path"][0]["string"][0] = $new_path;
                        $new_item["title"][0]["type"][0] = "text";
                        $new_item["title"][0]["text"][0] = $new_xml[$article_name][0][$section_name][0]["text"][0];

                        foreach(array_keys($order["multi_item"]) as $item_num) {
                            $item_path = readXml($order["multi_item"][$item_num], "path");
                            if($item_path == $new_path) {
                                $order["multi_item"][$item_num] = $new_item;
                                unset($new_item);
                            };
                        };
                        if(isset($new_item)) {
                            $order["multi_item"][] = $new_item;
                        };
                        
                        // ====== Order options ======
                        if($options = readXml($order, "_order options")) { $options = explode(";", $options); }
                        else { $options = []; };
                        // Auto sort
                        if(in_array("auto", $options)) {
                            $order["multi_item"] = orederAuto($order["multi_item"]);
                        };
                        
                        // ====== Output ======
                        if($debug_mode == true) {
                            arrayList($order);
                        }
                        else {
                            // Save
                            if(safeSave($order_file, XmlFileContent($order))) {
                                echo "<p class='done'>item updated: <a href='$save_path'>$new_path</a> in order file: <a href='$order_file'>$order_file</a></p>\n";
                            }
                            else {
                                echo "<p class='error'>Faild to update item: <a href='$save_path'>$new_path</a> in order file: <a href='$order_file'>$order_file</a></p>\n";
                                $errors++;
                            };
                        };
                        
                    }
                };
            
                // Reset ini session variables

                if($errors < 1) { sessionReset($_SESSION["ini_site_options"]); };
                
                // ======================================
                // Errors count output for js action
                echo "<input type='hidden' id='debug_mode' value='$debug_mode'>\n";
                echo "<input type='hidden' id='errors' value='$errors'>\n";
                echo "<input type='hidden' id='save_path' value='$save_path'>\n";
            
                echo "<a href='index.php?path=$save_path&popup=".urlencode(localize("changes-save-failed")."|error")."'><button>Back to editor</button></a>\n"; //Zmiany nie zostały zapisane
            
                // ======================================
                //          EDIT LANGUAGE CHANGE
                // ======================================
            
                echo "EDIT_LANG: ".$_POST["current_edit_lang"]."<br>\n";
                if(is_string($_POST["current_edit_lang"]) && $_POST["current_edit_lang"] != "") {
                    $_SESSION["edit_lang"] = $_POST["current_edit_lang"];
                }

                // ======================================
                // ======================================
                //             SESSION TEMP
                // ======================================
                // ======================================
                
                // Store temp data from $_POST in $_SESSION array variable
                // For usage with plugins
                $session_temp = xmlToArray($_POST["session_temp"]);
                foreach(array_keys($session_temp) as $key) {
                    echo "key: $key<br>\n";
                    $_SESSION["session_temp"][$key] = $session_temp[$key];
                };

            ?>
        </article>
        <?php exportLocalization(); ?>
        <script src='script/jquery-3.1.0.min.js'></script>
        <script>
            $(document).ready(function() {
                errors = parseInt( $("#errors").val() );
                debug_mode = $("#debug_mode").val();
                if(errors == 0 && debug_mode != "true") {
                    path = $("#save_path").val();
                    
                    setTimeout(function() {
                        location.href = "index.php?path=" + encodeURIComponent(path) + "&popup=" + encodeURIComponent(LOCALIZE["changes-save-done"] + "|done"); //Zmiany zostały zapisane
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