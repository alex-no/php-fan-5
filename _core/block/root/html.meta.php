<?php
/**
 * Basiс html Meta data
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
 * @version of file: 05.01.001 (03.09.2013)
 */
return array(
    'own' => array(
        'browserClasses' => array (
            'isOpera' => array(
                'regExp'   => '/Opera\W*(\d+(:?\.\d+)?)/',
                'olderVer' => array('isOpera8' => 9),
            ),
        ),
        'cache' => array(
            'mode' => 1,
        ),
    ),
);
?>