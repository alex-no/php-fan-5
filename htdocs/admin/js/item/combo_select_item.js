var comboSelect = newClass({
    reg : {
        "simple" : /^([^\{]*(\{EVENT\_id1\})?.*?)\[(.+?)\](.*)$/,
        "group1" : /^(.*?)\[\[(.+?)\]\](.*)$/,
        "group2" : /^(.*?)\[(.+?)\](.*)$/,
        "depended1" : /^(.*?)\[\[(.+?)\]\](.*)$/,
        "depended2" : /^(.*?(\{EVENT\_id(\d+)\}).*?)\[(.+?)\](.*)$/
    },

    __staticVar : {
        itemNum : 1
    },

    idPref : 0,
    idSuff : 1,
    eId : null,
    eWr : null,
    op_pat : null,

    winWr  : null,
    loader : null,
    srcArr : null,

    html : "",
    name : "",

    sel_dt : null,

    init : function(pc, html, name, type)
    {
        this.arg  = pc.arg;
        this.$w0  = pc.$w0;

        this.html = html;
        this.name = name;
        this.type = type;

        this.eId = {};
        this.eWr = {};
        this.idPref = "sel" + (this.__staticVar.itemNum++) + "_";
    },

    init_simple : function(si)
    {
        this._getElmWr();
        if (this._subscribeMain("sel")) {
            this.eWr.sel.elm.selectedIndex = si;
        }
    },

   init_hidden : function(ptrn)
    {
        this._getElmWr();
        if (this._subscribeMain("sel")) {
            this.eWr.div.addListener(this, "onclick",  "showSelectHidden").ptrn = ptrn;
            this.eWr.sel.addListener(this, "onblur",   "hideSelectHidden").nv = 0;
            this.eWr.sel.addListener(this, "onchange", "hideSelectHidden").nv = 1;
        }
    },

    init_optgroup : function(si)
    {
        this._getElmWr();
        if (this._subscribeMain("sel")) {
            if (si) {
                this.eWr.sel.elm.selectedIndex = -1;
            }
            //this.eWr.sel.addListener(this, "onchange", "onSelGroup");
        }
    },

    init_dependent : function(el1, el2, el3, ptrn)
    {
        var sel, i;
        this._getElmWr();
        for (i=0; i < this.srcArr.depth; i++) {
            if (isDefined(this.eWr, "sel_" + i)) {
                sel = this.eWr["sel_" + i];
                if (isDefined(el1, i)) {
                    sel.addListener(this, "onchange", "onChangeDepnd").ci = i;
                    this._subscribeMain("sel_" + i);
                }
                if (isDefined(el2, i)) {
                    sel.addListener(this, "onchange", "showNextSelect").ci = i;
                }
                if (el3[i] == -1) {
                    sel.elm.selectedIndex = -1;
                }
                sel.addListener(this, "onchange", "setDepndKey").ci = i;
            }
        }
    },

    onSubmit : function(evtWr)
    {
    },

    send : function(op, data)
    {
        if (this.loader) {
            this.loader.send({"op":op, "data":data});
        } else {
            //alert("Loader doesn't set!");
        }
    },

    onDataLoad : function(json, dom, txt)
    {
        if (txt == "ok") {
            var dt = json.data;
            if (json.op == "load_next_list") {
                var l = dt.level;
                if(this.sel_dt[l-1] && isDefined(this.sel_dt[l-1], dt.cval) ) {
                    this.sel_dt[l] = {};
                    for (var k in dt.hash) {
                        this.sel_dt[l][k] = {"val" : dt.hash[k]};
                    }
                    this.sel_dt[l-1][dt.cval].child = this.sel_dt[l];
                    if (this.eWr["sel_" + (l-1)].elm.value == dt.cval) {
                        var ns = this.eWr["sel_" + l];
                        var opdt = this._makeOptList(dt.hash, this.op_pat, -1);
                        patternCtrl.setInnerHTML(ns.elm, opdt[0]);
                        ns.elm.selectedIndex = -1;
                    }
                }
            }
        } else {
            alert(txt ? txt : "Load combo-select error");
        }
    },

    onError : function()
    {
        alert("File of select data load error");
    },

    getTagId : function(key)
    {
        this.eId[key] = this.idPref + (this.idSuff++);
        return this.eId[key];
    },

    getHTML : function()
    {
        return this["parse_select_" + this.type](this.html, this.name, this._getData());
    },

    parse_select_simple : function(html, name, cdt)
    {
        var val, a, opdt, cdt_;
        this._getSelectArr("select", name);
        val = cdt && isDefined(this.srcArr, cdt) ? this.srcArr[cdt] : "\xA0";
        
        // FX21 fix: ID -> _ID
        cdt_ = '_' + cdt;
        if (cdt_ && isDefined(this.srcArr, cdt_)) {
            val = this.srcArr[cdt_];
        }
        
        a  = this.reg.simple.exec(html);
        if (a) {
            if (a[2]) {
                html = a[1].replace("{EVENT_id1}", this.getTagId("div"));
                html = html.replace("{EVENT_id2}", this.getTagId("sel"));
                html = html.replace("{VALUE}", val) + a[4].replace("{VALUE}", val);
                this._chargeInit("init_hidden", [a[3]]);
            } else {
                html = a[1].replace("{EVENT_id}", this.getTagId("sel"));
                opdt = this._makeOptList(this.srcArr, a[3], cdt);
                html = html.replace("{VALUE}", val) + opdt[0] + a[4].replace("{VALUE}", val);
                this._chargeInit("init_simple", [opdt[1]]);
            }
        } else {
            html = html.replace("{VALUE}", val);
        }
        return html;
    },

    parse_select_optgroup : function(html, name, cdt)
    {
        var sd, ap, a1, a2, id1, id2, ret, dt, opdt, k;
        if (!this._getSelectArr("combo_select", name)) {
            return "No Data for select.";
        }
        sd = this.srcArr.data;

        ap = this.arg.ap;
        if (!isDefined(ap, "select")) {
            ap.select = {};
        }
        ap.select[name] = {};

        a1 = this.reg.group1.exec(html);
        a2 = this.reg.group2.exec(a1[2]);

        si = -1;
        ret = a1[1].replace("{EVENT_id}", this.getTagId("sel"));
        for (id1 in sd) {
            if (isDefined(sd[id1], "child")) {
                dt = this._arrGrConv(sd[id1].child);
                for (k in dt) {
                    ap.select[name][k] = sd[id1].val + ": " + dt[k];
                }
                opdt = this._makeOptList(dt, a2[2], cdt);
                if (opdt[1] != -1) {
                    si = 0;
                }
            } else {
                opdt = [""];
            }
            ret += a2[1].replace("{LABEL}", mainCtrl.htmlSpecialChars(sd[id1].val, 2)) + opdt[0] + a2[3];
        }
        ret += a1[3];

        this._chargeInit("init_optgroup", []);
        return ret;
    },

    parse_select_dependent : function(html, name, cdt)
    {
        var ap, da, sd, hd, a1, a2, tid, el1, el2, el3, ret, opdt, i;
        if (!this._getSelectArr("combo_select", name)) {
            return "No Data for select.";
        }
        da = this.srcArr;
        sd = da.data;

        if (da.loader) {
            this.loader = this.$w0.getLoadWrapper(da.loader);
            this.loader.addListener(this, "ondataload", "onDataLoad");
            this.loader.addListener(this, "ondataerror", "onError");
            this.loader.setMode(1);
        }

        ap = this.arg.ap;
        if (!isDefined(ap, "select")) {
            ap.select = {};
        }
        ap.select[name] = {};

        a1 = this.reg.depended1.exec(html);
        a2 = this.reg.depended2.exec(a1[2]);

        this.op_pat = a2[4];

        this.sel_dt = [];

        el1 = {};
        el2 = {};
        el3 = [];
        ret = a1[1];
        for (i=0; i < da.depth; i++) {
            this.sel_dt[i] = sd;
            hd = this._arrGrConv(sd);

            tid = this.getTagId("sel_" + i);
            if (a2[3] == 1 || i == (da.depth - 1)) {
                el1[i] = true;
                ap.select[name] = hd;
            }
            if (i != (da.depth - 1)) {
                el2[i] = true;
                if (!da.key[i + 1]) {
                    da.key[i] = null;
                }
            }
            ret += a2[1].replace("{LABEL}", (isDefined(da.label, i) ? da.label[i] + ":" : "\xA0")).replace(a2[2], tid);
            opdt = this._makeOptList(hd, a2[4], da.key[i]);
            ret += opdt[0] + a2[5];
            el3[i] = opdt[1];
            sd = isDefined(da.key, i) && isDefined(sd, da.key[i]) && isDefined(sd[da.key[i]], "child") ? sd[da.key[i]].child : {};
        }
        ret += a1[3];

        this._chargeInit("init_dependent", [el1, el2, el3]);
        return ret;
    },

    //---- event is occured ---- \\
    showSelectHidden : function(evtWr, data)
    {
        var sel = this.eWr.sel;
        if (data.ptrn) {
            var opdt = this._makeOptList(this.srcArr, data.ptrn, this._getData());
            sel.elm.innerHTML     = opdt[0];
            sel.elm.selectedIndex = opdt[1];
            data.ptrn = null;
        }
        this.eWr.div.hide();
        sel.show();
        sel.elm.focus();
    },
    hideSelectHidden : function(evtWr, data)
    {
        var sel, div, txt;
        sel = this.eWr.sel;
        div = this.eWr.div;
        sel.hide();
        div.show();
        if(data.nv) {
            txt = sel.elm.selectedIndex > -1 ? sel.elm.options[sel.elm.selectedIndex].text : "";
            div.write(txt ? txt : "\xA0");
        }
    },
    onChangeDepnd : function(evtWr, data)
    {
        this.arg.ap.select[this.name] = this._arrGrConv(this.sel_dt[data.ci]);
    },

    showNextSelect : function(evtWr, data)
    {
        var val, ns, ndt, opdt, i;
        i = data.ci + 1;
        val = evtWr.elmWr.elm.value;
        ns = this.eWr["sel_" + i];
        ndt = this.sel_dt[data.ci][val];
        if (isDefined(ndt, "child")) {
            if (ndt.child != null) {
                opdt = this._makeOptList(this._arrGrConv(ndt.child), this.op_pat, -1);
                patternCtrl.setInnerHTML(ns.elm, opdt[0]);
                ns.elm.selectedIndex = -1;
                this.sel_dt[i] = ndt.child;
            } else {
                patternCtrl.setInnerHTML(ns.elm, "");
                this.sel_dt[i] = null;
            }
        } else {
            patternCtrl.setInnerHTML(ns.elm, "");
            this.sel_dt[i] = null;
            this.send("load_next_list", {"level" : i, "cval" : val});
        }
        for (i=data.ci + 2; i < this.srcArr.depth; i++) {
            patternCtrl.setInnerHTML(this.eWr["sel_" + i].elm, "");
            this.sel_dt[i] = null;
        }
    },
    setDepndKey : function(evtWr, data)
    {
        this.srcArr.key[data.ci] = evtWr.elmWr.elm.value;
        for (var i=data.ci + 1; i < this.srcArr.depth; i++) {
            this.srcArr.key[i] = null;
        }
    },

    //---- local methods ---- \\
    _subscribeMain : function(key)
    {
        if (this.eWr[key]) {
            var ed = this.eWr[key].addListener(this.arg.obj, "onchange", "onDataChange");
            ed.ei   = this.arg.ei;
            ed.key  = this.arg.key;
            ed.src  = this._getData();
            ed.name = this.name;
            return true;
        }
        return false;
    },
    _getElmWr : function()
    {
        var elWr;
        for (var k in this.eId) {
            elWr = this.$("#" + this.eId[k]);
            if (elWr) {
                this.eWr[k] = elWr;
            }
        }
    },
    _makeOptList : function(sArr, ptrn, cdt)
    {
        var ret, si, ht, k, i, k_id;
        si = -1;
        ret = "";
        i = 0;
        for (k in sArr) {
            // FX21 fix: ID -> _ID
            k_id = k.substr(0, 1) == '_' ? k.substr(1) : k;
            ht = ptrn.replace("{VALUE}", k_id);
            ht = ht.replace("{TEXT}", sArr[k]);
            if (cdt instanceof Array ? cdt.indexOf(k_id) != -1 : cdt == k_id) {
                ht = ht.replace("{SELECTED}", ' selected="selected"');
                si = i;
            } else {
                ht = ht.replace("{SELECTED}", '');
            }
            ret += ht;
            i++;
        }
        return [ret, si];
    },
    _arrGrConv : function(sArr)
    {
        var ret = {};
        for (k in sArr) {
            ret[k] = sArr[k].val;
        }
        return ret;
    },

    _getData : function()
    {
        var dt = this.arg.data;
        return dt && isDefined(dt, this.name) ? dt[this.name] : "";
    },


    _getSelectArr : function(mk, name)
    {
        var ap = this.arg.ap;
        if (ap && ap[mk] && ap[mk][name]) {
            this.srcArr = ap[mk][name];
            return true;
        } else {
            this.srcArr = {};
            return false;
        }
    },

    _chargeInit : function(met, arg)
    {
        this.setTimeout(0, this, met, arg);
    },

    config : {
        //fileForm : 'formFileUpload'
    }
});