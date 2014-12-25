var make_thumbnail = function (tagId, cnt)
{
    this.cnt = cnt;
    this.winWr = mainCtrl.winWr;

    this.tagId = tagId;
    this.winWr.setTimeout(0, this, "onTimer");
    this.loader = this.winWr.getLoadWrapper(mainCtrl.baseUrl + this.config.loader, "get", false);
    this.loader.addListener(this, "ondataload", "onDataLoad");
}
implement(make_thumbnail.prototype, [{
    tagId : "",
    sel   : null,

    cnt : null, // Content Item
    winWr  : null,
    loader : null,

    onTimer : function ()
    {
        var el, n, k;
        this.sel = this.winWr.getElement(this.tagId);
        n = 0;
        for (k in this.cnt.data) {
            el = this.winWr.makeElement("option", {"value" : k}, this.cnt.data[k]);
            this.sel.elm.appendChild(el.elm);
            n++;
        }
        if (n) {
            this.cnt.parentTag.addListener(this, "onsubmit");
            this.winWr.getElmWrapper(this.sel.elm.parentNode).show();
        }
    },

    onsubmit : function (evtWr, data)
    {
        evtWr.eventDrop();
        this.loader.send({"id_cur" : this.cnt.cond.id_goods_variety, "id_file" : this.sel.elm.value});
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
        loader : '/root/addon/make_thumbnail_from.php',
        html   : ''
    }
}]);