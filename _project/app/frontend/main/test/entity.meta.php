<?php
/**
 * Home page meta-data
 * @version 05.02.001 (10.03.2014)
 */
return array(
    'own' => array(
        'title' => 'PHP-FAN.5: PHP-FAN.5: Test of operation with DB by entity',

        'embeddedBlocks' => array(
            'check_db'     => 'extra/check_db',
            'entity_intro' => 'description/entity/entity_intro',
            'entity_t1'    => 'description/entity/entity_t1',
            'entity_t2'    => 'description/entity/entity_t2',
            'entity_t3'    => 'description/entity/entity_t3',
            'entity_t4'    => 'description/entity/entity_t4',
            'entity_t5'    => 'description/entity/entity_t5',
            'entity_t6'    => 'description/entity/entity_t6',
            'entity_t7'    => 'description/entity/entity_t7',
            'entity_t8'    => 'description/entity/entity_t8',
        ),
    ),

    'check_db' => array(
        'entity' => array(
            'test\test_primary' => array(
                'fields' => array('date', 'header', 'content', 'is_complete'),
                'data'   => array(1, 5, 16, 18, 19),
            ),
            'test\test_subtable' => array(
                'fields' => array('sub_content'),
                'data'   => array(1, 2, 3, 4, 5, 6, 7),
            ),
        ),
    ),

    'task_list' => array(
        'tasks' => array(
            'ru' => array(
                'В первом примере попробуйте менять id, указанный как второй аргумент функции gr, чтобы получить разные записи из БД',
                'Изучите SQL-запрос во втором примере. Попробуйте изменить условия, заданные в WHERE. Попробуйте менять параметры, передаваемые в метод <b>getRowByKey</b>, в том числе так, чтобы условие в SQL-запросе не выполнилось (т.е. нулевое значение id). Изучите результаты, полученные после каждого изменения',
                'Попробуйте различные методы, с разными параметрами в четвертом примере: <b>getArrayAssoc, getColumn, getArrayHash</b>. Проанализируйте полученные результаты',
                'В примерах связанных с модифификацией данных раскоментируйте по очереди, описанные куски кода и выполните заданные там действия',
                'Попробуйте получить запись из БД способом описанным в первом примере и изменить в ней значения, а затем сохранить в БД',
                '<b>Усложненное задание</b>: Не меняя порядка тестов в PHP-файле добейтесь чтобы изменения, которые вы вносите во второй части метода сразу же отображались в первой части (без дополнительной перезагрузки страницы). Для этого в шаблон передавайте не массив, а объект <b>row</b>. Преобразование объекта в массив, а затем в строку (с помощью функции <b>print_r</b>) делайте уже в самом шаблоне. Подсказку как это делать найдёте в примере работы с произвольной таблицей БД',
            ),
        ),
    ),
);
?>