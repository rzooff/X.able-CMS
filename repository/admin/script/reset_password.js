$(document).ready(function() {
    
    // ==========================================
    //               Info popup
    // ==========================================
	
    function infoPopup(text, icon) {
    // --------------------------------
    // text = <string> mesage text
    // icon = <string> icon type
    // --------------------------------
    // Show custom vanishing alert box
    // --------------------------------
        // icon
        color = false;
        if(icon == "done") { icon = "fi-checkbox"; color = "color_done"; }
        else if(icon == "error") { icon = "fi-x"; color = "color_error"; }
        else if(jQuery.type(icon) != "string" || icon.substr(0, 3) != "fi-") { icon = "fi-lightbulb"; };
        // show popup
        $("#info_box").remove();
        html = "<div id='info_box'>";
        html = html + "<h6><span class='" + icon + "'></span></h6>";
        html = html + "<p class='info'>" + text + "</p>";
        html = html + "</div>"; // close popup box & container
        $("body").append( html );
        //if(color != false) { $("#info_box h6").css({ "background-color": color }); };
        if(color != false) { $("#info_box h6").addClass(color); };
        $("#info_box").fadeIn(250).delay(1000).fadeOut(1500, function() { $(this).remove(); });
    };
    
    // ==========================================
    //                  Form
    // ==========================================
    
    $("button.confirm").click(function() {
        new_pass = $("#new_password").val();
        confirm_pass = $("#confirm_password").val();

        if(new_pass.length < 6) {
            infoPopup(LOCALIZE["new-password-too-short"]); //Nowe hasło jest za krótkie
            $("#new_password").focus();
        }
        else if(new_pass != confirm_pass) {
            infoPopup(LOCALIZE["different-passwords"]); //Podane hasła różnią się
            $("#confirm_password").focus();
        }
        else {
            $(this).blur();
            return true;
        };
        
        $(this).blur();
        return false;
    });
    
    $("button.cancel").click(function() {
        href = $(this).attr("href");
        $("#page_fader, #popup_container").fadeOut(500);
        setTimeout(function() { location.href = href; }, 500);
        $(this).blur();
        return false;
    });
    
    $("button.goto_login").click(function() {
        location.href = "login.php";
        return false;
    });
    
    // ==========================================
    //              Initial actions
    // ==========================================

    if($("#popup").val() == "") {
        $("#login").val( $("#email").val() );
        $("form .popup_info").hide();
        $("form .buttons .goto_login").hide();
    }
    else {
        $("form .inputs, form .buttons button").hide();
        if($("#show_goto_login").length) { $("form .buttons .goto_login").show(); };
        // Info autofill
        $("form .popup_info p").html( $("#popup").val() );
    }

    $("#loader").fadeOut(200);
    $("#popup_container").delay(200).fadeIn(500);

    // ==========================================
    //             No code preview!
    // ==========================================
    
    $("#update_output").click(function() {
        alert(LOCALIZE["disabled-feature"]); //Funckja niedostępna
    });
    
});