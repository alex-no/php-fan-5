<?php
/**
 * Test of cache meta-data
 * @version 05.01.002 (01.05.2013)
 */
return array(
    'own' => array(
        'title'     => 'PHP-FAN.5: Test of cache',

        'embeddedBlocks' => array(
            'description' => 'description/cache',
            'task_list'   => 'extra/task_list',
        ),
        'additional_tasks' => array(
            'ru' => array(
                'Попробуйте создать еще один, новый тип кэша и провести с ним запись/чтение/удаление данных',
                'Проверьте как работает LIFETIME - уменьшите его значение до минимума. Сохраните данные в кэш и попробуйте их прочитать до и после наступления срока очистки',
                'Попробуйте устанавливать кэширование для некоторых тестовых страниц или блоков на этих страницах',
            ),
        ),

        'some-data' => 'Some data from meta',
    ),

    'task_list' => array(
        'tasks' => array(
            'ru' => array(
                'Попробуйте несколько раз перезагрузить данную страницу и убедитесь, что некоторые данные в результатах теста изменяются. Затем закомментируйте те строки в php-коде, где делается запись в кэш (но не трогайте строки, где данные читаются из кэша) и убедитесь, что у вас отображаются данные из кэша',
                'Попробуйте раскоментировать строки где делается удаление из кэша и проверьте удаленные данные',
            ),
        ),
    ),
);
?>