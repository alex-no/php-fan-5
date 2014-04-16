<?php
/**
 * Main carcass meta
 * @version 05.02.001 (10.03.2014)
 */
return array(
    'own' => array(
        'embeddedBlocks' => array( // Key - template var; Value - path to block
            'header'     => 'design/header',
            'main'       => '{MAIN}',
            'footer'     => 'design/footer',
        ),
        'externalCss' => array( // css files
            //'new' => array('/css/layout.css'),
        ),
    ),
);
?>