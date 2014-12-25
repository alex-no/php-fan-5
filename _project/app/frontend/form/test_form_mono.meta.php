<?php
/**
 * Test format meta
 * @version 05.01.002 (01.05.2013)
 */

return array(
    'own' => array(
        'form' => array(
            'action_method'    => 'FILE',
            'request_type'     => 'PGF',
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
                    'maxlength'   => 10,
                    'validate_rules' => array(
                        array(
                            'rule_name' => 'checkDate',
                            'error_msg' => 'Incorrect Value of Date',
                            'rule_data' => array('min_date' => '2012-12-10', 'max_date' => '2012-12-31'),
                        )
                    ),
                ),

                'mv' => array(
                    'label'       => 'Multi-value',
                    'input_type'  => 'text',
                    'is_required' => false,
                    'maxlength'   => 4,
                    'depth'       => 1,
                    'validate_rules' => array(
                        array(
                            'rule_name' => 'strlen',
                            'error_msg' => 'Incorrect len',
                            'rule_data' => array('max_length' => 3),
                        )
                    ),
                ),
                'mv[2]' => array(
                    'label'       => 'Multi-value222',
                    'input_type'  => 'text',
                    'is_required' => false,
                    'maxlength'   => 8,
                    'depth'       => 1,
                    'validate_rules' => array(
                        array(
                            'rule_name' => 'strlen',
                            'error_msg' => 'Incorrect len2',
                            'rule_data' => array('max_length' => 8),
                        )
                    ),
                ),

                'variant' => array(
                    'label'       => 'Variant',
                    'input_type'  => 'select',
                    //'input_type'  => 'radio_group',
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
                'f' => array(
                    'label'       => 'Upload file',
                    'input_type'  => 'file',
                    'is_required' => false,
                    'depth'       => 1,
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