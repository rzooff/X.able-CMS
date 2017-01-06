<?php
    //include "bufor.php"; <- wszystko co tam było poprawiłem w kodzie poniżej / maciej@maciejnowak.com

	session_start();
	require "script/functions.php";
	require "script/xml.php";

	$settings = loadXml("settings.xml");
	$navigation = loadXml("navigation.xml");
	$pages = "pages";

    // ====== Languages ======

    $languages = array();
    $all_languages = array();

    foreach($settings['multi_language'] as $language) {
        $active = readXml($language, "active");
        $id = readXml($language, "id");
        $all_languages[] = $id;
        if($active != "") { $languages[] = $id; };            
    };
    //arrayList($all_languages);

    if(in_array($_GET['lang'], $all_languages)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    elseif(!in_array($_SESSION['lang'], $languages)) {
        $_SESSION['lang'] = "pl";
    };

?>

<!doctype html>

<html>
	<head>
	
        <!--
            This website is powered by X.able CMS v3.0
            Copyright ©2017 by maciej@maciejnowak.com

            Design & development by maciejnowak.com
        -->
	
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta charset="UTF-8">
		<meta http-equiv="Content-Language" content="<?php echo $_SESSION['lang']; ?>">
		<title><?php echo readXml($settings, "page title"); ?></title>
        <meta name="description" content="<?php echo readXml($settings, "page description"); ?>"/>
        <meta name="keywords" content="<?php echo readXml($settings, "page keywords"); ?>"/>
        
        <!-- ====== CSS / might be moved to HEAD via js ====== -->
        <link class='async' rel="stylesheet" href="style/preview_mode.css">
        <link class='async' rel="stylesheet" href="style/layout.css">
        <link class='async' rel="stylesheet" href="style/layout-mobile.css">
        <link href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700,900&amp;subset=latin-ext" rel="stylesheet">

	</head>

	<body>

        <?php
            // RELOAD "page/"
            if(is_string($_GET['reload']) && $_GET['reload'] != "") {
                echo "<script> location.href = \"../".$_GET['reload']."\"; </script>\n";
            };
        
            // ====== Page ======
            //$current_page = $_GET['page'];
            //if(!is_string($current_page) || $current_page == "") { $current_page = "start"; };
        
            // ====== Check DRAFT PREVIEW mode ======
            if($_GET['preview'] == "none" || $_GET['preview'] == "false") { $_SESSION['preview_mode'] = false; }
            elseif(is_string($_GET['preview']) && $_GET['preview'] != "") { $_SESSION['preview_mode'] = $_GET['preview']; };
            // ====== Send preview status ======
            if(is_string($_SESSION['preview_mode']) &&  $_SESSION['preview_mode'] != "") {
                echo "<input type='hidden' id='preview_mode' value='".$_SESSION['preview_mode']."'>\n";
            };
        
            function makeId($string) {
                $string = strtolower(killPl($string));
                $string = str_replace(" ", "_", $string);
                return $string;
            }

        ?>
        
        <?php
            echo "\n";

            // ===============================
            //           NAVIGATION
            // ===============================
        
            echo "\t<nav>\n";
            echo "\t\t<ul class='menu'>\n";
        
            echo "\t\t\t<li><h1>".readXml($settings, "page title")."</h1></li>\n";
        
            foreach($navigation['multi_page'] as $page) {
                if(readXml($page, "active") != "") {
                    $title = readXml($page, "title");
                    $href = readXml($page, "href");
                    echo "\t\t\t<li><a href='$href'>$title</a></li>\n";
                }
            }

            echo "\t\t</ul>\n";
        
            // ====== Languages ======
            $lang_list = array();
            foreach($languages as $id) {
                //if($id == $_SESSION['lang']) { array_unshift($lang_list, $id); } // Current on top
                //else { $lang_list[] = $id; };
                $lang_list[] = $id;
            };
            if(count($lang_list) > 1) {
                echo "\t\t<ul class='languages'>\n";
                foreach($lang_list as $id) {
                    if($id == $_SESSION['lang']) { $current = "class='current'"; } else { $current = "class='change'"; };
                    echo "\t\t\t<li $current value='$id'><button>".strtoupper($id)."</button></li>\n";
                };
                echo "\t\t</ul>\n";
            };
            echo join("", $menu['languages']);
        
            // ============
        
            echo "\t</nav>\n";
        ?>
            
        <?php
            echo "\n";

            // ===============================
            //          MAIN CONTENT
            // ===============================
        
            foreach($navigation['multi_page'] as $page) {

                $href = readXml($page, "href");
                $title = readXml($page, "title");

                if(substr($href, 0 , 1) == "#") { $xml_name = substr($href, 1); } else { $xml_name = $href; };
                if(readXml($page, "active") != "" && is_array($xml = loadXml("$pages/$xml_name.xml", "draft"))) {
                    
                    echo "\t<section id='".makeId($xml_name)."'>\n";

                    foreach(array_keys($xml) as $article_name) {
                        // ====== POST ======
                        if($article_name == "multi_post") {
                            $article_group = $xml[$article_name];
                            foreach(array_keys($article_group) as $article_num) {

                                $article = $article_group[$article_num];
                                //arrayList($article);
                                echo "\t\t<article class='post_$article_num'>\n";

                                $title = readXml($article, "title");
                                $text = readXml($article, "text");
                                $media_type = readXml($article, "set");
                                $media = readXml($article, "media");
                                
                                if($title != "" || $text != "") {
                                    echo "\t\t\t<div class='text_box'>\n";
                                    echo "\t\t\t\t<h2>".$title."</h2>\n";
                                    echo "\t\t\t\t<p>".BBCode($text)."</p>\n";
                                    echo "\t\t\t</div>\n";
                                }

                                if($media_type == "image" || $media_type == "gallery") {
                                    if(is_file(array_shift(split(";", $media)))) {
                                        echo "\t\t\t<div class='images_box'>\n";
                                        foreach(split(";", $media) as $file) {
                                            echo "\t\t\t\t<figure style='background-image:url(\"$file\")'></figure>\n";
                                        }
                                        echo "\t\t\t</div>\n";
                                    }
                                }

                                if($media_type == "file" || $media_type == "files") {
                                    if(is_file(array_shift(split(";", $media)))) {
                                        echo "\t\t\t<div class='files_box'>\n";
                                        foreach(split(";", $media) as $file) {
                                            echo "\t\t\t\t<a href='$file' target='_blank'>".path($file, "basename")."</a><br>\n";
                                        }
                                        echo "\t\t\t</div>\n";
                                    }
                                }
                                
                                echo "\t\t</article>\n";
                                
                            } // foreach
                        } // if
                    } // foreach
                    
                    echo "\t</section>\n";
                } // if xml
            } // foreach
        ?>
        

        
        <!-- Javascript -->

        <script src='script/jquery-3.1.0.min.js'></script>
        <script src='script/functions.js'></script>
        <script src='script/preview_mode.js'></script>
        <script src='script/layout.js'></script>
        
	</body>
    

</html>
