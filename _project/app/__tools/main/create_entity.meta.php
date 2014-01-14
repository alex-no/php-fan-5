<?php
/**
 * Create entity tools meta
 * @version 1.0
 */
return array(
    'own' => array(
        'mainCarcass' => '{CARCASS}/carcass_with_db_connection',

        'title' => 'Create entity',

        'externalCss' => array( // css files
            'new' => array('~/entity.css'),
        ),

        'embeddedBlocks' => array(
            'entity_filter' => '{CAPP}/extra/entity_filter',
        ),

        'form' => array(
            'action_method'  => 'POST',
            'request_type'   => 'P',

            'form_key_name'  => 'create_entity',
            'form_id'        => 'create_entity',

            'fields' => array(
                'tbl' => array(
                    'label'          => 'Table',
                    'input_type'     => 'checkbox',
                ),
            ),
        ), //form

        'dontCrawl' => true,

        'cache' => array(
            'mode' => 1,
        ),
        'roles' => array (
            array (
                'condition'     => 'tools_access',
                'transfer_sham' => '~/',
            ),
        ),
    ),
);
?>