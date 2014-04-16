<?php
/**
 * Home page meta-data
 * @version 05.02.001 (10.03.2014)
 */
return array(
    'own' => array(
        'title' => 'PHP-FAN.5: Test of embeded block and view format',

        'embeddedBlocks' => array(
            'extra' => 'extra/test_format',
            'description' => 'description/format',
            'task_list'   => 'extra/task_list',
        ),
        //'default_view_format' => 'json',
    ),

    'task_list' => array(
        'tasks' => array(
            'ru' => array(
                'Изучите метод "getViewParserName" созданный в этом блоке. Посмотрите как здесь определяется custom-views, для которых нет правил в config-файле. Рассмотрите как определяется формат view в этом методе, если условия, указанные для custom, не выполняются',
                'Попробуйте указать различные значения по-умолчанию для формата view в мета-файле текущего блока (раскомментируйте значение default_view_format)',
                'Попробуйте изменять правила определения формата view в конфигурационном файле и произвести обращение',
                'Попробуйте создать свой custom-view или попросту именить те custom-view что даны здесь в качестве примера',
            ),
        ),
    ),
);
?>