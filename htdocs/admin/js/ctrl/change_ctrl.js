var changeCtrl = newObject({
    chContent : {},
    butBl : null,
    qtt : 0,

    init : function(mainCtrl)
    {
        this.mainCtrl = mainCtrl;
        this.$w0 = mainCtrl.$w0;

        var id = this.config.id;
        this.$(id.submit).addListener(this, "onclick", "onSubmit");
        this.$(id.cancel).addListener(this, "onclick", "onCancel");

        this.butBl = this.$(id.butBl);
    },

    addCnt : function(nm)
    {
        if (!isDefined(this.chContent, nm)) {
            this.chContent[nm] = 1;
            this.qtt++;
            if (this.qtt == 1) {
                this.butBl.show();
            }
        }
    },

    delCnt : function(nm)
    {
        if (isDefined(this.chContent, nm)) {
            delete this.chContent[nm];
            this.qtt--;
            if (this.qtt == 0) {
                this.butBl.hide();
            }
        }
    },

    onSubmit : function(evtWr)
    {
        evtWr.eventDrop();
        for (var nm in this.chContent) {
            this.mainCtrl.content[nm].load(true);
        }
    },
    onCancel : function(evtWr)
    {
        evtWr.eventDrop();
        for (var nm in this.chContent) {
            this.mainCtrl.content[nm].cancelChange();
        }
    },

    config : {
        id : {'butBl' : '#blButtons', 'submit' : '#saveButton', 'cancel' : '#cancelButton'}
    }
});