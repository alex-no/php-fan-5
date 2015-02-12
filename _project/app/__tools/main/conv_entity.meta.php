<?php
/**
 * Convert entity tools meta
 * @version 05.02.005 (12.02.2015)
 */
return array(
    'own' => array(
        'title' => 'Tool for convert entity from old (PHP-FAN4) to new (PHP-FAN5) format',

        'form' => array(
            'action_method'  => 'POST',
            'request_type'   => 'P',
            'form_key_name'  => 'conv_entity',
            'form_id'        => 'conv_entity',
            'fields' => array(
                'source_dir' => array(
                    'label'          => 'Source directory',
                    'input_type'     => 'text',
                    'is_required'    => true,
                ),
                'source_mask' => array(
                    'label'          => 'Regexp for select files',
                    'input_type'     => 'text',
                    'is_required'    => true,
                ),
                'dest_dir' => array(
                    'label'          => 'Destination directory',
                    'input_type'     => 'text',
                    'is_required'    => true,
                ),

            ),
        ), //form

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