<?php
/**
 * Home page meta-data
 * @version 05.01.002 (01.05.2013)
 */
return array(
    'own' => array(
        'title' => 'PHP-FAN.5: Presentation and testing',

        /** /
        'carcass'     => '{CARCASS}/home_carcass',
        'externalCss' => array( // css files
            'new' => array('/css/home.css'),
        ),/**/

        'tests' => array(
            'view_data' => array(
                'ru' => 'Проверка установки данных для view',
                'en' => 'Test of setting view data',
            ),
            'service_request' => array(
                'ru'   => 'Main/Add request. Сервис request',
                'en'   => 'Main/Add request. Service of request',
                'link' => 'service_request/aaa-bbb',
            ),
            'format' => array(
                'ru' => 'Проверка форматов view',
                'en' => 'Test of view format',
            ),
            'session' => array(
                'ru' => 'Test of session',
                'en' => 'Проверка сессий',
            ),
            'cache' => array(
                'ru' => 'Проверка работы с кэшем',
                'en' => 'Test of operation with cache',
            ),
/*
            'config' => array(
                'ru' => '',
                'en' => '',
            ),
            'date' => array(
                'ru' => '',
                'en' => '',
            ),
*/
            'entity' => array(
                'ru' => 'Проверка работы с базой данных с помощью entity',
                'en' => 'Test of operation with the database using entity',
            ),
            'form' => array(
                'ru' => 'Тестирование HTML-форм',
                'en' => 'Test HTML-form',
            ),
/*
            'msg' => array(
                'ru' => '',
                'en' => '',
            ),
            'role' => array(
                'ru' => '',
                'en' => '',
            ),
            'subscribing' => array(
                'ru' => '',
                'en' => '',
            ),
            'user' => array(
                'ru' => '',
                'en' => '',
            ),
            'zend' => array(
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