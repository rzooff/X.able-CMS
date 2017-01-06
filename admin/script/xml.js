$(document).ready(function() {

    function getVal($id) {
    // --------------------------------
    // $id = jquery element ID
    // --------------------------------
    // RETURNS: <string> element text value
    // --------------------------------
        tag = $id.prop("tagName").toLowerCase();
        if(tag == "textarea") { return $id.val().replace("\n", "[br]"); }
        else if(tag == "input") { return $id.val(); }
        else if(tag == "p") { return $id.text(); }
        else { return $id.attr("value"); };
    };

    function makeXml() {
    // --------------------------------
    // RETURNS: <string> XML output made from HTML CMS form
    // --------------------------------
        xml = "";
        // Article
        $("#cms article").each(function() {
            article_tag = $(this).attr("class");
            xml = xml + "<" + article_tag + ">\n";
            set = ""; // reset media set
            // Section
            $(this).children("section").each(function() {
                section_tag = $(this).attr("class");
                xml = xml + "\t<" + section_tag + ">\n";
                // Attributes
                $(this).children().each(function() {
                    if( (att_tag = $(this).attr("class")) != undefined ) {
                        // ====== Type ======
                        if( att_tag == "type" ) {
                            type = $(this).val();
                            xml = xml + "\t\t<type>" + type + "</type>\n";
                        }
                        // ====== media Set ======
                        else if( att_tag == "set" ) {
                            mode = $(this).find("input").attr("type");
                            $(this).find("input").each(function() {
                                if( $(this).prop("checked") == true ) { set = $(this).val(); };
                            });
                        }
                        // ====== Complex data ======
                        else if( $(this).children().length ) {
                            xml = xml + "\t\t<" + att_tag + ">\n";
                            $(this).children().each(function() {
                                children_tag = $(this).attr("class");
                                children_val = getVal( $(this) );
                                if((children_tag == undefined || children_val == undefined) && $(this).children().length) {
                                    children_tag = $(this).children().attr("class");
                                    children_val = getVal( $(this).children() );
                                };
                                //if(jQuery.type( children_val ) != "string") { children_val = $(this).attr("value"); };
                                xml = xml + "\t\t\t<" + children_tag + ">" + children_val + "</" + children_tag + ">\n";
                            });
                            xml = xml + "\t\t</" + att_tag + ">\n";
                            if(type == "media") {
                                xml = xml + "\t\t<set>" + set + "</set>\n";
                            };
                            // ====== options Selected ======
                            if(type == "option") {
                                selected = [];
                                $(this).closest("section").find("input.option").each(function() {
                                    if( $(this).prop("checked") == true ) { selected[selected.length] = $(this).val(); };
                                });
                                if(selected.length > 0) { selected = selected.join(";"); } else { selected = ""; }; 
                                xml = xml + "\t\t<selected>" + selected + "</selected>\n";
                            };
                        }
                        // ====== Simple data ======
                        else {
                            att_val = getVal( $(this) );
                            xml = xml + "\t\t<" + att_tag + ">" + att_val + "</" + att_tag + ">\n";
                        };
                    };
                });
                xml = xml + "\t</" + section_tag + ">\n";
            });
            xml = xml + "</" + article_tag + ">\n";
        });
        // ====== OUTPUT ======
        return xml;
    };

});