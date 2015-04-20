var patternCtrl = newObject({
    patterns : {}, // key - patternName: value - array(type, HTML-pattern, Event-array)
    reg : {
        "pattern"   : /^(.*?)(\{(.+?)(?:\-(.+?))?\})(.*)$/,
        "inpGroup"  : /^(.*?)\[(.+?)\](.*)$/,
        "dateInput" : /(\d{1,2})[-.\/](\d{1,2})[-.\/](\d{1,4})/,
        "dateMask"  : /(\w{1})[-.\/](\w{1})[-.\/](\w{1})/,
        "timeSprt"  : /^\s*(.+?)(?:\s+(.+?))?\s*$/
    },
    id_ind : 0,
    arg : {},
    del_param : null,
    opr_param : null,

    idSuff    : 1,
    evtDelay  : 0,

    isPatLoad : false,
    isRbData  : null,
    dateMask  : null,

    codeDrag  : null,

    init : function(mainCtrl)
    {
        this.mainCtrl = mainCtrl;
        this.$w0 = mainCtrl.$w0;

        this.loader = this.getLoadWrapper(mainCtrl.baseUrl + this.config.url, "post");
        this.loader.addListener(this, "ondataload", "onPatternLoad");
        this.loader.addListener(this, "ondataerror", "onError");
        this.dateMask = this.reg.dateMask.exec(this.config.dateConfig.dateFormat.toLowerCase());
        this.dateMask.shift();
    },

    load : function()
    {
        this.loader.send();
    },

    /**
    * Parse sourse html-code: replace placeholders to real elements
    * obj - content object
    * html - sourse HTML
    * data - data ot active elements
    * ap - add parameters
    * ei - edit/insert
    * key - id of edit/number of insert
    */
    parseHtml : function(html, obj, el, data, ap, ei, key)
    {
        var ret, tmp, a, pat, i;
        this.arg = {"obj" : obj, "el" : el, "data" : data, "ap" : ap, "ei" : isDefined(ei) ? ei : "edit", "key" : isDefined(key) ? key : null};
        ret = "";
        tmp = html;
        for (i = 0; i < this.config.maxPaternQtt && tmp; i++) {
            a = this.reg.pattern.exec(tmp);
            if (!a) {
                ret += tmp
                break;
            } else if (isDefined(this.patterns, a[3])) {
                pat = this.patterns[a[3]];
                ret += a[1] + this["parse_" + pat[0]](pat[1], isDefined(a, 4) ? a[4] : null);
            } else {
                ret += a[1] + a[2];
            }
            tmp = a[5];
        }
        if (i >= this.config.maxPaternQtt) {
            throw {message : "Search pattern limit is expired: " + html};
        }
        return ret;
    },
    setInnerHTML : function(obj, html){
        try {
            obj.innerHTML = html;
            return true;
        } catch (e) {
            this.errMsg(e.message + "\n\nIncorrect HTML-code:\n" + html);
            return false;
        }
    },

    //---- pase pattern ---- \\
    parse_label : function(html, name)
    {
        return html.replace("{LABEL}", this.arg.ap.label[name]);
    },

    parse_tbl_head : function(html, name)
    {
        html = html.replace("{ORDER_DIR}", this.arg.data[name]);
        html = html.replace("{LABEL}", this.arg.ap.label[name]);
        return this._prepareEvents([html, "EVENT_id"], this.arg.obj, "onclick", "onSort", {"name" : name});
    },

    parse_id : function(html, name)
    {
        var val = this._getData(name, 1);
        if(isDefined(this.arg.ap, "convId") && this.arg.ap.convId) {
            var k = this.arg.ap.convId[0];
            if(val.substr(0, k.length) == k) {
                val = "<b>new</b>";
            }
        }
        return html.replace(/\{VALUE\}/g, val);
    },

    parse_not_edit : function(html, name)
    {
        return html.replace(/\{VALUE\}/g, this._getData(name, -1));
    },

    parse_input : function(html, name)
    {
        html = html.replace("{VALUE}", this._getData(name, 2));
        return this._typicalEvents(html, "EVENT_id", this.arg.obj, {"onkeyup" : "onDataChange", "onchange" : "onDataChange", "onmouseover" : "onDataChange"}, name);
    },

    parse_wysiwyg : function(html, name)
    {
        html = html.replace("{VALUE}", this._getData(name, 2));

        var ed = this._setEvtParam({}, name);
        var htd = [html, "EVENT_id"];
        this._prepareEvents(htd, this.arg.obj, "onkeyup", "onDataChange", ed);
        //this._prepareEvents(htd, this.arg.obj, "onpaste", "onDataChange", ed);
        this._prepareEvents(htd, this.arg.obj, "onchange", "onDataChange", ed);
        this._prepareEvents(htd, this, "onmouseover", "onCodeDrag", ed);
        this._prepareEvents(htd, this.arg.obj, "onmouseover", "onDataChange", ed);
        this._prepareEvents(htd, this.arg.obj, "ondblclick", "onWysiwygShow", ed);

        return htd[0];
    },

    parse_date : function(html, name)
    {
        html = html.replace("{VALUE}", this._dateM2L(this._getData(name, 2)));

        if (!/EVENT_id/.test(html)) {
            return html;
        }
        var ed = this._setEvtParam({"obj" : this.arg.obj}, name);
        var htd = [html, "EVENT_id"];
        this._prepareEvents(htd, this, "onkeyup", "onDateEdit", ed);
        this._prepareEvents(htd, this, "onmousedown", "onDateEdit", ed);

        this.setTimeout(this.config.calendarDelay, this, "showCalendar", [htd[2], this.arg.obj, this._setEvtParam({}, name)]);

        return htd[0].replace("{IMG_ID}", htd[2] + "_img");
    },

    parse_date_time : function(html, name)
    {
        var val, ed, htd, a;
        val = this._getData(name, 2);
        if (val) {
            a = this.reg.timeSprt.exec(val);
            if (!a) {
                a = [val, "00:00:00"];
            } else if (!a[2]) {
                a[2] = "00:00:00";
            }
            val = this._dateM2L(a[1]) + " " + a[2];
        }
        html = html.replace("{VALUE}", val);

        if (!/EVENT_id/.test(html)) {
            return html;
        }
        ed = this._setEvtParam({"obj" : this.arg.obj}, name);
        htd = [html, "EVENT_id"];
        this._prepareEvents(htd, this, "onkeyup", "onDateTimeEdit", ed);
        this._prepareEvents(htd, this, "onmousedown", "onDateTimeEdit", ed);

        return htd[0];
    },

    parse_chk_rad : function(html, name)
    {
        var ap = this.arg.ap.select[name];
        html = html.replace("{VALUE}", this._getData(name, 2));
        html = html.replace("{NAME}", name);
        html = html.replace("{CHECKED}", this._getData(name, 0) == ap[1] ? ' checked="checked"' : '');

        var ed = this._setEvtParam({}, name);
        ed.val=ap;
        return this._prepareEvents([html, "EVENT_id"], this.arg.obj, "onclick", "onInputClick", ed);
    },

    parse_inp_gr : function(html, name)
    {
        var a, dd, ht, htd, k, n, d, i, k_id;
        a = this.reg.inpGroup.exec(html);
        n = 0;
        dd = "";
        for (k in this.arg.ap.select[name]) {
            // FX21 fix: ID -> _ID
            k_id = k.substr(0, 1) == '_' ? k.substr(1) : k;
            ht = a[2].replace("{VALUE}", k_id);
            ht = ht.replace("{CHECKED}", this._getData(name, 0) == k_id ? ' checked="checked"' : '');
            ht = ht.replace(/\{ID\}/g, 'el_id' + (this.id_ind++));
            ht = ht.replace("{TEXT}", this.arg.ap.select[name][k]);
            ht = ht.replace("{NUM}", "{" + n + "}");
            ht = ht.replace("{NAME}", name + (isDefined(this.arg, "key") && this.arg.key != null ? "{" + this.arg.ei + this.arg.key + "}" : ""));

            htd = [ht, "EVENT_id"];
            ht = this._prepareEvents(htd, this.arg.obj, "onclick", "onInputClick", this._setEvtParam({val : [null, k_id]}, name));
            ht = ht.replace("{EVENT_id}", htd[2]);

            dd += ht;
            this.id_ind++;
            n++;
        }
        return a[1] + dd + a[3];
    },

    parse_select : function(html, name)
    {
        return this.getComboSelect(html, name, "simple").getHTML();
    },

    parse_select_optgroup : function(html, name)
    {
        return this.getComboSelect(html, name, "optgroup").getHTML();
    },

    parse_select_dependent : function(html, name)
    {
        return this.getComboSelect(html, name, "dependent").getHTML();
    },

    parse_image : function(html, name)
    {
        var obj = new admImage(this, html, name);
        return obj.getHTML();
    },
    parse_flash : function(html, name)
    {
        var obj = new admFlash(this, html, name);
        return obj.getHTML();
    },
    parse_file : function(html, name)
    {
        var obj = new admFile(this, html, name);
        return obj.getHTML();
    },
    parse_error : function(html, name)
    {
        return html;
    },

    parse_note : function(html, name)
    {
        return html;
    },

    parse_submit : function(html, name)
    {
        html = html.replace("{VALUE}", this._getData(name, 2));
        return this._typicalEvents(html, "EVENT_id", this.arg.obj, {"onclick" : "onSubmit"}, name);
    },

    parse_openRight : function(html, name)
    {
        if (this.arg.data.id && this.arg.ei == "edit") {
            var ed = {};
            ed.key = isDefined(this.arg.ap, "open_right") && isDefined(this.arg.ap.open_right, name) ? this.arg.ap.open_right[name] : null;
            if (isDefined(this.arg.data, "id")) {
                ed.cond = {};
                ed.cond[this.arg.ap.id_name] = this.arg.data.id;
            }
            var ret = this._prepareEvents([html, "EVENT_id"], this.mainCtrl.topCurr[1], "onclick", "openRight", ed);
            this.arg.obj.eRgt = this.arg.el[this.arg.el.length - 1];
            return ret;
        } else {
            return "\xA0";
        }
    },

    parse_delete : function(html, name)
    {
        var ret = this._prepareEvents([html, "EVENT_id"], this.arg.obj, "onclick", "onRowDelete", {ei : this.arg.ei, key : this.arg.key});
        this.arg.obj.eDel = this.arg.el[this.arg.el.length - 1];
        return ret;
    },
    parse_not_standard : function(html, name)
    {
        if (!isDefined(this.arg.ap.not_standard, name)) {
            this.errMsg('Nonstandard JavaScript class isn\'t defined for ' + name + '.')
            return '!Not defined!';
        }
        try {
            eval("var obj = new " + this.arg.ap.not_standard[name] + "(this, html, name);");
            return obj.getHTML();
        }  catch (e) {
            this.errMsg("Incorrect call nonstandard JavaScript:\n" + e.message);
            return 'Incorrect call';
        }
    },


    //---- AUX methods ---- \\
    subscribeEvent : function(el)
    {
        var elmWr, ed, id, d, k, i;
        for (i in el) {
            d = el[i];
            elmWr = this.$("#" + d.id); // ToDo: It was to slow!!!
            //elmWr = getElmWrapper(this.$w0.doc.getElementById(d.id), this.$w0);
            if(elmWr && elmWr.elm) {
                ed = elmWr.addListener(d.obj, d.evt, d.met);
                if(elmWr.elm.tagName == "textarea" && isDefined(d.ed, "src")) {
                    d.ed.src = elmWr.elm.value;
                }
                if(d.ed) {
                    for (k in d.ed) {
                        ed[k] = d.ed[k];
                    }
                }
            }
        }
    },
    getComboSelect : function(html, name, type){
        //if (isDefined(this.arg.obj, "comboSelect")) {}
        var obj = new comboSelect(this, html, name, type);
        return obj;
    },
    getTagId : function(){
        return "ptrn_" + (this.idSuff++);
    },
    showCalendar : function(id, obj, dt)
    {
        if (this.$("#" + id) && this.$("#" + id + "_img")) {
            var cld = new calendar(id, id + "_img", this.config.dateConfig, this.$w0);
            cld.dateSelect = this.getClosedFunction(this, "calendarSelect", [id, obj, dt]);
            cld.onready();
        }
    },
    getDom : function(html, tag, whole)
    {
        var winWr, div, cont, ret, i;
        winWr = this.$w0;
        if ((tag == "tbody" || tag == "select") && winWr.bv.isIE) {
            div = winWr.doc.createElement("div");
            if (!this.setInnerHTML(div, tag == "tbody" ? "<table><tbody>" + html + "</tbody></table>" : "<form><select>" + html + "</select></form>")) return null;
            cont = div.firstChild.firstChild;
        } else {
            cont = winWr.doc.createElement(isDefined(tag) ? tag : "div");
            if (!this.setInnerHTML(cont, html)) return null;
        }
        if (isDefined(whole)) {
            return cont;
        }
        ret = [];
        for (i = 0; i < cont.childNodes.length; i++) {
            ret[i] = cont.childNodes[i]
        }
        return ret;
    },

    //---- load processing ---- \\
    onPatternLoad : function(json, dom, txt)
    {
        if (txt == "ok") {
            var tp, k;
            this.patterns = {};
            for (tp in json.patterns) {
                for (k in json.patterns[tp]) {
                    this.patterns[k] = [tp, json.patterns[tp][k]];
                    if(isDefined(json, 'events') && isDefined(json.events, k)) {
                        this.patterns[k][2] = json.events[k];
                    }
                }
            }
            this.isPatLoad = true;
            if (this.isRbData != null) {
                mainCtrl.startRebuild(this.isRbData);
            }
        } else {
            this.errMsg(txt ? txt : "Load patterns error");
        }
    },
    onError : function(json, dom, txt)
    {
        this.errMsg("Connection error. Please try later!");
    },
    setRebuild : function(rd)
    {
        this.isRbData = rd;
        if (this.isPatLoad) {
            mainCtrl.startRebuild(rd);
        }
    },

    //---- event processing ---- \\
    onDateEdit : function(evtWr, data)
    {
        data.obj.setChange(data.ei, data.key, data.name, this._dateL2M(evtWr.elmWr.elm.value), data.src);
    },
    calendarSelect : function(id, obj, dt)
    {
        obj.setChange(dt.ei, dt.key, dt.name, this._dateL2M(this.$("#" + id).elm.value), dt.src);
    },
    onDateTimeEdit : function(evtWr, data)
    {
        var val = evtWr.elmWr.elm.value;
        var a = this.reg.timeSprt.exec(val);
        if (!a) {
            a = [val, "00:00:00"];
        } else if (!a[2]) {
            a[2] = "00:00:00";
        }
        data.obj.setChange(data.ei, data.key, data.name, this._dateL2M(a[1]) + " " + a[2], data.src);
    },

// === Image drag and drop === \\
    startCodeDrag : function(codeDrag)
    {
        this.codeDrag = codeDrag;
    },

    onCodeDrag : function(evtWr, data)
    {
        if (this.codeDrag != null) {
            evtWr.elmWr.elm.value = evtWr.elmWr.elm.value.replace(this.codeDrag[0], this.codeDrag[2] ? "{IMG-" + this.codeDrag[2] + "}" : "{IMG_" + this.codeDrag[1] + "}");
            this.codeDrag = null;
        }
    },

    stopCodeDrag : function()
    {
        this.codeDrag = null;
    },

    /**
     * html - sourse html-code (with eventId-pattern)
     * idKey - patern key
     * obj object for parce event
     * el = {"event name" : "method name"}
     * name field name
     */
    _typicalEvents : function(html, idKey, obj, el, name)
    {
        var htmlData, ed, evt;

        htmlData =[html, idKey];
        ed = this._setEvtParam({}, name);

        for (evt in el) {
            this._prepareEvents(htmlData, obj, evt, el[evt], ed);
        }
        return htmlData[0];
    },
    _setEvtParam : function(dt, name)
    {
        dt.ei   = this.arg.ei;
        dt.key  = this.arg.key;
        dt.src  = this._getData(name, 0);
        dt.name = name;
        return dt;
    },
    /**
     * htmlData = [htmlCode, idHtmlKey, idValue] - if idValue isn't set it will be set
     * obj object for parce event
     * evt event name
     * met method name of object
     * ed = {ei:"edit/ins",key:"field key",src:"Source data",name:"field name",}
     */
    _prepareEvents : function(htmlData, obj, evt, met, ed)
    {
        if(!htmlData[2]) {
            htmlData[2] = isDefined(obj, "getTagId") ? obj.getTagId() : this.getTagId();
            htmlData[0] = htmlData[0].replace("{" + htmlData[1] + "}", htmlData[2]);
        }
        this.arg.el.push({
            "id"  : htmlData[2],
            "obj" : obj,
            "evt" : evt,
            "met" : met,
            "ed"  : ed
        });
        return htmlData[0];
    },

    //---- local methods ---- \\
    _dateL2M : function(d)
    {
        var arr, ret, i;
        if(!d) {
            return "";
        }
        arr = this.reg.dateInput.exec(d);
        if(!arr) {
            this.errMsg("Incorrect date format!");
            return "";
        }
        ret = [];
        for (i = 0; i < 3; i++) {
            if (this.dateMask[i] == "m") {
                ret[1] = arr[i+1];
            } else if (this.dateMask[i] == "y") {
                ret[0] = arr[i+1];
            } else {
                ret[2] = arr[i+1];
            }
        }
        return ret[0] + "-" + ret[1] + "-" + ret[2];
    },
    _dateM2L : function(d)
    {
        var ret, i;
        if(!d) {
            return "";
        }
        ret = [];
        for (i = 0; i < 3; i++) {
            if (this.dateMask[i] == "m") {
                ret[i] = d.substr(5, 2);
            } else if (this.dateMask[i] == "y") {
                ret[i] = d.substr(0, 4);
            } else {
                ret[i] = d.substr(8, 2);
            }
        }
        return ret[0] + "." + ret[1] + "." + ret[2];
    },

    _getData : function(name, repl)
    {
        var val = this.arg.data && isDefined(this.arg.data, name) ? this.arg.data[name] : '';
        return this.mainCtrl.htmlSpecialChars(val, repl);
    },

    config : {
        "url" : "/form_pattern.php",
        "calendarDelay" : 100,
        "dateConfig"    : {"dateFormat" : "d.m.Y", "cldrPos" : [0, 1]},
        "maxPaternQtt"  : 256
    }
});