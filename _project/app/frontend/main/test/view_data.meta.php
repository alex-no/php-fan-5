<?php
/**
 * Home page meta-data
 * @version 05.01.002 (01.05.2013)
 */
return array(
    'own' => array(
        'title'     => 'PHP-FAN.5: Test of setting view data',

        'embeddedBlocks' => array(
            'description' => 'description/view_data',
            'task_list'   => 'extra/task_list',
        ),

        'some-data' => 'Some data from meta',
    ),
    'task_list' => array(
        'tasks' => array(
            'ru' => array(
                'Попробуйте произвольно добавить другие данные во view и вывести их значения в шаблоне',
                'Попробуйте задавать и выводить данные разными способами',
            ),
        ),
    ),
);
?>