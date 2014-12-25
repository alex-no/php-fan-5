var replaceAddress = function (pc, html, name)
{
    this.winWr = pc.winWr;
    
    this.arg  = pc.arg;  // pattern control
    this.name = name;
}
implement(replaceAddress.prototype, [{
    html : "",
    name : "",
    opt  : null,

    mId  : "",
    data : "",
    cTag : null, // tag-container. Contain "mTag" and upload form

    divWr : null, // wrapper of activ DIV
    selWr : null, // wrapper of activ SELECT

    arg : null,
    obj : null,

    winWr  : null,
    loader : null,

    init : function (id1, id2)
    {
        var winWr, arg;
        winWr = this.winWr

        arg = this.arg;
        this.mId = arg.key;
        this.obj = arg.obj;

        this.loader = winWr.getLoadWrapper(this.config.loader[this.name], "get", false);
        this.loader.addListener(this, "ondataload", "onDataLoad");
        this.loader.addListener(this, "ondataerror", "onError");
        this.loader.setMode(2);
        
        this.divWr = winWr.getElement(id1);
        this.divWr.addListener(this, "onclick", "onClickDiv");
        this.selWr = winWr.getElement(id2);
        this.selWr.addListener(this, "onblur", "onBlurSelect");
        this.selWr.addListener(this, "onchange", "onChangeSelect");
    },

    onClickDiv : function (evtWr)
    {
        this.divWr.hide();
        if (this.opt) {
            this.selWr.show();
            this.selWr.elm.focus();
        } else {
            this.loader.send({"mId":this.mId});
        }
    },

    onBlurSelect : function (evtWr)
    {
        this.selWr.hide();
        this.divWr.show();
    },

    onChangeSelect : function (evtWr)
    {
        this.onBlurSelect(evtWr);
        var v = this.selWr.elm.value;
        this.divWr.write(this.opt[v]);
        this.obj.setChange("edit", this.mId, this.name, v, "");
    },

    onDataLoad : function (json, dom, txt)
    {
        var doc, sel, opt, v;
        if (txt == "ok") {
            doc = this.winWr.doc;
            this.opt = json.opt;
            sel = this.selWr.elm;
            for (v in this.opt) {
                opt = doc.createElement("option");
                opt.setAttribute("value", v);
                opt.appendChild(doc.createTextNode(this.opt[v]));
                sel.appendChild(opt);
            }
            this.selWr.show();
            sel.selectedIndex = -1;
            sel.focus();
        } else {
            alert(txt)
        }
    },

    onError : function ()
    {
        alert("Data load error for id=" + this.mId);
    },

    getHTML : function ()
    {
        if (this.arg.ei == "edit") {
            var id1 = patternCtrl.getTagId();
            var id2 = patternCtrl.getTagId();
            this.winWr.setTimeout(0, this, "init", [id1, id2]);
            return this.config.html_1 + id1 + this.config.html_2 + id2 + this.config.html_3;
        }
        return "<div></div>";
    },

    config : {
        loader : {'regions_replace_to':'root/addon/alt_regions.php', 'cities_replace_to':'root/addon/alt_cities.php'},
        html_1 : '<div class="sel_hidden"><div id="',
        html_2 : '">заменить на ...</div><select id="',
        html_3 : '" class="select1"></select></div>'
    }
}]);