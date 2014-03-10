<?php
/**
 * Meta data of test form block
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
 * @version of file: 05.01.002 (01.05.2013)
 */
return array(
    'own' => array(
        'title'   => 'Test form',

        'form' => array(
            'action_method'  => 'POST',
            'request_type'   => 'P',
            'form_key_name'  => 'test_user',
            'form_id'        => 'test_user',
            'required_msg'   => 'Field "{FIELD_LABEL}" is required for fill.',
            'fields' => array(
                'login' => array(
                    'label'       => 'Login',
                    'input_type'  => 'text',
                    'is_required' => true,
                ),

                'password' => array(
                    'label'       => 'Password',
                    'input_type'  => 'password',
                    'is_required' => false,
                    'validate_rules' => array(
                        array(
                            'rule_name' => 'checkPassword',
                            'rule_data' => array (
                                'login_field' => 'login',
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