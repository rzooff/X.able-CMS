$(document).ready(function() {

    // ==========================================
    //              @path update
    // ==========================================
    
    patterns = [];
    saveas = $("input#saveas").val().split("/");
    patterns['basename'] = saveas.pop();
    patterns['filename'] = patterns['basename'].path("filename");
    patterns['folder'] = saveas.pop();
	
    $("form .media").children("div").each(function() {
        val = $(this).attr("value");
        if(jQuery.type(val) == "string" && val != "") {
            for(pattern in patterns) {
                repl = patterns[pattern];
                find = new RegExp("@" + pattern, "g");
                //alert("pattern: " + pattern + " / find: " + find);
                val = val.replace(find, patterns[pattern]);
            };
            $(this).attr("value", val);

        };
    });

});