<?php
/**
 * Example of meta file for block
 * Note:
 *   - All paths to some other blocks set by {CONSTANTS}, which are defined in ini-file
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
 * @version of file: 05.02.006 (20.04.2015)
 */
return array(
    /**
     * Meta data for current form block
     */
    'own' => array(
        /**
         * form parameters
         */
        'form' => array(
            'action_url'        => 'URL', // NR. By default: current url
            'action_https'      => null OR true OR false, // NR. null: Current protocol; true: HTTPS false: HTTP. By default: action_protocol = null
            'action_method'     => 'GET/POST/FILE', // NR. By default: POST. FILE = 'method="post" enctype="multipart/form-data"'
            'request_type'      => 'GPF', // NR. By default: equivalent to 'action_method': GET=>'G', POST=>'P', FILE=>'PF' else 'GPF'

            'redirect_uri'      => 'URI', // NR. By default: current uri
            'redirect_required' => true OR false, // NR. By default: redirect_required = true
            'redirect_https'    => null OR true OR false, // NR. null: Current protocol; true: HTTPS false: HTTP. By default: redirect_protocol = null

            'always_parse'     => true OR false, // NR. Always validate this form. By default: validate_required = false
            'form_submit_name' => 'name(s) of Submit form button(s)', //NR. If specified key(s) has any value - it is need validate form
            'form_exceptions'  => array('value1','value2', 'etc'), //NR. If specified keys form id parsed always if they don't set

            'form_id'         => 'id of form tag', // Required if form has validation rules. You can (NR) set it also as value for hidden 'form_key_field'
            'form_key_name'   => 'name of key', //  Name of key for button when complex form is used
            'csrf_protection' => 8, //NR. By default 8. The length of CSRF-protection code from 4 to 32 (add it to form-key). If length is out of range - CSRF-protection is not used.

            'role_name' => 'name of one_time_roles', // It's used for show other html-code after submit. By default: 'form_submit_successful_' . form_id
            'element_id_prefix' => 'id prefix', // (NR) Prefix for Id of elements

            'js_url' => array( // URLs for JS-validators
                'js-wrapper' => '/js/js-wrapper.js',
                'js-loader'  => '/js/load-wrapper.js',
                'validator'  => '/js/form_validation.js'
            ),
            'js_validator'  => 'form_validation', // Name of JS-class for form validation
            'js_err_format' => 'alert', // Format of show of error messages. Available format for now: 'alert', 'div'. 'alert' - show error by alert, 'div' - show error by div near each input field
            'js_loader' => array(
                'url'    => '/form_validation/form_name.php',
                'fields' => array('field_name1', 'field_name2', 'etc'),
            ),

            'useMultiLanguage' => true OR false, //NR. Set form vessages as multy- OR one-language. By default: true or false depending on service locale is allowed or not
            'required_msg' => 'Field "{FIELD_LABEL}" is required for fill!', //NR. By default: has value as show in this example

            'startTabIndex' => 1, //NR. Start TabIndex number. By default: 1

            'notUseHtmlMaker' => true OR false, // NR. Do not create object HTML-maker in the template. By default: notUseHtmlMaker = false
            'not_role' => true OR false, // NR. Do not create temporary role for disable form show after submit. By default: not_role = false

            'form_parts' => array(), // NR. Set it for multi-part form in the main part. Contain list of form-parts and order of parsing them.
            'auto_init_parts' => true, // NR. Init all parts of  form (set data and default values for form elements) as soon as main parsing is runned.

            'fields' => array(
                'field_name1' => array(
                    'label'        => 'text before field', //Required if form has validation rules or 'is required'
                    'note'         => 'note for field',    //NR
                    'fill_empty'   => 'text into field before typing', //NR
                    'input_type'   => 'text/password/checkbox...etc', //NR
                    'trim_data'    => true OR false, //NR. By default: true for all input type except 'password'
                    'trim_tag'     => true OR false, //NR. Trim tag in get data. By default: true
                    'trim_tag_val' => '&"\'<>\\',    //NR. This parameter work together with trim_tag=true. There is list of symbols, which must be replaced. By default: '&"\'<>\\'
                    'is_required'  => true OR false, //NR. By default: if field has validation rule with ('not_empty' = false) - it is required, else it isn't
                    'depth'        => 1, //NR. Used for multi-value elements. It is points depth of data-array. For usual elements it equals 0

                    'data' => array( //NR. Data for select, radio-group, etc
                        array('value' => 'value1', 'text' => 'text1'),
                        array('value' => 'value2', 'text' => 'text2'),
                        // ....
                    ),
                    'dataSource' => array( //NR. Method for get data.
                        // This is more priority than "data". But if you point both of them and method return NULL - will be used "data"-parameter
                        'method' => 'Method name',
                        'class'  => 'Class name', //NR. If class is set - call static method of this class else method of current block
                    ),
                    'not_check_by_data' => false, //NR. Used for select, radio, etc elements only for disable check value by data

                    'default_value' => 'default value', //NR.
                    'maxlength'     => 23, // NR. This parameter is set as attribute for input-tag. If it is set received value will be cut for this length before validation
                    'attributes'    => array( //NR. List of additional attributes with arbitrary name and value
                        'name_of_attr' => 'value_of_attr',
                        // ...
                    ),
                    'validate_rules' => array( //NR.
                        array(
                            'rule_name' => 'name of validation rule',
                            'rule_data' => array('data for rule'), //NR. It is need for rule with additional data
                            'not_empty' => true OR false, // NR. true - use rule for not empty data only; false - use rule for all type data false. By default: false
                            'error_msg' => 'Message about error for field "{FIELD_LABEL}"',
                            'group_rule'=> true OR false, // NR. true - use rule for group values; false - use rule for each field. By default: false
                            'not_js'    => true OR false, // NR. true - do not use rule in JavaScript. By default: false
                        )
                    ),
                ),
                'field_name2' => array(
                    //...
                ),
                // etc.
            ),
        ), //'form'

        'cache' => array( // cache-control parameters
            'mode'     => 2, // Cache mode: 0 - don't use cache there and in container, 1 - don't use cache, 2 - clear cache by 'refresh' and 'expire', 3 - clear cache manually only
            'clear'    => 'path to block', // Clear cache of other block
            // <-OR->
            'clear'    => array( // Clear cache of other blocks
                'path-1 to block',
                'path-2 to block',
                //...
            ),
        ),
    ),
);
?>