var rightFrame = newClass({
    key     : "",
    name    : "",
    title   : "",
    leftFrm : null,

    cond : null,
    menu_el : null,

    top_content : null,
    bottom_content : null,

    init : function(key, data, leftFrm)
    {
        var conf, k;
        this.key   = key;
        this.name  = data.name;
        this.title = isDefined(data,"title") ? data.title : data.name;

        if (data.top_content) { this.top_content = leftFrm.mainCtrl.setContent(data.top_content); }
        if (data.bottom_content) { this.bottom_content = leftFrm.mainCtrl.setContent(data.bottom_content); }

        this.main_content = data.top_content[0];

        this.leftFrm = leftFrm;
        this.mainCtrl = leftFrm.mainCtrl

        this.$w0 = leftFrm.$w0;

        conf = this.config;
        for (k in conf.idList) {
            this[k]  = this.$(conf.idList[k]);
        }
    },
    activate : function()
    {
        var winWr, tag, cnt, ul, li, a, i;
        pagerRightCtrl.resetPager();
        this.leftFrm.mainContentRight = null;

        winWr = this.$w0;

        if (this.leftFrm.current_right != this.key) {
            this.leftFrm.right_frm[this.leftFrm.current_right].menu_el.removeClass("current");
        }
        this.menu_el.setClass("current");
        this.leftFrm.current_right = this.key;

        this.mainCtrl.removeChildren(this.frameTop.elm);

        if (this.title) {
            tag = winWr.makeElement("h1", null, this.title);
            this.frameTop.elm.appendChild(tag.elm);
        }

        for (i in this.top_content) {
            tag = winWr.makeElement("form", {"action" : "#"});
            this.frameTop.elm.appendChild(tag.elm);
            cnt = this.getContent(this.top_content, i);
            if (this.cond) {
                cnt.cond = this.cond;
            }
            if (this.top_content[i] == this.main_content) {
                cnt.isMain = true;
                this.leftFrm.mainContentRight = cnt;
            }
            //cnt.pager[0] = 1;
            cnt.setCurrentFrame(this, tag)
            cnt.load(true);
        }
/*
        for (i in this.bottom_content) {
            tag = winWr.makeElement("form", {"action" : "#"});
            this.frameBot.elm.appendChild(tag.elm);
            cnt = this.getContent(this.bottom_content, i);
            cnt.isMain = false;
            cnt.setCurrentFrame(this, tag)
            cnt.load(true);
        }
*/
    },

    setupPager : function(pg)
    {
        pagerRightCtrl.setupPager(pg);
    },

    getContent : function(obj, prop)
    {
        return this.mainCtrl.content[obj[prop]];
    },

    config : {
        idList : {frameTop : '#frameRightTop', frameBot : '#frameRightBottom'}
    }
});