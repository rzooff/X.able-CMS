<?php
    $admin_root = "..";
    $site_root = "../..";
    $pages = "../../pages";
        
    $title_tag = "header title";

    // ====== Load Functions Libraries ======

    require "$admin_root/script/functions.php";
    require "$admin_root/script/cms.php";
    require "$admin_root/script/xml.php";
?>

<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>X.able CMS / Order Update (txt -> xml)</title>
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
    function updateOrder($path, $title_tag, $languages) {
    // ----------------------------------------
    // $path = <string> full directory PATH
    // ----------------------------------------
    // Delete folder with it's content
    // ----------------------------------------
        foreach(filesTree($path) as $file) {
            if(is_file($file) && path($file, "extension") == "order") {
                
                if(!loadXml($file)) {
                    $folder = path($file, "dirname");
                    
                    echo "<hr><b>".$folder."</b><br>\n";
                    
                    $order = array_map("trim", file($file));
                    $updated_order = [];
                    $updated_order["_order"][0]["title"][0] = $title_tag;
                    
                    $updated_order["multi_item"] = [];
                    
                    foreach($order as $item) {
                        
                        $item_xml = [];
                        
                        $item = path(array_shift(explode("|", $item)), "filename").".xml";
                        $item_path = $folder."/".$item;
                        
                        if($xml = loadXml($item_path, "draft")) {
                            
                            $item_xml["path"][0]["type"][0] = "string";
                            $item_xml["path"][0]["string"][0] = $item;
                            
                            $item_xml["title"][0]["type"][0] = "text";

                            foreach($languages as $language) {
                                $title = readXml($xml, $title_tag, $language);
                                if(!is_string($title) || $title == "") { $title = path($item, "filename"); };
                                
                                
                                $item_xml["title"][0]["text"][0][$language][] = $title;
                            };
                            
                            $updated_order["multi_item"][] = $item_xml;
                        };
                        
                        
                    };
                    $order_file = $folder."/.order";
                    rename($folder."/.order", $folder."/_order.old");
                    unlink($folder."/.order.xml");
                    //arrayList($updated_order);
                    
                    if(safeSave($order_file, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<xable>\n".arrayToXml($updated_order, 1)."</xable>")) {
                        echo "Updated: $order_file<br>\n";
                    };

                }
                else {
                    echo "Already XML: $file<br>\n";
                }
            }
        };
    };
        
    echo "<h1>Order Update (txt -> xml)</h1><hr>\n";
    if(isset($_POST["action"]) && $_POST["action"] == "update") {
        updateOrder($pages, $title_tag, $languages);
    }
    else {
        echo "<form method='post'>\n";
        echo "<p>Update \".order\" files from obsolete TXT format to XML.</p>\n";
        echo "<button name='action' value='update' type='submit'>Submit</button>\n";
        echo "</form>\n";
    };
   
?>
        
    </body>
</html>