// ===================================================
//             X.able JS/jQuery FUNCTIONS
//           (C)2016 maciej@maciejnowak.com
// ===================================================
// build: 20190605
// ===================================================

// ==========================================
//                  FIXES
// ==========================================

// as of 1.4.2 the mobile safari reports wrong values on offset()
// http://dev.jquery.com/ticket/6446
// remove once it's fixed
if(/webkit.*mobile/i.test(navigator.userAgent)) {
    (function($) {
        $.fn.offsetOld = $.fn.offset;
        $.fn.offset = function() {
            var result = this.offsetOld();
            result.top -= window.scrollY;
            result.left -= window.scrollX;
            return result;
        };
    })(jQuery);
};

// ==========================================
//                 Arrays
// ==========================================

/*
Array.prototype.joinValues = function(char) {
// -------------------------------------------
// this = <array> file path
// char = <string> join character
// -------------------------------------------
// RETURNS: <string> Joined array values
// -------------------------------------------
    var input_array = this;
    return Object.keys(input_array).map(function(i){ return input_array[i]; }).join(char);
};
*/

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

function objectToXml(data, depth) {
// -------------------------------------------
// data = <array> or <object> variable(s) data
// depth = <number> array Depth level (optional)
// -------------------------------------------
// RETURNS: <string> Array content converted to XML string form
// -------------------------------------------
    if(typeof depth != "number") { depth = 0; }
    var xml = "";
    var i = "";
    for(i in data) {
        var indent = "";
        for(n = 0; n < depth; n++) { indent = indent + "\t"; };

        if(typeof data[i] == "object" || typeof data[i] == "array") {
            xml = xml + indent + "<" + i + ">\n";
            xml = xml + objectToXml(data[i], depth + 1);
            xml = xml + indent + "</" + i + ">\n";
        }
        else {
            xml = xml + indent + "<" + i + ">" + variableToString(data[i]) + "</" + i + ">\n";
        }
    }
    return xml;
};

function variableToString(string) {
// -------------------------------------------
// string = <string> 
// -------------------------------------------
// RETURNS: <string> Input string with problematic character replaced
// -------------------------------------------
    string = string + ""; // number/boolean to string conversion
    string = string.replace("<", "&lt;").replace(">", "&gt;");
    string = string.replace(/\n|<br>/gi, "[br]")
    return string;
};

// ==========================================
//                 Cookies
// ==========================================

function setCookie(key, value, days) {
// -------------------------------------------
// key = <string> cookie Key
// value = <string> cookie Value
// days = <string> Days to expiration, -1 for reset
// -------------------------------------------
// SETS: cookie data
// -------------------------------------------
    var d = new Date();
    d.setTime(d.getTime() + (days*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = key + "=" + value + ";" + expires + ";path=/";
}

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

Number.prototype.toDigits = function(digits) { // used in: translate()
// -------------------------------------------
// this = <number> or <string>
// num = <integer> minimal digits number
// -------------------------------------------
// RETURN: <string> Integer with specified digits length, with leading zero added id needed
// -------------------------------------------
    val = this;
    val = val + "";
    while(val.length < digits) { val = "0" + val; };
    return val;
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
    characterTest = email.replace(/[^a-z0-9|^@|^.|^!|^#|^$|^%|^&|^?|^'|^*|^_|^\-|^+|^=|^\||^~|^{|^}]/gi, "");
    emailParts = email.split("@");
    if(
        email == characterTest &&
        email.indexOf("..") < 0 &&
        emailParts.length == 2 &&
        emailParts[0] != "-" &&
        emailParts[0] != "_" &&
        emailParts[1].length > 2 &&
        emailParts[1].split(".").length >= 2 &&
        emailParts[1].substr(0, 1) != "." &&
        emailParts[1].substr(emailParts[1].length - 1) != "."
        
    ) {
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
    urlParts = url.split("?").shift().split("://"); // No queries
    if(urlParts.length == 1) { urlParts.unshift("http") };
    characterTest = urlParts[1].replace(/[^a-z0-9|^\-|^_|^.|^~|^\/]/gi, "");
    if(
        urlParts[0].length >= 3 &&
        urlParts[1] == characterTest &&
        urlParts[1].indexOf("//") < 0 &&
        urlParts[1].indexOf("..") < 0 &&
        urlParts[1].indexOf(".") > -1
    ) {
        return url;
    }
    else {
        return false;
    };
};

// ==========================================
//                  String
// ==========================================

String.prototype.killPL = function() {
// -------------------------------------------
// this = <string>
// -------------------------------------------
// RETURNS: <string> With non breaking spaces (HTML)
// -------------------------------------------
    text = this;
    var chars_map = {
        "ą": "a", "ć": "c", "ę": "e", "ł": "l", "ń": "n", "ó": "o", "ś": "s", "ż": "z", "ź": "z",
        "Ą": "A", "Ć": "C", "Ę": "E", "Ł": "L", "Ń": "N", "Ó": "O", "Ś": "S", "Ż": "Z", "Ź": "Z"
    }
    for(i in chars_map) {
        var re = new RegExp(i,"g");
        text = text.replace(re, chars_map[i])

    }
    return text;
};

String.prototype.makeId = function() {
// -------------------------------------------
// this = <string>
// -------------------------------------------
// RETURNS: <string> Simplified to math standards of filename / html tag etc.
// -------------------------------------------
    text = this;
    text = text.killPL().toLowerCase();
    text = text.replace(/ |\n|\t/g, "_");
    text = text.replace(/[^a-z0-9|^&|^_|^\-|^+|^(|^)]/gi, "");
    text = text.replace(/_+/g, "_");
    return text;
};

String.prototype.nbsp = function() {
// -------------------------------------------
// this = <string>
// -------------------------------------------
// RETURNS: <string> With non breaking spaces (HTML)
// -------------------------------------------
    return this.replace(/ /g, "&nbsp;");
};

String.prototype.capitalize = function(mode) { // used in: translate()
// -------------------------------------------
// this = <string>
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
// this = <string> file path
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

function autoUrl(href) {
// -------------------------------------------
// href = <string>
// -------------------------------------------
// RETURN: <string> Input HREF (URL) with encoded queries part
// -------------------------------------------
    href = href.split("?");
    if(href.length > 1) { href = href.shift() + "?" + encodeURIComponent(href.join("?")); }
    else { href = href.shift(); };
    return href;
};

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

function dateToString(format, date) {
// -------------------------------------------
// format = <string> output format
// date = <object> time/date object, if not specified -> currend date/time will be used
// -------------------------------------------
// RETURN: <string> time data in specified format
// -------------------------------------------
    if(jQuery.type(date) != "date") { date = new Date(); }
    iso_weekday = [ 7, 1, 2, 3, 4, 5, 6 ];
    var keys = {
        yyyy: date.getFullYear(),                   // Year, full
        dd: date.getDate().toDigits(2),             // Day, 2 digits
        D: date.getDate(),                          // Day
        mm: (date.getMonth() + 1).toDigits(2),      // Month, 2 digits
        M: (date.getMonth() + 1),                   // Month
        ho: date.getHours().toDigits(2),            // Hours, 2 digits
        mi: date.getMinutes().toDigits(2),          // Hours, 2 digits
        se: date.getSeconds().toDigits(2),          // Hours, 2 digits
        W: iso_weekday[ date.getDay() ],            // ISO-8601 weekday number (1=Monday, 7=Sunday)
        w: date.getDay(),                           // Weekday number (0=Sunday, 6=Saturday)
    }
    for(i in keys) {
        key = new RegExp(i, "g");
        format = format.replace(key, keys[i]);
    };
    return format;
};

function getMonthDays(date, option) {
// -------------------------------------------
// date = <object> Date object
// option = <string> "week": from Monday to Sunday containing all month days (optional)
// -------------------------------------------
// RETURNS: <array> Month days objects list + optional prefix/suffix days to complete full weeks table
// -------------------------------------------
    var nextMonth = new Date(date.getFullYear(), date.getMonth() + 1, 1);
    var nextDay = new Date(date.getFullYear(), date.getMonth(), 1);
    // ====== Options ======
    if(option == "week") {
        iso_weekday = [ 7, 1, 2, 3, 4, 5, 6 ];
        // Add month prefix
        diff = iso_weekday[nextDay.getDay()] - 1;
        if(diff > 0) {
            nextDay.setDate(nextDay.getDate() - diff);
        };
        // Add month suffix
        diff = 8 - iso_weekday[nextMonth.getDay()];
        //alert(diff);
        if(diff > 0) {
            nextMonth.setDate(nextMonth.getDate() + diff);
        }
    }
    // ====== Get days loop ======
    monthDays = [];
    while(nextDay < nextMonth)  {
        //alert(dateToString("yyyy-mm-dd (W)", nextDay))
        monthDays[monthDays.length] = new Date(nextDay);
        nextDay.setDate(nextDay.getDate() + 1);
    }
    return monthDays;
};

function textareaResize($textarea, min_height) {
// -------------------------------------------
// $textarea = <element> textarea jquery element
// min_height = <integer> min-height in [px]
// -------------------------------------------
// Automatic textarea height resize
// --------------------------------
    $textarea.height(min_height);
    if($textarea[0].scrollHeight > min_height) {
        $textarea.height($textarea[0].scrollHeight);
    };
};

$.fn.selectRange = function(start, end) {
// -------------------------------------------
// start = <integer> selection start position
// end = <integer> selection end position (optional)
// -------------------------------------------
// Set cursor/selection position in textarea
// -------------------------------------------
    if(end == undefined) {
        end = start;
    }
    return this.each(function() {
        if('selectionStart' in this) {
            this.selectionStart = start;
            this.selectionEnd = end;
        } else if(this.setSelectionRange) {
            this.setSelectionRange(start, end);
        } else if(this.createTextRange) {
            var range = this.createTextRange();
            range.collapse(true);
            range.moveEnd('character', end);
            range.moveStart('character', start);
            range.select();
        }
    });
};

function urlQuery(actions) {
// -------------------------------------------
// actions = <string> for query modifiy, or <false> to delete all,
//              options (multiple divided with comas):
//              "key=value": Replace query
//              "+key=value": Add (or modify) query
//              "-key": Delete query
//              "key": Read query (no modification)
// -------------------------------------------
// MODIFIES: current URL (without reloading a page) / or RETURNS: <string> Query value(s) in read mode separated with comas
// -------------------------------------------
    // ====== Output variables ======
    var read = [];
    var queries = {};
    // ====== Get all gata ======
    var original_url = location.href; // Original url HREF
    var url = original_url.split("?");
    var url_base = url[0];
    if(url.length == 2) { url = url[1].split("&"); } else { url = []; };
    for(i in url) {
        query = url[i].split("=");
        if(query.length == 2) {
            queries[ query[0] ] = query[1];
        };
    };
    // ====== Actions ======
    if(typeof(actions) != "string") {
        queries = {}; // Delete all
    }
    else if(actions.length) {
        actions = actions.split(",");
        for(i in actions) {
            action = actions[i];
            key_val = action.split("=");
            // +key=value -> add/mod value
            if(action.substr(0, 1) == "+" && key_val.length == 2) {
                queries[ key_val[0].substr(1) ] = key_val[1];
            }
            // key=value -> mod existing value
            else if(key_val.length == 2 && queries[ key_val[0] ]) {
                queries[ key_val[0] ] = key_val[1];
            }
            // -key -> delete
            else if(action.substr(0, 1) == "-") {
                delete queries[ action.substr(1) ];
            }
            // key -> READ value
            else if(action.length > 0 && queries[ action ]) {
                read[read.length] = queries[ action ];
            };
        }
    };
    // ====== Build new URL ======
    var url = [];
    for(key in queries) {
        value = queries[key];
        url[url.length] = key + "=" + value;
    };
    if(url.length) {
        updated_url = url_base + "?" + url.join("&");
    }
    else {
        updated_url = url_base;
    }
    // ====== Output ======
    // Modify URL
    if(updated_url != original_url) {
        //alert("updated URL:\n" + updated_url);
        window.history.pushState(window.location.href, document.title, updated_url);
    }
    else {
        //alert("URL not changed");
    }
    // Return value(s)
    if(read.length) {
        //alert("read VALUES:\n" + read);
        return read.join(",");
    };
};
