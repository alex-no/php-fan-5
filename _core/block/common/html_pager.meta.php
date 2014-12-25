<?php
/**
 * Play list meta
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
 * @version of file: 05.02.004 (25.12.2014)
 */
return array(
    /**
     * Meta data for curent carcass block
     */
    'own' => array(
        'tplType' => 'references',

        'qttLimit' => array(
            'startEnd' => 2,
            'middle'   => 5,
        ),

        'cache' => array(
            'mode' => 1,
        ),

        // quantifier params
        'quantifier' => array(
            'allow'  => false,
            'form'   => 'form/pager_quantifier',
            'label'  => 'Elements per page',
            'values' => '20,30,50,100', // comma separated values
        ),
    ),
);
?>