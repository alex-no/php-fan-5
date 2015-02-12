<?php
/**
 * Create entity tools meta
 * @version 05.02.005 (12.02.2015)
 */
return array(
    'own' => array(
        'title' => 'Create entity',

        'externalCss' => array( // css files
            'style' => array('~/entity.css'),
        ),

        'embeddedBlocks' => array(
            'entity_filter' => 'form/entity_filter',
        ),

        'form' => array(
            'action_method'  => 'POST',
            'request_type'   => 'P',

            'form_key_name'  => 'create_entity',
            'form_id'        => 'create_entity',

            'fields' => array(
                'tbl' => array(
                    'label'      => 'Table',
                    'input_type' => 'checkbox',
                    'depth'      => 1
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