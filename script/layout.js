$(document).ready(function() {
    
    var ANIMATION_TIME = 400;
    var MEMO_FACTOR = 0;
    var SCROLL_BUSY = true;

    $("h1").click(function() {
        alert( $(window).width() + " x " + $(window).height() );
    })
 
    // ==========================================
    //                  Layout
    // ==========================================
    
    $(window).scroll(function() { moveImage(); })
    $(window).resize(function() { moveImage(); })
    
    function moveImage() {
        scroll = $(window).scrollTop();
        
        img_hei = $("section#start .image_box").height();
        img_move = (img_hei * -0.5) + (scroll * 0.7);
        win_hei = window.innerHeight ? window.innerHeight : $(window).height()
        //txt_hei = $(window).height() - parseInt( $("section#start .image_box").css("margin-bottom") ) - (img_hei * 0.5);
        margin = parseInt( $("section#start .image_box").css("margin-bottom") );
        txt_hei = win_hei - (img_hei * 0.5) - margin;
        
        $("section#start .image_box").css({ "bottom": img_move });
        $("section#start .text_box").css({ "height": txt_hei });
    }
    moveImage();
    
    $("article p.text").each(function() {
        html = $(this).html();
        //if(html.indexOf("tel:") > -1) {
        //    alert(html);
        //}
        html = html.replace("<a href=\"mailto:", "<ion-icon name='mail-sharp'></ion-icon> <a href=\"mailto:");
        html = html.replace("<a class=\"internal\" data-href=\"tel:", "<ion-icon name='call-sharp'></ion-icon> <a class=\"internal\" data-href=\"tel:");
        html = html.replace("<a class=\"internal\" data-href=\"www:", "<ion-icon name='globe'></ion-icon> <a class=\"external\" target='_blank' href=\"");
        $(this).html(html);
    })
    
    $(".section_box button.scrolldown").click(function() {
        id = $(this).closest(".section_box").next(".section_box").children("section").attr("id");
        scrollTo("#" + id, 0);
    })
    
    // ==========================================
    //               Smooth Scroll
    // ==========================================
    
	function scrollTo(id, margin) {
        if (navigator.userAgent.match(/(iPod|iPhone|iPad|Android)/)) {           
            scroll = $(window).scrollTop() + $(id).offset().top;
        }
        else {
            scroll = $(id).offset().top;
        };

        $("html, body").css({ "overflow-y": "auto" }).animate({
            scrollTop: (scroll + margin)
        }, ANIMATION_TIME);
    };

    // ==========================================
    //                 Download
    // ==========================================
    
    $current = $("#download dl.key_installer dd").first();
    $("#download a.current_version").attr("href", $current.find("a").attr("href"));
    $("#download .button_box p").html( $current.find("a").html() );
    //alert($("#download .button_box").html());
    if($("#download dl.key_installer dd").length > 1) {
        $current.remove();
    }
    else {
        $current.closest("dl").remove();
    }

    $("#download dl dt").click(function() {
        $dl = $(this).closest("dl");
        if($dl.find("dd").css("display") == "block") {
            hideDownloads($dl);
        }
        else {
            showDownloads($dl);
        }
    })
    
    function showDownloads($dl) {
        if($dl.length) {
            $dl.find("dd").slideDown(ANIMATION_TIME);
            $dl.find("dt ion-icon").attr("name", "chevron-down");
        };
    }
    
    function hideDownloads($dl) {
        if($dl.length) {
            $dl.find("dd").slideUp(ANIMATION_TIME);
            $dl.find("dt ion-icon").attr("name", "chevron-forward");
        }
    }
    
    hideDownloads($("#download dl.key_installer"));

    // ==========================================
    //                Navigation
    // ==========================================

    // ==========================================
    //                URL Update
    // ==========================================

    function updateBBCodeLinks() {
        $("a.bbcode").each(function() {
            href = $(this).attr("data-href");
            
            if(href.split(":").length > 1) {
                $(this).attr("href", href);
            }
            else {
                $(this).attr("href", ROOT + href);
            }
        })
    }
    updateBBCodeLinks();
    
    // ==========================================
    //                Launch Page
    // ==========================================
    
    setTimeout(function() {
        $("#loader").delay(ANIMATION_TIME).fadeOut(ANIMATION_TIME);
    }, ANIMATION_TIME * 1);
    
    // ==========================================
    //                  Footer
    // ==========================================
    
    /*
    $footer = $("footer");
    $("main section").last().find("article").last().append( $footer.clone() );
    $footer.remove();
    */
})