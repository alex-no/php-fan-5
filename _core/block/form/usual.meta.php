<?php
/**
 * Example of meta file for block
 * Note:
 *   - All pathes to some other blocks set by {CONSTANTS}, which are defined in ini-file
 *   - NR - Not required parameter
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
 * @version of file: 05.02.001 (10.03.2014)
 */
return array(
    /**
     * Meta data for curent form block
     */
    'own' => array(
        'externalCss' => array(
            'new' => array('frm' => '/css/form.css'),
        ),

        'tpl_parent_class' => '\fan\project\service\template\type\form',

        /**
         * form parameters
         */
        'form' => array(
            'action_method'     => 'POST',
            'request_type'      => 'P',

            'redirect_required' => true,

            'csrf_protection' => 8,

            'js_url' => array(
                'js-wrapper' => '/js/js-wrapper.js',
                'js-loader'  => '/js/load-wrapper.js',
                'validator'  => '/js/form_validation.js'
            ),
            'js_validator'  => 'form_validation',
            'js_err_format' => 'alert',

            'required_msg'     => 'ERROR_FIELD_IS_REQUIRED',
            'required_msg_alt' => 'Field "{combi_part}" is required to be filled!',

            'formNumber' => 10,

            'notUseHtmlMaker' => false,
            'not_role' => false,

            'default_type' => array(
                'label'   => 'span',
                'error'   => 'field',
                'note'    => 'field',
                'button'  => 'submit_1',
                'formRow' => 'standard',
            ),
            'design' => array(
                'label' => array(
                    'span_required'         => '<span class="label require">{LABEL}<b>*</b>:</span>',
                    'span'                  => '<span class="label">{LABEL}:</span>',
                    'span_table_required'   => '<span class="label require">{LABEL}<b>*</b></span>',
                    'span_table'            => '<span class="label">{LABEL}</span>',
                    'simple_required'       => '{LABEL}<b>*</b>',
                    'simple'                => '{LABEL}',
                ),
                'input' => array(
                    'text'              => '<input type="text" name="{NAME}" value="{VALUE}"{MAXLENGTH}{ATTRIBUTES}{TABINDEX}{ID} class="inpText" />',
                    'text_empty'        => '<input type="text" name="{NAME}" value=""{MAXLENGTH}{ATTRIBUTES}{TABINDEX}{ID} class="inpText" />',
                    'text_short'        => '<input type="text" name="{NAME}" value="{VALUE}"{MAXLENGTH}{ATTRIBUTES}{TABINDEX}{ID} class="inpText shortText" />',
                    'password'          => '<input type="password" name="{NAME}" value=""{MAXLENGTH}{ATTRIBUTES}{TABINDEX}{ID} class="inpText" />',
                    'password_noAF'     => '<input type="text" name="disabled_password" disabled="disabled" style="display: none" /><input type="password" name="{NAME}" value=""{MAXLENGTH}{ATTRIBUTES}{TABINDEX}{ID} class="inpText" />',
                    'file'              => '<input type="file" name="{NAME}" value="{VALUE}"{ATTRIBUTES}{TABINDEX}{ID} />',
                    'textarea'          => '<textarea name="{NAME}" rows="5" cols="60"{MAXLENGTH}{ATTRIBUTES}{TABINDEX}{ID}>{VALUE}</textarea>',
                    'hidden'            => '<input type="hidden" name="{NAME}" value="{VALUE}"{ID} />',

                    'text_multiple'     => '<input type="text" name="{NAME}[]" value="{VALUE}"{MAXLENGTH}{ATTRIBUTES}{TABINDEX}{ID} class="inpText" />',
                    'file_multiple'     => '<input type="file" name="{NAME}[]" value="{VALUE}"{ATTRIBUTES}{TABINDEX}{ID} />',
                ),
                'checking' => array(
                    'checkbox'          => '<input type="checkbox" name="{NAME}" value="1"{CHECKED}{ATTRIBUTES}{TABINDEX}{ID} class="inpChkBx" />',
                    'radio'             => '<input type="radio" name="{NAME}" value="1"{CHECKED}{ATTRIBUTES}{TABINDEX}{ID} class="inpRadio" />',
                ),
                'select' => array(
                    'select'            => '<select name="{NAME}"{ATTRIBUTES}{TABINDEX}{ID}>[<option value="{VALUE}"{SELECTED}>{TEXT}</option>]</select>',
                    'select_short'      => '<select name="{NAME}"{ATTRIBUTES}{TABINDEX}{ID} class="shortSelect">[<option value="{VALUE}"{SELECTED}>{TEXT}</option>]</select>',
                    'radio_group'       => '<span class="formData">[<input type="radio" name="{NAME}" id="{ID}" value="{VALUE}"{CHECKED}{ATTRIBUTES}{TABINDEX} class="inpRadio" /><label for="{ID}">- {TEXT}</label>]</span>',
                    'radio_group_ml'    => '<div class="formData multiLine">[<div><input type="radio" name="{NAME}" id="{ID}" value="{VALUE}"{CHECKED}{ATTRIBUTES}{TABINDEX} class="inpRadio" /><label for="{ID}">- {TEXT}</label></div>]</div>',
                ),
                'select_separated' => array(
                    'radio_alone'   => '<input type="radio" name="{NAME}" id="{ID}" value="{VALUE}"{CHECKED}{ATTRIBUTES}{TABINDEX} class="inpRadio" />',
                    'radio_w_label' => '<input type="radio" name="{NAME}" id="{ID}" value="{VALUE}"{CHECKED}{ATTRIBUTES}{TABINDEX} class="inpRadio" /><label for="{ID}">- {TEXT}</label>',
                ),
                'select_multi' => array(
                    'select_multiple'   => '<select name="{NAME}[]" multiple="multiple"{ATTRIBUTES}{TABINDEX}{ID}>[<option value="{VALUE}"{SELECTED}>{TEXT}</option>]</select>',
                    'checkbox_group'    => '<div class="formData">[<input type="checkbox" name="{NAME}[]" id="{ID}" value="{VALUE}"{CHECKED}{ATTRIBUTES}{TABINDEX} class="inpChkBx" /><label for="{ID}">- {TEXT}</label>]</div>',
                    'checkbox_group_ml' => '<div class="formData multiLine">[<div><input type="checkbox" name="{NAME}[]" id="{ID}" value="{VALUE}"{CHECKED}{ATTRIBUTES}{TABINDEX} class="inpChkBx" /><label for="{ID}">- {TEXT}</label></div>]</div>',
                ),
                'select_multi_separated' => array(
                    'checkbox_alone'       => '<input type="checkbox" name="{NAME}[]" id="{ID}" value="{VALUE}"{CHECKED}{ATTRIBUTES}{TABINDEX} class="inpChkBx" />',
                    'checkbox_w_label'     => '<input type="checkbox" name="{NAME}[]" id="{ID}" value="{VALUE}"{CHECKED}{ATTRIBUTES}{TABINDEX} class="inpChkBx" /><label for="{ID}">- {TEXT}</label>',
                    'checkbox_w_label_one' => '<input type="checkbox" name="{NAME}" value="1"{CHECKED}{ATTRIBUTES}{TABINDEX} class="inpChkBx" id="{ID}" /><label for="{ID}">- {TEXT}</label>',
                ),
                'error' => array(
                    'field' => '<div class="errorField">{TEXT}</div>',
                    'form'  => '<div class="errorForm">{TEXT}</div>',
                ),
                'note' => array(
                    'field'       => '<div class="fieldNote"><i>{NOTE}</i>: {TEXT}</div>',
                    'field_short' => '<div class="fieldNote shortNote"><i>{NOTE}</i>: {TEXT}</div>',
                    'field_popup' => '<a class="fieldNote popupNote" href="#"><span>{TEXT}</span></a>',
                    'form'        => '<div class="formNote"><i>{NOTE}</i>: {TEXT}</div>',
                    'form_short'  => '<div class="formNote shortNote"><i>{NOTE}</i>: {TEXT}</div>',
                ),
                'button' => array(
                    'submit_1' => '<button type="submit"{NAME} value="{VALUE}"{ATTRIBUTES}{TABINDEX}{CLASS}{ID}><span>{TEXT}</span></button>',
                    'submit_2' => '<button type="submit"{NAME} value="{VALUE}"{ATTRIBUTES}{TABINDEX}{CLASS}{ID}><span><span>{TEXT}</span></span></button>',
                ),
                'formRow' => array(
                    'standard'   => '<div class="formRow">{LABEL}{FORM_FIELD}{ERROR}{NOTE}</div>',
                    'label_left' => '<div class="formRow labelLeft">{LABEL}{FORM_FIELD}{ERROR}{NOTE}</div>',
                    'label_right' => '<div class="formRow labelRight">{LABEL}{FORM_FIELD}{ERROR}{NOTE}</div>',
                ),
            ),
        ), //'form'

        'cache' => array(
            'mode'     => 2,
        ),
    ),
);
?>