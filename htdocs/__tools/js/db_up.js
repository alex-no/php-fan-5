var db_up = newObject({
    autoInit : true,

    implement : [
        { obj : form_property }
    ],

    footer : null,
    form   : null,
    frame  : null,

    onready : function()
    {
        var conf = this.config;

        this.footer = this.$("#" + conf.footer);
        this.form   = this.$("#" + conf.form_id);
        this.frame  = this.$("#" + conf.frame_id);

        if (this.form) {
            this.form.addListener(this, "onsubmit");
        }
        if (this.frame) {
            this.$w0.addListener(this, "onresize", "frame_resize");
            this.frame_resize();
        }
    },

    onsubmit : function(evtWr)
    {
        evtWr.eventDrop();
        this.frame.elm.src = this.form.elm.action + "?" + this.config.key_field + "=" + this.getFieldValue(this.config.key_field);
    },
    
    frame_resize : function()
    {
        var h = this.$w0.getWindowHeight() - this.footer.getHeight() - this.frame.getAbsTop() - 5;
        this.frame.style.height = (h < 150 ? 150 : h) + "px";
    },
    config : {
        "key_field" : "scenario",
        "footer"   : "footer",
        "form_id"  : "select_scenario",
        "frame_id" : "scenario_processing"
    }
});
