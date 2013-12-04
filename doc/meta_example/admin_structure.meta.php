<?php
/**
 * Example of meta file for block
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
 * @version of file: 02.008
 */
return array(
    'own' => array(
        'json' => array (
            'title' => 'Title for left frame',

            'top_content' => array(
                '{prs}m_url_1.php',
                '{prs}m_url_2.php',
                // ....
            ),

            'bottom_content' => array( // NR.
                '{prs}m_url_3.php',
                '{prs}m_url_4.php',
                // ....
            ),

            'right_frm' => array( // NR.
                'r_key_1' => array(
                    'name' => 'Name 1',
                    'title' => 'Title for right frame 1',
                    'top_content' => array(
                        '{prs}r_url_1.php',
                        '{prs}r_url_2.php',
                        // ....
                    )
                ),
                'r_key_2' => array(
                    'name' => 'Name 2',
                    'title' => 'Title for right frame 2',
                    'top_content' => array(
                        '{prs}r_url_3.php',
                        '{prs}r_url_4.php',
                        // ....
                    )
                ),
                // ....
            ),
        ),

        'addParam' => array( // NR. Add parameters for condition
            'select' =>array( // For tags: select, radio-group, checkbox-group
                'condition_1' =>array(
                    'key_1' => 'val_1',
                    'key_2' => 'val_2',
                    // ....
                ),
            ),
        ),

        'cond' => array( // Start condition value
            'condition_1' => 'st_val_1',
            'condition_2' => 'st_val_2',
            // ....
        ),

        'extra' => array(
            'active' => array( // Reload content after change next condition
                'condition_1' => 1,
                'condition_2' => 1,
                // ....
            ),
            'subtitle' => 'condition_1', // NR. Use this condition for set subtitle
        ),
    ),
);
?>