/**
 * This file is example of Custom JS of admin-system
 *
 * For using scipt like this do the next:
 *  - add load thit script to start admin file "index.meta.php"
 *    For example:
 *      'externalJS' => array(
 *          'head' => array(
 *              '/custom/custom_example.js',
 *          ),
 *      ),
 *  - add initialisation this script in the data file
 *    For example:
 *      'addParam' => array(
 *          'not_standard' => array(
 *              'field_name' => 'customExample',
 *          ),
 *      ),
 *
 */
var customExample = newClass({
    html : '',
    name : '',

    arg    : null,
    $w0    : null,

    loader : null,

    // Init this script-obbject
    init : function(pc, html, name)
    {
        this.arg = pc.arg; // Arguments of this element (has property: data, ap, ei, ei, etc)
        this.$w0 = pc.$w0; // Window wrapper of curent window

        this.html = html; // HTML code of current template
        this.name = name; // Field name for table
    },

    // Init HTML-code before making html-content of data
    // Usually there is made simple div-tag (like below) where will place another HTML-element in the initHTML-method.
    getHTML : function()
    {
        var id = patternCtrl.getTagId();
        this.setTimeout(0, this, 'initHTML', [id]);
        return '<div id="' + id + '"></div>';
    },
    
    // Init HTML-code after making html-content of data
    initHTML : function(id)
    {
    },

    // Some Event-listener
    onSomeEvent : function(evtWr, data)
    {
    },

    // Some DataLoad-listener (for additional data by loader)
    onDataLoad : function(json, dom, txt)
    {
    },
    // Some DataError-listener (for additional data by loader)
    onError : function()
    {
    },

    // Config data
    config : {
        someConf : 'confVal'
    }
});