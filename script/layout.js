$(document).ready(function() {

    // ==========================================
    //                 Language
    // ==========================================   
    
    $("nav .languages .change").click(function() {
        location.href = "index.php?lang=" + $(this).attr("value");
    });
    
    // ==========================================
    //                  Footer
    // ==========================================
    
    footer = "<footer>WebSite by <a href='http://maciejnowak.com' target='_blank'>maciejnowak.com</s></footer>";
    $("body").append(footer);
    
    urlQuery();

})