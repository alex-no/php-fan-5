/**
 * JavaScript Wrapper - The wrappers of browser JavaScript objects
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * This version includes three basic wrappers:
 *     winWrapper - wrapper of window;
 *    elmWrapper - wrapper of html-element;
 *    evtWrapper - wrapper of event-object;
 * For further information visit:
 *     http://www.alex.4n.com.ua/js-wrapper/
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version:  03.03.11
 * @modified: 2015-04-23 21:51:00
 */
// ===== Additional functions and classes ===== \\
function isDefined(obj, prop)
{
    return obj != null && !checkType(obj, "undefined") && (checkType(prop, "undefined") || (checkType(obj, "object") && (typeof(obj[prop]) + "").toLowerCase() != "undefined"));
}
function isObject(obj)
{
    return obj != null && checkType(obj, "object");
}
function checkType(obj, tp)
{
    return (typeof(obj) + "").toLowerCase() == tp;
}
// --- Implements add property to any object --- \\
function implement(destObj, srcObj, notReplace, isNext)
{
    if (destObj && srcObj) {
        if (!isNext && (srcObj instanceof Array)) {
            for (var i=0; i<srcObj.length; i++) {
                implement(destObj, srcObj[i], notReplace);
            }
        } else {
            var v, k;
            for (k in srcObj) {
                v = srcObj[k];
                if(isObject(v) && !(v instanceof Function) && typeof(destObj[k]) == typeof(v)) {
                    destObj[k] = implement(destObj[k], v, notReplace, true);
                } else if (!notReplace || !destObj[k]) {
                    destObj[k] = v;
                }
            }
        }
        return destObj;
    }
    return null;
}

// --- Check type of browser --- \\
var browserVer = (function(w)
{
    var d, o, ie, m, re, k;
    d = w.document;
    o = {};

    o.aN = w.navigator.appName;
    o.aV = w.navigator.appVersion;
    o.uA = w.navigator.userAgent;

    o.isDOM      = Boolean(d.getElementById);
    o.isStrict   = d.compatMode == 'CSS1Compat';
    o.isNetscape = o.aN == "Netscape";
    o.isMozilla  = o.isNetscape && o.isDOM && !o.isSafari;

    o.isOpera  = isDefined(w, 'opera') && o.isDOM;
    o.isOperaN = o.isOpera && Boolean(d.readyState);

    o.isChrome = /chrome/i.test(o.uA);
    o.isSafari = !o.isChrome && /webkit/i.test(o.uA);

    o.isIE    = isDefined(d, 'all') && Boolean(d.all.item) && !o.isOpera && !o.isSafari || /\Wtrident\W/i.test(o.uA);
    o.isNewIE = o.isIE && o.isNetscape;
    o.isOldIE = o.isIE && !o.isNewIE;

    o.isFF = o.isMozilla && /\Wfirefox\W/i.test(o.uA);

    o.identifyBrowser = o.isIE || o.isOpera || o.isChrome || o.isSafari || o.isNetscape || o.isMozilla;

    o.ver = null;
    re = {
        isOldIE    : /msie\s*([\d\.]+)/i,
        isNewIE    : /\Wrv\:\s*([\d\.]+)/i,
        isOpera    : /opera\W*([\d\.]+)/i,
        isChrome   : /\Wchrome([\d\.]+)/i,
        isSafari   : /\D([\d\.]+)\s+safari/i,
        isFF       : /firefox\W*([\d\.]+)/i,
        isNetscape : /[a-z]+\/([\d\.]+)\s*\D*\s*$/i
    };
    for (k in re) {
        if (o[k]) {
            m = re[k].exec(o.uA);
            o.ver = m ? m[1] : null;
            break;
        }
    }

    o.isIE10 = o.isTooOldIE = false;
    if (o.isIE) {
        if (o.ver == 10) {
            o.isIE10 = true;
        } else if (o.ver < 7) {
            o.isTooOldIE = true;
        }
    }
    return o;
})(window);
var browserVersion = browserVer;

// --- Basic Broadcaster for different object --- \\
var basicBroadcaster = function()
{
    this._listeners = {};
};
basicBroadcaster.prototype = {
    broadcastMessage : function()
    {
        var eType, mName, arg, i;
        eType = arguments[0];
        arg = [];
        for (i = 1; i<arguments.length; ++i) {
            arg[i-1] = arguments[i];
        }
        if (this._listeners[eType]) for (mName in this._listeners[eType]) {
            this._doMethod(this._listeners[eType][mName], mName, arg);
        }
        this._doMethod(this._getListeners(), eType, arg);
    },
    addListener : function(obj, eType, mName, param)
    {
        this.removeListener(obj, eType, mName);
        if(!isDefined(param)) param = {};
        this._getListeners(eType, mName).push([obj, param]);
        return param;
    },
    removeListener : function(obj, eType, mName)
    {
        var list, ql, i, p;
        list = this._getListeners(eType, mName);
        for (i = list.length - 1; i >= 0; i--) {
            if (list[i][0] === obj || obj === null) {
                list.splice(i, 1);
                break;
            }
        }
        ql = 0;
        for (p in this._listeners[eType]) {
            ql += this._listeners[eType][p].length;
        }
        return ql;
    },
    /**
     * Alert message
     */
    alert : function(srting, data, win)
    {
        if (data instanceof Array) {
            var re, res;
            re = /\{\$(\d+)\}/g;
            while ((res = re.exec(srting)) != null) {
                if (isDefined(data, res[1])) srting = srting.replace(res[0], data[res[1]]);
            }
        }
        if (!isDefined(win)) {
            win = isDefined(this, "win") ? this.win : (isDefined(this, "winWr") && isDefined(this.winWr, "win") ? this.winWr.win : window);
        }
        win.alert(srting);
        return this;
    },

    /**
     * Error message
     */
    errMsg : function(srting, data, win)
    {
        if(srting && this.config.DebugMode) {
            this.alert(srting, data, win);
        }
        return this;
    },
    _getListeners : function(eType, mName)
    {
        if (!eType) eType = "all";
        if (!mName || eType == "all") mName = eType;
        if (!this._listeners[eType]) this._listeners[eType] = {};
        if (!this._listeners[eType][mName]) this._listeners[eType][mName] = [];
        return this._listeners[eType][mName];
    },
    _doMethod : function(list, mName, arg)
    {
        if (list) {
            var cm, emsg, i;
            for (i = 0; i<list.length; i++) {
                if (list[i] && typeof(list[i][0][mName]) == "function") {
                    arg.push(list[i][1]);
                    try {
                        list[i][0][mName].apply(list[i][0], arg);
                    } catch(e) {
                        cm = this.config["errMessages_" + this.config.curLanguage];
                        emsg = (e.fileName ? cm["BC_err_in"] + e.fileName : "") + (e.lineNumber ? cm["BC_line"] + e.lineNumber : "");
                        emsg += (emsg ? "\n" : "") + (e.name ? e.name + ": " : "") + e.message;
                        this._errMsg("BC_cant_run", [mName, emsg]);
                    }
                    arg.pop();
                }
            }
        }
    },
    _errMsg : function(key, data)
    {
        var msg = this.config["errMessages_" + this.config.curLanguage];
        if (isDefined(msg, key)) this.errMsg(msg[key], data);
    }

};


// ------- Broadcaster for HTML object ------- \\
var htmlBroadcaster = {
    iniBroadcaster : function()
    {
        this._listenersFunc = {};
        this._BBC = new basicBroadcaster();
    },
    _broadcastMessage : function(evt)
    {
        var evtWr;
        if (evt._wrapper) {
            evtWr = evt._wrapper;
            evtWr.setElement.call(evtWr, this);
        } else {
            try {
                evtWr = new evtWrapper(evt, this);
            } catch(e) {
                evtWr = {};
                evtWr.evt = evt;
            }
        }
        this._BBC.broadcastMessage("on" + evtWr.evt.type, evtWr);
    },
    _addListener : function(obj, EventType, mName, param, useCapture)
    {
        var eType = this._getType(obj, EventType, "addListener");
        if(eType == null) return null;
        if (!isDefined(mName)) mName = EventType;
        var elm = this._bcElement(eType);
        if (!elm) return null;
        if (!this._listenersFunc[eType]) {
            this._listenersFunc[eType] = (function(BCobj){return function(e){return BCobj.broadcastMessage.call(BCobj, e);};})(this);
            if (this.bv.isOldIE) elm.attachEvent(eType, this._listenersFunc[eType]);
            else if (this.addEventListener) elm.addEventListener(eType.substr(2), this._listenersFunc[eType], useCapture ? true : false);
            else elm[eType] = this._listenersFunc[eType];
        }
        return this._BBC.addListener(obj, eType, mName, param);
    },
    _removeListener : function(obj, EventType, mName, useCapture)
    {
        var eType = this._getType(obj, EventType, "removeListener");
        if(eType == null) return;
        if (!isDefined(mName)) mName = EventType;
        var elm = this._bcElement(eType);
        if (!elm) return;
        if (!this._BBC.removeListener(obj, eType, mName) && this._listenersFunc[eType]) {
            if (this.bv.isOldIE) elm.detachEvent(eType, this._listenersFunc[eType]);
            else if (this.removeEventListener) elm.removeEventListener(eType.substr(2), this._listenersFunc[eType], useCapture ? true : false);
            else elm[eType] = undefined;
            this._listenersFunc[eType] = null;
        }
    },
    _getType : function(obj, EventType, met)
    {
        if(!isObject(obj)) {
            this._errMsg("incorObj",[met]);
            return null;
        }
        if(typeof(EventType) != "string") {
            this._errMsg("incorEvt");
            return null;
        }
        return (this.config.SubscriberUse_on ? "" : "on") + EventType.toLowerCase();
    },
    alert   : basicBroadcaster.prototype.alert,
    errMsg  : basicBroadcaster.prototype.errMsg,
    _errMsg : basicBroadcaster.prototype._errMsg
};


// ======= Event Wrapper ======== \\
var evtWrapper = function(e, elmWr)
{
    this.evt = e ? e : (isDefined(window, "event") ? window.event : null);
    if (this.evt != null) this.evt._wrapper = this;
    this.setElement(elmWr);
};
evtWrapper.prototype = {
    eventStatus : true,
    bubbleStatus : true,
    eventDrop : function(doNot)
    {
        if (!doNot && this.evt) {
            if (this.bv.isOldIE) this.evt.returnValue = false;
            else if (this.evt.preventDefault) this.evt.preventDefault();
            this.eventStatus = false;
        }
        return this;
    },
    stopBubbling : function(doNot)
    {
        if (!doNot && this.evt) {
            if (this.bv.isOldIE) this.evt.cancelBubble = true;
            else if (this.evt.stopPropagation) this.evt.stopPropagation();
            this.bubbleStatus = false;
        }
        return this;
    },
    setElement : function(elmWr)
    {
        if (elmWr) this.elmWr = elmWr;
        else {
            if(!this.evt) return this;
            var elm = this.bv.isOldIE ? this.evt.srcElement : this.evt.target;
            if (!elm) return this;
            this.elmWr = new elmWrapper(elm);
        }
        var winWr = this.winWr = elmWr.win && elmWr.doc ? elmWr : elmWr.winWr;
        if (winWr && this.evt) {
            if (this.bv.isOldIE || this.bv.isOperaN) {
                this.absX=this.evt.clientX + winWr.getScrollX();
                this.absY=this.evt.clientY + winWr.getScrollY();
            } else if (this.bv.isOpera) {
                this.absX=this.evt.clientX;
                this.absY=this.evt.clientY;
            } else if (this.bv.isNetscape) {
                this.absX=this.evt.pageX;
                this.absY=this.evt.pageY;
            }
            var os = elmWr.elm ? elmWr.getAbsOffset() : [0, 0];
            this.relX = this.absX - os[0];
            this.relY = this.absY - os[1];
        } else this.absX=this.absY=this.relX=this.relY=0;

        if (isDefined(this.evt, 'keyCode')) {
            if (this.evt.which == null) {
                //IE
                if (this.evt.keyCode < 32) {
                    this.charCode = null;
                } else {
                    this.charCode = this.evt.keyCode;
                }
            } else if (this.evt.which != 0 && this.evt.charCode != 0) {
                //Not-IE
                this.charCode = this.evt.which < 32 ? null : this.evt.which;
            } else {
                this.charCode = isDefined(this.evt, 'charCode') ? this.evt.charCode : null;
            }
            this.keyCode = (this.charCode) ? null : this.evt.keyCode;
        }
        return this;
    },
    bv : browserVer
};

// --- Event processor allows to call from HTML --- \\
function eventProcessor(elm, evt, obj, method, param)
{
    var evtWr;
    var elmWr = getElmWrapper(elm);
    if (evt._wrapper) {
        evtWr = evt._wrapper;
        evtWr.setElement.call(evtWr, elmWr);
    } else {
        evtWr = new evtWrapper(evt, elmWr);
    }
    if (!obj) obj = window;
    return obj[method].call(obj, evtWr, param);
}


// ======== HTML-elements wrapper ======== \\
var elmWrapper = function(elm, winWr)
{
    this.elm = elm;
    this.winWr = winWr ? winWr : (elm.ownerDocument && elm.ownerDocument._wrapper ? elm.ownerDocument._wrapper : window._wrapper);
    if (this.elm) {
        elm._wrapper = this;
        if (isDefined(elm, "style")) {
            this.style = elm.style;
            this.css = this._getCSS(elm);
        } else this.style = this.css = {};
        this.iniBroadcaster();
        this.userData = {};
    }
};
elmWrapper.prototype = {
    // .... Get neighbouring elenents .... \\
    getNextSibling : function()
    {
        var r = null;
        if (this.elm) {
            r = this.elm.nextSibling;
            while (r && r.nodeType != 1) r = r.nextSibling;
        }
        return r ? getElmWrapper(r, this.winWr) : null;
    },

    getPreviousSibling : function()
    {
        var r = null;
        if (this.elm) {
            r = this.elm.previousSibling;
            while (r && r.nodeType != 1) r = r.previousSibling;
        }
        return r ? getElmWrapper(r, this.winWr) : null;
    },

    getParent : function()
    {
        if (this.elm) {
            var r = this.elm.parentNode;
            return (r && r.nodeType == 1) ? getElmWrapper(r, this.winWr) : null;
        }
        return null;
    },

    getChild : function(n)
    {
        var nl = this.getChildren(false);
        if (n < 0) n += nl.length;
        return nl && nl[n] ? getElmWrapper(nl[n], this.winWr) : null;
    },

    getChildren : function(wrap)
    {
        var r, nl, i;
        r = [];
        if (!isDefined(wrap)) wrap = true;
        if (this.elm) {
            nl = this.elm.childNodes;
            for (i = 0; i < nl.length; i++) {
                if (nl[i].nodeType == 1) r.push(wrap ? getElmWrapper(nl[i], this.winWr) : nl[i]);
            }
        }
        return r;
    },

    getClone : function(incl)
    {
        if (this.elm) {
            var elmWr = getElmWrapper(this.elm.cloneNode(isDefined(incl) ? incl : true), this.winWr);
            elmWr.elm.removeAttribute('id');
            return elmWr;
        }
        return null;
    },

    appendChild : function(appElm)
    {
        if (appElm && this.elm) {
            if (appElm.elm) appElm = appElm.elm;
            if (appElm.nodeType == 1) {
                this.elm.appendChild(appElm);
                return true;
            }
        }
        return false;
    },

    remove : function()
    {
        var parent = this.getParent();
        if (parent && parent.elm) {
            parent.elm.removeChild(this.elm);
            return true;
        }
        return false;
    },
    data : function(key)
    {
        return this.elm.getAttribute("data-" + key);
    },

    // .... Get parameters .... \\
    getAbsOffset : function()
    {
        var bv, telm, elm, ret, nl, parN;
        bv  = this.bv;
        elm = this.elm;
        ret = [0, 0];
        ret.cor = function(x, y)
        {
            this[0] += x;
            this[1] += y;
        };
        nl = [];
        nl.fnd = function(v)
        {
            for (var i = 0; i < this.length; i++) {
                if (this[i] == v) return true;
            }
            return false;
        };

        if (bv.isOldIE || bv.isOpera || bv.isMozilla || bv.isSafari) {

            telm = elm;
            while (this._checkTag(telm)) {
                parN = telm.offsetParent;
                if (this._checkTag(parN)) {
                    nl.push(parN);
                    this._corPosition(parN, ret, bv, elm);
                }
                ret.cor(telm.offsetLeft, telm.offsetTop);
                telm = parN;
            }

            if (this._getCSS(elm).position != "absolute") {
                parN = elm.parentNode;
                while (this._checkTag(parN)) {
                    if (!nl.fnd(parN)) this._corPosition(parN, ret, bv, null);
                    parN = parN.parentNode;
                }
            }
        } else if (bv.isNetscape) {
            ret.cor(elm.offsetLeft, elm.offsetTop);
        }

        return ret;
    },
    getAbsLeft : function()
    {
        return this.getAbsOffset()[0];
    },

    getAbsTop : function()
    {
        return this.getAbsOffset()[1];
    },

    getRelOffset : function()
    {
        var bv = this.bv;
        var left = 0;
        var top  = 0;
        var elm = this.elm;
        if (bv.isOldIE || (bv.isOpera && parseFloat(bv.ver) < 8)) {
            left = elm.offsetLeft;
            top  = elm.offsetTop;
        } else if (bv.isMozilla || bv.isOpera) {
            left = elm.offsetLeft - elm.parentNode.offsetLeft;
            top  = elm.offsetTop - elm.parentNode.offsetTop;
        }
        return [left, top];
    },
    getRelLeft : function()
    {
        return this.getRelOffset()[0];
    },

    getRelTop : function()
    {
        return this.getRelOffset()[1];
    },

    getWidth : function()
    {
        var bv = this.bv;
        if (bv.isOldIE || bv.isMozilla || bv.isOperaN || bv.isSafari) return this.elm.offsetWidth;
        if (bv.isOpera) return this.css.pixelWidth;
        return null;
    },

    getHeight : function()
    {
        var bv = this.bv;
        if (bv.isOldIE || bv.isMozilla || bv.isOperaN || bv.isSafari) return this.elm.offsetHeight;
        if (bv.isOpera) return this.css.pixelHeight;
        return null;
    },

    //deprecated
    getZIndex : function()
    {
        return this.css.zIndex;
    },

    // type: color (default), backgroundColor, borderLeftColor, borderRightColor, borderTopColor, borderBottomColor
    getColor : function(type)
    {
        if (!type) type = "color";
        var clr = this.css[type];
        var toHex = function(s){
            var n = parseInt(s);
            return (n<16? "0" : "") + n.toString(16);
        };
        if ((rArr = /^rgb\((\d+)\,\s*(\d+)\,\s*(\d+)\)$/i.exec(clr))) clr = "#" + toHex(rArr[1]) + toHex(rArr[2]) + toHex(rArr[3]);
        return clr;
    },

    isVisible : function()
    {
        return this.css.visibility.toLowerCase().charAt(0) != 'h';
    },

    isDisplay : function()
    {
        return this.css.display != 'none';
    },

    isShow : function()
    {
        return this.isDisplay() && this.isVisible();
    },

    // -- Set parameters --
    setInnerHtml : function(code, pos)
    {
        var tmpElm, lst, i;

        tmpElm = this.winWr.doc.createElement(this.elm.tagName);
        try {
            tmpElm.innerHTML = code;
        } catch(e) {
            this.errMsg(e.message + "\n\n" + code);
            return this;
        }

        lst = tmpElm.childNodes;

        if (!isDefined(pos) || pos == 0) {
            this.elm.innerHTML = code;
        } else if (pos > 0) {
            for (i = 0; i < lst.length; i++) {
                this.elm.appendChild(lst[i]);
            }
        } else {
            for (i = lst.length - 1; i >= 0; i--) {
                this.elm.insertBefore(lst[i], this.elm.firstChild);
            }
        }
        return this;
    },
    setAbsLeft : function(x)
    {
        var bv = this.bv;
        if (bv.isOpera) this.style.pixelLeft=x;
        else this.style.left=x + "px";
        return this;
    },

    setAbsTop : function(y)
    {
        var bv = this.bv;
        if (bv.isOpera) this.style.pixelTop=y;
        else this.style.top = y + "px";
        return this;
    },

    moveAbs : function(x,y)
    {
        this.setAbsLeft(x);
        this.setAbsTop(y);
        return this;
    },

    moveRel : function(x,y)
    {
        this.moveAbs(this.getRelLeft()+x, this.getRelTop()+y);
        return this;
    },

    //deprecated
    setZIndex : function(z)
    {
        this.style.zIndex=z;
        return this;
    },


    setVisibility : function(v)
    {
        this.style.visibility = v ? "visible" : "hidden";
        return this;
    },

    setDisplay : function(v)
    {
        if (!v) v = "none";
        else if (v.toString() == "true") v="block";
        else if (this.bv.isOldIE && this.config.ReplaceDisplay4IE && v != "none" && v != "inline") v = "block";
        this.style.display = v;
        return this;
    },

    invVisibility : function()
    {
        this.setVisibility(!this.isVisible());
        return this;
    },

    invDisplay : function(v)
    {
        this.setDisplay(this.isDisplay() ? "none" : (v ? v : "block"));
        return this;
    },

    show : function(display)
    {
        if (display || !isDefined(display)) this.setDisplay(!isDefined(display) || display == "none" ? true : display);
        else this.setVisibility(true);
        return this;
    },

    hide : function(display)
    {
        if (display || !isDefined(display)) this.setDisplay(false);
        else this.setVisibility(false);
        return this;
    },

    write : function(text, pos, noDOM)
    {
        var bv = this.bv;
        if (bv.isDOM && !noDOM) {
            if (!pos){while (this.elm.hasChildNodes()) this.elm.removeChild(this.elm.firstChild);}
            var tNode = this.winWr.doc.createTextNode(text);
            if (pos<0) this.elm.insertBefore(tNode, this.elm.firstChild);
            else this.elm.appendChild(tNode);
        } else {
            this.elm.innerHTML = pos ? (pos>0 ? this.elm.innerHTML + text : text + this.elm.innerHTML) : text;
        }
        return this;
    },

    setBgColor : function(c)
    {
        var bv = this.bv;
        if (bv.isOldIE || bv.isMozilla || bv.isOperaN) this.style.backgroundColor=c;
        else if (bv.isOpera) this.style.background=c;
        return this;
    },

    setBgImage : function(url)
    {
        var bv = this.bv;
        if (bv.isOldIE || bv.isMozilla || bv.isOpera) this.style.backgroundImage="url("+url+")";
        return this;
    },

    setClip : function(top,right,bottom,left)
    {
        var bv = this.bv;
        if (bv.isOldIE || bv.isMozilla || bv.isOperaN) this.style.clip="rect("+this._setPt(top)+" "+this._setPt(right)+" "+this._setPt(bottom)+" "+this._setPt(left)+")";
        return this;
    },

    getCursorPos : function()
    {
        var elm = this.elm;
        if (isDefined(elm, 'selectionStart')) {
            return elm.selectionStart;
        }
        var doc = this.winWr.doc;
        if (this.bv.isOldIE && isDefined(doc, 'selection') && isDefined(elm, 'value')) {
            elm.focus();
            var sel = doc.selection.createRange();
            sel.moveStart ('character', -elm.value.length);
            return sel.text.length;
        }
        return 0;
    },
    setCursorPos : function(pos)
    {
        var elm = this.elm;
        if(isDefined(elm, 'setSelectionRange')) {
            elm.focus();
            elm.setSelectionRange(pos, pos);
        } else if (isDefined(elm, 'createTextRange')) {
            var range = elm.createTextRange();
            range.collapse(true);
            range.moveEnd('character', pos);
            range.moveStart('character', pos);
            range.select();
        }
        return this;
    },

    setClass : function(className)
    {
        this.elm.className = className;
        return this;
    },
    addClass : function(className)
    {
        this.removeClass(className);
        this.setClass((this.elm.className ? this.elm.className + " " : "") + className);
        return this;
    },
    removeClass : function(className)
    {
        var curClass = this.elm.className;
        if (!curClass || curClass == className) curClass = "";
        else {
            curClass = curClass.replace(new RegExp("^" + className + "\\s+", "i"), "");
            curClass = curClass.replace(new RegExp("\\s+" + className + "(?!\\S)", "i"), "");
        }
        this.elm.className = curClass;
        return this;
    },
    hasClass : function(className)
    {
        return this.elm.className.search(new RegExp("(?:^|\\s+)" + className + "(?!\\S)", "i")) != -1;
    },
    _bcElement : function(eType)
    {
        if (!this.elm) {
            this._errMsg("elmNotSet", [eType]);
            return null;
        }
        return this.elm;
    },
    _getCSS : function(elm)
    {
        var doc = this.winWr.doc;
        if (this.bv.isOldIE) return elm.currentStyle ? elm.currentStyle : elm.style;
        else if (this.bv.isDOM && doc.defaultView && doc.defaultView.getComputedStyle) return doc.defaultView.getComputedStyle(elm, null);
        else if (this.bv.isSafari) return elm.style;
        return elm.style;
    },
    _checkTag : function(tag)
    {
        return tag && tag.nodeType == 1 && tag.nodeName.toUpperCase() != "BODY" && tag.nodeName.toUpperCase() != "HTML";
    },
    _corPosition : function(tag, ret, bv, elm)
    {
        var ver, css, cX, cY;
        ver = parseFloat(bv.ver);
        if (elm && (css = this._getCSS(tag)) && (!bv.isOpera || ver < 9) && !bv.isOldIE) {
            // ToDo: Add condition for border
            cX = parseInt("0" + css.borderLeftWidth, 10);
            cY = parseInt("0" + css.borderTopWidth, 10);
            if (bv.isMozilla && css.overflow != "visible" && (ver < 3 || !bv.isFF || this._getCSS(elm).position == "absolute")) {
                // ToDo: Add condition for border
                cX = cX * 2;
                cY = cY * 2;
            }
            // Border
            ret.cor(cX, cY);
        }
        // Scroller
        ret.cor(-tag.scrollLeft, -tag.scrollTop);
        return this;
    },
    _setPt : function(v)
    {
        return isFinite(v) ? v + "px" : v;
    },
    bv : browserVer
};
implement(elmWrapper.prototype, htmlBroadcaster);
with (elmWrapper.prototype) {
    implement(elmWrapper.prototype, {
        broadcastMessage : _broadcastMessage,
        addListener      : _addListener,
        removeListener   : _removeListener,
        al : _addListener,
        aL : _addListener,
        rl : _removeListener,
        rL : _removeListener,

        getX  : getAbsLeft,
        getY  : getAbsTop,
        moveX : setAbsLeft,
        moveY : setAbsTop,
        move  : moveAbs,
        moveZ : setZIndex,
        clip  : setClip
    });
}

// --- get wrapper of HTML-element --- \\
function getElmWrapper(elm, winWr)
{
    return !elm ? null : (elm._wrapper && elm._wrapper.elm === elm ? elm._wrapper : new elmWrapper(elm, winWr));
}


// ======= Window wrapper ======== \\
var winWrapper = function(win)
{
    if(win && isDefined(win, "_wrapper")) {
        throw "You can't create many Wrappers for one window!";
    }
    this.iniBroadcaster();
    this._frameL = {};
    this._winL   = {};
    this._delaySubscr = [];
    if (win) {
        this.iniWrapper(win);
    }
};
winWrapper.prototype = {
    win : null,
    doc : null,
    cookieVal   : null,
    cookieExtra : null,
    sidVal      : null,
    _isReady    : false,
    _findRE     : /^(\w+)?(?:\#([\w\-\*]+)|\.([\w\-\*]+)|\[(\w+)(?:\=(.+?))?\])?(?:\:(first|last|\d+))?$/,
    _frameL     : null,
    _winL       : null,
    _delaySubscr : null,

    iniWrapper : function(win)
    {
        if(!win || !win.document) {
            throw "Object window doesn't set!";
        }
        var t, lr, i;
        t =this;
        t.win = win;
        t.doc = t.win.document;
        t.win._wrapper = this;
        t.doc._wrapper = this;
        t.setOnUnload(t, "_unloadCtrl");
        for (i in t._delaySubscr) {
            lr = t._delaySubscr[i];
            t.addListener(lr[0], lr[1], lr[2], lr[3]);
        }
        t._isReady = false;
        t._followReady();
    },
    broadcastMessage : function(evt)
    {
        this._broadcastMessage(evt);
        if (evt.type == "unload") {
            var t = this;
            t.win = null;
            t.doc = null;
            t._isReady = false;
            t._listenersFunc = {};
            t._BBC._listeners = {};
        }
    },
    addListener : function(obj, EventType, mName, param)
    {
        if ((!this.win || this.win.opener) && !this._checkDelay(obj, EventType, mName, false)) {
            if (!isDefined(param)) param = {};
            this._delaySubscr.push([obj, EventType, mName, param]);
        }
        if (this.win) {
            if (EventType == this._getPrefix() + "ready") {
                var eType = this._getType(obj, EventType, "addListener");
                return eType ? this._BBC.addListener(obj, eType, mName, param) : null;
            }
            return this._addListener(obj, EventType, mName, param);
        }
        return param;
    },
    removeListener : function(obj, EventType, mName)
    {
        this._checkDelay(obj, EventType, mName, true);
        if (this.win) {
            if (EventType == this._getPrefix() + "ready") {
                var eType = this._getType(obj, EventType, "removeListener");
                if (eType) this._BBC.removeListener(obj, eType, mName);
                return;
            }
            this._removeListener(obj, EventType, mName);
        }
        return this;
    },

// .... Event subscribing .... \\
    setOnready : function(obj, mName, param)
    {
        return this.addListener(obj, this._getPrefix() + "ready", mName, param);
    },
    setOnload : function(obj, mName, param)
    {
        return this.addListener(obj, this._getPrefix() + "load", mName, param);
    },
    delOnload : function(obj, mName, param)
    {
        this.removeListener(obj, this._getPrefix() + "load", mName, param);
        return this;
    },
    setOnUnload : function(obj, mName, param)
    {
        return this.addListener(obj, this._getPrefix() + "unload", mName, param);
    },
    delOnUnload : function(obj, mName, param)
    {
        this.removeListener(obj, this._getPrefix() + "unload", mName, param);
        return this;
    },
// .... Get elements .... \\
    makeElement : function(name, attr, child, wrap)
    {
        var tag, k;
        tag = this.doc.createElement(name);
        if(attr) {
            for(k in attr)
            tag.setAttribute(k, attr[k]);
        }
        if(child) {
            tag.appendChild(typeof(child) == "string" ? this.doc.createTextNode(child) : isDefined(child, "elm") ? child.elm : child);
        }
        return !isDefined(wrap) || wrap ? getElmWrapper(tag, this) : tag;
    },
    $ : function(sel, src)
    {
        return this._$$(sel, src, true);
    },
    $$ : function(sel, src)
    {
        var r = this._$$(sel, src, false);
        if (r) {
            r.winWr = this;
            r.addListener = r.al = r.aL = function(obj, EventType, mName, param, useCapture)
            {
                var ea = [];
                for (var i = 0; i < this.length; i++) {
                    ea.push(this[i].addListener(obj, EventType, mName, param, useCapture));
                }
                return ea;
            }
        }
        return r;
    },
    $f : function(nm)
    {
        if (!this._frameL[nm]) this._frameL[nm] = new winWrapper();
        return this._frameL[nm];
    },
    $w : function(nm)
    {
        if (this.win.name == nm) return null;
        if (!this._winL[nm]) this._winL[nm] = new winWrapper();
        return this._winL[nm];
    },
    appendChild : function(appElm)
    {
        if (appElm && this.doc && this.doc.body) {
            if (appElm.elm) appElm = appElm.elm;
            if (appElm.nodeType == 1) {
                this.doc.body.appendChild(appElm);
                return true;
            }
        }
        return false;
    },

// .... Global window/document methods .... \\
    getDocFrame : function()
    {
        return this.bv.isStrict ? this.doc.documentElement : this.doc.body;
    },
    getWindowLeft : function()
    {
        var bv = this.bv;
        if (bv.isOldIE || bv.isOperaN) return this.win.screenLeft;
        if (bv.isNetscape || bv.isOpera) return this.win.screenX;
        return null;
    },
    getWindowTop : function()
    {
        var bv = this.bv;
        if (bv.isOldIE || bv.isOperaN) return this.win.screenTop;
        if (bv.isNetscape || bv.isOpera) return this.win.screenY;
        return null;
    },
    getWindowWidth : function()
    {
        var bv = this.bv;
        if (bv.isOldIE) return this.getDocFrame().clientWidth;
        if (bv.isNetscape || bv.isOpera) return this.win.innerWidth;
        if (bv.isMozilla || bv.isFF) return this.doc.body.clientWidth;
        return null;
    },
    getWindowHeight : function()
    {
        var bv = this.bv;
        if (bv.isOldIE) return this.getDocFrame().clientHeight;
        if (bv.isNetscape || bv.isOpera) return this.win.innerHeight;
        if (bv.isMozilla || bv.isFF) return this.doc.body.clientHeight;
        return null;
    },
    getDocumentWidth : function()
    {
        var bv = this.bv;
        if (bv.isOldIE || bv.isOperaN) return this.getDocFrame().scrollWidth;
        if (bv.isMozilla || bv.isFF) return this.doc.body.clientWidth;
        if (bv.isNetscape) return this.doc.width;
        if (bv.isOpera) return this.doc.body.style.pixelWidth;
        return null;
    },
    getDocumentHeight : function()
    {
        var bv = this.bv;
        if (bv.isMozilla || bv.isFF) return this.doc.body.scrollHeight;
        if (bv.isOldIE || bv.isOperaN) return this.getDocFrame().scrollHeight;
        if (bv.isSafari) return this.doc.documentElement.scrollHeight;
        if (bv.isNetscape) return this.doc.height;
        if (bv.isOpera) return this.doc.body.style.pixelHeight;
        return null;
    },
    getScrollX : function()
    {
        var bv = this.bv;
        try {
            if (bv.isOldIE || bv.isOperaN) return this.getDocFrame().scrollLeft;
            if (bv.isNetscape || bv.isOpera) return this.win.pageXOffset;
        } catch(e) {}
        return null;
    },
    getScrollY : function()
    {
        var bv = this.bv;
        try {
            if (bv.isOldIE || bv.isOperaN) return this.getDocFrame().scrollTop;
            if (bv.isNetscape || bv.isOpera) return this.win.pageYOffset;
        } catch(e) {}
        return null;
    },
    createStyle : function(selector, style, indx)
    {
        if (isDefined(this.doc, 'styleSheets')) {
            var ss = this.doc.styleSheets;
            if (!isDefined(indx)) indx = ss.length - 1;
            else if (indx < 0) {
                this.doc.getElementsByTagName('head')[0].appendChild(this.doc.createElement('style'));
                indx = ss.length - 1;
            } else indx = Number(indx);
            if (this.bv.isOldIE) ss[indx].addRule(selector, style, ss[indx].rules.length);
            else {
                for (var i = indx; i >= 0; i--) {
                    try {
                        ss[i].insertRule(selector+'{'+style+'}', ss[i].cssRules.length);
                        break;
                    } catch(e) {
                        if (i == 0) {
                            this._errMsg('notCss', [selector+'{'+style+'}']);
                        }
                    }
                }
            }
        }
        return this;
    },
    loadStyle : function(url)
    {
        var style = this.doc.createElement('link');
        style.rel = 'stylesheet';
        style.type = 'text/css';
        style.href = url;
        this.doc.getElementsByTagName('head')[0].appendChild(style);
        return this;
    },
    getSelection : function()
    {
        if (this.doc.getSelection) return this.doc.getSelection();
        if (this.doc.selection && this.doc.selection.createRange) return this.doc.selection.createRange().text;
        return "";
    },
// .... Additional methods .... \\
    getCookie : function(name)
    {
        if (!this.cookieVal) {
            var rArr;
            this.cookieVal = {};
            var re = /\s*(\w*)\=([^;]*)\;?/g;
            while ((rArr = re.exec(this.doc.cookie))) this.cookieVal[rArr[1]] = unescape(rArr[2]);
        }
        return (name && this.cookieVal[name]) ? this.cookieVal[name] : null;
    },
    setCookie : function(name, val, expires, path, secure)
    {
        var old = this.getCookie(name);
        if (name && old != val) {
            var curCookie = name + "=" + escape(val);
            if (curCookie.length > 4000) this._errMsg("cookLen");
            if (!expires && !path && !secure && this.cookieExtra) curCookie += "; " + this.cookieExtra;
            else {
                if (expires) curCookie += "; expires=" + expires.toGMTString();
                if (path || this.config.DefaultCookiePath) curCookie += "; path=" + (path ? path : this.config.DefaultCookiePath);
                if (this.config.SubdomainCookie) {
                    var rArr = /^(?:www[^.]*\.)?(.*)$/i.exec(this.doc.domain);
                    curCookie += "; domain=." + rArr[1];
                }
                if (secure) curCookie += "; secure";
            }
            this.doc.cookie = curCookie;
            this.cookieVal[name] = val;
        }
        return this;
    },
    deleteCookie : function(name, path)
    {
        if (this.getCookie(name)) {
            this.setCookie(name, '', new Date(1970,1,1,0,0,1), path);
            delete this.cookieVal[name];
        }
        return this;
    },
    getSid : function()
    {
        if (!this.sidVal) {
            var sk = this.config.SessionIdKey;
            if (this.getCookie(sk)) this.sidVal = this.getCookie(sk);
            else {
                var re = new RegExp("\\?.*?" + sk + "\\=([a-zA-Z0-9]+)");
                var rArr = re.exec(this.doc.location);
                if (rArr) this.sidVal = rArr[1];
            }
        }
        return this.sidVal;
    },

// .... Special methods .... \\
    getClosedFunction : function(obj,met,arg)
    {
        if(!arg) arg=[];
        return function(){obj[met].apply(obj,arg);};
    },
    setTimeout : function(time,obj,met,arg)
    {
        return setTimeout(this.getClosedFunction(obj,met,arg), time);
    },
    setInterval : function(time,obj,met,arg)
    {
        return setInterval(this.getClosedFunction(obj,met,arg), time);
    },
    getRandKey : function(ur)
    {
        var k = (new Date()).getTime();
        return isDefined(ur) && !ur ? k : k + '_' + Math.round(Math.random() * 1000);
    },

// .... Private methods .... \\
    setFW : function(wn)
    {
        var frm, nm, i;
        frm = this.win.frames;
        for (i = 0; i < frm.length; i++) if(wn === frm[i]) {
            nm = wn.name;
            if (nm) {
                this.$f(nm).iniWrapper(wn);
            }
            break;
        }
        return this;
    },
    setWW : function(wn)
    {
        var nm, i;
        nm = wn.name;
        if (nm) {
            this.$w(nm).iniWrapper(wn);
        }
        return this;
    },

    _$$ : function(sel, src, one)
    {
        var a, s, r, i;
        r = null;
        if (!sel) {
            this._errMsg("noSel");
        } else if (typeof sel == "string") {
            s = [src && src.elm ? src.elm : (isDefined(src, "nodeType") && src.nodeType == 1 ? src : this.doc)];
            a = sel.split(" ");
            while (a.length > 0 && !a[0]) a.shift();
            for (i in a) {
                if (a[i]) {
                    r = [];
                    while (s.length > 0) {
                        r = r.concat(this._find(a[i], s.shift(), i));
                    }
                    if (r.length == 0) break;
                    s = r;
                }
            }

            if (!r.length) return null;
            if (one)       return getElmWrapper(r[0], this);
            for (i in r) r[i] = getElmWrapper(r[i], this);
            return r;
        } else if (isDefined(sel, "nodeType") && sel.nodeType == 1) {
            r = getElmWrapper(sel, this);
            if (!one) r = [r];
        } else {
            this._errMsg("incorSel", [sel]);
        }
        return r;
    },

    _bcElement : function(eType)
    {
        if (eType == "onload" || eType == "onunload") {
            return this.bv.isOpera ? this.doc : this.win;
        } else {
            var winEvt = ["onresize", "onscroll", "onfocus", "onactivate", "onblur", "onerror", "onafterprint", "onbeforedeactivate", "onbeforeprint", "onbeforeunload", "oncontrolselect", "ondeactivate", "onhelp", "onresizeend", "onresizestart"];
            for (var i in winEvt) if (eType == winEvt[i]) return this.win;
            return this.doc;
        }
    },

    _checkDelay : function(obj, EventType, mName, del)
    {
        var a, i;
        for (i in this._delaySubscr) {
            a = this._delaySubscr[i];
            if (a && a[0] === obj && a[1] == EventType && a[2] == mName) {
                if (del) this._delaySubscr.splice(i, 1);
                return true;
            }
        }
        return false;
    },

    _find : function(sel, src, n)
    {
        var r, a, el, i;
        r = [];
        // 1 - tag, 2 - id, 3 - class, 4 - attribute, 5 - attribute value, 6 - sequence
        a = this._findRE.exec(sel);
        if (!a) {
            this._errMsg("incorSel", [sel]);
            return r;
        }
        if (a[2] && !/\*/.test(a[2]) && n == 0 && src === this.doc) {
            el = src.getElementById(a[2]);
            if (el && (!a[1] || a[1].toUpperCase() == el.nodeName.toUpperCase())) {
                r = [el];
            }
        } else {
            if (a[1]) {
                // By tag name (?and id/class)
                el = src.getElementsByTagName(a[1]);
                for (i = 0; i < el.length; i++) {
                    if (this._checkIdClass(el[i], a)) {
                        r.push(el[i]);
                    }
                }
            } else if (a[2] || a[3] || a[4]) {
                // By id/class/attributes
                this._getElements(src, a, r);
            }
        }
        if (r.length > 0 && a[6]) {
            i = a[6];
            r = i == "first" ? [r[0]] : (i == "last" ? [r[r.length - 1]] : (isDefined(r, i) ? [r[i]] : []));
        }
        return r;
    },
    _checkIdClass : function(e, dt)
    {
        var re, a, i;
        if (dt[2]) {
            // Check by ID
            re = new RegExp("^" + dt[2].replace(/\*/g, "\\w+?") + "$", "i");
            return re.test(e.getAttribute("id"));
        } else if (dt[3]) {
            // Check by Class name
            re = new RegExp("^" + dt[3].replace(/\*/g, "\\w+?") + "$", "i");
            a = e.className.split(" ");
            for (i in a) {
                if (re.test(a[i])) return true;
            }
            return false;
        } else if (dt[4]) {
            // Check by Attribute
            return dt[5] ? e.getAttribute(dt[4]) == dt[5] : e.getAttribute(dt[4]) != "";
        }
        return true;
    },
    _getElements : function(e, dt, r)
    {
        var ch, i;
        ch = e.childNodes;
        for (i = 0; i < ch.length; i++) {
            if (ch[i].nodeType == 1) {
                if (this._checkIdClass(ch[i], dt)) {
                    r.push(ch[i]);
                }
                this._getElements(ch[i], dt, r);
            }
        }
    },
    _getPrefix : function()
    {
        return this.config.SubscriberUse_on ? "on" : "";
    },
    _unloadCtrl : function()
    {
    },
    _followReady : function()
    {
        var follower = {
            p   : this,
            doc : this.doc,
            ie : function()
            {
                if (this.p._isReady) return;
                try {
                    this.doc.documentElement.doScroll("left");
                } catch(e) {
                    this.p.setTimeout(0,this,"ie");
                    return;
                }
                this.isReady(null);
            },
            opera : function(evt)
            {
                if (this.p._isReady) return;
                for (var i = 0; i < this.doc.styleSheets.length; i++) {
                    if (this.doc.styleSheets[i].disabled) {
                        this.p.setTimeout(0, this, "opera", [evt]);
                        return;
                    }
                }
                this.isReady(evt);
            },
            safari : function(n)
            {
            if (this.p._isReady) return;
                if (this.doc.readyState != "loaded" && this.doc.readyState != "complete" ) {
                    this.p.setTimeout(0,this,"safari", [n]);
                    return;
                }
                //if (!n) n = xxx("style, link[rel=stylesheet]").length; // ToDo it
                if (this.doc.styleSheets.length != n) {
                    this.p.setTimeout(0,this,"safari", [n]);
                    return;
                }
                this.isReady(null);
            },
            isReady : function(evt)
            {
                this.p._broadcastReady(evt);
            }
        };
        if (this.doc.addEventListener && (!this.bv.isOpera || (this.bv.isOpera &&  parseFloat(this.bv.ver) >= 9))) {
            // Mozilla / Opera
            this.doc.addEventListener("DOMContentLoaded", this.getClosedFunction(follower, this.bv.isOpera ? "opera" : "isReady"), false);
            return;
        }
        // IE
        if (this.bv.isOldIE && (this.win === top || this.win.opener)) {
            follower.ie();
            return;
        }
        // Safari
        if (this.bv.isSafari) {
            follower.safari();
            return;
        }
        this.addListener(this, this._getPrefix() + "load", "_broadcastReady");
    },
    _broadcastReady : function(evt)
    {
        this._isReady = true;
        this._BBC.broadcastMessage("onready", new evtWrapper(evt, this));
    },
    bv : browserVer
};
implement(winWrapper.prototype, htmlBroadcaster);
with (winWrapper.prototype) {
    implement(winWrapper.prototype, {
        al : addListener,
        aL : addListener,
        rl : removeListener,
        rL : removeListener
    });
}
function linkWrapper()
{
    var w = window;
    var checkW = function(obj)
    {
        return isDefined(obj) && obj !== w && obj._wrapper;
    };
    try {
        if (checkW(w.parent)) w.parent._wrapper.setFW.apply(w.parent._wrapper, w);
        else if (checkW(w.top)) w.top._wrapper.setFW.apply(w.top._wrapper, w);
        else if (checkW(w.opener) && w.name) w.opener._wrapper.setWW.apply(w.opener._wrapper, w);
    } catch (e) {}
    if (!isDefined(w, "_wrapper")) new winWrapper(w);
}


// ======= Basic create function/clases ======== \\
function newClass(obj)
{
    var c = function()
    {
        this.init.apply(this, arguments);
    };
    delete obj.autoInit;
    c.prototype = newObject(obj, false);
    return c;
}

function newObject(obj, init)
{
    var c = function(obj)
    {
        var find, checkImpl, implObj, implExcept, r, k, i, j;
        find = function(arr, v)
        {
            if (!(arr instanceof Array)) {
                return false;
            }
            for (var i in arr) {
                if (arr[i] == v) {
                    return true;
                }
            }
            return false;
        };
        checkImpl = function(t)
        {
            if (isDefined(implObj, k) && !find(implExcept, k)) {
                for (var i in r) {
                    if (find(r[i], k)) return;
                }
                t.parent[k] = implObj[k];
                t[k] = implObj[k];
            }
        };
        r = [];
        r[0] = ["_BBC", "_event_func", "arg"];
        r[1] = ["init"];
        r[2] = ["$w", "$f", "getCookie", "setCookie", "deleteCookie", "getClosedFunction", "setTimeout", "setInterval"];
        r[3] = ["broadcastMessage", "addListener", "removeListener", "alert", "errMsg"];
        r[4] = ["$", "$$"];

        this.parent = implement({}, c.prototype);
        for (i in r[0]) {
            if (isDefined(obj, r[0][i])) {
                delete obj[r[0][i]];
            }
        }
        for (i in r[1]) {
            if (isDefined(obj, r[1][i])) {
                obj["_" + r[1][i]] = obj[r[1][i]];
                delete obj[r[1][i]];
            }
        }
        for (i in r[2]) {
            this[r[2][i]] = this.parent[r[2][i]] = (function(k){return function(){return this.$w0[k].apply(this.$w0, arguments);}})(r[2][i]);
        }
        if (isDefined(obj, "implement")) {
            if (obj.implement instanceof Array) {
                for (i in obj.implement) {
                    implObj    = obj.implement[i].obj;
                    implExcept = obj.implement[i].except;
                    if (isObject(implObj)) {
                        if (isDefined(obj.implement[i], "only")) {
                            for (j in obj.implement[i].only) {
                                k = obj.implement[i].only[j];
                                checkImpl(this);
                            }
                        } else {
                            for (k in implObj) {
                                checkImpl(this);
                            }
                        }
                    }
                }
            }
            delete obj.implement;
        }
        implement(this, obj);
        this._BBC = new basicBroadcaster();
        this._BBC.winWr = this.winWr = this.$w0 = window._wrapper;
        for (i in r[3]) {
            this[r[3][i]] = (function(k){return function(){return this._BBC[k].apply(this._BBC, arguments);}})(r[3][i]);
        }
        if (this.$w0.getLoadWrapper) {
            this.getAjax = this.getLoadWrapper = function(url, method, useCache)
            {
                return new loadWrapper(this.$w0, url, method, useCache)
            }
        }
        this.bv = this.$w0.bv;
    };
    c.prototype = {
        arg : [],
        init : function()
        {
            this.arg = arguments;
            if (isDefined(this, "onready")) this.$w0.setOnready(this, "onready");
            if (isDefined(this, "onload"))  this.$w0.setOnload(this, "onload");
            if (isDefined(this, "_init"))   this._init.apply(this, arguments);
        },
        $  : function(sel, src)
        {
            var ew = this.$w0.$(sel, src);
            if (!ew) return null;
            var f = this._event_func;
            f.prototype = ew;
            return new f(this, false);
        },
        $$ : function(sel, src)
        {
            var ew = this.$w0.$$(sel, src);
            if (!ew) return null;
            var f = this._event_func;
            f.prototype = ew;
            return new f(this, true);
        },
        subscribe : function(sel, EventType, mName, param, useCapture)
        {
            var obj = this.$(sel);
            if (obj) {
                return obj.addListener(this, EventType, mName, param, useCapture);
            }
            return null;
        },
        openPopUp : function(obj, met, uri, name, prop)
        {
            if (!name) name = "wrWin_" + this.$w0.getRandKey();
            open(uri, name, prop);
            var pw = this.$w(name);
            return pw.setOnready(obj, met, {});
        },
        getDelayer : function(time, method, param)
        {
            var f = function(obj, param)
            {
                this.obj   = obj;
                this.timer = [];
                if (isDefined(param)) {
                    this.param = param;
                }
            };
            f.prototype = {
                // Object. Method will be called from this object
                obj     : null,
                // Start method
                m_s     : null,
                // Break method
                m_b     : null,
                // Delayed method
                m_d     : null,
                // Value of delay
                time    : 0,
                // Allow to break on Start-event
                isBreak : true,
                // Array of timers
                timer   : null,
                // Global parameters - will be merged with event-parameters
                param   : null,
                // ---- Interface methods ---- \\
                setTime : function(time, startBreak)
                {
                    this.time = time;
                    this.isBreak = isDefined(startBreak) ? startBreak == true : true;
                },
                addStart : function(elmWr, EventType, param, useCapture)
                {
                    elmWr.addListener(this, EventType, 'onStart', param, useCapture);
                },
                removeStart : function(EventType, elmWr, useCapture)
                {
                    elmWr.removeListener(this, EventType, 'onStart', useCapture);
                },
                addBreak : function(elmWr, EventType, param, useCapture)
                {
                    elmWr.addListener(this, EventType, 'onBreak', param, useCapture);
                },
                removeBreak : function(EventType, elmWr, useCapture)
                {
                    elmWr.removeListener(this, EventType, 'onBreak', useCapture);
                },
                setDelayedMethod : function(method, obj)
                {
                    this._set_m('m_d', method, obj);
                },
                setStartMethod : function(method, obj)
                {
                    this._set_m('m_s', method, obj);
                },
                setBreakMethod : function(method, obj)
                {
                    this._set_m('m_b', method, obj);
                },
                clearTimer : function()
                {
                    while(this.timer.length > 0) {
                        clearTimeout(this.timer.shift());
                    }
                },
                // ---- Event methods ---- \\
                onStart : function(evtWr, param)
                {
                    if (this.isBreak) {
                        this.clearTimer();
                    }
                    this.timer.push(this.obj.$w0.setTimeout(this.time, this, 'onTimer', [evtWr, param]));
                    this._run_m(this.m_s, evtWr, param);
                },
                onBreak : function(evtWr, param)
                {
                    this.clearTimer();
                    this._run_m(this.m_b, evtWr, param);
                },
                onTimer : function(evtWr, param)
                {
                    this._run_m(this.m_d, evtWr, param);
                },
                // ---- Internal methods ---- \\
                _set_m : function(k, method, obj)
                {
                    this[k] = method ? {
                        o : isDefined(obj) ? obj : this.obj,
                        m : method
                    } : null;
                },
                _run_m : function(c, evtWr, param)
                {
                    if (c) {
                        c.o[c.m](evtWr, (this.param ? (isObject(param) ? implement({}, [this.param, param]) : this.param) : param), this);
                    }
                }
            };
            var r = new f(this, param);
            r.setTime(time, true);
            r.setDelayedMethod(method);
            return r;
        },
        _event_func : function(obj, ar)
        {
            var fwr, el, pr, n, e, j;
            fwr = function(brc, lsn, evt)
            {
                return function(met, param) {
                    return brc.addListener(lsn, evt, met ? met : evt, param);
                }
            };
            el = ["click", "contextmenu", "dblclick", "mousedown", "mousemove", "mouseout", "mouseover", "mouseup", "keydown", "keypress", "keyup", "blur", "change", "error", "focus", "load", "reset", "resize", "scroll", "select", "submit", "drag", "drop"];
            pr = this.winWr.config.SubscriberUse_on ? "on" : "";
            for(e in el) {
                n = pr + el[e];
                this[n] = fwr(this, obj, n);
                if (ar) {
                    for (j = 0; j < this.length; j++) {
                        this[j][n] = fwr(this[j], obj, n);
                    }
                }
            }
        }
    };
    var ro = new c(obj);
    if (init || ro.autoInit) {
        ro.init();
    }
    return ro;
}



basicBroadcaster.prototype.config = {
    // Current language
    curLanguage : "ru",
    // Show or not error messages
    DebugMode   : true,
    errMessages_en : {
        "BC_cant_run" : "Can't run method \"{$0}\"!\n\n{$1}",
        "BC_err_in"   : "Error in ",
        "BC_line"     : " line "
    },
    errMessages_ru : {
        "BC_cant_run" : "Не могу выполнить метод \"{$0}\"!\n\n{$1}",
        "BC_err_in"   : "Ошибка в ",
        "BC_line"     : " строка "
    }
};
elmWrapper.prototype.config = winWrapper.prototype.config = implement({
    // Use or not prefix "on" at the event name
    SubscriberUse_on   : true,
    // Replace "table", "table-row", etc. on "block" on Display for IE
    ReplaceDisplay4IE  : true,
    // Return this value after event
    DefaultEventReturn : true,
    // Default Cookie Path
    DefaultCookiePath  : "/",
    // Enable read set cookie in subdomain too
    SubdomainCookie    : true,
    // Session ID key
    SessionIdKey       : "SID",
    errMessages_en : {
        "incorObj"  : 'Incorrect object type in {$0}!',
        "incorEvt"  : 'Incorrect event type!',
        "noSel"     : 'No selector!',
        "incorSel"  : 'Incorrect selector "{$0}"!',
        "elmNotSet" : 'Element for event "{$0}" is not set!',
        "elmNotFnd" : 'Element with ID "{$0}" is not found!',
        "frmNotFnd" : 'Form [{$0}] is not found!',
        "felNotFnd" : 'Form element [{$0}] is not found!',
        "imgNotFnd" : 'Image [{$0}] is not found!',
        "cookLen"   : 'Cookie length exceed 4KB and will be cut!',
        "notCss"    : 'Can\'t create CSS-definition\n{$0}'
    },
    errMessages_ru : {
        "incorObj"  : 'Некорректный тип объекта в {$0}!',
        "incorEvt"  : 'Некорректный тип события!',
        "noSel"     : 'Не указан селектор!',
        "incorSel"  : 'Некорректный селектор "{$0}"!',
        "elmNotSet" : 'Элемент для события "{$0}" не задан!',
        "elmNotFnd" : 'Элемент c ID "{$0}" не найден!',
        "frmNotFnd" : 'Форма [{$0}] не найдена!',
        "felNotFnd" : 'Элемент формы [{$0}] не найден!',
        "imgNotFnd" : 'Рисунок [{$0}] не найден!',
        "cookLen"   : 'Длина куки превысила 4KB and will be cut!',
        "notCss"    : 'Не могу создать CSS-определение:\n{$0}'
    }
}, basicBroadcaster.prototype.config, true);

linkWrapper();