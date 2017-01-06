<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>X.able CMS / Login</title>
		<link href='http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900|Inconsolata:400,700|Audiowide&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
		<style>
			* { margin: 0; padding 0; }
			header { text-align: center; color: #ffffff; background-color: #2f393b; padding: 15px 0; }
			header a { color: inherit; text-decoration: none; }
			header a:hover { color: #ffffff; text-decoration: underline; }
			h1, h1 strong { font-family: 'Audiowide', cursive; font-size: 25px; }
			h1, h2 { font-weight: normal; }
			h1 strong { position: relative; top: 3px; font-size: 1.4em; text-shadow: 0 0 10px #ffffff; }
			h1 span { position: relative; top: 1px; font-size: 0.33em; }
			h2 { position: relative; top: -5px; margin-bottom: 10px; font-size: 0.8em; opacity: 0.5; }
			h3 {
				font-size: inherit;
				font-weight: normal;
				text-align: left;
				padding: 0;
				margin-bottom: 10px;
				border-bottom: 1px solid #aaaaaa;
			}
			h3 span {
				position: relative;
				top: -8px;
				left: 10px;
			}
			body {
				width: 100%;
				height: 100%;
				color: #333333;
				background-color: #e4e4e4;
				background-color: #d7dddd;;
				font-family: 'Lato', sans-serif;
				font-size: 14px;
			}
			article {
                display: none;
				width: 300px;
				margin: 100px auto;
				background-color: #ffffff;
				box-shadow: 0 0 10px #aaaaaa;
				border-radius: 5px;
				overflow: hidden;
				padding: 0;
			}
			.button {
				text-align: center;
			}
			button {
				margin-top: 20px;
				padding: 10px 25px;
				min-width: 120px;
				font-size: inherit;
				color: #ffffff;
				border: none;
				background-color: #46d6ce;;
				border-radius: 1px;
				font-weight: bold;
				font-size: 0.8em;
				cursor: pointer;
				transition: 0.5s;
			}
			button:hover {
				opacity: 0.7;
			}
            .center { text-align: center; }
			ul, li { list-style: none; margin: 0; }
			ul { padding: 20px; }
			li { padding: 10px 0; }
			li p { padding: 5px 0; }
			li input.text { width: 100%; }
			li label { float: left; width: 50%; }
			li hr { border: 0; border-top: 1px solid #333333; margin-top: 20px; }
			label span { padding-left: 5px; }
            label.disabled { opacity: 0.5; }
            input { border: 1px solid #dddddd; }
            input:disabled { background-color: #f4f4f4; }
            
            p.done { font-size: 1.5em; font-weight: bold; }
            p.log { opacity: 0.5; padding: 0; }`
		</style>
        
	</head>
	<body>
		<article>
			<form action='install.php?action=install' method='post'>
				<header>
					<h3><span>Install</span></h3>
					<h1><strong>&gt;&lt;</strong>.able<span>CMS</span></h1>
					<h2>(C)2017 by <a href='mailto:maciej@maciejnowak.com'>maciej@maciejnowak.com</a></h2>
				</header>
                <?php
                    require("xable/admin/script/functions.php");
                    require("xable/admin/script/xml.php");
                    require("xable/admin/script/cms.php");
                    // INSTALL
                    if($_GET['action'] == "install" && count($_POST) > 0) {
                        $root = "..";
                        $xable_content = "xable";
                        $admin_folder = "admin";
                        $admin_path = "$root/$admin_folder";
                        echo "<ul id='done'>\n";
                        echo "<input type='hidden' id='admin_path' value='$admin_path'>\n";
                        echo "<li>\n";
                        // ====== COPY FILES ======
                        if($_POST['type'] == "complete") {
                            echo "<p class='log'>&bull; Installation type: Complete</p>";
                            copyDir($xable_content, $root);
                            unlink("$root/xable.log"); // clean log on full install
                        }
                        else {
                            echo "<p class='log'>&bull; Installation type: Update</p>";
                            if(file_exists($admin_path)) { rename($admin_path, $admin_path."_bak"); };
                            mkDir($admin_path);
                            copyDir("$xable_content/$admin_folder", $admin_path);
                        };
                        // ====== CREATE DEV USER ======                        
                        if($_POST['user'] == "create" && $_POST['login'] != "" && $_POST['password'] != "") {
                            // .htpasswd
                            $ini_pathes = loadIni("$xable_content/$admin_folder/xable.ini", "pathes");
                            $pass_path = $ini_pathes['passwords'];
                            makeDir(path($pass_path, "dirname"));
                            if(safeSave($pass_path, $_POST['login'].":".crypt($_POST['password'], "mn"))) {
                                echo "<p class='log'>&bull; Saved: User login & password</p>";
                            };
                            // .groups
                            $groups = array( "dev: ".$_POST['login'] );
                            foreach(listDir("$admin_path/_users", "ini") as $group) {
                                $group = path($group, "filename");
                                if($group != "dev") { $groups[] = $group.":"; };
                            };
                            if(safeSave("$admin_path/_users/.groups", join("\n", $groups))) {
                                echo "<p class='log'>&bull; SuperUser joined 'dev' group</p>";
                            };
                            // .htaccess - depreciated on v2.1!
                            //$full_pass_path = split("/", getcwd());
                            //array_pop($full_pass_path);
                            //$full_pass_path = join("/", $full_pass_path)."/".substr($pass_path, strlen($root) + 1);
                            //$htaccess = "AuthName \"Podaj hasło\"\n".
                            //    "AuthType Basic\n".
                            //    "AuthUserFile $full_pass_path\n".
                            //    "Require valid-user\n";
                            //if(safeSave("$admin_path/.htaccess", $htaccess)) {
                            //    echo "<p class='log'>&bull; Authorization activated</p>";
                            //};
                        }
                        // ====== UPDATE: RESTORE USERS ======
                        elseif($_POST['user'] != "create" && $_POST['type'] == "update" && file_exists("../admin_bak")) {
                            // Restore _users
                            removeDir($admin_path."/_users");
                            makeDir($admin_path."/_users");
                            copyDir($admin_path."_bak/_users", $admin_path."/_users");
                            // Restore xable.ini
                            rename($admin_path."/xable.ini", $admin_path."/xable.ini.bak");
                            copy($admin_path."_bak/xable.ini", $admin_path."/xable.ini");
                            // Copy backups
                            rename($admin_path."_bak/_backup", $admin_path."/_backup");
                            // log
                            echo "<p class='log'>&bull; Users/groups authorization restored</p>";
                        };
                        
                        // ====== MOVE / REMOVE INSTALLER FILES ======
                        $installer_folder = "$admin_path/_installer";
                        mkdir($installer_folder);

                        // Move installer files
                        $files = array("unzip.php", "install.php", "readme.txt");
                        foreach($files as $file) {
                            copy($file, "$installer_folder/$file");
                        };
                        // Delete temp files & folder
                        unlink("$root/unzip.php");
                        //
                        echo "<p class='log'>&bull; Removed installer temporary files</p>";
                        
                        // Move zip package
                        foreach(listDir($root) as $file) {
                            if(strstr(strtolower($file), "xable")) {
                                rename("$root/$file", "$installer_folder/$file");
                            };
                        };

                        // ====== END ======
                        echo "</li>\n";
                        echo "<li><p class='done center'>Installation complete!</p></li>";
                        echo "<li class='button'><button>Continue</button></li>\n";
                        echo "</ul>\n";                

                    };
                ?>
				<ul id='config'>
					<li>
						<p class='title'>Installation type</p>
						<label><input type='radio' class='type' name='type' value='complete' checked><span>Complete</span></label>
						<?php
							if(file_exists("../index.htm") || file_exists("../index.html") || file_exists("../index.php")) {
								echo "<label><input type='radio' class='type' name='type' value='update' checked><span>Update</span></label>\n";
							}
							else {
								echo "<label class='disabled'><input type='radio' class='type' name='type' value='update' disabled><span>Update</span></label>\n";
							};
							
						?>

					</li>
					<li><hr></li>
					<li>
                        <?php
                            if(file_exists("../admin/_users/.groups")) {
                                echo "<label><input type='checkbox' class='user' name='user' value='create'><span>Create user</span></label>";
                            }
                            else {
                                echo "<label><input type='checkbox' class='user' name='user'  value='create' checked><span>Create user</span></label>";
                            };
                        ?>
                        
					</li>
					<li class='user'>
						<p class='title'>Developer login:</p>
						<input type='text' class='text login' name='login'>
					</li>
					<li class='user'>
						<p class='title'>Password (min. 6 characters):</p>
						<input type='password' class='text password' name='password'>

						<p class='title'>Repeat password:</p>
						<input type='password' class='text repeat_password'>
					</li>
					<li class='button'>
						<button>Continue</button>
					</li>
				</ul>
			</form>
		</article>
        <script src="xable/admin/script/jquery-3.1.0.min.js"></script>
        <script>
            $(document).ready(function() {
                if($("#done").length) { $("#config").hide(); };
                
                
                if($("input.user").prop("checked") == false) {
                    $("li.user input").prop("disabled", true);
                };
                $("input.user").change(function() {
                    if( $(this).prop("checked") == true ) { $("li.user input").prop("disabled", false); }
                    else { $("li.user input").prop("disabled", true); };
                });
                $("input.type").change(function() {
                   if( $(this).val() == "complete" ) {
                       $("input.user").prop("checked", true);
                       $("li.user input").prop("disabled", false);
                   };
                });
                $("#config button").click(function() {
                    $(this).blur();
                    if($("input.user").prop("checked") == false) { return true; }
                    else if($("input.login").val().length < 3) {
                        alert("Login is too short");
                        $("input.login").focus();
                        return false;
                    }
                    else if($("input.password").val().length < 6) {
                        alert("Password is too short");
                        $("input.password").focus();
                        return false;
                    }
                    else if($("input.password").val() != $("input.repeat_password").val()) {
                        alert("Passwords don't match");
                        $("input.repeat_password").focus();
                        return false;
                    }
                    else { return true };
                    return false;
                });
                
                $("#done button").click(function() {  
                    $(this).blur();
                    //alert($("input#admin_path").val());
                    location.href = $("input#admin_path").val() + "?install=completed";
                    return false;
                });
                
                $("article").delay(200).fadeIn(500);
    
            });
        </script>
	</body>
</html>
