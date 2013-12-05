<?php
/**
 * Main carcass meta
 * @version 1.0
 */
return array(
    'own' => array(
        'embeddedBlocks' => array( // Key - template var; Value - path to block
            //'header'     => '{CAPP}/design/header',
            //'columnLeft' => '{CAPP}/design/column_left',
            'main'       => '{MAIN}',
            'footer'     => '{CAPP}/design/footer',
        ),
        'externalCss' => array( // css files
            'new' => array('/css/layout.css'),
        ),
        'template' => 'work.tpl',
    ),
);
?>