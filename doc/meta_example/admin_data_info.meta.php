<?php
/**
 * Example of meta file for admin_content_info block
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
 * @version of file: 02.002
 */
return array(
    'own' => array(
        'force'  => array(
            'template'   => true OR false, // NR. Set it if you want to parce template each time.     By default: false
            'add_param'  => true OR false, // NR. Set it if you want to set add parameters each time. By default: false
            'extra_data' => true OR false, // NR. Set it if you want to set extra data each time.     By default: false
        ),
		
        'tagId' => 'id_name', // NR. Id for tag container. It will be with prefix "cont_". Usualy use for css-classes

        'parsingScript' => 'Some script which run after create block',

        'template' => 'used_template_name(NR - it is need to set when template_name not equal to class_name)',

        /**
         * All parameters below it is possible to set as "own"-part, amd in 'common'-part
         */
        'tplVars' => array( // variable, which sets in template automaticaly
            'tplVar1' => 'Value of variable 1',
            'tplVar2' => 'Value of variable 2',
        ),
    ),
);
?>