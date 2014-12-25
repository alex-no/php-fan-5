var content = newClass({
     content_type : "info",

    type  : "",
    url_  : "",
    curFrame : null,
    parentTag : null,
    stubTag   : null,
    showAfterLoad : null,
    isMain : false,
    useMainPage : false,
    isLoad : false,

    evtDelay : 0,
    idPref   : "",
    idSuff   : 1,

    delArr   : null,
    rgtArr   : null,

    // Input
    code    : "",
    data    : null,
    srcData : null,
    param   : null,
    extra   : null,
    pager   : null,
    ei_mode : null,
    // Output
    edit  : null,
    ins   : null,
    del   : null,
    first : true,
    cond  : null,
    order : null,

    __staticVar : {
        itemNum : 1
    },

   init : function(url_, mainCtrl)
    {
        this.url_ = url_;
        this.mainCtrl = mainCtrl;

        this.$w0 = mainCtrl.$w0;

        this.loader = this.getLoadWrapper(mainCtrl.getFullUrl("data", url_), "post");
        this.loader.addListener(this, "ondataload", "onDataLoad");
        this.loader.addListener(this, "ondataerror", "onError");
        this.pager = [1, 1];

        this.idPref = "cnt" + (this.__staticVar.itemNum++) + "_";
    },

    // set Current Frame for this content-item
    setCurrentFrame : function(frm, tag)
    {
        this.curFrame  = frm;
        this.parentTag = tag;

        this.stubTag = frm.winWr.makeElement("div");
        this.stubTag.hide();
        tag.elm.parentNode.insertBefore(this.stubTag.elm, tag.elm);
    },

    // load data for show
    load : function(shw)
    {
        var data, pa
        if (this.isLoad) {
            return;
        }
        this.isLoad = true;
        this.showAfterLoad = shw;

        data = {};
        pa = ["first", "edit", "ins", "del", "cond", "order"];
        for (var i in pa) {
            if (this[pa[i]]) {
                data[pa[i]] = this[pa[i]];
            }
        }
        if (this.useMainPage) {
            this.pager[0] = this.mainCtrl.content[this.curFrame.main_content].pager[0];
        }
        if (this.isMain || this.useMainPage) {
            data.page = this.pager[0];
        }
        this.loader.send(data);
        if (shw) {
            this.parentTag.hide();
            this.stubTag.setClass("stub_load");
            this.stubTag.show();
        }
    },

    refrash : function()
    {
        if (this.isLoad) {
            return;
        }
        if (this.isChanged() && !confirm("Save changes in this block?")) {
            this.cancelChange();
        } else {
            this.load(true);
        }
    },

    cancelChange : function()
    {
        if (this.isChanged()) {
            this.edit = null;
            this.ins  = null;
            this.del  = null;
            this.load(true);
        }
        changeCtrl.delCnt(this.url_);
    },

    show : function()
    {
        if (this.parentTag) {
            this.parentTag.show();
            if (this.isMain) {
                this.curFrame.setupPager(this.pager);
            }
            if (this.extra && isDefined(this.extra, "tagId")) {
                this.parentTag.elm.setAttribute("id", this.extra.tagId);
            }

            this.delArr = null;
            this.rgtArr = null;
            var el = [];
            try {
                patternCtrl.setInnerHTML(this.parentTag.elm, this.getCode(el));
                this.stubTag.hide();
                if (this.delArr != null || this.rgtArr != null) {
                    this.attachRows();
                }
                this.setTimeout(this.evtDelay, patternCtrl, "subscribeEvent", [el]);
            } catch(e) {
                alert(e.message);
                this.stubTag.hide();
            }
        }
    },

    // On Data load from server
    onDataLoad : function(json, dom, txt)
    {
        this.isLoad = false;
        if (!this.mainCtrl.checkTimeout(json, txt)) {
            this.parentTag.show();
            this.stubTag.hide();
            return;
        }
        if (txt == "ok") {
            this.stubTag.setClass("stub_make");
            if (!this.type && json.type) {
                implement(this, [content_addon[json.type]]);
            }
            var pa = ["code", "data", "extra", "pager", "ei_mode", "useMainPage"];
            for (var i in pa) {
                if (isDefined(json, pa[i])) {
                    this[pa[i]] = json[pa[i]];
                }
            }
            if (isDefined(json, "param")) {
                if (!this.param) {
                    this.param = {};
                }
                for (var k in json.param) {
                    this.param[k] = json.param[k];
                }
            }

            this.srcData = this.data;

            if (isDefined(json, "code")) {
                this.preParseCode();
            }
            if (this.first) {
                this.first = false;
            } else if (this.isChanged()) {
                this.edit = null;
                this.ins  = null;
                this.del  = null;
                changeCtrl.delCnt(this.url_);
            }

            if (this.showAfterLoad) {
                this.parentTag.elm.innerHTML = "";
                this.runBeforeShow();
                this.show();
            } else {
                this.stubTag.hide();
            }
        } else {
            this.parentTag.show();
            this.stubTag.hide();
            alert(txt);
        }
    },

    onError : function(txt)
    {
        this.parentTag.show();
        this.stubTag.hide();
        this.isLoad = false;
        alert(txt);
    },

    isChanged : function()
    {
        return this.edit || this.ins || this.del;
    },

    /**
     *
     * tps: ins, up, del
     */
    setChange : function(tps, key, nm, vl, src)
    {
        var dvl = this._getDefaultVar();
        var tp = tps == "ins" || this._isInsertById(key) ? "ins" : tps;
//        if (vl == src && (tp != "ins" || !isDefined(dvl, nm))) {
        if (vl == src) {
            var da, i;
            da = this._getDataArr(tp, key);
            if (da && isDefined(da, nm)) {
                delete da[nm];
                if (key != null) {
                    if (this._isNotEmpty(da)) {return;}
                    delete this[tp][key];
                }
                if (this._isNotEmpty(this[tp])) {return;}
                this[tp] = null;
                if (!this.isChanged()) {
                    changeCtrl.delCnt(this.url_);
                }
            }
            return;
        }
        if (!this[tp]) {
            this[tp] = {};
        }
        if (key != null && !isDefined(this[tp], key)) {
            this[tp][key] = {};
        }
        if (tps == "ins") {
            this.insNewFields(key);
        }
        if (tp == "ins") {
            if (isDefined(this.param, "convId") && this.param.convId != null) {
                var ak = this.param.convId[1];
                if (tps != "ins" && !isDefined(dvl, ak)) {
                    dvl[ak] = key.substr(this.param.convId[0].length)
                }
            }
            var svl = this._getDataArr(tp, key);
            for (var k in dvl) {
                if (!isDefined(svl, k)) {
                    svl[k] = dvl[k];
                }
            }
        }
        this._getDataArr(tp, key)[nm] = vl;
        changeCtrl.addCnt(this.url_);
    },
    getTagId : function(){
        return this.idPref + (this.idSuff++);
    },

// === Internal methods === \\
    _getDataArr : function(tp, key){
        if (key == null) {
            return this[tp] ? this[tp] : null;
        }
        return this[tp] && isDefined(this[tp], key) ? this[tp][key] : null;
    },
    _getDefaultVar : function(val){
        var reg, v, a, i;
        if (!isDefined(val)) {
            //val = isDefined(this.param, "default_val") ? {} : null;
            val = {};
        }
        if (isDefined(this.param, "default_val")) {
            reg = /^\[condition_(.*?)\]$/i;
            for (i in this.param.default_val) {
                v = this.param.default_val[i];
                a = reg.exec(v);
                if (!a) val[i] = v;
                else if (this.cond) val[i] = this.cond[a[1]];
                else this.alert('Incorrect condition for "' + i + '"');
            }
        }
        return val;
    },
    _isNotEmpty : function(arr){
        for (var i in arr) {
            return true;
        }
        return false;
    },
    _isInsertById : function(id){
        if(isDefined(this.param, "convId") && this.param.convId) {
            var k = this.param.convId[0];
            return (id + "").substr(0, k.length) == k;
        }
        return false;
    },
// -------- events -------- \\
    onDataChange : function(evtWr, data)
    {
        this.setChange(data.ei, data.key, data.name, evtWr.elmWr.elm.value, data.src);
    },
    onInputClick : function(evtWr, data)
    {
        this.setChange(data.ei, data.key, data.name, data.val[evtWr.elmWr.elm.checked ? 1 : 0], data.src);
    },
    onRowDelete : function(evtWr, data)
    {
        evtWr.eventDrop();
        evtWr.elmWr.elm.blur();
        if (data.ei == "edit" || this[data.ei] && this[data.ei][data.key]) {
            for (var i in data.nl) {
                data.nl[i].parentNode.removeChild(data.nl[i]);
            }
        }

        var ei = data.ei == "ins" || this._isInsertById(data.key) ? "ins" : data.ei;
        if (this[ei] && this[ei][data.key]) {
            delete this[ei][data.key];
        }

        if (ei == "edit") {
            this.setChange("del", null, data.key, 1, 0);
        } else if (ei == "ins") {
            if (this._isNotEmpty(this.ins)) {return;}
            this.ins = null;
            if (!this.isChanged()) {
                changeCtrl.delCnt(this.url_);
            }
        }
    },

    onWysiwygShow : function(evtWr, dt)
    {
        wysiwygCtrl.show(this, evtWr.elmWr, dt)
    },

// -------- rededefined methods -------- \\
    getCode : function(el)
    {
        return patternCtrl.parseHtml(this.code, this, el, this.data, this.param, this.ei_mode == "ins" ? "ins" : "edit");
    },

    preParseCode : function()
    {
    },

    runBeforeShow : function()
    {
    },

    attachRows : function()
    {
    },

    insNewFields : function()
    {
    },

// -------- config -------- \\
    config : {
        idCondForm : "conditionForm"
    }
});