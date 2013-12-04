<?php
/**
 * Basiс xhtml_1 Meta data
 */
return array(
    'own' => array(
        'browserClasses' => array (
            'isOpera' => array(
                'regExp'   => '/Opera\W*(\d+(:?\.\d+)?)/',
                'olderVer' => array('isOpera8' => 9),
            ),
        ),
        'cache' => array(
            'mode' => 1,
        ),
    ),
);
?>