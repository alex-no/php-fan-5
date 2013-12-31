<?php
/**
 * Home page meta-data
 * @version 1.0
 */
return array(
    'own' => array(
        'title' => 'PHP-FAN.5: List of test-files',

        /** /
        'carcass'     => '{CARCASS}/home_carcass',
        'externalCss' => array( // css files
            'new' => array('/css/home.css'),
        ),/**/

        'tests' => array(
            'test_view_data' => array(
                'ru' => 'Проверка установки данных для view',
                'en' => 'Test of setting view data',
            ),
            'test_service_request' => array(
                'ru'   => 'Main/Add request. Сервис request',
                'en'   => 'Main/Add request. Service of request',
                'link' => 'test_service_request/aaa-bbb',
            ),
            'test_format' => array(
                'ru' => 'Проверка форматов view',
                'en' => 'Test of view format',
            ),
            'test_session' => array(
                'ru' => 'Test of session',
                'en' => 'Проверка сессий',
            ),
            'test_cache' => array(
                'ru' => 'Проверка работы с кэшем',
                'en' => 'Test of operation with cache',
            ),
/*
            'test_config' => array(
                'ru' => '',
                'en' => '',
            ),
            'test_date' => array(
                'ru' => '',
                'en' => '',
            ),
            'test_entity' => array(
                'ru' => '',
                'en' => '',
            ),
            'test_form' => array(
                'ru' => '',
                'en' => '',
            ),
            'test_msg' => array(
                'ru' => '',
                'en' => '',
            ),
            'test_role' => array(
                'ru' => '',
                'en' => '',
            ),
            'test_subscribing' => array(
                'ru' => '',
                'en' => '',
            ),
            'test_user' => array(
                'ru' => '',
                'en' => '',
            ),
            'test_zend' => array(
                'ru' => '',
                'en' => '',
            ),
*/
        ),

        /**
         * All parameters below it is possible to set as "own"-part, amd in "common"-part
         * /
        'tplVars' => array( // variable, which sets in template automaticaly
            'tplVar1' => 'Value of variable 1',
            'tplVar2' => 'Value of variable 2',
        ),/**/
    ),
    /** /
    'common' => array(
        'test0' => 1,
    ),/**/
);
?>