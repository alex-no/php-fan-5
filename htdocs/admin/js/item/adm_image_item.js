var admImage = newClass({
    isLine : false,

    html : "",
    name : "",

    arg  : null,
    $w0  : null,

    mId  : "",
    data : "",
    cTag : null, // tag-container. Contain "mTag" and upload form
    mTag : null, // main-tag. Contain image/images

    mainForm    : null, // main upload form
    previewForm : null, // preview image form

    loaderGet : null,
    loaderSet : null,

    codeDrag : 0,

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
        if (!isDefined(arg.ap, "image_loader") || !isDefined(arg.ap.image_loader, this.name)) {
            alert("Image loader isn't defined!");
            return;
        }

        this.cTag = this.$("#" + id).elm;

        this.mId  = arg.obj.content_type == "table" ? arg.key : arg.obj.getMainCondition();
        url = mainCtrl.getFullUrl("upload", arg.ap.image_loader[this.name]);
        this.loaderGet = this.getLoadWrapper(url, "post");
        this.loaderSet = this.getLoadWrapper(url, "post");

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
                this.imgShow(this.data);
            }
        }

        this.mainForm = this.$(this.config.forms[0]).getClone();
        this.mainForm.addListener(this, "onsubmit", "onSubmit");
        this.mainForm.addListener(this, "onreset", "onReset");
        this.cTag.appendChild(this.mainForm.elm);

        this.previewForm = this.$(this.config.forms[1]).getClone();
        this.previewForm.addListener(this, "onsubmit", "onSubmitUpdate");
        this.previewForm.addListener(this, "onreset", "onReset");
        this.cTag.appendChild(this.previewForm.elm);

        this.loaderGet.addListener(this, "ondataload", "onDataLoad");
        this.loaderGet.addListener(this, "ondataerror", "onError");
        this.loaderSet.addListener(this, "ondataload", "onDataLoad");
        this.loaderSet.addListener(this, "ondataerror", "onError");
        this.loaderSet.setMode(1);
        this.loaderSet.setTransportForm(this.mainForm, null, false, true);
        this.setTimeout(10, this, "sendGet", [this.data ? "ad" : "ld", null]);
    },

    onPreview : function(evtWr, dt)
    {
        var data, i;
        evtWr.eventDrop();
        evtWr.elmWr.elm.blur();

        if (this.isLine) {
            for (i in this.data) {
                if (dt.imgId == this.data[i].id) {
                    data = this.data[i];
                    break;
                }
            }
        } else {
            data = this.data;
        }

        if (isDefined(data, "img_type")) {
            var ets = this.previewForm.elm.elements;
            ets.alt_txt.value     = data.alt;
            ets.description.value = data.description;

            this.curImgId = data.id;
            var img = this.previewForm.elm.getElementsByTagName("img")[0]
            img.src = this.config.previewUrl + data.id + "&con=" + (isDefined(this.arg.ap, "file_connecton") ? this.arg.ap.file_connecton : "") + "&rand=" + this.loaderGet.getRandKey();

            var h = viewCtrl.getWorkHeight() - 220;
            if (h < 100) {
                h = 100;
            } else if (h > 460) {
                h = 460;
            }
            img.parentNode.style.height = h + "px";

            this.previewForm.show();
        } else {
            alert("We are sorry. Data is not ready, yet.\nTry later please.");
        }
    },

    onUpload : function(evtWr, data)
    {
        evtWr.eventDrop();
        evtWr.elmWr.elm.blur();
        this.curImgId = isDefined(data, "imgId") ? data.imgId : null;
        this.mainForm.show();
    },

    onDelete : function(evtWr, data)
    {
        evtWr.eventDrop();
        evtWr.elmWr.elm.blur();
        this.mTag.innerHTML = "";
        this.sendGet("dl", isDefined(data, "imgId") ? data.imgId : null);
    },

    onSubmit : function(evtWr)
    {
        if(evtWr.elmWr.elm.elements.image.value) {
            this.mTag.innerHTML = "";
            this.sendSet("ul", this.curImgId);
        }
        this.onReset(evtWr);
    },

    onSubmitUpdate : function(evtWr, data)
    {
        var ets =evtWr.elmWr.elm.elements;
        this.sendGet("sa", this.curImgId, {"alt" : ets.alt_txt.value, "description" : ets.description.value});
        this.onReset(evtWr);
    },

    onReset : function(evtWr)
    {
        evtWr.eventDrop();
        if (isDefined(evtWr.elmWr.elm.elements, "image")) {
            evtWr.elmWr.elm.elements.image.value = "";
        }
        evtWr.elmWr.hide();
        this.curImgId = null;
    },

// === Image drag and drop === \\
    onCodeDown : function(evtWr, data)
    {
        this.codeDrag = data.imgId;
        patternCtrl.startCodeDrag([evtWr.elmWr.elm.href, data.imgId, data.num]);
    },
    onCodeUp : function(evtWr, data)
    {
        this.codeDrag = 0;
        this.setTimeout(0, patternCtrl, "stopCodeDrag");
    },
    onCodeClick : function(evtWr)
    {
        evtWr.eventDrop();
    },
// === Get-send data === \\
    sendGet : function(op, imgId, adt)
    {
        var dt = {"op":op, "mId":this.mId, "imgId":imgId, "line":this.isLine};
        if (isDefined(adt)) {
            implement(dt, adt);
        }
        this.loaderGet.send(dt);
    },
    sendSet : function(op, imgId)
    {
        this.loaderSet.send({"op":op, "mId":this.mId, "imgId":imgId, "line":this.isLine});
    },

    onDataLoad : function(json, dom, txt)
    {
        if (!mainCtrl.checkTimeout(json, txt)) {
            return;
        }
        if (txt == "ok") {
            var i;
            if (isDefined(json, "data")) {
                if (!this.data || json.op != "sa") {
                    this.data = json.data;
                } else if (this.isLine) {
                    for (i in json.data) {
                        if (isDefined(this.data, i) && isDefined(json.data, i)) {
                            implement(this.data[i], json.data[i]);
                        }
                    }
                } else {
                    implement(this.data, json.data);
                }
            } else if (!this.data) {
                this.data = this.isLine ? [] : {};
            }
            if (json.refresh) {
                this.mTag.innerHTML = "";
                if (this.isLine) {
                    for (i in this.data) {
                        this.imgShow(this.data[i]);
                    }
                    this.imgShow([]);
                } else {
                    this.imgShow(this.data);
                }
            }
        } else {
            alert(txt)
        }
    },

    imgShow : function(data)
    {
        var id, html, evtId, winWr, elWrs, elWr, dt, i;
        id = isDefined(data, "id") && data.id ? data.id : 0;
        html = this.html.replace("{VALUE}", id);
        html = html.replace("{RAND}", id ? this.loaderSet.getRandKey() : 0);
        html = html.replace("{NAIL_SIZE}", "");
        html = html.replace("{CONNECTION}", isDefined(this.arg.ap, "file_connecton") ? this.arg.ap.file_connecton : "");
        if (isDefined(data, "order_num")) {
            html = html.replace(/\{IMG_num\}/g, data.order_num);
            dt = new Date();
            html = html.replace(/\{IMG_code\}/g, data.order_num + '_' + dt.getTime() + '-' + dt.getMilliseconds());
            html = html.replace("{NUM_show}", 1);
        } else {
            html = html.replace("{NUM_show}", 0);
            //html = html.replace(' href="#img-{IMG_num}"', '');
        }
        evtId = [];
        for (i = 1; i < 6; i++) {
            evtId[i] = patternCtrl.getTagId();
            html = html.replace("{EVENT_id" + i + "}", evtId[i]);
        }
        this.mTag.appendChild(patternCtrl.getDom(html)[0]);

        // Subscribe for active elements
        winWr = this.$w0;
        elWrs = [];
        for (i = 1; i < 6; i++) {
            elWr = this.$("#" + evtId[i]);
            if (elWr) {
                elWrs[i] = elWr;
            }
        }

        if (id && elWrs[1]) {
            if (isDefined(elWrs, 4)) {
                elWrs[4].addListener(this, "onmousedown", "onCodeDown", {
                    imgId : data.id,
                    num   : isDefined(data, "order_num") ? data.order_num : 0
                });
                elWrs[4].addListener(this, "onmouseup", "onCodeUp").imgId = data.id;
                elWrs[4].addListener(this, "onclick", "onCodeClick").imgId = data.id;
            }
            if (isDefined(elWrs, 5)) {
                elWrs[5].addListener(this, "onclick", "onPreview").imgId = data.id;
            }
            elWrs[1].addListener(this, "onclick", "onPreview").imgId = data.id;
            elWrs[2].addListener(this, "onclick", "onDelete").imgId  = data.id;
            elWrs[3].addListener(this, "onclick", "onUpload").imgId  = data.id;
        } else if (elWrs[1]) {
            elWrs[1].hide();
            elWrs[2].hide();
            elWrs[3].addListener(this, "onclick", "onUpload");
        }

    },

    onError : function()
    {
        alert("Image load error for id=" + this.mId);
    },

    config : {
        forms : ['#formImageUpload', '#imgPreview'],
        previewUrl : "/file.php?id=" // ToDo: URL from config PHP
    }
});