var mainCtrl = newObject({
    url_prefix : {},
    menu    : null,
    content : {},
    topCurr : null, // [0] - DOM-element [1] - left-frame object

    currInd : 1,
    hash    : "",
    rbData  : null,

    loginTimeout : 300000,
    loginTimer   : null,
    menuLoader   : null,

    isLogin : false,
    baseUrl : "",
    winWr   : null,

    init : function(isLogin, baseUrl, winWr)
    {
        var bv = winWr.bv
        if (bv.isOpera && bv.ver < 9) {
            alert ("This system does'n work with browser Opera 8 or lower.\nPlease use FireFox 2.0+.");
            return;
        } else if (bv.isIE && (bv.ver < 7 || Math.floor(bv.ver) == 8)) {
            alert ("This system does'n work with IE8 and IE6 and lower.\nPlease use FireFox 2.0+.");
            return;
        }
        this.isLogin = isLogin;
        this.baseUrl = baseUrl;
        if (winWr)  this.$w0 = winWr;

        this.menuLoader = this.getLoadWrapper(this.baseUrl + this.config.menuLoader, "get", false);
        this.menuLoader.addListener(this, "ondataload", "onMenuLoad");
        this.menuLoader.addListener(this, "ondataerror", "onError");
        this.menuLoader.setMode(2);
    },
    onready : function(evtWr)
    {
        var winWr, win, ctrl, onm;

        // ini other objects
        winWr = this.$w0;
        win   = winWr.win;
        for (ctrl in this.config.iniObjects) {
            onm = this.config.iniObjects[ctrl];
            if(win[onm]) win[onm].init(this);
        }

        this.$(this.$(this.config.id.logout).elm.firstChild).addListener(this, "onclick", "onLogout");

        if (this.isLogin) {
            this.onLogin();
        } else {
            viewCtrl.loginReverse(false);
        }
    },

    onLogin : function()
    {
        viewCtrl.loginReverse(true);
        if (!this.menu) {
            this.menuLoader.send();
            patternCtrl.load();
        }
        this.refreshTimeout();
    },

    onLogout : function(evtWr)
    {
        evtWr.eventDrop();
        evtWr.elmWr.elm.blur();
        this.clearData();
        if (this.topCurr) {
            this.topCurr[1].condForm.hide();
            this.topCurr = null;
        }
        viewCtrl.isMenuShow = true;
        viewCtrl.selectWkPn = false;
        viewCtrl.loginReverse(false);
        loginCtrl.logout();
    },

    onMenuLoad : function(json, dom, txt)
    {
        if (txt == "ok") {
            this.url_prefix = json.url_prefix;
            this.menu = json.menu;
            var mMenu = this.$(this.config.id.mMenu);

            var hash = this.$w0.win.location.hash;
            this.hash = hash ? hash.substr(1) : "";
            this.currInd = 1;
            this.rbData = null;
            this.makeMainMenu(mMenu, this.menu, 1);

            var k = json.timeout * 0.05;
            if (k < 1) k = 1;
            else if (k > 5) k = 5;
            this.loginTimeout = (json.timeout - k) * 1000;
            this.refreshTimeout();

            if (this.rbData != null) {
                this.setTimeout(10,patternCtrl,"setRebuild",[this.rbData]);
            }
        } else {
            viewCtrl.loginReverse(false);
            alert(txt ? txt : "Menu load error");
        }
    },

    onError : function(txt)
    {
        alert("Connection error. Please try later!");
    },

    makeMainMenu : function(domPrnt, data, level)
    {
        var ul, li, tm, ind, i, j;
        ul = this.$w0.makeElement("ul");
        ul.setClass("lev" + level);
        for (i in data) {
            li = this.$w0.makeElement("li", null, data[i].name);
            ul.elm.appendChild(li.elm);
            if (data[i].top_menu) {
                li.setClass("addTop");
                tm = data[i].top_menu;
                for (j in tm) {
                    ind = this.config.hashPrefix + (this.currInd++);
                    if(this.hash == ind) {
                        this.rbData = [tm, ind, tm[j]];
                    }
                    tm[j].ind = ind;
                }
                li.addListener(this, "onclick", "makeTopMenu").tm = tm;
            } else if (data[i].sub_menu) {
                this.makeMainMenu(li, data[i].sub_menu, level + 1);
            }
        }
        domPrnt.elm.appendChild(ul.elm);
    },

    makeTopMenu : function(evtWr, data)
    {
        evtWr.eventDrop();
        if (this.topCurr && !this.topCurr[1].checkForSubmit(true)) {
            return;
        }
        this._makeTopMenu(data.tm);
    },

    startRebuild : function(rd) {
        this._activateMenu(this._makeTopMenu(rd[0], rd[1]), rd[2]);
    },

    // Event of top menu click
    makeLeftFrame : function(evtWr, data)
    {
        evtWr.elmWr.elm.blur();
        this._activateMenu(evtWr.elmWr, data.lf);
    },

    checkTimeout : function(json, txt)
    {
        if (isDefined(json, "error") && json.error == "timeout") {
            viewCtrl.loginReverse(false);
            alert(txt);
            return false;
        }
        this.refreshTimeout();
        return true;
    },

    refreshTimeout : function()
    {
        if (this.loginTimer) {
            clearTimeout(this.loginTimer);
        }
        this.loginTimer = this.setTimeout(this.loginTimeout,viewCtrl,"loginReverse",[false]);
    },

    getFullUrl : function(dest, url)
    {
        return isDefined(this.url_prefix, dest) ? this.url_prefix[dest] + url : url;
    },

    setContent : function(cnt)
    {
        for (var i in cnt) {
            if (!isDefined(this.content, cnt[i])) {
                this.content[cnt[i]] = new content(cnt[i], this);
            }
        }
        return cnt;
    },

    clearData : function() {
        if (this.menu) {
            this.removeByIDl("4clear_f");
            this.removeChildren(this.$(this.config.id.tMenu).elm, this.config.id.logout.substr(1));
            this.menu = null;
            this.content = {};
        }
    },

    removeByIDl : function(k) {
        var idl = this.config.id[k];
        for (var i in idl) {
            this.removeChildren(this.$(idl[i]).elm);
        }
    },
    removeChildren : function(oDom, id) {
        while(oDom.lastChild && (!id || !oDom.lastChild.id || oDom.lastChild.id != id)) {
            oDom.removeChild(oDom.lastChild);
        }
    },
    /**
     * mixed val - sourse value
     * integer lev - replace level (1 - quote, amp; 2 - <, >)
     */
    htmlSpecialChars : function(val, lev) {
        if (val == null) {
            return '';
        }
        if (val && lev) {
            val = (val + '');
            val = val.replace(/\&/g, '&amp;');
            if (lev >= 0) {
                val = val.replace(/\"/g, '&quot;');
            } else {
                val = val.replace(/&amp;quot;/g, '&quot;');
                val = val.replace(/&amp;lt;/g, '&lt;');
                val = val.replace(/&amp;gt;/g, '&gt;');
            }
            if (lev > 1) {
                val = val.replace(/\</g, '&lt;');
                val = val.replace(/\>/g, '&gt;');
            }
        }
        return val;
    },
    _makeTopMenu : function(dt, ind) {
        var winWr, tMenu, a, ret, i;
        winWr = this.$w0;

        this.removeByIDl("4clear_l");
        if (this.topCurr) {
            this.topCurr[1].hideCondition();
            this.topCurr = null;
        }
        viewCtrl.hideAddFrame();
        viewCtrl.hideMenu();

        viewCtrl.selectWkPn = true;

        tMenu = this.$(this.config.id.tMenu);
        this.removeChildren (tMenu.elm, this.config.id.logout.substr(1));
        for (i in dt) {
            a = winWr.makeElement("a", {href:"#" + dt[i].ind}, dt[i].name);
            a.addListener(this, "onclick", "makeLeftFrame").lf = dt[i];
            tMenu.elm.appendChild(winWr.makeElement("li", null, a).elm);
            if (isDefined(ind) && dt[i].ind == ind) {
                ret = a;
            }
        }
        return ret;
    },
    _activateMenu : function(mEl, dt) {
        viewCtrl.hideMenu()
        if (this.topCurr) {
            if (this.topCurr[1].menu.url == dt.url) {
                if (this.topCurr[0] != mEl) {
                    this.topCurr[0] = mEl;
                    mEl.addClass("current");
                }
                this.topCurr[1].refrashContent();
                return;
            }
            if (!this.topCurr[1].checkForSubmit(true)) {
                return;
            }
            this.topCurr[0].removeClass("current");
        }

        mEl.addClass("current");
        viewCtrl.hideAddFrame();
        if (!isDefined(dt, "left_frm")) {
            dt.left_frm = new leftFrame(dt, this);
        }
        this.topCurr = [mEl, dt.left_frm];
        dt.left_frm.remake();
    },

    config : {
        iniObjects : ["viewCtrl", "loginCtrl", "patternCtrl", "changeCtrl", "pagerLeftCtrl", "pagerRightCtrl", "wysiwygCtrl"],
        menuLoader : "/menu.php",
        hashPrefix  : "adm",
        id : {
            "mMenu"  : "#blMainMenuContent",
            "tMenu"  : "#blTopMenu",
            "logout" : "#logout",
            "4clear_l" : ["#frameLeftTop", "#frameLeftBottom"],
            "4clear_f" : ["#blMainMenuContent", "#frameLeftTop", "#frameLeftBottom", "#frameRightTop"]
        }
    }
});