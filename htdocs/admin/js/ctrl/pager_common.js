var pagerCommon = newObject({
    curPage : 1,
    pageCount : 1,
    startPage : 1,

    init : function(mainCtrl) {
        var conf, scrollers, i;

        this.mainCtrl = mainCtrl;
        this.$w0 = mainCtrl.$w0;

        conf = this.config;
        this.blDiv = this.$(conf.container);
        scrollers = this.$$(conf.container + " a")
        for(i in conf.scrollFunc) {
            scrollers[i].addListener(this, "onclick", conf.scrollFunc[i]);
        }
        this.resetPager();

    },

    setupPager : function(pg) {
        this.curPage      = pg[0];
        this.pageCount    = pg[1];
        this.startPage    = pg[0] < 10 ? 1 : pg[0] - 9;
        this._makePager();
    },
    resetPager : function() {
        this.curPage      = 1;
        this.pageCount    = 1;
        this.startPage    = 1;
        this._makePager();
    },

    scroll2First : function(evtWr) {
        this.startPage = 1;
        this._makePager(evtWr);
    },
    scroll2Previous: function(evtWr) {
        this.startPage -= 15;
        this._makePager(evtWr);
    },
    scroll2Next : function(evtWr) {
        this.startPage += 15;
        if(this.startPage > this.pageCount - 15 ) this.startPage = this.pageCount - 15;
        this._makePager(evtWr);
    },
    scroll2Last : function(evtWr) {
        this.startPage = this.pageCount - 15;
        this._makePager(evtWr);
    },

    _makePager : function(evtWr) {
        var li, a, par, i;
        if(evtWr) {
            evtWr.eventDrop();
            evtWr.elmWr.elm.blur();
        }
        if(this.startPage < 1) this.startPage = 1;
        var lastPage = this.startPage + 40;
        this.mainCtrl.removeChildren(this.blDiv.elm, this.config.pre_first);

        if(lastPage > this.pageCount) {lastPage = this.pageCount;}
        for (i=this.startPage; i<=lastPage; i++) {
            a  = this.$w0.makeElement("a", {href:"#"}, i + "");
            li = this.$w0.makeElement("li", null, a);
            this.blDiv.elm.appendChild(li.elm);
            if (i == this.curPage) {
                li.setClass("current");
                this.curWr=li;
            }
            par = a.addListener(this, "onclick", "selectNewPage");
            par.liWr = li;
            par.num = i;
        }
    }
});