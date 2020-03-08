<?php
    $admin_root = "..";
    $site_root = "../..";
    $admin_pathes = [ $admin_root, "$site_root/_plugins" ];

    // ====== Load Functions Libraries ======
    require "$admin_root/script/functions.php";
    require "$admin_root/script/cms.php";
    require "$admin_root/script/xml.php";
?>

<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>X.able CMS / Admin Bak Delete</title>
		<link href='http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900|Inconsolata:400,700|Audiowide&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" type="text/css" href="<?php echo "$admin_root/_tools/_tools.css" ?>" />
        <style></style>
    </head>
    <body>
        <button class='close' onclick="self.close()">X</button>

<?php
    function AdminCleaner($pathes) {
    // ----------------------------------------
    // $path = <string> full directory PATH
    // ----------------------------------------
    // Delete folder with it's content
    // ----------------------------------------
        $flag = false;
        foreach($pathes as $path) {
            foreach(filesTree($path, "bak") as $file) {
                echo "[deleted]: $file<br>\n";
                unlink($file);
                $flag = true;
            }
        };
        if(!$flag) { echo "Nothing to delete.<br>\n"; };
    };
        
    echo "<h1>Admin Bak Delete</h1><hr>\n";
    if(isset($_POST["action"]) && $_POST["action"] == "delete") {
        AdminCleaner($admin_pathes);
    }
    else {
        echo "<form method='post'>\n";
        echo "<p>Delete all \".bak\" files in Admin folder.</p>\n";
        echo "<button name='action' value='delete' type='submit'>Submit</button>\n";
        echo "</form>\n";
    };

?>
        
    </body>
</html>