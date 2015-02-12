<?php
/**
 * Basiс xhtml_1 Meta data
 * @version of file: 05.02.005 (12.02.2015)
 */
return array(
    /**
     * Meta data for embedded blocks
     */
    'common' => array(
    ),
    /**
     * Meta data for curent carcass block
     */
    'own' => array(
        'template' => 'xhtml_10_transitional.tpl',
        //'template' => 'xhtml_10_strict.tpl',

        'externalCss' => array(
            'style' => array('/css/main.css'),
            'ie'    => array('/css/invalid_ie.css'),
            'ie6'   => array('/css/invalid_ie6.css'),
        ),
    ),
);
?>