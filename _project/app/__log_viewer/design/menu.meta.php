<?php
/**
 * Meta data of main menu
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
        'tplVars' => array( // variable, which sets in template automaticaly
            'aMenu' => array(
                array('key' => 'error',     'name' => 'Error log'),
                array('key' => 'data',      'name' => 'Data log'),
                array('key' => 'message',   'name' => 'Message log'),
                array('key' => 'bootstrap', 'name' => 'Bootstrap log'),
            ),
            'sCurrent' => 'error',
        ),
    ),
);
?>
