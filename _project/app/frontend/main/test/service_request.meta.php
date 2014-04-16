<?php
/**
 * Home page meta-data
 * @version 05.01.002 (01.05.2013)
 */
return array(
    'own' => array(
        'title'     => 'PHP-FAN.5: Main/Add request. Service of request',

        'embeddedBlocks' => array(
            'description' => 'description/service_request',
            'task_list'   => 'extra/task_list',
        ),

        'some-data' => 'Some data from meta',
    ),
    'task_list' => array(
        'tasks' => array(
            'ru' => array(
                'Попробуйте произвольно менять значение Additional Request, при вызове текущей страницы, и посмотрите как изменится значение $request3 выводимое здесь',
                'Попробуйте добавить GET-парамеры в текущий запрос и посмотрите как изменится значение $request4. Попробуйте указать указать GET и Additional Request с одинаковыми ключами, а затем поменяйте приоритность их просмотра для $request4 и посмотрите на результат',
                'Попробуйте получать данные из других источников, например из HTTP-заголовков',
            ),
        ),
    ),
);
?>