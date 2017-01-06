<?php
    echo "\n";

    function checkFiles($root, $files) {
        $checked = array();
        foreach($files as $file) {
            if(file_exists("$root/$file")) { $checked[] = $file; };
        };
        //arrayList($checked);
        return $checked;
    };

    // ======================================
    //         put XML data functions
    // ======================================

    function putHidden($att_name, $att) {
    // --------------------------------
    // $att = <array> xml ATTribute part
    // --------------------------------
    // Put hidden xml string data to HTML
    // --------------------------------
        $string = $att[0];
        echo "\t\t\t\t\t<input type='hidden' class='$att_name' value='".$string."'>\n";
    };

    function putLabel($att, $att_name) {
    // --------------------------------
    // $att = <array> xml ATTribute part
    // $att_name = <string> ATTribute NAME
    // --------------------------------
    // Put xml label or description data to HTML
    // --------------------------------
        // no languages version
        echo "\t\t\t\t\t<p class='$att_name'>".$att[0]."</p>\n";
        /*
        // languages version
        echo "\t\t\t\t\t<div class='$att_name'>\n";
        foreach(array_keys($att[0]) as $text_lang) {
            $text = $att[0][$text_lang][0];
            echo "\t\t\t\t\t\t<p class='$text_lang'>$text</p>\n";
        };
        echo "\t\t\t\t\t</div>\n";
        */
    };

    function putMedia($id, $section, $att, $root) {
    // --------------------------------
    // $id = <string> section unique ID
    // $section = <array> xml SECTION part
    // $att = <array> xml ATTribute part
    // $root = <string> root folder path
    // --------------------------------
    // Put xml media data to HTML
    // --------------------------------
        // Set
        $set = $section['set'][0];
		$valid_media = array_keys($section['media'][0]);
		if(!is_string($set) || $set == "" || !in_array($set, $valid_media)) { $set = array_shift(array_keys($section['media'][0])); };
		//if($set == "")
        $set_id = "$id-set-0";
        echo "\t\t\t\t\t<div class='set'>\n";
        foreach(array_keys($section['media'][0]) as $media_type) {
            if($media_type == $set) { $checked = "checked"; } else { $checked = ""; };
            echo "\t\t\t\t\t\t<label><input name='$set_id' type='radio' value='$media_type' $checked><span>$media_type</span></label>\n";
        };
        echo "\t\t\t\t\t</div>\n"; // .set
        // Media thumbs
        echo "\t\t\t\t\t<div class='media'>\n";
        foreach(array_keys($att[0]) as $media_type) {
            $media = $att[0][$media_type][0];
            // ====== FILE ======
            if($media_type == "file") {                
                $file_id = "$id-media-0-file-0";
                if($media != "" && file_exists("$root/$media") && !is_dir("$root/$media")) { // Existing file path
                    echo "\t\t\t\t\t\t<div class='file' value='$media'>\n";
                    $icon = path("$root/$media", "extension");
                    if(in_array($icon, array("csv", "doc", "pdf"))) { $icon = "fi-page-".$icon; }
                    else { $icon = "fi-page"; };
                    echo
                        "\t\t\t\t\t\t\t<div class='thumb' path='$media'><span class='file_icon $icon'></span>".
                        "<p>".clearFilename($media)."</p>".
                        "<button class='download'><span class='fi-download'></span></button>".
                        "<button class='delete' help='Usuń plik'><span>x</span></button></div>\n";
                }
                else { // No file or not found
                    if(path("$root/$media", "extension") != "") { $media = path($media, "dirname"); };
                    echo "\t\t\t\t\t\t<div class='file' value='$media'>\n";
                };
                // ====== File upload ======
                if(path($media, "extension") == "") { $folder = "$root/$media"; }
                else { $folder = path("$root/$media", "dirname"); };
                $taken = join(";", listDir( $folder, "." ) ); // taken filenames in destination folder
                echo
                    "\t\t\t\t\t\t\t<div class='upload'>".
                    "<label for='$file_id' class='upload_file fi-upload' help='Dodaj obrazek'><p>Dodaj plik</p></label>".
                    "<input name='$file_id' id='$file_id' type='file' class='upload_file $file_id' accept='*'>".
                    "<input name='upload|$file_id' class='upload_filenames' type='hidden' value=''>".
                    "<input class='taken_filenames' type='hidden' value='$taken'>".
                    "</div>\n";
                // =========================
                echo "\t\t\t\t\t\t</div>\n"; // .file
            }
            // ====== IMAGE ======
            elseif($media_type == "image") {
                $image_id = "$id-media-0-image-0";
                echo "\t\t\t\t\t\t<div class='image' value='$media'>\n";
                if(file_exists("$root/$media") && path("$root/$media", "extension") != "") { // Existing image path
                    if(path("$root/$media", "extension") == "png") { $ext = " png"; } else { $ext = ""; };
                    list($width, $height) = getimagesize("$root/$media");
                    echo
                        "\t\t\t\t\t\t\t<div class='thumb$ext' size='".$width."x".$height."' style='background-image:url(\"$root/$media\")'>".
                        "<button class='zoom'><span class='fi-magnifying-glass'></span></button>".
                        "<button class='delete' help='Usuń obrazek'><span>x</span></button></div>\n";
                }
                elseif(!file_exists("$root/$media") && path("$root/$media", "extension") != "") { // Image not found
                    echo
                        "\t\t\t\t\t\t\t<div class='thumb not_found'><label class='fi-prohibited'><p>Brak pliku!</p></label>".
                        "<button class='delete' help='Usuń obrazek'><span>x</span></button></div>\n";
                }
                elseif(!file_exists("$root/$media")) { // path not exists
                    //echo "<script> alert('ERROR! Invalid path: $image_id');</script>";
                };
                // ====== File upload ======
                if(path($media, "extension") == "") { $folder = "$root/$media"; }
                else { $folder = path("$root/$media", "dirname"); };
                $taken = join(";", listDir( $folder, "bmp,jpg,jpeg,gif,png,ico,svg,tif,tiff" ) ); // taken filenames in destination folder
                echo
                    "\t\t\t\t\t\t\t<div class='upload'>".
                    "<label for='$image_id' class='upload_file fi-upload' help='Dodaj obrazek'><p>Dodaj plik</p></label>".
                    "<input name='$image_id' id='$image_id' type='file' class='upload_file $image_id' accept='image/*'>".
                    "<input name='upload|$image_id' class='upload_filenames' type='hidden' value=''>".
                    "<input class='taken_filenames' type='hidden' value='$taken'>".
                    "</div>\n";
                // =========================
                echo "\t\t\t\t\t\t</div>\n"; // .image
            }
            // ====== multiple FILES ======
            elseif($media_type == "files") {
                // Media fix
                if(path(array_shift(split(";", $media)), "extension") == "") {
                    $folder = $media;
                }
                else {
                    $temp = split(";", $media);
                    $media = array();
                    // check for valid images only
                    foreach($temp as $file) {
                        if($file != "" && file_exists("$root/$file") && !is_dir("$root/$file")) {
                            $media[] = $file;
                        };
                    };
                    // Check the result
                    if(count($media) > 0) { // found images
                        $folder = path($media[0], "dirname");
                    }
                    else { // not found any of images
                        $folder = path($temp[0], "dirname");
                        $media = array($folder);
                    };
                    $media = join(";", $media);
                };
                
                echo "\t\t\t\t\t\t<div class='files' value='$media'>\n";
                foreach(split(";", $media) as $path) {
                    $file = "$root/$path";
                    $icon = path("$file", "extension");
                    if(in_array($icon, array("csv", "doc", "pdf"))) { $icon = "fi-page-".$icon; }
                    else { $icon = "fi-page"; };
                    
                    if($file != "" && file_exists($file) && !is_dir($file)) {
                        echo
                            "\t\t\t\t\t\t\t<div class='thumb' path='$path' ><span class='file_icon $icon'></span>".
                            "<p>".clearFilename($file)."</p>".
                            "<button class='download'><span class='fi-download'></span></button>".
                            "<button class='delete' help='Usuń plik'><span>x</span></button>".
                            "<button class='sort move_left' help='Przesuń w lewo'><span>&lt;</span></button>".
                            "<button class='sort move_right' help='Przesuń w prawo'><span>&gt;</span></button>".
                            "</div>\n";
                    };
                };
                // ====== Files upload ======
                $files_id = "$id-media-0-files-0";
                //$folder = array_shift(split(";", $media));
                // Taken
                
                $taken = join(";", listDir("$root/$folder", "." ) ); // taken filenames in destination folder
                
                echo
                    "\t\t\t\t\t\t\t<div class='upload'>".
                    "<label for='$files_id' class='upload_file fi-upload' help='Dodaj obrazki'><p>Dodaj pliki</p></label>".
                    "<input name='".$files_id."[]' id='$files_id' type='file' class='upload_file $files_id' multiple>".
                    "<input name='upload|$files_id' class='upload_filenames' type='hidden' value=''>".
                    "<input class='taken_filenames' type='hidden' value='$taken'>".
                    "</div>\n";
                // =========================
                echo "\t\t\t\t\t\t</div>\n"; // .gallery
            }
            // ====== GALLERY ======
            elseif($media_type == "gallery") {
                // Media fix
                if(path(array_shift(split(";", $media)), "extension") == "") {
                    $folder = $media;
                }
                else {
                    $temp = split(";", $media);
                    $media = array();
                    // check for valid images only
                    foreach($temp as $image) {
                        if($image != "" && file_exists("$root/$image") && !is_dir("$root/$image")) {
                            $media[] = $image;
                        };
                    };
                    // Check the result
                    if(count($media) > 0) { // found images
                        $folder = path($media[0], "dirname");
                    }
                    else { // not found any of images
                        $folder = path($temp[0], "dirname");
                        $media = array($folder);
                    };
                    $media = join(";", $media);
                };
                
                echo "\t\t\t\t\t\t<div class='gallery' value='$media'>\n";
                foreach(split(";", $media) as $path) {
                    //echo "\t\t\t\t\t\t\t<img src='$image'>\n";
                    $image = "$root/$path";
                    if($image != "" && file_exists($image) && !is_dir($image)) {
                        list($width, $height) = getimagesize($image);
                        echo
                            "<div class='thumb' size='".$width."x".$height."' path='$path' style='background-image:url(\"$image\")'>".
                            "<button class='delete' help='Usuń obrazek'><span>x</span></button>".
                            "<button class='zoom'><span class='fi-magnifying-glass'></span></button>".
                            "<button class='sort move_left' help='Przesuń w lewo'><span>&lt;</span></button>".
                            "<button class='sort move_right' help='Przesuń w prawo'><span>&gt;</span></button>".
                            "</div>\n";
                    };
                };
                // ====== Files upload ======
                $gallery_id = "$id-media-0-gallery-0";
                //$folder = array_shift(split(";", $media));
                // Taken

                $taken = join(";", listDir("$root/$folder", "bmp,jpg,jpeg,gif,png,ico,svg,tif,tiff" ) ); // taken filenames in destination folder
                
                echo
                    "\t\t\t\t\t\t\t<div class='upload'>".
                    "<label for='$gallery_id' class='upload_file fi-upload' help='Dodaj obrazki'><p>Dodaj pliki</p></label>".
                    "<input name='".$gallery_id."[]' id='$gallery_id' type='file' class='upload_file $gallery_id' accept='image/*' multiple>".
                    "<input name='upload|$gallery_id' class='upload_filenames' type='hidden' value=''>".
                    "<input class='taken_filenames' type='hidden' value='$taken'>".
                    "</div>\n";
                // =========================
                echo "\t\t\t\t\t\t</div>\n"; // .gallery
            }
            // ====== VIDEO ======
            elseif ($media_type == "video") {
                $media = str_replace("youtube.com/watch?v=", "youtube.com/embed/", $media); // standard youtube -> embed
                echo "\t\t\t\t\t\t<div class='video' value='$media'>".
                    "<div class='thumb embed'>".
                    "<iframe src='$media' autoplay='false' frameborder='0' allowfullscreen></iframe>".
                    "<button class='delete' help='Usuń link do video'><span>x</span></button>".
                    "<label class='upload_file update_link fi-share' help='Dodaj link'><p>Dodaj link</p></label>".
                    "</div>\n";
                // =========================
                echo "\t\t\t\t\t\t</div>\n"; // .video
                
            }
            // ====== NONE ======
            elseif ($media_type == "none") {
                echo "\t\t\t\t\t\t<div class='none' value=''><div class='thumb'></div></div>\n";
            };
        };
        echo "\t\t\t\t\t</div>\n"; // .media
    };

    function putOption($id, $section, $att, $att_name) {
    // --------------------------------
    // $id = <string> section unique ID
    // $section = <array> xml SECTION part
    // $att = <array> xml ATTribute part
    // $att_name = <string> ATTribute NAME
    // --------------------------------
    // Put xml option (checkbox/radio) data to HTML
    // --------------------------------
        $selected = split(";", $section['selected'][0]);
		// radio & no valid selected fix needed!
		echo "\t\t\t\t\t<ul class='$att_name'>\n";
        foreach($att[0]['option'] as $option) {
            if(in_array($option, $selected)) { $checked = "checked"; } else { $checked = ""; };
            if(strlen($option) < 3) { $label = strtoupper($option); } else { $label = ucfirst($option); };
            echo "\t\t\t\t\t\t<li><label class='option' value='$option' ><input name='$id' type='$att_name' value='$option' $checked><span>$label</span></label></li>\n";

        };
        echo "\t\t\t\t\t</ul>\n"; //.att_name
    };

    function putDisabled($att) {
    // --------------------------------
    // $att = <array> xml ATTribute part
    // --------------------------------
    // Put xml string data to HTML
    // --------------------------------
        $string = $att[0];
        echo "\t\t\t\t\t<input type='hidden' class='disabled' value='".$string."'>\n";
    };

    function putString($att, $section_name, $section, $root) {
    // --------------------------------
    // $att = <array> xml ATTribute part
    // --------------------------------
    // Put xml string data to HTML / or active page link
    // --------------------------------
        $string = $att[0];
        if($section_name == "active_link" && is_string($section['disabled'][0]) && $section['disabled'][0] != "") {
            //echo "\t\t\t\t\t<div class='string' value='$string'><a class='$section_name help' help='Otwórz link w nowym oknie' href='$root/$string' target='_blank'>$string</a></div>\n";
            echo "\t\t\t\t\t<input type='hidden' class='string' value='".$string."'>\n";
            echo "\t\t\t\t\t<div><a class='$section_name help' help='Otwórz link w nowym oknie' href='$root/$string' target='_blank'>$string</a></div>\n";
        }
        else {
            echo "\t\t\t\t\t<input type='text' class='string' value='".$string."'>\n";
        };
    };

    function putText($type, $att) {
    // --------------------------------
    // $type = <string> section data TYPE
    // $att = <array> xml ATTribute part
    // --------------------------------
    // Put xml text/textarea data to HTML
    // --------------------------------
        echo "\t\t\t\t\t<div class='text'>\n";
        foreach(array_keys($att[0]) as $language) {
            $text = $att[0][$language][0];
            if($type == "textarea") {
                $text = str_replace("&#91;", "\[", $text); // fix for [
                $text = str_replace("&#93;", "\]", $text); // fix for ]
                $text = str_replace("[br] ", "\n", $text); // fix for now
                $text = str_replace("[br]", "\n", $text);
                echo "\t\t\t\t\t\t<textarea class='$language'>$text</textarea>\n";
            }
            else {
                echo "\t\t\t\t\t\t<input type='text' class='$language' value='$text'>\n";
            };
        };
        echo "\t\t\t\t\t</div>\n";
    };
	 
    function putTable($id, $section, $att, $root) {
    // --------------------------------
    // $type = <string> section data TYPE
    // $att = <array> xml ATTribute part
    // --------------------------------
    // Put xml text/textarea data to HTML
    // --------------------------------
		//arrayList($section);
        echo "\t\t\t\t\t<div class='table'>\n";
		foreach(array_keys($section['table'][0]) as $language) {
			echo "\t\t\t\t\t\t<table class='$language'>\n";
			$table = $section['table'][0][$language];
			echo "\t\t\t\t\t\t\t<tr class='start'><td class='heading help' help='Aby edytować tabelę, wykonaj \"prawy klik\" na nagłówku wiersza lub kolumny'>?</td>";

			foreach(array_keys(split(";", $table[0])) as $col_num) {
                $col_chr = chr(65 + $col_num);
				//echo "<td class='num_$num heading edit_column help' help='Dodaj/usuń/przesuń kolumnę'>".chr(65 + $num)."</td>";
				echo "<td class='num_$col_chr heading edit_column'>X</td>";
			};
			echo "</tr>\n";

			foreach(array_keys($table) as $row_num) {
				$row = $table[$row_num];
				echo "\t\t\t\t\t\t\t<tr class='num_$row_num'>\n";
				//echo "\t\t\t\t\t\t\t\t<td class='heading edit_row help' help='Dodaj/usuń/przesuń wiersz'>".($row_num + 1)."</td>\n";
				echo "\t\t\t\t\t\t\t\t<td class='heading edit_row'>0</td>\n";
				$row = split(";", $row);
				foreach(array_keys($row) as $col_num) {
					$col = $row[$col_num];
                    $col_chr = chr(65 + $col_num);
					if(trim($col) == "") { $col = "&nbsp;"; } // dummy content for emty table
                    elseif(substr($col, strlen($col) - 4, 4) == "[br]") { $col = substr($col, 0, strlen($col) - 4); };
                    //$col = str_replace("[br]", "<br>", $col);
					echo "\t\t\t\t\t\t\t\t<td  class='num_$col_chr' contenteditable>".$col."</td>\n";
				};
				echo "\t\t\t\t\t\t\t</tr>\n";
			};
			echo "\t\t\t\t\t\t</table>\n";
            
		};
        echo "<button class='button add_table_row help' help='Dodaj wiersz tabeli'><span class='fi-plus'></span></button>\n";
        echo "\t\t\t\t\t</div>\n";
	 };

    // ======================================
    //           Image exits check
    // ======================================

    function showImage($path) {
    // --------------------------------
    // $path = <string> image file PATH
    // --------------------------------
    // RETURNS: <string> image file path or "no image" picture path if image file not exists.
    // --------------------------------
        $no_image = "images/noimage.jpg";
        if(file_exists($path) && in_array(path($path, "extension"), array("jpg", "jpeg", "gif", "png"))) { return $path;}
        else{ return $no_image; }
    };

    // ======================================
    // ======================================
    // ======================================
    //               Build HTML
    // ======================================
    // ======================================
    // ======================================

    // ====== Check for _special folder (repository ect) ======
    if(strpos($path, "/_repository/")) { $edit_buttons = "essential"; }
    else { $edit_buttons = "full"; };

    // ====== Form start ======

    echo "\n\t\t<form id='cms' class='xml' method='post' action='_publish.php' enctype='multipart/form-data'>\n";
    require("modules/header.php"); // User & Notifications panel
    echo "\t\t\t<main>\n";

    // ====== Languages ======
    echo "\t\t\t\t<div id='lang' ><span class='fi-web help' help='Język edytowanych treści'></span><p>Język:</p><ul>\n";
    foreach($languages as $language) { echo "\t\t\t\t\t<li value='$language'>".strtoupper($language)."</li>\n"; };
    echo "\t\t\t\t</ul></div>\n";

    // ====== Main title ======
    if($path != $saveas) {
        $group = $_GET['group'];
        if(!is_string($group) || $group == "") { $group = ucfirst( array_pop( split("/", path($saveas, "dirname")) ) ); };
        echo "\t\t\t<h2>".ucfirst(path($saveas, "filename"))." <span>/ w grupie: $group</span></h2>\n";
    }
    else {
        echo "\t\t\t<h2>Nowa strona: ".path($saveas, "basename")."</h2>\n";
    };

    // ====== File(s) status ======
    $draft_path = "$path.$draft_ext";
    $previous_path = "$path.$previous_ext";

    $page_info = array();
    
    if(file_exists($path) && path($path, "extension") != "template") {
        $page_info[] = "<span class=\"light_info\">Opublikowany:</span> <b>".str_replace(" ", ", ", path($path, "modified"))."</b>";
        $page_status = "published";
        echo "<input type='hidden' id='published_version' value='true'>\n";
    }
    else {
        $page_info[] = "<span class=\"light_info\">Opublikowany:</span> <span class=\"warning\"><b>BRAK</b></span>";
        $page_status = "template";
        echo "<input type='hidden' id='published_version' value='false'>\n";
    };
    
    if(file_exists($draft_path)) {
        $page_info[] = "<span class=\"light_info\">Edytowany:</span> <b>".str_replace(" ", ", ", path($draft_path, "modified"))."</b>";
        if($page_status == "published") {
            $page_status = "draft";
        }
        else {
            $page_status = "unpublished-draft";
        };
        $path = $draft_path; // ====== DRAFT PATH ======
        echo "<input type='hidden' id='draft_version' value='true'>\n";
    }
    elseif($edit_buttons == "full") {
        $page_info[] = "<span class=\"light_info\">Edytowany:</span> <b>BRAK</b>";
        echo "<input type='hidden' id='draft_version' value='false'>\n";
    };

    if(file_exists($previous_path)) {
        $page_info[] = "<span class=\"light_info\">Poprzedni:</span> <b>".str_replace(" ", ", ", path($previous_path, "modified"))."</b>";
        echo "<input type='hidden' id='previous_version' value='true'>\n";
    }
    else {
        $page_info[] = "<span class=\"light_info\">Poprzedni:</span> <b>BRAK</b>";
        echo "<input type='hidden' id='previous_version' value='false'>\n";
    };

    $page_info = "<span class=\"file_info\">".join("<br>", $page_info)."</span>";

    // Show status
    if($site_options['draft_support'] == "false") {
        // Status not shown
    }
    elseif($page_status == "published") {
         echo "\t\t\t<p class='show_info'><span class='fi-info manual color_published' help='$page_info'></span> Opublikowany</p>\n";
    }
    elseif($page_status == "unpublished-draft") {
         echo "\t\t\t<p class='show_info'><span class='fi-info manual color_unpublished' help='$page_info'></span> Edytowany, bez publikacji</p>\n";
    }
    elseif($page_status == "draft") {
         echo "\t\t\t<p class='show_info'><span class='fi-info manual color_draft' help='$page_info'></span> Edytowany</p>\n";
    }
    else {
         echo "\t\t\t<p class='show_info'><span class='fi-info manual color_template' help='$page_info'></span> Nowy</p>\n";
    };

    // ====== Load XML ======
    $xml = loadXml($path);

    // ====== Articles ======
    foreach(array_keys($xml) as $article_name) {
        if(substr($article_name, 0, 1) != "_") {
            $article_group = $xml[$article_name];
            foreach(array_keys($article_group) as $article_num) {
                $article = $article_group[$article_num];
                echo "\t\t\t<article class='$article_name'>\n";
                echo "\t\t\t\t<h3 class='xml'>$article_name</h3>\n";
                // Fold / unfold
                echo "\t\t\t\t<div class='fold_expand'><span class='fi-minus help fold' help='Zwiń wpis'></span><span class='fi-list help expand' help='Rozwiń wpis'></span></div>\n";
                // ====== POST type Article Buttons ======
                if(substr($article_name, 0, 6) == "multi_") {
                    echo
                        "\t\t\t\t<div class='buttons'>\n".
                        "\t\t\t\t\t<button class='delete' help='Usuń wpis'><span class='fi-x'></span></button>\n".
                        "\t\t\t\t\t<button class='down' help='Przesun w dół'><span class='fi-arrow-down'></span></button>\n".
                        "\t\t\t\t\t<button class='up' help='Przesuń w górę'><span class='fi-arrow-up'></span></button>\n".
                        "\t\t\t\t\t<button class='new $article_name' help='Nowy wpis'><span class='fi-plus'></span></button>\n".
                        "\t\t\t\t</div>\n";
                };
                // ====== Section HTML form ======
                foreach(array_keys($article) as $section_name) {
                    $section_group = $article[$section_name];
                    foreach(array_keys($section_group) as $section_num) {
                        $section = $section_group[$section_num];
                        $id = "$article_name-$article_num-$section_name-$section_num";
                        echo "\t\t\t\t<section class='$section_name'>\n";
                        echo "\t\t\t\t\t<h5>$section_name</h5>\n"; // if no label/description
                        // Type
                        //arrayList($section);
                        $type = $section['type'][0];
                        echo "\t\t\t\t\t<input type='hidden' class='type' value='".$type."'>\n";
                        // Attributes
                        foreach(array_keys($section) as $att_name) {
                            $att = $section[$att_name];
                            if($att_name == "type") {} // type already added to xml
                            elseif ($att_name == "label" || $att_name == "description") { putLabel($att, $att_name); }
                            // ====== CUSTOM ======
                            // ====================
                            elseif($att_name == "disabled") { putDisabled($att); }
                            elseif($att_name == "string") { putString($att, $section_name, $section, $root); }
                            elseif($att_name == "checkbox" || $att_name == "radio") { putOption($id, $section, $att, $att_name); }
                            elseif($att_name == "text") { putText($type, $att); }
                            elseif($att_name == "format") { putHidden($att_name, $att); }
                            elseif($att_name == "media") { putMedia($id, $section, $att, $root); }
                            elseif($att_name == "table") { putTable($id, $section, $att, $root); }
                            else {};
                        };
                        echo "\t\t\t\t</section>\n";
                    };
                };
                echo "\t\t\t</article>\n";
            };
        };
    };
    echo "\t\t\t</main>\n";

    // ====== FILE MANAGER ======
    $file_manager = array();
    $file_manager['upload'] = readXml($xml, "_file_manager upload");
    if(!is_string($file_manager['upload'])) { $file_manager['upload'] = ""; };
    $file_manager['delete'] = readXml($xml, "_file_manager delete");
    if(!is_string($file_manager['delete'])) { $file_manager['delete'] = ""; };

    // ====== Outupt data fields ======
    echo "\t\t\t<div id='outputs'>\n";
    echo "\t\t\t\t<input type='text' name='edit_path' id='edit_path' value='$path'>\n";
    echo "\t\t\t\t<input type='text' name='save_path' id='save_path' value='$saveas'>\n";
    echo "\t\t\t\t<input type='text' name='delete_files' id='delete_files' placeholder='Files to delete' value=''>\n";
    //echo "\t\t\t\t".join("\n\t\t\t\t", $file_manager)."\n";


    foreach(array_keys($file_manager) as $key) {
        $files = checkFiles($root, split(";", $file_manager[$key]));
        echo "\t\t\t\t<input type='text' name='file_manager|$key' id='file_manager|$key' placeholder='file_manager $key' value='".join(";", $files)."'>\n";;
    };

    echo "\t\t\t\t<textarea name='output|xml' id='output'></textarea>\n";
    echo "\t\t\t</div>\n";

    echo "\t\t</form>\n"; // #cms

?>