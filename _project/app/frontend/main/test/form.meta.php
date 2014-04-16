<?php
/**
 * Meta data of test form block
 *
 * This file is part PHP-FAN (php-framework of Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.001 (10.03.2014)
 */
return array(
    'own' => array(
        'title' => 'PHP-FAN.5: Test HTML-form',

        'embeddedBlocks' => array(
            'test_form'    => 'form/test_form_mono',
            'description1' => 'description/form1',
            'description2' => 'description/form2',
            'task_list'    => 'extra/task_list',
        ),
    ), //'own'

    'task_list' => array(
        'tasks' => array(
            'ru' => array(
                'Попробуйте в мета-данных изменить \'input_type\' для поля \'variant\' с \'select\' на \'radio_group\'. В качестве примера соответствующая строка в meta-файле закоментирована',
                'Данные для поля \'variant\' сейчас берутся непосредственно из meta-файла. Попробуйте получить их с помощью метода getVariants, описанного высше',
                'Попробуйте изменить граничные значения для валидации поля date, а затем введите запредельное значение и посмотрите на результат',
                'Попробуйте изменить в шаблоне порядок элементов для поля date',
                'Добавьте в форму одно или несколько своих полей - их надо прописать meta-файле и в шаблоне',
            ),
        ),
    ),
);
?>