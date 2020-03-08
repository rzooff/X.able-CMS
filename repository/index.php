<?php
    //$updated = "20190701-1400";
    $updated = uniqid();

    require "modules/launcher.php";
?>

<!doctype html>

<html>
	<head>
        <!--
            This website is powered by X.able CMS
            http://xable.maciejnowak.com

            Design & development: maciejnowak.com
        -->
        <?php pasteStatistics("head") ?>
        <?php require "modules/head.php" ?>

        <!-- ====== CSS / might be moved to HEAD via js ====== -->
        <link class='async' rel="stylesheet" href="<?php echo $root; ?>style/layout.css<?php echo "?v=$updated"; ?>">
        <link class='async' rel="stylesheet" href="<?php echo $root; ?>style/layout-mobile.css<?php echo "?v=$updated"; ?>">
        
        <!-- ====== ICONS ====== -->
        <!-- <script src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js"></script> -->
        <script src="https://unpkg.com/ionicons@5.0.0/dist/ionicons.js"></script>
        
        <!-- ====== FONTS ====== -->
        <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700|Audiowide&amp;subset=latin-ext" rel="stylesheet">
        
        <?php include "modules/loader.php"; ?>

	</head>

	<body>
        <?php echo $loader_html; ?>
        <?php pasteStatistics("body") ?>
        
        <main>
            <?php

                echo "\n";

                // ===============================
                //          MAIN CONTENT
                // ===============================
            
                $site_map = [];

                foreach($pages_list as $href) {
                    $page_path = "$pages/$href.xml";
                    if($xml = loadXml($page_path, "draft")) {
                        
                        $id = makeId($href);
                        $site_map[$id] = readXml($xml, "header title");
                        echo "<!-- $href -->\n";

                        echo "\t\t\t<div class='section_box'>\n";
                        echo "\t\t\t\t<section class='standard' id='".$id."'>\n";
                            
                        foreach(array_keys($xml) as $article_name) {
                            if(substr($article_name, 0, 1) != "_" && $article_name != "subpages") {

                                echo "\t\t\t\t\t<div class='article_box ".$article_name."_box'>\n";

                                $article_group = $xml[$article_name];

                                foreach(array_keys($article_group) as $article_num) {

                                    $article = $article_group[$article_num];
                                    $val = getVal($article);

                                    if($id == "start") {
                                        //arrayList($val);
                                        echo "\t\t\t\t\t\t<div class='bg bg_image' style='background-image:url(\"".$root.$val["background"]."\")'></div>\n";
                                        echo "\t\t\t\t\t\t<div class='bg bg_fade'></div>\n";

                                        echo "\t\t\t\t\t\t<article class='".$article_name."'>\n";

                                        //$logo = "<span class='big'>&gt;&lt;</span>.able<span class='small'>CMS</span>";
                                        $logo = "<span class='logo_font'><span class='big'>&gt;&lt;</span>.able</span><span class='small'>CMS</span>";
                                        echo "\t\t\t\t\t\t\t<h1>$logo</h1>\n";

                                        echo "\t\t\t\t\t\t\t<div class='text_box vertical_center'>\n";
                                        echo "\t\t\t\t\t\t\t\t<h3 class='headline'>".BBCode($val["headline"], true)."</h3>\n";
                                        echo "\t\t\t\t\t\t\t\t<p class='text'>".BBCode($val["text"], true)."</p>\n";
                                        echo "\t\t\t\t\t\t\t\t<div class='button_box'><button class='scrolldown'>".$val["scrolldown"]."</button></div>\n";
                                        echo "\t\t\t\t\t\t\t</div>\n";
                                        echo "\t\t\t\t\t\t\t<div class='image_box'><img src='".$root.$val["image"]."'></div>\n";

                                        echo "\t\t\t\t\t\t</article>\n";

                                    }
                                    elseif($article_name == "header") {

                                        echo "\t\t\t\t\t\t<article class='slide_up ".$article_name."'>\n";
                                        echo "\t\t\t\t\t\t\t<div class='text_box'>\n";
                                        echo "\t\t\t\t\t\t\t\t<h2 class='title'>".$val["title"]."</h2>\n";
                                        echo "\t\t\t\t\t\t\t\t<p class='text'>".BBCode($val["text"], true)."</p>\n";
                                        echo "\t\t\t\t\t\t\t</div>\n";
                                        echo "\t\t\t\t\t\t</article>\n";

                                    }
                                    elseif($article_name == "multi_post" || $article_name == "multi_feature") {
                                        //arrayList($val);
                                        if(testMedia($val["image"])) {
                                            $class = "image_post";
                                            $image = $val["image"];
                                        }
                                        else {
                                            $class = "text_post";
                                            $image = false;
                                        };
                                        echo "\t\t\t\t\t\t<article class='slide_up $class $article_name num_$article_num'>\n";
                                        echo "\t\t\t\t\t\t\t<div class='text_box vertical_center'>\n";
                                        if(isset($val["icon_font"]) && $val["icon_font"] != "") {
                                            echo "\t\t\t\t\t\t\t\t<div class='icon_box'><ion-icon name='".$val["icon_font"]."'></ion-icon></div>\n";
                                        }
                                        echo "\t\t\t\t\t\t\t\t<h4 class='title'>".$val["title"]."</h4>\n";
                                        echo "\t\t\t\t\t\t\t\t<p class='text'>".BBCode($val["text"], true)."</p>\n";

                                        if(isset($val["folder_path"]) && $val["folder_path"] != "" && ($files_list = listDir($val["folder_path"], ".,?"))) {
                                            //arrayList($files_list);
                                            $max_date = 0;
                                            $downloads = [ "other" => [], "installer" => [] ];
                                            $key_names = [ "other" => "Dodatkowe pliki", "installer" => "Wersje archiwalne" ];

                                            foreach($files_list as $file) {
                                                $ext = path($file, "extension");
                                                if($ext == "zip") {
                                                    $date = intval(array_pop(explode("_", path($file, "filename"))));
                                                    $downloads["installer"][$date] = $file;
                                                }
                                                elseif($ext != "php") {
                                                    $downloads["other"][] = $file;
                                                }
                                            }
                                            if(count($downloads["installer"]) > 0) {
                                                krsort($downloads["installer"]);
                                                foreach(array_keys($downloads) as $key) {
                                                    $files_group = $downloads[$key];
                                                    $icon = "<ion-icon name='chevron-down'></ion-icon>";
                                                    echo "<dl class='text key_$key'>\n";
                                                    echo "\t<dt>$icon ".$key_names[$key]."</dt>\n";

                                                    foreach($files_group as $file) {
                                                        $basename = path($file, "basename");
                                                        $filesize = round(filesize($file) / 1024, 2);

                                                        $icon = "<ion-icon name='document-sharp'></ion-icon>";
                                                        echo "\t\t<dd>$icon <a href='".$root.$file."' download>$basename <span class='small'>($filesize kB)</span></a></dd>\n";
                                                    }

                                                    echo "</dl>\n";
                                                }

                                                echo "<div class='button_box'><a class='button current_version' href=''>Pobierz aktualną wersję</a><p class='version'>wersja</p></div>\n";
                                            }
                                        };

                                        echo "\t\t\t\t\t\t\t</div>\n";
                                        // media?
                                        if($image) {
                                            echo "\t\t\t\t\t\t\t<figure class='photo' style='background-image:url(\"".$root.$photo."\")'></figure>\n";
                                        }


                                        echo "\t\t\t\t\t\t</article>\n";
                                    }

                                    else {
                                        //echo "----------------\n";
                                        echo "<!-- Undefinded article type: \"".$article_name."\" -->\n";
                                        //echo "----------------\n";
                                        //arrayList($article);
                                    };
                                }; // foreach

                                echo "\t\t\t\t\t</div>\n";

                            } ; // if

                        } // foreach
 
                        echo "\t\t\t\t</section>\n";
                        echo "\t\t\t</div>\n";

                    } // if xml
                    
                    $order = loadXml("$pages/$href/.order");
                    $subpages_active = readXml($xml, "subpages active");
                    
                    if($order && (!isset($subpages_active) || $subpages_active != "")) {
                        echo "\t\t\t<div class='section_box'>\n";
                        
                        if($image = readXml($xml, "subpages image")) {
                            echo "\t\t\t\t<section class='standard subpages_box bg_box' style='background-image:url(\"".$root.$image."\")'>\n";
                        }
                        else {
                            echo "\t\t\t\t<section class='standard subpages_box nobg_box'>\n";
                        }
                        
                        echo "\t\t\t\t\t<div class='article_box'>\n";
                        echo "\t\t\t\t\t\t<article class='vertical_center'>\n";
                        echo "\t\t\t\t\t\t\t<div class='button_box'>\n";
                        foreach($order["multi_item"] as $item) {
                            $sub_path = readXml($item, "path");
                            if($sub_xml = loadXml("$pages/$href/$sub_path")) {
                                $sub_link = $root.$href."/".path($sub_path, "filename")."/";
                                $sub_title = BBCode(readXml($sub_xml, "header title"));

                                echo "\t\t\t\t\t\t\t\t<a class='button' href='$sub_link'>$sub_title</a>\n";
                            }
                        };
                        echo "\t\t\t\t\t\t\t</div>\n";
                        echo "\t\t\t\t\t\t</article>\n";
                        echo "\t\t\t\t\t</div>\n";
                        echo "\t\t\t\t</section>\n";
                        echo "\t\t\t</div>\n";
                    }
                    
                } // foreach

            ?>

        </main>
        
        <aside id='project_zoom'>
            <div class='slide_box master' data-num="0">
                <div class='zoom_box'></div>
            </div>
            <div class='toggle_buttons'>
                <!-- Buttons -->
            </div>
        </aside>
        
        <nav>
            <div class='fake_cover'></div>
            <div class='bg_bar'></div>
            <?php
                echo "\n";
            
                // ===============================
                //           NAVIGATION
                // ===============================

                echo "\t\t\t<div class='nav_box'>\n";

                echo "\t\t\t\t<ul class='menu'>\n";
                foreach(array_keys($site_map) as $href) {
                    $title = $site_map[$href];
                    echo "\t\t\t\t\t<li><a href='#".$href."'>$title</a></li>\n";
                }

                echo "\t\t\t\t</ul>\n";

                /*
                // ====== Languages ======
                $lang_list = array();
                foreach($languages as $id) {
                    //if($id == $_SESSION['lang']) { array_unshift($lang_list, $id); } // Current on top
                    //else { $lang_list[] = $id; };
                    $lang_list[] = $id;
                };
                if(count($lang_list) > 1) {
                    echo "\t\t\t\t<ul class='languages'>\n";
                    foreach($lang_list as $id) {
                        if($id == $_SESSION['lang']) { $current = "class='current'"; } else { $current = "class='change'"; };
                        echo "\t\t\t\t\t<li $current value='$id'><button>".strtoupper($id)."</button></li>\n";
                    };
                    echo "\t\t\t\t</ul>\n";
                };
                echo join("", $menu['languages']);
                */

                // ============
                echo "\t\t\t</div>\n";

                echo "\t\t\t<div class='button_box vertical_center'>\n";
                echo "\t\t\t\t<button class='show'><ion-icon name='menu-sharp'></ion-icon></button>\n";
                echo "\t\t\t\t<button class='hide'><ion-icon name='chevron-back-sharp'></ion-icon></button>\n";
                echo "\t\t\t\t<button class='close'><ion-icon name='close-sharp'></ion-icon></button>\n";
                echo "\t\t\t</div>\n";
            
            ?>
        </nav>
        
        <!-- Javascript -->
        <script>
            <?php
                echo "\n";
                echo "\t\t\tvar ROOT = '$root';\n";
                echo "\t\t\tvar CURRENT_PAGE = '$current_page';\n";
                if(is_string($_SESSION['popup']) && $_SESSION['popup'] != "") {
                    echo "\t\t\tvar SHOW_POPUP = '".$_SESSION['popup']."';\n";
                }
                else {
                    echo "\t\t\tvar SHOW_POPUP = false;\n";
                }
            
            ?>
        </script>
        <script src='<?php echo $root; ?>script/jquery-3.4.1.min.js'></script>
        <script src='<?php echo $root; ?>script/functions.js'></script>
        <script src='<?php echo $root; ?>script/layout.js<?php echo "?v=$updated"; ?>'></script>

        <?php include "modules/plugins.php"; ?>

        <!-- <div id='help'>-</div> ->
        
        <!-- <footer><span>WebSite by </span><a href='http://maciejnowak.com' target='_blank'>maciejnowak.com</a></footer> -->
	</body>

</html>
