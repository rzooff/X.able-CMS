<?php
    // ======================================
    //          ><.able CMS - CREATOR
    //        (C)2016 maciejnowak.com
    //          v.2.0 build.0
    // ======================================
	// compatibile: php4 or higher

    require("modules/_session-start.php");
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

    $log = array();
    $error = array();
    $modified_files = array();
    $path = $_POST['path'];
    if($path == "") { $dir = $root; } else { $dir = $root."/".$path; };

    // ====== ACTIONS ======

    // New Folder
    if($_GET['action'] == "new_folder") {
        $folder = $dir."/".$_POST['filename'];
        mkdir($folder);
        if(file_exists($folder)) {
            $log[] = "New folder created: $folder";
        }
        else {
            $error[] = "New folder NOT created: $folder";
        };
        $modified_files[] = path($folder, "basename");
    }
    // New File
    elseif($_GET['action'] == "new_file") {
        $file = $dir."/".$_POST['filename'];
        file_put_contents($file, "");
        if(file_exists($file)) {
            $log[] = "New file created: $file";
        }
        else {
            $error[] = "New file NOT created: $file";
        };
        $modified_files[] = path($file, "basename");
        $current_file = $current_dir."/".$_POST['filename'];
    }
    // Delete
    else if($_GET['action'] == "delete") {
        foreach($_POST['selected'] as $name) {
            $folder = $dir."/".$name;

            if(!file_exists($folder)) {
                $error[] = "File/folder not found: $folder";
            }
            else if(is_dir($folder)) {
                if($folder != "" && $folder != $root) {
                    removeDir($folder);
                    $log[] = "Folder deleted: $name";
                }
                else {
                    $error[] = "Root & Admin folder cannot be deleted: $folder";
                };
            }
            else {
                unlink($folder);
                $log[] = "File deleted: $name";
            };
        };
    }
    // rename
    else if($_GET['action'] == "rename") {
        $old = $_POST['selected'][0];
        $new = $_POST['filename'];
        rename("$dir/$old", "$dir/$new");
        if(!file_exists("$dir/$old") && file_exists("$dir/$new")) {
            $log[] = "Rename done: $old -> $new";
            $modified_files[] = $new;
        }
        else {
            $error[] = "Unable to rename file/folder: $old -> $new";
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
            $log[] = "Copy done: $old -> $new";
            $modified_files[] = $new;
        }
        else {
            $error[] = "Unable to copy file/folder: $old -> $new";
        };
    }
    // COPY / CUT
    else if($_GET['action'] == "copy" || $_GET['action'] == "cut") {
        $_SESSION['clipboard_action'] = $_GET['action'];
        $_SESSION['clipboard_content'] = array();
        foreach($_POST['selected'] as $name) {
            $_SESSION['clipboard_content'][] = "$dir/$name";
            $log[] = "File(s) added to clipboard: $name</p>";
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
                    $log[] = "Copy-Paste: $old -> $new";
                }
                else {
                    rename($old, $new);
                    $log[] = "Cut-Paste: $old -> $new";
                };
                $modified_files[] = path($old, "basename");
            }
            else {
                $error[] = "File/folder not found!";
            };
        };

        if($_SESSION['clipboard_action'] == "cut") {
            $_SESSION['clipboard_content'] = array();
            $log[] = "Clipboard cleared!</p>";
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
                $log[] = "Uploaded file: $dir/$name";
                $modified_files[] = $name;
            }
            else {
                $error[] = "Upload failed: $dir/$name";
            };
        }
    }
    // SAVE edited
    else if($_GET['action'] == "save") {
        $file = $dir."/".$_POST['filename'];
        file_put_contents($file, $_POST['content']);
        if(file_exists($file)) {
            $log[] = "File saved: $file";
            $modified_files[] = $_POST['filename'];
            $current_file = $current_dir."/".$_POST['filename'];
        }
        else {
            $error[] = "Saving failed: $file";
        };
    }
    // unknown
    else {
        $log[] = "No action.";
    };



?>

<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>X.able CMS / Explorer</title>
        <link href='http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900|Inconsolata:400,700|Audiowide&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="style/foundation-icons.css" />
        <link rel="stylesheet" type="text/css" href="style/xable_creator.css" />
        <link rel="stylesheet" type="text/css" href="style/xable_explorer.css" />
        
        <script src='script/jquery-3.1.0.min.js'></script>
        <script src='script/functions.js'></script>
	</head>
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
            <nav>
                <div id="menu_bar">
                    <label class='logo'>
                        <span>&gt;&lt;</span>
                    </label>
                    <label class='title menu'>
                        <p>Explorer</p>
                        <ul>
                            <li>Creator</li>
                            <li>Users</li>
                            <li>Update</li>
							<li class='separator'><hr></li>
                            <li>Quit</li>
                        </ul>
                    </label>
                </div>
            </nav>
                
            <article id='explorer'>
                <h3><span class="article_icon fi-folder"></span>File browser</h3>
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
                                    "\t\t<td class='filename'>Name</td>\n".
                                    "\t\t<td class='size'>Size</td>\n".
                                    "\t\t<td class='time'>Modified</td>\n".
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
                                        "\t\t<td class='filename'><input type='checkbox' class='select' name='selected[]' value='$name'><span>$name</span></td>\n".
                                        "\t\t<td class='file_info size'><span>$size</span></td>\n".
                                        "\t\t<td class='file_info time'><span>$time</span></td>\n".
                                    "\t</tr>\n";

                            };
                            echo "</table>\n";
                        ?>
                    </form>
                </section>
                <p><label><input type='checkbox' name='show_hidden' id='show_hidden_trigger' value='show_hidden'>Show hidden files</label></p>
                <button class='new_folder'>New folder</button>
                <button class='new_file'>New file</button>
                <button class='upload'>Upload</button>
                <!-- <button class='deselect'>Deselect All</button> -->

            </article>
            
            <article id='clipboard'>
                <h3><span class="article_icon fi-marker"></span>Clipboard (<?php echo $_SESSION['clipboard_action']; ?>)</h3>
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
                    <button class='paste'>Paste</button>
                </form>
            </article>
            
        </main>
        <aside>
            <div id='code'>
                <?php
                    $text_files = split(",", "css,csv,draft,eml,htm,html,ini,js,log,lsp,order,php,prev,rtf,template,txt,xml");
                    $image_files = split(",", "bmp,gif,ico,jpg,jpeg,png,svg,tif,tiff");
                
                    if($current_file && file_exists("$root/$current_file") && !is_dir("$root/$current_file")) {
                        $file = "$root/$current_file";
                        echo "<input type='hidden' id='current_file' value='$current_file'>\n";
                        echo "<input type='hidden' id='root' value='$root'>\n";
                        echo "<a href='$file' target='_blank'>".path($current_file, "basename")."</a> <span class='flag edit'>[edit]</span><hr>";
                        
                        if((substr(path($file, "basename"), 0, 1) == "." && path($file, "filename") == "") || in_array(path($file, "extension"), $text_files)) {
                            $content = file($file);

                            foreach($content as $txt) {
                                $txt = str_replace("<", "&lt;", $txt);
                                $txt = str_replace(">", "&gt;", $txt);
                                $txt = str_replace(" ", "&nbsp;", $txt);
                                $txt = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $txt);

                                echo "<span class='tag'>$txt</span><br>\n";
                            };
                        }
                        elseif(in_array(path($file, "extension"), $image_files)) {
                            echo "<figure style='background-image:url(\"$file\")'></figure>\n";
                        }
                        elseif(path($file, "extension") == "pdf") {
                            echo "<embed src='$file' type='application/pdf'>\n";
                        }
                        else {
                            echo "<span class='flag'>No preview avaliable.</span>";
                        };
                    }
                    else {
                        echo "<span class='flag'>Nothing selected.</span>";
                    };
                ?>
            </div>
        </aside>
        <textarea id='file_content'><?php echo join("", $content); ?></textarea>

        <script src='script/xable_explorer.js'></script>
        
	</body>
</html>

    

