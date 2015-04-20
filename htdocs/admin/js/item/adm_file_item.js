var admFile = newClass({
    isLine : false,

    html : "",
    name : "",

    arg  : null,
    $w0  : null,

    mId  : "",
    data : "",
    cTag : null, // tag-container. Contain "mTag" and upload form
    mTag : null, // main-tag. Contain file/files
    form : null, // upload form

    loader : null,

    init : function(pc, html, name)
    {
        this.arg = pc.arg;
        this.$w0 = pc.$w0;

        this.html = html;
        this.name = name;
    },

    getHTML : function()
    {
        if (this.arg.ei == "edit") {
            var id = patternCtrl.getTagId();
            this.setTimeout(0, this, "initHTML", [id]);
            return '<div id="' + id + '"></div>';
        }
        return "";
    },
    
    initHTML : function(id)
    {
        var arg, url, a;
        arg = this.arg;
        if (!isDefined(arg.ap, "file_loader") || !isDefined(arg.ap.file_loader, this.name)) {
            alert("File loader isn't defined!");
            return;
        }
        
        this.cTag = this.$("#" + id).elm;
        
        this.mId  = arg.obj.content_type == "table" ? arg.key : arg.obj.getMainCondition();
        url = mainCtrl.getFullUrl("upload", arg.ap.file_loader[this.name]);
        this.loader = this.getLoadWrapper(url, "post");
        
        a = /(.*?)\[(.+?)\](.*)/.exec(this.html);
        if (a) {
            this.isLine = true;
            this.html = a[2];
            if(a[1]) {
                this.mTag = patternCtrl.getDom(a[1] + a[3])[0];
            } else {
                this.mTag = this.$w0.doc.createElement("div");
            }
            this.cTag.appendChild(this.mTag);
        } else {
            this.mTag = this.$w0.doc.createElement("div");
            this.cTag.appendChild(this.mTag);
            if (isDefined(arg.data, this.name)) {
                this.data = {"id" : arg.data[this.name]};
                this.fileShow(this.data);
            }
        }
        
        this.form = this.$(this.config.fileForm).getClone();
        this.form.addListener(this, "onsubmit", "onSubmit");
        this.form.addListener(this, "onreset", "onReset");
        this.cTag.appendChild(this.form.elm);

        this.loader.addListener(this, "ondataload", "onDataLoad");
        this.loader.addListener(this, "ondataerror", "onError");
        this.loader.setMode(1);
        this.loader.setTransportForm(this.form, null, false, true);
        this.setTimeout(10, this, "send", ["ld", null]);
    },

    onUpload : function(evtWr, data)
    {
        evtWr.eventDrop();
        evtWr.elmWr.elm.blur();
        this.curFileId = isDefined(data, "fileId") ? data.fileId : null;
        this.form.show();
    },

    onDelete : function(evtWr, data)
    {
        evtWr.eventDrop();
        evtWr.elmWr.elm.blur();
        this.mTag.innerHTML = "";
        this.send("dl", isDefined(data, "fileId") ? data.fileId : null);
    },

    onSubmit : function(evtWr)
    {
        if(evtWr.elmWr.elm.elements.file.value) {
            //this.mTag.innerHTML = "";
            this.send("ul", this.curFileId);
        }
        this.onReset(evtWr);
    },

    onReset : function(evtWr)
    {
        evtWr.eventDrop();
        evtWr.elmWr.elm.elements.file.value = "";
        evtWr.elmWr.hide();
        this.curFileId = null;
    },

    send : function(op, fileId)
    {
        this.loader.send({"op":op, "mId":this.mId, "fileId":fileId, "line":this.isLine});
    },

    onDataLoad : function(json, dom, txt)
    {
        if (!mainCtrl.checkTimeout(json, txt)) {
            return;
        }
        if (txt == "ok") {
            this.mTag.innerHTML = "";
            this.data = isDefined(json, "data") && json.data ? json.data : {};
            if (this.isLine) {
                for (var i in this.data) {
                    this.fileShow(this.data[i]);
                }
                this.fileShow([]);
            } else {
                this.fileShow(this.data);
            }
        } else {
            alert(txt)
        }
    },

    fileShow : function(data)
    {
        var id, filename, html, evtId, elWrs, elWr, i;
        id = isDefined(data, "id") && data.id ? data.id : 0;
        filename = data.filename ? data.filename : "";
        html = this.html.replace("{VALUE}", id);
        html = html.replace("{FILE_NAME}", filename);
        html = html.replace("{STYLE_DISPLAY}", filename ? "inline" : "block");
        html = html.replace("{RAND}", id ? this.loader.getRandKey() : 0);
        evtId = [];
        for (i = 1; i < 4; i++) {
            evtId[i] = patternCtrl.getTagId();
            html = html.replace("{EVENT_id" + i + "}", evtId[i]);
        }
        this.mTag.appendChild(patternCtrl.getDom(html)[0]);

        // Subscribe for active elements
        elWrs = [];
        for (i = 1; i < 4; i++) {
            elWr = this.$("#" + evtId[i]);
            if (elWr) {
                elWrs[i] = elWr;
            }
        }

        if (id) {
            elWrs[2].addListener(this,  "onclick", "onDelete").fileId = data.id;
            elWrs[3].addListener(this,  "onclick", "onUpload").fileId = data.id;
        } else {
            elWrs[2].hide();
            elWrs[3].addListener(this, "onclick", "onUpload");
        }
    },

    onError : function()
    {
        alert("File load error for id=" + this.mId);
    },


    config : {
        fileForm : '#formFileUpload'
    }
});