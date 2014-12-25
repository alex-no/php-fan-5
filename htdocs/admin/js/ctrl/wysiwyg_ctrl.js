var wysiwygCtrl = newObject({
    blk : null, // wrapper of wysiwyg-block
    fld : null, // wrapper of wysiwyg-fieldset
    frm : null, // wrapper of wysiwyg-form
    cont  : null, // content data 
    elmWr : null, // element (of event) wrapper
    cdt   : null, // current data 

    init : function(mainCtrl)
    {
        var winWr, v, i;
        this.mainCtrl = mainCtrl;
        winWr = this.$w0 = mainCtrl.$w0;
        this.config.editorPath = mainCtrl.baseUrl + this.config.editorPath;
    },

    show : function(cont, elmWr, cdt) {
        this.cont  = cont;
        this.elmWr = elmWr;
        this.cdt   = cdt;
        var conf = this.config;
        
        this.blk = this.$("#wysiwygBlock");
        
        this.fld = this.$("#wysiwygFieldset")
        this.fld.elm.innerHTML = conf.formHTML.replace(/\{fckName\}/g, conf.fckName);
        
        this.frm = this.$(this.fld.elm.firstChild);
        this.frm.elm.elements[conf.fckName].value = elmWr.elm.value;
        this.frm.elm.submit = this.getClosedFunction(this, "save");
        this.frm.elm.lastChild.innerHTML = conf.frameHTML.replace(/\{fckName\}/g, conf.fckName).replace(/\{editorPath\}/g, conf.editorPath).replace(/\{toolBar\}/g, isDefined(cont.param.wysiwyg, cdt.name) ? cont.param.wysiwyg[cdt.name] : conf.defaultToolBar).replace(/\{height\}/g, this.$w0.getDocumentHeight() * 0.85);
        
        this.blk.show();
    },

    save : function() {
        var conf = this.config;
        var cdt  = this.cdt;

        var val = this.frm.elm.elements[conf.fckName].value;

        this.blk.hide();
        this.elmWr.elm.value = val;
        this.cont.setChange(cdt.ei, cdt.key, cdt.name, val, cdt.src)
        this.fld.elm.innerHTML = '';

        // Clear FCK
        FCKeditorAPI = null;
        for(var k in this.$w0.win) {
            if (typeof(k) == "string" && k.substr(0, conf.varPrefix.length) == conf.varPrefix) {
                this.$w0.win[k] = null;
            }
        }
    },
    config : {
        fckName : 'FCKeditor1',
        editorPath : '/fckeditor',
        defaultToolBar : 'Default',
        formHTML  : '<form action="#" method="get"><input type="hidden" id="{fckName}" name="{fckName}" value="" /><input type="hidden" id="{fckName}___Config" value="" /><div></div></form>',
        frameHTML : '<iframe id="{fckName}___Frame" src="{editorPath}/editor/fckeditor.html?InstanceName={fckName}&amp;Toolbar={toolBar}" width="100%" height="{height}" frameborder="0" scrolling="no"></iframe>',
        varPrefix : 'FCK_'
    }
});