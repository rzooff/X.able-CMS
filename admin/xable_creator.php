<?php
    // ======================================
    //         ><.able CMS - CREATOR
    //        (C)2016 maciejnowak.com
    //          v.2.0 build.20161004
    // ======================================
	// compatibile: php5+

    require("modules/_session-start.php");
    $ini_hidden = loadIni("xable.ini", "hidden");

    // ====== LANGUAGE ======
    $languages = array();
    foreach($settings['multi_language'] as $lang_data) {
        //if(readXml($lang_data, "active") != "") {
            $id = readXml($lang_data, "id");
            $languages[] = $id;
        //};
    };
    $lang = "pl";
	
    // GET & POST input
	if(is_string($xml_path = $_GET['save']) && $xml_path != "") {
		// Save
        $dir = path($xml_path, "dirname");
        if(!file_exists($dir)) { makeDir($dir); };
		if($_POST['xml'] == "" || !safeSave($xml_path, $_POST['xml'])) {
			echo "<script> alert('Saving error ocured :('); </script>\n";
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
	<head>
		<meta charset="UTF-8">
		<title>X.able CMS / Creator</title>
        <link href='http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900|Inconsolata:400,700|Audiowide&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" type="text/css" href="style/xable_creator.css" />
		<link rel="stylesheet" type="text/css" href="style/foundation-icons.css" />
		
        <script src='script/jquery-3.1.0.min.js'></script>
        <script src='script/functions.js'></script>
	</head>
	<body>
        <main>
            <nav>
                <div id="menu_bar">
                    <label class='logo'>
                        <span>&gt;&lt;</span>
                    </label>
                    <label class='title menu'>
                        <p>Creator</p>
                        <ul>
							<li>Update</li>
							<li>Users</li>
							<li>Explorer</li>
							<li class='separator'><hr></li>
                            <li>Quit</li>
                        </ul>
                    </label>
                    <label class='menu'>
                        <p>File</p>
                        <ul>
                            <li>New</li>
                            <li>Open</li>
                            <li class='active'>Reload</li>
                            <li class='active'>Save</li>
                            <li>Save&nbsp;as</li>
                        </ul>

                    </label>
                    <!--
                    <label class='menu'>
                        <p>Help</p>
                        <ul>
							<li>About</li>
                            <li>Report&nbsp;an&nbsp;issue</li>
                            <li>Manual</li>
                            <li class='separator'><hr></li>
							<li>Update&nbsp;code</li>
                        </ul>
                    </label>
                    -->
                </div>
            </nav>

            <?php
                echo "\n<input type='hidden' id='language' value='$lang'>\n";
                echo "\n<input type='hidden' id='languages' value='".join(",", $languages)."'>\n";
				echo "\n<input type='hidden' id='xml_path' value='$xml_path'>\n";
				
				if($xml_path == "") { $label = "*New file*"; }
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
                                        echo "<input type='hidden' class='non_editable' value='true'>";
                                    }
                                    else {
                                        echo "<input type='hidden' class='non_editable' value=''>";
                                    };

                                    echo "\t\t\t<form>\n".
                                        "\t\t\t\t<input type='text' class='label' value='".$section['label'][0]."' placeholder='Label'>\n".
                                        "\t\t\t\t<input type='text' class='description' value='".$section['description'][0]."' placeholder='Description'>\n";
                                    // ----- Types ------
                                    if($type == "string") {
                                        echo "\t\t\t\t<input type='hidden' class='string' value='".$section['string'][0]."'>";
                                    }
                                    elseif($type == "text" || $type == "textarea") {
										if($type == "textarea") { echo "\t\t\t\t<input type='hidden' class='format' value='".$section['format'][0]."'>\n"; }; // Complete with jQuery
                                        echo "\t\t\t\t<div class='text'>";
										foreach(array_keys($section['text'][0]) as $language) {
											echo "<textarea class='$language'>".$section['text'][0][$language][0]."</textarea>";
                                            
										};
										echo "</div>\n";
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
                        <p>Browser</p>
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
                            <?php htmlTree($root, false, "draft,xml,template", $ini_hidden); ?>
                        </details>
                    </div>
                    <div id='input'>
                        <input type='text' id='file' value='' placeholder="Filename">
                        <button class='confirm'>OK</button>
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

        <script src='script/xable_creator.js'></script>
        
	</body>
</html>

    

