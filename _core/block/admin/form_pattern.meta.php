<?php
/**
 * Form pattern meta file
 *
 * This file is part PHP-FAN (php-framework of Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.002 (17.12.2013)
 */
return array(
    'own' => array(
        'json' => array (
            'patterns' => array(
                'label' => array(
                    'lbl_span_req'   => '<span class="label require">{LABEL}<b>*</b>:</span>',
                    'lbl_span'       => '<span class="label">{LABEL}:</span>',
                    'lbl_simple_req' => '<span>{LABEL}<b>*</b></span>',
                    'lbl_simple'     => '<span>{LABEL}</span>',
                ),
                'tbl_head' => array(
                    'tbl_order'      => '<a href="#" class="order_{ORDER_DIR}" id="{EVENT_id}">{LABEL}</a>',
                ),
                'id' => array(
                    'id'       => '<div class="rowId">{VALUE}</div>',
                    'id_title' => '<div class="rowId" title="{VALUE}">{VALUE}</div>',
                ),
                'not_edit' => array(
                    'not_edit'       => '<span class="notEdit">{VALUE}</span>',
                    'not_edit_right' => '<span class="notEdit right">{VALUE}</span>',
                    'not_edit_pre'   => '<pre class="notEdit">{VALUE}</pre>',
                    'not_wrap'       => '<span class="notEdit notWrap">{VALUE}</span>',
                    'email'          => '<a href="mailto:{VALUE}" class="notEdit notWrap">{VALUE}</a>',
                    'link'           => '<a href="{VALUE}" class="notEdit notWrap" target="_blank">{VALUE}</a>',
                    'no_tag'         => '{VALUE}',
                ),
                'input' => array(
                    'text'       => '<input type="text" value="{VALUE}" id="{EVENT_id}" class="input1" />',
                    'text_right' => '<input type="text" value="{VALUE}" id="{EVENT_id}" class="input1 right" />',
                    'text_empty' => '<input type="text" value="" id="{EVENT_id}" class="input1" />',
                    'text_short' => '<input type="text" value="{VALUE}" id="{EVENT_id}" class="input1 shortText" />',
                    'password'   => '<input type="password" value="" id="{EVENT_id}" class="input1" />',
                    'textarea'   => '<textarea rows="5" cols="60" id="{EVENT_id}" class="textarea1">{VALUE}</textarea>',
                ),
                'wysiwyg' => array(
                    'wysiwyg' => '<textarea rows="5" cols="60" id="{EVENT_id}" class="textarea2">{VALUE}</textarea>',
                ),
                'date' => array(
                    'date_not_edit' => '<span class="notEdit">{VALUE}</span>',
                    'date_simple'   => '<input type="text" value="{VALUE}" id="{EVENT_id}" class="input1" />',
                    'date_clndr'    => '<span class="inp_date_clndr"><input type="text" value="{VALUE}" id="{EVENT_id}" class="input1" /><img src="/image/extra/calendar.gif" alt="Calendar" title="Click Here for set date" class="calendar_ini" id="{IMG_ID}" /></span>',
                ),
                'date_time' => array(
                    'date_time_not_edit' => '<span class="notEdit">{VALUE}</span>',
                    'date_time_simple'   => '<input type="text" value="{VALUE}" id="{EVENT_id}" class="input1" />',
                ),
                'chk_rad' => array(
                    'checkbox' => '<input type="checkbox" value="{VALUE}"{CHECKED} id="{EVENT_id}" class="inpChkBx1" />',
                    'radio'    => '<input type="radio" name="{NAME}" value="{VALUE}"{CHECKED} id="{EVENT_id}" class="inpRadio1" />',
                ),
                'inp_gr' => array(
                    'checkbox_group'    => '<span class="inp_gr">[<input type="checkbox" value="{VALUE}"{CHECKED} id="{EVENT_id}" class="inpChkBx2" /><label for="{EVENT_id}">- {TEXT} </label> ]</span>',
                    'checkbox_group_ml' => '<span class="inp_gr multiLine">[<div><input type="checkbox" value="{VALUE}"{CHECKED} id="{EVENT_id}" class="inpChkBx2" /><label for="{EVENT_id}">- {TEXT}</label></div>]</span>',
                    'radio_group'       => '<span class="inp_gr">[<input type="radio" name="{NAME}" value="{VALUE}"{CHECKED} id="{EVENT_id}" class="inpRadio2" /><label for="{EVENT_id}">- {TEXT} </label> ]</span>',
                    'radio_group_ml'    => '<span class="inp_gr multiLine">[<div><input type="radio" name="{NAME}" value="{VALUE}"{CHECKED} id="{EVENT_id}" class="inpRadio2" /><label for="{EVENT_id}">- {TEXT}</label></div>]</span>',
                ),
                'select' => array(
                    'select'        => '<select id="{EVENT_id}" class="select1">[<option value="{VALUE}"{SELECTED}>{TEXT}</option>]</select>',
                    'select_short'  => '<select id="{EVENT_id}" class="shortSelect">[<option value="{VALUE}"{SELECTED}>{TEXT}</option>]</select>',
                    'select_multi'  => '<select multiple="multiple" id="{EVENT_id}">[<option value="{VALUE}"{SELECTED}>{TEXT}</option>]</select>',
                    'select_hidden' => '<div class="sel_hidden"><div id="{EVENT_id1}">{VALUE}</div><select class="select1" id="{EVENT_id2}">[<option value="{VALUE}"{SELECTED}>{TEXT}</option>]</select></div>',
                    'replace_value' => '<div class="notEdit">{VALUE}</div>',
                ),
                'select_optgroup' => array(
                    'select_optgroup' => '<select id="{EVENT_id}" class="select1 select_optgroup">[[<optgroup label="{LABEL}">[<option value="{VALUE}"{SELECTED}>{TEXT}</option>]</optgroup>]]</select>',
                ),
                'select_dependent' => array(
                    'select_dependent_each'    => '[[<span>{LABEL}</span> <select id="{EVENT_id1}" class="select1">[<option value="{VALUE}"{SELECTED}>{TEXT}</option>]</select>]]',
                    'select_dependent_each_ml' => '<div class="sel_dep_ml">[[<div class="sel_dep_rw"><span class="sel_dep_lb">{LABEL}</span><select id="{EVENT_id1}" class="select1">[<option value="{VALUE}"{SELECTED}>{TEXT}</option>]</select></div>]]</div>',
                    'select_dependent_last'    => '[[<span>{LABEL}</span> <select id="{EVENT_id2}" class="select1">[<option value="{VALUE}"{SELECTED}>{TEXT}</option>]</select>]]',
                    'select_dependent_last_ml' => '<div class="sel_dep_ml">[[<div class="sel_dep_rw"><span class="sel_dep_lb">{LABEL}</span><select id="{EVENT_id2}" class="select1">[<option value="{VALUE}"{SELECTED}>{TEXT}</option>]</select></div>]]</div>',
                ),
                'image' => array(
                    'image_one_simple'  => '<div class="adm_img"><a href="#" title="Preview image" id="{EVENT_id1}" class="preview">\\xA0</a><a href="#" title="Delete image" id="{EVENT_id2}" class="del_img">\\xA0</a><a href="#" title="Upload image" id="{EVENT_id3}" class="upload">\\xA0</a></div>',
                    'image_one_nail'    => '<div class="adm_img"><img src="/adm_nail.php?id={VALUE}&amp;con={CONNECTION}&amp;rand={RAND}" id="{EVENT_id5}" class="adm_nail" /><div class="adm_img_but"><a href="#" title="Preview image" id="{EVENT_id1}" class="preview">\\xA0</a><a href="#" title="Delete image" id="{EVENT_id2}" class="del_img">\\xA0</a><a href="#" title="Upload image" id="{EVENT_id3}" class="upload">\\xA0</a></div></div>',
                    'image_line_simple' => '<div class="adm_img_ln">[<div class="adm_img"><span class="img_num img_num_s{NUM_show}"><a href="#img-{IMG_code}" id="{EVENT_id4}">img-{IMG_num}</a></span><a href="#" title="Preview image" id="{EVENT_id1}" class="preview">\\xA0</a><a href="#" title="Delete image" id="{EVENT_id2}" class="del_img">\\xA0</a><a href="#" title="Upload image" id="{EVENT_id3}" class="upload">\\xA0</a></div>]</div>',
                    'image_line_nail'   => '<div class="adm_img_ln">[<div class="adm_img"><div class="img_num img_num_s{NUM_show}"><a href="#img-{IMG_code}" id="{EVENT_id4}">img-{IMG_num}</a></div><img src="/adm_nail.php?id={VALUE}&amp;con={CONNECTION}&amp;rand={RAND}" id="{EVENT_id5}" class="adm_nail" /><div class="adm_img_but"><a href="#" title="Preview image" id="{EVENT_id1}" class="preview">\\xA0</a><a href="#" title="Delete image" id="{EVENT_id2}" class="del_img">\\xA0</a><a href="#" title="Upload image" id="{EVENT_id3}" class="upload">\\xA0</a></div></div>]</div>',
                ),
                'flash' => array(
                    'flash_upload_1' => '<div class="adm_flash"><a href="/file.php?id={VALUE}" target="_blank" style="display:{STYLE_DISPLAY}" id="{EVENT_id1}" class="file_name">{FILE_NAME}</a><a href="#" title="Delete flash" id="{EVENT_id2}" class="del_file">\\xA0</a><a href="#" title="Upload flash" id="{EVENT_id3}" class="upload">\\xA0</a></div>',
                ),
                'file' => array(
                    'file_upload_1' => '<div class="adm_file"><a href="/file.php?id={VALUE}&amp;pos=0" style="display:{STYLE_DISPLAY}" target="_blank" id="{EVENT_id1}" class="file_name">{FILE_NAME}</a><a href="#" title="Delete file" id="{EVENT_id2}" class="del_file">\\xA0</a><a href="#" title="Upload file" id="{EVENT_id3}" class="upload">\\xA0</a></div>',
                ),
                'error' => array(
                    'err_field' => '<div class="errorForm">{TEXT}</div>',
                ),
                'note' => array(
                    'note_fld'       => '<div class="fieldNote"><i>{NOTE}</i>: {TEXT}</div>',
                    'note_fld_short' => '<div class="fieldNote shortNote"><i>{NOTE}</i>: {TEXT}</div>',
                    'note_frm'       => '<div class="formNote"><i>{NOTE}</i>: {TEXT}</div>',
                    'note_frm_short' => '<div class="formNote shortNote"><i>{NOTE}</i>: {TEXT}</div>',
                ),
                'submit' => array(
                    'submit_1' => '<button type="submit" id="{EVENT_id}"><span>{VALUE}</span></button>',
                ),
                'openRight' => array(
                    'open_r1' => '<a href="#" title="open right frame for detail" id="{EVENT_id}" class="openRight"> </a>',
                ),
                'delete' => array(
                    'delete_1' => '<a href="#" title="delete row" id="{EVENT_id}"> </a>',
                ),
                'not_standard' => array(
                    'ns_empty' => '',
                ),
            ),
        ),
    ),
);
?>