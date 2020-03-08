<?php
        // build: 20200306
?>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta charset="UTF-8">
		<meta http-equiv="Content-Language" content="<?php echo $_SESSION['lang']; ?>">
		<title><?php echo readXml($settings, "page title"); ?></title>
        <meta name="description" content="<?php echo readXml($settings, "page description"); ?>"/>
        <meta name="keywords" content="<?php echo readXml($settings, "page keywords"); ?>"/>
        <link rel='canonical' href='https://<?php echo readXml($settings, "page domain"); ?>' />
        
        <!-- ====== Favicon: https://realfavicongenerator.net ====== -->
        <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $root; ?>_favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $root; ?>_favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="<?php echo $root; ?>_favicon/favicon-16x16.png">
        <link rel="manifest" href="<?php echo $root; ?>_favicon/site.webmanifest">
        <link rel="mask-icon" href="<?php echo $root; ?>_favicon/safari-pinned-tab.svg" color="#5bbad5">
        <link rel="shortcut icon" href="<?php echo $root; ?>_favicon/favicon.ico">
        <meta name="msapplication-TileColor" content="#da532c">
        <meta name="msapplication-config" content="<?php echo $root; ?>_favicon/browserconfig.xml">
        <meta name="theme-color" content="#ffffff">
        
        <!-- ====== FACEBOOK ====== -->
		<meta class='share-url' property='og:url' content='https://<?php echo readXml($settings, "page domain"); ?>' />
		<meta class='share-type' property='og:type' content='website' />
		<meta class='share-title' property='og:title' content='<?php echo readXml($settings, "page title"); ?>' />
		<meta class='share-description' property='og:description' content='<?php echo readXml($settings, "page description"); ?>' />
		<meta class='share-image' property='og:image' content='https://<?php echo readXml($settings, "page domain")."/".readXml($settings, "media thumb"); ?>' />
