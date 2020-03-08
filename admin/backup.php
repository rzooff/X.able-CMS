<?php
    //error_reporting(E_ALL);

    require("modules/_session-start.php");

    $pass_path = $ini_pathes['passwords'];
    $admin_folder = $_SESSION['admin_folder'];
    $backup_folder = $ini_pathes['backup'];

    //if(!file_exists($backup_folder)) { mkdir($backup_folder); };

    //echo "folder: $backup_folder\n";
    $installer_folder = "$root/$admin_folder/_installer";

    // ====== BACKUP NOW ======

    $site_name = $_SESSION["ini_site_options"]['site_ID'];

    $exclude = array($backup_folder, $installer_folder);
    if(is_string($_SESSION["ini_site_options"]['backup_exclude']) && $_SESSION["ini_site_options"]['backup_exclude']) {
        foreach(explode(",", $_SESSION["ini_site_options"]['backup_exclude']) as $path) {
            $exclude[] = $root."/".$path;
        };
    };

    //$site_name = readXml($settings, "page domain");
    if(is_string($site_name) && $site_name != "") {
        if($_GET['action'] == "backup") {
            $replace = array(
                "/" => "-",
                " " => "_",
                "*" => "",
                "!" => "",
                "@" => "",
                "$" => "",
                "%" => "",
            );
            foreach(array_keys($replace) as $key) { $site_name = str_replace($key, $replace[$key], $site_name); };
            $bak_name = $site_name."_".date("Ymd-Hi").".zip"; // domain.com_yyymmdd-homi.zip
            $bak_filesTree = filesTree($root, ".", $exclude);
            if(!file_exists($backup_folder)) { makeDir($backup_folder); };
            //arrayList($bak_filesTree);
            //echo "$backup_folder/$bak_name<br>\n";
            archiveFiles("$backup_folder/$bak_name", $bak_filesTree);
            addLog("backup archived", $bak_name);
        };
    }
    else {
        echo "<script> alert(".localize("settings-load-fail")."); </script>\n";
    };
?>

<!doctype html>
<html>
	<head>
        <style><?php include "style/loader.css"; ?></style>
        
		<meta charset="UTF-8">
		<title><?php echo localize("xable-backup"); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
		<link rel="stylesheet" type="text/css" href="style/index.css" />
		<link rel="stylesheet" type="text/css" href="style/cms.css" />
        <link rel="stylesheet" type="text/css" href="style/colors.css" />
        <link rel="stylesheet" type="text/css" href="style/password.css" />
        <link rel="stylesheet" type="text/css" href="style/_responsive.css" />
		<link rel="stylesheet" type="text/css" href="style/foundation-icons.css" />
		<link href='https://fonts.googleapis.com/css?family=Lato:100,300,400,700,900|Inconsolata:400,700|Audiowide&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
		
        <script src='script/jquery-3.1.0.min.js'></script>
        <script src='script/functions.js'></script>
	</head>
    
	<body>
        <div id='loader'>
            <div id="loadingProgressG">
                <div id="loadingProgressG_1" class="loadingProgressG"></div>
            </div>
        </div>
        
        <div id='backup_info'>
            <div class='popup'>
                <p><?php echo localize("backup-in-progress"); ?>...</p>
                <div class="uploader"><?php echo localize("working"); ?>...</div>
            </div>
        </div>
        
        <form id="backup" method='post' action='backup.php?action=backup'>
            <div id="page_fader"></div>
            <div id="popup_container">
                <div id="popup_box">
                    <h6><span class="fi-save"></span></h6>
                    <h3><?php echo localize("site-backup"); ?></h3>
                    <div class='inputs'>
						<p class='label'><?php echo localize("last-backup-date"); ?></p>
						<?php
							// ====== Sort backups by creation date (in filenames) ======
							$backups = array();
							$backup_files = listDir($backup_folder, "zip");
							foreach($backup_files as $file) {
								$date = array_pop(explode("_", path($file, "filename")));
								if(is_string($date)) { $backups[$date] = $file; }
							};
							ksort($backups); // sort by creation date
							// ====== DELETE BACKUP EXCEEDS NUMBER OF 10 ======
							while(count($backups) > 10) {
								unlink("$backup_folder/".array_shift($backups));
							};
							// ====== LAST BACKUP ======
							if($_GET['action'] == "backup") {
								echo "<input type='text' class='string' value='".localize("moment-label")."' disabled>\n";
							}
							else {
								$last = end($backups);
								if(!is_string($last) || $last == "") {
									echo "<input type='text' class='string' value='".localize("status-none")."' disabled>\n";
								}
								else {
									$date = array_pop(explode("_", path($last, "filename")));
									$date = substr($date, 0, 4)."-".substr($date, 4, 2)."-".substr($date, 6, 2).
										", ".substr($date, 9, 2).":".substr($date, 11, 2);
									echo "<input type='text' class='string' value='$date' disabled>\n";
								};
							};
						?>
                        
                        
                        <div class='text'>
                            <p class='label'><?php echo localize("archive-download"); ?></p>
                            <p class='description'><?php echo localize("new-on-top"); ?></p>
                            <ul>
								<?php

									// ====== ARCHIVES LIST ======
									foreach(array_reverse($backups) as $file) {
										$path = "$backup_folder/$file";
										$size = path($path, "size");
                                        $size = round( floatval($size) / 1024, 1 );
										echo "<li><a href='$path'>$file</a><span class='size'>$size Mb</span></li>";
									};
								?>
                            </ul>
							
                        </div>
                        <div class='text'>
                            <p class='description'><?php echo localize("max-archives"); ?></p>
                        </div>
                    </div>
                    <div class='buttons'>
                        <button class='confirm'><?php echo localize("backup-now"); ?></button>
                        <button class='cancel' href='index.php?path=<?php echo urlencode($_GET['page']); ?>'><?php echo localize("close-label"); ?></button>
                    </div>
                </div>
            </div>
        </form>
        
        <?php 
            if(is_string($popup)) { echo "<input id='popup' value='$popup'>\n"; };
            echo "<input type='hidden' id='saveas' value='".$_GET['page']."'>\n";
        
            foreach(array_keys($ini_enable) as $key) {
                echo "<input type='hidden' id='enable_$key' value='".$ini_enable[$key]."'>\n";
            };
        
            
        ?>
        
        <script src='script/footer.js'></script>
        <script src='script/backup.js'></script>
        
	</body>
</html>

