// Use "form_property" for get type or value of elements of form.
// Set "form_property" for any type of your classes.
var form_property = {
    getFieldValue : function(field_name){
        var elm, re, rArr, ret, field, field_type, i;
        elm = this.form.elm.elements;
        if (!elm[field_name]) {
            rArr = /^(.*?)\[\]$/i.exec(field_name);
            if (rArr) {
                re = new RegExp("^" + rArr[1] + "\\[\\d+\\]$", "i");
                for (i=0;i<elm.length;i++) if (isDefined(elm[i], "type") && elm[i].type == "checkbox" && re.exec(elm[i].name)) {
                    field_name = elm[i].name;
                    break;
                }
            } else {
                // Multidata field
                re = new RegExp("^(" + field_name + "\\[.+?\\])$", "i");
                ret = {"__multidata" : 0, "values" : {}};
                for (i=0; i<elm.length; i++) {
                    rArr = re.exec(elm[i].name);
                    if (rArr && rArr[1]) {
                        //toDo: Multidata with checkbox-list
                        ret.values[rArr[1]] = this.getFieldValue(rArr[1]);
                        ret.__multidata++;
                    }
                }
                // Return as simple object
                return ret.__multidata > 0 ? ret : null;
            }
        }

        field = elm[field_name];
        if (!field) return null;
        field_type = this.getFieldType(field);
        if (field_type && field_type instanceof Array) {
            if (this.winWr && this.winWr.config && this.winWr.config.DebugMode) alert("Attention!\nMixed field isn't analysed at the current version.");
            return null;
        }
        switch (field_type) {
        case "select-one":
            return field.selectedIndex < 0 ? null : field.options[field.selectedIndex].value;
        case "select-multiple":
            ret = [];
            for (i=0; i<field.length; i++) {
                if (field.options[i].selected) ret.push(field.options[i].value);
            }
            // Return as Array
            return ret;
        case "radio":
            if (isDefined(field, "length")) {
                for (i=0;i<field.length; i++) {
                    if (field[i].checked) return field[i].value;
                }
            } else if (isDefined(field, "type") && field.type == "radio") {
                return field.checked ? field.value : "";
            }
            return "";
        case "checkbox-list":
            ret = [];
            for (i=0; i<this._checkboxNameList.length; i++) {
                if (elm[this._checkboxNameList[i]].checked) ret.push(elm[this._checkboxNameList[i]].value);
            }
            // Return as Array
            return ret;
        case "checkbox":
            return field.checked ? field.value : "";
        default:
            return isDefined(field, "value") ? field.value : null;
        }
    },
    getFieldType : function(field){
        var elm, re, rArr, i;
        if (field) {
            if (isDefined(field, "type")) {
                if (field.type == "checkbox") {
                    rArr = /^(.*?)\[\d+\]$/i.exec(field.name);
                    if (rArr) {
                        this._checkboxNameList = new Array(field.name);
                        elm = this.form.elm.elements;
                        re = new RegExp("^" + rArr[1] + "\\[\\d+\\]$", "i");
                        for (i=0;i<elm.length;i++) {
                            if (isDefined(elm[i], "type") && elm[i].type == "checkbox" && elm[i].name != field.name && re.exec(elm[i].name)) this._checkboxNameList.push(elm[i].name);
                        }
                        if (this._checkboxNameList.length > 1) return "checkbox-list";
                    }
                    return "checkbox";
                }
                if (field.tagName.toLocaleLowerCase() == "input" || field.type == "textarea" || field.type == "select-one" || field.type == "select-multiple") return field.type;
            } else if (isDefined(field, "length") && field.length > 0) {
                var eqv = true;
                var pl = [];
                pl[0] = this.getFieldType(field[0]);
                for (i=1; i<field.length; i++) {
                    pl[i] = this.getFieldType(field[i]);
                    if (pl[i] != pl[0]) eqv = false;
                }
                return eqv ? pl[0] : pl;
            }
        }
        return null;
    }
}

// Use "form_validation_main" for make own validator of form.
// Set "form_validation_main" for implement and redefine or add your methods in your class.
var form_validation_main = {
    validateExcept : false,
    validateEnable : true,
    isError        : false,

    err_array        : null,
    errorMsgMethod   : '',

    vbl_loader   : null,
    vbl_fields   : null,
    vbl_errDivs : null,

    prepareData : function (ini_keys, fld_rules, winWr, isLoaded) {
        var field, rules, rule, i;

        if (!isObject(ini_keys)) {
            ini_keys = typeof(ini_keys) == "string" ? {form : ini_keys} : {};
        }
        if (!isDefined(ini_keys, 'form')) {
            return;
        }
        this.err_array   = {};
        this.errDivs     = {};
        this.vbl_errDivs = {};

        this.form_id    = "#" + ini_keys.form;
        this.field_name = ini_keys.field ? ini_keys.field : undefined;
        this.ini_event  = ini_keys.ini_event ? ini_keys.ini_event : (this.field_name ? "onclick" : "onsubmit");
        this.exception  = ini_keys.exception ? ini_keys.exception : undefined;

        // ini field rules
        this.fld_rules = fld_rules ? fld_rules : {};
        for (field in this.fld_rules) {
            rules = this.fld_rules[field];
            if (isObject(rules)) for (i in rules) {
                rule = rules[i];
                if (isObject(rule)) {
                    if (!isDefined(rule, "isEnabled")) rule.isEnabled = true;
                    if (!isDefined(rule, "ruleData"))  rule.ruleData = {};
                    // ToDo: check it
                    if (isDefined(rule, "ini_rule") && typeof(this[rule.ini_rule]) == "function") this[rule.ini_rule](rule);
                }
            }
        }

        this.errorMsgMethod = 'errorMsg_' + (ini_keys.err_format ? ini_keys.err_format : this.config.errorFormatDef);

        if (winWr) {
            this.$w0 = this.winWr = winWr;
        }

        if (ini_keys.loader) {
            this.vbl_loader = this.$w0.getLoadWrapper(ini_keys.loader.url, "get", false);
            this.vbl_loader.addListener(this, "ondataload", "onValidatorLoad");
            this.vbl_fields = ini_keys.loader.fields
        }

        if (isLoaded) {
            this.subscribeForm();
        }
    },
    subscribeForm : function () {
        var fld, field, field_name, i;
        // Subscribe to validation
        this.form = this.$(this.form_id);
        if (this.form) {
            if (this.field_name) {
                if (isObject(this.field_name.constructor)) {
                    for (fld in this.field_name) {
                        // ToDo: check it
                        this._set_subscribe(fld, (this.field_name[fld] ? this.field_name[fld] : this.ini_event), "runValidate");
                    }
                } else {
                    this._set_subscribe(this.field_name, this.ini_event, "runValidate");
                }
            } else if (this.form && this.form.elm) {
                this.form.addListener(this, this.ini_event, "runValidate");
            }
            if (this.exception) {
                for (fld in this.exception) {
                    // ToDo: check it
                    this._set_subscribe(fld, (this.exception[fld] ? this.exception[fld] : this.ini_event), "setException");
                }
            }
            if (this.vbl_fields) {
                for (i in this.vbl_fields) {
                    field_name = this.vbl_fields[i];
                    field = this._get_field(field_name);
                    if (field) {
                        field.addListener(this, "onchange", "validateByLoader", field_name);
                    }
                }
            }
        }
    },
    runValidate : function(evtWr) {
        var conf, errDiv, val, field, sfield, i;
        if (this.validateExcept) {
            this.validateExcept = false;
            evtWr.eventDrop();
            return;
        }
        if (this.validateEnable) {
            // Remove error messages
            conf = this.config;
            for (field in this.err_array) {
                if (isDefined(this.err_array[field], 'div') && this.err_array[field].div) {
                    this.err_array[field].div.remove();
                }
                if (conf.errorClass) {
                    this._get_field(field).removeClass(conf.errorClass);
                }
            }
            if (conf.errorDivClass) {
                errDiv = this.$$(this.form_id + " ." + conf.errorDivClass);
                if (errDiv) {
                    for (i = 0; i < errDiv.length; i++) {
                        if (!this._checkErrDiv(errDiv[i])) {
                            errDiv[i].remove();
                        }
                    }
                }
            }
            this.err_array = {};
            //this.isError = false;

            if (this.checkBeforeValidation()) {
                for (field in this.fld_rules) {
                    if (!this.vbl_errDivs[field]) {
                        val = this.getFieldValue(field);
                        if (val != null) {
                            if (isObject(val) && !(val instanceof Array)) {
                                for (sfield in val.values) {
                                    this._do_rule(val.values[sfield], field, sfield);
                                }
                            } else {
                                this._do_rule(val, field);
                            }
                        } else {
                            this._debugAlert('Not found field "' + field + '"');
                        }
                    } else {
                        this.isError = true;
                    }
                }
            }
            if (this.checkAfterValidation()) {
                if (this.isError) {
                    evtWr.eventDrop();
                    this.actionIsNotValid();
                    this.isError = false;
                    this.broadcastMessage("isNotValid", this.err_array);
                } else {
                    this.actionIsValid();
                    this.broadcastMessage("isValid");
                }
            }
        }
    },
    setException : function(evtWr) {
        this.validateExcept = true;
    },
    add_rule : function(field_name, rule_obj) {
        var rules = this.fld_rules[field_name];
        rules.push(rule_obj);
    },
    remove_rule : function(field_name, rule_name) {
        var ret = null;
        var rules = this.fld_rules[field_name];
        for (var i in rules) {
            if (rules[i].rule_name == rule_name) {
                ret = rules[i];
                delete rules[i];
                break;
            }
        }
        return ret;
    },
    personalValidate : function(evtWr, eDt) {
        var conf, val, ruleName, j;
        val = this.getFieldValue(eDt.field);
        if (!isObject(val)) this._trim(val);
        ruleName = eDt.rule.rule_name;
        if (this["rule_" + ruleName](val, eDt.rule.ruleData)) {
            conf = this.config;
            if (conf.errorClass) {
                evtWr.elmWr.removeClass(conf.errorClass);
            }
            if (conf.personalEvent) {
                for (j in conf.personalEvent) {
                    evtWr.elmWr.removeListener(this, conf.personalEvent[j], "personalValidate");
                }
            }
            if (isDefined(this.err_array[eDt.field], 'div')) {
                this.err_array[eDt.field].div.remove();
                this.err_array[eDt.field].div = null;
            }
        }
    },
    validateByLoader : function(evtWr, field_name) {
        this.vbl_loader.send({field:field_name, value:this.getFieldValue(field_name)});
        if (isDefined(this.vbl_errDivs, field_name) && this.vbl_errDivs[field_name]) {
            this.vbl_errDivs[field_name].remove();
            this.vbl_errDivs[field_name] = null;
            if (!this.err_array[field_name] && this.config.errorClass) {
                this._get_field(field_name).removeClass(this.config.errorClass);
            }
        }
    },
    onValidatorLoad : function(json, dom, txt) {
        var field_name = json.field;
        if (txt != 'ok' && json.value == this.getFieldValue(field_name)) {
            this.vbl_errDivs[field_name] = this._makeErrorDiv(field_name, txt);
        }
    },
// ----- Internal functions -----
    _get_field : function(field_name) {
        return this.$(this.form_id + " [name=" + field_name + "]");
    },
    _set_subscribe : function(field_name, event, func) {
        var field = this._get_field(field_name);
        if (field) {
            field.addListener(this, event, func);
            return field;
        }
        return null;
    },
    _do_rule : function(val, field, sfield) {
        var conf, rule, ruleName, fieldWr, evtObj, i, j;
        conf = this.config;
        if (val && !(val instanceof Array)) val = this._trim(val);
        for (i in this.fld_rules[field]) {
            rule = this.fld_rules[field][i];
            if (isObject(rule) && rule.isEnabled) {
                ruleName = rule.rule_name;
                if (ruleName && typeof(this["rule_" + ruleName]) == "function") {
                    if ((val || !rule.not_empty) && !this["rule_" + ruleName](val,rule.ruleData)) {
                        if (!sfield) sfield = field;
                        this.err_array[sfield] = rule;
                        fieldWr = this._get_field(sfield);
                        if (fieldWr) {
                            if (conf.errorClass) {
                                fieldWr.addClass(conf.errorClass);
                            }
                            if (conf.personalEvent) {
                                for (j in conf.personalEvent) {
                                    evtObj = fieldWr.addListener(this, conf.personalEvent[j], "personalValidate");
                                    evtObj.field = sfield;
                                    evtObj.rule = rule;
                                }
                            }
                        }
                        this.isError = true;
                        return false;
                    }
                } else this._debugAlert(ruleName ? "Incorrect rule - rule_" + rule.rule_name : "Rule is'n set");
            }
        }
        return true;
    },
    _regexp_min_max : function(val, rgxp, data, format) {
        if (!this._regexp(val, rgxp)) return false;
        var min, max , c1, c2;
        c1 = !isDefined(data, "min_value");
        c2 = !isDefined(data, "max_value");
        if (format == "date") {
            val = this._date2string(val);
            min = c1 ? val : this._date2string(data.min_value);
            max = c2 ? val : this._date2string(data.max_value);
        } else {
            if (format == "int") val = parseInt(val);
            else if (format == "float") val = parseFloat(val);
            min = c1 ? val : data.min_value;
            max = c2 ? val : data.max_value;
        }
        return (val>=min && val<=max);
    },
    _regexp : function(val, rgxp) {
        if (typeof(rgxp) == "string") {
            var md = "";
            if (rgxp.substr(0, 1) == "/") {
                while (rgxp.length > 2 && rgxp.substr(rgxp.length - 1, 1) != "/") {
                    md += rgxp.substr(rgxp.length - 1, 1);
                    rgxp = rgxp.substr(0, rgxp.length - 1);
                }
                rgxp = rgxp.substr(1, rgxp.length - 2);
            }
            rgxp = new RegExp(rgxp, md);
        }
        return rgxp.exec(val) != null;
    },
    _conv4compare : function (val, data) {
        var cmp = [val, this._trim(this.getFieldValue(data.compare_field))];
        if (isDefined(data, "data_type") && (data.data_type == 'date' || data.data_type == 'datetime')) {
            cmp[0] = this._date2string(cmp[0], data);
            cmp[1] = this._date2string(cmp[1], data);
        } else if (this._isInteger(cmp[0]) && this._isInteger(cmp[1])) {
            cmp[0] = parseInt(cmp[0]);
            cmp[1] = parseInt(cmp[1]);
        } else if (this._isNumber(cmp[0]) && this._isNumber(cmp[1])) {
            cmp[0] = parseFloat(cmp[0]);
            cmp[1] = parseFloat(cmp[1]);
        }

        return cmp;
    },
    _date2string : function(val, data) {
        if (!isDefined(val) || !isDefined(data)) return null;
        if (!isDefined(data, "date_format")) {
            this._debugAlert("Need set date format for check interval!");
            return null;
        }
        // Order of date elements in regexp. Example: {y:3,m:2,d:1,h:4,i:5,s:6}
        var order = data.date_order;
        if (!isObject(order)) {
            this._debugAlert("Need set order date element!");
            return null;
        }
        var da = data.date_format.exec(val);
        if (!da) return null;

        if (da[order.y].length == 2) {
            // year has 2 digits
            var threshold = parseInt(data.year2digits_threshold);
            if (threshold == "NaN" || threshold == 0) threshold = 60;
            da[order.y] = parseInt(da[order.y]) < threshold ? "19" + da[order.y] : "20" + da[order.y];
        }
        var ret = "";
        for (var k in order) ret += (da[order[k]].length < 2 ? "0" : "") + da[order[k]];
        return ret;
    },
    _debugAlert : function(msg) {
        if (this.winWr.config.DebugMode) alert(msg);
    },
    _trim : function(val) {
        return val.replace(/^\s*(.*?)\s*$/, "$1");
    },
    _makeErrorDiv : function(field_name, error_msg) {
        var conf, div, field, parent, sibling, isRow, i;
        conf = this.config;
        field = this._get_field(field_name);
        if (field) {
            parent  = field.getParent();
            sibling = field.getNextSibling();
            div = this.$w0.makeElement('div', null, error_msg);

            if (conf.errorClass) {
                field.addClass(conf.errorClass);
            }
            if (conf.errorDivClass) {
                div.addClass(conf.errorDivClass);
            }

            isRow = false;
            if (conf.formRowClasses) {
                for (i in conf.formRowClasses) {
                    if ((new RegExp('(^|\s)' + conf.formRowClasses[i] + '(\s|$)')).test(parent.elm.className)) {
                        isRow = true;
                        break;
                    }
                }
            }

            if (!isRow && sibling) {
                parent.elm.insertBefore(div.elm, sibling.elm);
            } else {
                parent.elm.appendChild(div.elm);
            }
            return div;
        }
        return null;
    },
    _checkErrDiv : function(field) {
        for (var k in this.vbl_errDivs) {
            if (this.vbl_errDivs[k] == field) {
                return true;
            }
        }
        return false;
    },
    _isInteger : function(n) {
        return /^\-?\d+$/.test(n);
    },
    _isNumber : function(n) {
        return /^\-?\d+(\.\d*)?$/.test(n);
    },

// ---------- This actionr may be replace to other -----------
    checkBeforeValidation : function() {
        return true;
    },
    checkAfterValidation : function() {
        return true;
    },
    actionIsValid : function() {
    },
    actionIsNotValid : function() {
        this[this.errorMsgMethod]();
        if (this.config.errorFocus) {
            for (var k in this.err_array) {
                this._get_field(k).elm.focus();
                break;
            }
        }
    },
    errorMsg_alert : function() {
        var err_txt, k;
        err_txt = "";
        for (k in this.err_array) {
            err_txt += this.err_array[k].error_msg + "\n"
        }
        if(err_txt) {
            alert(err_txt);
        }
    },
    errorMsg_div : function() {
        var err, field_name;
        for (field_name in this.err_array) {
            err = this.err_array[field_name];
            err.div = this._makeErrorDiv(field_name, err.error_msg);
        }
    },


// ---------- Validate rules -----------
    rule_isRequired : function(val) {
        if (val && val instanceof Array) {
            if (val.length == 1) val = val[0];
            else return val.length > 1 ? true : false;
        }
        return val != "" && val != 0 && val != null;
    },
    rule_strlen : function(val, data) {
        var len = val.length;
        if (isDefined(data, "max_value") && len > data.max_value) return false;
        if (isDefined(data, "min_value") && len < data.min_value) return false;
        return true;
    },
    rule_isAlphalogin : function(val) {
        return this._regexp(val, /^[A-Z0-9][\w\-@\.]*$/i);
    },
    rule_isAlphanumeric : function(val) {
        return this._regexp(val, /^[A-Z0-9][\w\-]*$/i);
    },
    rule_isInt : function(val, data) {
        return this._regexp_min_max(val, /^\-?\d+$/, data, "int");
    },
    rule_isFloat : function(val, data) {
        return this._regexp_min_max(val, /^\-?\d+(\.\d*)?$/, data, "float");
    },
    rule_isEmail : function(val) {
        return this._regexp(val, /^[a-z_0-9!#*=.-]+@([a-z0-9-]+\.)+[a-z]{2,4}$/i);
    },
    rule_isDate : function(val, data) {
        if (!data.date_format) {
            data.date_format = /(\d{2,4})[-\/.](\d{2})[-\/.](\d{2,4})/
        }
        if (!data.date_order) {
           data.date_order = {m:2,d:3,y:1};
        }
        return this._regexp_min_max(val, data.date_format, data, "date");
    },
    rule_matchRegexp : function(val, data) {
        return this._regexp(val, data.regexp);
    },
    rule_equalTo : function(val, data) {
        var cmp = this._conv4compare(val, data);
        return cmp[0] == cmp[1];
    },
    rule_notEqualTo : function(val, data) {
        var cmp = this._conv4compare(val, data);
        return cmp[0] != cmp[1];
    },
    rule_greaterThan : function(val, data) {
        var cmp = this._conv4compare(val, data);
        return cmp[0] > cmp[1];
    },
    rule_lesserThan : function(val, data) {
        var cmp = this._conv4compare(val, data);
        return cmp[0] < cmp[1];
    },
    rule_greaterOrEqualTo : function(val, data) {
        var cmp = this._conv4compare(val, data);
        return cmp[0] >= cmp[1];
    },
    rule_lesserOrEqualTo : function(val, data) {
        var cmp = this._conv4compare(val, data);
        return cmp[0] <= cmp[1];
    },
    rule_dateInterval : function(val, data) {
        var cval = this._date2string(val, data);
        if (typeof(cval) == "null") return false;
        if (isDefined(data, "max_value") && (cval > this._date2string(data.max_value, data))) return false;
        if (isDefined(data, "min_value") && (cval < this._date2string(data.min_value, data))) return false;
        return true;
    },
    config : {
    }
};

/*
Validate init example:
var validation_loginForm = new form_validation({form:"loginForm"}, {
    login:[
        {rule_name:"isRequired", error_msg:"Field \"Login\" is required for fill!"},
        {rule_name:"isAlphalogin", error_msg:"Field \"Login\" must contain only Numbers, Letters, _, @, - and point.",not_empty:1}
    ],
    password:[
        {rule_name:"isRequired", error_msg:"Field \"Password\" is required for fill!"}
    ]
}, _wrapper);
*/

var form_validation = newClass({
    init : function (ini_keys, fld_rules, winWr, isLoaded) {
        this.prepareData(ini_keys, fld_rules, winWr, isLoaded);
    },
    implement : [
        {obj : form_property},
        {obj : form_validation_main}
    ],
    onready : function () {
        this.subscribeForm();
    },
    config : {
        //Now possible values: "errorMsg_div", "errorMsg_alert"
        errorFormatDef : "alert",
        errorClass     : "error_element",
        errorDivClass  : "errorForm",
        errorFocus     : true,
        formRowClasses : ["formRow"],
        //, "onclick" ?
        personalEvent : ["onchange", "onkeyup"]
    }
});
