<h1 id="data_title">&nbsp;</h1>

<form action="#" method="get" id="content">&nbsp;</form>

<div id="pager_cont"><table><tr id="pager"><td class="current"><a href="#">1</a></td></tr></table></div>

<div id="patterns">
    <div id="pattern_record">
        <div class="rec_param1"><span class="rec_time">_REQ_TIME_</span><span class="rec_type">_REQ_TYPE_</span><h2>_REQ_HEADER_</h2>{if $isDelete}<input type="checkbox" name="del__REQ_ID_" />{/if}<span id="PH_PID_SEL_" class="rec_pid">PID = PH_REQ_PID_</span></div>
        <div class="rec_param2"><span class="rec_method">_REQ_METHOD_</span> <a href="_REQ_HREF_" target="_blank">_REQ_HREF_</a></div>
    </div>
    <div id="pattern_data_singular">
        <div class="dt_block">
            <div class="dt_type">(<span>_TYPE_</span>)</div><div class="dt_value">_VALUE_</div>
        </div>
    </div>
    <div id="pattern_data_multiple">
        <div class="dt_block">
            <div class="dt_type">(<span>_TYPE_</span>)</div><div class="switch" id="ID_SWITCH1"><span>_SWITCH_SIGN_</span></div><div class="dt_list" style="display: none;">_DT_LIST_</div>
        </div>
    </div>
    <div id="pattern_data_row">
        <div class="dt_row">
            <div class="dt_key">_KEY_</div><div class="dt_arrow">=></div>_DATA_
        </div>
    </div>
    <div id="pattern_trace">
        <div class="rec_trace"><div class="tr_word">trace</div><div class="switch" id="ID_SWITCH2"><span>+</span></div><div class="trace_list"></div></div>
    </div>
    <div id="pattern_trace_row1">
        <div class="rec_trace_file"><span class="tr_file">_FILE_PATH_</span>: <span class="tr_line">line _FILE_LINE_</span></div>
    </div>
    <div id="pattern_trace_row2">
        <div class="rec_trace_function"><span class="tr_func">_FUNCTION_NAME_</span>(<span class="tr_arg">_FUNCTION_ARGUMENTS_</span>)</div>
    </div>

    <div id="is_loading" class="is_loading">
        <img src="/__log_viewer/image/is_loading.gif" alt="Wait please" />
    </div>
</div>