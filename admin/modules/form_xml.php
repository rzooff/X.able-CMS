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
                    $filesize = path("$root/$media", "size");
                    if(in_array($icon, array("csv", "doc", "pdf"))) { $icon = "fi-page-".$icon; }
                    elseif(in_array($icon, array("mp4", "ogg", "webm"))) { $icon = "fi-play-video"; }
                    else { $icon = "fi-page"; };
                    $filename = clearFilename($media);
                    $info = "$filename<br><span class=\"info\">".path($media, "dirname")."/</span><br><span class=\"info\">$filesize kB</span>";
                    echo
                        "\t\t\t\t\t\t\t<div class='thumb' filesize='".$filesize."' path='$media'><span class='file_icon $icon'></span>".
                        "<p>$filename</p>".
                        "<button class='download action'><a href='$root/$media' class='help' help='$info' download><span class='fi-download'></span></a></button>".
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
                    "<label for='$file_id' class='upload_file fi-upload' help='".localize("add-file")."'><p>".localize("add-file")."</p></label>".
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
                    $ext = path("$root/$media", "extension");
                    list($width, $height) = imagesize("$root/$media");
                    $filesize = path("$root/$media", "size");
                    echo
                        "\t\t\t\t\t\t\t<div class='thumb $ext' filesize='".$filesize."' size='".$width."x".$height."' path='$media' style='background-image:url(\"$root/$media\")'>".
                        "<button class='zoom action'><span class='fi-magnifying-glass'></span></button>".
                        "<button class='delete' help='".localize("delete-image")."'><span>x</span></button></div>\n";
                }
                elseif(!file_exists("$root/$media") && path("$root/$media", "extension") != "") { // Image not found
                    echo
                        "\t\t\t\t\t\t\t<div class='thumb not_found'><label class='fi-prohibited'><p>".localize("not-found")."!</p></label>".
                        "<button class='delete' help='".localize("delete-image")."'><span>x</span></button></div>\n";
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
                    "<label for='$image_id' class='upload_file fi-upload' help='".localize("add-image")."'><p>".localize("add-image")."</p></label>".
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
                if(path(array_shift(explode(";", $media)), "extension") == "") {
                    $folder = $media;
                }
                else {
                    $temp = explode(";", $media);
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
                foreach(explode(";", $media) as $path) {
                    $file = "$root/$path";
                    $icon = path("$file", "extension");
                    $filesize = path($file, "size");
                    $filename = clearFilename($file);
                    $info = "$filename<br><span class=\"info\">".path($path, "dirname")."/</span><br><span class=\"info\">$filesize kB</span>";
                    
                    if(in_array($icon, array("csv", "doc", "pdf"))) { $icon = "fi-page-".$icon; }
                    else { $icon = "fi-page"; };
                    
                    if($file != "" && file_exists($file) && !is_dir($file)) {
                        echo
                            "\t\t\t\t\t\t\t<div class='thumb' filesize='".$filesize."' path='$path' ><span class='file_icon $icon'></span>".
                            "<p>$filename</p>".
                            "<button class='download action'><a href='$path' class='help' help='$info' download><span class='fi-download'></span></a></button>".
                            "<button class='delete' help='".localize("delete-file")."'><span>x</span></button>".
                            "<button class='sort move_left' help='".localize("move-left")."'><span>&lt;</span></button>".
                            "<button class='sort move_right' help='".localize("move-right")."'><span>&gt;</span></button>".
                            "</div>\n";
                    };
                };
                // ====== Files upload ======
                $files_id = "$id-media-0-files-0";
                //$folder = array_shift(explode(";", $media));
                // Taken
                
                $taken = join(";", listDir("$root/$folder", "." ) ); // taken filenames in destination folder
                
                echo
                    "\t\t\t\t\t\t\t<div class='upload'>".
                    "<label for='$files_id' class='upload_file fi-upload' help='".localize("add-files")."'><p>".localize("add-files")."</p></label>".
                    "<input name='".$files_id."[]' id='$files_id' type='file' class='upload_file $files_id' multiple>".
                    "<input name='upload|$files_id' class='upload_filenames' type='hidden' value=''>".
                    "<input class='taken_filenames' type='hidden' value='$taken'>".
                    "</div>\n";
                // =========================
                echo "\t\t\t\t\t\t</div>\n"; // .gallery
            }
            // ====== GALLERY ======
            elseif($media_type == "gallery") {
                
                //echo "LOCALIZE: ".localize("cancel-label")."<br>\n";
                // Media fix
                if(path(array_shift(explode(";", $media)), "extension") == "") {
                    $folder = $media;
                }
                else {
                    $temp = explode(";", $media);
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
                foreach(explode(";", $media) as $path) {
                    //echo "\t\t\t\t\t\t\t<img src='$image'>\n";
                    $image = "$root/$path";
                    if($image != "" && file_exists($image) && !is_dir($image)) {
                        
                        list($width, $height) = imagesize($image);
                        $filesize = path($image, "size");
                        echo
                            "<div class='thumb' size='".$width."x".$height."' filesize='".$filesize."' path='$path' style='background-image:url(\"$image\")'>".
                            "<button class='delete' help='".localize("delete-image")."'><span>x</span></button>".
                            "<button class='zoom action'><span class='fi-magnifying-glass'></span></button>".
                            "<button class='sort move_left' help='".localize("move-left")."'><span>&lt;</span></button>".
                            "<button class='sort move_right' help='".localize("move-right")."'><span>&gt;</span></button>".
                            "</div>\n";
                    };
                };
                // ====== Files upload ======
                $gallery_id = "$id-media-0-gallery-0";
                //$folder = array_shift(explode(";", $media));
                
                // Taken
                $taken = join(";", listDir("$root/$folder", "bmp,jpg,jpeg,gif,png,ico,svg,tif,tiff" ) ); // taken filenames in destination folder
            
                echo
                    "\t\t\t\t\t\t\t<div class='upload'>".
                    "<label for='$gallery_id' class='upload_file fi-upload' help='".localize("add-images")."'><p>".localize("add-images")."</p></label>".
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
                $media = str_replace("youtu.be/", "youtube.com/embed/", $media); // standard youtube -> shared
                
                echo "\t\t\t\t\t\t<div class='video' value='$media'>".
                    "<div class='thumb embed'>".
                    "<iframe src='$media' frameborder='0' allowfullscreen></iframe>".$play_button.
                    "<button class='delete' help='".localize("remove-video-link")."'><span>x</span></button>".
                    "<label class='upload_file update_link fi-share' help='".localize("add-video-link")."'><p>Dodaj link</p></label>".
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
        $selected = explode(";", $section['selected'][0]);
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

    function putDate($section) {
    // --------------------------------
    //  $section = <array> section xml content
    // --------------------------------
    // Put xml date data to HTML
    // --------------------------------
        $date = $section['date'][0];
        $options = explode(";", $section['options'][0]);
        
        if(in_array("disabled", $options)) {
            $help = localize("calendar-auto-info");
            $prop = "disabled";
        }
        else {
            $help = localize("calendar-input-info");
            $prop = "";
        };
        
        $date_format = $_SESSION["ini_site_options"]["date_format"];
        echo "\t\t\t\t\t<input type='hidden' class='date' value='".$date."' disabled>\n";
        echo "\t\t\t\t\t<input type='hidden' class='options' value='".join(";", $options)."' disabled>\n";
        echo "\t\t\t\t\t<div><div class='date_box input_box'>\n";
        echo "\t\t\t\t\t\t<input type='text' value='' placeholder='".$_SESSION["ini_site_options"]["date_format"]."' $prop>\n";
        echo "\t\t\t\t\t\t<button class='calendar help' help='".$help."'><span class='fi-calendar'></span></button>\n";
        echo "\t\t\t\t\t</div></div>\n";
    };

    function putString($att, $section_name, $section, $root) {
    // --------------------------------
    // $att = <array> xml ATTribute part
    // $section_name = <string> xable section name
    // $section = <array> section xml content
    // $root = <string> site root path
    // --------------------------------
    // Put xml string data to HTML / or active page link
    // --------------------------------
        $string = $att[0];
        if($section_name == "active_link" && is_string($section['disabled'][0]) && $section['disabled'][0] != "") {
            $label = $section['label'][0];
            if($label == "") { $label = "Otwórz link w nowym oknie"; };
            echo "\t\t\t\t\t<input type='hidden' class='string' value='".$string."'>\n";
            echo "\t\t\t\t\t<div><a class='active_link help hide_label' help='$label' href='$root/$string' target='_blank'>$string</a></div>\n";
        }
        elseif($section_name == "active_button" && is_string($section['disabled'][0]) && $section['disabled'][0] != "") {
            $label = $section['label'][0];
            if($label == "") { $label = "OK"; };
            echo "\t\t\t\t\t<input type='hidden' class='string' value='".$string."'>\n";
            $link = $root."/".str_replace("@path", $_GET['path'], $string);
            echo "\t\t\t\t\t<div><a class='active_link help hide_label' help='$label' href='$link'>".array_shift(explode(" ", $label))."</a></div>\n";
        }
        elseif(in_array($section_name, [ "href", "href_404", "folder_path" ])) {
            $string = str_replace("'", "&#39;", $string);
            $help = localize("browse-file-info");
            echo "\t\t\t\t\t<input type='hidden' class='string' value='".$string."'>\n";
            echo "\t\t\t\t\t<div><div class='browse_box'>".
                "<input type='text' value=''>".
                "<button class='browse help' help='$help'><span class='fi-folder'></span></button>".
                "</div></div>\n";
        }
        else {
            $string = str_replace("'", "&#39;", $string);
            echo "\t\t\t\t\t<input type='text' class='string' value='".$string."'>\n";
        };
        // Options:
        if(!is_string($options = $section['options'][0])) { $options == ""; };
        echo "\t\t\t\t\t<input type='hidden' class='options' value='".$options."'>\n";
    };

    function putButton($section) {
    // --------------------------------
    // $att = <array> xml ATTribute part
    // --------------------------------
    // Put xml string data to HTML / or active page link
    // --------------------------------
        $label = $section['label'][0];
        $help = $section['description'][0];
        if(is_string($help) && $help != "") { $help_class = "help"; } else { $help_class = ""; };
        $action = $section['action'][0];
        echo "\t\t\t\t\t<input type='hidden' class='action' value='".$action."'>\n";
        if(substr($action, 0, 7) == "http://" || substr($action, 0, 8) == "https;//") { $target = "target='_blank'"; } else { $target = ""; }
        //echo "\t\t\t\t\t<div><a class='active_link hide_label $help_class' help='$help' value='action' href='$action' $target>".$label."</a></div>\n";
        echo "\t\t\t\t\t<div><button class='action_button hide_label $help_class' help='$help' href='$action' $target>".$label."</button></div>\n";
        echo "\t\t\t\t\t</div>\n";
    };

    function putCode($att) {
    // --------------------------------
    // $att = <array> xml ATTribute part
    // --------------------------------
    // Put xml string data to HTML / or active page link
    // --------------------------------
        $string = $att[0];
        $string = str_replace("'", "&#39;", $string);
        $string = str_replace("[br]", "\n", $string);
        echo "\t\t\t\t\t<textarea class='code'>".$string."</textarea>\n";
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
                if(substr($text, 0, 1) == "\n") { $text = "\n".$text; }; // Fix for ignore leading enter in textarea by default
                echo "\t\t\t\t\t\t<textarea class='$language'>$text</textarea>\n";
            }
            else {
                $text = str_replace("'", "&#39;", $text); // fix for '
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
			echo "\t\t\t\t\t\t\t<tr class='start'><td class='heading manual' help='".localize("table-help-html")."'>?</td>";

			foreach(array_keys(explode(";", $table[0])) as $col_num) {
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
				$row = explode(";", $row);
				foreach(array_keys($row) as $col_num) {
					$col = $row[$col_num];
                    $col_chr = chr(65 + $col_num);
					if(trim($col) == "") { $col = "&nbsp;"; } // dummy content for emty table
                    elseif(substr($col, strlen($col) - 4, 4) == "[br]") { $col = substr($col, 0, strlen($col) - 4); };
                    $col = str_replace("[semi]", "&semi;", $col);
                    $col = str_replace("[br]", "<br>", $col);
					echo "\t\t\t\t\t\t\t\t<td  class='num_$col_chr' contenteditable>".$col."</td>\n";
				};
				echo "\t\t\t\t\t\t\t</tr>\n";
			};
			echo "\t\t\t\t\t\t</table>\n";
            
		};
        echo "<button class='button add_table_row help' help='".localize("add-table-row")."'><span class='fi-plus'></span></button>\n";
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
    if(count($languages) > 0) {
        echo "\t\t\t\t<div id='lang'><span class='fi-web manual' help='".localize("language-change")."'></span><p>".localize("language-label").":</p><ul>\n";
        foreach($languages as $language) { echo "\t\t\t\t\t<li value='$language'>".strtoupper($language)."</li>\n"; };
        echo "\t\t\t\t</ul></div>\n";
    }
    else {
        echo "\t\t\t\t<div id='lang' ><span class='fi-web'></span><p>".localize("language-label").":&nbsp;&nbsp;&nbsp;-</p></div>\n";
    }
    
    // ====== Path info ======
    echo "<p id='path_info'>path_info</p>\n";

    // ====== Main title ======
    if($path != $saveas) {
        $group = $_GET['group'];
        if(!is_string($group) || $group == "") { $group = ucfirst( array_pop( explode("/", path($saveas, "dirname")) ) ); };
        echo "\t\t\t<h2>".ucwords(path($saveas, "filename"))."<span> / ".localize("in-group").": @group</span></h2>\n";
    }
    else {
        echo "\t\t\t<h2>".localize("new-page").": ".path($saveas, "basename")."</h2>\n";
    };

    // ====== File(s) status ======
    $draft_path = "$path.$draft_ext";
    $previous_path = "$path.$previous_ext";

    $page_info = array();
    
    if(file_exists($path) && path($path, "extension") != "template") {
        $page_info[] = "<span class=\"light_info\">".localize("status-published").":</span> <b>".str_replace(" ", ", ", path($path, "modified"))."</b>";
        $page_status = "published";
        echo "<input type='hidden' id='published_version' value='true'>\n";
    }
    else {
        $page_info[] = "<span class=\"light_info\">".localize("status-published").":</span> <span class=\"warning\"><b>".localize("status-none")."</b></span>";
        $page_status = "template";
        echo "<input type='hidden' id='published_version' value='false'>\n";
    };
    
    if(file_exists($draft_path)) {
        $page_info[] = "<span class=\"light_info\">".localize("status-edited").":</span> <b>".str_replace(" ", ", ", path($draft_path, "modified"))."</b>";
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
        $page_info[] = "<span class=\"light_info\">".localize("status-edited").":</span> <b>".localize("none-label")."</b>";
        echo "<input type='hidden' id='draft_version' value='false'>\n";
    };

    if(file_exists($previous_path)) {
        $page_info[] = "<span class=\"light_info\">".localize("status-previous").":</span> <b>".str_replace(" ", ", ", path($previous_path, "modified"))."</b>";
        echo "<input type='hidden' id='previous_version' value='true'>\n";
    }
    else {
        $page_info[] = "<span class=\"light_info\">".localize("status-previous").":</span> <b>".localize("none-label")."</b>";
        echo "<input type='hidden' id='previous_version' value='false'>\n";
    };

    $page_info = "<span class=\"file_info\">".join("<br>", $page_info)."</span>";

    // Show status
    if($_SESSION['ini_site_options']['draft_support'] == "false") {
        // Status not shown
    }
    elseif($page_status == "published") {
        echo "\t\t\t<p class='show_info'><span class='fi-info manual color_published' help='$page_info'></span> ".localize("status-published")."</p>\n";
    }
    elseif($page_status == "unpublished-draft") {
        echo "\t\t\t<p class='show_info'><span class='fi-info manual color_unpublished' help='$page_info'></span> ".localize("status-unpublished")."</p>\n";
    }
    elseif($page_status == "draft") {
        echo "\t\t\t<p class='show_info'><span class='fi-info manual color_draft' help='$page_info'></span> ".localize("status-edited")."</p>\n";
    }
    else { // Template
        echo "\t\t\t<p class='show_info'><span class='fi-info manual color_template' help='$page_info'></span> ".localize("status-new")."</p>\n";
        echo "\t\t\t<input type='hidden' id='user_filename' value='".$_GET["user_filename"]."'>\n";
    };

    // ====== Load XML ======
    $xml = loadXml($path);
    //arrayList($xml);
    // ====== Articles ======

    $new_below = explode(",", $_SESSION['ini_site_options']['new_below']);

    foreach(array_keys($xml) as $article_name) {
        if(substr($article_name, 0, 1) != "_") {
            $article_group = $xml[$article_name];
            foreach(array_keys($article_group) as $article_num) {
                $article = $article_group[$article_num];
                echo "\t\t\t<article class='$article_name'>\n";
                echo "\t\t\t\t<h3 class='xml'>$article_name</h3>\n";
                // Fold / unfold
                echo "\t\t\t\t<div class='fold_expand'><span class='fi-minus help fold' help='".localize("post-fold")."'></span><span class='fi-list help expand' help='".localize("post-expand")."'></span></div>\n";
                // ====== POST type Article Buttons ======
                if(substr($article_name, 0, 6) == "multi_") {
                    // Addd direction in Help
                    if(in_array($article_name, $new_below)) { $help = localize("below-label"); }
                    else { $help = localize("above-label"); };
                    $help = lowercase($help);
                    
                    echo
                        "\t\t\t\t<div class='buttons'>\n".
                        "\t\t\t\t\t<button class='delete' help='".localize("post-remove")."'><span class='fi-x'></span></button>\n".
                        "\t\t\t\t\t<button class='down' help='".localize("move-down")."'><span class='fi-arrow-down'></span></button>\n".
                        "\t\t\t\t\t<button class='up' help='".localize("move-up")."'><span class='fi-arrow-up'></span></button>\n".
                        "\t\t\t\t\t<button class='new $article_name' help='".localize("post-new")." ($help)"."'><span class='fi-plus'></span></button>\n".
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
                            elseif($att_name == "code") { putCode($att); }
                            elseif($att_name == "checkbox" || $att_name == "radio") { putOption($id, $section, $att, $att_name); }
                            elseif($att_name == "text") { putText($type, $att); }
                            elseif($att_name == "format") { putHidden($att_name, $att); }
                            elseif($att_name == "media") { putMedia($id, $section, $att, $root); }
                            elseif($att_name == "table") { putTable($id, $section, $att, $root); }
                            elseif($att_name == "date") { putDate($section); }
                            elseif($type == "button" && $att_name == "action") { putButton($section); }
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
    echo "\t\t\t<p class='header'>".localize("xml-preview")."<span class='changed'></span></p>\n";

    echo "\t\t\t\t<p class='label'>Open path</p>\n";
    echo "\t\t\t\t<input type='text' name='edit_path' id='edit_path' value='$path'>\n";
    echo "\t\t\t\t<p class='label'>Save path</p>\n";
    echo "\t\t\t\t<input type='text' name='save_path' id='save_path' value='$saveas'>\n";
    echo "\t\t\t\t<p class='label'>Files to delete</p>\n";
    echo "\t\t\t\t<input type='text' name='delete_files' id='delete_files' value=''>\n";
    echo "\t\t\t\t<input type='hidden' name='current_edit_lang' id='current_edit_lang' value='".$_SESSION['edit_lang']."'>\n";
    //echo "\t\t\t\t".join("\n\t\t\t\t", $file_manager)."\n";
    foreach(array_keys($file_manager) as $key) {
        $files = checkFiles($root, explode(";", $file_manager[$key]));
        echo "\t\t\t\t<p class='label'>File manager: $key</p>\n";
        echo "\t\t\t\t<input type='text' name='file_manager|$key' id='file_manager|$key' value='".join(";", $files)."'>\n";;
    };
    echo "\t\t\t\t<p class='label'></p>\n";
    echo "\t\t\t\t<textarea name='output|xml' id='output'></textarea>\n";
    echo "\t\t\t\t<div class='scroll_box'><div class='xml_preview' contenteditable='true'></div></div>\n";

    echo "\t\t\t\t<button class='close fi-x'></button>\n";
    echo "\t\t\t</div>\n";

    // ======
    echo "\t\t</form>\n"; // #cms

?>