<?php

    require("modules/_session-start.php");

?>

<!doctype html>
<html>
	<head>
        <style><?php include "style/loader.css"; ?></style>
        
		<meta charset="UTF-8">
		<title><?php echo localize("xable-log"); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
		<link rel="stylesheet" type="text/css" href="style/index.css" />
		<link rel="stylesheet" type="text/css" href="style/cms.css" />
        <link rel="stylesheet" type="text/css" href="style/colors.css" />
        <link rel="stylesheet" type="text/css" href="style/show-log.css" />
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
        
        <form id="backup" method='post' action='backup.php?action=backup'>
            <div id="page_fader"></div>
            <div id="popup_container">
                <div id="popup_box">
                    <h6><span class="fi-calendar"></span></h6>
                    <h3><?php echo localize("events-log"); ?></h3>
                    <div class='inputs'>

						<?php
							$log_file = $ini_pathes['log'];
                            if(file_exists($log_file)) {
                                
                                echo "<table class='log'>\n";
                                $log_content = array_map("trim", file($log_file));
                                
                                array_shift($log_content); // Cut header titles
                                $titles = localize("log-header");
                                //$log_content[] = $titles;
                                echo "\t\t<td>".join("</td>\n\t\t<td>", explode(";", $titles))."</td>\n";
                                
                                foreach(array_reverse($log_content) as $log) {
                                    
                                    echo "\t<tr>\n";
                                    //echo "\t\t<td>".join("</td>\n\t\t<td>", explode(";", $log))."</td>\n";
                                    $log = explode(";", $log);
                                    echo "\t\t<td class='time'><span class='date'>".str_replace(" ", "</span>, ", $log[0])."</td>\n";
                                    
                                    if(file_exists($log[1]) || file_exists($log[1].".draft")) {
                                        echo "\t\t<td class='document'>".
                                            "<a href='index.php?path=".$log[1]."'>".
                                            path($log[1], "dirname")."/".
                                            "<span class='filename'>".path($log[1], "filename")."</span>.".
                                            path($log[1], "extension").
                                            "</a>\n";
                                            "</td>\n";
                                    }
                                    else {
                                        echo "\t\t<td class='document non-path'>".$log[1]."</td>\n";
                                    };
                                    
                                    echo "\t\t<td class='action'>".$log[2]."</td>\n";
                                    
                                    
                                    echo "\t\t<td class='group-user'><span class='group'>".str_replace(".", ".</span><span class='user'>", $log[3])."</span></td>\n";
                                    
                                    echo "\t</tr>\n";
                                };
                                
                                echo "</table>\n";
                            }
                            else {
                                echo "<p class='description justify-center'>".localize("empty-log")."</p>";
                            };
	
						?>
                        
                        

                    </div>
                    <div class='buttons'>
                        <button class='cancel' href='index.php?path=<?php echo $_GET['page']; ?>'><?php echo localize("close-label"); ?></button>
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

