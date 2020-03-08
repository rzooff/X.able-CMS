<?php
    error_reporting(E_ERROR | E_PARSE); // Disable warnings output

// ===================================================
//               X.able PHP FUNCTIONS
//           (C)2018 maciej@maciejnowak.com
// ===================================================
// compatibility: php6+
// build: 20200224
// ===================================================

// ===================================================
//                      ARRAYS
// ===================================================

    // ====== RESET array KEYS / begin ======
    function resetKeys($array) {
    // -----------------------------------------------
    // $array = <array> simple array with unsorted integer keys
    // -----------------------------------------------
    // RETURNS: <array> input array with keys sorted ascending from 0
    // -----------------------------------------------
        $output = [];
        foreach($array as $element) {
            $output[] = $element;
        };
        return $output;
    };
    // ====== RESET array KEYS / end ======

// ===================================================
//                    The Mailer
// ===================================================

	// ====== Mailer / begin ======
	function mailer($to, $from, $subject, $message, $files = false) {
    // ----------------------------------------
    // $to = <string> recipient's email address:
    //  - for multiple recipient, divide addresses with ";" or ",".
    //  - for Cc or Bcc use "/" to divide sddresses sections: "<To>/<Cc>/<Bcc>", eg. for Bcc only: "//<Bcc>".
    // $from = <string> sender's email address.
    // $subject = <string> email subject.
    // $message = <string> message in plain or html format.
    // $file = <string> Attachement file path or <false> for no attachement:
    //  - for multiple files, divide pathes with ";".
    // ----------------------------------------
    // Send email, optionally with attachement
    // ----------------------------------------
        $message = stripcslashes($message); // fix for quotation
        $flag = true; // cancel if error flag
        $to = explode("/", $to); // split for To/Cc/Bcc
        $separator = md5(time()); // a random hash will be necessary to send mixed content
        $eol = PHP_EOL; // carriage return type (we use a PHP end of line constant)
        // main header (multipart mandatory)
        $headers = "From: ".$from.$eol;
        if($to[1] != false) { $headers .= "Cc: ".$to[1].$eol; }; // Cc
        if($to[2] != false) { $headers .= "Bcc: ".$to[2].$eol; }; // Bcc
        $headers .= "MIME-Version: 1.0".$eol;
        $headers .= "Content-Type: multipart/mixed; boundary=\"".$separator."\"".$eol.$eol;
        $headers .= "Content-Transfer-Encoding: 7bit".$eol;
        $headers .= "This is a MIME encoded message.".$eol.$eol;
        // message
        // Automatic check for xml formating -> format: html, otherwise: plain
        if(substr(trim($message), 0, 1) == "<" && substr(trim($message), (strlen(trim($message)) - 1), 1) == ">") { $format = "html"; } else { $format = "plain"; };
        $headers .= "--".$separator.$eol;
        $headers .= "Content-Type: text/".$format."; charset=\"utf-8\"".$eol;
        $headers .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
        $headers .= $message.$eol.$eol;
        // Attahcements
        if($files != false) {
			if(substr($files, 0, 1) == ";") { $files = substr($files, 1, (strlen($files) - 1)); };
			if(substr($files, (strlen($files) - 1), 1) == ";") { $files = substr($files, 0, (strlen($files) - 1)); };
            $files = explode(";", $files); // split file pathes
            foreach($files as $file) {
                unset($content);
                if(file_exists($file)) {
                    $filename = pathinfo($file);
                    $filename = $filename['basename'];
                    $file_size = filesize($file);
                    $handle = fopen($file, "r");
                    $content = fread($handle, $file_size);
                    fclose($handle);
                    $content = chunk_explode(base64_encode($content));
                    // Add file
                    $headers .= "--".$separator.$eol;
                    $headers .= "Content-Type: application/octet-stream; name=\"".$filename."\"".$eol;
                    $headers .= "Content-Transfer-Encoding: base64".$eol;
                    $headers .= "Content-Disposition: attachment".$eol.$eol;
                    $headers .= $content.$eol.$eol;
                }
                else {
                    echo "* ERROR! Attachement file not found: '$file'<br>";
                    $flag = false;
                };
            };
        };
		$headers .= "--".$separator."--";
        //SEND Mail
        if ($flag == true && mail($to[0], $subject, "", $headers, "-f ".$from)) { // Updated -> need to be tested
            $log = "@sent";
            $ext = "eml";
            $time = date("Y.m.d G:i:s");
            $recipients = "";
            if($to[0] != "") { $recipients = $recipients. " [To:] ".$to[0]; };
            if($to[1] != "") { $recipients = $recipients. " [Cc:] ".$to[1]; };
            if($to[2] != "") { $recipients = $recipients. " [Bcc:] ".$to[2]; };
            $recipients = trim($recipients);
            $file = "$time $recipients | $subject.$ext";
            if($files != false) { $attachements = "(".count($files)."): ".join(", ", $files); } else { $attachements = ": none"; };
            file_put_contents("$log/$file", "$time\n$recipients\n[From:] $from\n[Subject:] $subject\nAttachements $attachements\n-\n$message");
            return [ $to, $from, $subject, $message, $files ];
        }
        else {
            return false;
        };
	}; // ====== Mailer / end ======

    // ====== sendMail / begin ======
    function sendMail($to, $from, $subject, $message) {
    // ----------------------------------------
    // $to = <string> Recipient email adress
    // $from = <string> Sender email adress
    // $subject = <string> Email subject
    // $message = <string> Email message
    // ----------------------------------------
    // Send email & RETURN: <true> if sent or <false> if failed to send
    // ----------------------------------------
        $header = "";
        $header .= "Content-type: text/html; charset=utf-8\r\n";
        $header .= "Content-Transfer-Encodin: 8bitr\n";
        if(mail($to, $subject, $message, $header."Reply-to: ".$from, "-f ".$from)) {
            return true;
        }
        else {
            return false;
        }
    };
    // ====== sendMail / end ======

// ===================================================
//                   FILES & FOLDERS
// ===================================================

    // ====== clear duplicated Filename time stamp / begin ======
    function clearFilename($path, $no_extension = false) {
    // ----------------------------------------
    // $path = <string> file path
    // $no_extension = <boolean> not include the extension flag
    // ----------------------------------------
    // RETURNS: <string> Filename without the time stamp added by xable cms (on duplicated filenames)
    // ----------------------------------------
        if($no_extension) { $ext = ""; }
        else {  $ext = ".".path($path, "extension"); };
        $filename = path($path, "filename");
        if(is_string($time = array_pop(explode("_", $filename))) &&
           count($time = explode("-", $time)) == 2 &&
           strlen($time[0]) == 8 &&
           strlen($time[1]) == 6 &&
           is_numeric($time[0]) &&
           is_numeric($time[1])
          ) {
            $filename = substr($filename, 0, strlen($filename) - 16);
            //echo "fix: $filename<br>";
        };
        return $filename.$ext;
    };
    // ====== clear duplicated Filename time stamp / end ======

    // ====== copy DIRectory content / begin ======
    function copyDir($source, $destination) {
    // ----------------------------------------
    //$source = <string> Source folder path
    //$destination = <string> Destination folder in existing path
    // ----------------------------------------
    //Copy all speficied to folder content to destination, including subdirectories tree & content
    // RETRUNS: <boolean> Copy success status
    // ----------------------------------------
        if(!file_exists($destination)) { mkdir($destination); };
        if(file_exists($source) && file_exists($destination)) {
            foreach(filesTree($source) as $path) {
                if(!is_dir($path)) {
                    $relative_path = $destination."/".substr($path, strlen($source) + 1);
                    $relative_dir = path($relative_path, "dirname");
                    if(!file_exists($relative_dir)) { makeDir($relative_dir); };
                    copy($path, $relative_path);
                };
            };
            return file_exists($relative_path);
        }
        else {
            return false;
        };
    };
    // ====== copy DIRectory content / end ======

	// ====== LIST DIRectory / begin ======
	function listDir($path, $options = false) {
	// -----------------------------------------------
	// $path = <string> directory path, <false> for current
    // $options = <string> options, for multiple use coma, eg:"/,jpg"
    //      <none> or "*"=include all (files & folders),
    //      "." = include files,
    //      "/" = include folders,
    //      "<extension>" = include specified file type (case insesitive), for multiple use coma, eg: "jpg,gif"
    //      "?" = return full path
	// -----------------------------------------------
    // RETURNS: <array> files/folders list
	// -----------------------------------------------
        if($path == false || $path == "") {
            $path = getcwd();
            $current = true;
        }
        else {
            $current = false;
        };
        if(file_exists($path)) {
            $files_array = [];
            if ($options == "?") { $options = [ "?", "*" ]; }
            elseif ($options != false ) { $options = explode(",", strtolower($options)); }
            else { $options = [ "*" ]; };
            $dir = opendir($path);
            while(false !== ($file = readdir($dir))) {
                $ext = pathinfo($file);
                $ext = strtolower($ext['extension']);
                if($file != "." && $file != ".." && $file != "" && ( // Ignore
                        in_array("*", $options) || // Any
                        (in_array(".", $options) && !is_dir("$path/$file")) || // File
                        (in_array("/", $options) && is_dir("$path/$file")) || // Folder
                        in_array($ext, $options) // Matched extension
                )) {
                    if($current != true && in_array("?", $options)) { $file = "$path/$file"; };
                    $files_array[] = $file;
                };
            };
            natcasesort($files_array);
            return resetKeys( $files_array );
        }
        else {
            return false;
        };
	}; // ====== LIST DIRectory / end ======
	
	// ====== list all DIRectories TREE / begin ======
	function dirTree($path, $ignore = false) {
    // -----------------------------------------------
	// $path = <string> directory path
	// $ignore = <string> path or <array> pathes to ignore
    // -----------------------------------------------
    // RETURNS:
    // <array> folders list (with patches) including it's subfolders
    // -----------------------------------------------
		$flag = true;
		if(is_string($ignore)) { $ignore = [ $ignore ]; }
        elseif(!$ignore) { $ignore = []; };
        $folders = [];
        //$folders = listDir($path, "/,?");
        foreach(listDir($path, "/,?") as $folder) {
            if(!in_array($folder, $ignore)) {
                $folders[] = $folder;
                $sub_folders = dirTree($folder, $ignore);
                if(count($sub_folders) > 0) {
                    $folders = array_merge($folders, $sub_folders);
                };
            };
        };
        natcasesort($folders);
        return resetKeys( $folders );
	}; // ====== list all DIRectories TREE / end ======
	
	// ====== list all FILES TREE / begin ======
	function filesTree ($path, $options = false, $ignore = false) {
    // -----------------------------------------------
	// $path = <string> directory path
    // $options = <string> options, for multiple use coma, eg:"/,jpg"
    //      <none> or "*"=include all (files & folders),
    //      "." = include any files,
    //      "extension" = include specified file type (case insesitive), for multiple use coma, eg: "jpg,gif"
	// $ignore = <string> path or <array> pathes to ignore
    // -----------------------------------------------
    // RETURNS:
    // <array> files/folders list (with patches) including it's subfolders
    // -----------------------------------------------
		$flag = true;
		if(is_string($ignore)) { $ignore = [ $ignore ]; };
		if(is_array($ignore)) {
			foreach($ignore as $ommit) {
				if(substr($path, 0, strlen($ommit)) == $ommit) { $flag = false; };
			};
		};
		if($flag == true) {
			if($options == false) { $options = "?"; }
			elseif (!strstr($options, "?")) { $options .= ",?"; };
			$files = listDir($path, $options);
			foreach((listDir($path, "/,?")) as $folder) {
				$sub_files = filesTree($folder, $options, $ignore);
				if(count($files) > 0 && count($sub_files) > 0) {
					$files = array_merge($files, $sub_files);
				}
				elseif(count($sub_files) > 0) {
					$files = $sub_files;
				};
			};
			return $files;
		};
	}; // ====== list all FILES TREE / end ======
	
    // ====== MAP files & directories TREE to array / begin ======
    function mapTree($path, $options = false, $ignore = false) {
    // -----------------------------------------------
	// $path = <string> directory path
    // $options = <string> files extension filter eg: "jpg", "jpg,gif"
	// $ignore = <string> path or <array> pathes to ignore
    // -----------------------------------------------
    // RETURNS:
    // <array> files and folder array map
    // -----------------------------------------------
        if($path == "") { $path = getcwd(); };
        if(is_string($ignore)) { $ignore = [ $ignore ]; };
        if(!is_array($ignore)) { $ignore = []; };
        if(!is_string($options) || $options == "") { $options = "."; };
        $tree = listDir($path, $options);
        foreach(listDir($path, "/") as $folder) {
            if(!in_array($folder, $ignore)) {
                $branch = mapTree("$path/$folder", $options, $ignore);
                if(!is_array($branch)) { $branch = []; }; // empty folder
                $tree[$folder] = $branch;
            };
        }; 
        return $tree;
    };
    // ====== MAP files & directories TREE to array / end ======
	
	// ====== build HTML files/folders TREE / begin ======
	function htmlTree($path, $sortBy = false, $filter = false, $disabled = false) {
	// -----------------------------------------------
	// $path = <string> base directory PATH
	// $sortBy = <string> (optional) SORT BY option: "name", "kind", "size", "modified"
	// $filter = <string> (optional) file extension(s) FILTER, eg: "jpg", "jpg,gif"
	// $disabled = <string> or <array> (optional) disable specified folders, eg: "folder", [ "folder_1", "folder_2" ]
	// -----------------------------------------------
	// Builds HTML directories & content tree
	// -----------------------------------------------

		// /* ================================== */
		// /*         Basic htmlTree CSS         */
		// /* ================================== */
		// details, p { padding-left: 20px; }				/* Level indent */
		// .enabled { cursor: pointer; }					/* Enabled folders */
		// .disabled { color: #ff0000; cursor: no-drop; }	/* Disabled folders */
		// summary { font-weight: bold; }					/* All folders */
		// .valid { cursor: default; }						/* Valid files */
		// .filtered { opacity: 0.33; cursor: no-drop; }	/* Filtered out files */
		// summary:focus { outline: none; }					/* Focus frame off */

		// ====== Check Variables ======
		$currentDir= getcwd();
		// Fix base root
		if($path == "") { $path = $currentDir; };
		// Check sortBy option
		if($sortBy == "name" || $sortBy == "filename") { $sortBy = "basename"; };
		if($sortBy == "kind" || $sortBy == "type") { $sortBy = "extension"; };
		$sortKeys = [ "basename", "extension", "modified", "size" ];
		if(is_string($sortBy)) { $sortBy = strtolower($sortBy); };
		if(!in_array($sortBy, $sortKeys)) { $sortBy = "basename"; };
		// Check filetypes filter
		if(is_string($filter) && strlen($filter) > 1) { $filter = explode(",", $filter); }
		elseif(!is_array($filter)) { $filter = []; };
		// Check disabled
		if(is_string($disabled)) { $disabled = [ $disabled ]; }
		elseif(!is_array($disabled)) { $disabled = []; };
		// ====== Sort directory content ======
		$dir_content = [];
		foreach(listDir($path, "?") as $item) {
			$sortData = [];
			foreach($sortKeys as $key) { $sortData[$key] = path($item, $key); };
			if(is_dir($item)) { $sortData['extension'] = "*folder*"; };
			$sortData = path($item, $sortBy)."/".join("/", $sortData); // "sortBy/name/kind/modified/size"
			$dir_content[$sortData] = $item;
		};
		$dir_sorted = [];
		$dir_keys = array_keys($dir_content);
		natcasesort($dir_keys);
		// ====== Build tree ======
		foreach($dir_keys as $key) {
			$itemPath = $dir_content[$key];
			// Get rid off getcwd() data from path if needed
			if(substr($itemPath, 0, strlen($currentDir)) == $currentDir) {
				$itemPath = substr($itemPath, strlen($currentDir) + 1);
			};
			// Output
			list($itemSort, $itemName, $itemKind, $itemModified, $itemSize) = explode("/", $key);
            if(substr($itemName, 0, 1) == ".") { $hidden = "hidden"; } else { $hidden = ""; };
			if($itemKind == "*folder*") {
				$itemSize = count(listDir($itemPath));
				// FOLDER
				if(!in_array($itemPath, $disabled)) {
					echo "<details class='enabled $hidden'>\n";
					echo "<summary class='folder $hidden' path='$itemPath'><span class='name'>$itemName</span><span class='size'>$itemSize</span><span class='modified'>$itemModified</span></summary>\n";
					htmlTree($itemPath, $sortBy, $filter, $disabled); // RECURENCE
					echo "</details>\n";
				}
				else {
					echo "<details class='disabled $hidden'>\n";
					echo "<summary class='folder disabled' path='$itemPath'><span class='name'>$itemName</span></summary>\n";
					echo "</details>\n";
				};
			}
			else {
				// FILE
				if(count($filter) == 0 || in_array($itemKind, $filter)) {
					echo "<p class='file valid type_$itemKind $hidden' path='$itemPath'><span class='name'>$itemName</span><span class='size'>$itemSize kB</span><span class='modified'>$itemModified</span></p>\n";
				}
				else {
					echo "<p class='file filtered type_$itemKind $hidden' path='$itemPath'><span class='name'>$itemName</span><span class='size'>$itemSize kB</span><span class='modified'>$itemModified</span></p>\n";
				};
			};
		};
	};
	// ====== build HTML files/folders TREE / end ======

	// ====== PATH info short / begin ======
	function path($path, $key) {
    // ----------------------------------------
	// $path = <string> full file PATH
	// $key = <string> output data key: "basename", "dirname", "extension", "filename", "modified", "size"
    // ----------------------------------------
	// RETURNS: <string> specified content extracted from input path
    // ----------------------------------------
		$info = pathinfo($path); // basic info can be done on non existing file too
        if($info['dirname'] == ".") { $info['dirname'] = ""; }; // fix for no directory
		if(file_exists($path)) {  // only if file exists
			if(!is_dir($path)) { $info['size'] = round(filesize($path) / 1024, 1); }
			else { $info['size'] = "0"; };
			$info['modified'] = date("Y-m-d H:i:s", filemtime($path));
		};
		return $info[$key];
	}; // ====== PATH info short / end ======

    // ====== make Directory / begin ======
    function makeDir($path) {
    // ----------------------------------------
    // $path = <string> full directory PATH
    // ----------------------------------------
    // Create full folder path
    // ----------------------------------------
        if(file_exists($path)) {
            return $path; // already exists
        }
        else {
            $dir = [];
            foreach(explode("/", $path) as $folder) {
                $dir[] = $folder;
                if(!file_exists(join("/", $dir))) { mkdir(join("/", $dir)); };
            };
            if(file_exists($path)) { return $path; }
            else { return false; };
        };
    };
    // ====== make Directory / end ======

    // ====== remove Directory / begin ======
    function removeDir($path) {
    // ----------------------------------------
    // $path = <string> full directory PATH
    // ----------------------------------------
    // Delete folder with it's content
    // ----------------------------------------
        if($path != "" && !path != "/") { // safety protection!
            if(!file_exists($path)) {
                return true; // nothing to remove
            }
            else {
                foreach(array_reverse(filesTree($path)) as $item) {
                    if(is_dir($item)) { rmdir($item); } // folder
                    else { unlink($item); }; // file
                };
                rmdir($path); // main folder
                if(!file_exists($path)) { return $path; }
                else { return false; };
            };
        }
        else {
            echo "\n<b>ERROR! removeDir() -> root directory remove forbidden!</b><br>";
            return false;
        };
    };
    // ====== remove Directory / end ======

    // ====== safe Save / begin ======
    function safeSave($path, $content) {
    // ----------------------------------------
    // $path = <string> file path
    // $content = <string> file content
    // ----------------------------------------
    // Makes a backup (.bak) if file exists & saves file content to specifed path
    // RETURNS: <boolean> saving success status
    // ----------------------------------------
        $temp = "$path.temp";
        $bak = "$path.bak";
        if(file_exists($temp)) { unlink($temp); };
        file_put_contents($temp, $content);
        if(file_exists($temp)) {
            if(file_exists($bak)) { unlink($bak); };
            if(file_exists($path)) { rename($path, $bak); };
            rename($temp, $path);
            if(file_exists($path)) {
                return true;
            }
            else {
                return false; // safe save error
            };
        }
        else {
            return false; // base save error
        };
    };
    // ====== safe Save / end ======

    // ====== safe Delete / begin ======
    function safeDelete($path) {
    // ----------------------------------------
    // $path = <string> file path
    // ----------------------------------------
    // Renames file to a backup (.bak)
    // RETURNS: <boolean> saving success status
    // ----------------------------------------
        $bak = "$path.bak";
        if(file_exists($path)) {
            rename($path, $bak);
            if(file_exists($bak) && !file_exists($path)) {
                return true;
            }
            else {
                return false; // rename problem
            };
        }
        else {
            return false; // file not found
        };
    };
    // ====== safe Delete / end ======

    // ====== unique Filename / begin ======
    function uniqueFilename($filepath) {
    // ----------------------------------------
    // $filepath = <string> File path
    // ----------------------------------------
    // RETURNS: <string> Unique filename path (with added nuber if needed)
    // ----------------------------------------
        $num = 1;
        $dirname = path($filepath, "dirname");
        if($dirname != "") { $dirname = $dirname."/"; };
        $filename = path($filepath, "filename");
        $extension = path($filepath, "extension");
        if($extension != "") { $extension = ".".$extension; };
        while(file_exists($filepath)) {
            $filepath = $dirname.$filename." (".$num++.")".$extension;
        }
        return $filepath;
    };
    // ====== unique Filename / end ======

// ===================================================
//                        ZIP
// ===================================================

	// ====== archive files into zip / begin ======
	function archiveFiles($zip_path, $files, $exclude = false) {
		// -----------------------------------------------
		// $zip_path = <string> Zip file path
		// $files = <array> files to zip pathes or <string> for single file zip
        // $exclude = <array> optional - exclude specified files by extension, eg: [ "bak", "zip" ]
		// -----------------------------------------------
		// SAVES specified files into zip archive
		// -----------------------------------------------
		//echo "ZIP FILE: $zip_path<br>\n";
		if(is_string($files)) { $files = [ $files ]; };
		if(is_array($files)) {
			if(file_exists($zip_path)) { unlink($zip_path); }; // Overwrite existing!
			$zip = new ZipArchive;
			if($zip->open($zip_path, ZipArchive::CREATE)) {
				foreach($files as $file) {
                    if(file_exists($file) && !is_dir($file)) { // ommit folders patches if any
                        if(!is_array($exclude) || count($exclude) == 0 || !in_array(path($file, "extension"), $excllude)) {
                            // fix up folder root
                            if(substr($file, 0, 3) == "../") {
                                $zip_path = substr($file, 3);
                            }
                            else {
                                $zip_path = $file;
                            };
                            // fix .name files & folders --> will be ranemed to _name
                            $zip_path = explode("/", $zip_path);
                            foreach(array_keys($zip_path) as $i) {
                                $part = $zip_path[$i];
                                if(substr($part, 0, 1) == ".") {
                                    $zip_path[$i] = "_".substr($part, 1);
                                };
                            };
                            $zip_path = join("/", $zip_path);
                            // Add to zip
                            //echo "ZIP: $file -> $zip_path<br>\n";
                            $zip->addFile($file, $zip_path);
                        };
                    };
                };
				$zip->close();
				return $zip_path;
			}
			else {
				echo "Zip saving error!<br>";
			};
		}
		else {
			echo "zipFiles() input variable(s) error!<br>";
		};
	}; // ====== archive files into zip / end ======

    // ====== extract zip archive / begin ======
    function extractArchive($zip_path, $destination) {
    // -----------------------------------------------
    // $zip_path = <string> ZIP file PATH
    // $destination = <string> destination directory path
    // -----------------------------------------------
    // Extract ZIP archive to specified location
    // RETURN: <boolean> Unzip success
    // -----------------------------------------------
        if($destination == "") { $destination = getcwd(); };
        if(file_exists($zip_path) && is_string($destination)) {
            $zip = new ZipArchive;
            if ($zip->open($zip_path) == true) {
                $zip->extractTo($destination);
                $zip->close();
                return true;
            } else {
                return false;
            };
        }
        else {
            return false;
        }
    };
    // ====== extract zip archive / end ======

// ===================================================
//                      IMAGES
// ===================================================

    // ====== get Image Size / begin ======
    function imageSize($image_path) {
    // -----------------------------------------------
    // $image_path = <string> IMAGE file PATH
    // -----------------------------------------------
    // RETURN: <array> Image size: [ <width>, <height> ]
    // -----------------------------------------------
        if(file_exists($image_path)) {
            $ext = path($image_path, "extension");
            if($ext == "svg") {
                $svgXML = simplexml_load_file($image_path);
                list($originX, $originY, $width, $height) = explode(' ', $svgXML['viewBox']);
            }
            else {
                list($width, $height) = getimagesize($image_path);
            }
            return [ $width, $height ];
        }
        else {
            return false;
        }
    };
    // ====== get Image Size / end ======

// ===================================================
//                      STRINGS
// ===================================================
    
    // ====== apply BBCode Style / begin ======
    function BBCode($text, $dead_links = false) {
    // ----------------------------------------
    // $text = <string>
    // $dead_links = <boolean> Return "dead" links with data-href attribute
    // ----------------------------------------
    // RETURNS: <string> text converted from BBCode to HTML tags format
    // ----------------------------------------
        // Special characters
        $text = str_replace("\"", "&quot;", $text);
        //$text = str_replace("\'", "&#39;", $text);
        
        // Lists
        $text = str_replace("[/list][br]", "[/list]", $text);
        $text = str_replace("[br][/list]", "[/list]", $text);
        
        $n = preg_match_all("/\[list\](.*?)\[\/list\]/i", $text, $found);
        if($n > 0) {
            foreach(array_keys($found[1]) as $num) {
                $list = $found[1][$num];
                $style_list = "</p>\n<ul class='bbcode'>\n\t<li>".str_replace("[br]", "</li>\n\t<li>", $list)."</li>\n</ul>\n<p class='bbcode'>";
                
                $style_list = str_replace("<li>-", "<li style='list-style:none'>-", $style_list);
                $text = str_replace($found[0][$num], $style_list, $text);
            };
        };
        // Other styles
        $bbcode = [
            "/\\\\\[/" => "&#91;", // "["
            "/\\\\\]/" => "&#93;", // "]"
			"/\[br\]/i" => "<br>", // line break
        
            // Banner
            "/\[banner(.*?)\[url(.*?)\[img=(.*?)\]\[\/url\](.*?)\[\/banner\]/i" => "[banner$1[url$2<figure class='img' style='background-image:url(\"$3\")'></figure>[/url]<p class='bbcode'>$4</p>[/banner]", // banner link image
            "/\[banner(.*?)\[img=(.*?)\](.*?)\[\/banner\]/i" => "[banner$1<div class='figure' style='background-image:url(\"$2\")'></div><p class='bbcode'>$3</p>[/banner]", // banner image
            "/\[\/banner\]<br>/i" => "[/banner]", // enter fix 1
            "/\[banner\|r\](.*?)\[\/banner\]/i" => "</p><div class='banner text_right'>$1</div><p class='bbcode'>", // banner|r
            "/\[banner\|l\](.*?)\[\/banner\]/i" => "</p><div class='banner text_left'>$1</div><p class='bbcode'>", // banner|l
            "/\[banner\](.*?)\[\/banner\]/i" => "</p><div class='banner'>$1</div><p class='bbcode'>", // banner
            
            // Button
            "/\[\/button\]\[button(.*?)\]/i" => "", // join touching buttons (unify size -> first)
            "/\[\/button\]<br>/i" => "[/button]", // enter fix 1
            "/<br>\[button(.*?)\]/i" => "[button$1]", // enter fix 2            
            "/\[button\|(.*?)\](.*?)\[\/button\]/i" => "</p><div class='button size_$1'>$2</div><p class='bbcode'>", // sized button
            "/\[button\](.*?)\[\/button\]/i" => "</p><div class='button'>$1</div><p class='bbcode'>", // button
            
            // ====== 'Enter' fixes ======
            "/\[hr\]<br>/i" => "[hr]", // enter fix
            "/\[\/center\]<br>/i" => "[/center]", // enter fix
            
            // ====== Main Styles ======
            // Images
            "/\[img=(.*?)\]/i" => "<img src='$1'>", // image
            
            // Complex
            "/\[url\|i=(.*?)\](.*?)\[\/url\]/i" => "<a class='internal' data-href='$1'>$2</a>", // forced internal links
            "/\[url\|e=(.*?)\](.*?)\[\/url\]/i" => "<a href='$1' target='_blank'>$2</a>", // forced external links
            "/\[url=http:\/\/(.*?)\](.*?)\[\/url\]/i" => "<a href='http://$1' target='_blank'>$2</a>", // auto external links
            "/\[url=https:\/\/(.*?)\](.*?)\[\/url\]/i" => "<a href='https://$1' target='_blank'>$2</a>", // auto external links
            "/\[url=(.*?)\](.*?)\[\/url\]/i" => "<a class='internal' data-href='$1'>$2</a>", // std links
            "/\[email=(.*?)\](.*?)\[\/email\]/i" => "<a href=\"mailto:$1\">$2</a>", // mailto
            "/\[tel=(.*?)\](.*?)\[\/tel\]/i" => "<a href=\"tel:$1\">$2</a>", // tel
            "/\[color=(.*?)\](.*?)\[\/color\]/i" => "<span class='color_$1'>$2</span>", // color
            "/\[size=(.*?)\](.*?)\[\/size\]/i" => "<span class='size_$1'>$2</span>", // size
            "/\[\*\]/i" => "&bull;", // bullet character
            "/\[hr\]/i" => "</p><div class='bbcode hr'><hr></div><p class='bbcode'>", // horizintal line
            // Simple
            "/\[b\](.*?)\[\/b\]/i" => "<b>$1</b>", // bold
            "/\[i\](.*?)\[\/i\]/i" => "<i>$1</i>", // italic
            "/\[u\](.*?)\[\/u\]/i" => "<u>$1</u>", // unerline
            "/\[sup\](.*?)\[\/sup\]/i" => "<sup>$1</sup>", // superscript
            "/\[sub\](.*?)\[\/sub\]/i" => "<sub>$1</sub>", // subscript
            "/\[center\](.*?)\[\/center\]/i" => "</p><p class='bbcode' style='text-align:center'>$1</p><p class='bbcode'>", // center
            
            "/ +/" => " ", // multiple spaces
            "/ (.?) /" => " $1&nbsp;", // widow letters fix
        ];
        foreach($bbcode as $find => $replace){ $text = preg_replace($find, $replace, $text); };
        if($dead_links) {  $text = preg_replace("/<a href='(.*?)'>/i", "<a class='bbcode' data-href='$1'>", $text); }
        return $text;
    };

    // ====== strip off BBCode / begin ======
    function noBBCode($text) {
    // ----------------------------------------
    // $text = <string>
    // ----------------------------------------
    // RETURNS: <string> pure text without BBCode tags
    // ----------------------------------------
        $bbcode = [
            "/\[br\]/i" => " ", // line break
            "/\[url=(.*?)](.*?)\[\/url\]/i" => "$2", // external links
            "/\[email=(.*?)\](.*?)\[\/email\]/i" => "$2", // mailto
            "/\[tel=(.*?)\](.*?)\[\/tel\]/i" => "$2", // tel
            "/\[color=(.*?)\](.*?)\[\/color\]/i" => "$2", // color
            "/\[size=(.*?)\](.*?)\[\/size\]/i" => "$2", // size
            "/\[center\](.*?)\[\/center\]/i" => "$1", // center
            "/\[list\](.*?)\[\/list\]/i" => "$1", // list
            "/\[\*\]/i" => "*", // bullet character
            "/\[(...?)\]/i" => "", // index tag open
            "/\[\/(...?)\]/i" => "", // index tag close
            "/\[(.?)\]/i" => "", // simple tag open
            "/\[\/(.?)\]/i" => "", // simple tag close
            "/  /i" => " ", // double spaces
        ];
        foreach($bbcode as $find => $replace){ $text = preg_replace($find, $replace, $text); };
        return $text;
    }; // ====== strip off BBCode / end ======

    // ====== auto text Wrap / begin ======
    function autoWrap($text, $num) {
    // ----------------------------------------
    // $text = <string>
    // $num = <integer> words number in first line
    // ----------------------------------------
    // RETURNS: <string> with line break tag after specified number of words
    // ----------------------------------------
        $text = str_replace(" i ", " i[space]", $text);
        $words = explode(" ", $text);
        if(is_numeric($num) && count($words) > $num) {
            if(count($words) == $num + 1 && $num > 1) { $num--; };
            $line_1 = array_slice($words, 0, $num);
            $line_2 = array_slice($words, $num);
            $text = join(" ", $line_1)."<br>".join(" ", $line_2);
        }
        else {
            $text = $text."<br>&nbsp;";
        };
        return str_replace("[space]", " ", $text);
    }; // ====== auto text Wrap / end ======

    // ====== divide Number in string / begin ======
    function divideNumber($txt) {
    // ----------------------------------------
    // $txt = <string>
    // ----------------------------------------
    // RETURNS: <string> With numbers separated with space, eg "kurs1" -> "kurs 1"
    // ----------------------------------------
        $out = "";
        $n = 0;
        while($n < strlen($txt)) {
            $ch = substr($txt, $n, 1);
            $flag = is_numeric($ch);
            if($n++ == 0) { $out = $ch; }
            elseif($memo == $flag) { $out = $out.$ch; }
            elseif($previous != " ") { $out = $out." ".$ch; };
            $memo = $flag;
			$previous = $ch;
        };
        return $out;
    }; // ====== divide Number in string / beendgin ======

    // ====== KILL PLiterki / begin ======
    function killPl($txt) {
    // -----------------------------------------------
    // $txt = <string>
    // -----------------------------------------------
    // RETURNS: <string> without polish diacritic characters
    // -----------------------------------------------
        $pl = [ "ą" => "a", "ć" => "c", "ę" => "e", "ł" => "l", "ń" => "n",  "ó" => "o", "ś" => "s", "ź" => "z", "ż" => "z", "Ą" => "A", "Ć" => "C", "Ę" => "E", "Ł" => "L", "Ń" => "N",  "Ó" => "O", "Ś" => "S", "Ź" => "Z", "Ż" => "Z" ];
        foreach(array_keys($pl) as $key) {
            $txt = str_replace($key, $pl[$key], $txt);
        };
        return $txt;
    }; // ====== KILL PLiterki / end ======

    function makeId($string) {
    // ----------------------------------------
    // $string = <string>
    // ----------------------------------------
    // RETURNS = <string> Lowercase without spaces & special characters
    // ----------------------------------------
        $string = strtolower(killPl($string));
        $string = preg_replace("/ |\n|\t/", "_", $string);
        $string = preg_replace("/[^a-z0-9|^&|^_|^\-|^\+]/i", "", $string);
        $string = preg_replace("/_+/", "_", $string);
        return $string;
    };

    function nbsp($txt) {
    // -----------------------------------------------
    // $txt = <string>
    // -----------------------------------------------
    // RETURNS: <string> with non breaking spaces (HTML)
    // -----------------------------------------------
        return str_replace(" ", "&nbsp;", $txt);
    };

    // ====== verify Email / begin ======
	function verifyEmail($val) {
    // -----------------------------------------------
    // $val = <string>
    // -----------------------------------------------
    // RETURNS: <string> if input string is valid email or <false> if not
    // -----------------------------------------------
		if(($val != false) && (count(explode(" ", $val)) == 1) && ($val == killPl($val)) && (count($val = explode("@", $val)) == 2) && (count(explode(".", $val[1])) > 1)) { return $val; }
		else { return false; };		
	}; // ====== verify Email / end ======
    
    // ====== verify Name (forename + surname) / begin ======
    function verifyName($val) {
    // -----------------------------------------------
    // $val = <string>
    // -----------------------------------------------
    // RETURNS: <string> "Forename Surname" if input string is valid name or <false> if not
    // -----------------------------------------------
        if($val != false && $val != "" && count($val = explode(" ", $val)) == 2) {
            $val = array_map("strtolower", $val);
            $val = array_map("ucfirst", $val);
            return join(" ", $val);
        }
        else {
            return false;
        };
    }; // ====== verify Name (forename + surname) / end ======

	// ====== generate Password / begin ======
    function generatePassword($length = false, $characters = false) {
    // -----------------------------------------------
    // $length = <integer> output length (optional)
    // $characters = <string> allowed characters (optional)
    // -----------------------------------------------
    // RETURNS: <string> Random string matching given conditions
    // -----------------------------------------------
        // Default values
        if(!is_int($length)) { $length = 8; } // Default length
        if(!is_string($characters)) {
            $characters = "0123456789".         // digits
                "abcdefghijklmnopqrstuvwxyz".   // small letters
                "ABCDEFGHIJKLMNOPQRSTUVWXYZ".   // big letters
                ".!-_";                         // special chars
        }
        // Generate
        $password = "";
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $password;
    }; // ====== generate Password / end ======
	
    // ====== Case conversion with pl characters support
    function uppercase($string) { return mb_convert_case($string, MB_CASE_UPPER, "UTF-8"); };
    function lowercase($string) { return mb_convert_case($string, MB_CASE_LOWER, "UTF-8"); };
    function capitalize($string) {
        return mb_convert_case(substr($string, 0, 1), MB_CASE_UPPER, "UTF-8").mb_convert_case(substr($string, 1, mb_strlen($string, "UTF-8") - 1), MB_CASE_LOWER, "UTF-8");
    };

?>