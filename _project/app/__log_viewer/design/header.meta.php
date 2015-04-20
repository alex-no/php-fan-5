<?php
/**
 * Meta data of header main block
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
 * @version of file: 05.02.005 (12.02.2015)
 */
return array(
    'own' => array(
        'embeddedBlocks' => array(
            'nav' => 'design/nav',
        ),

        'externalJS' => array(
            'head' => array(
                '/js/js-wrapper.js',
                '/js/load-wrapper.js',
                '~/log_ctrl.js',
                //'/js/debug.js',
            ),
        ),

        'initOrder' => 1200,
    ),
);
?>