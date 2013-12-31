<?php
/**
 * Create entity tools meta
 * @version 1.0
 */
return array(
    'own' => array(
        'form' => array(
            'action_method'  => 'GET',
            'request_type'   => 'G',

            'form_key_name'  => 'filter',
            'form_id'        => 'filter',

            'fields' => array(
                'connection' => array(
                    'label'      => 'Database',
                    'input_type' => 'select',
                ),
                'ns_pref' => array(
                    'label'      => 'Model subdirectory',
                    'input_type' => 'select',
                ),
                'table_regexp' => array(
                    'label'         => 'Table name regexp',
                    'input_type'    => 'text',
                    'default_value' => '/.+/',
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
                'out_transfer' => '/',
            ),
        ),
    ),
);
?>