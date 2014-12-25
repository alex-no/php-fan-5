var copy_property = function (tagId, cnt)
{
    this.cnt = cnt;
    this.winWr = mainCtrl.winWr;

    this.tagId = tagId;
    this.winWr.setTimeout(0, this, "onTimer");
    this.loader = this.winWr.getLoadWrapper(mainCtrl.baseUrl + this.config.loader, "get", false);
    this.loader.addListener(this, "ondataload", "onDataLoad");
}
implement(copy_property.prototype, [{
    tagId : "",
    sel   : null,

    cnt : null, // Content Item
    winWr  : null,
    loader : null,

    onTimer : function ()
    {
        var el, k;
        this.sel = this.winWr.getElement(this.tagId);
        for (k in this.cnt.data) {
            el = this.winWr.makeElement("option", {"value" : k}, this.cnt.data[k]);
            this.sel.elm.appendChild(el.elm);
        }
        this.cnt.parentTag.addListener(this, "onsubmit");
    },

    onsubmit : function (evtWr, data)
    {
        evtWr.eventDrop();
        this.loader.send({"id_cur" : this.cnt.cond.id_goods_variety, "id_copy" : this.sel.elm.value});
        //alert(this.sel.elm.value);
    },

    onDataLoad : function (json, dom, txt)
    {
        var doc, sel, opt, v;
        if (txt == "ok") {
            mainCtrl.content[this.cnt.curFrame.top_content[0]].refrash();
        } else {
            alert(txt)
        }
    },

    onError : function ()
    {
        alert("Data load error for id=" + this.mId);
    },

    config : {
        loader : '/root/addon/copy_property_from.php',
        html   : ''
    }
}]);