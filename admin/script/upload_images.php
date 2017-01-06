<?php

    // ====== Image Resize / begin ======
    function imageResize($in_file, $out_file, $mode) {
    // -----------------------------------------------
    // $in_file = <string> full INput jpg FILE path
    // $out_file = <string> full OUTput jpg FILE path
    // $mode = <string> function mode:
    //      "1200" = scale to match longer side size
    //      "200x300" = scale to exact size (stretch)
    //      "width:300" = scale to match width
    //      "height:300" = scale to match height
    //      "max:200" = scale to not exceed maximal longer side size
	//		"max:200x300" = scale to not exceed maximal format size
    //      "50%", "scale:50%", "scale:0.5" = scale image proportionaly
	//		"crop: 100x200" = scale to exact size & crop to destination proportions (centered)
    // -----------------------------------------------
    // RETURNS: <boolean> 'true' for resized image save success or not necessary, 'false' if resize or saving failed.
    // -----------------------------------------------
        list($wid, $hei) = getimagesize($in_file);
        $ratio = $wid / $hei; // r = w/h, w = r*h, h = w/r
		$c_x = 0;
		$c_y = 0;
        // ====== take care about mode ======
        $mode = trim($mode);
        if((substr($mode, (strlen($mode) - 1), 1) == "%") && is_numeric(substr($mode), 0, (strlen($mode) - 1))) { $mode = "scale:".$mode; }; // "50%" -> "scale:50%"
        // ====== find new width & height ======
        if(!file_exists($in_file)) { echo "#ERROR! imageResize() file not found!<br>"; }
        elseif($mode == "") { echo "#ERROR! imageResize() no mode specified!<br>"; }
        elseif(is_numeric($mode)) {
            // ====== "<longer side>" ======
            if($wid >= $hei) {
                $n_wid = $mode;
                $n_hei = round($n_wid / $ratio);
            }
            else {
                $n_hei = $mode;
                $n_wid = round($n_hei * $ratio);
            };
        }
        elseif((count($dat = explode("x", $mode)) == 2) && is_numeric($dat[0]) && is_numeric($dat[1])) {
            // ====== "<width>x<height>" ======
            list($n_wid, $n_hei) = $dat;
        }
        elseif(count($dat = explode(":", $mode)) != 2) { echo "#ERROR! imageResize() invalid mode format!<br>"; }
		elseif($dat[0] == "crop") {
			if((count($crop = explode("x", $dat[1])) == 2) && is_numeric($crop[0]) && is_numeric($crop[1])) {
				// ====== "crop:<width>x<height>" ======
				list($n_wid, $n_hei) = $crop;
				$n_ratio = $n_wid / $n_hei;
				if($ratio > $n_ratio) { // by height
					$c_hei = $hei;
					$c_wid = round($c_hei * $n_ratio);
					$c_x = round(($wid - $c_wid) / 2.0);
					$c_y = 0;
				}
				else { // by width
					$c_wid = $wid;
					$c_hei = round($c_wid / $n_ratio);
					$c_x = 0;
					$c_y = round(($hei - $c_hei) / 2.0);
				};
				$wid = $c_wid;
				$hei = $c_hei;
			};
		}
        elseif($dat[0] == "scale") {
            // ====== "scale:<scale> or <scale in precentage>%" ======
            if(is_numeric($dat[1])) {
				$scale = $dat[1];
			}
            elseif((substr($dat[1], (strlen($dat[1]) - 1), 1) == "%") && is_numeric($scale = substr($dat[1], 0, 1))) {
                $scale = $dat[1] / 100.0;
            };
            if($scale != 1.0) {
                $n_wid = $wid;
                $n_hei = $hei;
                $n_wid = round($wid * $scale);
                $n_hei = round($hei * $scale);
            };
        }
        elseif($dat[0] == "max") {
			if(is_numeric($dat[1])) {
				// ====== "max:<maximal longer side>" ======
				if(($wid >= $hei) && ($wid > $dat[1])) {
					$n_wid = $dat[1];
					$n_hei = round($n_wid / $ratio);
				}
				elseif(($hei > $wid) && ($hei > $dat[1])) {
					$n_hei = $dat[1];
					$n_wid = round($n_hei * $ratio);
				}
                else {
                    $n_wid = $wid;
                    $n_hei = $hei;
                };
			}
			elseif((count($max = split("x", $dat[1])) == 2) && is_numeric($max[0]) && is_numeric($max[1])) {
				// ====== "max:<width>x<height>" ======
				list($n_wid, $n_hei) = $max;
				$n_ratio = $n_wid / $n_hei; // r = w/h, w = r*h, h = w/r
				if(($n_ratio > $ratio) && ($hei > $n_hei)) {
					$n_wid = round($n_hei * $ratio);
				}
				elseif(($n_ratio < $ratio) && ($wid > $n_wid)) {
					$n_hei = round($n_wid / $ratio);
				}
                else {
                    $n_wid = $wid;
                    $n_hei = $hei;
                };
			};
        }
        elseif(!is_numeric($dat[1])) { echo "#ERROR! imageResize() non numeric value!<br>"; }
        elseif($dat[0] == "width") {
            // ====== "width:<width>" ======
            $n_wid = $dat[1];
            $n_hei = round($n_wid / $ratio);
        }
        elseif($dat[0] == "height") {
            // ====== "height:<height>" ======
            $n_hei = $dat[1];
            $n_wid = round($n_hei * $ratio);
        }
        else { echo "#ERROR! imageResize() unknown mode!<br>"; };
        // =================================
        //        Resize & save image
        // =================================
        if($n_wid && $n_hei) {
            $ext = pathinfo($in_file);
            $ext = strtolower($ext['extension']);
            if(in_array($ext, array("jpg", "jpeg"))) {
                $image = imagecreatetruecolor($n_wid, $n_hei);
                $in_file = imagecreatefromjpeg($in_file);
                imagecopyresized($image, $in_file, 0, 0, $c_x, $c_y, $n_wid, $n_hei, $wid, $hei);
                return (imagejpeg($image, $out_file));
            }
            elseif($ext == "png") {
                $image = imagecreatetruecolor($n_wid, $n_hei);
                $in_file = imagecreatefrompng($in_file);
                imagealphablending( $image, false );
                imagesavealpha( $image, true );
                imagecopyresized($image, $in_file, 0, 0, $c_x, $c_y, $n_wid, $n_hei, $wid, $hei);
                
                return (imagepng($image, $out_file, 9));
            }
            elseif($ext == "gif") {
                $image = imagecreatetruecolor($n_wid, $n_hei);
                $in_file = imagecreatefromgif($in_file);
                imagealphablending( $out_file, false );
                imagesavealpha( $out_file, true );
                imagecopyresized($image, $in_file, 0, 0, $c_x, $c_y, $n_wid, $n_hei, $wid, $hei);
                return (imagegif($image, $out_file));
            }
        }
        else {
            return false;
        };
    }; // ====== Image Resize / end ======
	
    

    // ====== image Orientation Fix / begin ======
	function orientationFix($file) { // jpeg only for now
	// ========================================================
	// $file = <string> jpg FILE full path
	// ========================================================
	// Rotates image according to it's exif orientation
	// ========================================================
        $info = pathinfo($info);
        if(in_array(strtolower($info['extension']), array( "jpg", "jpeg" ))) {
            $image = imagecreatefromjpeg($file);
            $exif = exif_read_data($file);
            switch ($exif['Orientation']) {
                case 3:
                    $image = imagerotate($image, 180, 0);
                    break;
                case 6:
                    $image = imagerotate($image, -90, 0);
                    break;
                case 8:
                    $image = imagerotate($image, 90, 0);
                    break;
                default: // no orientation change needed
                    imagedestroy($image);
                    $image = false;
            }
            if($image != false) {
                imagejpeg($image, $file);
                imagedestroy($image);
            };
        };
	}; // ====== image Orientation Fix / end ======

    // ====== Upload image(s) / begin ======
	function uploadImages($key, $folder, $resize) {
	// -----------------------------------------------
	// $key = <string> uploaded $_FILES (form) valid key name
	// $folder = <string> destination FOLDER path
	// $resize = <string> optional resize mode -> see imageResize() doc.
	// -----------------------------------------------
	// Save uploaded images to specified path & optionaly resize it.
    // RETURNS: <array> successfully uploaded files
	// -----------------------------------------------
        if(($file = $_FILES[$key]) != false && file_exists($folder)) {
            $names = $file['name'];
            $temps = $file['tmp_name'];
            if(!is_array($names)) {
                $names = array($names);
                $temps = array($temps);
            };
            for($n = 0; $n < count($names); ++$n) {
                $image = $names[$n];
                if(move_uploaded_file($temps[$n], "$folder/$image") && file_exists("$folder/$image")) {
                    $info = pathinfo($image);
                    if($resize != false && $resize != "" && in_array(strtolower($info['extension']), array( "gif", "jpg", "jpeg", "png" ))) {
                        imageResize("$folder/$image", "$folder/$image", $resize);
                    }
                    orientationFix("$folder/$image"); // rotate to exif orientation (if needed)
                    $output[] = $image;
                }
                else {
                    echo "uploadImages(): ERROR! File upload failed!<br>";
                    return false;
                };
            };
            return $output;
        }
        else {
            echo "<b>uploadImages(): ERROR! Invalid input variables!</b><br>";
            echo "[key:] '$key', [folder:] '$folder', [resize:] '$resize'<br>";
            return false;
        };
    }; // ====== Upload image(s) / end ======

    // ====== Upload image(s) / begin ======
	function uploadRenameImages($key, $folder, $resize, $rename) {
	// -----------------------------------------------
	// $key = <string> uploaded $_FILES (form) valid key name
	// $folder = <string> destination FOLDER path, existing!
	// $resize = <string> optional resize mode -> see imageResize() doc.
    // $rename = <string> rename options:
    //      "force:file_name": image will be saved as file_name* & overwrite existing,
    //      "name:file_name": image will be saved as file_name*, number will be added if file exists,
    //      false: image will be save with it's original name & overwrite existing,
    //      "auto": image will be saved with original name, number will be added if file exists,
    //      "date": image will be saved with date filename, number will be added if file exists.
    //      * do not include extension in file_name!
    //      * spaces will be replaced with "_"!
	// -----------------------------------------------
	// Save uploaded images to specified path & optionaly resize it.
    // RETURNS: <array> successfully uploaded files
	// -----------------------------------------------
        if(is_string($rename)) { $rename = split(":", $rename); };
        if(($file = $_FILES[$key]) != false && file_exists($folder)) {
            $names = $file['name'];
            $temps = $file['tmp_name'];
            if(!is_array($names)) {
                $names = array($names);
                $temps = array($temps);
            };
            for($n = 0; $n < count($names); ++$n) {
                $image = $names[$n];
                // ====== RENAME ======
                if(is_array($rename)) {
                    if($rename[0] == "date") { $rename[1] = date("Ymd_Hi"); };
                    if($rename[0] == "auto") {
                        $image = str_replace(" ", "_", $image);
                    }
                    elseif(is_string($rename[1])) {
                        $force_rename = split(";", $rename[1]);
                        
                        
                        $info = pathinfo($image);
                        $ext = $info['extension'];
                        $image = $force_rename[$n].".".$ext;
                    };
                    if((in_array($rename[0], array( "name", "auto", "date" ))) && (file_exists("$folder/$image"))) {
                        $num = 2;
                        $info = pathinfo($image);
                        $ext = $info['extension'];
                        $fname = $info['filename'];
                        while(file_exists("$folder/$fname"."_"."$num.$ext")) { $num++; };
                        $image = "$fname"."_"."$num.$ext";
                    };
                }
                // ====== UPLOAD ======
                if(move_uploaded_file($temps[$n], "$folder/$image") && file_exists("$folder/$image")) {
                    $info = pathinfo($image);
                    if($resize != false && $resize != "" && in_array(strtolower($info['extension']), array( "jpg", "jpeg", "png" ))) {
                        imageResize("$folder/$image", "$folder/$image", $resize);
                    }
                    orientationFix("$folder/$image"); // rotate to exif orientation (if needed)
                    $output[] = $image;
                }
                else {
                    echo "uploadImages(): ERROR! File upload failed!<br>";
                    return false;
                };
            };
            return $output;
        }
        else {
            echo "<b>uploadImages(): ERROR! Invalid input variables!</b><br>";
            echo "[key:] '$key', [folder:] '$folder', [resize:] '$resize'<br>";
            return false;
        };
    }; // ====== Upload image(s) / end ======
                                             
?>