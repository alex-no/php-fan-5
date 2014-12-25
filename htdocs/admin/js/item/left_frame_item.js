var leftFrame = newClass({
    menu : null,

    condition : null,
    condForm  : null,

    title : "",
    top_content    : null,
    bottom_content : null,
    right_frm      : null,
    active_right   : null,
    main_content   : null,
    current_right  : null, // Key of selected right frame
    active_elm     : null,

    mainContentRight : null,

    init : function(menu, mainCtrl)
    {
        var conf, k;
        this.menu     = menu;
        this.mainCtrl = mainCtrl;

        this.$w0 = mainCtrl.$w0;

        conf = this.config;
        for (k in conf.idList) {
            this[k]  = this.$(conf.idList[k]);
        }

        this.loader = this.getLoadWrapper(mainCtrl.getFullUrl("structure", menu.url), "get", false);
        this.loader.addListener(this, "ondataload", "onDataFrameLoad");
        this.loader.addListener(this, "ondataerror", "onError");
    },
    remake : function()
    {
        this.showCondition();
        this.mainCtrl.removeChildren(this.frameTop.elm);
        this.mainCtrl.removeChildren(this.frameBot.elm);
        if (!this.top_content && !this.bottom_content) {
            this.loader.send();
            return;
        }
        this.showContent();
    },

    showCondition : function()
    {
        if (this.condition) {
            this.condition.show();
        } else {
            this.condForm.hide();
        }
        viewCtrl.setContentHeight();
    },

    hideCondition : function()
    {
        this.condForm.hide();
        viewCtrl.setContentHeight();
    },

    showContent : function()
    {
        var winWr, title, tag, cnt, ul, li, a, i;
        winWr = this.$w0;

        pagerLeftCtrl.resetPager();

        if (this.title) {
            title = this.title;
            tag = winWr.makeElement("h1", null);
            this.frameTop.elm.appendChild(tag.elm);
            if (this.condition) {
                this.condition.defineTitle([tag, title]);
            } else {
                tag.elm.innerHTML = title;
                winWr.doc.title = this.title.replace(/\<.+?\>/g, '');
            }
        } else {
            winWr.doc.title = this.menu.name;
        }

        for (i in this.top_content) {
            tag = winWr.makeElement("form", {"action" : "#"});
            this.frameTop.elm.appendChild(tag.elm);
            cnt = this.getContent(this.top_content, i);
            if (cnt) {
                if(this.condition) {
                    cnt.cond = this.condition.cond;
                }
                cnt.isMain = this.top_content[i] == this.main_content;
                cnt.setCurrentFrame(this, tag)
                cnt.load(true);
            }
        }
        for (i in this.bottom_content) {
            tag = winWr.makeElement("form", {"action" : "#"});
            this.frameBot.elm.appendChild(tag.elm);
            cnt = this.getContent(this.bottom_content, i);
            if (cnt) {
                if(this.condition) {
                    cnt.cond = this.condition.cond;
                }
                cnt.isMain = false;
                cnt.setCurrentFrame(this, tag)
                cnt.load(true);
            }
        }

        this.rightSubSelect.elm.innerHTML = "";
        if (this.right_frm) {
            ul = winWr.makeElement("ul");
            for (i in this.right_frm) {
                a = winWr.makeElement("a", {"href" : "#"}, this.right_frm[i].name);
                a.addListener(this.mainCtrl.topCurr[1], "onclick", "activeRight").frm = this.right_frm[i];
                li = winWr.makeElement("li", null, a.elm);
                if (this.right_frm[i].key == this.current_right) {
                    li.setClass("current");
                }
                this.right_frm[i].menu_el = li;
                ul.elm.appendChild(li.elm);
            }
            this.rightSubSelect.elm.appendChild(ul.elm);
        }
    },

    refrashContent : function()
    {
        if (!this.checkForSubmit(false)) {
            return;
        }
        var cnt, i;
        if (this.top_content) {
            for (i in this.top_content) {
                cnt = this.getContent(this.top_content, i);
                if(this.condition) {
                    cnt.cond = this.condition.cond;
                }
                cnt.refrash();
            }
        }
    },

    requestPage : function(pn)
    {
        var mc = this.getContent(this, "main_content");
        mc.pager[0] = pn;
        mc.refrash();
    },

    setupPager : function(pg)
    {
        pagerLeftCtrl.setupPager(pg);
    },

    checkForSubmit : function(ls)
    {
        var chLeft, chRigt, i;
        chLeft = [];
        chRigt = [];
        for (i in this.top_content) {
            if (this.getContent(this.top_content, i).isChanged()){
                chLeft.push(this.top_content[i])
            }
        }
        if (this.active_right) {
            for (i in this.active_right.top_content) {
                if (this.getContent(this.active_right.top_content, i).isChanged()) {
                    chRigt.push(this.active_right.top_content[i])
                }
            }
        }
        if (!chLeft.length && !chRigt.length) {
            return true;
        }
        if (!confirm("Save current changes?")) {
            return false;
        }
        var i;
        for (i in chRigt) {
            this.getContent(chRigt, i).load(false);
        }
        if (ls) {
            for (i in chLeft) {
                this.getContent(chLeft, i).load(false);
            }
        }
        return !chRigt.length;
    },

    onDataFrameLoad : function(json, dom, txt)
    {
        if (!this.mainCtrl.checkTimeout(json, txt)) {
            return;
        }
        if (txt == "ok") {
            if (json.condition) {
                this.condition = new condition(json.condition, this);
                this.showCondition();
            }

            if (json.title) { this.title = json.title; }

            if (json.top_content) { this.top_content = this.mainCtrl.setContent(json.top_content); }
            if (json.bottom_content) { this.bottom_content = this.mainCtrl.setContent(json.bottom_content); }
            if (json.right_frm) {
                this.right_frm = [];
                for (var k in json.right_frm) {
                    this.right_frm[k] = new rightFrame(k, json.right_frm[k], this);
                    if (!this.current_right) {this.current_right = k;}
                }
            }
            this.main_content = isDefined(json, "main_content") ? json.main_content : (this.top_content ? this.top_content[0] : null);

            this.showContent();
        } else {
            alert(txt);
        }
    },

    onError : function(txt)
    {
        alert(txt);
    },

    getContent : function(obj, prop)
    {
        return this.mainCtrl.content[obj[prop]];
    },

    // Control of right frame
    openRight : function(evtWr, data)
    {
        evtWr.eventDrop();
        evtWr.elmWr.elm.blur();
        if (this.checkForSubmit()) {
            viewCtrl.showAddFrame();
            if (data.cond) {
                for (var i in this.right_frm) {
                    this.right_frm[i].cond = data.cond;
                }
            }
            this.lightActiveRow(data.nl);
            this.active_right = this.right_frm[data.key ? data.key : this.current_right]
            this.active_right.activate();
        }
    },

    activeRight : function(evtWr, data)
    {
        evtWr.eventDrop();
        evtWr.elmWr.elm.blur();
        if (this.checkForSubmit()) {
            data.frm.activate();
        }
    },

    closeRight : function()
    {
        if (this.checkForSubmit()) {
            this.lightActiveRow();
            return true;
        }
        return false;
    },

    lightActiveRow : function(aElm)
    {
        var i;
        if (this.active_elm) {
            for (i in this.active_elm) {
                this.$(this.active_elm[i]).removeClass("active");
            }
        }
        if (aElm) {
            for (i in aElm) {
                this.$(aElm[i]).addClass("active");
            }
            this.active_elm = aElm;
        } else {
            this.active_elm = null;
        }
    },

    config : {
        idList : {condForm : '#conditionForm', frameTop : '#frameLeftTop', frameBot : '#frameLeftBottom', rightSubSelect : '#blAddSubSelect'}
    }
});