
    <head>
        <?php
            $xable_version = trim(array_shift(file("doc/version.txt")));
            $last_update = array_pop(explode(";", $xable_version));
            $last_update = uniqid();
        
            $xable_date = array_pop(explode(";", $xable_version));
            echo "\n".
                "\t\t<!-- ======================================\n".
                "\t\t               ><.able CMS"."\n".
                "\t\t      (c)".substr($xable_date, 0, 4)." maciej@maciejnowak.com"."\n".
                "\t\t         v.".str_replace(";", ", build.", $xable_version)."\n".
                "\t\t====================================== -->\n";
        ?>
        
		<meta charset="UTF-8">
		<title>X.able / <?php echo $panel_label; ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <link href='https://fonts.googleapis.com/css?family=Lato:100,300,400,700,900|Inconsolata:400,700|Audiowide&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" type="text/css" href="style/xable_nav.css?v=<?php echo $last_update; ?>" />
        <link rel="stylesheet" type="text/css" href="style/xable_creator.css?v=<?php echo $last_update; ?>" />
        <?php if($panel_name != "creator") { echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"style/xable_$panel_name.css?v=$last_update\" />\n"; } ?>
        <link rel="stylesheet" type="text/css" href="style/xable_responsive.css?v=<?php echo $last_update; ?>" />
		<link rel="stylesheet" type="text/css" href="style/foundation-icons.css" />
        
		
        <script src='script/jquery-3.1.0.min.js'></script>
        <script src='script/functions.js'></script>
        <script src='script/xable_nav.js'></script>
        <?php echo "<script src='script/xable_$panel_name.js'></script>\n"; ?>
        
        <?php exportLocalization(); ?>
	</head>