<?php
/**
 * Main carcass meta
 * @version 05.02.005 (12.02.2015)
 */
return array(
    'own' => array(
        'embeddedBlocks' => array( // Key - template var; Value - path to block
            'header'     => 'design/header',
            'main'       => '{MAIN}',
            'footer'     => 'design/footer',
        ),
        'externalCss' => array( // css files
            //'style' => array('/css/layout.css'),
        ),
    ),
);
?>