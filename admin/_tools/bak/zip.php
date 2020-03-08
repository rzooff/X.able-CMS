<?php
    $root = "../";
    require $root."script/functions.php";
    require $root."script/cms.php";
    require $root."script/xml.php";

    if($action == "unzip") {
        echo "<h1>UNZIP</h1>\n";

        $zip_path = "unzipped.zip";
        
        $unzipped_folder = path($zip_path, "filename");
        $unzipped_folder = uniqueFilename($unzipped_folder);

        if(extractArchive($zip_path, $unzipped_folder)) {
            echo "Unzipped!<br>\n";
        } else {
            echo "ERROR! Unable to unzip archive file.<br>\n";
        };
    };

    if($action == "zip") {
        echo "<h1>ZIP</h1>\n";

        $folder = $root."_tools";

        $zip_path = path($folder, "filename").".zip";
        $files = filesTree($folder, ".");

        if(archiveFiles($zip_path, $files, false)) {
             echo "Zip created.<br>\n";
        }
        else {
             echo "ERROR! Unable to create zip file.<br>\n";
        }
    };

?>