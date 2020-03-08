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
        current_pass = $("#current_password").val();
        new_pass = $("#new_password").val();
        confirm_pass = $("#confirm_password").val();
        if(current_pass.length == 0) {
            infoPopup(LOCALIZE["no-current-password"]); //Musisz podać aktualne hasło
            $("#current_password").focus();
            return false;
        }
        else if(new_pass.length < 6) {
            infoPopup(LOCALIZE["new-password-too-short"]); //Nowe hasło jest za krótkie
            $("#new_password").focus();
            return false;
        }
        else if(new_pass != confirm_pass) {
            infoPopup(LOCALIZE["different-passwords"]); //Podane hasła różnią się
            $("#confirm_password").focus();
            return false;
        }
        else if(new_pass == current_pass) {
            infoPopup(LOCALIZE["same-passwords"]); //Obecne i nowe hasło są takie same
            $("#new_password").focus();
            return false;
        }
        else {
            $(this).blur();
            return true;
        };  
    });
    
    $("button.cancel").click(function() {
        href = $(this).attr("href");
        $("#page_fader, #popup_container").fadeOut(500);
        setTimeout(function() { location.href = href; }, 500);
        $(this).blur();
        return false;
    });
    
    // ==========================================
    //              Initial actions
    // ==========================================
        
    if($("#redirect").length) {
        location.href = $("#redirect").val() + "?popup=" + encodeURIComponent(LOCALIZE["password-changed"] + "|done"); //Hasło zostało zmienione
    }
    else if($("#popup").length) {
        popup = $("#popup").val().split("\|");
        icon = false;
        if(popup.length > 1) {
            icon = popup.pop();
            message = popup.join("\|");
        }
        else {
            message = popup.shift();
        };
        $("#loader").fadeOut(200, function() { infoPopup( message, icon ); });
        $("#popup_container").delay(200).fadeIn(500);
    }
    else {
        $("#loader").fadeOut(200);
        $("#popup_container").delay(200).fadeIn(500);
    };

    // ==========================================
    //             No code preview!
    // ==========================================
    
    $("#update_output").click(function() {
        alert("Funckja niedostępna"); //Funckja niedostępna
    });
    
});