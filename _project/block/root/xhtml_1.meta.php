<?php
/**
 * Basiс xhtml_1 Meta data
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
            'new' => array('/css/main.css'),
            'ie'  => array('/css/invalid_ie.css'),
            'ie6' => array('/css/invalid_ie6.css'),
        ),
    ),
);
?>