/**
 * Calendar Maker
 * Copyright (C) 2007 Prilipko Andrey, Alexandr Nosov
 *
 * @author: Prilipko Andrey (andrey_prill@mail.ru)
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version:  01.05.15 Alpha
 * @modified: 2009-05-22 03:40:00
 */
var calendar = newClass({
    clndr    : null,  // html-element (caledar)
    butClndr : null,  // html-element (caledar-button)
    formEl   : null,  // html-element (input of date)
    errMsg   : null,  // html-element (error Message)

    evtDt       : null, // Event-object at A-element of Grid (JS-object)
    days        : null, // A - day in Grid (HTML-element)
    currentCell : null, // A - current day in Grid (HTML-element)
    lastRow     : null, // TR - last Row in Grid (HTML-element)

    sourseDate  : null, // sourse Date (date object)
    currentDate : null, // current Date (date object)

    inputYearT  : null, // Year Input (HTML-element)
    yearList    : null, // Div - year list (HTML-element)
    monthList   : null, // Div - month list (HTML-element)

    bShow4input : null, // Show calendad on focus in the input element
    startYear   : null, // Start year for year list
    lng         : null, // Current language

    timerHideList : null, // Timer of hide lists of year and month (Timer-element)
    ieSelects     : null, // List of Selects in IE6

    common : {}, // JS-object common for All calendar

    init : function (formElId, calendarId, param)
    {
        var conf, pl, i;
        this.formElId   = formElId;
        this.calendarId = calendarId;

        // Set params
        if (!isObject(param)) {
            param = {};
        }
        conf = this.config;
        this.dateFormat  = conf.dateFormat;
        this.cldrPos     = [conf.ClndrX, conf.ClndrY];
        this.hideElm     = [];
        this.bShow4input = conf.bShow4input;
        this.startYear   = conf.startYear;
        pl = ['dateFormat', 'cldrPos', 'hideElm', 'bShow4input', 'startYear'];
        for (i in pl) {
            if (isDefined(param, pl[i])) {
                this[pl[i]] = param[pl[i]];
            }
        }
        this.lng = isDefined(param, 'language') && isDefined(conf.months, param.language) ? param.language : conf.language;

        this.days  = [[],[],[],[],[],[]];
        this.evtDt = [[],[],[],[],[],[]];
        conf.selectHide = conf.selectHide && this.$w0.bv.isIE && (this.$w0.bv.ver < 7);
    },
    onready : function ()
    {
        var conf, hc, topElm, o, ev, od, cYear, i;

        this.formEl = this.$("#" + this.formElId);
        if (!this.formEl || !this.formEl.elm) return;
        this.butClndr = this.$("#" + this.calendarId);
        if (!this.butClndr || !this.butClndr.elm) return;

        if (this.bShow4input) {
            this.formEl.addListener(this, 'onFocus', 'onCallClndr', false)
            this.formEl.addListener(this, 'onkeyPress', 'onPressTab');
            this.formEl.addListener(this, 'onclick', 'stopBubbling', false)
        }

        //initialization Calendar button
        this.butClndr.setDisplay('inline');
        this.butClndr.addListener(this, "onclick", "onCallClndr", true);

        //initialization Calendar body
        this.clndr = this.$w0.makeElement("div");
        conf = this.config;
        hc = conf.htmlCode;
        for (i in conf.weekday[this.lng]) {
            hc = hc.replace(new RegExp("\\{WD" + i + "\\}"), conf.weekday[this.lng][i]);
        }
        this.clndr.elm.innerHTML = hc;
        this.clndr.setClass("calendar");
        this.clndr.addListener(this, "onclick", "onClick_ClndrBody");
        this.$w0.doc.body.appendChild(this.clndr.elm);

        topElm = this.clndr.elm.firstChild.childNodes;
        //event to set calendar
        o = {"lmonth":topElm[0], "rmonth":topElm[2], "lyear":topElm[4].lastChild, "ryear":topElm[4].firstChild};
        for (ev in o) {
            od = this.$(o[ev]);
            od.addListener(this, "onclick", "_eventDrop");
            od.addListener(this, "onmousedown", "onclick_" + ev);
            od.addListener(this, "onmouseup",  "clearTimers");
            od.addListener(this, "onmouseout", "clearTimers");
        }

        this.topMonth = this.$(topElm[1]);
        this.topMonth.addListener(this, "onclick", "onClick4List").list = "monthList";

        this.topYear = this.$(topElm[3]);
        this.topYear.addListener(this, "onclick", "onClick4List").list = "yearList";

        this._makeGrid();
        this._makeMonthList();
        this._makeYearList();

        this.$w0.addListener(this, "onclick", "hideCalendar");

        this.hideElmWr = [];
        for (i in this.hideElm) {
            this.hideElmWr[i] = this.$("#" + this.hideElm[i]);
        }

        cYear = (new Date()).getFullYear();
        this.currentDate = new Date(cYear < this.startYear ? cYear : this.startYear, 0, 1);
        this.sourseDate  = new Date();
 
        this.errMsg = this.$('div.cldr_error', this.clndr);
    },

    stopBubbling : function(evtWr)
    {
        evtWr.stopBubbling();
    },

    onCallClndr : function(evtWr, isBut)
    {
        var re, arr, arr2, curOpen, ieSelects, iSelect, i;

        if(this.common.curOpen && isBut) {
            curOpen = this.common.curOpen;
            curOpen.hideCalendar();
            if(curOpen == this) {
                return;
            }
        }

        //Hide selects from IE6
        if (this.config.selectHide) {
            ieSelects = this.$w0.doc.getElementsByTagName("select");
            this.ieSelects = {};
            for (i = 0; i < ieSelects.length; i++){
                iSelect = this.$w0.getElmWrapper(ieSelects[i]);
                if (iSelect.isVisible()) {
                    this.ieSelects[i] = iSelect;
                    iSelect.setVisibility(false);
                }
            }
        }

        //Hide external elements
        for (i in this.hideElmWr) {
            this.hideElmWr[i].setVisibility(false);
        }

        if (isBut) {
            evtWr.stopBubbling();
            this._eventDrop(evtWr);
        }

        this.clndr.setAbsLeft(this.butClndr.getX() + this.cldrPos[0]);
        this.clndr.setAbsTop(this.butClndr.getY() + this.butClndr.getHeight() + this.cldrPos[1]);

        if (this.formEl.elm.value != "") {
            re  = new RegExp(this.config.iniFormat);
            arr = re.exec(this.dateFormat.toLowerCase());

            re  = new RegExp(this.config.inputFormat);
            arr2 = re.exec(this.formEl.elm.value);


            if(arr2) {
                for (i = 1; i < 4; i++) {
                    if (arr[i] == "m") {
                        this.sourseDate.setMonth(arr2[i]-1);
                        this.currentDate.setMonth(arr2[i]-1);
                        this.CurrMonthSet = this.currentDate.getMonth();
                    } else if (arr[i] == "y") {
                        this.sourseDate.setYear(arr2[i]);
                        this.currentDate.setYear(arr2[i]);
                    } else {
                        this.sourseDate.setDate(arr2[i]);
                        this.currentDate.setDate(arr2[i]);
                    }
                }
                this._hideErr();
            } else {
                this._showErr('incorrectDate');
            }
        }

        //Create input element of input year
        if (this.inputYearT == null){
            this.inputYearT = this.$w0.makeElement('input',{"type":"text","maxlength":"4"});
            this.inputYearT.setClass("year_input");
            this.inputYearT.addListener(this, "onkeypress", "onKeypPressYearInput");
            this.inputYearT.addListener(this, "onkeyup", "onKeypUpYearInput");
            this.inputYearT.addListener(this, "onblur", "onBlurYearInput");
        }
        this.topYear.elm.appendChild(this.inputYearT.elm);

        this._makeCalendar();
        this.clndr.show(true);
        this.common.curOpen = this;
    },

    onClick_ClndrBody : function(evtWr)
    {
        this._hideLists();
        evtWr.stopBubbling();
    },

    onPressTab : function(evtWr)
    {
        if (evtWr.keyCode == 9) {
            this.hideCalendar()
        }
    },

    hideCalendar : function (evtWr)
    {
        var i;

        if (this.clndr.isShow()) {
            if (this.inputYearT) this.inputYearT.elm.parentNode.removeChild(this.inputYearT.elm);
            this._hideLists();
            this.clndr.hide();
        }
        this.common.curOpen = null;

        //Show selects from IE6
        if (this.config.selectHide){
            for (i in this.ieSelects) {
                this.ieSelects[i].setVisibility(true);
            }
        }

        //Show external elements
        for (i in this.hideElmWr) {
            this.hideElmWr[i].setVisibility(true);
        }
    },

    onclick_lmonth : function(evtWr, obj)
    {
        this.currentDate.setMonth(this.currentDate.getMonth() - 1);
        this._fastChPrepare("Month", "decr");
    },
    onclick_rmonth : function(evtWr, obj)
    {
        this.currentDate.setMonth(this.currentDate.getMonth() + 1);
        this._fastChPrepare("Month", "incr");
    },

    onclick_lyear : function(evtWr, obj)
    {
        this.currentDate.setYear(this.currentDate.getFullYear() - 1);
        this._fastChPrepare("Year", "decr");
    },

    onclick_ryear : function(evtWr, obj)
    {
        this.currentDate.setYear(this.currentDate.getFullYear() + 1);
        this._fastChPrepare("Year", "incr");
    },

    clearTimers : function(evtWr, obj)
    {
        if (this.timerFast) {
            clearTimeout(this.timerFast);
            this.timerFast = null;
       }
        if (this.intervalFast) {
            clearInterval(this.intervalFast);
            this.intervalFast = null;
            this._makeCalendar();
       }
    },

    onclick_date : function(evtWr, obj)
    {
        var ExpDate;

        this._eventDrop(evtWr);
        this.currentDate.setDate(obj.dt);

        //export date
        ExpDate = this.dateFormat.toLowerCase();
        ExpDate = ExpDate.replace("m", this._forwardZero(this.currentDate.getMonth()+1));
        ExpDate = ExpDate.replace("d", this._forwardZero(this.currentDate.getDate()));
        ExpDate = ExpDate.replace("y", this.currentDate.getFullYear());
        this.formEl.elm.value = ExpDate;

        this.hideCalendar();
        this.dateSelect();
    },

    onClick4List : function(evtWr, obj)
    {
        this.$w0.setTimeout(this.config.ListDeley, this, "_onTimerList", [obj.list]);
    },

    onclick_monthlist : function (evtWr, obj)
    {
        this._eventDrop(evtWr);
        this._setMonth(obj.month);
    },

    onclick_yearlist : function (evtWr, obj){
        this._eventDrop(evtWr);
        this._setYear(obj.year);
    },

    onKeypUpYearInput : function(evtWr){
        if (this.inputYearT.elm.value.length > 2){
            if (this.inputYearT.elm.value.length > 4){
                this.inputYearT.elm.value = this.inputYearT.elm.value.substr(0,4);
            }
            this._setYear(this.inputYearT.elm.value);
        }
    },

    onKeypPressYearInput : function(evtWr){
        this.yearList.hide();
        if (evtWr.keyCode == 13) {
            this.onBlurYearInput(evtWr);
            this.inputYearT.elm.blur();
            evtWr.eventDrop();
        } else if (evtWr.charCode > 47 && evtWr.charCode < 58){
            return;
        } else if (evtWr.charCode) {
            evtWr.eventDrop();
        }
    },

    onBlurYearInput : function(evtWr){
        if (this.yearList.isShow()) {
            return;
        } else if (this.inputYearT.elm.value.length == 2) {
            this._setYear((this.inputYearT.elm.value > this.config.Year2NumBound  ? "19" : "20") + this.inputYearT.elm.value);
        } else if (this.inputYearT.elm.value.length == 1) {
            this.inputYearT.elm.value = this.currentDate.getFullYear();
        }
    },

    // Method for redefine
    dateSelect : function(){
    },

    _hideLists : function()
    {
        this.monthList.hide();
        this.yearList.hide();
        clearTimeout(this.timerHideList);
        this.timerHideList = null;
    },

    _onTimerList : function (list)
    {
        this[list].show();
        if (this.timerHideList) {
            clearTimeout(this.timerHideList);
        }
        this.timerHideList = this.$w0.setTimeout(this.config.ListDeleyHide, this, "_hideLists");
    },

    _setMonth : function(month)
    {
        this.topMonth.write(this.config.months[this.lng][month]);
        this.currentDate.setMonth(month);
        this.monthList.hide();
        this._makeCalendar();
    },

    _setYear : function(year)
    {
        this.inputYearT.elm.value = year;
        this.currentDate.setYear(year);
        this.yearList.hide();
        this._makeCalendar();
    },

    _makeCalendar : function()
    {
        var curDate, tmpDt, iday, dayQtt, i, j;

        this._changeYearMonth();

        curDate = this.currentDate;

        this.currentCell.removeClass("currday");
        if (this.$w0.bv.isIE) this.currentCell.removeClass("cur_cell_ie");

        tmpDt = new Date(curDate.getFullYear(), curDate.getMonth()+1, 0);
        dayQtt = tmpDt.getDate();
        tmpDt.setDate(1);
        iday = tmpDt.getDay();
        iday = iday ? 2 - iday : -5;

        for (i = 0; i < this.days.length; i++) {

            if (i == 5) {
                if (iday > dayQtt) {
                    this.lastRow.setDisplay('none');
                    break;
                } else {
                    this.lastRow.setDisplay('table-row');
                }
            }

            for (j = 0; j < 7; j++) {
                if(iday <= 0 || iday > dayQtt) {
                    this.days[i][j].setDisplay(false);
                } else {
                    if (iday == curDate.getDate()){
                        if (this.sourseDate.getMonth() == curDate.getMonth()){
                            this.days[i][j].addClass("currday");
                            this.currentCell = this.days[i][j];
                        }
                    }
                    this.days[i][j].setDisplay(true);
                    this.days[i][j].write(iday);
                    this.evtDt[i][j].dt = iday;
                }
                iday++;
            }
        }
    },

    _changeYearMonth : function()
    {
        var curDate = this.currentDate;
        this.topMonth.write(this.config.months[this.lng][curDate.getMonth()]);
        this.inputYearT.elm.value = curDate.getFullYear();
    },

    _makeGrid : function()
    {
        var tbl, tr, td, i, j;
        tbl = this.$('table', this.clndr).elm;

        for (i = 1; i < 7; i++) {

            tr = this.$w0.makeElement('tr').elm;
            tbl.appendChild(tr);
            for (j = 0; j < 7; j++) {
                this.days[i-1][j]  = this._makeTagLink('A');
                this.evtDt[i-1][j] = this.days[i-1][j].addListener(this, "onclick", "onclick_date");

                td = this.$w0.makeElement('td', null, this.days[i-1][j].elm).elm;
                if(j > 4){
                    td.className = "weekend";
                }
                tr.appendChild(td);
            }
        }

        this.currentCell = this.days[0][0];
        this.lastRow = this.$(tr);
    },

    _makeMonthList : function()
    {
        var month_a, i;
        this.monthList = this.$w0.makeElement('div');
        this.monthList.setClass("month_list");
        this.monthList.addListener(this, "onclick", "_eventDrop");

        for (i = 0; i < 12; i++){
            month_a = this._makeTagLink(this.config.months[this.lng][i]);
            month_a.addListener(this, "onclick", "onclick_monthlist").month = i;
            this.monthList.elm.appendChild(month_a.elm);
        }

        this.clndr.elm.appendChild(this.monthList.elm);

    },

    _makeYearList : function()
    {
        var year_a, i;
        this.yearList = this.$w0.makeElement('div');
        this.yearList.setClass("year_list");
        this.yearList.addListener(this, "onclick", "_eventDrop");

        for (i = this.startYear; i > 1944; i--){
            year_a = this._makeTagLink(i);
            year_a.addListener(this, "onclick", "onclick_yearlist").year = i;
            this.yearList.elm.appendChild(year_a.elm);
        }
        this.clndr.elm.appendChild(this.yearList.elm);
    },

    _makeTagLink : function(val)
    {
        return this.$w0.makeElement('a', {"href":"#"}, val + "");
    },

    _forwardZero : function(val)
    {
        return (val>9 ? "" : "0") + val;
    },

    _eventDrop : function(evtWr)
    {
        evtWr.eventDrop();
        evtWr.elmWr.elm.blur();
    },

    // --- Fast-change functions ---
    _fastChPrepare : function (elm, oper)
    {
        this._makeCalendar();
        this.timerFast = this.$w0.setTimeout(this.config.nextsDelay, this, "_onTimer", [elm, oper]);
    },

    _onTimer : function (elm, oper)
    {
        this.intervalFast = this.$w0.setInterval(this.config.nextInterval, this, "_change" + elm, [oper]);
        this.timerFast = null;
    },

    _changeMonth : function (oper)
    {
        this.currentDate.setMonth(this.currentDate.getMonth() + (oper == "incr" ? 1 : -1));
        this._changeYearMonth();
    },

    _changeYear : function (oper)
    {
        this.currentDate.setYear(this.currentDate.getFullYear() + (oper == "incr" ? 1 : -1));
        this._changeYearMonth();
    },
    _showErr : function (key)
    {
        this.errMsg.write(this.config[key][this.lng]);
        this.errMsg.show();
    },
    _hideErr : function ()
    {
        this.errMsg.hide();
    },

    config : {
        "bShow4input" : 1,
        "dateFormat"  : "m.d.Y",
        "inputFormat" : "(\\d{1,2})[-./](\\d{1,2})[-./](\\d{1,4})",
        "iniFormat"   : "(\\w{1})[-./](\\w{1})[-./](\\w{1})",
        "nextsDelay"    : 500,
        "nextInterval"  : 150,
        "ListDeley"     : 50,
        "ListDeleyHide" : 10000,
        "Year2NumBound" : 35,
        "ClndrX" : 100,
        "ClndrY" : 1,
        "startYear" : 2015,
        "selectHide" : true,
        "language" : 'ru',
        "incorrectDate" : {
            "en" : "Error. Incorrect date format!",
            "ru" : "Ошибка. Не корректный формат даты!",
            "ua" : "Помилка. Не коректний формат дати!"
        },
        "months" : {
            "en" : ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
            "ru" : ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
            "ua" : ["Січень", "Лютий", "Березень", "Квітень", "Травень", "Червень", "Липень", "Серпень", "Вересень", "Жовтень", "Листопад", "Грудень"]
        },
        "weekday" : {
            "en" : ["Mo", "Tu", "We", "Thu", "Fr", "Sa", "Su"],
            "ru" : ["Пн", "Вт", "Ср", "Чт",  "Пт", "Сб", "Вс"],
            "ua" : ["Пн", "Вт", "Ср", "Чт",  "Пт", "Сб", "Нд"]
        },
        "htmlCode" : '<div class="clndr_set"><a href="#">&#171;</a><div class="clndr_month">\xA0</div><a href="#">&#187;</a><div class="clndr_year"></div><div class="topyearbut"><a class="ty_up" href="#">\xA0</a><a class="ty_down" href="#">\xA0</a></div></div><table summary="Calendar"><tbody><tr><th>{WD0}</th><th>{WD1}</th><th>{WD2}</th><th>{WD3}</th><th>{WD4}</th><th class="weekend">{WD5}</th><th class="weekend">{WD6}</th></tr></tbody></table><div class="cldr_error"></div>'
    }

});