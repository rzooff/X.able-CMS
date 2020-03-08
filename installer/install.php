<?php

    session_start();
    session_unset();

    $root = "..";
    $xable_admin = "xable/admin";
    $site_admin = "$root/admin";

    // ====== Load Functions Libraries ======

    require "$xable_admin/script/functions.php";
    require "$xable_admin/script/cms.php";
    require "$xable_admin/script/xml.php";

    $_SESSION["xable_installer_file"] = path(__FILE__, "basename");

    // ===================================================
    //                    LOCALIZATION
    // ===================================================

    if(is_string($site_options['admin_lang'])) {
        $_SESSION['admin_lang'] = $site_options['admin_lang'];
    }
    else {
        $_SESSION['admin_lang'] = "pl"; // default
    };
    loadLocalization("$xable_admin/localization");

    // ===================================================
    //                     FUNCTIONS
    // ===================================================

    function createUser($pass_path, $groups_path) {
    // -------------------------------------------
    // $pass_path = <string> Passwords file path relative to installer
    // $groups_path = <string> Groups file path relative to installer
    // -------------------------------------------
    // Create admin account (add password && "dev" group membership)
    // -------------------------------------------
        // ====== .htaccess ======
        $users = [];
        // Get existing users
        if(file_exists($pass_path)) {
            foreach(array_map("trim", file($pass_path)) as $user) {
                $login = array_shift(explode(":", $user));
                $users[$login] = $user;
            }
        }
        // New passwords path
        else {
            makeDir(path($pass_path, "dirname"));
        };
        // Add new Admin password
        $users[ $_POST["login"] ] = $_POST['login'].":".crypt($_POST['password'], "mn");
        // Save
        if(!safeSave($pass_path, join("\n", $users))) { $_POST["summary_mode"] = "failed"; };
        
        // ====== .groups ======
        $group_files = listDir(path($groups_path, "dirname"), "ini");
        $groups = [];
        // Read current .groups
        if(file_exists($groups_path)) {
            foreach(array_map("trim", file($groups_path)) as $group) {
                $group = array_map("trim", explode(":", $group));
                $id = $group[0];
                // Delete not defined users
                $group_users = [];
                foreach(explode(" ", $group[1]) as $user) {
                    if(in_array($user, array_keys($users))) { $group_users[] = $user; };
                };
                if(in_array("$id.ini", $group_files)) {
                    $groups[$id] = $id.": ".join(" ", $group_users);
                };
            };
        };
        // Add missing ini name(s) to .groups
        foreach($group_files as $file) {
            $id = path($file, "filename");
            if($id != "dev" && !isset($groups[$id])) {
                $groups[$id] = "$id: ";
            }
        };
        // Add new Admin user to Dev
        if(isset($groups["dev"])) {
            $users = explode(" ", trim(array_pop(explode(":", $groups["dev"]))));
            if(!in_array($_POST["login"], $users)) { $users[] = $_POST["login"]; };
            $groups["dev"] = "dev: ".join(" ", $users);
        }
        // Create Dev group
        else {
            $groups["dev"] = "dev: ".$_POST["login"];
        };
        // Save
        if(!safeSave($groups_path, join("\n", $groups))) { $_POST["summary_mode"] = "failed"; };
    };

    function CleanUp($root, $site_admin) {
    // -------------------------------------------
    // $root = <string> Site root path relative to installer
    // $site_admin = <string> Site admin folder path relative to installer
    // -------------------------------------------
    // Delete/move installer files after instalation complete
    // -------------------------------------------
        $installer_folder = "$site_admin/_installer";
        $installer_files = [ "unzip.php.txt", "install.php", "readme.txt" ];
        mkdir($installer_folder);
        
        // Copy installer files
        foreach(listDir(getcwd(), ".") as $file) {
            copy($file, "$installer_folder/$file");
        };
        // Move installer package
        foreach(listDir($root, "zip") as $file) {
            if(strstr(strtolower($file), "xable")) {
                rename("$root/$file", "$installer_folder/$file");
            };
        };
        // Delete unzip script
        unlink("$root/unzip.php");
        // Left only install folder
    };

    // ===================================================
    //                      INSTALL
    // ===================================================

    // ====== Install ======
    if($_POST["installer_stage"] == "summary") {
        $_POST["summary_mode"] = "done";

        // COMPLETE
        if($_POST["mode"] == "complete") {
            // Copy files
            copyDir("xable", $root);
            unlink("$root/xable.log"); // clean log on full install
            // Apply website .htaccess
            if(file_exists("$root/_htaccess")) {
                rename("$root/_htaccess", "$root/.htaccess");
            };
        }
        // UPDATE
        else {
            // Make current CMS backup
            $site_admin_bak = "$site_admin-bak";
            if(file_exists($site_admin)) { rename($site_admin, $site_admin_bak); };
            // Copy CMS files
            mkDir($site_admin);
            copyDir($xable_admin, $site_admin);
            // Restore site data
            if(file_exists($site_admin_bak)) {
                // Restore Users/Groups
                removeDir("$site_admin/_users");
                makeDir("$site_admin/_users");
                copyDir("$site_admin_bak/_users", "$site_admin/_users");
                // Restore xable.ini
                rename("$site_admin/xable.ini", "$site_admin/xable-updated.ini.bak");
                copy("$site_admin_bak/xable.ini", "$site_admin/xable.ini");
                // Copy site Backups
                rename("$site_admin_bak/_backup", "$site_admin/_backup");
                // Restore Plugins
                foreach(listDir("$site_admin_bak/_plugins", "/") as $plugin) {
                    if(!file_exists("$site_admin/_plugins/$plugin")) {
                        copyDir("$site_admin_bak/_plugins/$plugin", "$site_admin/_plugins/$plugin");
                    }
                };
            };
        };
        
        // Create Admin account
        if(is_string($_POST["create_admin"]) && $_POST["create_admin"] == "create_admin") {
            $ini_pathes = loadIni("$xable_admin/xable.ini", "pathes");
            $pass_path = $ini_pathes["passwords"];
            $groups_path = "$site_admin/_users/.groups";
            createUser($pass_path, $groups_path);
        };

        // SUMMARY
        if($_POST["summary_mode"] == "done" && file_exists("$site_admin/index.php") && file_exists("$root/index.php")) {
            CleanUp($root, $site_admin);
            
            $form_action = $site_admin;
        }
        else {
            $_POST["summary_mode"] = "failed";
        };

    }
    // ====== Options ======
    else {
        $form_action = $_SESSION["xable_installer_file"];
        $_POST["installer_stage"] = "options";
        
        if(file_exists("$root/index.php") || file_exists("$root/start.php")) {
            $_POST["installer_mode"] = "update";
        }
        else {
            $_POST["installer_mode"] = "complete";
        }
    };

?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>X.able CMS / <?php echo localize("installer-label"); ?></title>

		<link rel="stylesheet" type="text/css" href="<?php echo $xable_admin; ?>/style/index.css">
		<link rel="stylesheet" type="text/css" href="<?php echo $xable_admin; ?>/style/login.css">
        <link rel="stylesheet" type="text/css" href="<?php echo $xable_admin; ?>/style/colors.css">
        <link rel="stylesheet" type="text/css" href="<?php echo $xable_admin; ?>/style/install.css">
        
		<link rel="stylesheet" type="text/css" href="<?php echo $xable_admin; ?>/style/foundation-icons.css">
        
		<link href="http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900|Inconsolata:400,700|Audiowide&amp;subset=latin,latin-ext" rel="stylesheet" type="text/css">
        
		<?php exportLocalization(); ?>
    </head>
    <body>
	
        <main>
            <form action="<?php echo $form_action; ?>" method="post">
                <article id="splash">
                    <nav>
                        <h1><strong>&gt;&lt;</strong>.able<span>CMS</span></h1>
                        <h2>&copy; by <a href="mailto:maciej@maciejnowak.com">maciej@maciejnowak.com</a></h2>
                    </nav>
                    <section id="options">
                        <ul>
                            <li><h3><?php echo localize("instalation-options"); ?></h3></li>
                            <li class="installer_mode">
                                <label><input type="radio" class="installer_mode complete" name="mode" value="complete"><?php echo localize("complete-install"); ?></label>
                                <label><input type="radio" class="installer_mode update" name="mode" value="update"><?php echo localize("update-install"); ?></label>
                            </li>
                            <li class="hr"><hr></li>
                            <li class="create_admin">
                                <label><input type="checkbox" class="create_admin" name="create_admin" value="create_admin"><?php echo localize("create-admin"); ?></label>
                            </li>
                            <li><p><?php echo localize("admin-login"); ?>:</p></li>
                            <li><input type="text" class="text login" name="login" value=""></li>
                            <li class="spacer">&nbsp;</li>
                            <li><p><?php echo localize("password-label"); ?>:</p></li>
                            <li><input type="password" class="text password" name="password" value=""></li>
                            <li><p><?php echo localize("repeat-password"); ?>:</p></li>
                            <li><input type="password" class="text repeat_password" value="" disabled=""></li>
                        </ul>
                        <div class="buttons">
                            <input type='hidden' name='installer_stage' value='summary'>
                            <button class="submit off" disabled=""><?php echo localize("continue-label"); ?></button>
                        </div>
                    </section>
                    <section id="summary">
                        <ul>
                            <li class="done"><h3><?php echo localize("installation-complete-info"); ?></h3></li>
                            <li class="done">
                                <figure class="big_icon fi-check"></figure>
                            </li>
                            <li class="failed"><h3><?php echo localize("installation-failed-info"); ?></h3></li>
                            <li class="failed">
                                <figure class="big_icon fi-x"></figure>
                            </li>
                        </ul>
                        <div class="buttons">
                            <button class="submit off" disabled=""><?php echo localize("continue-label"); ?></button>
                        </div>
                    </section>
                </article>
            </form>
        </main>
        
        <div id="post_output">
            <?php
                echo "\n";
                foreach(array_keys($_POST) as $key) {
                    $val = $_POST[$key];
                    echo "\t\t\t<input type='hidden' class='$key' value='$val'>\n";
                };
            ?>
        </div>

        <script src="xable/admin/script/jquery-3.1.0.min.js"></script>
        <script src="xable/admin/script/install.js"></script>

    </body>
</html>
