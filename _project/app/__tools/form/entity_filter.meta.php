<?php
/**
 * Create entity tools meta
 * @version 05.02.005 (12.02.2015)
 */
return array(
    'own' => array(
        'externalCss' => array(
            'style' => array('frm' => '~/form.css'),
        ),

        'form' => array(
            'action_method'  => 'GET',
            'request_type'   => 'G',

            'form_key_name'  => 'filter',
            'form_id'        => 'filter',

            'fields' => array(
                'connection' => array(
                    'label'      => 'Database',
                    'input_type' => 'select',
                    'dataSource' => array(
                        'method' => 'getDbList',
                    ),
                ),
                'ns_pref' => array(
                    'label'      => 'Model subdirectory',
                    'input_type' => 'select',
                    'dataSource' => array(
                        'method' => 'getDirList',
                    ),
                ),
                'table_regexp' => array(
                    'label'         => 'Table name regexp',
                    'input_type'    => 'text',
                    'default_value' => '/.+/',
                    'note'          => 'Enter the regular expression to filter by names of the database tables',
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