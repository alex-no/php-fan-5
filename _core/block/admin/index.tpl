<noscript id="not_js"><div>System doesn't work when Java Script is disabled.</div></noscript>
<div id="login_part">
<fieldset>
<form action="#" id="login_form" method="post">
    <div><label for="login">Login:</label> <input type="text" value="" name="login" id="login" tabindex="1" /></div>
    <div><label for="password">Password:</label> <input type="password" value="" name="password" id="password" tabindex="2" /></div>
    <div><input type="submit" value="Submit" id="login_submit" tabindex="3" /></div>
</form>
</fieldset>
</div>



<div id="work_part">
    <div id="mainBody">
        <div id="blTop">
            <ul id="blTopMenu">
                <li id="logout"><a href="#">Logout</a></li>
                <li id="selectWkPn">Select Work Panel (below)</li>
            </ul>
            <form action="#" id="conditionForm">conditionForm</form>
        </div>

        <div id="blContent">
            <div id="blMainMenu">
                <div id="vSeparator1"><div id="vSeparator1_" class="line_arrow1">&nbsp;</div></div>
                <div id="blMainMenuContent"></div>
            </div>
            <table>
                <tr>
                    <td id="blMain">
                        <div id="blMainContent">
                            <div id="blMainContent__">
                                <table class="contentData">
                                    <tr><td class="contentDataTop" id="frameLeftTop">
                                    </td></tr>
                                    <tr><td class="contentDataBottom" id="frameLeftBottom">
                                    </td></tr>
                                </table>
                            </div>
                        </div>
                    </td>
                    <td id="vSeparator2" rowspan="2"><div>&nbsp;</div></td>
                    <td id="blAdd">
                        <div id="blAddContent">
                            <div id="blAddSubSel"><div id="closeAdd"><a href="#" class="win_close"></a></div>{* *}<div id="blAddSubSelect"></div></div>
                            <div id="blAddContent__">
                                <table class="contentData">
                                    <tr><td class="contentDataTop" id="frameRightTop">
                                    </td></tr>
                                    <tr><td class="contentDataBottom" id="frameRightBottom">
                                    </td></tr>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="background-color: #B500D6;">
                        <ol class="pager" id="pageListLeft">
                            <li class="pageScroll"><ul>
                                <li><a href="#"><img src="{$oTab->getURI('~/first-pages.gif','img')}" alt="" /></a></li>
                                <li><a href="#"><img src="{$oTab->getURI('~/prev-pages.gif','img')}" alt="" /></a></li>
                                <li><a href="#"><img src="{$oTab->getURI('~/next-pages.gif','img')}" alt="" /></a></li>
                                <li><a href="#"><img src="{$oTab->getURI('~/last-pages.gif','img')}" alt="" /></a></li>
                            </ul></li>
                            <li class="pre_first" id="preFirstLeft">&nbsp;</li>
                            <li class="current"><a href="#">1</a></li>
                        </ol>
                    </td>
                    <td class="background-color: #B500D6;">
                        <ol class="pager" id="pageListRight">
                            <li class="pageScroll"><ul>
                                <li><a href="#"><img src="{$oTab->getURI('~/first-pages.gif','img')}" alt="" /></a></li>
                                <li><a href="#"><img src="{$oTab->getURI('~/prev-pages.gif','img')}" alt="" /></a></li>
                                <li><a href="#"><img src="{$oTab->getURI('~/next-pages.gif','img')}" alt="" /></a></li>
                                <li><a href="#"><img src="{$oTab->getURI('~/last-pages.gif','img')}" alt="" /></a></li>
                            </ul></li>
                            <li class="pre_first" id="preFirstRight">&nbsp;</li>
                            <li class="current"><a href="#">1</a></li>
                        </ol>
                    </td>
                </tr>
            </table>
            {if @$browserType == "normal"}<div id="blClear"></div>{/if}
        </div>
    </div>
    <div id="blBottom">
        <div id="blSubBottom">
            <div id="blButtons"><button id="saveButton">Save</button><button id="cancelButton">Cancel</button></div>
            <div id="blStatusBar">&nbsp;</div>
        </div>
    </div>
</div>
<div id="temporaryLoadBlock"></div>
<div id="vSeparator2_"></div>

<div id="formElements">
{* Upload image *}
    <form action="#" method="post" enctype="multipart/form-data" id="formImageUpload" class="uplForm">
        <div class="modalBgr"></div>
        <div class="modal_win"><fieldset>
            <h3>Upload image</h3>
            <div class="modal_content">
                <div class="modal_file"><span>File:</span><input type="file" name="image" /></div>
                <div class="modal_win_row"><span>Alt. text:</span><input type="text" name="alt_txt" class="text" /></div>
                <div class="modal_win_row"><span>Comment:</span><textarea name="description" rows="3" cols="40" class="text"></textarea></div>
                <div class="buttons"><button type="submit" class="float_left">Ok</button><button type="reset" class="float_right">Cancel</button></div>
            </div>
        </fieldset></div>
    </form>
{* Upload file *}
    <form action="#" method="post" enctype="multipart/form-data" id="formFileUpload" class="uplForm">
        <div class="modalBgr"></div>
        <div class="modal_win"><fieldset>
            <h3>Upload file</h3>
            <div class="modal_content">
                <div class="modal_file"><span>File:</span><input type="file" name="file" /></div>
                <div class="modal_win_row"><span>Comment:</span><textarea name="description" rows="3" cols="40" class="text"></textarea></div>
                <div class="buttons"><button type="submit" class="float_left">Ok</button><button type="reset" class="float_right">Cancel</button></div>
            </div>
        </fieldset></div>
    </form>
{* Upload flash *}
    <form action="#" method="post" enctype="multipart/form-data" id="formFlashUpload" class="uplForm">
        <div class="modalBgr"></div>
        <div class="modal_win"><fieldset>
            <h3>Upload flash</h3>
            <div class="modal_content">
                <div class="modal_file"><span>File:</span><input type="file" name="flash" /></div>
                <div class="modal_win_row"><span>Width:</span><input type="text" name="width" class="text" /></div>
                <div class="modal_win_row"><span>Height:</span><input type="text" name="height" class="text" /></div>
                <div class="modal_win_row"><span>BG-color:</span><input type="text" name="bgcolor" maxlength="6" class="text" /></div>
                <div class="modal_win_row"><span>Comment:</span><textarea name="description" rows="3" cols="40" class="text"></textarea></div>
                <div class="buttons"><button type="submit" class="float_left">Ok</button><button type="reset" class="float_right">Cancel</button></div>
            </div>
        </fieldset></div>
    </form>
{* Preview image *}
    <form action="#" method="get" id="imgPreview" class="modalPreview">
        <div class="modalBgr"></div>
        <div class="modal_win"><fieldset>
            <h3>Preview image</h3>
            <div class="modal_content">
                <div class="imgBigFile"><img src="/file.php?id=0" alt="" /></div>
                <div class="modal_win_row"><span>Alt. text:</span><input type="text" name="alt_txt" class="text" /></div>
                <div class="modal_win_row"><span>Comment:</span><textarea name="description" rows="3" cols="40" class="text"></textarea></div>
                <div class="buttons"><button type="submit" class="float_left">Ok</button><button type="reset" class="float_right">Cancel</button></div>
            </div>
        </fieldset></div>
    </form>
    {@$customBlocks}
</div>

<div id="wysiwygBlock" class="wysiwygForm">
    <div class="modalBgr"></div>
    <div class="modal_win"><fieldset id="wysiwygFieldset"></fieldset></div>
</div>
