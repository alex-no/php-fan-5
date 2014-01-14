<?php
/**
 * Example of meta file for block
 * Note:
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
 * @version of file: 02.004
 */
return array(
    'own' => array(
        'json' => array (
            //Many-dimensional array of src JSON-object
        ),

        'notUseTemplate' => true OR false, // NR. true - do not parse template automaticaly. By default: false
        'template' => 'used_template_name(NR - it is need to set when template_name not equal to class_name)',

        /**
         * All parameters below it is possible to set as "own"-part, amd in "common"-part
         */
        'tplVars' => array( // variable, which sets in template automaticaly
            'tplVar1' => 'Value of variable 1',
            'tplVar2' => 'Value of variable 2',
        ),

        'text' => 'srctext',

        'roles' => array (
            array (
                'condition'    => '(role_A|role_B)&!role_C',
                'transfer_int' => 'transferURL',
            ),
        ),
    ),
);
?>