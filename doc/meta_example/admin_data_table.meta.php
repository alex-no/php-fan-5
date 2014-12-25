<?php
/**
 * Example of meta file for admin_content_table block
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
 * @version of file: 02.009
 */
return array(
    'own' => array(
        'entity' => 'table_name',
        'entity_key' => 'key_name', // NR. It is need to set it when used special SQL-request

        'force'  => array(
            'template'   => true OR false, // NR. Set it if you want to parce template each time.     By default: false
            'add_param'  => true OR false, // NR. Set it if you want to set add parameters each time. By default: false
            'extra_data' => true OR false, // NR. Set it if you want to set extra data each time.     By default: false
        ),

        'condition' => array( // NR. Add condition for select data for table
            'key_1' => 'val_1',
            'key_2' => 'val_2',
            // ....
        ),
        'order' => array( // NR. Fields for order
            'field_name_1' => 1,  // Primordial: by ascending
            'field_name_2' => 0,  // Primordial: not used
            'field_name_3' => -1, // Primordial: by descending
            // ....
        ),
        'open_right' => array(
            'field_name_1' => array(
                'pos' => 'before' OR 'after', // NR. By default 'before'
                'pat' => 'pattern_name', // NR. By default 'open_r1'
                'key' => 'r_key_1', // NR. Set it if you what to switch to this key each click
            ),
        ),

        'elmPerPage' => 20, //  NR. Qtt elements per one page. Default value is set in base class
        'useMainPage' => true OR false, // NR. Allow for additional content data to use Main pager

        'addParam' => array( // NR. Add parameters
            'select' =>array( // For tags: select, radio-group, checkbox-group
                'field_name_0' => array(
                    'key_1' => 'val_1',
                    'key_2' => 'val_2',
                    // ....
                ),
                'field_name_1' =>array(
                ),
                // ....
            ),
            'combo_select' => array(
                'field_name_0' => array(
                    'data' => array(
                        'key_1' => array('val' => 'val_1', 'child' => array() /* The same data*/),
                        'key_2' => array('val' => 'val_2', 'child' => array() /* The same data*/),
                        // ....
                    ),
                    'depth'  => 2, // max depth
                    'label'  => array('label_0','label_1',),
                    'key'    => array('sel_key_0','sel_key_1',),
                    'loader' => 'Path of loader',
                ),
                'field_name_1' =>array(
                    // ....
                ),
                // ....
            ),
            'default_val' => array( // NR. Set condition key or real value.
                'field_name_0' => '[condition_key_1]',
                'field_name_4' => 'XXX',
            ),
            'new_row_cond' => array( // NR. Set row for insert new data by condition.
                'field_name_0' => array(
                    0 => array('value_1', 'value_2', /*....*/ 'value_N'), // 'condition' must be NOT equal to one of values
                    1 => array('value_1', 'value_2', /*....*/ 'value_N'), // 'condition' must be equal to one of values
                ),
                'field_name_1' => array(
                    0 => array('value_1', 'value_2', /*....*/ 'value_N'), // Value for disable line 'new'
                    1 => array('value_1', 'value_2', /*....*/ 'value_N'), // Value for enable line 'new'
                ),
                // ....
            ),
            'convId' => array('!', 'default_conv_id'),
            // Special additional Java-script \\
            'wysiwyg' => array(
                'field_name_0' => 'config_name_0',
                'field_name_1' => 'config_name_0',
                // ....
            ),
            'image_loader' => array(
                'field_name_0' => 'image_loader_0',
                'field_name_1' => 'image_loader_1',
                // ....
            ),
            'flash_loader' => array(
                'field_name_0' => 'flash_loader_0',
                'field_name_1' => 'flash_loader_1',
                // ....
            ),
            'file_loader' => array(
                'field_name_0' => 'file_loader_0',
                'field_name_1' => 'file_loader_1',
                // ....
            ),
            'not_standard' => array(
                'field_name_0' => 'script_name_0',
                'field_name_1' => 'script_name_1',
                // ....
            ),
        ),

        'tagId' => 'id_name', // NR. Id for tag container. It will be with prefix 'cont_'. Usualy use for css-classes

        'editId' => true OR false, // NR. Allow get and edit ID

        'default_tpl' => substr(__FILE__, 0, -8) . 'tpl', // NR. Template for make table. Default template is set in base class
        'table_struct' => array(
            'isHead'  => true OR false, // NR. Is head table. By default: true
            'showId'  => true OR false, // NR. Is show Id columns. By default: true
            'showDel' => true OR false, // NR. Is show Del columns. By default: true
            'newData' => true OR false, // NR. Is show row for new records. By default: true

            'columns' =>  array( // Table columns
                array(
                    'field'  => 'field_name_1',
                    'head'   => 'Table head 1',
                    'type'   => 'text',        // Pattern type
                    'width'  => 400,           // NR. Column width (px). By default: by content
                    'notSQL' => true OR false, // NR. Not use this field in SQL-request. By default: false
                ),
                array(
                    'field' => 'field_name_2',
                    'head'  => 'Table head 2',
                    'type'  => 'radio_group',
                ),
                array(
                    'field' => 'field_name_3',
                    'head'  => 'Table head 3',
                    'type'  => 'not_edit',
                ),
            ),
        ),

        'validation' => array(
            'field_name1' => array(
                'is_required' => true OR false, // NR. By default: false
                'trim_data'   => true OR false, // NR. true - trim data before validate; false - do not trim data. By default: true
                'validate_rules' => array( //NR.
                    array(
                        'rule_name' => 'name of validation rule', // Rule method
                        'rule_data' => array('data for rule'), //NR. It is need for rule with additional data
                        'not_empty' => true OR false, // NR. true - use rule for not empty data only; false - use rule for all type data false. By default: false
                        'error_msg' => 'Message about error for field "{FIELD_LABEL}"',
                    )
                ),
            ),
            'field_name2' => array(
                //...
            ),
            // etc.
        ),
        'check_access4edit'   => 'entity_method_name1', //NR. It is need set (in the entity) only for member with limited possibility
        'check_access4delete' => 'entity_method_name2', //NR. It is need set (in the entity) only for member with limited possibility
    ),
);
?>