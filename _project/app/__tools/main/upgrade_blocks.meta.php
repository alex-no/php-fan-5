<?php
/**
 * Index tools meta
 * @version 05.02.001 (10.03.2014)
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
            'ns'   => 'fan\app\admin',
            'extends' => array(
                'block_loader_admin_structure'    => '\fan\project\block\admin\structure',
                'block_loader_admin_data_table'   => '\fan\project\block\admin\data_table',
                'block_loader_admin_data_form'    => '\fan\project\block\admin\data_form',
                'block_loader_admin_data_info'    => '\fan\project\block\admin\data_info',
                'block_loader_admin_upload_image' => '\fan\project\block\admin\upload_image',
                'block_loader_admin_upload_flash' => '\fan\project\block\admin\upload_flash',
                'block_loader_admin_upload_file'  => '\fan\project\block\admin\upload_file',
                'block_loader_base'               => '\fan\project\block\loader\base',
                'block_html_base'                 => '\fan\project\block\common\simple',
                'simple_template_block'           => '\fan\project\block\common\simple',
                'block_html_form_base'            => '\fan\project\block\form\injector',
                'block_form_base_input'           => '\fan\project\block\form\injector',
                'entity_base'                     => '\fan\project\base\model\entity',
            ),
            'direct_replace' => array(
                'php' => array(
                    '/(?<=\W)array_merge_recursive_spec(?=\W)/' => 'array_merge_recursive_alt',
                    '/getMetaVar/'                => 'getMeta',
                    '/load_runner\:\:parse_path/' => '\bootstrap::parsePath',
                    '/\$this\-\>getAggrEntities\s*\(/' => '$this->getRowset(',
                    '/\$this\-\>saveEntityData\s*\(/'  => '$this->saveRow(',
                    '/\$this\-\>runAggrRequest\s*\(/'  => '$this->getArrayAssoc(',
                    '/\-\>setTemplateVar\s*\(/' => '->_setViewVar(',
                    '/\-\>getBlock\s*\(/'       => '->_getBlock(',
                    '/\-\>parseForm\s*\(/'      => '->_parseForm(',
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