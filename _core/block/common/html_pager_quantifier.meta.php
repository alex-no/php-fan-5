<?php
/**
 * Meta-data of pager quantifier
 * Note:
 *   - All pathes to some other blocks set by {CONSTANTS}, which are defined in ini-file
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
 * @version of file: 05.02.004 (25.12.2014)
 */
return array(
    'own' => array(
        'initOrder' => 850,

        'externalJS' => array(
            'head' => array('//_javascript/js-wrapper.js', '//_javascript/auto_submit.js'),
        ),
        'embedJS' => array(
            'head' => 'new auto_submit("#pager_quantifier", "button", {"select[name=pager_quantifier]":"onchange"});',
        ),

        'form' => array(
            'action_method'     => 'GET',
            'request_type'      => 'G',
            'redirect_required' => false,

            'always_parse'     => true,

            'form_key_name' => 'pager_quantifier',

            'fields' => array(
                'pager_quantifier' => array(
                    'label'       => 'Elements per page',
                    'input_type'  => 'select',
                ),
            ),
        ),
    ),
);
?>