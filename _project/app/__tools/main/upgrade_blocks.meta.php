<?php
/**
 * Index tools meta
 * @version 1.0
 */
return array(
    'own' => array(
        'title' => 'Tool for convert admin files from PHP-FAN4 to PHP-FAN5',
/*
        'externalCss' => array( // css files
            'new' => array('/entity.css'),
        ),
*/
        'src' => array(
            'path' => '{PROJECT}/app/admin',
            'ns'   => 'app\\admin',
            'extends' => array(
                'block_loader_admin_structure'    => '\project\block\admin\structure',
                'block_loader_admin_data_table'   => '\project\block\admin\data_table',
                'block_loader_admin_data_form'    => '\project\block\admin\data_form',
                'block_loader_admin_data_info'    => '\project\block\admin\data_info',
                'block_loader_admin_upload_image' => '\project\block\admin\upload_image',
                'block_loader_admin_upload_flash' => '\project\block\admin\upload_flash',
                'block_loader_admin_upload_file'  => '\project\block\admin\upload_file',
                'block_loader_base'               => '\project\block\loader\base',
                'block_html_base'                 => '\project\block\common\simple',
                'simple_template_block'           => '\project\block\common\simple',
                'block_html_form_base'            => '\project\block\form\usual',
                'block_form_base_input'           => '\project\block\form\usual',
                'entity_base'                     => '\project\base\model\entity',
            ),
            'direct_replace' => array(
                'php' => array(
                    '/(?<=\W)array_merge_recursive_spec(?=\W)/' => 'array_merge_recursive_alt',
                    '/getMetaVar/'                  => 'getMeta',
                    '/load_runner\:\:parse_path/'   => '\\bootstrap::parsePath',
                    '/\$this\-\>getAggrEntities\(/' => '$this->getRowset(',
                    '/\$this\-\>saveEntityData\(/'  => '$this->saveRow(',
                    '/\$this\-\>runAggrRequest\(/'  => '$this->getArrayAssoc(',
                ),
                'meta' => array(
                    '/(?<=\W)array_merge_recursive_spec(?=\W)/' => 'array_merge_recursive_alt',
                    '/(?<=\W)d4mf\(/' => '$this->_makeActiveMeta(',
                ),
                'tpl' => array(
                    //'' => '',
                ),
            ),
        ), //src

        'dontCrawl' => true,

        'cache' => array(
            'mode' => 1,
        ),
        'roles' => array (
            array (
                'condition'    => 'tools_access',
                'transfer_out' => '~/',
            ),
        ),
    ),
);
?>