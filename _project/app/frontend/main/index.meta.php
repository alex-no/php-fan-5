<?php
/**
 * Home page meta-data
 * @version 1.0
 */
return array(
    'own' => array(
        'title' => 'Home page',

        /** /
        'carcass'     => '{CARCASS}/home_carcass',
        'externalCss' => array( // css files
            'new' => array('/css/home.css'),
        ),/**/

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