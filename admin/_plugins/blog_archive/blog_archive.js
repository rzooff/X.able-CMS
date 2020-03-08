$(document).ready(function() {
    
    function getCurrentLanguage() {
        var lang = $("#edit_lang").val();
        $("#lang li").each(function() {
            if($(this).css("display") != "none") {
                lang = $(this).attr("value");
            }
        })
        return lang;
    }
    
    $("section._blog_archive_edit button").click(function() {

        path = $("input#root").val() + "/" + $(this).attr("href").split(":").pop();
        alert(path);
        
        $("#loader").fadeIn(250, function() {
            location.href = "index.php?path=" + path + "&lang=" + getCurrentLanguage();
        });
    })
})