<?php
/**
 * Block admin meta-data
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
        'externalCss' => array(
            'new' => array(
                'c00' => '~/main.css',
                'c01' => '~/layout.css',
                'c02' => '~/data.css',
                'c03' => '~/elm.css',
                'c04' => '/css/extra/calendar.css'
            ), // files are attached by @import-directive
            'ie'  => array(
                'c00' => '~/ie.css'
            ), // files are used for IE5-IE7
            //'ie6' => array('~/ie6.css'), // files are used for IE5-IE6
        ),
        'externalJS' => array( // JavaScript files
            'head' => array(
                //'m00' => '/js/debug.js',
                'm01' => '/js/js-wrapper.js',
                'm02' => '/js/load-wrapper.js',

                'm03' => '~/ctrl/main_ctrl.js',
                'm04' => '~/ctrl/login_ctrl.js',
                'm05' => '~/ctrl/pattern_ctrl.js',
                'm06' => '~/ctrl/view_ctrl.js',
                'm07' => '~/ctrl/change_ctrl.js',
                'm08' => '~/ctrl/pager_common.js',
                'm09' => '~/ctrl/pager_left_ctrl.js',
                'm10' => '~/ctrl/pager_right_ctrl.js',
                'm11' => '~/ctrl/wysiwyg_ctrl.js',

                'm12' => '~/item/left_frame_item.js',
                'm13' => '~/item/right_frame_item.js',
                'm14' => '~/item/condition_item.js',

                'm15' => '~/item/content_item.js',
                'm16' => '~/item/content_addon.js',

                'm17' => '~/item/combo_select_item.js',

                'm18' => '~/item/adm_image_item.js',
                'm19' => '~/item/adm_flash_item.js',
                'm20' => '~/item/adm_file_item.js',
                'a01' => '/js/extra/calendar.js',
            ),
        ),
        'embedJS' => array( // embed JavaScript text
            'head' => 'mainCtrl.init(' . (role('admin') ? 1 : 0) . ', \'/admin\', _wrapper);',
        ),

        'title' => 'Admin System',

        'template' => dirname(__FILE__) . '/index.tpl',
    ),
);
?>