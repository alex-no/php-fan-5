<?php
/**
 * Meta data of main carcass
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
 * @version of file: 05.003 (23.12.2013)
 */
return array(
    'own' => array(
        'embeddedBlocks' => array(
            'header' => '{CAPP}/design/header',
            'main'   => '{MAIN}',
            'footer' => '{CAPP}/design/footer',
        ),

        'externalCss' => array(
            'new' => array('~/layout.css'),
        ),

        'template' => '/main.tpl',
    ),
);
?>