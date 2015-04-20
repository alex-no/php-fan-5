/**include
/js/js-wrapper.js;
*/

/**
 * Load Wrapper - this is addon of window Wrapper for load JSON, HTML or plain-text elements
 * Copyright (C) 2005-2006 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * This version includes three basic wrappers:
 *     add method for Load Wrapper - getLoadWrapper();
 *    loadWrapper - load Wrapper;
 * For further information visit:
 *     http://www.alex.4n.com.ua/js-wrapper/
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version:  03.01.08
 * @modified: 2013-12-13 01:20:00
 */
/**
 * mode:
 *   0 - independant request,
 *   1 - wait before previous request will finished,
 *   2 - kill previous request
 */

// --- Addon Load-wrapper XML --- \\
var loadWrapper_xml = function(loadWr, hk)
{
    this.loadWr = loadWr;
    this.hk = hk;

    this._getXmlReq();
    this.ready = this.xmlReq != null;
};
implement(loadWrapper_xml.prototype, [{
    isAbort : false,
    xmlReq  : null,
    ready   : false,
    dataStr : null,

    /**
     * Send requesr to server
     */
    send : function(isRetry)
    {
        var loadWr, dataStr, xmlReq;

        loadWr  = this.loadWr;
        if (isRetry) {
            this._getXmlReq();
            if (!this.xmlReq) {
                loadWr._errMsg("ingorRec",[loadWr.url]); //ToDo: other message!
            }
        } else {
            this.dataStr = loadWr._urlEncodeData();
        }
        xmlReq  = this.xmlReq;
        dataStr = this.dataStr;

        if (xmlReq.overrideMimeType) {
            xmlReq.overrideMimeType("text/plain; charset=UTF-8");
        }
        try {
            xmlReq.onreadystatechange = loadWr.winWr.getClosedFunction(this, "responce");
            this.isAbort = false;

            if (loadWr.method == "post") {
                xmlReq.open("POST", loadWr.url, true);
                if (isDefined(xmlReq, "setRequestHeader")) xmlReq.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
                xmlReq.send(dataStr);
            } else {
                xmlReq.open("GET", loadWr._reduceQuery() + dataStr, true);
                xmlReq.send(null);
            }
        } catch (e) {
            loadWr._errMsg("ingorRec",[loadWr.url]);
        }
    },

    /**
     * Parce responce from server
     */
    responce : function()
    {
        var loadWr, xmlReq, data, hk;
        loadWr = this.loadWr;
        xmlReq = this.xmlReq;
        if (xmlReq.readyState == 4 && !this.isAbort) {
            if (xmlReq.status == 200) {
                try {
                    eval('data=' + xmlReq.responseText);
                    if (data.length != 4) {
                        loadWr._responseError(this.hk, "JSbroken", [xmlReq.responseText]);
                        return;
                    }
                    hk = data.shift();
                    if (hk != this.hk) {
                        // ToDo: Is it need this check???
                    }
                } catch (e) {
                    loadWr._responseError(this.hk, "JSbroken", [xmlReq.responseText]);
                    return;
                }
                try {
                    data[1] = loadWr.getDOM(data[1]);
                } catch (e) {
                    loadWr._responseError(this.hk, "HTMLbroken", [data[1]]);
                    return;
                }
                loadWr._responseSuccess(this.hk, data);
            } else {
                loadWr._responseError(this.hk, "retrProbl", [xmlReq.status, xmlReq.statusText]);
            }
        }
    },

    /**
     * Abort request
     */
    abort : function()
    {
        this.isAbort = true;
        if (this.xmlReq) {
            this.xmlReq.abort();
            return true;
        }
        return false;
    },

    /**
     * get Xml Request
     */
    _getXmlReq : function()
    {
        this.xmlReq = null;
        if(window.XMLHttpRequest) {
            try {this.xmlReq = new XMLHttpRequest();}
            catch (e){}
        } else {
            try {this.xmlReq = new ActiveXObject('Msxml2.XMLHTTP');}
            catch (e){
                if(!this.xmlReq) try {this.xmlReq = new ActiveXObject('Microsoft.XMLHTTP');}
                catch (e){}
            }
        }
    }
}]);







// --- Addon Load-wrapper JavaScript-tag --- \\
var loadWrapper_js = function(loadWr, hk)
{
    this.loadWr = loadWr;
    this.hk = hk;

    this.ready = true;
};
implement(loadWrapper_js.prototype, [{
    isAbort    : false,
    jsTag      : null,
    ready      : false,
    handlerNum : null,
    srcBak     : null,

    /**
     * Send requesr to server
     */
    send : function(isRetry)
    {
        var loadWr = this.loadWr;
        this.isAbort = false;
        if(!isRetry) {
            this.srcBak = loadWr._reduceQuery() + loadWr._urlEncodeData();
        }
        this.jsTag = loadWr.winWr.doc.createElement("script");
        loadWr.winWr.setTimeout(10, this, "_cstiptSrc")
    },

    /**
     * Parce responce from server
     */
    responce : function(json, html, txt)
    {
        if (!this.isAbort) {
            this.loadWr._responseSuccess(this.hk, [json, this.loadWr.getDOM(html), txt]);
        }
        this.abort();
    },

    /**
     * Abort request
     */
    abort : function()
    {
        var js = this.jsTag;
        this.isAbort = true;
        if (js) {
            js.parentNode.removeChild(js);
            this.jsTag = null;
            return true;
        }
        return false;
    },

    /**
     * Abort request
     */
    _cstiptSrc : function()
    {
        var js = this.jsTag;
        js.language="JavaScript";
        js.setAttribute("format", "text/javascript");
        js.setAttribute("src", this.srcBak);
        this.loadWr._getLoaderTag().appendChild(js);
    }
}]);







// --- Addon Load-wrapper Form --- \\
var loadWrapper_frm = function(loadWr, hk)
{
    this.loadWr = loadWr;
    this.hk = hk;

    this.formTag  = loadWr.formTag;
    this.frameTag = loadWr.frameTag;
    this.ready = this.formTag.elm && this.frameTag.elm;

};
implement(loadWrapper_frm.prototype, [{
    isAbort     : false,
    formTag     : null,
    frameTag    : null,
    isRealForm  : true,
    isRealFrame : true,
    ready       : false,
    handlerNum  : null,
    cdBak       : null,

    /**
     * Send requesr to server
     */
    send : function(isRetry)
    {
        var loadWr, doc, cd, form, elm, fdiv, inp, k, i;
        loadWr = this.loadWr;
        doc = loadWr.winWr.doc;

        form = this.formTag.elm;
        elm  = form.elements;
        fdiv = doc.createElement("div");


        // Prepare and submit form
        this.frameTag.addListener(this, "onerror", "error");

        if (!isRetry) {
            this.cdBak = loadWr._convData(loadWr._sendData);
        }
        cd = this.cdBak;

        for (k in cd) {
            if (isDefined(elm, k)) {
               elm[k].value = cd[k];
            } else {
                 if (loadWr.bv.isIE) {
                    fdiv.innerHTML += '<input type="hidden" name="' + k + '" value="' + cd[k] + '" />';
                } else {
                    inp = doc.createElement("input");
                    inp.setAttribute("type", "hidden");
                    inp.setAttribute("name", k);
                    inp.setAttribute("value", cd[k]);
                    fdiv.appendChild(inp);
                }
            }
        }
        form.appendChild(fdiv);
        this.frameTag.elm.setAttribute("src", "");
        this.isAbort = false;
        form.submit();

        // Clear form
        if (loadWr.fullClear) {
            var list = form.childNodes;
            for (i = list.length - 1; i >= 0 ; i--) {
                form.removeChild(list[i]);
            }
        } else {
            form.removeChild(fdiv);
        }
    },

    /**
     * Parce responce from server
     */
    responce : function(json, srcDoc, txt)
    {
        if (!this.isAbort) {
            this.loadWr._responseSuccess(this.hk, [json, srcDoc.body.firstChild && srcDoc.body.firstChild.nodeType == 1 ? this.loadWr.getDOM(srcDoc.body.firstChild.innerHTML) : "", txt]);
            this.abort();
        }
    },

    /**
     * Parce responce from server
     */
    error : function()
    {
        if (!this.isAbort) {
            this.loadWr._responseError(this.hk, "frmNotLoad");
            this.abort();
        }
    },

    /**
     * Abort request
     */
    abort : function()
    {
        this.isAbort = true;
        if (this.frameTag.elm.contentWindow) {
            this.frameTag.elm.setAttribute("src", "about:blank");
            return true;
        }
        return false;
    }
}]);







// --- Addon Load-wrapper Image --- \\
var loadWrapper_img = function(loadWr, hk)
{
    this.loadWr = loadWr;
    this.hk = hk;

    this.ready = true;
};
implement(loadWrapper_img.prototype, [{
    isAbort : false,
    ready   : false,
    imgObj  : null,
    srcBak  : null,

    /**
     * Send requesr to server
     */
    send : function(isRetry)
    {
        var loadWr = this.loadWr;
        if(!isRetry) {
            this.srcBak = loadWr._reduceQuery() + loadWr._urlEncodeData();
        }

        this.isAbort = false;
        this.imgObj = new Image();
        this.imgObj.onload  = loadWr.winWr.getClosedFunction(this, "success");
        this.imgObj.onerror = loadWr.winWr.getClosedFunction(this, "error");
        this.imgObj.src = this.srcBak;
    },

    /**
     * Parce responce from server
     */
    success : function()
    {
        if (!this.isAbort) {
            this.loadWr._responseSuccess(this.hk, [null, null, null]);
        }
        this.abort();
    },

    /**
     * Parce responce from server
     */
    error : function()
    {
        if (!this.isAbort) {
            this.loadWr._responseError(this.hk, "imgNotLoad");
        }
        this.abort();
    },

    /**
     * Abort request
     */
    abort : function()
    {
        delete this.imgObj;
        this.isAbort = true;
        return true;
    }
}]);





// --- Handler of script/form transports --- \\
var ldWrHandler = function(num, json, html, txt)
{
    loadWrapper.prototype.__ldWrHandler(num, json, html, txt);
};

// =========== Main Load-wrapper ========== \\
var loadWrapper = function(winWr, url, method, useCache)
{

    // Checking parameter errors
    if(!winWr || !(winWr instanceof winWrapper)) {
        this._errMsg("wwNotSet");
        return;
    }

    // Set basic loader pareameters
    this.winWr     = winWr;
    this.method    = method && method.toLowerCase() == "post" ? "post"  : "get";
    this.url       = isDefined(url) ? url : null;
    this.useCache  = this.method == "get" && (!isDefined(useCache) || useCache);

    if (!winWr.getCookie(winWr.config.SessionIdKey)) {
        this.sidVal = winWr.getSid();
    }

    this._sendStack = [];
    this._sendData  = {};
    this._listeners = {};
};
implement(loadWrapper.prototype, [basicBroadcaster.prototype, {
    winWr       : null,
    method      : "get",
    url         : "",
    sidVal      : null,
    useCache    : true,
    transport   : "xml",
    mode        : 0,

    formTag     : null,
    frameTag    : null,
    isRealForm  : false,
    isRealFrame : false,
    fullClear   : true,

    _loaderTag  : null,
    _curKey     : 0,
    _sendData   : null,
    _listeners  : null,
    _sendStack  : null,
    _waiterTag  : null,

    __conTimer  : {},
    __cache     : {},
    __handlers  : {
        key : 0,
        obj : {}
    },



    /**
     * Set Transport as JavaScript
     */
    setTransportJS : function()
    {
        if (this.method == "post") {
            this._errMsg("jsPost");
            return;
        }
        this.transport = "js";
    },

    /**
     * Set Transport as Form
     */
    setTransportForm : function(formWr, frameWr, fullClear, frameNearForm)
    {
        var winWr, frameId;
        winWr = this.winWr;

        if (isDefined(frameWr) && frameWr) {
            if (!frameWr.getAttribute("id")) {
                frameId = frameWr.getAttribute("name");
                frameWr.elm.setAttribute("id", frameId);
            }
            this.isRealFrame = true;
        } else {
            if (!this.frameTag) {
                frameId = "fr_" + this.getRandKey();
                var div = winWr.makeElement('div');
                div.hide();
                div.elm.innerHTML = '<iframe src="about:blank" name="' + frameId + '" id="' + frameId + '"></iframe>';
                frameWr = div.getChildren()[0];
                (formWr && frameNearForm && formWr.elm.parentNode ? formWr.elm.parentNode : this._getLoaderTag()).appendChild(div.elm);
            } else {
                frameWr = this.frameTag;
            }
            this.isRealFrame = false;
        }
        this.frameTag = frameWr;

        if (isDefined(formWr) && formWr) {
            this.method = formWr.elm.getAttribute("method").toLowerCase();
            this.isRealForm = true;
        } else {
            if (!this.formTag) {
                formWr = winWr.makeElement('form', {"method" : this.method});
                this._getLoaderTag().appendChild(formWr.elm);
            } else {
                formWr = this.formTag;
            }
            this.isRealForm = false;
        }
        formWr.elm.setAttribute("action", this.url);
        formWr.elm.setAttribute("target", frameId ? frameId : frameWr.elm.getAttribute("id"));
        this.formTag = formWr;

        this.fullClear = typeof(fullClear) == "boolean" ? fullClear : !this.isRealForm;

        this.transport = "frm";
        if (!this.mode) {
            this.mode = 1;
        }
    },

    /**
     * Set Transport as JavaScript
     */
    setTransportImage : function()
    {
        if (this.method == "post") {
            this._errMsg("imgPost");
            return;
        }
        this.useCache  = false;
        this.transport = "img";
    },

    /**
     * Set send mode
     * @param array mode
     */
    setMode : function(mode)
    {
        if (mode > 2 || mode < 0) {
            this._errMsg("unkMode", [mode]);
            return;
        }
        this.mode = this.transport == "frm" && !mode ? 1 : mode;
    },

    /**
     * Set URL
     * @param string url
     */
    setUrl : function(url)
    {
        this.url = url;
        if (this.transport == "frm" && this.formTag) {
            this.formTag.elm.setAttribute("action", url);
        }
    },

    /**
     * Set data for send
     * @param array data
     */
    setSendData : function(data)
    {
        if (data || !this._sendData[this.config.dataKey]) {
            this._setServiceKey(this.config.dataKey, (isDefined(data) ? data : null));
        }
    },

    /**
     * Set gif path to file
     * default path in config
     */
    setWaiter : function(path)
    {
        if (!isDefined(path)) {
            path = this.config.waiterGif;
        }
        this._waiterTag = this.winWr.makeElement('table', {'style' : this.config.waiterStyle}, null, false);
        var cell = this._waiterTag.insertRow(0).insertCell(0);
        cell.style.textAlign = 'center';
        cell.appendChild(this.winWr.makeElement('img', {'src' : path}, null, false));
    },

    /**
     * Send request to server
     */
    send : function(data)
    {
        this._showWaiter();
        var t, hk, reqObj;
        t = this;

        t.setSendData(data);
        if (t.useCache) {
            var url = t._reduceQuery() + t._urlEncodeData(this._sendData[this.config.dataKey]);
            if (isDefined(t.__cache, url)) {
                this.broadcastMessage.apply(this, t.__cache[url]);
                return null;
            }
        }
        if (t.mode == 1 && t._curKey) {
            t._sendStack.push([t.url, t._sendData]);
            return null;
        } else if (t.mode == 2 && t._curKey) {
            t.__handlers.obj[t._curKey].abort();
            t._restartTimer(t._curKey);
        }

        t._curKey = t.__handlers.key = hk = t.__handlers.key + 1;
        // ToDo: check many simultaneous requests
        for (var i=0; i<2; i++) {
            switch (t.transport) {
            case "xml":
                reqObj = new loadWrapper_xml(t, hk);
                break;
            case "js":
                reqObj = new loadWrapper_js(t, hk);
                break;
            case "frm":
                reqObj = new loadWrapper_frm(t, hk);
                break;
            case "img":
                reqObj = new loadWrapper_img(t, hk);
                break;
            default:
                reqObj = null;
            }

            if (reqObj && reqObj.ready) {
                break;
            } else if (t.transport != "xml") {
                t._errMsg("objNotCrtd");
                return null;
            }

            if (t.method == "get" ) {
                t.setTransportJS();
            } else {
                t.setTransportForm();
            }
        }
        if (t.useCache) {
            reqObj.cacheUrl = url;
        }

        if (t.sidVal) {
            t._setServiceKey(t.winWr.config.SessionIdKey, t.sidVal);
        }
        t._setServiceKey(t.config.controlKey, t.transport + "-" + hk + (t.useCache ? "" : "-" + this.getRandKey()));

        t.__handlers.obj[hk] = reqObj;
        reqObj.send();

        t._sendData = {};

        t._restartTimer(hk, true);
        return hk;
    },

    /**
     * Abort of data send
     */
    abort : function (hk, noFinish)
    {
        var r = true;
        var t = this;
        if(!hk) {
            if (!t.mode) {
                t._errMsg("noKeyMthd", ["abort"]);
                return r;
            }
            hk = t._curKey;
        }
        if (hk && t.__handlers.obj[hk]) {
            r = t.__handlers.obj[hk].abort(noFinish);
            if(!noFinish && r) t._responseFinish(hk);
        }
        return r;
    },

    /**
     * Retry of data send
     */
    retry : function (hk)
    {
        var t = this;
        if(!hk) {
            if (!t.mode) {
                t._errMsg("noKeyMthd", ["retry"]);
                return;
            }
            hk = t._curKey;
        }
        var req;
        if (hk && (req = t.__handlers.obj[hk])) {
            if(req.abort(true)) {
                req.send(true);
                t._restartTimer(hk, true);
            }
        } else {
            t._responseFinish(hk);
        }
    },


    /**
     * Get DOM-object by HTML-sourse
     */
    getDOM : function (html)
    {
        var div = this.winWr.doc.createElement('div');
        div.innerHTML = html;
        return div;
    },


    /**
     * Get Random Key
     */
    getRandKey : function()
    {
        var dt = new Date();
        return dt.getTime()+ '_' + Math.round(Math.random() * 1000);
    },

    /**
     * Get loader tag
     */
    _getLoaderTag : function ()
    {
        if(!this._loaderTag) {
            var doc = this.winWr.doc
            if (doc) {
                this._loaderTag = this.winWr.makeElement('div');
                this._loaderTag.setDisplay(false);
                doc.body.appendChild(this._loaderTag.elm);
            } else {
                this._errMsg("notBody");
            }
        }
        return this._loaderTag.elm;
    },

    /**
     * Load timeout
     */
    _loadTimeout : function (hk)
    {
        if(!this.abort(hk, true)) {
            return;
        }
        if (this.__handlers.obj[hk] && confirm(this.config["alertMessages_" + this.config.curLanguage].retryReq)) {
            this.retry(hk);
        } else {
            this.abort(hk);
            if (this.config.broadcastTmErr) {
                this._responseError(hk, "loadTimeout");
            }
        }
    },

    /**
     * Broadcast success message when response is parsed
     * @param object data - array with 3 elements [json, DOM, text]
     */
    _responseSuccess : function (hk, data)
    {
        var t = this;
        if (isDefined(data, 0) && isObject(data[0]) && isDefined(data[0], "_error")) {
            t._responseFinish(hk);
            if(t.config.interceptErr) {
                if(data[2]) alert(data[2]);
            } else {
                t.broadcastMessage.apply(this, ["ondataerror", data[2]]);
            }
            return;
        }

        if (t.useCache) {
            t.__cache[t.__handlers.obj[hk].cacheUrl] = data;
        }
        t._responseFinish(hk);
        data.unshift("ondataload");
        t.broadcastMessage.apply(t, data);
    },
    /**
     * Broadcast error message when response is not finished
     * @param string errMsg - error message
     */
    _responseError : function (hk, msgKey, msgDt)
    {
        this._responseFinish(hk);
        var msg = this.config["alertMessages_" + this.config.curLanguage];
        if (isDefined(msg, msgKey)) {
            this.broadcastMessage.apply(this, ["ondataerror", msg[msgKey]]);
        }
        this._errMsg(msgKey, msgDt);
        this._hideWaiter();
    },

    /**
     * Finish procedures after request
     */
    _responseFinish : function (hk)
    {
        delete this.__handlers.obj[hk];
        this._curKey = 0;
        if (this._sendStack.length) {
            data = this._sendStack.shift();
            this.setUrl(data[0]);
            this.send(data[1]);
        }
        this._restartTimer(hk, false);
        this._hideWaiter();
    },
    /**
     * Restart timer
     */
    _restartTimer : function (hk, start)
    {
        if (this.__conTimer[hk]) {
            this.winWr.win.clearTimeout(this.__conTimer[hk]);
        }
        if (start) {
            this.__conTimer[hk] = this.winWr.setTimeout(this.config.connectTimeout, this, "_loadTimeout", [hk]);
        } else {
            delete this.__conTimer[hk];
        }
    },

    /**
     * Set service key
     */
    _setServiceKey : function(key, val)
    {
        this._sendData[key] = val;
    },

    /**
     * Encode Data
     */
    _urlEncodeData : function(data)
    {
        var query, cd, k;
        if(!isDefined(data)) data = this._sendData;
        if(!data) return null;
        query = [];
        cd = this._convData(data);
        for (k in cd) {
            query.push(encodeURIComponent(k) + "=" + encodeURIComponent(cd[k]));
        }
        return query.length ? query.join('&') : "";
    },

    /**
     * Encode Data
     */
    _convData : function(val, key)
    {
        if (val===null) {
            return null;
        }
        if (!isDefined(key)) {
            key = "";
            this.tmpRet = {};
        }
        if (isObject(val)) {
            for (var k in val) {
                this._convData(val[k], (key ? key + "[" + k + "]" : k));
            }
        } else {
            if(val===true){
                val=1;
            }
            if(val===false){
                val="";
            }
            this.tmpRet[key] = val;
        }
        return this.tmpRet;
    },

    /**
     * Encode Data
     */
    _reduceQuery : function()
    {
        var url, cd, re, rArr, k, tmp;
        url = this.url
        if(url.search(/\?/) >= 0) {
            cd = this._convData(this._sendData);
            for(k in cd) {
                re = new RegExp("\\?(?:.*?\\&)?(" + k + "\\=[^&]*\\&?)", "i");
                rArr = re.exec(url);
                if(rArr) url = url.replace(rArr[1], "");
            }
            tmp = url.substring(url.length-1, url.length);
            return tmp == "&" && tmp == "?" ? url : url += "&";
        }
        return url + "?";
    },

    /**
     * Show ajax loader
     */
    _showWaiter : function()
    {
        if (this._waiterTag) {
            this.winWr.doc.body.appendChild(this._waiterTag);
        }
    },

    /**
     * Hide ajax loader
     */
    _hideWaiter : function()
    {
        if (this._waiterTag) {
            this.winWr.doc.body.removeChild(this._waiterTag);
        }
    },

    /**
     * Integrate loadWrapper to winWrapper
     */
    __init : function()
    {
        if(!window.winWrapper || !winWrapper.prototype) {
            this._errMsg('no_wrapper');
            return false;
        } else {
            winWrapper.prototype.getAjax = winWrapper.prototype.getLoadWrapper = function (url, method, useCache)
            {
                return new loadWrapper(this, url, method, useCache)
            }
            return true;
        }
    },

    /**
     * Integrate loadWrapper to winWrapper
     */
    __ldWrHandler : function(num, json, html, txt)
    {
        if (this.__handlers.obj[num]) this.__handlers.obj[num].responce(json, html, txt);
    },

    bv : browserVer
}]);

implement(loadWrapper.prototype.config, {
    'connectTimeout' : 21000,
    'handlerLimit'   : 100,
    'broadcastTmErr' : true,
    'interceptErr'   : false,
    'dataKey'        : 'dl_data',
    'controlKey'     : 'dl_ctrl',
    'waiterStyle'    : 'opacity: 0.85; background-color: #FFFFFF; position: absolute; top: 0px; left: 0px; z-index:100; width: 100%; height: 100%;',
    'waiterGif'      : '/image/loader/ajax-loader.gif',
    alertMessages_en  : {
        'JSbroken'    : 'JavaScript code is broken.',
        'HTMLbroken'  : 'HTML-code is broken.',
        'retrProbl'   : 'Load data error.',
        'frmNotLoad'  : 'Form data isn\'t loaded!',
        'imgNotLoad'  : 'Image isn\'t loaded!',
        'loadTimeout' : null,
        'retryReq'    : 'Time for get data from server is expired!\nWould you like to retry this request again?'
    },
    alertMessages_ru  : {
        'JSbroken'    : 'JavaScript код некорректный.',
        'HTMLbroken'  : 'HTML-код некорректный.',
        'retrProbl'   : 'Проблемы с получением данных:.',
        'frmNotLoad'  : 'Данные формы не были загружены!',
        'imgNotLoad'  : 'Рисунок не загружен!',
        'loadTimeout' : null,
        'retryReq'    : 'Время для получения данных от сервера истекло!\nХотели-бы Вы повторить запрос еще раз?'
    },
    errMessages_en : {
        'ingorRec'   : 'Incorrect request!\n{$0}',
        'JSbroken'   : 'JavaScript code is broken!\n\n{$0}',
        'HTMLbroken' : 'HTML-code is broken!\n\n{$0}',
        'retrProbl'  : 'There was a problem of retrieving the XML data:\nError {$0}\n{$1}',
        'frmNotLoad' : 'Form data isn\'t loaded!',
        'imgNotLoad' : 'Image isn\'t loaded!',
        'wwNotSet'   : 'Error!\nWindow Wrapper doesn\'t set!',
        'jsPost'     : 'JS-transport isn\'t be use for POST-method!',
        'imgPost'    : 'Image-transport isn\'t be use for POST-method!',
        'unkMode'    : 'Unknown mode - {$0}!',
        'objNotCrtd' : 'Error!\nObject for send data wasn\'t be created!',
        'noKeyMthd'  : 'Do not use {$0}-method in mode "0" without key.',
        'handLimit'  : 'Top out handler limit!',
        'notBody'    : 'Can\'t create new tag without "body"!',
        'no_wrapper' : '"Load-wrapper" isn\'t be used without "window Wrapper"!'
    },
    errMessages_ru : {
        'ingorRec'   : 'Некорректный запрос!\n{$0}',
        'JSbroken'   : 'JavaScript код некорректный!\n\n{$0}',
        'HTMLbroken' : 'HTML-код некорректный!\n\n{$0}',
        'retrProbl'  : 'Проблемы с получением XML-данных:\nОшибка {$0}\n{$1}',
        'wwNotSet'   : 'Ошибка!\n"Window Wrapper" не установлен!',
        'jsPost'     : 'JS-транспорт не может использоваться для метода POST!',
        'frmNotLoad' : 'Данные формы не были загружены!',
        'imgNotLoad' : 'Рисунок не загружен!',
        'imgPost'    : 'Image-транспорт не может использоваться для метода POST!',
        'unkMode'    : 'Неизвестный режим - {$0}!',
        'objNotCrtd' : 'Ошибка!\nОбъект для передачи данных не был создан!',
        'noKeyMthd'  : 'Не используйте метод {$0} без ключа в режиме "0".',
        'handLimit'  : 'Превышен лимит хендлеров!',
        'notBody'    : 'Не могу создать новый тэг без "body"!',
        'no_wrapper' : '"Load-wrapper" не может использоваться без "window Wrapper"!'
    }
});

if(!loadWrapper.prototype.__init()) {loadWrapper = null;}