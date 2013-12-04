<?php
/**
 * Example of meta file for upload flash
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
        'main_table' => array(
            'table_name' => 'name_of_main_table',
            'flash_id'   => 'id_of_image_table', // Not used if link table is set. Else NR. By default: "id_file_data"
        ),
        'link_table' => array( // Use if link table is set
            'table_name' => 'name_of_link_table',
            'main_id'    => 'id_of_main_table',
            'flash_id'   => 'id_of_image_table', // NR. By default: "id_file_data"
        ),
        'access_type' => 'download price', // NR. Set access type
    ),
);
?>