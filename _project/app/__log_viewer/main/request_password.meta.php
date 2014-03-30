<?php
/**
 * Meta data of request password block
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
 * @version of file: 05.02.002 (31.03.2014)
 */
return array(
    'own' => array(

        'carcass' => 'carcass/simple',

        'title'   => 'Enter password',

        'externalCss' => array(
            'new' => array('~/password.css'),
        ),

        'passwd_as_hash' => 0,

        'form' => array(
            'action_method'  => 'POST',
            'request_type'   => 'P',
            'redirect_uri'   => '~/index.html',
            'form_key_name'  => 'log_password',
            'form_id'        => 'log_password',
            'required_msg'   => 'FIELD_"{FIELD_LABEL}"_IS_REQUIRED',
            'fields' => array(
                'login' => array(
                    'label'          => 'Login',
                    'input_type'     => 'text',
                    'is_required'    => true,
                ),

                'password' => array(
                    'label'          => 'Password',
                    'input_type'     => 'password',
                    'is_required'    => true,
                    'validate_rules' => array(
                        array(
                            'rule_name' => 'checkPassword',
                            'rule_data' => array (
                                'login' => 'login',
                            ),
                            'not_empty' => true,
                            'error_msg' => 'Incorrect login or password',
                            'not_js'    => true,
                        ),
                    ),
                ),
            ),
        ), //form
    ), //'own'
);
?>