<?php
/**
 * Main carcass meta
 * @version 05.01.002 (01.05.2013)
 */
return array(
    'own' => array(
        'tplVars' => array( // variable, which sets in template automaticaly
            'aMenu' => array(
                array('key' => 'db_up',          'name' => 'Update DataBase'),
                array('key' => 'create_entity',  'name' => 'Create entity'),
                array('key' => 'create_block',   'name' => 'Create block'),
                //array('key' => 'static_content', 'name' => 'Static content'),
                //array('key' => 'manage_admin',   'name' => 'Manage admin'),
                //array('key' => 'crawler',        'name' => 'Crawler'),
                //array('key' => 'link_list',      'name' => 'Link list'),
                //array('key' => 'counter',        'name' => 'Counter'),
                array('key' => 'upgrade_blocks',   'name' => 'Upgrade blocks'),
            ),
        ),
    ),
);
?>
