$(document).ready(function() {

    var ANIMATION_TIME = 500;
    
    var users_path = ".login/users";
    
    $("nav dd .action-remove-page").each(function() {
        href = $(this).attr("value");
        if(href.indexOf(users_path + "/") > -1 && href.path("extension") == "xml") {
            //alert($(this).get(0).outerHTML);
            $(this).addClass("members_login-remove");
        }
    })
    $("nav dd.add_page .item_label").each(function() {
        href = $(this).closest("dd").find(".folder_path").val();
        if(href.indexOf(users_path) > -1) {
            $(this).click(function() {
                setTimeout(function() { // give a time for popup
                    $popup = $("#popup_box");
                    if($popup.length) {
                        // Change title
                        $popup.find("p").first().text(LOCALIZE["new-user"]);
                        // Disable confirm button
                        $popup.find("button.confirm").prop("disabled", true);
                        $email = $popup.find("input").first();
                        $email.attr("placeholder", LOCALIZE["enter-email"]);
                        // Enable confirm button when valid email is entered
                        $email.change(function() {
                            email = $email.val();
                            if(validateEmail(email)) {
                                $popup.find("button.confirm").prop("disabled", false);
                            }
                            else {
                                alert(LOCALIZE["enter-email"]);
                                $email.focus();
                            }
                        })
                    }
                }, 100)
                
            })
        }
    });
    
    // ==========================================
    //                Mailer XML
    // ==========================================
    
    function mailerXml(email, key, variables) {
    // ------------------------------------------
    // key = <string> Action: settings article tag
    // variables = <object> strings to @replace with variables: { key: string, ... }, busy keys: @login, @email
    // ------------------------------------------
    // RETURNS: <string> XML _mailer data
    // ------------------------------------------
        if(typeof variables != "object") { variables = {}; };
        // Get user data
        var $user = $("article.user");
        variables["email"] = email; //$user.find("section.email input.string").val();

        // Get settings data
        var from = $("#members_login-mailer input.from").val();
        var subject = $("#members_login-mailer ." + key + "_subject").val();
        var message = "";
        if($("#members_login-mailer ." + key + "_message").length) {
            message = message + $("#members_login-mailer ." + key + "_message").val()
            // Cut trailing enter(s)
            while(message.substr(message.length - 4) == "[br]") { message = message.substr(0, message.length - 4); }
        }
        message = message + "[br][br]" + $("#members_login-mailer").find(".admin_signature").val();
        
        // Variables replacement        
        for(i in variables) {
            var re = new RegExp("@" + i ,"g");
            message = message.replace(re, variables[i]);
        }
        // Output
        mailer = {
            "_mailer": {
                "to": email,
                "from": from,
                "subject": subject,
                "message": message
            }
        }
        return mailer;
    };
    
    // ==========================================
    //                  Popup
    // ==========================================
    
    function showPopup(icon, info, buttons, email, subject, message) {
    // ------------------------------------------
    // text = <string>
    // buttons = <string> BUTTONS labels
    // input = optional: <boolean> show INPUT; or <string> input placeholder content
    // ------------------------------------------
    // Adds & show page fader & mailto popup box
    // ------------------------------------------
 
        $("#page_fader, #popup_container").remove();
        html = "<div id='page_fader'></div>";
        html = html + "<div id='popup_container'><div id='popup_box'>";
        html = html + "<h6><span class='" + icon + "'></span></h6>";
        html = html + "<p class='info'>" + info + "</p>";
        html = html + "<input type='email' name='to' value='" + email + "' disabled>";
        html = html + "<input type='text' name='subject' value='" + subject + "'>";
        if(message == undefined || message == false) { message =""; };
        html = html + "<textarea name='message' placeholder='" + LOCALIZE["your-message"] + "'>" + message + "</textarea>";
        
        // Buttons
        buttons = buttons.split(",");
        if(buttons.length == 2) {
            html = html + "<div class='buttons two'><button class='confirm'>" + buttons[0] + "</button><button class='cancel'>" + buttons[1] + "</button></div>";
        }
        else {
            html = html + "<div class='buttons one'><button class='confirm'>" + buttons[0] + "</button></div>";
        };
        // End
        html = html + "</div></div>"; // close popup box & container
        $("body").append( html );

        $("#page_fader, #popup_container").fadeIn(500);
        $("#popup_container").fadeIn(250, function() {
            if( $("#popup_container textarea").length ) { $("#popup_container textarea").first().focus(); };
        });
        
    };

    function hidePopup() {
    // ------------------------------------------
    //    Hide & remove page fader & popup box
    // ------------------------------------------
        $("#page_fader, #popup_container").fadeOut(250, function() { $(this).remove(); });
        $(document).unbind("keyup");
    };
    
    // ==========================================
    //            CREATE new user
    // ==========================================

    if($("article.user section.email").length) {
        // Creating NEW USER
        if($("#edit_path").val().indexOf(".template") > -1 && $("#edit_path").val() != $("#save_path").val()) {
            $email = $("article.user section.email input.string");

            // ====== Hide ADMIN actions ======
            $("article.admin").hide();

            // Autofill email
            user_email = $("#user_filename").val();
            if(validateEmail(user_email)) { $email.val( user_email ); }; 
    
            // ====== Force valid email on Publish ======
            $("header button.publish").click(function() {
                if(validateEmail($email.val())) {
                    return true;
                }
                else {
                    alert(LOCALIZE["valid-address-alert"]);
                    $email.focus();
                    return false;
                };
            })
        }
    };

    // ==========================================
    //              ACTION BUTTONS
    // ==========================================
    
    $("article.admin button.action_button").each(function() {
        action = $(this).attr("href").split(":").pop();
        $(this).addClass("members_login-" + action).unbind("click");
    });
    
    // ====== Send Message ======
    $("article.admin button.members_login-send_message").click(function() {
        email = $("article.user section.email input.string").val();
        subject = $("#members_login-mailer .admin_subject").val();
        message = "[br][br]" + $("#members_login-mailer .admin_signature").val();

        showPopup("fi-mail", LOCALIZE['send-info-label'], LOCALIZE["send-cancel-buttons"], email, subject, "\n" + message.replace(/\[br\]/g, "\n"));
        
        // ====== Send ======
        $("#popup_box .confirm").click(function() {
            var xml = mailerXml(email, "admin");
            xml["_mailer"]["message"] = $("#popup_box textarea").val().replace(/\n/g, "[br]");
            // Put into <form> & send
            var $form = $("form#cms");
            // Check changes???
            $form.append("<textarea style='display:none' name='session_temp'>" + objectToXml(xml) + "</textarea>");
            $form.submit();
            //alert(objectToXml(xml));
        });
        
        // ====== Cancel ======
        $("#popup_box .cancel").click(function() { hidePopup(); });
        
        return false;
    });
    
    // ====== Reset Password ======
    $("article.admin button.members_login-reset_password").click(function() {
        if(confirm(LOCALIZE["are-you-sure"])) {
            email = $("article.user section.email input.string").val();
            var get_data = [
                "back_url=" + location.href.replace(/^(?:\/\/|[^\/]+)*\//, "") + encodeURIComponent("&popup=Reset password link sent"),
                "action=password_reset",
                "email=" + encodeURIComponent(email)
            ];
            //alert($("input#root").val() + "/_login.php?" + get_data.join("&"));
            location.href = $("input#root").val() + "/_login.php?" + get_data.join("&");
        };
        return false;
    })
    
    // ==========================================
    //            DELETE user button
    // ==========================================

    $("nav .members_login-remove").click(function() {

        // Variables
        href = $(this).attr("value");
        path = href.split("?path=").pop().split("&").shift();
        email = $(this).closest(".nav_item").find(".item_label").text();
        var $form = $("form#cms");
        var xml = {
            "_remove_user": {
                "path": path,
                "email": email
            }
        }

        // Send info
        if(confirm("Send remove info to user?")) {
            mailer = mailerXml(email, "remove");
            var xml = Object.assign(xml, mailer);
            //alert(objectToXml(xml));
        }
        // Add to <form>
        $form.append("<textarea class='members_login-remove' style='display:none' name='session_temp'>" + objectToXml(xml) + "</textarea>");
        //alert(objectToXml(xml));
        // Bind cancel on popup confirm form
        setTimeout(function() {
            // Modify filename -> email
            var filename = new RegExp(href.path("basename"), "g");
            info_text = $("#popup_box p.info").html().replace(filename, email);
            $("#popup_box p.info").html(info_text);
            // Cancel button
            $("#popup_box .cancel").click(function() {
                $form.find("textarea.members_login-remove").remove();
            });
        }, 100)
    });
    
    // ==========================================
    //           Sent GROUP change info
    // ==========================================
    
    // ====== Send group change info
    if($("article.user ._category").length && $("#edit_path").val().indexOf(".template") < 0) {
        var groups_init = $("article.user ._category input.string").val();
        //alert(groups_init);
        $("header button.publish").click(function() {
            groups = $("article.user ._category input.string").val();
            if(groups != groups_init && confirm("Send group change info to user?")) {
                
                email = $("article.user section.email input.string").val();
                
                // ====== Get Groups ======
                var $user = $("article.user");
                var groups_keys = $user.find("section._category input.string").val().split(";");
                var groups = [];
                for(i in groups_keys) {
                    groups[groups.length] = CATEGORIES_LIST[ groups_keys[i] ];
                }
                //alert(groups);

                // Put to <form>
                var $form = $("form#cms");
                var xml = mailerXml(email, "group", { groups: groups.join(", ") });
                $form.append("<textarea style='display:none' name='session_temp'>" + objectToXml(xml) + "</textarea>");
                $form.submit();
                return false;
            }
            else {
                // No changes in group membership
            };
            
        });
    };

})