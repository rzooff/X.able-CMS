<?php

    require("modules/_session-start.php");

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

?>

<!doctype html>
<html>
	<head>
        <style><?php include "style/loader.css"; ?></style>
        
		<meta charset="UTF-8">
		<title>X.able CMS / Wyszukiwanie</title>
        
		<link rel="stylesheet" type="text/css" href="style/index.css" />
		<link rel="stylesheet" type="text/css" href="style/cms.css" />
        <link rel="stylesheet" type="text/css" href="style/colors.css" />
        <link rel="stylesheet" type="text/css" href="style/show-log.css" />
		<link rel="stylesheet" type="text/css" href="style/foundation-icons.css" />
		<link href='http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900|Inconsolata:400,700|Audiowide&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
		
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
                    <h6><span class="fi-magnifying-glass"></span></h6>
                    <h3>Wyszukiwanie</h3>
                    <div class='inputs'>
                        <div class='text'>
                            <p class='label'>Wyszukiwana fraza (język: <?php echo strtoupper($lang); ?>)</p>
                            <input type='text' id='find' class='string' value='<?php echo $_GET['find']; ?>'>
                            <div class='buttons'><button class='search'>Wyszukaj</button></div>
                        </div>
                        <div class='text'>
                            <p class='label table-title'>Wyniki wyszukiwania</p>
                        </div>
						<?php

                            $find = $_GET['find'];
                            $found = array();
                            //arrayList($_SESSION['nav_pathes_list']);
                            //$_SESSION['nav_pathes_list'] = array("../pages/projects/mnisw.xml");
                            foreach($_SESSION['nav_pathes_list'] as $xml_path) {
                                //echo "> path: $xml_path<br>\n";
                                if(file_exists("$xml_path.draft")) { $xml = loadXml("$xml_path.draft"); }
                                else { $xml = loadXml($xml_path); };
                                foreach(array_keys($xml) as $article_name) {
                                    $article_group = $xml[$article_name];
                                    if(substr($article_name, 0, 1) != "_") {
                                        foreach(array_keys($article_group) as $article_num) {
                                            $article = $article_group[$article_num];
                                            //arrayList($article);
                                            foreach(array_keys($article) as $section_name) {
                                                $type = $article[$section_name][0]['type'][0];
                                                if(in_array($type, array("text", "textarea"))) {
                                                    $val = $article[$section_name][0]['text'][0][$lang][0];
                                                    $val = str_replace("[br]", " ", $val);
                                                    $val = noBBCode($val);
                                                    //echo "VAL: ".$val."<br>\n";
                                                    $test = strpos(lowercase($val), lowercase($find));
                                                    if(is_numeric($test) && $test >= 0) { $found[] = array($xml_path, $val); };
                                                }
                                                elseif($type == "string") {
                                                    $val = $article[$section_name][0]['text'][0];
                                                    $test = strpos(lowercase($val), lowercase($find));
                                                    if(is_numeric($test) && $test >= 0) { $found[] = array($xml_path, $val); };
                                                }
                                                elseif($type == "table") {
                                                    //arrayList($article[$section_name]);
                                                    $table = $article[$section_name][0]['table'][0][$lang];
                                                    foreach($table as $row) {
                                                        foreach(split(";", $row) as $cell) {
                                                            $test = strpos(lowercase($cell), lowercase($find));
                                                            if(is_numeric($test) && $test >= 0) { $found[] = array($xml_path, $cell); };
                                                        };
                                                    };
                                                };
                                            };
                                        };
                                    };
                                };
                            };
                            //arrayList($found);
                            if(count($found) > 0) {
                                echo "<table class='search'>\n";
                                echo "\t<tr><td>Dokument</td><td>Treść</td></tr>\n";
                                
                                foreach($found as $item) {
                                    $xml_path = $item[0];
                                    $val = $item[1];
                                    echo "\t<tr>\n".
                                        "\t\t<td><a href='index.php?path=$xml_path&search=".urlencode($find)."'>".path($xml_path, "dirname")."/<b>".path($xml_path, "filename")."</b>.".path($xml_path, "extension")."</a></td>\n".
                                        "\t\t<td class='found-text'>".$val."</td>\n".
                                        "\t</tr>\n";
                                };
                                echo "</table>\n";
                            }
                            else {
                                echo "<p class='justify-center'><b>Niestety nic nie znaleziono...</b></p>\n";
                            };
						?>

                    </div>
                    <div class='buttons'>
                        <button class='cancel' href='index.php?path=<?php echo $_GET['page']; ?>'>Zamknij</button>
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

