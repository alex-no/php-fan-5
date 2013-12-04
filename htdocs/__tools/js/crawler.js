var crawler = newObject({
    hd : null,
    dt : null,
    reqTimer : null,
    zbi : 0,
    data : {},
    onready : function(evtWr)
    {
        this.hd = this.$("#" + this.arg[0]);
        this.dt = this.$("#" + this.arg[1]);

        this.loader  = this.getLoadWrapper(this.config.loader, "get", false);
        //this.loader.setMode(1);
        this.loader.addListener(this, "ondataload", "onDataLoad");
        this.loader.addListener(this, "ondataerror", "onDataError");
        this.loader.send();

        this.reqTimer = this.setInterval(this.config.period, this, "nextRequest");
    },

    nextRequest : function()
    {
        this.loader.send();
    },

    onclick : function(evtWr, dt)
    {
        dt.ref.invDisplay();
    },

    onDataLoad : function (json, dom, txt)
    {
        var urs, aTd, tr, td, ref, i;
        if (txt == "ok") {
            if (this.reqTimer && json.process == "finished") {
                clearInterval(this.reqTimer);
                this.hd.write("Process is finished");
                this.hd.setClass("finished");
            }

            aTd = ["url","status","refers"];
            for (url in json.data) {
                dt = json.data[url];

                if (!isDefined(this.data, url)) {
                    tr = this.$w0.makeElement('tr');
                    tr.setClass("zb" + this.zbi);
                    this.dt.elm.appendChild(tr.elm);
                    for (i in aTd) {
                        td = this.$w0.makeElement('td');
                        td.setClass(aTd[i]);
                        tr.elm.appendChild(td.elm);
                    }
                    tr.elm.firstChild.innerHTML = this.config.dataUrl.replace(/\{url\}/g, url);
                    tr.elm.lastChild.innerHTML  = this.config.dataRef;

                    this.data[url] = {
                        "status"  : tr.getChild(1),
                        "ref_txt" : tr.getChild(-1).getChild(0),
                        "ref_lst" : tr.getChild(-1).getChild(-1)
                    },

                    this.data[url].ref_txt.addListener(this, "onclick").ref = this.data[url].ref_lst;
                    this.zbi = this.zbi ? 0 : 1;
                }

                this.data[url].status.write(dt.status);
                this.data[url].status.setClass("status stat_" + dt.status.toLowerCase().replace(/\s+/g, "_"));

                ref_lst = "";
                for (i in dt.ref) {
                    ref_lst += this.config.referRow.replace(/\{url\}/g, dt.ref[i]);
                }
                this.data[url].ref_lst.elm.innerHTML = ref_lst;
                this.data[url].ref_txt.write("Refers list (" + dt.ref.length + ")");
            }
        } else {
            if (this.reqTimer) {
                clearInterval(this.reqTimer);
                this.reqTimer = null;
            }
            if (confirm(txt + "\nContinue process?")) {
                this.reqTimer = this.setInterval(this.config.period, this, "nextRequest");
            }
        }
    },
    onDataError : function (err)
    {
        alert(err + "\nData error!");
    },
    config : {
        "loader" : "/__tools/crawler_data.php",
        "period" : 5000,
        "dataUrl" : '<a href="{url}" target="_blank">{url}</a>',
        "dataRef" : '<span>Refers list (0)</span><ul></ul>',
        "referRow" : '<li><a href="{url}" target="_blank">{url}</a></li>'
    }
});
