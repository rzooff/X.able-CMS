<?php

    require("modules/_session-start.php");

    $admin_lang = $_SESSION["admin_lang"];
    $edit_lang = $_POST["language"];
    $find = lowercase($_POST["find"]);
    $documents = array_map("trim", explode("\n", $_POST["documents"]));
?>

<!doctype html>
<html>
	<head>
        <style><?php include "style/loader.css"; ?></style>
        
		<meta charset="UTF-8">
		<title><?php echo localize("xable-search"); ?></title>
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
        
        <form id="backup" action='search.php' method='post'>
            <div id="page_fader"></div>
            <div id="popup_container">
                <div id="popup_box">
                    <h6><span class="fi-magnifying-glass"></span></h6>
                    <h3><?php echo localize("search-label"); ?></h3>
                    <div class='inputs'>
                        <div class='text search_box'>
                            <p class='label'><?php echo localize("search-phrase"); ?> (<?php echo localize("language-label"); ?>: <?php echo strtoupper($edit_lang); ?>)</p>
                            
                            <?php
                            
                                echo "<input type='text' id='find' class='string' name='find' value='".$_POST['find']."'>\n";
                            
                                echo "<input class='hidden' name='language' value='".$_POST['language']."'>\n";
                                echo "<input class='hidden' name='back_url' value='".$_POST['back_url']."'>\n";
                                echo "<textarea class='hidden' name='documents'>".$_POST["documents"]."</textarea>\n";
                            
                                echo "<div class='buttons'><button class='search'>".localize("search-label")."</button></div>\n";

                            ?>
                            
                        </div>
						<?php
                        
                            //arrayList($_POST);


                        
                            $found = [];

                            foreach($documents as $xml_path) {

                                $draft_path = $xml_path.".draft";
                                if(file_exists($draft_path)) { $xml = loadXml($draft_path); }
                                else { $xml = loadXml($xml_path); }
                                
                                foreach(array_keys($xml) as $article_name) {

                                    $article_group = $xml[$article_name];
                                    if(substr($article_name, 0, 1) != "_") {
                                        
                                        foreach(array_keys($article_group) as $article_num) {
                                            $article = $article_group[$article_num];
                                            //arrayList($article);
                                            foreach(array_keys($article) as $section_name) {
                                                
                                                
                                                $type = $article[$section_name][0]["type"][0];
                                                $val = readXml($article, $section_name, $edit_lang);
                                                //echo $type."<br>\n";
                                                
                                                if(!is_string($val) || !is_string($type) || $val == "" || $type == "") {
                                                    // ignore empty data   
                                                }
                                                // ====== Text ======
                                                if(in_array($type, [ "code", "string", "text", "textarea" ])) {
                                                    if(strstr(lowercase($val), $find)) {
                                                        $text = str_replace("[br]", "<br>", $val);
                                                        $text = preg_replace("/(".$find.")/i", "<span class='search-found'>$1</span>", $text);
                                                        $found[] = [ $xml_path, $text, "$article_name $article_num $section_name" ];
                                                    };
                                                }
                                                // ====== Media ======
                                                elseif($type == "media") {
                                                    $set = $article[$section_name][0]["set"][0];
                                                    // Video link
                                                    if($set == "video") {
                                                        if(strstr(lowercase($val), $find)) {
                                                            $text = preg_replace("/(".$find.")/i", "<span class='search-found'>$1</span>", $val);
                                                            $found[] = [ $xml_path, $val, "$article_name $article_num $section_name" ];
                                                        }
                                                    }
                                                    // File / files
                                                    elseif($set != "none") {
                                                        $files = explode(";", $val);
                                                        foreach(array_keys($files) as $file_num) {
                                                            $file = $files[$file_num];
                                                            $path = $_SESSION["admin_root"]."/".$file;
                                                            
                                                            if(!is_dir($path) && file_exists($path) && strstr(lowercase(path($file, "filename")), $find)) {
                                                                $text = preg_replace("/(".$find.")/i", "<span class='search-found'>$1</span>", $file);
                                                                if(in_array(lowercase(path($path, "extension")), [ "bmp", "gif", "jpg", "jpeg", "png", "svg", "tiff" ])) {
                                                                    $link = "<a class='image' href='$path' target='_blank'><img src='$path'><br>$text</a>";
                                                                }
                                                                else {
                                                                    $link = "<a href='$path' target='_blank'><i class='icon fi-page'></i>$text</a>";
                                                                };

                                                                $found[] = [ $xml_path, $link, "$article_name $article_num $section_name $file_num" ];
                                                            }
                                                        }
                                                    }
                                                }
                                                // ====== Table ======
                                                elseif($type == "table") {
                                                    foreach(array_keys($val) as $row_num) {
                                                        $row = explode(";", $val[$row_num]);
                                                        foreach(array_keys($row) as $cell_num) {
                                                            $cell = $row[$cell_num];
                                                            $text = str_replace("[br]", "<br>", $cell);
                                                            if(strstr(lowercase($text), $find)) {
                                                                $text = preg_replace("/(".$find.")/i", "<span class='search-found'>$1</span>", $text);
                                                                $found[] = [ $xml_path, $text, "$article_name $article_num $section_name $row_num $cell_num" ];
                                                            }
                                                        }
                                                    }
                                                };
                                                
                                            };
                                        };
                                    };
                                };
                            };
                        
                        
                            if(count($found) > 0) {
                                echo "<div class='found_box'>\n";
                                //echo "<p class='label table-title'>".localize("search-results")."</p>\n";
                                
                                foreach($found as $item) {
                                    echo "\t<div class='item_box'>\n";
                                    
                                    list($xml_path, $text, $key_string) = $item;
                                    $link = "index.php?path=".$xml_path."&lang=".$edit_lang."&found=$key_string";
                                    echo "\t\t<p class='link'><a href='$link'>$xml_path</a></p>\n";
                                    echo "\t\t<p class='key'>$key_strin</p>\n";
                                    echo "\t\t<p class='article'>$text</p>\n";
                                    
                                    echo "\t</div>\n";
                                }
                                
                                
                                echo "</div>\n";
                            }
                            else {
                                echo "<p class='justify-center'><b>".localize("nothing-found")."</b></p>\n";
                            }
                        

						?>

                    </div>
                    <div class='buttons'>
                        <button class='cancel' href='index.php?path=<?php echo $_POST['back_url']."&lang=".$_POST["language"]; ?>'><?php echo localize("close-label"); ?></button>
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
        <script src='script/search.js'></script>
        
	</body>
</html>
