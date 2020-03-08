<?php
    // ======================================
    //              ><.able CMS
    //      (C)2015-2019 maciejnowak.com
    // ======================================
    // compatibile: php5.4+ or higher

    //error_reporting(E_ALL);

    require("modules/_session-start.php");

    $panel_name = "explorer";
    $panel_label = localize("file-explorer-label");

    $admin_folder = $_SESSION['admin_folder'];

    // ======================================
    //               EXPLORER
    // ======================================
    //echo "PATH -> ".$_GET['path']."<br>\n";

    if(is_string($_GET['path'])) {
        if(is_dir($root."/".$_GET['path'])) {
            $current_dir = $_GET['path'];
            $current_file = false;
        }
        else {
            $current_file = $_GET['path'];
            $current_dir = path($current_file, "dirname");
        };
    }
    elseif(is_string($_POST['path']) && $_POST['path'] != "") {
        if(is_dir($root."/".$_POST['path'])) {
            $current_dir = $_POST['path'];
            $current_file = false;
        }
        else {
            $current_file = $_GET['path'];
            $current_dir = path($current_file, "dirname");
        };
    }
    else {
        if(!is_string($_GET['path']) && !is_string($_POST['path'])) { unset($_SESSION['clipboard_content']); };
        $current_dir = "";
        $current_file = false;
    };

    // ======================================
    //               ACTIONS
    // ======================================
    
    //echo "GET<br>\n";
    //arrayList($_GET);

    $log = [];
    $error = [];
    $modified_files = [];
    $path = $_POST['path'];
    if($path == "") { $dir = $root; } else { $dir = $root."/".$path; };

    // ====== ACTIONS ======

    // New Folder
    if($_GET['action'] == "new_folder") {
        $folder = $dir."/".$_POST['filename'];
        mkdir($folder);
        if(file_exists($folder)) {
            $log[] = localize("new-folder-created").": $folder"; //Utwrzono nowy folder
        }
        else {
            $error[] = localize("new-folder-failed").": $folder"; //Nowy folder NIE został utworzony
        };
        $modified_files[] = path($folder, "basename");
    }
    // New File
    elseif($_GET['action'] == "new_file") {
        $file = $dir."/".$_POST['filename'];
        file_put_contents($file, "");
        if(file_exists($file)) {
            $log[] = localize("new-file-failed").": $file"; //Utworzono nowy plik
        }
        else {
            $error[] = localize("new-file-failed").": $file"; //Nowy plik NIE został utworzony
        };
        $modified_files[] = path($file, "basename");
        $current_file = $current_dir."/".$_POST['filename'];
    }
    // Delete
    else if($_GET['action'] == "delete") {
        foreach($_POST['selected'] as $name) {
            $folder = $dir."/".$name;

            if(!file_exists($folder)) {
                $error[] = localize("file-folder-not-found").": $folder"; //Brak pliku/folderu
            }
            else if(is_dir($folder)) {
                if($folder != "" && $folder != $root) {
                    removeDir($folder);
                    $log[] = localize("folder-deleted").": $name"; //Folder usunięty
                }
                else {
                    $error[] = localize("important-folder-alert").": $folder"; //Folder bazowy i administracyjny nie mogą zostac usunięte
                };
            }
            else {
                unlink($folder);
                $log[] = localize("file-deleted").": $name"; //Plik usunięty
            };
        };
    }
    // rename
    else if($_GET['action'] == "rename") {
        $old = $_POST['selected'][0];
        $new = $_POST['filename'];
        rename("$dir/$old", "$dir/$new");
        if(!file_exists("$dir/$old") && file_exists("$dir/$new")) {
            $log[] = localize("rename-done").": $old -> $new"; //Zmieniono nazwę
            $modified_files[] = $new;
        }
        else {
            $error[] = localize("rename-failed").": $old -> $new"; //Nie udało się zmienić nazwy
        };
    }
    // duplicate
    else if($_GET['action'] == "duplicate") {
        $old = $_POST['selected'][0];
        $new = $_POST['filename'];
        if(is_dir("$dir/$old")) {
            copyDir("$dir/$old", "$dir/$new");
        }
        else {
            copy("$dir/$old", "$dir/$new");

        };
        if(file_exists("$dir/$new")) {
            $log[] = localize("copy-done").": $old -> $new"; //Skopiowane
            $modified_files[] = $new;
        }
        else {
            $error[] = localize("dopy-failed").": $old -> $new"; //Kopiowanie nie powiodło się
        };
    }
    // COPY / CUT
    else if($_GET['action'] == "copy" || $_GET['action'] == "cut") {
        $_SESSION['clipboard_action'] = $_GET['action'];
        $_SESSION['clipboard_content'] = array();
        foreach($_POST['selected'] as $name) {
            $_SESSION['clipboard_content'][] = "$dir/$name";
            $log[] = localize("added-to-clipboard").": $name</p>"; //Dodane do pamięci podręcznej
        };
    }
    // PASTE -> no paste in the same location!!!! overwrite block?
    else if($_GET['action'] == "paste") {
        foreach($_SESSION['clipboard_content'] as $old) {
            if(file_exists($old)) {
                $new = "$dir/".path($old, "basename");
                if($_SESSION['clipboard_action'] == "copy") {
                    if(is_dir($old)) {
                        copyDir($old, $new);
                    }
                    else {
                        copy($old, $new);
                    };
                    $log[] = localize("added-to-clipboard").": $old -> $new"; //Kopiuj-wklej
                }
                else {
                    rename($old, $new);
                    $log[] = localize("added-to-clipboard").": $old -> $new"; //Wytnij-wklej
                };
                $modified_files[] = path($old, "basename");
            }
            else {
                $error[] = localize("file-folder-not-found"); //Brak pliku/folderu!
            };
        };

        if($_SESSION['clipboard_action'] == "cut") {
            $_SESSION['clipboard_content'] = array();
            $log[] = localize("clipboard")."!</p>"; //Pamięć podręczna opróżniona
        };
    }
    // UPLOAD
    else if($_GET['action'] == "upload") {
        $names = $_FILES['upload']['name'];
        $tmps = $_FILES['upload']['tmp_name'];
        foreach(array_keys($names) as $i) {
            $name = $names[$i];
            $tmp = $tmps[$i];
            copy($tmp, "$dir/$name");
            if(file_exists("$dir/$name")) {
                $log[] = localize("upload-done").": $dir/$name"; //Załadowany plik
                $modified_files[] = $name;
            }
            else {
                $error[] = localize("upload-failed").": $dir/$name"; //Załadowanie pliku nie powiodło się
            };
        }
    }
    // SAVE edited
    else if($_GET['action'] == "save") {
        $file = $dir."/".$_POST['filename'];
        file_put_contents($file, $_POST['content']);
        if(file_exists($file)) {
            $log[] = localize("save-done").": $file"; //Zapisany plik
            $modified_files[] = $_POST['filename'];
            $current_file = $current_dir."/".$_POST['filename'];
        }
        else {
            $error[] = localize("save-failed").": $file"; //Zapisanie pliku nie powiodło się
        };
    }
    // UnZip selected
    else if($_GET['action'] == "unzip") {
        $folder = "$root/$current_dir";
        $zip_path = $folder."/".array_shift($_POST['selected']);
        $unzip_folder = "$folder/".path($zip_path, "filename");
        $unzip_folder = uniqueFilename($unzip_folder);
        if(extractArchive($zip_path, $unzip_folder)) {
            $modified_files[] = path($unzip_folder, "basename");
        };
    }
    // Zip selected
    else if($_GET['action'] == "zip") {
        $folder = "$root/$current_dir";
        $files = [];

        foreach($_POST['selected'] as $name) {
            $path = "$root/$current_dir/$name";

            if(is_dir($path) && ($subfolder_content = filesTree($path, "."))) {
                $files = array_merge($files, $subfolder_content);
            }
            else {
                $files[] = $path;
            }
        };
        
        $zip_path = "$root/$current_dir/Archive.zip";
        $zip_path = uniqueFilename($zip_path);
        
        $zip = new ZipArchive;
        if($zip->open($zip_path, ZipArchive::CREATE)) {
            foreach($files as $path) {
                $relative_path = substr($path, strlen("$root/$current_dir/"));
                $filename = path($relative_path, "filename");
                $extension = path($relative_path, "extension");
                // Fix for hidden files
                if($filename == "" && $extension != "") {
                    $relative_path = path($relative_path, "dirname")."/_$extension";
                }
                // Add to zip
                $zip->addFile($path, $relative_path);
                //echo "> $path -> $zip_path<br>\n";
            }
            $zip->close();
            //echo "ZIP: ".$zip_path."<br>\n";
            $modified_files[] = path($zip_path, "basename");
        }
    }
    // unknown
    else {
        $log[] = localize("no-action"); //Brak akcji
    };
    //arrayList($modified_files);
?>

<!doctype html>
<html>
    <?php require("modules/xable_head.php"); ?>
	<body>
        
        <?php
            // ====== ERRORS / LOG ======
            if(count($error) > 0) {
                echo "<script> alert(\"".join("\\n", $error)."\"); </script>\n";
                arrayList($_POST);
                arrayList($_FILES);
            }
            elseif(count($log) > 0) {
                echo "\n<!--\nLOG:\n".join("\n", $log)."\n-->\n";
            };
        ?>
        
        <main>
            <?php require("modules/xable_nav.php"); ?>
                
            <article id='explorer'>
                <h3><span class="article_icon fi-folder"></span><?php echo localize("browser-label"); ?></h3>
                <section id='current_dir'>
                    <?php
                        if($current_dir == "") {
                            echo "<h2>/</h2>\n";
                            echo "<button class='disabled'><span class='fi-arrow-up'></span></button>\n";
                        }
                        else {
                            echo "<h2>/$current_dir</h2>\n";
                            echo "<button class='path' path='".path($current_dir, "dirname")."'><span class='fi-arrow-up'></span></button>\n";
                        };
                    ?>
                </section>
                <section id='browser'>
                    <form action='xable_explorer.php?action=@action' method='post'>
                        <input type='hidden' class='path' name='path' value='<?php echo $current_dir; ?>'>
                        <input type='hidden' class='show_hidden' name='show_hidden' value='<?php echo $_POST['show_hidden'] ?>'>

                        <?php
                        
                            echo "\n";
                            echo "<table>\n";
                            echo "\t<tr class='header'>\n".
                                    "\t\t<td class='icon deselect'><span class='fi-check'></span></td>\n".
                                    "\t\t<td class='filename'>".localize("name-label")."</td>\n".
                                    "\t\t<td class='size'>".localize("size-label")."</td>\n".
                                    "\t\t<td class='time'>".localize("mod-date-label")."</td>\n".
                                "\t</tr>\n";
                            $dir_list = listDir("$root/$current_dir", "*");
                            echo "<input type='hidden' id='dir_list' value='".join(";", $dir_list)."'>\n";
                            foreach($dir_list as $name) {
                                $path = "$root/$current_dir/$name";
                                if($_SESSION['show_hidden'] != true && substr($name, 0, 1) == ".") { $hidden = "hidden"; } else { $hidden = ""; };
                                if(is_dir($path)) {
                                    $type = "folder";
                                    $icon = "fi-folder";
                                    if(in_array($name, $modified_files)) { $current = "current"; } else { $current = ""; };
                                    $size = "-";
                                    $time = path($path, "modified");
                                }
                                else {
                                    $type = "file";
                                    $icon = "fi-page";
                                    if($name == path($current_file, "basename") || in_array($name, $modified_files)) { $current = "current"; } else { $current = ""; }
                                    $size = path($path, "size");
                                    if($size > 1024) { $size = round(($size / 1024), 1) . " MB"; } else { $size = $size." kB"; };
                                    $time = path($path, "modified");
                                };

                                echo "\t<tr class='path $type $hidden $current' path='$current_dir/$name'>\n".
                                        "\t\t<td class='icon'><span class='$icon'></span></td>\n".
                                        "\t\t<td class='filename'><input type='checkbox' class='select' name='selected[]' value='$name'><span>$name</span><i class='more_options fi-list'></i></td>\n".
                                        "\t\t<td class='file_info size'><span>$size</span></td>\n".
                                        "\t\t<td class='file_info time'><span>$time</span></td>\n".
                                        //"\t\t<td class='more_options'><span class='fi-list'>#</span></td>\n".
                                    "\t</tr>\n";

                            };
                            echo "</table>\n";
                        ?>
                    </form>
                </section>
                <p><label><input type='checkbox' name='show_hidden' id='show_hidden_trigger' value='show_hidden'><?php echo localize("show-hidden-files"); ?></label></p>
                <button class='new_folder'><?php echo localize("new-folder-label"); ?></button>
                <button class='new_file'><?php echo localize("new-file-label"); ?></button>
                <button class='upload'><?php echo localize("upload-label"); ?></button>
                <!-- <button class='deselect'>Deselect All</button> -->

            </article>
            
            <article id='clipboard'>
                <?php $action_title = array("copy" => "copy-label", "cut" => "cut-label"); ?>

                <h3><span class="article_icon fi-marker"></span><?php echo localize("clipboard-label")." (".localize($action_title[ $_SESSION['clipboard_action'] ]).")"; ?></h3>
                <form action='xable_explorer.php?action=paste' method="post" enctype='multipart/form-data'>
                    <input type='hidden' class='path' name='path' value='<?php echo $current_dir; ?>'>
                    <?php
                        if(is_array($_SESSION['clipboard_content']) && count($_SESSION['clipboard_content']) > 0) {
                            foreach($_SESSION['clipboard_content'] as $file_path) {
                                $relative_path = substr($file_path, strlen($root) + 1);
                                echo "<p class='clipboard'>$relative_path<input type='hidden' name='clipboard[]' value='$file_path'></p>";
                            };
                        };
                    ?>
                    <button class='paste'>Wklej</button>
                </form>
            </article>
            
        </main>
        <aside>
            <div id='code'>
                <?php
                    echo "\n";
                    echo "\t\t\t\t<input type='hidden' id='exporer_script_path' value='".$_SERVER['PHP_SELF']."'>\n";
                    echo "\t\t\t\t<input type='hidden' id='root' value='$root'>\n";
                
                    $text_files = explode(",", "css,csv,draft,eml,htm,html,ini,js,log,lsp,order,prev,rtf,template,txt,xml");
                    $image_files = explode(",", "bmp,gif,ico,jpg,jpeg,png,svg,tif,tiff");
                
                    if($current_file && file_exists("$root/$current_file") && !is_dir("$root/$current_file")) {
                        $file = "$root/$current_file";
                        $ext = strtolower(path($file, "extension"));
                        
                        echo "\t\t\t\t<input type='hidden' id='current_file' value='$current_file'>\n";
                        echo "\t\t\t\t<a href='$file' target='_blank'>".path($current_file, "basename")."</a>\n";

                        if((substr(path($file, "basename"), 0, 1) == "." && path($file, "filename") == "") || in_array($ext, $text_files)) {
                            echo "\t\t\t\t<span class='flag edit'>[edytuj]</span><hr>\n";
                            $content = file($file);

                            foreach($content as $txt) {
                                $txt = str_replace("<", "&lt;", $txt);
                                $txt = str_replace(">", "&gt;", $txt);
                                $txt = str_replace(" ", "&nbsp;", $txt);
                                $txt = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $txt);

                                echo "\t\t\t\t<span class='tag'>$txt</span><br>\n";
                            };
                        }
                        elseif(in_array($ext, $image_files)) {
                            echo "\t\t\t\t<figure style='background-image:url(\"$file\")'></figure>\n";
                        }
                        elseif($ext == "pdf") {
                            echo "\t\t\t\t<embed src='$file' type='application/pdf'>\n";
                        }
                        else {
                            echo "\t\t\t\t<span class='flag'>".localize("no-preview")."</span>\n";
                        };
                    }
                    else {
                        echo "\t\t\t\t<span class='flag'>".localize("file-not-selected")."</span>\n";
                    };
                
                ?>
            </div>
        </aside>
        <textarea id='file_content'><?php echo join("", $content); ?></textarea>
	</body>
</html>

    

