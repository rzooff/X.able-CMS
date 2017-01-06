// ===================================================
//               X.able PHP FUNCTIONS
//           (C)2016 maciej@maciejnowak.com
// ===================================================
// compatibility: php5.3+
// build: 20161006
// ===================================================

// ==========================================
//                 Arrays
// ==========================================

function arrayIntersection(array_1, array_2) {
// --------------------------------
// array_1 = <array>
// array_2 = <array>
// --------------------------------
// RETURN: <array> intersection of input arrays or <false> if no intersection found
// --------------------------------
    intersection = [];
    // Search speed optimalisation -> shorter loop
    if(array_1.length < array_2.length) { shorter = array_1; longer = array_2; }
    else { shorter = array_2; longer = array_1; };
    // Find intersections
    for(i = 0; i < shorter.length; i++) {
        if(longer.indexOf(shorter[i]) > -1) {
            intersection[intersection.length] = shorter[i];
        };
    };
    // Output
    if(intersection.length > 0) { return intersection; } else { return false; };
};

// ==========================================
//                 Numbers
// ==========================================

function digits(int, num) { // used in: currentTime(format)
// -------------------------------------------
// int = <integer> INTeger
// num = <integer> digits NUMber
// -------------------------------------------
// RETURN: <string> Integer with specified digits length, with leading zero added id needed
// -------------------------------------------
    int = int + ""; // convert to string
    while(int.length < num) { int = "0" + int; };
    return int;
};

// ==========================================
//                Validations
// ==========================================

function validateEmail(email) { // used in: applyStyle()
// --------------------------------
// Check if string is valid email
// --------------------------------
// RETURN: <string> input email or <false> if check failed
// --------------------------------
    nick = email.split("@");
    if(nick.length == 2 && nick.pop().split(".").length >= 2) {
        return email;
    }
    else {
        return false;
    };
};

function validateUrl(url) { // used in: applyStyle()
// --------------------------------
// Check if string is valid url
// --------------------------------
// RETURN: <string> input url (with added "http://" if needed) or <false> if check failed
// --------------------------------
    invalid = [" ", "@", "=\""]; // Invalid charaters
    if(url.split(".").length > 1) {
        if(url.substr(0, 7) != "http://" && url.substr(0, 8) != "https://") { url = "http://" + url; };
        return url;
    }
    else {
        return false;
    };
};

// ==========================================
//                  String
// ==========================================

String.prototype.capitalize = function(mode) { // used in: translate()
// -------------------------------------------
// mode = <string> method MODE
//     "string", undefined -> eg: "Lorem ipsum. lorem ipsum." (default)
//     "sentence", "paragraph" -> eg: "Lorem ipsum. Lorem ipsum."
//     "title", "word" -> eg: "Lorem Ipsum. Lorem Ipsum."
// -------------------------------------------
// RETURNS: <string> Capitalized input string
// -------------------------------------------
    if(mode == "sentence" || mode == "paragraph") {
        sentences = this.split(". ");
        for(n = 0; n < sentences.length; n++) { sentences[n] = sentences[n].capitalize(); };
        return sentences.join(". ");
    }
    else if(mode == "title" || mode == "word") {
        words = this.split(" ");
        for(n = 0; n < words.length; n++) { words[n] = words[n].capitalize(); };
        return words.join(" ");
    }
    else { // "string"
        return this.substr(0, 1).toUpperCase() + this.substr(1).toLowerCase();
    };
};

String.prototype.path = function(mode) { // used in: *common*
// -------------------------------------------
// mode = <string> method MODE
//     undefined -> "folder/file.txt"
//     "basename"-> eg: "file" or last folder in specified path
//     "dirname" -> eg: "folder"
//     "extension" -> eg: "txt"
//     "filename" -> eg: "file.txt"
// -------------------------------------------
// RETURNS: <string> Specified path data part
// -------------------------------------------
    // Path analyze
    dirname = this.split("/");

    if(dirname.length > 1) { // folder
        basename = dirname.pop();
        dirname = dirname.join("/");
    }
    else { // no folder
        basename = dirname.shift();
        dirname = "";
    };
    // File analyze
    filename = basename.split(".");
    if(filename.length > 1) { // extension
        extension = filename.pop();
        filename = filename.join(".");
    }
    else { // no extension -> no file
        basename = filename.shift();
        if(dirname == "") { dirname = basename; }
        else { dirname = dirname + "/" + basename; };
        filename = "";
        extension = "";
    };
    // OUTPUT
    if(mode == "basename") { return basename; }
    else if(mode == "dirname") { return dirname; }
    else if(mode == "extension") { return extension; }
    else if(mode == "filename") { return filename; }
    else { return this; };
};

// ==========================================
//               Miscleanous
// ==========================================

function currentTime(format) { // used in: initializeNewPost()
// -------------------------------------------
// format = <string> output format, eg: "yyyy-dd-mm" -> "2016-02-11"
// -------------------------------------------
// RETURN: <string> time data in specified format
// -------------------------------------------
    currentdate = new Date();
    time = {
        yyyy: currentdate.getFullYear(),
        mm: digits(currentdate.getMonth()+1, 2),
        dd: digits(currentdate.getDate(), 2),
        ho: digits(currentdate.getHours(), 2),
        mi: digits(currentdate.getMinutes(), 2),
        se: digits(currentdate.getSeconds(), 2)
    };
    for(key in time) {
        format = format.replace(key, time[key]);
    };
    return format;
};

function textareaResize($textarea, min_height) {
// --------------------------------
// $textarea = <element> textarea jquery element
// min_height = <integer> min-height in [px]
// --------------------------------
// Automatic textarea height resize
// --------------------------------
    $textarea.height(min_height);
    if($textarea[0].scrollHeight > min_height) {
        $textarea.height($textarea[0].scrollHeight);
    };
};

function urlQuery(action) {
// --------------------------------
// action = <string> for query modifiy, or <false> to delete, options:
//              "key=value": Replace query(ies)
//              "+key=value": Add (or modify) query
//              "-key": Delete query
//              "key": Read query (no modification)
// --------------------------------
// MODIFIES: current URL (without reloading a page) / or RETURNS: <string> Query value in read mode
// --------------------------------
    url = location.href.split("?");
    href = url[0];
    if(url.length == 2) { query = url[1].split("&"); } else { query = []; };
    // check action
    if(action == false || jQuery.type(action) != "string" || action == "") {
        query = false;
    }
    else if(action.substr(0, 1) == "+" || action.substr(0, 1) == "-") {
        // Delete existing variable (if any)
        temp = [];
        key = action.substr(1).split("=").shift();
        for(i in query) {
            item_key = query[i].split("=").shift();
            if(item_key != key) { temp[temp.length] = query[i]; };
        };
        // Add variable
        if(action.substr(0, 1) == "+") {
            temp[temp.length] = action.substr(1);
        };
        query = temp;
    }
    else if(action.split("=").length > 1) {
        query = [ action ];
    }
    else if(action.indexOf(" ") < 0) {
        url = false;
        for(i in query) {
            item = query[i].split("=")
            item_key = item.shift();
            item_val = item.join("=");
            if(item_key == action) { val = item_val; };
        };
    }
    else {
        query = false;
    };
    if(url) {
        // Add queries
        if(query && query.length) {
            url = url[0] + "?" + query.join("&");
        }
        else {
            url = url[0];
        };
        // Modify url
        //alert("url: " + url);
        title = document.title;
        window.history.pushState(window.location.href, title, url);
    }
    else {
        //alert("val: " + val);
        return val;
    };
};

