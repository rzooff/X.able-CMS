<?php
    // ======================================
    //              ><.able CMS
    //      (C)2015-2019 maciejnowak.com
    // ======================================
    // compatibile: php5.4+ or higher

    //error_reporting(E_ALL);

    require("modules/_session-start.php");
    $ini_hidden = loadIni("xable.ini", "hidden");

    $panel_name = "creator";
    $panel_label = localize("creator-label");

    // ====== LANGUAGE ======
    $languages = array();
    foreach($settings['multi_language'] as $lang_data) {
        //if(readXml($lang_data, "active") != "") {
            $id = readXml($lang_data, "id");
            $languages[] = $id;
        //};
    };
    $lang = "pl";

    // ====== Move files - Media location change Tool ======
    function moveFiles($root) {
        $old_key = "move_files-old";
        $new_key = "move_files-new";
        $old_folders = [];
        $moved_count = 0;
        foreach(array_keys($_POST) as $key) {
            if(substr($key, 0, strlen($old_key)) == $old_key) {
                $old_path = $_POST[$key];
                $n = array_pop(explode("_", $key));
                $new_path = $_POST[$new_key."_".$n];
                if($new_path != "" && $old_path != "" && file_exists("$root/$old_path")) {
                    //echo "$n) $old_path -> $new_path<br>\n";
                    $folder = path("$root/$new_path", "dirname");
                    if($folder != "" && !file_exists($folder)) { mkdir($folder); };
                    rename("$root/$old_path", "$root/$new_path");
                    
                    $old_folder = path("$root/$old_path", "dirname");
                    if($old_folder != "" && !in_array($old_folder, $old_folders)) {
                        $old_folders[] = $old_folder;
                    };
                    $moved_count++;
                };
            }
        }
        
        if(count($old_folders) > 0) {
            foreach($old_folders as $old_folder) {
                if(file_exists($old_folder) && !listDir($old_folder)) {
                    //echo "OLD_FOLDER: $old_folder<br>\n";
                    rmdir($old_folder);
                }
            }
        }
        
        return $moved_count;
    };

    // GET & POST input
	if(is_string($xml_path = $_GET['save']) && $xml_path != "") {
		// Save
        $dir = path($xml_path, "dirname");
        if(!file_exists($dir)) { makeDir($dir); };
		if($_POST['xml'] != "" && safeSave($xml_path, $_POST['xml'])) {
            // Move files - Media location change Tool
            if($_POST["action"] == "move_files") {
                if(($moved_count = moveFiles($root)) > 0) {
                    // Done info
                    $info = localize("move-folder-done");
                    $info = str_replace("\\n", "\n", $info);
                    $info = str_replace("@count", $moved_count, $info);
                    
                    // Delete previous version (if any)
                    $prev_path = "$xml_path.prev";
                    if(file_exists($prev_path)) {
                        // Delete deleted files from previous
                        $prev_xml = loadXml($prev_path);
                        $delete_files = explode(";", readXml($prev_xml, "_file_manager delete"));
                        foreach($delete_files as $file) {
                            if(file_exists("$root/$file")) {
                                //echo "Unlink: $root/$file<br>\n";
                                unlink("$root/$file");
                            }
                        }
                        rename($prev_path, $prev_path.".bak");
                        $info = $info."\n".localize("previous-deleted");
                    }
                    $_SESSION["show_popup"] = $info;
                }
            };
        }
        else {
			$_SESSION["show_popup"] = localize("file-save-error");
			$xml_path = "";
		};

        
	}
	elseif(is_string($xml_path = $_GET['open']) && $xml_path != "" && in_array(path($xml_path, "extension"), array("xml", "draft", "template"))) {
		// Open
	}
	else {
		// New
		$xml_path = "";
	};

?>

<!doctype html>
<html>
    <?php require("modules/xable_head.php"); ?>
	<body>
        <main class='<?php echo $panel_name; ?>'>
            <?php
            
                require("modules/xable_nav.php");
            
                echo "\n<input type='hidden' id='language' value='$lang'>\n";
                echo "\n<input type='hidden' id='languages' value='".join(",", $languages)."'>\n";
				echo "\n<input type='hidden' id='xml_path' value='$xml_path'>\n";
				
				if($xml_path == "") { $label = "*".localize("new-document-label")."*"; }
				else { $label = substr($xml_path, strlen($root) + 1); }
				echo "<div id='label'><p><span class='fi-page'></span>".$label."</p></div>\n";
                
                if(is_string($xml_path) && $xml_path != "" && file_exists($xml_path)) {
                    $xml = loadXml($xml_path);
                    foreach(array_keys($xml) as $article_name) {
                        $article_group = $xml[$article_name];
                        foreach(array_keys($article_group) as $article_num) {
                            // ====== ARTICLE ======
                            $article = $article_group[$article_num];
                            $multi = "multi_";
                            if(substr($article_name, 0, strlen($multi)) == $multi) {
                                $article_tag = substr($article_name, strlen($multi));
                                $multi = "checked";
                            }
                            else {
                                $article_tag = $article_name;
                                $multi = "";
                            };
                            echo "\t<article>\n".
                                // BUTTONS -> complete with jQuery
                                "\t\t<input type='text' class='article_tag string' value='$article_tag'>\n".
                                "\t\t<label class='option'><input type='checkbox' class='article_multi checkbox' $multi><span>multi</span></label>\n";
                            foreach(array_keys($article) as $section_name) {
                                
                                $section_group = $article[$section_name];
                                foreach(array_keys($section_group) as $section_num) {
                                    // ====== SECTION ======
                                    $section = $section_group[$section_num];
                                    $type = $section['type'][0];
                                    
                                    echo "\t\t<section>\n".
                                        // BUTTONS -> complete with jQuery
                                        "\t\t\t<label class='section_tag'><span class='type'>".$type."</span><input type='text' class='section_tag string' value='$section_name'></label>\n";
                                    if($section['disabled'][0] == "true") {
                                        echo "\t\t\t<input type='hidden' class='non_editable' value='true'>\n";
                                    }
                                    else {
                                        echo "\t\t\t<input type='hidden' class='non_editable' value=''>\n";
                                    };

                                    echo "\t\t\t<form>\n".
                                        "\t\t\t\t<input type='text' class='label' value='".$section['label'][0]."' placeholder='".localize("title-label")."'>\n".
                                        "\t\t\t\t<input type='text' class='description' value='".$section['description'][0]."' placeholder='".localize("description-label")."'>\n";
                                    // ----- Types ------
                                    if($type == "button") {
                                        echo "\t\t\t\t<p>Akcja</p><input type='text' class='action' value='".$section['action'][0]."'>\n";
                                    }
                                    elseif(in_array($type, [ "date" ])) {
                                        echo "\t\t\t\t<input type='hidden' class='$type' value='".$section[$type][0]."'>\n";
                                        echo "\t\t\t\t<input type='text' class='options' value='".$section['options'][0]."' placeholder='".localize("more-options")."'>\n";
                                    }
                                    elseif(in_array($type, [ "string" ])) {
                                        $text = str_replace("'", "&#39;", $section[$type][0]);
                                        echo "\t\t\t\t<input type='hidden' class='$type' value='".$text."'>\n";
                                        if(!is_string($options = $section["options"][0])) { $options = ""; };
                                        echo "\t\t\t\t<input type='text' class='options' value='".$options."' placeholder='".localize("more-options")."'>\n";
                                    }
                                    elseif($type == "code") {
                                        $text = str_replace("'", "&#39;", $section['code'][0]);
                                        echo "\t\t\t\t<textarea class='code'>".$text."</textarea>\n";
                                    }
                                    elseif($type == "text" || $type == "textarea") {
										if($type == "textarea") { echo "\t\t\t\t<input type='hidden' class='format' value='".$section['format'][0]."'>\n"; }; // Complete with jQuery
                                        echo "\t\t\t\t<div class='text'>\n";
										foreach(array_keys($section['text'][0]) as $language) {
                                            $text = $section['text'][0][$language][0];
                                            $text = str_replace("'", "&#39;", $text);
                                            $text = str_replace("  ", "&nbsp; ", $text);
											echo "\t\t\t\t\t<textarea class='$language'>".$text."</textarea>\n";
										};
										echo "\t\t\t\t</div>\n";
                                    }
                                    elseif($type == "table") {
                                        echo "\t\t\t\t<div class='table'>\n";
										foreach(array_keys($section['table'][0]) as $language) {
                                            foreach($section['table'][0][$language] as $row) {
                                                echo "\t\t\t\t\t<textarea class='$language'>$row</textarea>\n";
                                            };
										};
										echo "\t\t\t\t</div>\n";
                                    }
                                    elseif($type == "option") {
                                        //arrayList($section);
                                        echo "\t\t\t\t<div class='option'>\n";
                                        if(is_array($section['radio'])) {
                                            $mode = "radio"; $radio = "selected"; $chbox = "";
                                        } else {
                                            $mode = "checkbox"; $radio = ""; $chbox = "selected";
                                        };
                                        echo "\t\t\t\t\t<select><option value='checkbox' $chbox>Checkbox</option><option value='radio' $radio>Radio</option></select>\n";
                                        echo "\t\t\t\t\t<ul>\n";
                                        
                                        foreach($section[$mode][0]['option'] as $option) {
                                            echo "\t\t\t\t\t\t<li><span class='fi-checkbox icon'></span><input type='text' class='label' value='$option'></li>\n";
                                        };
                                        echo "\t\t\t\t\t</ul>\n";
                                        echo "\t\t\t\t\t<input type='hidden' class='selected' value='".$section['selected'][0]."'>\n";
                                        echo "\t\t\t\t</div>\n";
                                    }
									elseif($type == "media") {
										echo "\t\t\t\t<div class='media'>\n";
										echo "\t\t\t\t\t<ul>\n";
										foreach(array_keys($section['media'][0]) as $media_type) {
											echo "\t\t\t\t\t\t<li><input type='hidden' class='$media_type' value='".$section['media'][0][$media_type][0]."'></li>\n";
										};
										echo "\t\t\t\t\t</ul>\n";
										echo "\t\t\t\t\t<input type='hidden' class='set' value='".$section['set'][0]."'>\n";
										echo "\t\t\t\t</div>\n";
										
									};
                                    // -------------------

                                    echo "\t\t\t</form>\n".
                                        "\t\t</section>\n";
                                };
                            };
                            
                            
                            echo "\t</article>\n"; // DROPDOWN -> complete with jQuery
                        };
                    };
                    
                    //arrayList($xml);
                };
            ?>

            <button class='new_article'>+</button>

            <div id='popup_container'>
                <div id='tree' class='popup'>
                    <nav>
                        <p><?php echo localize("file-browser"); ?></p>
                        <div class='buttons'>
                            <button class='cancel'><span class='fi-x'></span></button>
                        </div>
                    </nav>
                    <div id='folder'>
                        <input type='text' id='dir' value='' disabled>
                        <span id='back' class='fi-eject'></span>
                    </div>
                    <div id='list'>
                        <details class='folder enabled' open>
                            <summary path='<?php echo $root; ?>'>root</summary>
                            <?php
                                //htmlTree($root, false, "draft,xml,template", $ini_hidden);
                                htmlTree($root, false, "draft,xml,template", false);
                            ?>
                        </details>
                    </div>
                    <div id='input'>
                        <input type='text' id='file' value='' placeholder="Filename">
                        <button class='confirm'><?php echo localize("ok-label"); ?></button>
                    </div>
                </div>
            </div>
        
        </main>
        <aside>
            <form id='save' action='xable_creator.php?save=<?php echo $xml_path; ?>' method='post'>
				<textarea name='xml'></textarea>
			</form>
            <div id='code'><p></p></div>
        </aside>

        <?php
            if(isset($_SESSION["show_popup"]) && $_SESSION["show_popup"] != "") {
                echo "<input type='hidden' id='show_popup' value='".$_SESSION["show_popup"]."'>\n";
                unset($_SESSION["show_popup"]);
            };
        ?>
	</body>
</html>

    

