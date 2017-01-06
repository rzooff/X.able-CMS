<?php
    // ======================================
    //              ><.able CMS
    //        (C)2016 maciejnowak.com
    // ======================================
	// compatibile: php5.4+ or higher

    $xable_version = "3.0;20170105";
    require("modules/_session-start.php");

    $manual_folder = "doc";
    $ini_libraries = loadIni($ini_file, "libraries");
    $draft_ext = "draft"; // draft file extension
    $previous_ext = "prev"; // pervious file extension

?>

<!doctype html>
<html>
	<head>
        <?php
            echo "\n".
                "\t\t<!-- ======================================\n".
                "\t\t               ><.able CMS"."\n".
                "\t\t      (c)2017 maciej@maciejnowak.com"."\n".
                "\t\t         v.".array_shift(split(";", $xable_version)).", "." build.".array_pop(split(";", $xable_version))."\n".
                "\t\t====================================== -->\n";
        ?>
        
        <style><?php include "style/loader.css"; ?></style>
        
		<meta charset="UTF-8">
		<title>X.able CMS / Editor</title>
		
		<link rel="stylesheet" type="text/css" href="style/index.css" />
		<link rel="stylesheet" type="text/css" href="style/nav.css" />
		<link rel="stylesheet" type="text/css" href="style/cms.css" />
        <!-- <link rel="stylesheet" type="text/css" href="style/csv.css" /> -->
        <link rel="stylesheet" type="text/css" href="style/colors.css" />
		<link rel="stylesheet" type="text/css" href="style/foundation-icons.css" />
		<link href='http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900|Inconsolata:400,700|Audiowide&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
		
        <script src='script/jquery-3.1.0.min.js'></script>
        <script src='script/functions.js'></script>
        <script src='script/footer.js'></script>

	</head>
	<body>
        
        <div id='loader'>
            <div id="loadingProgressG">
                <div id="loadingProgressG_1" class="loadingProgressG"></div>
            </div>
        </div>
        
        <?php
        
            // ====== LANGUAGE ======
            $languages = array();
            foreach($settings['multi_language'] as $lang_data) {
                //if(readXml($lang_data, "active") != "") {
                    $id = readXml($lang_data, "id");
                    $languages[] = $id;
                //};
            };
            $admin_lang = "pl";
            $lang = $_GET['lang'];
            if(!is_string($admin_lang)) {
                $languages = array( "pl" );
                $admin_lang = "pl";
            };
            if(!is_string($lang) || $lang == "") { $lang = $admin_lang; };
            // Read auto translate dictionary
            $translate_dictionary = file_get_contents("dictionary.csv");
            $translate_dictionary = str_replace("\n", "|", $translate_dictionary);

            // ====== EDIT PATH ======
            $path = $_GET['path'];
            if(path($path, "extension") == $draft_ext) { $path = substr($path, 0, strlen($path) - strlen($draft_ext) - 1); };
            if(!is_string($path) || $path == "" || (!file_exists($path) && !file_exists("$path.$draft_ext"))) {
                $path = getFirstPath($nav_documents, $root);
            };  

            // ====== SAVE PATH ======
            if(is_string($_GET['saveas']) && $_GET['saveas'] != "") {
                $saveas = path($path, "dirname")."/".$_GET['saveas'];
            }
            else {
                $saveas = $path;
            };
        
            // ====== POPUP ======
            if(is_string($_GET['popup']) && $_GET['popup'] != "") {
                echo "\t\t<input type='hidden' id='popup' value='".$_GET['popup']."'>\n";
            };
        
            // ====== SEARCH ======
            if(is_string($_GET['search']) && $_GET['search'] != "") {
                echo "\t\t<input type='hidden' id='search-scroll' value='".$_GET['search']."'>\n";
            };
        
            // ====== INSTALL / UPDATE COMPLETE ======
            if(is_string($_GET['install']) && $_GET['install'] == "completed") {
                // Show popup
                echo "\t\t<input type='hidden' id='popup' value='Instalacja zakończona!|done'>\n";
                // Delete install files
                if(!file_exists("install")) {  removeDir("$root/install"); };
            };
        
            echo "\n";
        
            // ======================================
            //            Send variables
            // ======================================
            
            echo "\t\t<input type='hidden' id='xable_version' value='".$xable_version."'>\n";
            echo "\t\t<input type='hidden' id='root' value='".$root."'>\n";
            echo "\t\t<input type='hidden' id='path' value='".$path."'>\n";
            echo "\t\t<input type='hidden' id='saveas' value='".$saveas."'>\n";
            echo "\t\t<input type='hidden' id='languages' value='".join(",", $languages)."'>\n";
            echo "\t\t<input type='hidden' id='lang' value='".$lang."'>\n";
            echo "\t\t<input type='hidden' id='admin_lang' value='".$admin_lang."'>\n";
            echo "\t\t<input type='hidden' id='translate_dictionary' value='".str_replace("\n", "\|", $translate_dictionary)."'>\n";
        
            foreach(array_keys($ini_enable) as $key) {
                echo "\t\t<input type='hidden' id='enable_$key' value='".$ini_enable[$key]."'>\n";
            };
            //arrayList($site_options);
            foreach(array_keys($site_options) as $key) {
                echo "\t\t<input type='hidden' id='site_$key' value='".$site_options[$key]."'>\n";
            };

            include("modules/nav.php");
            
            if(path($path, "extension") == "xml" || path($path, "extension") == "template") { include("modules/xml.php"); }
            elseif(path($path, "extension") == "order") { include("modules/order.php"); }
            elseif(path($path, "extension") == "csv") { include("modules/csv.php"); }
            else { include("modules/text.php"); };
        
            // ======================================
            //            Template script
            // ====================================== 
            if(path($path, "extension") == "template") {
                echo "\t\t<script src='script/template.js'></script>\n";
            };

        ?>
        
		<script src='script/cms.js'></script>
        <div id='libraries_data'>
<?php

    // ===============================
    //           Libraries
    // ===============================
	
    function sendLibrary($lib, $lib_path, $root) {
        echo "\t\t\t<div class='$lib' path='$path'>\n";
		// ====== xml data based content ======
		if(path($lib_path, "extension") == "xml") {
			$xml = loadXml($lib_path);
			foreach($xml['multi_folder'] as $folder) {
				$name = readXml($folder, "name");
				$files = readXml($folder, "files");
				if($name != "" && $files != "" && !is_dir("$root/$files")) {
					echo "\t\t\t\t<div class='folder'>\n";
					echo "\t\t\t\t\t<p class='name help' help='Pokaż / ukryj zawartość folderu'><span class='fi-folder'></span>$name</p>\n";
					echo "\t\t\t\t\t<ul>\n";
					foreach(split(";", $files) as $file) {
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
			echo "\t\t\t\t\t<p class='name help' help='Pokaż / ukryj zawartość folderu'><span class='fi-folder'></span>$name</p>\n";
			echo "\t\t\t\t\t<ul>\n";
			foreach(listDir($lib_path, "xml") as $file) {
				$xml = loadXml("$lib_path/$file");
				$title = readXml($xml, "header title");
				if($title == "") { $title = path("$lib_path/$file", "filename"); };
				$mod_time = path("$lib_path/$file", "modified");
				$relative = split("/", $lib_path);
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
        
	</body>
</html>

