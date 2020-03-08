var ANIMATION_TIME = 500;

$(document).ready(function() {
    
    // ==========================================
    //                 Functions
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
        else if(icon == "alert") { icon = "fi-alert"; }
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
    //                Form actions
    // ==========================================

    //$("button.cancel").click(function() { location.href = "../"; return false; });
    $("button.confirm").click(function() {
        $(this).blur();
        login = $("input#login").val();
        pass = $("input#password").val();
        if( $("input#login").val() == "" ) {
            alert(localize("login-input"));
            $("input#login").focus();
            return false;
        }
        else if( $("input#password").val() == "" ) {
            alert(localize("password-input"));
            $("input#password").focus();
            return false;
        }
        else {
            return true;
        };
    });

    $(document).keyup(function(e) { if (e.keyCode == 13) { $("form#password").submit(); } }); // Esc key

    // ==========================================
    //              Reset Password
    // ==========================================

    function initializeResetPassword() {
        login = $("#login").val();
        if(login != "" && validateEmail(login)) {
            $(".reset_password").slideDown(ANIMATION_TIME / 2);
        }
        else {
            $(".reset_password").slideUp(ANIMATION_TIME / 2);
        }
    }
    initializeResetPassword()
    $("input#login").on('change keyup paste input blur', function() {
        initializeResetPassword();
    })

    
    $(".reset_password").click(function() {
        $form = $(this).closest("form");
        login = $("#login").val()

        if(login.trim() == "") {
            infoPopup("Najpierw wpisz swój login", "alert")
        }
        if(!validateEmail(login)) {
            infoPopup("Login nie jest adresem email", "alert")
        }
        else {
            $("#info_box").remove();
            html = "<div id='info_box'>";
            html = html + "<h3>" + LOCALIZE["reset-password"] + "</h3>";
            html = html + "<p class='info'>" + LOCALIZE["reset-password-html"] + "</p>";
            html = html + "<div class='buttons'><button class='confirm'>" + LOCALIZE["reset-label"] + "</button><button class='cancel'>" + LOCALIZE["cancel-label"] + "</button></div>";

            html = html + "</div>"; // close popup box & container


            $("body").append(html);

            $("#info_box .buttons .cancel").click(function() {
                $("#info_box").fadeOut(ANIMATION_TIME, function() { $(this).remove(); });
            })                    
            $("#info_box .buttons .confirm").click(function() {
                $form.attr("action", "reset_password.php?action=reset").submit();
            })
        };
    })
    
    // ==========================================
    //               Launch page
    // ==========================================

    $("#loader").fadeOut(200);
    $("#popup_container").delay(200).fadeIn(200);

    urlQuery();
});