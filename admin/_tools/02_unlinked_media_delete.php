<?php
    $admin_root = "..";
    $site_root = "../..";
    $xml_pathes = [ "../..", "../../pages", "../../services" ];
    $media_pathes = [ "../../media", "../../multimedia" ];

    // ====== Load Functions Libraries ======

    require "$admin_root/script/functions.php";
    require "$admin_root/script/cms.php";
    require "$admin_root/script/xml.php";
?>

<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>X.able CMS / Unlinked Media Delete</title>
		<link href='http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900|Inconsolata:400,700|Audiowide&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" type="text/css" href="<?php echo "$admin_root/_tools/_tools.css" ?>" />
        <style></style>
    </head>
    <body>
        <button class='close' onclick="self.close()">X</button>
        
<?php
    
    function getMediaLinks($pathes, $site_root) {
    // ----------------------------------------
    // $path = <string> full directory PATH
    // ----------------------------------------
    // Delete folder with it's content
    // ----------------------------------------
        $media_links = [];
        $checked_list = [];
        foreach($pathes as $path) {
            // Get files tree list
            if($path == $site_root) {
                $files_list = listDir($path, "xml,draft,?");
            }
            else {
                $files_list = filesTree($path, "xml,draft");
            };
            // Check files links
            foreach($files_list as $file) {
                $filename = getFilename($file);
                $xml_path = path($file, "dirname")."/$filename.xml"; // to load .draft only
                if(!in_array($filename, $xml_path) && $xml = loadXml($xml_path, "draft", true)) {
                    //echo "[xml]: $file<br>";
                    //arrayList($xml);
                    foreach(array_keys($xml) as $article_name) {
                        $article_group = $xml[$article_name];
                        foreach(array_keys($article_group) as $article_num) {
                            $article = $article_group[$article_num];
                            foreach(array_keys($article) as $section_name) {
                                $section = $article[$section_name][0];
                                if(isset($section["media"]) && is_array($section["media"])) {
                                    $media = $section["media"][0];
                                    //arrayList($media);
                                    foreach($media as $item) {
                                        foreach(explode(";", $item[0]) as $file) {
                                            //echo "$file"
                                            $filepath = "$site_root/$file";
                                            if(trim($file) != "" && !is_dir($filepath) && file_exists($filepath)) {
                                                if(!in_array($filepath, $media_links)) {
                                                    $media_links[] = $filepath;
                                                }
                                            }
                                        }
                                    };
                                }
                            };
                        };
                    };
                    $checked_list[] = $xml_path;
                }
            };
        };
        return $media_links;
    };
        
    function mediaCleaner($pathes, $media_links) {
        $flag = false;
        $folders = [];
        foreach($pathes as $path) {
            foreach(filesTree($path, ".") as $file) {
                if(!in_array($file, $media_links)) {
                    $folder = path($file, "dirname");
                    if(!in_array($folder, $folders)) { $folders[] = $folder; };
                    echo "[file:] $file<br>";
                    // ====== FILE DELETE ======
                    unlink($file);
                    $flag = true;
                };
            }
        }
        if($flag) {
            foreach($folders as $folder) {
                if(!listDir($folder, "*")) {
                    echo "[folder:] $folder<br>";
                    // ====== EMPTY FOLDER DELETE ======
                    rmdir($folder);
                }
            }
        }
        else {
            echo "Nothing to delete.<br>";
        }
    }
        
    echo "<h1>Unlinked Media Delete</h1><hr>\n";
    if(isset($_POST["action"]) && $_POST["action"] == "delete") {
        $media_links = getMediaLinks($xml_pathes, $site_root);
        //echo "<h3>Linked media (not deleted)</h3>\n";
        //arrayList($media_links);
        //echo "<h3>Deleted unlinked media</h3>\n";
        mediaCleaner($media_pathes, $media_links);
    }
    else {
        echo "<form method='post'>\n";
        echo "<p>Delete all unlinked files in \"media\" folder.</p>\n";
        echo "<button name='action' value='delete' type='submit'>Submit</button>\n";
        echo "</form>\n";
    };
        

    
?>
        
    </body>
</html>