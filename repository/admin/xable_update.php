<?php
    // ======================================
    //              ><.able CMS
    //      (C)2015-2019 maciejnowak.com
    // ======================================
    // compatibile: php5.4+ or higher

    //error_reporting(E_ALL);

    require("modules/_session-start.php");

    $panel_name = "update";
    $panel_label = localize("update-label");

    $installer_folder = "_installer"; // Installer archives repositories folder
    $xable_website = "http://xable.maciejnowak.com";

    // ======================================
    //               Actions
    // ======================================

    $error = [];
    $action_done = "";

    // ====== REMOVE ======
    if(is_string($_GET['remove']) && $_GET['remove'] != "") {
        $filepath = $installer_folder."/".$_GET['remove'];
        if(file_exists($filepath)) {
            unlink($filepath);
            //header("Location: xable_update.php");
        }
        else {
            $error[] = str_replace("@filename", $filepath, localize("not-found-alert"));
        };
    }
    // ====== UPLOAD & INSTALL ======
    elseif(is_array($_FILES['installer'])) {
        $zip = $_FILES['installer'];

        if(is_array($zip) && count($zip) > 0 && $zip['error'] == 0) {
            
            $root = $ini_pathes['root'];
            $unzip = "unzip.php.txt";
            $filename = $zip['name'];
            $temp = $zip['tmp_name'];
            $zip_path = "$installer_folder/$filename";

            copy($temp, $zip_path);
            extractArchive($zip_path, $root);
            
            if(file_exists("$root/install/xable")) {
                // Copy new CMS from installer
                rename("$root/install/xable/admin", "$root/admin-new");
                
                // Installer contents
                mkdir("$root/admin-new/_installer/");
                foreach(listDir("$root/admin/_installer", "zip,?") as $zip) {
                    rename($zip, "$root/admin-new/_installer/".path($zip, "basename"));
                }
                foreach(listDir("$root/install", "txt,php,?") as $file) {
                    rename($file, "$root/admin-new/_installer/".path($file, "basename"));
                };
                // Move site Backups
                rename("$root/admin/_backup", "$root/admin-new/_backup");
                
                // Restore Users/Groups
                removeDir("$root/admin-new/_users");
                mkdir("$root/admin-new/_users");
                copyDir("$root/admin/_users", "$root/admin-new/_users");
                // Restore xable.ini file (keep copy of a new one)
                rename("$root/admin-new/xable.ini", "$root/admin-new/xable-updated.ini.bak");
                copy("$root/admin/xable.ini", "$root/admin-new/xable.ini");
                // Restore Plugins
                foreach(listDir("$root/admin/_plugins", "/") as $plugin) {
                    if(!file_exists("$root/admin-new/_plugins/$plugin")) {
                        copyDir("$root/admin/_plugins/$plugin", "$root/admin-new/_plugins/$plugin");
                    }
                };
                
                // PostInstall CleanUP
                rename("$root/admin", "$root/admin-old");
                removeDir("$root/install");
                rename("$root/admin-new", "$root/admin");
                $action_done = "updated";

            }
            else {
                $error[] = localize("installer-unzip-failed"); //Błąd uploadu instalatora
            };
        }
        else {
            $error[] = localize("installer-upload-failed"); //Błąd uploadu instalatora
        };
    }
    // ====== UPDATE CHANGELOG ======
    else if($_GET['action'] == "changelog") {
        $log_path = "doc/change.log";
        $version_path = "doc/version.txt";
        
        $changes = [];
        $notes = [];
        foreach(file($log_path) as $txt) {
            //echo "$txt<br>";
            if(count($notes) > 0 || substr($txt, 0, 1) == "=") {
                $notes[] = $txt;
            }
            else {
                $changes[] = $txt;
            }
        }
        //arrayList($changes);
        $new_change = [];
        $new_change[] = $_POST['time']."\n";
        foreach(explode("\n", $_POST['info']) as $txt) {
            $new_change[] = "\tInfo:\t".$txt."\n";
        };
        $new_change[] = "\tFiles:\t".$_POST['files']."\n";
        $new_change[] = "\n";
        $log_content = array_merge($changes, $new_change, $notes);
        safeSave($log_path, join("", $log_content));
        
        $xable_version = trim(array_shift(file("doc/version.txt")));
        
        $version = array_shift(explode(";", $xable_version));
        $build = str_replace("-", "", array_shift(explode(",", $_POST['time'])));
        safeSave($version_path, "$version;$build");
    }
    else {
        // no action
    };

    // ======================================
    //               Pannel
    // ======================================

    // ====== CREATE INSTALLER ======
    if($_GET['action'] == "installer") {
        
        $xable_version = trim(array_shift(file("doc/version.txt")));
        $version = "v".array_shift(explode(";", $xable_version));
        $installer_name = "Xable-CMS_".$version."_".date("Ymd").".zip";    // New installer archive filename
        $archive_main = "install";                                         // Main folder in installer archive
        $archive_content = "$archive_main/xable";                          // Contents folder in installer archive
        $admin_folder = $_SESSION['admin_folder'];

        // ====== Excluded ======
        $exclude = [];
        // Excluded in root
        foreach(array("_bak", "install", "redirect") as $folder) { $exclude[] = "$root/$folder"; };
        // Excluded in root/admin
        foreach(array($installer_folder, "_backup", "_update", "_bak") as $folder) { $exclude[] = "$root/$admin_folder/$folder"; };
        // Excluded list from xable.ini
        foreach(explode(",", $_SESSION["ini_site_options"]["backup_exclude"]) as $folder) {
            $folder = "$root/$folder";
            if(!in_array($folder, $exclude)) { $exclude[] = $folder; };
        };
        
		// ====== Update Scripts in site folder ======
		// update sript: functions.php, xml.script
		$update_script = array("functions.js", "functions.php", "xml.php");
		$script_folder = "script";
		foreach($update_script as $file) { copy("$script_folder/$file", "$root/$script_folder/$file"); };
		// ====== Create ZIP Archive ======
		$zip_log = [];
		$zip_path = "$installer_folder/$installer_name";
		$zip_log[] = "$zip_path";
		if(file_exists($zip_path)) { unlink($zip_path); }; // Overwrite existing!
        
        $zip = new ZipArchive;
        if($zip->open($zip_path, ZipArchive::CREATE)) {
        
            foreach(listDir($installer_folder) as $file) {
                if(!is_dir("$installer_folder/$file") && path($file, "extension") != "zip") {
                    $path = "$installer_folder/$file";
                    $zip_path = "$archive_main/$file";
                    $zip_log[] = "$path -> $zip_path";
                    $zip->addFile($path, $zip_path);
                };
            };
            
            //arrayList($zip_log);
            
            $excluded_extensions = [ "bak", "prev" ];

            foreach(filesTree($root, false, $exclude) as $path) {
                
                $basename = path($path, "basename");
                $dirname = substr(path($path, "dirname"), strlen($root) + 1);
                $extension = path($path, "extension");
                $filename = path($path, "filename");
                
                // .hidden_folder test
                $hidden = false;
                foreach(explode("/", $dirname) as $folder) {
                    if(substr($folder, 0, 1) == ".") { $hidden = true; };
                }

                //$brench_root = array_shift(explode("/", $dirname));
                
                if(!$hidden && !is_dir($path) && !in_array($extension, $excluded_extensions)) {
                    if($dirname == "" && $basename == ".htaccess") { $basename = "_htaccess"; };
                    if($dirname != "") { $dirname = $dirname."/"; };
                    
                    $zip_path = $archive_content."/".$dirname.$basename; // zip path -> instal/xable/<relative path>
                    $zip->addFile($path, $zip_path);
                    $zip_log[] = "$path -> $zip_path";
                };

            };

			// Close zip
			$zip->close();
            
        }; // zip / end
    } // Create installer / end
	else {
		$zip_log = false;
	};

    // Installer archives list
    $installer_packages = [];
    foreach(listDir($installer_folder, "zip") as $file) {
        $data = explode("_", path($file, "filename"));
        if(count($data) > 1) {
            $installer_packages[join("_", array_reverse($data))] = $file;
        };
    };
    krsort($installer_packages);
?>

<!doctype html>
<html>
    <?php require("modules/xable_head.php"); ?>
	<body>
        
        <?php
            // ====== ERRORS ======
            if(count($error) > 0) {
                echo "<script> alert(\"".join("\\n", $error)."\"); </script>\n";
                arrayList($_POST);
                arrayList($_FILES);
            };
        ?>
        
        <main>
            <?php require("modules/xable_nav.php"); ?>
            
            <article id='update'>
                <h3><span class="article_icon fi-upload-cloud"></span><?php echo localize("update-label"); ?></h3>
                <p><?php echo localize("check-for-updates"); ?>:</p>
                <p><a href='<?php echo $xable_website; ?>' target='_blank'><?php echo localize("xable-website"); ?></a></p>
                <form method='post' action='xable_update.php' enctype='multipart/form-data'>
                    <p><?php echo localize("add-zip"); ?>:</p>
                    <section>
                        <input name='installer' class='installer' type='file' accept='.zip' value=''>
                    </section>
                    <button class='update' name='action' value='upload'><?php echo localize("update-button"); ?></button>
                </form>
            </article>
                
            <article id='create'>
                <h3><span class="article_icon fi-archive"></span><?php echo localize("new-zip"); ?></h3>
                <p><?php echo localize("zip-preview"); ?>:</p>
                <section id='content_preview'>
					<details>
						<summary>install</summary>
                            <?php
                                foreach(listDir($installer_folder) as $file) {
                                    if(!is_dir("$installer_folder/$file") && path($file, "extension") != "zip") {
                                        echo "<p class='file type_php'>$file</p>";
                                    };
                                };
                            ?>
							<details>
								<summary>xable</summary>
								<?php htmlTree($root, "name", false, $exclude) ?>
							</details>
					</details>
					<p class='file type_php'>readme.txt</p>
                </section>
                <button class='confirm'><?php echo localize("create-label"); ?></button>
            </article>
            
            <article>
                <h3><span class="article_icon fi-wrench"></span><?php echo localize("tools-label"); ?></h3>
                <section>
                    <?php
                        $tools_folder = "_tools";
                        echo "\n";
                        echo "<ul id='advanced_tools'>\n";
                        foreach(listDir($tools_folder, "php") as $file) { 
                            $title = explode("_", path($file, "filename"));
                            if(strlen($title[0]) < 3 && is_numeric($title[0])) { $num = array_shift($title); } else { $num = 0; };
                            $title = capitalize(join(" ", $title));
                            if($_SESSION["logged_user"] == "rzooff" || $num < 90) {
                                echo "<li>&bull; <a href='$tools_folder/$file' target='_blank'>$title</a></li>\n";
                            }
                        };
                        echo "</ul>\n";
                    ?>
                </section>
            </article>
            
        </main>
        <aside>
            <div id='code'>
                <div id='changelog'>
                    <span class='preview fi-calendar'></span>
                    <span class='add fi-plus'></span>
                    <?php
                        $log_path = "doc/change.log";
                        echo "<input type='hidden' class='path' value='$log_path'>\n";
                        $changes = [];
                        $notes = [];
                        foreach(file($log_path) as $txt) { $changes[] = $txt; };
                        echo "<textarea class='changes'>".join("<br>", $changes)."</textarea>\n";
                    ?>
                </div>

                <?php
                    // Installer Files
                    echo "<br><span class='flag'><hr>".localize("installer-files")." (".localize("click-download").")<hr></span><br>\n";
                    foreach(listDir($installer_folder, ".") as $file) {
                        if(path($file, "extension") != "zip" && $file != "install.php") {
                            $size = path("$installer_folder/$file", "size");
                            echo "<span class='tag'>$installer_folder/</span><a href='$installer_folder/$file' download>$file</a>&nbsp;<span class='flag'>$size kB</span><br>\n";
                        };
                    };
                
                    // Installer Packages
                    echo "<br><span class='flag'><hr>".localize("installer-zip")." (".localize("click-download").")<hr></span><br>\n";
                    foreach($installer_packages as $file) {
                        $size = path("$installer_folder/$file", "size");
                        echo "<span class='tag'>$installer_folder/</span><a href='$installer_folder/$file' download>$file</a>&nbsp;<span class='flag'>$size kB</span> <span class='remove' value='$file'>[x]</span><br>\n";
                    };
					
					if(is_array($zip_log)) {
						echo "<br><span class='flag'><hr>".localize("zip-log")."<hr></span><br>\n";
						foreach(array_keys($zip_log) as $n) {
							if($n == 0 && file_exists($zip_log[0])) {
								echo "<span class='flag'>".localize("created-file").":</span> ".$zip_log[0]."<br><br>\n";
							}
							else {
								echo "<span class='flag'>$n.</span> <span class='tag'>".str_replace(" -> ", "</span> <span class='flag'>-></span> ", $zip_log[$n])."<br>\n";
							};
						};
					};
                
                    echo "<input id='action_done' type='hidden' value='$action_done'>\n";
                ?>
            </div>
        </aside>
	</body>
</html>