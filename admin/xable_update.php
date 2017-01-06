<?php
    // ======================================
    //          ><.able CMS - CREATOR
    //        (C)2016 maciejnowak.com
    //          v.2.0 build.0
    // ======================================
	// compatibile: php4 or higher

    require("modules/_session-start.php");
    $installer_folder = "_installer"; // Installer archives repositories folder
    $xable_website = "http://maciejnowak.com";

    // ======================================
    //               Actions
    // ======================================

    $error = array();

    // ====== REMOVE ======
    if(is_string($_GET['remove']) && $_GET['remove'] != "") {
        $filepath = $installer_folder."/".$_GET['remove'];
        if(file_exists($filepath)) {
            unlink($filepath);
            //header("Location: xable_update.php");
        }
        else {
            $error[] = "File not found: $filepath";
        };
    }
    // ====== UPLOAD & INSTALL ======
    elseif(is_array($_FILES['zip'])) {
        echo "ZIP";
        $zip = $_FILES['zip'];
        if($zip['error'] == 0) {
            $filename = $zip['name'];
            $temp = $zip['tmp_name'];
            copy($temp, "$installer_folder/$filename");
            if(file_exists("$installer_folder/$filename") && file_exists("$installer_folder/unzip.php")) {
                $root = $ini_pathes['root'];
                $unzip = "unzip.php";
                // Delete any previous xable package versions
                foreach(listDir($root, "zip") as $file) {
                    if(strstr(strtolower($file), "xable")) { unlink("$root/$file"); };
                };
                // Copy package files
                copy("$installer_folder/$filename", "$root/$filename");
                copy("$installer_folder/$unzip", "$root/$unzip");
                if(file_exists("$root/$filename") && file_exists("$root/$unzip")) {
                    header("Location: $root/$unzip");
                }
                else {
                    $error[] = "Installer files copy error!";
                };
            }
            else {
                $error[] = "Installer files not found!";
            };
        }
        else {
            $error[] = "Package upload ERROR!";
        };
    }
    // ====== UPDATE CHANGELOG ======
    else if($_GET['action'] == "changelog") {
        $log_path = "doc/change.log";
        $changes = array();
        $notes = array();
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
        $new_change = array();
        $new_change[] = $_POST['time']."\n";
        foreach(split("\n", $_POST['info']) as $txt) {
            $new_change[] = "\tInfo:\t".$txt."\n";
        };
        $new_change[] = "\tFiles:\t".$_POST['files']."\n";
        $new_change[] = "\n";
        $log_content = array_merge($changes, $new_change, $notes);
        safeSave($log_path, join("", $log_content));
        //header("Location: xable_update.php");
    }
    else {
        // no action
    };


    // ======================================
    //               Pannel
    // ======================================

    $installer_name = "Xable-CMS_".date("Ymd").".zip";  // New installer archive filename
	$archive_main = "install";                          // Main folder in installer archive
	$archive_content = "$archive_main/xable";	        // Contents folder in installer archive
    $admin_folder = $_SESSION['admin_folder'];

    // ====== Excluded ======
    $exclude = array();
	// Excluded in root
    foreach(array("_bak", "install", "redirect") as $folder) { $exclude[] = "$root/$folder"; };
	// Excluded in root/admin
    foreach(array($installer_folder, "_backup", "_update", "_bak", "test") as $folder) { $exclude[] = "$root/$admin_folder/$folder"; };

    // ====== CREATE INSTALLER ======
    if($_GET['action'] == "installer") {
		// ====== Update Scripts in site folder ======
		// update sript: functions.php, xml.script
		$update_script = array("functions.js", "functions.php", "xml.php");
		$script_folder = "script";
		foreach($update_script as $file) { copy("$script_folder/$file", "$root/$script_folder/$file"); };
		// ====== Create ZIP Archive ======
		$zip_log = array();
		$zip_path = "$installer_folder/$installer_name";
		$zip_log[] = "$zip_path";
		if(file_exists($zip_path)) { unlink($zip_path); }; // Overwrite existing!
		$zip = new ZipArchive;
		if ($zip->open($zip_path, ZipArchive::CREATE)) {
			// Add installer
			foreach(listDir($installer_folder) as $file) {
                if(!is_dir("$installer_folder/$file") && path($file, "extension") != "zip") {
					$path = "$installer_folder/$file";
					$zip_path = "$archive_main/$file";
					$zip_log[] = "$path -> $zip_path";
					$zip->addFile($path, $zip_path);
				};
			};
			// Add xable content
			foreach(filesTree($root, false, $exclude) as $path) {
				if(														// IGNORE:
					!is_dir($path)  &&									// directories pathes
					substr(path($path, "basename"), 0, 1) != "." &&		// .hidden files
					path($path, "extension") != "bak" &&				// bak files
					//path($path, "extension") != "prev" &&				// prev files
					(!is_array($exclude) || count($exclude) == 0 || !in_array(path($path, "extension"), $excllude)) // non-excluded
				) {
					$relative_path = substr($path, strlen($root) + 1);
					foreach(split("/", $relative_path) as $folder) { if(substr($folder, 0, 1) == ".") { $path = false; }; }; // IGNORE files in hidden folder
					if(is_string($path)) {
						$zip_path = $archive_content."/".$relative_path; // zip path -> instal/xable/<relative path>
						$zip_log[] = "$path -> $zip_path";
						$zip->addFile($path, $zip_path);
					};
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
    $installer_packages = array();
    foreach(listDir($installer_folder, "zip") as $file) {
        $date = split("_", path($file, "filename"));
        if(count($date) > 1) {
            $installer_packages[array_pop($date)] = $file;
        };
    };
    krsort($installer_packages);
?>

<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>X.able CMS / Update</title>
        <link href='http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900|Inconsolata:400,700|Audiowide&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="style/foundation-icons.css" />
        <link rel="stylesheet" type="text/css" href="style/xable_creator.css" />
        <link rel="stylesheet" type="text/css" href="style/xable_update.css" />
        
        <script src='script/jquery-3.1.0.min.js'></script>
        <script src='script/functions.js'></script>
	</head>
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
            <nav>
                <div id="menu_bar">
                    <label class='logo'>
                        <span>&gt;&lt;</span>
                    </label>
                    <label class='title menu'>
                        <p>Update</p>
                        <ul>
                            <li>Creator</li>
                            <li>Users</li>
                            <li>Explorer</li>
							<li class='separator'><hr></li>
                            <li>Quit</li>
                        </ul>
                    </label>
                </div>
            </nav>
            
            <article id='update'>
                <h3><span class="article_icon fi-upload-cloud"></span>Update</h3>
                <p>Check for avaliable updates:</p>
                <p><a href='<?php echo $xable_website; ?>' target='_blank'>Xable Website</a></p>
                <form method='post' action='xable_update.php' enctype='multipart/form-data'>
                    <p>Upload installer package (zip):</p>
                    <section>
                        <input name='zip' type='file' accept='.zip'>
                    </section>
                    <button class='update' name='action' value='upload'>Update</button>
                </form>
            </article>
                
            <article id='create'>
                <h3><span class="article_icon fi-archive"></span>Create package</h3>
                <p>Archive content preview:</p>
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
                <button class='confirm'>Create</button>
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
                        $changes = array();
                        $notes = array();
                        foreach(file($log_path) as $txt) { $changes[] = $txt; };
                        echo "<textarea class='changes'>".join("<br>", $changes)."</textarea>\n";
                    ?>
                </div>

                <?php
                    // Installer Files
                    echo "<br><span class='flag'><hr>Installer files (click to download)<hr></span><br>\n";
                    foreach(listDir($installer_folder, ".") as $file) {
                        if(path($file, "extension") != "zip" && $file != "install.php") {
                            $size = path("$installer_folder/$file", "size");
                            echo "<span class='tag'>$installer_folder/</span><a href='$installer_folder/$file' download>$file</a>&nbsp;<span class='flag'>$size kB</span><br>\n";
                        };
                    };
                
                    // Installer Packages
                    echo "<br><span class='flag'><hr>Installer packages (click to download)<hr></span><br>\n";
                    foreach($installer_packages as $file) {
                        $size = path("$installer_folder/$file", "size");
                        echo "<span class='tag'>$installer_folder/</span><a href='$installer_folder/$file' download>$file</a>&nbsp;<span class='flag'>$size kB</span> <span class='remove' value='$file'>[x]</span><br>\n";
                    };
					
					if(is_array($zip_log)) {
						echo "<br><span class='flag'><hr>Installer log<hr></span><br>\n";
						foreach(array_keys($zip_log) as $n) {
							if($n == 0 && file_exists($zip_log[0])) {
								echo "<span class='flag'>File created:</span> ".$zip_log[0]."<br><br>\n";
							}
							else {
								echo "<span class='flag'>$n.</span> <span class='tag'>".str_replace(" -> ", "</span> <span class='flag'>-></span> ", $zip_log[$n])."<br>\n";
							};
						};
					};
                ?>
            </div>
        </aside>

        <script src='script/xable_update.js'></script>
        
	</body>
</html>

    

