$(document).ready(function() {

    // ==========================================
    //            Footer & copyrights
    // ==========================================
    
    version = $("input#xable_version").val().split(";");
    subject = "[X.able CMS] v" + version[0] + " / " + $("input#site_site_ID").val();

	footer = [
        // Copyrights
		"<b>X.able CMS</b> v" + version[0] + " &copy;" + version[1].substr(0, 4) + " <a href='http://maciejnowak.com' target='_blank'>maciejnowak.com</a>&nbsp;<span class='divide'>|</span>",
        // Standard tools
		"<a href='mailto:maciej@maciejnowak.com?Subject=" + subject + "'><span class='fi-mail help' help='" + LOCALIZE["bug-report"] + "'></span></a>",
        "<a href='backup.php?page=" + encodeURIComponent($("input#saveas").val()) + "' class='unsaved'><span class='fi-save help' help='" + LOCALIZE["site-backup"] + "'></span></a>",
        // Advanced tools
        "<a href='xable_update.php' class='unsaved'><span class='fi-loop creator_module advanced_update help' help='" + LOCALIZE["advanced-tools-label"] + " | " + LOCALIZE["update-label"] + "'></span></a>",
		"<a href='xable_users.php' class='unsaved'><span class='fi-torsos-all creator_module advanced_users help' help='" + LOCALIZE["advanced-tools-label"] + " | " + LOCALIZE["users-groups-label"] + "'></span></a>",
		"<a href='xable_creator.php?open=" + encodeURIComponent($("input#edit_path").val()) + "' class='unsaved'><span class='fi-wrench creator_module advanced_creator help' help='" + LOCALIZE["advanced-tools-label"] + " | " + LOCALIZE["creator-label"] + "'></span></a>",
		"<a href='xable_explorer.php' class='unsaved'><span class='fi-folder creator_module advanced_explorer help' help='" + LOCALIZE["advanced-tools-label"] + " | " + LOCALIZE["file-explorer-label"] + "'></span></a>",
        "<span id='update_output' class='fi-eye creator_module advanced_preview help' help='" + LOCALIZE["xml-preview-label"] + "'></span>"
	];

	$("form").append("<footer><p>" + footer.join(" ") + "</p></footer>");
    
    function disableModule(icon) {
        // Deactivate
        /*
        $item = $("footer " + icon).closest("a");
        span = $item.html();
        $item.replaceWith(span);
        $("footer " + icon).click(function() {
            $(this).blur();
            alert("Funkcja niedostępna");
        });
        */
        // Hide
        $("footer " + icon).each(function() {
            $a = $(this).parent("a");
            if($a.length) { $a.hide(); }
            else { $(this).hide(); }
        });
    };
	
	if( $("input#enable_creator").val() != "true" ) {
        // creator_modules
		disableModule(".fi-torsos-all");
		disableModule(".fi-wrench");
		disableModule(".fi-eye");
		disableModule(".fi-loop");
		disableModule(".fi-folder");
		if( $("input#enable_backup").val() != "true" ) { disableModule(".fi-save"); };
		if( $("input#enable_password").val() != "true" ) { disableModule(".fi-torso-business"); };
		//if( $("input#enable_users").val() != "true" ) { disableModule(".fi-torsos-all"); };
	};
    
    if( $("input#enable_publish").val() != "true" ) {
        $(".publisher_only").remove();
        $("#menu_notifications").remove();
    };

    if( $("input#enable_remove").val() != "false" ) {
		$("nav a").each(function() {
			$(this).children("dd").append("<span class='remove manual fi-x' help='" + LOCALIZE["remove-page"] + "'></span>");
		});
	};
    	
});