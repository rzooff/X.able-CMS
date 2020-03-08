<?php

    $tags = array_keys($xml);
    $first_tag = "_";
    while(count($tags) > 0 && substr($first_tag, 0, 1) == "_") {
        $first_tag = array_shift($tags);
    }
    

    echo "FIRST_TAG: $first_tag\n";

    if(isset($xml[$first_tag][0]["_blog_archive_max"])) {
        $archive_edit = readXml($xml, "$first_tag _blog_archive_edit");
        $archive_file = $root."/".array_pop(explode(":", $archive_edit));
        $archive_folder = readXml($xml, "$first_tag _blog_archive_media");
        $blog_max = readXml($xml, "$first_tag _blog_archive_max");
        $updated_flag = false;
        
        echo "max: $blog_max\nfolder: $archive_folder\nfile: $archive_file\n\n";
        
        $archive_xml = loadXml($archive_file);
        //arrayList($archive_xml);

        // ====== Check for content to archive ======
        $blog_tag = false;
        foreach(array_keys($xml) as $tag) {
            
            if(count($xml[$tag]) > $blog_max) {
                $blog_posts = $xml[$tag];
                $archive_posts = [];
                $archive_media = [];
                $n = 1;
                foreach(array_keys($blog_posts) as $post_num) {
                    if($post_num >= $blog_max) {
                        $post = $blog_posts[$post_num];

                        // Get media files to move
                        foreach(array_keys($post) as $section_name) {
                            $section = $post[$section_name];
                            if($section[0]["type"][0] == "media") {
                                foreach(array_keys($section[0]["media"][0]) as $media) {
                                    
                                    $val = $section[0]["media"][0][$media][0];
                                    $files = explode(";", $val);
                                    $file = array_shift($files);

                                    if($file != "" && !is_dir("$root/$file") && file_exists("$root/$file")) {
                                        foreach($files as $file) {
                                            
                                            $archive_media["$root/$file"] = "$root/$archive_folder/".path($file, "basename");
                                        }
                                        $val = str_replace(path($file, "dirname"), $archive_folder, $val);
                                        
                                        $post[$section_name][0]["media"][0][$media][0] = $val;
                                    }
                                }
                            }
                        };

                        // Move to post to archive xml array
                        $archive_posts[] = $post;
                        unset($blog_posts[$post_num]);

                    }
                };
                
                if(count($blog_posts) < count($xml[$tag])) {
                    $archive_posts = array_merge($archive_posts, $archive_xml[$tag]);
                    $archive_xml[$tag] = array_values($archive_posts);
                    $xml[$tag] = $blog_posts;
                    $updated_flag = true;
                };
            }
            
            
            // ====== Check for content to get back ======
            if(count($xml[$tag]) < $blog_max && count($archive_xml[$tag]) > 0) {
                //while
            }
            
        };
        
        
        // ====== Save archive & move files ======
        if($updated_flag) {
            $xml_declaration = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
            
            //echo "#save to: $archive_file\n";
            //echo "#save to: $path\n";
            /*
            if(safeSave($archive_file, "$xml_declaration\n<xable>\n".arrayToXml($archive_xml, 1)."</xable>\n") &&
               safeSave($path, "$xml_declaration\n<xable>\n".arrayToXml($xml, 1)."</xable>\n"))
            {
                
                if(!file_exists("$root/$archive_folder")) {
                    mkdir("$root/$archive_folder");
                };
                
                foreach(array_keys($archive_media) as $old_path) {
                    $new_path = $archive_media[$old_path];
                    rename($old_path, $new_path);
                    if(file_exists($new_path)) {
                        //echo "> moved: $old_path -> $new_path\n";
                    }
                    else {
                        //echo "ERRROR! not moved: $old_path -> $new_path\n";
                    };
                }
                
                echo "<script>\n";
                //echo "\talert(\"Blog archive: Done!\")\n";
                echo "\tlocation.reload();\n";
                echo "</script>\n";
                
            }
            */

            
        };

    };

                


?>