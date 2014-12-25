var viewCtrl = newObject({
    isMenuShow : true,
    isAddShow  : false,
    menuTimer  : null,
    resizeIsOn : false,
    addWidth   : null,
    selectWkPn : false, // Work panel is selected

    init : function(mainCtrl)
    {
        var winWr, v, i;
        this.mainCtrl = mainCtrl;
        winWr = this.$w0 = mainCtrl.$w0;

        this.$('#closeAdd').addListener(this, "onclick", "closeAddFrame");
        this.$('#vSeparator2').addListener(this, "onmousedown", "startResizeAdd");

        for (var i in this.config.idList) {
            v = this.config.idList[i];
            this[v] = this.$("#" + v);
        }

        winWr.addListener(this, "onresize", "setContentHeight");
        winWr.addListener(this, "onscroll", "setContentHeight");

        this.blMainMenu.addListener(this, "onmouseover", "menuOver");
        this.blMainMenu.addListener(this, "onmouseout", "menuOut");
        this.vSeparator2_.addListener(this, "onmouseup", "endResizeAdd");

        this.addWidth = this.config.defaultAddWidth*this.$w0.getDocumentWidth();
    },

    loginReverse : function(t)
    {
        this.login_part.setDisplay(!t);
        this.work_part.setDisplay(t);
        if (t) {
            this.setContentHeight();
            this.hideAddFrame();
            if (this.isMenuShow) {
                this.isMenuShow = false;
                this.onMenuTimer();
                //this.startTimer("startDelay");
            }
        } else {
            this.$w0.win.title = "Admin system";
        }
    },

    getWorkHeight : function()
    {
        return this.$w0.getDocumentHeight() - this.blTop.getHeight() - this.blBottom.getHeight();
    },

    setContentHeight : function()
    {
        var h = this.getWorkHeight();
        this.blContent.style.height = h + "px";
        var h2 = (h - 16) + "px";
        this.blMainMenuContent.style.height = h2;
        this.blMainMenu.style.height   = h2;
        this.vSeparator2_.style.height = h2;
        this.blMain.style.height = h2;
        this.blAdd.style.height  = h2;
        this.doResizeAdd();
    },

    menuOver : function(evtWr)
    {
        if (this.selectWkPn) {
            if(!this.isMenuShow) this.startTimer("inDelay");
            else this.clearTimer();
        }
    },
    menuOut : function(evtWr)
    {
        if (this.selectWkPn) {
            if(this.isMenuShow) this.startTimer("outDelay");
            else this.clearTimer();
        }
    },
    hideMenu : function()
    {
        this.isMenuShow = true;
        this.onMenuTimer();
    },
    startTimer : function(t)
    {
        this.menuTimer = this.setTimeout(this.config.menu[t], this, "onMenuTimer");
    },
    clearTimer : function()
    {
        if (this.menuTimer) clearTimeout(this.menuTimer);
        this.menuTimer = null;
    },
    onMenuTimer : function() {
        this.clearTimer();
        this.isMenuShow = !this.isMenuShow;
        this.blMainMenuContent.setDisplay(this.isMenuShow);
        this.blMainMenu.style.width = this.config.menu[this.isMenuShow ? "widthMax" : "widthMin"] + "px";
        this.vSeparator1_.setClass("line_arrow" + (this.isMenuShow ? 1 : 2));
    },
    
    showAddFrame : function()
    {
        this.isAddShow = true;
        this.doResizeAdd();
    },
    hideAddFrame : function() {
        this.isAddShow = false;
        this.doResizeAdd();
    },
    closeAddFrame : function(evtWr) {
        evtWr.eventDrop();
        if(this.mainCtrl.topCurr[1].closeRight()) {
            this.hideAddFrame();
        }
    },
    switchAddFrame : function()
    {
        this[this.isAddShow ? "hideAddFrame" : "showAddFrame"]();
    },

    startResizeAdd : function(evtWr)
    {
        this.resizeIsOn = true;
        this.work_part.addListener(this, "onmouseup", "endResizeAdd");
        this.work_part.addListener(this, "onmousemove", "moveSeparator");
        this.work_part.addListener(this, this.$w0.bv.isIE ? "onselectstart" : "onmousedown", "cancelSelect");
        this.vSeparator2_.setAbsLeft(evtWr.absX < 7 ? 7 : evtWr.absX - 3);
        this.vSeparator2_.setDisplay(true);
    },
    moveSeparator : function(evtWr)
    {
        this.vSeparator2_.setAbsLeft(evtWr.absX < 7 ? 7 : evtWr.absX - 3);
    },
    cancelSelect : function(evtWr)
    {
        evtWr.eventDrop();
    },
    endResizeAdd : function(evtWr)
    {
        this.resizeIsOn = false;
        this.work_part.removeListener(this, "onmouseup", "endResizeAdd");
        this.work_part.removeListener(this, "onmousemove", "moveSeparator");
        this.work_part.removeListener(this, this.$w0.bv.isIE ? "onselectstart" : "onmousedown", "cancelSelect");
        this.vSeparator2_.setDisplay(false);
        this.addWidth = this.$w0.getDocumentWidth() - evtWr.absX + 2;
        if (this.addWidth < this.config.minAddWidth) this.addWidth = this.config.minAddWidth;
        this.doResizeAdd();
    },
    doResizeAdd : function()
    {
        var disp = this.isAddShow ? "table-cell" : "none";
        this.blAdd.setDisplay(disp);
        this.vSeparator2.setDisplay(disp);

        var mWidth = this.$w0.getDocumentWidth();
        if (this.isAddShow) {
            var aWidth = this.addWidth;
            this.blAddContent.style.width  = (aWidth - 5) + "px";
            this.blAdd.style.width  = aWidth + "px";
            //this.vSeparator2.style.width = this.config.separatorWidth + "px";
            mWidth = mWidth-aWidth-this.config.separatorWidth;
        }
        this.blMainContent.style.width = (mWidth - 6) + "px";
        this.blMain.style.width = mWidth + "px";
    },

    config : {
        defaultAddWidth : 0.5,
        minAddWidth     : 50,
        separatorWidth  : 7,
        idList : ['login_part', 'work_part', 'blMainMenu', 'blMainMenuContent', 'blTop', 'blContent', 'blMain', 'blAdd', 'blMainContent', 'blAddContent', 'blBottom', 'vSeparator1_', 'vSeparator2', 'vSeparator2_'],
        menu : {
            startDelay : 2500,
            inDelay    : 300,
            outDelay   : 1000,
            widthMin   : 8,
            widthMax   : 170
        }
    }
});