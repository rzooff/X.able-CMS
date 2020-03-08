$(document).ready(function() {
    
    // ==========================================
    //                 FUNCTIONS
    // ==========================================
    
    function validateLogin($input) {
        val = $input.val();
        if(val == "") { alert(LOCALIZE["empty-input-alert"]); }
        else if(val.length < 3) { alert(LOCALIZE["short-text-alert"]); }
        else if(val.indexOf(" ") >  -1) { alert(LOCALIZE["no-spaces-alert"]); }
        else { val = false; };
        // Invalid action
        if(val) {
            $input.css({ "outline": "2px solid orange" }).focus();
            return false;
        }
        else {
            $input.css({ "outline": "none" });
            return true;
        };
    };
        
    function validatePassword($input, $repeat_input) {
        val = $input.val();
        if(val == "") { alert(LOCALIZE["empty-input-alert"]); }
        else if(val.length < 6) { alert(LOCALIZE["short-text-alert"]); }
        else if(val.indexOf(" ") >  -1) { alert(LOCALIZE["no-spaces-alert"]); }
        else { val = false; };
        // Invalid action
        if(val) {
            $input.css({"outline": "2px solid orange"}).focus();
            $repeat_input.prop("disabled", true);
            return false;
        }
        else {
            $input.css({ "outline": "none" });
            $repeat_input.prop("disabled", false);
            return true;
        };
    };
        
    function validateRepeatPassword($input, $pass_input) {
        pass = $pass_input.val();
        if(pass != "") {
            val = $input.val();
            if(val != pass) {
                alert(LOCALIZE["different-passwords"]);
                $input.css({"outline": "2px solid orange"}).focus();
                return false;
            }
            else {
                $input.css({ "outline": "none" }).focus();
                return true;
            }
        }
        else {
            $input.css({ "outline": "none" });
        }
    };
    
    function enableUser() {
        $li = $("#options li.create_admin");
        var disabled = false;
        if($li.find("input.create_admin").prop("checked") == false) { disabled = true; };
        while(($li = $li.next("li")) && $li.length) {
            $input = $li.find("input");
            $input.prop("disabled", disabled);
            //if(disabled) { $input.val(""); }; // Clear inputs
        };
        allFilled();
    }
    
    function allFilled() {
        var filled = true;
        if($("#options li.create_admin input.create_admin").prop("checked") == true) {
            $("input.text").each(function() {
                if($(this).val() == "") { filled = false; };
            });
            if($("input.password").val() != $("input.repeat_password").val()) { filled = false; };
        };
        if(filled) {
            $("#options .buttons .submit").prop("disabled", false).removeClass("off");
        }
        else {
            $("#options .buttons .submit").prop("disabled", true).addClass("off");
        }
    };
    
    // ==========================================
    //                  Summary
    // ==========================================
    
    if($("#post_output input.installer_stage").val() == "summary") {
        $("#summary").show();
        if($("#post_output input.summary_mode").val() == "done") {
            $("#summary li.failed").hide();
            $("#summary button.submit").removeClass("off").prop("disabled", false);
        }
        else {
            $("#summary li.done").hide();
        }
    }
    // ==========================================
    //                  Options
    // ==========================================
    else {
        $("#options").show();
        
        if($("#post_output input.installer_mode").val() == "complete") {
            // Instalation mode
            $("#options li.installer_mode input.complete").prop("checked", true);
            $("#options li.installer_mode input.update").prop("disabled", true).closest("label").css({ "color": "#aaa" });
            // Create user
            $("#options li.create_admin input.create_admin").prop("checked", true).prop("disabled", true).closest("label").css({ "color": "#aaa" });
        }
        else {
            $("#options li.installer_mode input.update").prop("checked", true);
        };
        
        setTimeout(function() {
            $("input.text").val(""); // Kill autofill

            $("input.login").change(function() { validateLogin($(this)); $(".create_admin input").prop("checked", true); });
            $("input.password").change(function() { validatePassword($(this), $("input.repeat_password")); $("input.repeat_password").val(""); });
            $("input.repeat_password").change(function() { validateRepeatPassword($(this), $("input.password")); });
            $("#options li.create_admin input.create_admin").change(function() { enableUser(); });

            $("input.password").keyup(function() { $("input.repeat_password").prop("disabled", false); });
            
            $("input.repeat_password").keyup(function() { allFilled(); });
            
            $("#options .buttons .submit").click(function() {
                if(
                    $("#options li.create_admin input.create_admin").prop("checked") == false ||
                    (
                        validateLogin($("input.login")) &&
                        validatePassword($("input.password"), $("input.repeat_password")) &&
                        validateRepeatPassword($("input.repeat_password"), $("input.password"))
                    )
                ) {
                    $("form input").prop("disabled", false);
                    $("form article").fadeOut(500, function() { return true; })
                }
                else {
                    return false;
                };
            });
            
            enableUser();
        }, 500);
    }
    
    // ==========================================
    // ==========================================
    // ==========================================
    //                  Launch
    // ==========================================
    // ==========================================
    // ==========================================
    
    setTimeout(function() {
        $("form article").fadeIn(500);
    }, 500);

});