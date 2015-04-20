var condition = newClass({
    code : "",
    cond : null,
    param : "",
    extra : null,

    left_frm : null,
    condForm : null,
    title : null,

    evtDelay : 0,
    idPref   : "",
    idSuff   : 1,
    
    __staticVar : {
        itemNum : 1
    },

    init : function(cd, left_frm)
    {
        this.code  = isDefined(cd, "code")  ? cd.code  : "";
        this.cond  = isDefined(cd, "cond")  ? cd.cond  : {};
        this.param = isDefined(cd, "param") ? cd.param : null;
        this.extra = isDefined(cd, "extra") ? cd.extra : {};

        this.left_frm = left_frm;
        this.$w0 = left_frm.winWr;
        this.condForm  = this.$(this.config.idCondForm);
        this.condForm.addListener(this, "onsubmit", "onSubmit");
        
        this.idPref = "cnd" + (this.__staticVar.itemNum++) + "_";

        this.show();
    },
    show : function()
    {

        this.condForm.elm.innerHTML = "";
        var el = [];
        var html = patternCtrl.parseHtml(this.code, this, el, this.cond, this.param);
        if (patternCtrl.setInnerHTML(this.condForm.elm, html)) {
            this.setTimeout(this.evtDelay, patternCtrl, "subscribeEvent", [el]);
            this.condForm.show();
        }
    },
    getTagId : function(){
        return this.idPref + (this.idSuff++);
    },
    setChange : function(tps, key, nm, vl, src)
    {
        this.cond[nm] = vl;
        if (isDefined(this.extra, "active") && this.extra.active[nm]) {
            this.left_frm.refrashContent();
            if (this.title) {
                this.setTitle();
            }
        }
    },
    defineTitle : function(ttl)
    {
        this.title = ttl;
        this.setTitle()
    },
    setTitle : function()
    {
        var re, tit, res, v, v_;
        re = /\{condition_(.+?)\}/ig;
        tit = this.title[1];
        while ((res = re.exec(tit))) {
            if (isDefined(this.cond, res[1])) {
                v = this.cond[res[1]];
                if (isDefined(this.param.select, res[1]) && isDefined(this.param.select[res[1]], v)) {
                    v = this.param.select[res[1]][v];
                }
                // FX21 fix: ID -> _ID
                v_ = '_' + this.cond[res[1]];
                if (isDefined(this.param.select, res[1]) && isDefined(this.param.select[res[1]], v_)) {
                    v = this.param.select[res[1]][v_];
                }
                tit = tit.replace(res[0], v);
            }
        }
        this.title[0].elm.innerHTML = tit;
        this.$w0.doc.title = tit.replace(/\<.+?\>/g, '');
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

    onSubmit : function(evtWr)
    {
        evtWr.eventDrop();
        this.left_frm.remake();
    },

    config : {
        idCondForm : "#conditionForm"
    }
});