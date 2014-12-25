var loginCtrl = newObject({
    errorCnt : 0,
    isLorinRequest : false,
    init : function(mainCtrl)
    {
        var conf, form;
        this.mainCtrl = mainCtrl;
        this.$w0 = mainCtrl.$w0;

        conf = this.config;
        this.loader = this.getLoadWrapper(mainCtrl.baseUrl + conf.url, "post");
        this.loader.addListener(this, "ondataload", "onLogin");
        this.loader.addListener(this, "ondataerror", "onError");
        this.loader.setMode(2);

        form = this.$(conf.idForm);
        form.addListener(this, "onsubmit", "onSubmit");
        this.login = this.$(form.elm.elements[conf.loginFld]);
        this.psswd = this.$(form.elm.elements[conf.psswdFld]);
    },
    somePr : function()
    {

    },

    onSubmit : function(evtWr)
    {
        evtWr.eventDrop();
        if (!this.isLorinRequest) {
            this.isLorinRequest = true;
            this.loader.send({login : this.login.elm.value, psswd : this.psswd.elm.value});
        }
    },

    logout : function()
    {
        this.login.elm.value = "";
        this.psswd.elm.value = "";
        this.$w0.win.location.hash = '';
        this.loader.send({logout : 1});
    },

    onLogin : function(json, dom, txt)
    {
        this.isLorinRequest = false;
        if (txt == "ok") {
            if(!json.logout) {
                this.login.elm.value = "";
                this.psswd.elm.value = "";
                this.errorCnt = 0;
                this.mainCtrl.onLogin();
            }
        } else {
            this.errorCnt++;
            if (this.errorCnt >= this.config.errorLmt) {
                this.mainCtrl.clearData();
            }
            alert(txt ? txt : "Login error");
        }
    },

    onError : function(json, dom, txt)
    {
        this.isLorinRequest = false;
        alert("Connection error. Please try later!");
    },

    config : {
        url : "/login.php",
        idForm   : "#login_form",
        loginFld : "login",
        psswdFld : "password",
        errorLmt : 3
    }
});