var pagerRightCtrl = newObject({
    implement : [
        { obj : pagerCommon }
    ],
    selectNewPage : function(evtWr, par) {
        var rc, k1, k2, i;
        evtWr.eventDrop();
        evtWr.elmWr.elm.blur();

        if (this.curPage == par.num) return;

        if (this.curWr) this.curWr.setClass("");
        if (par.liWr) {
           par.liWr.setClass("current");
           this.curWr=par.liWr;
        }
        this.curPage = par.num;

        if (this.mainCtrl.topCurr && this.mainCtrl.topCurr[1] && this.mainCtrl.topCurr[1].mainContentRight) {
            k1 = this.mainCtrl.topCurr[1].current_right;
            for (i in this.mainCtrl.topCurr[1].right_frm[k1].top_content) {
                k2 = this.mainCtrl.topCurr[1].right_frm[k1].top_content[i];
                rc = this.mainCtrl.content[k2];
                if (rc.pager && (rc.isMain || rc.useMainPage)) {
                    rc.pager[0] = this.curPage;
                    rc.refrash();
                }
            }
        }
    },
    config : {
        container  : "#pageListRight",
        pre_first  : "preFirstRight",
        scrollFunc : ["scroll2First", "scroll2Previous", "scroll2Next", "scroll2Last"]
    }
});