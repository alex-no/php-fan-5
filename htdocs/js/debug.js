/*
Array.prototype.toString = Object.prototype.toString = function()
{
    var addslashes, cont, v, vs, k;
    addslashes = function(s)
    {
        return s.split('\\').join('\\\\').split('"').join('\\"');
    }
    cont = [];
    for (k in this) {
        if (cont.length) cont[cont.length - 1] += ",";
        v = this[k];
        try {
            vs = v.constructor == String ? '"' + addslashes(v) + '"' : v.toString();
        } catch(e) {
            vs = "~~ Couldn't get data ~~";
        }
        cont[cont.length] = this instanceof Array ? vs : k + ": " + vs
    }
    cont = "    " + cont.join("\n").split("\n").join("\n    ");
    return this.constructor == Object ? "{\n" + cont + "\n}" : (this instanceof Array ? "[\n" + cont + "\n]" : cont);
}
*/
function propA(obj, depth)
{
    new alertProperty(obj, depth);
}
function propP(obj, depth)
{
    new showProperty(obj, depth);
}
function propC(obj, depth)
{
    if (typeof(console) == 'object') {
        new consoleProperty(obj, depth);
    } else {
        new alertProperty(obj, depth);
    }
}
var prop = propC;

var showProperty = function(obj, depth)
{
    this.objects = {};
    if (typeof(depth) != "undefined") this.depth = depth;
    if (this.is_property(obj)) this.objects["root"] = obj;
    var s = this.get_property(obj, 0, 'root');
    if (s) {
        window.open("", "", "toolbar=no,scrollbars=yes,resizable=yes").document.write('<hi style="font-size:20px;font-family:Times,serif;">' + this.get_object_name(obj) + '</h1><hr/><pre style="font-size:12px;font-family:Courier,monospace;line-height:16px;">' + s + '</pre>');
    }
    else alert("There is empty element for show property!");
}
showProperty.prototype = {
    indent : 4,
    depth  : 1,
    showFunc : false,
    use_tag  : true,
    colors : {
        "number"    : "0000EE",
        "string"    : "009900",
        "boolean"   : "00BBBB",
        "object"    : "000000",
        "function"  : "BB5E00",
        "null"      : "0095C4",
        "undefined" : "999999",
        "unknown"   : "FFCC33",
        "not_get"   : "FF0000"
    },
    objects : null,
    get_property : function(obj, depth, path)
    {
        var ret, v, t, p, k;
        if (this.get_type(obj) != "object") {
            return obj + "";
        }
        ret = "";
        for (p in obj) {
            try {
                v = obj[p];
                t = this.get_type(v);
                if (t == "object") {
                    if (v == null) {
                        v = this.use_tag ? "<i>NULL</i>" : "NULL";
                        t = "null";
                    } else {
                        exist_obj:
                        do {
                            for (k in this.objects) {
                                if (v == this.objects[k]) {
                                    v = this.use_tag ? '{Existing Object} #### See above - <b>' + k + '</b> ####' : '{Existing Object} ## See above - ' + k + ' ##';
                                    break exist_obj;
                                }
                            }
                            if (depth < this.depth) {
                                if (this.is_property(v)) {
                                    this.objects[path + "." + p] = v;
                                }
                                v = "\n" + this.get_property(v, depth + 1, path + "." + p);
                            } else {
                                v = this.get_object_name(obj);
                            }
                        } while (false);
                    }
                } else if (t == "boolean") {
                    v = v ? "true" : "false";
                } else if (t == "function" && !this.showFunc) {
                    v = "Function(){...}";
                } else {
                    v = t == "string" ? this.html_entities(v) : v + "";
                    v = v.split("\n").join("\n" + this.get_spaces(depth + 1));
                }
            } catch(e) {
                t = "not_get";
                v = "~~ data doesn't get ~~";
            }

            ret += this.get_spaces(depth);
            if (this.use_tag) {
                if (this.get_type(this.colors[t]) == "undefined") t = "unknown";
                ret += '<b style="color:#777777;">' + p + '</b>: <span style="color:#' + this.colors[t] + ';">' + v + '</span>\n';
            } else {
                ret += p + ': ' + v + '\n';
            }
        }
        if (!ret && depth) {
            ret = this.get_spaces(depth) + "~~ there aren't property arguments ~~";
        }
        return ret;
    },
    get_object_name : function(obj)
    {
        if (obj == null) return "NULL";
        try {
            var objName = isDefined(obj, 'toString') ? obj.toString() : 'Nameless object';
        } catch (e) {
            var objName = 'Error name object';
        }
        if (objName.search(/\n/) > -1) {
            objName = "{Object}";
        }
        return objName;
    },
    get_type : function(obj)
    {
        return (typeof(obj) + "").toLowerCase();
    },
    is_property : function(obj)
    {
        if (this.get_type(obj) == "object" && obj != null) {
            for (var p in obj) {
                return true;
            }
        }
        return false;
    },
    html_entities : function(str)
    {
        if (this.use_tag) {
            str = str.replace(/\&/g, '&amp;');
            str = str.replace(/\</g, '&lt;');
            str = str.replace(/\>/g, '&gt;');
        }
        return str;
    },
    get_spaces : function(depth)
    {
        var ret = "";
        for (var i = 0; i < this.indent * depth; i++) {
            ret += " ";
        }
        return ret;
    }
}

var alertProperty = function(obj, depth)
{
    this.depth = typeof(depth) == "undefined" ? 0 : depth;
    this.use_tag = false;
    this.objects = {}

    if (this.is_property(obj)) this.objects["root"] = obj;
    var s = this.get_property(obj, 0, 'root');
    if (s) {
        alert(this.get_object_name(obj) + '\n--------------------------------------------\n' + s);
    }
    else alert("There is empty element for show property!");
}
alertProperty.prototype = showProperty.prototype;

var consoleProperty = function(obj, depth)
{
    if (typeof(depth) != "undefined") this.depth = depth;
    this.use_tag = false;
    this.objects = {}

    if (this.is_property(obj)) this.objects["root"] = obj;
    var s = this.get_property(obj, 0, 'root');
    if (s) {
        if (typeof(console.group) == 'undefined') {
            console.log(this.get_object_name(obj) + '\n--------------------------------------------\n' + s);
        } else {
            //console.group(this.get_object_name(obj))
            console.groupCollapsed(this.get_object_name(obj))
            if (depth == -1) {
                console.info(obj);
            } else {
                console.info(s);
            }
            console.groupEnd(false);
        }
    }
    else console.log("There is empty element for show property!");
}
consoleProperty.prototype = showProperty.prototype;
