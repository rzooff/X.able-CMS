<?php

    session_start();
    $admin_root = "..";
    $site_root = "../..";
    $pathes = [ "../../pages", "../../services" ];
        
    $title_tags = [ "header title", "person name" ];

    // ====== Load Functions Libraries ======

    require "$admin_root/script/functions.php";
    require "$admin_root/script/cms.php";
    require "$admin_root/script/xml.php";

    $date_format = $_SESSION['ini_site_options']["date_format"];
?>

<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>X.able CMS / Date string to Date input</title>
		<link href='http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900|Inconsolata:400,700|Audiowide&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" type="text/css" href="<?php echo "$admin_root/_tools/_tools.css" ?>" />
        <style></style>
    </head>
    <body>
        <button class='close' onclick="self.close()">X</button>
        
<?php

    
    //echo "-> ".testDate("29.07.1977", "dd-mm-yyyy")."<hr>\n";
              
              
    function testDate($date, $format) {
        //echo "<hr>testDate($date, $format)<hr>\n";
        $year = substr($date, strrpos($format, "yyyy"), 4);
        $month = substr($date, strrpos($format, "mm"), 2);
        $day = substr($date, strrpos($format, "dd"), 2);
        if(
            strlen($date) == strlen($format) &&
            strlen($year) == 4 && is_numeric($year) &&
            strlen($month) == 2 && is_numeric($month) && intval($month) <= 12 && intval($month) >= 1 &&
            strlen($day) == 2 && is_numeric($day) && intval($day) <= 31 && intval($day) >= 1
           ) {
            /*
            $format = str_replace("yyyy", $year, $format);
            $format = str_replace("mm", $month, $format);
            $format = str_replace("dd", $day, $format);
            */
            return join("-", [ $year, $month, $day ]);
        }
        else {
            return false;
        }
    };
              
    function findStringDate($pathes, $site_root, $date_format, $options) {
    // ----------------------------------------
    // $path = <string> full directory PATH
    // ----------------------------------------
    // Delete folder with it's content
    // ----------------------------------------
        $updated_files = [];
        foreach($pathes as $path) {
            // Get files tree list
            if($path == $site_root) {
                $files_list = listDir($path, "xml,?");
            }
            else {
                $files_list = filesTree($path, "xml");
            };
            
            // Check files links
            foreach($files_list as $file) {
                $updated = false;
                if($xml = loadXml($file, "draft", true)) {
                    foreach(array_keys($xml) as $article_name) {
                        $article_group = $xml[$article_name];
                        foreach(array_keys($article_group) as $article_num) {
                            $article = $article_group[$article_num];
                            foreach(array_keys($article) as $section_name) {
                                $section = $article[$section_name][0];
                                if($section_name == "date" || $section_name == "_date" && $section["type"][0] == "string") {
                                    $string = $section["string"][0];
                                    if(!$date = testDate($string, $date_format)) {
                                        $date = "";
                                    };
                                    //echo "[xml]: $file<br>\n";
                                    //echo "[section]: $section_name<br>\n";
                                    //echo "[string]: $string -> [date]: $date<br>\n";
                                    $updated = true;
                                    unset($section["string"]);
                                    $section["type"][0] = "date";
                                    $section["date"][0] = $date;
                                    $section["options"][0] = $options;
                                    $xml[$article_name][$article_num][$section_name][0] = $section;
                                }
                            };
                        };
                    };
                }
                $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<xable>\n".arrayToXml($xml, 1)."</xable>";
                
                //echo "<hr><textarea>".$xml."</textarea>\n";
                if($updated && safeSave("$file.draft", $xml)) { $updated_files[] = $file; };
            };
        };
        return $updated_files;
    };
              
    
    echo "<h1>Date string to Date input</h1><hr>\n";
    if(isset($_POST) && is_string($_POST["date_options"])) {
        $updated_files = findStringDate($pathes, $site_root, $date_format, $_POST["date_options"]);
        if(count($updated_files) == 0) { echo "Nothing to update found.<br>\n"; }
        else {
            foreach($updated_files as $file) {
                echo "File updated: $file<br>\n";
            }
        };
    }
    elseif(isset($date_format) && is_string($date_format) && strlen($date_format) >= 8) {
        echo "<form method='post'>\n";
        echo "\t<p><label>Date format</label><input name='date_format' value='".$date_format."' disabled></p>\n";
        echo "\t<p><label>Date options</label><input name='date_options' value=''></p>\n";
        echo "\t<hr>\n";
        echo "\t<p><button class='submit'>Submit</button></p>\n";
        echo "</form>\n";
    }
    else {
        echo "Date format must be set in xable.ini!";
    }
              
    

?>
        
    </body>
</html>