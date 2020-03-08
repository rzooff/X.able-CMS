<?php

    // ======================================
    //              ><.able CMS
    //      (C)2015-2019 maciejnowak.com
    // ======================================
	// compatibile: php5.5+ or higher

    $xable_version = trim(array_shift(file("doc/version.txt")));
    $last_update = array_pop(explode(";", $xable_version));
    //$last_update = uniqid(); // instant update (dev/bugfix)

    require("modules/_session-start.php");

    $manual_folder = "doc";
    $plugins_folder = "_plugins";
    $ini_libraries = loadIni($ini_file, "libraries");
    $draft_ext = "draft"; // draft file extension
    $previous_ext = "prev"; // pervious file extension

    //$supported_extensions = array("xml", "draft", "csv", "txt", "order", "template", "prev");
    $supported_extensions = array("xml", "draft", "order", "template", "prev");

    // Get all languages
    $languages = array();
    foreach($settings['multi_language'] as $lang_data) { $languages[] = readXml($lang_data, "id"); };
    if(count($languages) == 0) { $languages = array( $_SESSION['admin_lang'] ); };
    // Change edit language by GET
    if(is_string($_GET['lang']) && $_GET['lang'] != "" && in_array($_GET['lang'], $languages)) {
        $_SESSION['edit_lang'] = $_GET['lang'];
    }
    // Set default edit language -> first
    elseif(!is_string($_SESSION['edit_lang']) || $_SESSION['edit_lang'] == "" || !in_array($_SESSION['edit_lang'], $languages)) {
        $_SESSION['edit_lang'] = $languages[0];
    };

?>

<!doctype html>
<html>
	<head>
        <?php
            $xable_date = array_pop(explode(";", $xable_version));
            echo "\n".
                "\t\t<!-- ======================================\n".
                "\t\t               ><.able CMS"."\n".
                "\t\t      (c)".substr($xable_date, 0, 4)." maciej@maciejnowak.com"."\n".
                "\t\t         v.".str_replace(";", ", build.", $xable_version)."\n".
                "\t\t====================================== -->\n";
        ?>
        
        <!-- X.able website: http://xable.maciejnowak.com -->
        <!-- Icons: http://zurb.com/playground/foundation-icon-fonts-3 -->
        
        <style><?php include "style/loader.css"; ?></style>
        
		<meta charset="UTF-8">
		<title><?php echo localize("xable-editor"); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
            <!-- ====== Favicon ====== -->
        <link rel="apple-touch-icon" sizes="180x180" href="_favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="_favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="_favicon/favicon-16x16.png">
        <link rel="manifest" href="_favicon/manifest.json">
        <meta name="theme-color" content="#ffffff">
		
		<link rel="stylesheet" type="text/css" href="style/index.css<?php echo "?v=".$last_update; ?>" />
		<link rel="stylesheet" type="text/css" href="style/nav.css<?php echo "?v=".$last_update; ?>" />
		<link rel="stylesheet" type="text/css" href="style/cms.css<?php echo "?v=".$last_update; ?>" />
        <!-- <link rel="stylesheet" type="text/css" href="style/csv.css<?php echo "?v=".$last_update; ?>" /> -->
        <link rel="stylesheet" type="text/css" href="style/colors.css<?php echo "?v=".$last_update; ?>" />
        <link rel="stylesheet" type="text/css" href="style/calendar.css<?php echo "?v=".$last_update; ?>" />
        
        <link rel="stylesheet" type="text/css" href="style/_responsive.css<?php echo "?v=".$last_update; ?>" />
        
		<link rel="stylesheet" type="text/css" href="style/foundation-icons.css" />
		<link href='https://fonts.googleapis.com/css?family=Lato:100,300,400,700,900|Inconsolata:400,700|Audiowide&amp;subset=latin,latin-ext' rel='stylesheet' type='text/css'>
        
        <!-- ====== LOCALIZATION ====== -->
        <?php exportLocalization() ?>
		
        <script src="script/jquery-3.1.0.min.js"></script>
        <script src="script/functions.js"></script>
        <script src="script/footer.js<?php echo "?v=".$last_update; ?>"></script>

	</head>
	<body>
        
        <!-- =======================================
                      External plugins
        ======================================== -->
        
        <?php
            if(isset($_SESSION["xable-publish_plugin"])) {
                $plugin_path = $_SESSION["xable-publish_plugin"];
                if(is_string($plugin_path) && $plugin_path != "" && is_file($plugin_path)) {
                    echo "<iframe id='publish_plugin' src='$plugin_path'></iframe>\n";
                };
                unset($_SESSION["xable-publish_plugin"]);
            };
        ?>
        
        <div id='loader'>
            <div id="loadingProgressG">
                <div id="loadingProgressG_1" class="loadingProgressG"></div>
            </div>
        </div>
        
        <div id='upload_info'>
            <div class='popup'>
                <p><?php echo localize("upload-in-progress"); ?>...</p>
                <div class="uploader"><?php echo localize("loading"); ?>...</div>
            </div>
        </div>
        
        <?php

            // ====== EDIT PATH ======
            $path = $_GET['path'];
            if(!in_array(strtolower(path($path, "extension")), $supported_extensions)) {
                $path = "$root/settings.xml";
            }
            if(!file_exists($path) && !file_exists("$path.draft")) {
                $path = "$root/settings.xml";
            };

            $_SESSION["edit_path"] = $path;

            // ====== SAVE PATH ======
            if(is_string($_GET['saveas']) && $_GET['saveas'] != "") {
                $saveas = $_GET['saveas'];
            }
            else {
                $saveas = $path;
            };
        
            // ====== POPUP ======
            if(is_string($_GET['popup']) && $_GET['popup'] != "") {
                echo "\t\t<input type='hidden' id='popup' value='".$_GET['popup']."'>\n";
            }
            else if(is_string($_SESSION['popup']) && $_SESSION['popup'] != "") {
                echo "\t\t<input type='hidden' id='popup' value='".$_SESSION['popup']."'>\n";
                unset($_SESSION['popup']);
            }
        
        
            // ====== SEARCH ======
            if(is_string($_GET['found']) && $_GET['found'] != "") {
                echo "\t\t<input type='hidden' id='search-found' value='".$_GET['found']."'>\n";
            };
        
            echo "\n";
        
            // ======================================
            //            Send variables
            // ======================================
            echo "\t\t<!-- SEND VARIABLES TO JS -->\n";
            echo "\t\t<input type='hidden' id='xable_version' value='".$xable_version."'>\n";
            echo "\t\t<input type='hidden' id='root' value='".$root."'>\n";
            echo "\t\t<input type='hidden' id='path' value='".$path."'>\n";
            echo "\t\t<input type='hidden' id='saveas' value='".$saveas."'>\n";
            echo "\t\t<input type='hidden' id='languages' value='".join(",", $languages)."'>\n";
            echo "\t\t<input type='hidden' id='edit_lang' value='".$_SESSION['edit_lang']."'>\n";
            echo "\t\t<input type='hidden' id='admin_lang' value='".$_SESSION['admin_lang']."'>\n";
            echo "\t\t<input type='hidden' id='translate_dictionary' value='".join("|", $_SESSION['dictionary'])."'>\n";
        
            foreach(array_keys($ini_enable) as $key) {
                echo "\t\t<input type='hidden' id='enable_$key' value='".$ini_enable[$key]."'>\n";
            };
            //arrayList($_SESSION['ini_site_options']);
            foreach(array_keys($_SESSION['ini_site_options']) as $key) {
                echo "\t\t<input type='hidden' id='site_$key' value='".$_SESSION['ini_site_options'][$key]."'>\n";
            };
        
            // ====== Pages & Media Tree ======
        
            echo "\t\t<!-- Pages & Media Tree -->\n";
        
            $media_path = "$root/media";
            $media_folders = [];
            foreach(filesTree($media_path, "/") as $folder) {
                if(substr(path($folder, "filename"), 0, 1) != "_") {
                    $folder = substr($folder, strlen($root) + 1);
                    $media_folders[] = $folder;
                };
            }
            sort($media_folders);
            //arrayList($media_folders);
            echo "\t\t<input type='hidden' id='media_folders' value='".join(";", $media_folders)."'>\n";
        
            $pages_path = "$root/pages";
            $pages_files = [];
            foreach(filesTree($pages_path, "xml,draft,?") as $file) {
                if(substr(path(substr($file, strlen($pages_path) + 1), "dirname"), 0, 1) != "_") {
                    $file = substr($file, strlen($root) + 1);
                    $filename = path($file, "filename");
                    if(path($filename, "extension") != "xml") { $filename = path($filename, "filename"); };
                    $file = path($file, "dirname")."/$filename.xml";
                    if(!in_array($file, $pages_files)) { $pages_files[] = $file; };
                };
            };
            sort($pages_files);
            //arrayList($pages_files);
            echo "\t\t<input type='hidden' id='pages_files' value='".join(";", $pages_files)."'>\n";
        
        
            // ======================================
            //               Navigation
            // ======================================

            include("modules/nav.php");
        
            // ======================================
            //                Editor
            // ======================================
            
            if(path($path, "extension") == "xml" || path($path, "extension") == "template") { include("modules/form_xml.php"); }
            elseif(path($path, "extension") == "order") { include("modules/form_order.php"); }
            elseif(path($path, "extension") == "csv") { include("modules/form_csv.php"); }
            else { include("modules/form_text.php"); };

            // ======================================
            //            Template script
            // ====================================== 
            if(path($path, "extension") == "template") {
                echo "\t\t<script src='script/template.js?v=".$last_update."'></script>\n";
            };

        ?>

		<script src="script/cms.js<?php echo "?v=".$last_update; ?>"></script>
		<script src="script/nav.js<?php echo "?v=".$last_update; ?>"></script>
        <div id='libraries_data'>
<?php

    // ===============================
    //           Libraries
    // ===============================
	
    function sendLibrary($lib, $lib_path, $root) {
        
        echo "\t\t\t<div class='$lib' path='$path'>\n";
		// ====== xml data based content ======
		if(path($lib_path, "extension") == "xml") {
            //echo "LIB> $lib, $lib_path, $root\n";
			$xml = loadXml($lib_path);
			foreach($xml['multi_folder'] as $folder) {
                //arrayList($folder);
				$name = readXml($folder, "name", $_SESSION["admin_lang"]);
				$files = readXml($folder, "files", $_SESSION["admin_lang"]);
                
                //echo "FOLDER: $name, $files\n";
                
				if($files != "" && !is_dir("$root/$files")) {
					echo "\t\t\t\t<div class='folder'>\n";
					echo "\t\t\t\t\t<p class='name help' help='".localize("show-hide-folder-content")."'><span class='fi-folder'></span>$name</p>\n";
					echo "\t\t\t\t\t<ul>\n";
					foreach(explode(";", $files) as $file) {
						if($file != "" && file_exists("$root/$file") && !is_dir("$root/$file")) {
							if(strstr(strtolower($lib), "images")) {
								echo "\t\t\t\t\t\t<li class='image' value='$file'><figure class='' style='background-image:url(\"$root/$file\")'></figure><p clas='filename'>".path($file, "basename")."</p><p class='details'>".path("$root/$file", "size")." kB</p></li>\n";
							}
							else {
								echo "\t\t\t\t\t\t<li class='file' value='$file'><p class=''>".path($file, "basename")." <span class='details'>(".path("$root/$file", "size")." kB)</span></p></li>\n";
							};
						};
					};
					echo "\t\t\t\t\t</ul>\n";
					echo "\t\t\t\t</div>\n";
				};
			};
		}
		// ====== folder files based content ======
		else {
			if($name == "") { $name = "Folder"; };
			if($lib == "temp_lib") { $name = "Strony tymczasowe"; };
			echo "\t\t\t\t<div class='folder'>\n";
			echo "\t\t\t\t\t<p class='name help' help='".localize("show-hide-folder-content")."'><span class='fi-folder'></span>$name</p>\n";
			echo "\t\t\t\t\t<ul>\n";
			foreach(listDir($lib_path, "xml") as $file) {
				$xml = loadXml("$lib_path/$file");
				$title = readXml($xml, "header title", $_SESSION["admin_lang"]);
				if($title == "") { $title = path("$lib_path/$file", "filename"); };
				$mod_time = path("$lib_path/$file", "modified");
				$relative = explode("/", $lib_path);
				array_shift($relative); // cut off "<root>/" from url
				array_shift($relative); // cut off "pages/" from url
				$link = "index.php?page=".join("/", $relative)."/".path($file, "filename");
				echo "\t\t\t\t\t\t<li class='file' value='$link'><p class=''>$title <span class='details'>(mod: $mod_time)</span></p></li>\n";
			};
			echo "\t\t\t\t\t</ul>\n";
			echo "\t\t\t\t<div>\n";
		};
		echo "\t\t\t</div>\n";
    };
	
    foreach(array_keys($ini_libraries) as $lib) {
        $lib_path = $ini_libraries[$lib];
        if(file_exists($lib_path)) {
            sendLibrary($lib, $lib_path, $root);
        };
    };
	
?>
        
        </div>
        
        <textarea id='detectChanges' style='display:none'>-</textarea>
        
        <!-- =======================================
                        Xable Plugins
        ======================================== -->
        
        <div id='plugins_box'>
            <?php
                if(file_exists($plugins_folder) && is_dir($plugins_folder)) {
                    echo "\n";
                    // ====== Plugin Folders ======
                    foreach(listDir($plugins_folder, "/,?") as $folder) {
                        // Filetypes order
                        $plugin_files = array(
                            "htm" => [],
                            "html" => [],
                            "php" => [],
                            "js" => [],
                            "css" => []
                        );
                        // ====== Files list ======
                        foreach(listDir($folder, ".,?") as $file) {
                            $ext = path($file, "extension");
                            if(is_array($plugin_files[$ext])) {
                                $files = $plugin_files[$ext];
                                $files[] = $file;
                                $plugin_files[$ext] = $files;
                            }
                        }
                        // ====== Include plugin components ======
                        echo "\n<!-- ====== [plugin] $folder ====== -->\n";
                        foreach(array_keys($plugin_files) as $ext) {
                            $files = $plugin_files[$ext];
                            foreach($files as $file) {
                                if($ext == "js") {
                                    echo "<script type=\"text/javascript\" src=\"".$file."\"></script>\n";
                                }
                                elseif($ext == "css") {
                                    echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$file."\" />\n";
                                }
                                else {
                                    include $file;
                                };
                            };
                        };
                    };
                };
            ?>
        </div>
        
        <!-- <p id='tester'>?</p> -->
        
	</body>
</html>

