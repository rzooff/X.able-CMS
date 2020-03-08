$(document).ready(function() {
    
    // ==========================================
    //         Preview mode info popup
    // ==========================================
    if($("input#preview_mode").length && $("input#preview_mode").val() == "draft") {
        html = "<div id='preview_info'>\n" +
            "\t<p><b>X.able CMS</b> | Podgląd wersji roboczej</p>\n" +
            "\t<button>Wyłącz podgląd</button>\n" +
            "</div>\n";
        $("body").append(html);
        // back to non-preview page
        $("#preview_info button").click(function() {
            url = document.location.href;
            // Delete previous get value if any
            url = url.replace("?preview=preview&", "?");
            url = url.replace("?preview=preview", "");
            url = url.replace("&preview=preview&", "&");
            url = url.replace("&preview=preview", ""); 
            // Manage hash
            url_hash = url.split("#");
            if(url_hash.length == 1) {
                url = url_hash.shift();
                hash = "";
            }
            else {
                url = url_hash.shift();
                hash = "#" + url_hash.pop();
            };
            // Manage query(ies)
            url_query = url.split("?");
            if(url_query.length == 1) {
                url = url_query.shift();
                query = "?preview=none";
            }
            else {
                url = url_query.shift();
                query = "?" + url_query.pop() + "&preview=none";
            };
            // Reload
            location.href = url + query + hash;
        });
    };
    
    // ==========================================
    //      Clean url from preview GET data
    // ==========================================
    prev_key = "preview=";
    prev_found = false;
    url = location.href.split("?");
    if(url.length == 2) {
        queries = url[1].split("&");
        cleaned = [];
        for(i in queries) {
            if(queries[i].substr(0, prev_key.length) == prev_key) {
                prev_found = true;
            }
            else {
                cleaned[cleaned.length] = queries[i];
            };
        };
        if(prev_found) {
            if(cleaned.length > 0) {
                url = url[0] + "?" + cleaned.join("&");
            }
            else {
                url = url[0];
            };
            title = document.title;
            window.history.pushState(window.location.href, title, url);
        };
    };

});