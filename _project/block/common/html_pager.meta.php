<?php
/**
 * Pager meta-file
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
    /**
     * Meta data for curent carcass block
     */
    'own' => array(
        'externalCss' => array(
            'new' => array('/css/pager.css'),
        ),

        'elmPerPage'    => 8,
        'qttLimit' => array(
            'startEnd' => 2,
            'middle'   => 5,
        ),

        'tplVars' => array (
            //the type of templates
            'tplType'       => 'references',
            //if showIfOnePage=1, the pager will be showed even if there is one page
            'showIfOnePage' => 0,
            //if showPrevNext=1, the references on the previous and next will be showed
            'showPrevNext'  => 1,
            //if showFirstLast=1, the references on the first page and last page will be showed
            'showFirstLast' => 1,
        ),
    ),
);
?>