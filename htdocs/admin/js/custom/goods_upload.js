var goods_upload = {
    tagId : "",

    cnt : null, // Content Item
    winWr  : null,

    init : function (tagId, cnt)
    {
        this.cnt = cnt;
        this.winWr = mainCtrl.winWr;
        this.tagId = tagId;

        cnt.parentTag.addListener(this, "onsubmit");
        cnt.parentTag.elm.setAttribute("method", "post");
        cnt.parentTag.elm.setAttribute("enctype", "multipart/form-data");

        loader = this.winWr.getLoadWrapper(mainCtrl.baseUrl + this.config.loader, "post");
        loader.setMode(1);
        loader.setTransportForm(this.cnt.parentTag);
        loader.addListener(this, "ondataload", "onDataLoad");
        this.loader = loader;
    },

    onsubmit : function (evtWr, data)
    {
        evtWr.eventDrop();
        this.loader.send({"id_sup" : this.cnt.cond.id_supplier});
    },

    onDataLoad : function (json, dom, txt)
    {
        if (txt == "ok") {
            this.winWr.setTimeout(0, this, "onTimer");
        } else {
            alert(txt);
        }
    },

    onTimer : function ()
    {
        var cFr = this.cnt.curFrame;
        mainCtrl.content[cFr.top_content[0]].refrash();
        mainCtrl.content[cFr.bottom_content[0]].refrash();
    },

    config : {
        loader : '/root/addon/copy_property_from.php',
        html   : ''
    }
};