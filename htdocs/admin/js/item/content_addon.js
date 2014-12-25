var content_addon = {};

content_addon.info = {
    runBeforeShow : function()
    {
        if (isDefined(this.extra, "parsingScript")) {
            eval(this.extra.parsingScript);
        }
    }
};

content_addon.table = {
    content_type : "table",
    insNum   : 0,
    zebra    : 1,
    zebraReg : /class\=\"row\{zebra\}\"/igm,
    codeReg  : /^(.+?)\[(.+?)\](.+)$/,
    workArr  : null,
    portionSize  : 4,
    markText     : '<!-- [DINAMIC_ROWS] -->',
    seDynamic    : [/^\s*\[(start|end)\-(?:del\((.+?)\))?(?:rgt\((.+?)\))?\]\s*$/, '<!-- [start-', '] -->', '<!-- [end-', '] -->'],
    maxMarkDepth : 8,
    newRowDelay  : 100,
    makeTimer    : null,

    eDel : false,
    eRgt : false,

    preParseCode : function()
    {
        if (this.code) {
            var a = this.codeReg.exec(this.code);
            if (a && a[2]) {
                this.code = [a[1] + this.markText + a[3], a[2]];
            } else {
                this.code[0] = this.code;
            }
        }
    },

    /**
    * el - event list (for subscribe)
    */
    getCode : function(el)
    {
        var rows, row, id, sem, code, isn, cn, k, v, i;
        rows = "";
        for (id in this.data) {
            this.eDel = false;
            this.eRgt = false;
            this.data[id].id = id.substr(1);
            row = patternCtrl.parseHtml(this.parseZebra(this.code[1]), this, el, this.data[id], this.param, "edit", id.substr(1));
            sem = "";
            if (this.eDel) {
                if (this.delArr == null) {
                    this.delArr = {};
                }
                this.delArr[this.eDel.id] = this.eDel;
                sem = "del(" + this.eDel.id + ")";
            }
            if (this.eRgt) {
                if (this.rgtArr == null) {
                    this.rgtArr = {};
                }
                this.rgtArr[this.eRgt.id] = this.eRgt;
                sem += "rgt(" + this.eRgt.id + ")";
            }
            rows += this.seDynamic[1] + sem + this.seDynamic[2] + row + this.seDynamic[3] + sem + this.seDynamic[4];
        }

        code = this.code[0].replace('{total_qtt}', this.pager[2]);

        code = patternCtrl.parseHtml(code, this, el, this.extra.order, this.param);
        code = code.replace(this.markText, rows + this.markText);

        if (!this.extra.not_new) {
            isn = true;
            if (this.param.new_row_cond && this.cond) {
                // Check condition for new row
                for (k in this.param.new_row_cond) {
                    cn = this.param.new_row_cond[k];
                    v  = this.cond[k];
                    if (cn[0]) for (i = 0; i < cn[0].length; i++) {
                        isn = isn && (cn[0][i] != v);
                    }
                    if (cn[1]) {
                        isn = false;
                        for (i = 0; i < cn[1].length; i++) {
                            isn = isn || (cn[1][i] == v);
                        }
                    }
                    if(!isn) {
                        break;
                    }
                }
            }
            if (isn) {
                this.setTimeout(this.newRowDelay, this, "insNewFields", [-1]);
            }
        }
        return code;
    },

    attachRows : function()
    {
        var mark, cn, cid, cir, re, i;
        cid = 0;
        cir = 0;
        mark = this.findMarker(this.parentTag.elm, 0);
        if(mark != null) {
            cn = mark.parentNode.childNodes;
            for (i = 0; i < cn.length; i++) {
                if(cn[i].nodeType == 8) {
                    re = this.seDynamic[0].exec(cn[i].data);
                    if (re && re[1]) {
                        if (re[1] == "start") {
                            if (this.delArr) {
                                cid = re[2];
                                this.delArr[cid].ed.nl = [];
                            }
                            if (this.rgtArr && re[3]) {
                                cir = re[3];
                                this.rgtArr[cir].ed.nl = [];
                            }
                        } else {
                            cid = 0;
                            cir = 0;
                        }
                    }
                } else {
                    if (cid && this.delArr) {
                        this.delArr[cid].ed.nl.push(cn[i]);
                    }
                    if (cir && this.rgtArr && cn[i].nodeType == 1) {
                        this.rgtArr[cir].ed.nl.push(cn[i]);
                    }
                }
            }
        }
    },

    insNewFields : function(ek)
    {
        if(ek + 1 == this.insNum) {
            var mark, prnt, tmp, el, nlt, nl, i;
            mark = this.findMarker(this.parentTag.elm, 0);
            if(mark == null) {
                alert("Error! Marker of insert isn't found")
                return;
            }

            prnt = mark.parentNode;
            el = [];
            nl = [];
            var html = patternCtrl.parseHtml(this.parseZebra(this.code[1]), this, el, this._getDefaultVar({"id" : "<b>new</b>"}), this.param, "ins", this.insNum++);
            nlt = patternCtrl.getDom(html, prnt.nodeName);
            for (i = 0; i < nlt.length; i++) {
                nl[i] = prnt.insertBefore(nlt[i], mark);
            }
            for (i = 0; i < el.length; i++) {
                if (el[i].met == "onRowDelete"){
                    el[i].ed.nl = nl;
                }
            }
            patternCtrl.subscribeEvent(el);
        }
    },

    parseZebra : function(html)
    {
        this.zebra = this.zebra ? 0 : 1;
        return html.replace(this.zebraReg, 'class="row' + this.zebra + '"');
    },

    findMarker : function(obj, depth)
    {
        var nl, mark, i;
        depth++;
        nl = obj.childNodes;
        for (i = 0; i < nl.length; i++) {
            if(nl[i].nodeType == 8 && ('<!--' + nl[i].data + '-->' == this.markText)) {
                return nl[i];
            } else if(nl[i].nodeType == 1 && depth < this.maxMarkDepth) {
                mark = this.findMarker(nl[i], depth);
                if(mark != null) {
                    return mark;
                }
            }
        }
        return null;
    },


// -------- events -------- \\
    onSort : function(evtWr, data)
    {
        var ol, k;
        evtWr.eventDrop();
        evtWr.elmWr.elm.blur();
        this.order = {};
        ol = this.extra.order;
        for (k in ol) {
            if (k == data.name) {
                ol[k] = ol[k] == 2 ? 1 : 2;
                evtWr.elmWr.setClass("order_" + ol[k]);
                this.order[k] = ol[k];
            } else {
                ol[k] = 0;
            }
        }
        this.refrash();
    }

};

content_addon.form = {
    content_type : "form",
    getMainCondition : function()
    {
        return isDefined(this.cond, this.param.mainConditionKey) ? this.cond[this.param.mainConditionKey] : null;
    }
};
