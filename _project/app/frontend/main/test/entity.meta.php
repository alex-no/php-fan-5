<?php
/**
 * Home page meta-data
 * @version 05.02.001 (10.03.2014)
 */
return array(
    'own' => array(
        'title' => 'PHP-FAN.5: PHP-FAN.5: Test of operation with DB by entity',

        'embeddedBlocks' => array(
            'check_db' => 'extra/check_db',
        ),
    ),
    'check_db' => array(
        'entity' => array(
            'test\test_primary' => array(
                'fields' => array('date', 'header', 'content', 'is_complete'),
                'data'   => array(1, 5, 16, 18, 19),
            ),
            'test\test_subtable' => array(
                'fields' => array('sub_content'),
                'data'   => array(1, 2, 3, 4, 5, 6, 7),
            ),
        ),
    ),
);
?>