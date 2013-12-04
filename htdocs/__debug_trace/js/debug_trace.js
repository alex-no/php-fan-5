/**include
/js/js-wrapper.js;
*/
/**
 * Class constructor
 * Initialisation of object is done by constructor
 */
var debug_trace = newObject({
    nMode        : null,
    cPanel       : null,
    activeDetail : null,
    activePath   : null,
    aBlock       : null,
    aMarker      : null,
    timer        : null,

    init : function(nMode)
    {
        this.nMode   = nMode;
        this.aBlock  = [];
        this.aMarker = {};
    },

    onready : function(evtWr)
    {
        var conf, aLst, i;

        conf = this.config;
        if (this.nMode) {
            aLst = this.$$('i.' + conf.parentClass);
            if (aLst) {
                aLst.addListener(this, 'onmouseover', 'onParentOver');
                aLst.addListener(this, 'onmouseout',  'onParentOut');
            }

            aLst = this.$$('div.' + conf.metaClass);
            if (aLst) {
                aLst.addListener(this, 'onclick', 'onClickMeta');
            }

            this.$w0.addListener(this, 'onclick', 'onClickBody');

            if (this.nMode == 1) {
                //aLst = this.$$('div.' + conf.headClass);
                aLst = this.$$('div.debug_block');
                for (i = 0; i < 1024; i++) {
                    if (!isDefined(aLst, i)) {
                        break;
                    }
                    this.aBlock[i] = new debug_info(aLst[i], i);
                }
                this.setTimeout(0, this, '_formBlock')
                this.$w0.addListener(this, 'onresize', 'positionBlocks');
            } else if (this.nMode == 2) {
                aLst = this.$$('div.' + conf.buttonClass);
                if (aLst) {
                    for (i = 0; i < aLst.length; i++) {
                        aLst[i].addListener(this, 'onclick', 'onClickDD', aLst[i].getNextSibling());
                    }
                }
            }
        }
        this.makeCpanel(conf)
    },

    positionBlocks : function()
    {
        var i;
        this.aMarker = {};
        for (i in this.aBlock) {
            this.aBlock[i].oSource.addClass('debug_overflow');
        }
        for (i in this.aBlock) {
            this.aBlock[i].positionBlock();
        }
        for (i in this.aBlock) {
            this.aBlock[i].oSource.removeClass('debug_overflow');
        }
    },

    getMarkShift : function(ind, x, y)
    {
        var shift, w, h, xi, yi, ts, n1, n2, i1, i2;

        shift = 0;
        w = this.config.markW;
        h = this.config.markH;

        yi = [parseInt(y / h)];
        if (yi[0] != y * w) {
            yi[1] = yi[0] + 1;
        }

        // Check Overlap markers and calculate Shift
        for (i1 = 0; i1 < 100; i1++) {

            xi = [parseInt((x + shift) / w)];
            if (xi[0] != (x + shift) * w) {
                xi[1] = xi[0] + 1;
            }

            ts = this._checkOverlap(x + shift, y, xi, yi);
            if (ts) {
                shift += ts;
            } else {
                break;
            }
        }

        // Save marker index in the table
        for (i1 in yi) {
            n1 = yi[i1];
            if (!isDefined(this.aMarker, n1)) {
                this.aMarker[n1] = {};
            }
            for (i2 in xi) {
                n2 = xi[i2];
                if (!isDefined(this.aMarker[n1], n2)) {
                    this.aMarker[n1][n2] = [];
                }
                this.aMarker[n1][n2].push(ind);
            }
        }

        return shift;
    },

    // Show/Hide detail info
    onClickDD : function(evtWr, detail)
    {
        var x, y;
        evtWr.stopBubbling();
        if (this.activeDetail && this.activeDetail != detail) {
            this.activeDetail.hide();
        }
        detail.invDisplay();
        if (detail.isShow()) {
            if (this.nMode == 1) {
                x = parseInt(evtWr.elmWr.css.left);
                y = parseInt(evtWr.elmWr.css.top);
                detail.moveAbs(x, y + evtWr.elmWr.getHeight() + 1);
            } else {
                detail.moveAbs(evtWr.elmWr.getX(), evtWr.elmWr.getY() + evtWr.elmWr.getHeight() + 1);
            }
            this.activeDetail = detail;
            detail.addListener(this, 'onclick', 'onClickDetail');
        } else {
            this.activeDetail = null;
        }
    },

    onClickDetail : function(evtWr)
    {
        evtWr.stopBubbling();
        //evtWr.eventDrop();
        this.hidePath();
    },

    // Show/Hide parent path
    onParentOver : function(evtWr)
    {
        var path = evtWr.elmWr.getNextSibling();
        if (this.activePath) {
            if (this.activePath == path) {
                this.resetTimer(false);
                return;
            } else {
                this.hidePath();
            }
        }
        if (!this.activePath) {
            path.show();
            path.addListener(this, 'onmouseover', 'onPathOver');
            path.addListener(this, 'onmouseout', 'onParentOut');
            path.addListener(this, 'onclick', 'onClickPath');
            this.activePath = path;
        }
        this.resetTimer(false);
    },
    onPathOver : function(evtWr)
    {
        this.resetTimer(false);
    },
    onClickPath : function(evtWr)
    {
        evtWr.stopBubbling();
        evtWr.eventDrop();
    },
    onParentOut : function(evtWr)
    {
        this.resetTimer(true);
    },
    resetTimer : function(bStart)
    {
        if (this.timer) {
            clearTimeout(this.timer);
        }
        this.timer = bStart ? this.setTimeout(this.config.parentDelay, this, 'hidePath') : null;
    },
    hidePath : function()
    {
        this.resetTimer(false);
        if (this.activePath) {
            this.activePath.hide();
            this.activePath = null;
        }
    },

    // Click Meta-label
    onClickMeta : function(evtWr)
    {
        evtWr.elmWr.getNextSibling().invDisplay();
    },

    // Click Body
    onClickBody : function(evtWr)
    {
        if (evtWr.evt.button == 0) {
            if (this.activeDetail) {
                this.activeDetail.hide();
            }
            this.hidePath();
        }
    },

    // -------- Control Panel --------- \\
    makeCpanel : function(conf)
    {
        var cPanel2, nMode, lst, but, a, k;
        this.cPanel = this.$w0.makeElement('div', {title:'Control debug panel', id:'debug_cpanel_1'});
        this.$w0.doc.body.appendChild(this.cPanel.elm);
        this.cPanel.addListener(this, 'onclick', 'onClickCpanel');

        cPanel2 = this.$w0.makeElement('div', {id:'debug_cpanel_2'});
        cPanel2.elm.innerHTML = conf.controlHTML;
        this.$w0.doc.body.appendChild(cPanel2.elm);

        a = /(\?|\&)debug\=(\d+)/.exec(this.$w0.win.location.href);
        if (!a) {
            nMode = this.nMode ? this.nMode * 10 : 0;
        } else {
            nMode = a[2] == 1 || a[2] == 2 || a[2] == 10 || a[2] == 20 ? a[2] : 0;
        }

        lst = {'#debug_cb_1s' : 1, '#debug_cb_2s' : 2, '#debug_cb_1m' : 10, '#debug_cb_2m': 20};
        for (k in lst) {
            but = this.$(k);
            if (nMode == lst[k]) {
                but.addClass('debug_current_button');
            }
            but.addListener(this, 'onclick', 'onClickButCpanel').i = nMode == lst[k] ? 0 : lst[k];
        }
    },
    onClickCpanel : function(evtWr)
    {
        evtWr.elmWr.getNextSibling().invDisplay();
    },

    onClickButCpanel : function(evtWr, dt)
    {
        var loc, srch;
        loc      = this.$w0.win.location
        srch     = loc.search.replace(/\?debug\=\d+$|debug\=\d+\&|\&debug\=\d+/,'');
        loc.href = loc.pathname + (srch ? srch + '&' : '?') + 'debug=' + dt.i + loc.hash;
    },


    _formBlock : function()
    {
        var cntDiv, cntInf, i;

        cntDiv = this.$w0.makeElement('div', {id : 'debug_contours'});
        this.$w0.doc.body.appendChild(cntDiv.elm);
        cntInf = this.$w0.makeElement('div', {id : 'debug_infos'});
        this.$w0.doc.body.appendChild(cntInf.elm);

        for (i = 0; i < this.aBlock.length; i++) {
            this.aBlock[i].formBlock(cntDiv, cntInf);
        }
        this.positionBlocks();
    },
    _checkOverlap : function(x, y, xi, yi)
    {
        var ind, w, h, cx, cy, n1, n2, i1, i2, j;
        w = this.config.markW;
        h = this.config.markH;

        for (i1 in yi) {
            n1 = yi[i1];
            for (i2 in xi) {
                n2 = xi[i2];
                if (isDefined(this.aMarker, n1) && isDefined(this.aMarker[n1], n2)) {
                    for (j in this.aMarker[n1][n2]) {
                        ind = this.aMarker[n1][n2][j];
                        cx = this.aBlock[ind].tlpX + this.aBlock[ind].markShift;
                        cy = this.aBlock[ind].tlpY;
                        if (x < cx + w && x + w > cx && y < cy + h && y + h > cy) {
                            return cx - x + w;
                        }
                    }
                }
            }
        }

        return 0;
    },


    // Config
    config : {
        'secondId'    : 'debug2',
        'headClass'   : 'debug_header',
        'buttonClass' : 'debug2_button',
        'parentClass' : 'debug_parent_class',
        'metaClass'   : 'debug_meta_label',
        'controlHTML' : '<div id="debug_cb_1s">1</div><div id="debug_cb_2s">2</div><div id="debug_cb_1m">1</div><div id="debug_cb_2m">2</div>',
        'parentDelay' : 500,
        'markW' : 7, // Marker width
        'markH' : 24 // Marker height
    }

});


var debug_info = newClass({
    oSource  : null,
    oInfoBl  : null,
    oLabel   : null,
    oHeader  : null,
    oInfo    : null,
    oContour : null,
    nIndex   : null,

    blTimer  : null,

    tlpX      : 0, // Top Left point: X-coordinate
    tlpY      : 0, // Top Left point: Y-coordinate
    markShift : 1, // Shifting of marker by X-coordinate

    markW : 0, // Marker width
    markH : 0, // Marker height

    init : function(oSource, nIndex)
    {
        this.oSource = oSource;
        this.nIndex  = nIndex;

        var inf = oSource.getChild(0);
        this.oHeader = inf.getChild(0)
        this.oInfo   = inf.getChild(1)
        this.oHeader.addListener(debug_trace, 'onclick', 'onClickDD', this.oInfo);
        inf.remove();

        this.markW = debug_trace.config.markW;
        this.markH = debug_trace.config.markH;
    },

    formBlock : function(cntDiv, cntInf)
    {
        var mark, lst, tfl, fl, w, i;
        w = debug_trace.$w0;

        // Set float for Source element
        lst = this.oSource.elm.childNodes;
        for(i = 0; i < lst.length; i++) {
            if (this._checkTag(lst[i])) {
                tfl = getElmWrapper(lst[i], w).css.cssFloat;
                if (!tfl) {
                    fl = 'none';
                    break;
                } else if(!fl){
                    fl = tfl;
                } else if(fl != tfl){
                    fl = 'none';
                    break;
                }
            }
        }
        if (fl == 'left' || fl == 'right') {
            this.oSource.style.cssFloat = fl;
        }


        // Set Contour
        this.oContour = w.makeElement('div');
        this.oContour.addClass('debug_contour');
        cntDiv.elm.appendChild(this.oContour.elm);


        // Set Info Block
        this.oInfoBl = w.makeElement('div');
        this.oInfoBl.addClass('debug_info_bl');
        cntInf.elm.appendChild(this.oInfoBl.elm);

        mark = w.makeElement('div');
        mark.addClass('debug_mark');
        this.oInfoBl.elm.appendChild(mark.elm);
        this.oInfoBl.elm.appendChild(this.oHeader.elm);
        this.oInfoBl.elm.appendChild(this.oInfo.elm);

        this.oInfoBl.addListener(this, 'onmouseover', 'showHeader');
        this.oInfoBl.addListener(this, 'onmouseout',  'outContour');
        this.oHeader.addListener(this, 'onmouseover', 'headerOver');
    },
    positionBlock : function()
    {
        this.tlpX = this.oSource.getAbsLeft();
        this.tlpY = this.oSource.getAbsTop();

        this.oContour.moveAbs(this.tlpX, this.tlpY);

        this._resizeContour('getWidth',  'width');
        this._resizeContour('getHeight', 'height');

        this.markShift = debug_trace.getMarkShift(this.nIndex, this.tlpX, this.tlpY);

        this.oInfoBl.moveAbs(this.tlpX + this.markShift, this.tlpY);
    },

    showHeader : function(evtWr)
    {
        this.oHeader.show();
        this.oContour.addClass('dtBorder');
        this.resetBlTimer();
    },
    outContour : function(evtWr)
    {
        this.blTimer = this.setTimeout(this.config.hideDelay, this, 'hideHeader');
    },

    // Show Header
    headerOver : function(evtWr)
    {
        this.resetBlTimer();
    },
    // Hide Header
    hideHeader : function()
    {
        this.oHeader.hide();
        this.oInfoBl.style.minWidth = this.markW + 'px';
        this.oContour.removeClass('dtBorder');
        this.blTimer = null;
    },
    resetBlTimer : function(ind)
    {
        if (this.blTimer) {
            clearTimeout(this.blTimer);
        }
        this.blTimer = null;
    },
    _resizeContour : function(met, prop)
    {
        var n = this.oSource[met]() - 2;
        if (n < 1) {
            n = 1;
        }
        this.oContour.style[prop]  = n + 'px';
    },

    _checkTag : function(tag)
    {
        return tag && tag.nodeType == 1 && tag.nodeName.toUpperCase() != 'BODY' && tag.nodeName.toUpperCase() != 'HTML';
    },

    // Config
    config : {
        hideDelay : 500
    }

});