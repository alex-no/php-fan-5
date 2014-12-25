<?php
/**
 * Example of meta file for block
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
    /**
     * Meta data for curent form block
     */
    'own' => array(
        /**
         * form parameters
         */
        'form' => array(
            'request_type'    => 'P',
            'csrf_protection' => 8,

            'required_msg'     => 'ERROR_FIELD_IS_REQUIRED',
            'required_msg_alt' => 'Field "{combi_part}" is required to be filled!',

            'not_role' => false,

        ), //'form'

        'cache' => array(
            'mode'     => 2,
        ),
    ),
);
?>