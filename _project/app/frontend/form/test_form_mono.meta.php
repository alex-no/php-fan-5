<?php
/**
 * Test format meta
 * @version 1.0
 */

return array(
    'own' => array(
        'form' => array(
            'action_method'    => 'POST',
            'request_type'     => 'PG',
            //'redirect_uri'     => '~/index.html',
            'form_key_name'    => 'test_form_1',
            'form_id'          => 'test_form_1',
            'required_msg'     => 'Field "{combi_part}" is required to be filled!',
            'useMultiLanguage' => false,
            'fields' => array(
                'text1' => array(
                    'label'       => 'Text',
                    'input_type'  => 'textarea',
                    'is_required' => true,
                ),

                'date' => array(
                    'label'       => 'Date',
                    'input_type'  => 'text',
                    'is_required' => false,
                    'note'        => 'Date must be between 2012-12-10 and 2012-12-31',
                    'validate_rules' => array(
                        array(
                            'rule_name' => 'checkDate',
                            'error_msg' => 'Incorrect Value of Date',
                            'rule_data' => array('min_date' => '2012-12-10', 'max_date' => '2012-12-31'),
                        )
                    ),
                ),

                'variant' => array(
                    'label'       => 'Variant',
                    //'input_type'  => 'select',
                    'input_type'  => 'radio_group',
                    'data' => array(
                        array('value' => 'value1', 'text' => 'text1'),
                        array('value' => 'value2', 'text' => 'text2'),
                    ),
                    /*
                    'dataSource' => array(
                        'method' => 'getVariants',
                        //'class'  => 'Class name',
                    ),
                     */
                ),
            ),
            'design' => array(
                'note' => array(
                    'field' => '<div class="fieldNote">{TEXT}</div>',
                ),
            ),
        ), //form
    ), //'own'
);
?>