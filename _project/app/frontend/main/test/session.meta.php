<?php
/**
 * Test of session meta-data
 * @version 05.01.002 (01.05.2013)
 */
return array(
    'own' => array(
        'title'     => 'PHP-FAN.5: Test of session',

        'embeddedBlocks' => array(
            'description' => 'description/session',
            'task_list'   => 'extra/task_list',
        ),

        'some-data' => 'Some data from meta',
    ),

    'task_list' => array(
        'tasks' => array(
            'ru' => array(
                'Попробуйте несколько раз перезагрузить данную страницу и убедитесь, что данные в результатах теста изменяются. Затем закомментируйте те строки в php-коде, где делается запись в сессию (но не трогайте строки, где данные читаются из сессии) и убедитесь, что у вас отображаются данные из предыдущего сеанса',
                'Попробуйте изменять namespace для custom-сессии и сохранять/получать данные из разных namespace',
                'Попробуйте использовать автоочистку сессии, описанную в подразделе "<b>Изоляция на уровне блока</b>"',
            ),
        ),
    ),
);
?>