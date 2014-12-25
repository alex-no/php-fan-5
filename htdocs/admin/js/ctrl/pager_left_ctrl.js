var pagerLeftCtrl = newObject({
    implement : [
        { obj : pagerCommon }
    ],
    selectNewPage : function(evtWr, par) {
        evtWr.eventDrop();
        evtWr.elmWr.elm.blur();
        if (this.curPage == par.num) return;
        this.$w0.win.viewCtrl.hideAddFrame();
        if (this.curWr) this.curWr.setClass("");
        if (par.liWr) {
           par.liWr.setClass("current");
           this.curWr=par.liWr;
        }
        this.curPage = par.num;
        if (this.mainCtrl.topCurr && this.mainCtrl.topCurr[1]) this.mainCtrl.topCurr[1].requestPage(this.curPage);
    },
    config : {
        container  : "#pageListLeft",
        pre_first  : "preFirstLeft",
        scrollFunc : ["scroll2First", "scroll2Previous", "scroll2Next", "scroll2Last"]
    }
});