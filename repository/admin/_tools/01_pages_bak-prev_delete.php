<?php
    $admin_root = "..";
    $site_root = "../..";
    $pathes = [ "../../pages", "../../services" ];

    // ====== Load Functions Libraries ======

    require "$admin_root/script/functions.php";
    require "$admin_root/script/cms.php";
    require "$admin_root/script/xml.php";
?>

<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>X.able CMS / Pages Bak-prev Delete</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<link href='http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900|Inconsolata:400,700|Audiowide&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" type="text/css" href="<?php echo "$admin_root/_tools/_tools.css" ?>" />
        <style></style>
    </head>
    <body>
        <!-- <input class='close' type="button" value="X" onclick="self.close()"> -->
        <button class='close' onclick="self.close()">X</button>

<?php
    function XmlCleaner($pathes) {
    // ----------------------------------------
    // $path = <string> full directory PATH
    // ----------------------------------------
    // Delete folder with it's content
    // ----------------------------------------
        $flag = false;
        foreach($pathes as $path) {
            foreach(filesTree($path, ".") as $file) {

                //echo "> $file<br>\n";
                $ext = path($file, "extension");

                if(is_file($file) && ($ext == "bak" || $ext == "prev")) {
                    $folder = path($file, "dirname");
                    $orig_file = path($file, "dirname")."/".path($file, "filename");

                    if(!file_exists($orig_file)) {
                        unlink($file);
                        echo "Deleted: $file<br>\n";
                        $flag = true;
                    }
                }
            }
        };
        if(!$flag) { echo "Nothing to delete.<br>\n"; };
    };
        
    echo "<h1>Bak Cleaner script</h1><hr>\n";
    if(isset($_POST["action"]) && $_POST["action"] == "delete") {
        XmlCleaner($pathes);
    }
    else {
        echo "<form method='post'>\n";
        echo "<p>Delete all \".bak\" and \".prev\" files in pages & services folders.<br>Some unlinked media may stay in \"media\" folder.</p>\n";
        echo "<button name='action' value='delete' type='submit'>Submit</button>\n";
        echo "</form>\n";
    };
    
?> 

    </body>
</html>