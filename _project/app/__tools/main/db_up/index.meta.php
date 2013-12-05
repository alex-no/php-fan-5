<?php
/**
 * Index tools meta
 * @version 1.0
 */
return array(
    'own' => array(
        'title' => 'Update database',

        'externalCss' => array(
            'new'   => array('/db_up_index.css'),
        ),

        'externalJS' => array(
            'head' => array('//_javascript/js-wrapper.js', '//_javascript/form_validation.js', '/db_up.js'),
        ),

        'form' => array(
            'action_method'  => 'GET',
            'request_type'   => 'G',

            'form_id'        => 'select_scenario',
            'form_key_name'  => 'select_scenario',

            'fields' => array(
                'scenario' => array(
                    'label'       => 'Scenario',
                    'is_required' => true,
                    'input_type'  => 'radio_group_ml',
                ),
            ),
        ), //form

        'roles' => array (
            array (
                'condition'    => 'tools_access',
                'out_transfer' => '/index.html',
            ),
        ),
    ),
);
?>