var logCtrl = newObject({
    dataLoader  : null,
    traceLoader : null,
    dateList    : null,

    menuObj    : {},
    headTitle  : {},
    pattern    : {},
    subscrElm  : {},
    pidArr     : {},

    data_title : null,
    content    : null,
    form       : null,
    is_loading : null,
    cur_loadWr : null,

    curPage    : 1,
    pageQtt    : 1,

    selectDate : {},
    curVariety : "",
    timer      : null,
    lastRecId  : 0,
    idInd      : 0,
    zebra      : 0,
    isDel      : 0,

    sendQtt    : 0,

    init : function (dateList, cDt, isDel, ses)
    {
        this.dateList = dateList;
        this.isDel = isDel;

        for (var k in dateList) {
            this.selectDate[k] = cDt + '_' + dateList[k][cDt][0];
        }

        this.dataLoader = this.getLoadWrapper(this.config.dataLoader + ses, "post");
        this.dataLoader.addListener(this, "ondataload", "onDataLoad");
        this.dataLoader.addListener(this, "ondataerror", "onDataError");
        this.dataLoader.setMode(2);

        this.traceLoader = this.getLoadWrapper(this.config.traceLoader + ses, "post");
        this.traceLoader.addListener(this, "ondataload", "onTraceLoad");
        this.traceLoader.addListener(this, "ondataerror", "onDataError");
    },
    onready : function (evtWr)
    {
        var conf, lnk, selDt, vr, ptrn, k, i;
        conf = this.config;

        // Init menu elements
        for (vr in this.dateList) {
            lnk = this.$("#" + vr + '_mn');
            lnk.addListener(this, "onclick", "onMenuClick", vr);
            this.headTitle[vr] = lnk.elm.innerHTML;
            this.menuObj[vr] = lnk.getParent();
            if (!this.curVariety) {
                this.curVariety = vr;
            }
        }

        // Init control form elements
        this.form = this.$(conf.id.form);
        selDt = this.$(this.form.elm.elements.date);
        selDt.addListener(this, "onchange", "onFormEvent");
        selDt.addListener(this, "onfocus", "stopTimer");
        selDt.addListener(this, "onblur", "onAutoUpdateChange");
        this.$(this.form.elm.elements.update).addListener(this, "onchange", "onAutoUpdateChange");
        this.$(this.form.elm.elements.group_idt).addListener(this, "onchange", "onFormEvent");
        if (this.isDel) {
            this.$(conf.id.delete_rec).addListener(this, "onclick", "onDeleteRecords");
            this.$(conf.id.inv_select).addListener(this, "onclick", "onInvertSelection");
        }

        // Init patterns
        for (i in conf.pattern) {
            k = conf.pattern[i];
            ptrn = this.$("#pattern_" + k);
            if (ptrn) {
                this.pattern[k] = ptrn.elm.innerHTML;
            }
        }
        this.is_loading = this.$(conf.is_loading);

        // Define main elements
        this.data_title  = this.$(conf.id.title);
        this.content     = this.$(conf.id.content);
        this.pager       = this.$(conf.id.pager);

        if (this.content) {
            this.$w0.addListener(this, "onresize", "resizeContent");
            this.resizeContent();
        }

        this.requestData(true);

    },

    onMenuClick : function (evtWr, vr)
    {
        var sel, opt, key, dt, i;
        evtWr.eventDrop();
        evtWr.elmWr.elm.blur();
        if (vr != this.curVariety) {
            this.menuObj[this.curVariety].removeClass("current");
            this.menuObj[vr].addClass("current");
            this.curVariety = vr;

            sel = this.form.elm.elements.date;
            sel.innerHTML = "";
            for (dt in this.dateList[vr]) {
                for (i in this.dateList[vr][dt]) {
                    key = dt + '_' + this.dateList[vr][dt][i];
                    opt = this.$w0.makeElement("option", {value : key}, dt + ' (' + this.dateList[vr][dt][i] + ')', 0);
                    if (key == this.selectDate[vr]) {
                        opt.setAttribute("selected", "selected");
                    }
                    sel.appendChild(opt);
                }
            }
        }

        this.requestData(true);
    },

    onFormEvent : function (evtWr, dt)
    {
        //evtWr.elmWr.elm.blur();
        this.selectDate[this.curVariety] = this.form.elm.elements.date.value;
        this.requestData(true);
    },

    stopTimer : function (evtWr, dt)
    {
        this.timerControl(0);
    },

    onAutoUpdateChange : function (evtWr, dt)
    {
        this.timerControl(this.form.elm.elements.update.checked, this.config.refPeriod);
    },

    onDeleteRecords : function (evtWr, dt)
    {
        var del_rec, inp, k, i;
        del_rec = [];
        for(i in this.content.elm.elements) {
            inp = this.content.elm.elements[i];
            if (isDefined(inp, "type") && inp.type == "checkbox" && inp.checked) {
                del_rec.push(inp.name.substr(4));
            }
        }
        if (del_rec.length) {
            this.requestData(true, {del : del_rec, curPage : this.curPage});
        } else {
            alert("Nothing delete");
        }
    },

    onInvertSelection : function (evtWr, dt)
    {
        var inp, i;
        for(i = 0; i < this.content.elm.elements.length; i++) {
            inp = this.content.elm.elements[i];
            if (isDefined(inp, "type") && inp.type == "checkbox") {
                inp.checked = !inp.checked;
            }
        }
    },

    requestData : function (res, adt)
    {
        var dt, cEl, k, i;
        if (!this.content) {
            return;
        }
        if (res) {
            this.lastRecId = 0;
            this.timerControl(0);
            this.sendQtt = 0;
        }
        if (!this.sendQtt) {
            cEl = this.content.elm;

            dt = {lastRecId : this.lastRecId, vr : this.curVariety, gr_idt : this.form.elm.elements.group_idt.checked};
            if (res) {
                dt.redraw = 1;
                if (!isDefined(adt, "curPage")) {
                    dt.curPage = 1;
                }
                cEl.innerHTML = "";
            }
            if (adt) {
                implement(dt, adt);
            }

            for (i in this.config.formSend) {
                k = this.config.formSend[i];
                dt[k] = this.form.elm.elements[k].value;
            }

            this.cur_loadWr = this.is_loading.getClone();
            if (cEl.firstChild) {
                cEl.insertBefore(this.cur_loadWr.elm, cEl.firstChild);
            } else {
                cEl.appendChild(this.cur_loadWr.elm);
            }

            this.dataLoader.send(dt);
        }
        this.sendQtt++;
        if (this.sendQtt > 5) {
            this.form.elm.elements.update.checked = false;
            this.timerControl(0);
        }
    },

    onDataLoad : function (json, dom, txt)
    {
        var list, rec, arr, i;
        this.sendQtt = 0;
        if (txt != "ok") {
            alert(txt);
        } else if (isDefined(json, 'oper')) {
            if (json.oper == "redraw") {
                this.data_title.setInnerHtml("<b>" + this.headTitle[json.vr] + "</b> for <i>" + json.date + "</i>");
                this.pidArr = {};
                this.zebra = 0;
            } else if (json.oper == "add" && isDefined(json, "records")) {
                list = this.content.elm.childNodes;
                for (i = 0; i < list.length; i++) {
                    rec = this.$(list[i]);
                    arr = /zebraN(0|1)/.exec(rec.elm.className);
                    if(arr && arr[0]) {
                        rec.removeClass("zebraN" + arr[1]);
                        rec.addClass("zebraO" + arr[1]);
                    }
                }
            }
            if (isDefined(json, "records")) {
                this.makeContent(json.records, json.oper == "first" ? "O" : "N");
            }
            if (json.oper != "none") {
                this.makePages(json.curPage, json.pageQtt);
            }
        }
        if (this.cur_loadWr) {
            this.cur_loadWr.remove();
            this.cur_loadWr = null;
        }
    },

    onTraceLoad : function (json, dom, txt)
    {
        var html, tr, trc, i, j;

        if (txt == "ok") {
            trc = this.$("#" + json["idHtml"]).getNextSibling();
            if (isDefined(json, "trace")) {
                html = "";
                for (i in json.trace) {
                    tr = json.trace[i];
                    if (isDefined(tr, "file")) {
                        html += this.pattern["trace_row1"].replace("_FILE_PATH_", tr.file).replace("_FILE_LINE_", tr.line);
                    }
                    html += this.pattern["trace_row2"].replace("_FUNCTION_NAME_", tr.func).replace("_FUNCTION_ARGUMENTS_", isDefined(tr, "arg") ? this.parseArguments(tr.arg) : '');
                }
                trc.setInnerHtml(html);
            } else {
                trc.setInnerHtml("There isn't trace");
            }
        } else {
            alert(txt);
        }
    },

    onDataError : function (txt)
    {
        this.timerControl(0);
        if (txt) {
            alert(txt);
        }
        this.cur_loadWr.remove();
    },

    parseArguments : function (src)
    {
        var res = '', i;
        if (src) {
            for (i in src) {
                if (res) {
                    res += ', ';
                }
                res += '<i>(' + src[i][0] + ')</i> ' + src[i][1];
            }
        }
        return res;
    },

    makeContent : function (records, zebra_key)
    {
        var cnt, rec, ref, html, div, allowTag, pid, tid, dt, a, sw, k, i;
        cnt   = this.content.elm;

        this.subscrElm = {};

        allowTag = this.config.allowTag;
        for (i = 0; i < records.length; i++) {
            try {
                rec = records[i];
                ref = rec.attr.request.replace(/\&/g, '&amp;');
                if (isDefined(rec.attr, "domain")) {
                    ref = (isDefined(rec.attr, "protocol") ? rec.attr.protocol : "http") + "://" + rec.attr.domain + ref;
                }
                html = this.pattern["record"];
                if (isDefined(rec.attr, "pid")) {
                    pid = rec.attr.pid;
                    tid = this.getSubscribe("s", "onclick", "onSelectPid", pid);
                    html = html.replace("PH_REQ_PID_", pid);
                    html = html.replace("PH_PID_SEL_", tid);
                    if (!isDefined(this.pidArr, pid)) {
                        this.pidArr[pid] = [];
                    }
                    this.pidArr[pid].push(tid);
                } else {
                    html = html.replace(/\<[^>]+\>[^_]*PH_REQ_PID_[^<]*\<\/\w+\>/, '');
                }
                html = html.replace(/_REQ_ID_/g,  rec.id);
                html = html.replace("_REQ_TIME_", rec.attr.time);
                html = html.replace("_REQ_TYPE_", rec.attr.type);
                html = html.replace("_REQ_METHOD_", rec.attr.method);
                html = html.replace(rec.attr.method == 'CLI' ? /\<a\s+href[^\>]+\>_REQ_HREF_\<\/a\>/ : /_REQ_HREF_/g, ref);
                html = html.replace("_REQ_HEADER_", this.trimHTML(rec.header));
                if (isDefined(rec, "data")) {
                    html += '<div class="rec_data">' + this.getData(rec.data, true) + '</div><div class="clear"></div>';
                }
                if (isDefined(rec, "main_msg")) {
                    html += '<div class="rec_msg">' + this.trimText(rec.main_msg, allowTag) + '</div>';
                    //html += '<pre class="rec_msg">' + this.trimText(rec.main_msg) + '</pre>';
                }
                if (isDefined(rec, "note")) {
                    html += '<div class="rec_note">' + this.trimText(rec.note, allowTag) + '</div>';
                    //html += '<pre class="rec_note">' + this.trimText(rec.note) + '</pre>';
                }
                if (isDefined(rec, "trace")) {
                    html += this.pattern["trace"].replace("ID_SWITCH2", this.getSubscribe("t", "onclick", "onSwitchTrace", rec.id));
                }

                div = this.$w0.makeElement("div");
                div.setClass("record");
                this.zebra = this.zebra ? 0 : 1;
                div.addClass("zebra" + zebra_key + this.zebra);
                div.elm.innerHTML = html;
                div.id_rec = rec.id;
                cnt.appendChild(div.elm);
                allowTag = this.config.allowTag;
            } catch (e) {
                if (!allowTag) {
                    alert(e.name + ": " + e.message + "\n\nCan't insert HTML-code: \n" + html);
                    break;
                }
                allowTag = false;
                i--;
            }
        }

        this.lastRecId = rec.id;

        a = this.$w0.makeElement("a", {"href" : "#"}, "_");
        a.setClass("foc");
        cnt.appendChild(a.elm);
        a.elm.focus();
        cnt.removeChild(a.elm);

        for(k in this.subscrElm) {
            sw = this.$("#" + k)
            if (sw) {
                dt = this.subscrElm[k];
                sw.addListener(this, dt[0], dt[1]).id = dt[2];
            }
        }
    },

    makePages : function (curPage, pageQtt)
    {
        var pg, td, a, i;
        pg = this.pager.elm;
        pg.innerHTML = "";
        for (i = 1; i <= pageQtt; i++) {
            a = this.$w0.makeElement("a", {"href" : "#"}, i + "");
            a.addListener(this, "onclick", "onPageClick").p = i;
            td = this.$w0.makeElement("td", {}, a);
            if (i == curPage) {
                td.addClass("current");
            }
            pg.appendChild(td.elm);
        }
        this.curPage = curPage;
    },

    onPageClick : function (evtWr, dt)
    {
        evtWr.eventDrop();
        evtWr.elmWr.elm.blur();
        this.requestData(true, {curPage : dt.p});
    },

    getData : function (data, open)
    {
        var html, lst, dtl, i;
        if (isDefined(data, "singular")) {
            html = this.pattern["data_singular"];
            html = html.replace("_TYPE_", data.type);
            html = html.replace("_VALUE_", this.trimHTML(data.singular));
        } else {
            html = this.pattern["data_multiple"];
            html = html.replace("_TYPE_", data.type);
            html = html.replace("_TYPE_", data.type);
            html = html.replace("_SWITCH_SIGN_", open ? "-" : "+");
            if (open) {
                html = html.replace("display: none", "display: block");
            }
            html = html.replace("ID_SWITCH1", this.getSubscribe("d", "onclick", "onSwitchData", null));
            dtl = "";
            for (i in data.multiple) {
                lst = this.pattern["data_row"];
                lst = lst.replace("_KEY_", this.trimHTML(i));
                lst = lst.replace("_DATA_", this.getData(data.multiple[i], false));
                dtl += lst;
            }
            html = html.replace("_DT_LIST_", dtl);
        }
        return html;
    },

    onSelectPid : function (evtWr, dt)
    {
        var list, pidArr, i;
        evtWr.eventDrop();

        list = this.content.elm.childNodes;
        for (i = 0; i < list.length; i++) {
            this.$(list[i]).removeClass("pid_select");
        }

        pidArr = this.pidArr[dt.id];
        for (i in pidArr) {
            this.$("#" + pidArr[i]).getParent().getParent().addClass("pid_select");
        }
    },

    onSwitchData : function (evtWr, dt)
    {
        var span = evtWr.elmWr.elm.firstChild;
        span.innerHTML = span.innerHTML == "+" ? "-" : "+";
        evtWr.elmWr.getNextSibling().setDisplay(span.innerHTML == "-");
    },

    onSwitchTrace : function (evtWr, dt)
    {
        var span, trc, dts, k, i;
        span = evtWr.elmWr.elm.firstChild;
        trc  = evtWr.elmWr.getNextSibling();
        span.innerHTML = span.innerHTML == "+" ? "-" : "+";
        trc.setDisplay(span.innerHTML == "-");

        if (span.innerHTML == "-" && trc.elm.innerHTML == "") {
            dts = {idHtml : evtWr.elmWr.elm.id, idRecord : dt.id, vr : this.curVariety};
            for (i in this.config.formSend) {
                k = this.config.formSend[i];
                dts[k] = this.form.elm.elements[k].value;
            }
            this.traceLoader.send(dts);
        }
    },

    getSubscribe : function (prefix, event, method, id)
    {
        var k = prefix + "_" + (this.idInd++);
        this.subscrElm[k] = [event, method, id];
        return k;
    },

    timerControl : function (r, t)
    {
        if (this.timer) {
            clearInterval(this.timer)
        }
        this.timer = r ? this.setInterval(t, this, "requestData") : null;
    },

    trimText : function (txt, allowTag)
    {
        txt = allowTag ? txt + "" : this.trimHTML(txt);
        return txt.replace(/^\n+\s*/g, "").replace(/\s*\n+$/g, "").replace(/\n+/g, "<br/>");
    },

    trimHTML : function (txt)
    {
        txt = txt + "";
        //.replace(/\"/g, '&quot;');
        return txt.replace(/\&/g, '&amp;').replace(/\</g, '&lt;').replace(/\>/g, '&gt;');
    },

    resizeContent : function ()
    {
        var h = this.$w0.getDocumentHeight() - this.config.cor_size;
        this.content.style.height = h + "px";
    },

    config : {
        dataLoader  : '/__log_viewer/get_log_data.php?',
        traceLoader : '/__log_viewer/get_trace.php?',
        formSend  : ['date'],
        pattern   : ['record', 'data_singular', 'data_multiple', 'data_row', 'trace', 'trace_row1', 'trace_row2'],
        is_loading : '#is_loading',
        refPeriod : 3500,
        cor_size  : 145,
        allowTag  : true,
        id : {
            'form'       : '#select_param',
            'delete_rec' : '#delete_rec',
            'inv_select' : '#inv_select',
            'title'      : '#data_title',
            'content'    : '#content',
            'pager'      : '#pager'
        }
    }
});