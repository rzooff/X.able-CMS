<?php
    $admin_root = "..";
    $site_root = "../..";
    $pathes = [ "../../pages", "../../services" ];
        
    $title_tags = [ "header title", "person name" ];

    // ====== Load Functions Libraries ======

    require "$admin_root/script/functions.php";
    require "$admin_root/script/cms.php";
    require "$admin_root/script/xml.php";
?>

<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>X.able CMS / Order Generate</title>
		<link href='http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900|Inconsolata:400,700|Audiowide&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" type="text/css" href="<?php echo "$admin_root/_tools/_tools.css" ?>" />
        <style></style>
    </head>
    <body>
        <button class='close' onclick="self.close()">X</button>

        
<?php   
    // ====== Load Languages ======

    $settings = loadXml("$site_root/settings.xml");
    $languages = [];
    foreach($settings["multi_language"] as $language) {
        if(readXml($language, "active") != "") {
            $languages[] = readXml($language, "id");
        };
    };
        
    // ====== remove Directory / begin ======
    function addOrder($pathes, $title_tags, $languages) {
    // ----------------------------------------
    // $path = <string> full directory PATH
    // ----------------------------------------
    // Delete folder with it's content
    // ----------------------------------------
        foreach($pathes as $path) {
            foreach(dirTree($path) as $folder_path) {
                
                $folder_name = path($folder_path, "filename");
                
                if(substr($folder_name, 0, 1) != "_") {
                    if(!file_exists($folder_path."/.order")) {
                        echo "Folder: $folder_path<br>\n";
                        
                        $updated_order = [];
                        $updated_order["_order"][0]["title"][0] = "header title"; // default
                        $updated_order["multi_item"] = [];
                        
                        foreach(listDir($folder_path, "?,xml") as $file_path) {
                            
                            $xml = loadXml($file_path, "draft");
                            $title = [];
                            $item_xml = [];
                            
                            // Title by document title
                            foreach($title_tags as $tags) {
                                $text = readXml($xml, $tags);
                                if($text && is_string($text) && strlen($text) > 1) {
                                    $updated_order["_order"][0]["title"][0] = $tags;
                                    foreach($languages as $language) {
                                        $title[$language][0] = readXml($xml, $tags, $language);
                                    };
                                };
                            };
                            
                            // Title by filename
                            if(count($title) == 0) {
                                $text = capitalize(str_replace("_", " ", path($file_path, "filename")));
                                foreach($languages as $language) {
                                    $title[$language][0] = $text;
                                }
                            };

                            $item_xml["path"][0]["type"][0] = "string";
                            $item_xml["path"][0]["string"][0] = path($file_path, "filename").".xml";
                            
                            $item_xml["title"][0]["type"][0] = "text";
                            $item_xml["title"][0]["text"][0] = $title;
                            
                            $updated_order["multi_item"][] = $item_xml;
                            
                            //echo "> $file_path | ".$title["pl"]."<br>\n";
                            
                        }; // files loop
                        
                        $order_file = $folder_path."/.order";
                        
                        $order_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<xable>\n".arrayToXml($updated_order, 1)."</xable>";
                        //arrayList($updated_order);
                        //echo "<textarea>".$order_xml."</textarea><br>\n";
                        if(safeSave($order_file, $order_xml)) {
                            echo "Order file created: $order_file<br>\n";
                        };
                    }
                    else {
                        echo "Folder: $folder_path - Order file exists.<br>\n";
                    }
                };
            }; // folders loop
        };
    };
        
    echo "<h1>Order generate</h1><hr>\n";
    if(isset($_POST["action"]) && $_POST["action"] == "generate") {
        addOrder($pathes, $title_tags, $languages);
    }
    else {
        echo "<form method='post'>\n";
        echo "<p>Generate xml .order files to all pages folders with Title names.</p>\n";
        echo "<button name='action' value='generate' type='submit'>Submit</button>\n";
        echo "</form>\n";
    };

?>
        
    </body>
</html>