$(document).ready(function() {
    
    urlQuery();
	
	// Add (class) names to all inputs
	$("form select, form input").each(function() { $(this).attr("name", $(this).attr("class")); });
	
    // ==========================================
    //                 Edit ini
    // ==========================================

    var content = "";
    
    // Update editor style
    function updateEditor($element) {
        content = $element.html();
        content = content.replace(/<br>/gi, "\n");
        content = content.replace(/<div>/gi, "\n");
        content = content.replace(/<\/div>/gi, "");
        content = content.replace(/<(?:.|\n)*?>/gm, '');
        styled = content.split("\n");
        for(i in styled) {
            line = styled[i];
            if(line.trim().substr(0, 1) == ";") {
                line = "<span class='comment'>" + line + "</span>";
            };
            styled[i] = line;
        };
        $element.html( styled.join("<br>") );
        //return content;
    };

    // ====== Delete Group ======
	$("span.delete").click(function() {
		file = $(this).closest("span.group").find(".ini").attr("value");
        if(file.path("filename") == "dev") {
            alert("Nie można usunąć grupy developera!");
        }
        else if(file.path("filename") == "dev") {
            alert("Nie można usunąć własnej grupy!");
        }
		else if(confirm("Czy na pewno chcesz usunąć grupę '" + file.path("filename") + "' ?")) {
			location.href = "_save-ini.php?delete_group=" + file;
		};
	});
    
    // ====== Open Group ini Editor ======
	$("span.ini").click(function() {
		file = $(this).attr("value");
		if($("#ini_content").find("#" + file.path("filename")).length) {
			content = $("#ini_content").find("#" + file.path("filename")).val();
		}
		else {
			content = $("#ini_content").find("#dev").val();
		};
		popup = "<div id='popup_container'>" +
				"<form class='edit_popup' action='_save-ini.php' method='post'>" +
					"<div class='buttons'><button name='accept' class='accept' value='accept'>Zapisz</button><button class='cancel' value='accept'>Anuluj</button></div>" +
					"<input class='file' type='text' name='file' value=''>" +
					"<div class='textarea' contenteditable>[group]<br>; key = value</div>" +
                    "<textarea name='content'></textarea>" +
				"</form>" +
			"</div>";
		$("body").prepend(popup);
        content= content.replace(/</g, "&lt;");
        content= content.replace(/>/g, "&gt;");
		$("#popup_container").fadeIn(200, function() {
            main_ini = $("#main_ini").attr("value");
            filename = $("#popup_container .file").val();
            if(main_ini == filename) {
                $("#popup_container .file").prop("disabled", true);
            }
            else {
                $("#popup_container .file").prop("disabled", false);
            };
        });
		$("#popup_container .file").val(file);
		$("#popup_container .textarea").html(content.replace(/\n/g, "<br>"));
        
        // Update style
        $("#popup_container .textarea").on("blur", function() { updateEditor($(this)); });
        
        /*
        $("#popup_container .textarea").on('keypress', function (e) {
            $textarea = $(this);
            //alert(e.keyCode);
            keycodes = [ 13, 8, 46, 59 ];
            if(keycodes.indexOf(e.keyCode) > -1) {
                setTimeout(function() {
                    updateEditor($textarea);
                }, 500);
            };
        });
        */
        
        updateEditor( $("#popup_container .textarea") );
        
        // ====== Cacel ======
		$(".edit_popup .cancel").click(function() {
			$("#popup_container").fadeOut(200, function() { $(this).remove(); });
			return false;
		});
        
        // ====== Save ======
		$(".edit_popup .accept").click(function() {
            $("#popup_container textarea").html( content );
			if($("#popup_container .file").val() == "") {
				alert("Brak nazwy pliku!");
				return false;
			}
			else if($("#popup_container .file").val().toLocaleLowerCase() == "xable") {
				alert("Nazwa \"xable\" jest zastrzeżona!");
				return false;
			}
			else if(confirm("Czy na pewno zapisać zmiany?")) {
                $("#popup_container .file").prop("disabled", false);
                file = $("#popup_container .file").val();
				if(file.path("extension") != "ini") {
					$("#popup_container .file").val(file + ".ini");
				};
				$(this).closest("form").submit();
			}
			else {
				return false;
			};
		});

	});
    
    //$("span.group").last().hide();
	
    // ==========================================
    //                 Menu bar
    // ==========================================

	// Show menu dropdown
    $("nav label.menu").mouseenter(function() {
        $(this).find("ul").stop().show(200);
        $(this).find("p").css({ "opacity": "0.25" });
    });

	// Hide menu dropdown
    $("nav label.menu").mouseleave(function() {
        $(this).find("ul").stop().hide(100);
        $(this).find("p").css({ "opacity": "1" });
    });
    
	// Menu actions
	$("nav li").click(function() {
		// Hide dropdown
        $(this).closest("label").find("ul").stop().hide(100);
        $(this).closest("label").find("p").css({ "opacity": "1" });
        // Actions
		action = $(this).html().replace(/&nbsp;/g, "_").toLowerCase();
		if(action == "quit") {
            location.href = "index.php";
        }
		else if(action == "creator" || action == "update" || action == "explorer") {
            location.href = "xable_" + action + ".php";
        }
        else {
            alert("Unimplemented: " + action)
        };
    });
    
    // ==========================================
    //                   Edit
    // ==========================================
    
    $("#popup_container .cancel").click(function() {
        $("#popup_container, .popup").fadeOut(200);
    });
    
	$("main .delete_user").click(function() {
		$(this).blur();
		login = $(this).closest("tr").find(".login").text();
		if(confirm("Delete user: '" + login + "'\nAre you sure?")) {
			$form = $("form#delete_user");
			$form.find(".login").val(login);
			$form.submit();
		}
		else {
			return false;
		};
	});
    
	$("main tr .group").change(function() {
		$(this).blur();
		login = $(this).closest("tr").find(".login").text();
		group = $(this).val();
		if(confirm("Change group for user: '" + login + "'\nAre you sure?")) {
			$form = $("form#change_group");
			$form.find(".login").val(login);
			$form.find(".group").val(group);
			$form.submit();
		}
		else {
			location.reload();
		};
	});
	
    function verify($input) {
		val = $input.val();
		title = $input.attr("class").capitalize().replace("_", " ");
		exisitingUsers = $("input#users").val().split(" ");
		//alert(exisitingUsers.indexOf(val));
        if(val == "") {
            alert(title + " can't be empty");
			$input.focus();
			return false;
        }
        else if(title == "Login" && exisitingUsers.indexOf(val) > -1) {
            alert(title + " already exists");
			$input.focus();
			return false;
        }
        else if(title == "Login" && val.length < 3 || title != "Login" && val.length < 6) {
            alert(title + " is too short");
			$input.focus();
			return false;
        }
        else if(val.indexOf(" ") > -1) {
            alert(title + " can't contain any space");
			$input.focus();
			return false;
        }
        else {
            return true;
        };
    };
	
	//alert($("input#users").val().split(" "));
	
	$("main .change_password").click(function() {
		$(this).blur();
		login = $(this).closest("tr").find(".login").text();
		$("#change_password").find(".login").val(login);
        $("#popup_container, #change_password").fadeIn(200);
        $("#new_user option").last().prop("selected", true);
	});
	
    $("#change_password .confirm").click(function() {
        $(this).blur();
        $form = $(this).closest("form");
		if(verify($form.find(".new_password"))) {
			if($form.find(".new_password").val() == $form.find(".new_repeat").val()) {
                $form.find(".login").removeProp("disabled");
				$form.attr("action", "xable_users.php?action=change_password");
				return true;
			}
			else {
				alert("Passwords don't match");
				$form.find(".repeat").focus();
				return false;
			};
		}
        else {
            $form.find(".login").prop("disabled", true);
			return false;
		};
    });
	
    $("#change_password .test").click(function() {
        $(this).blur();
        $form = $(this).closest("form");
		if(verify($form.find(".current_password"))) {
            $form.find(".login").removeProp("disabled");
			$form.attr("action", "xable_users.php?action=test_password");
			return true;
		}
        else {
			return false;
		};
    });
    
    $("main .new_user").click(function() {
		$(this).blur();
        $("#popup_container, #new_user").fadeIn(200);
        $("#new_user option").last().prop("selected", true);
    });
 
    $("#new_user .confirm").click(function() {
        $(this).blur();
        $form = $(this).closest("form");   
		
		if(verify($form.find(".login")) && verify($form.find(".password"))) {
			if($form.find(".password").val() == $form.find(".repeat").val()) {
				$form.attr("action", "xable_users.php?action=new_user");
				return true;
			}
			else {
				alert("Passwords don't match");
				$form.find(".repeat").focus();
				return false;
			};
		}
        else {
			return false;
		};
    });
    
});