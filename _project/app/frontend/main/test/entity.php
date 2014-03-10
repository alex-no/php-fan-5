<?php namespace fan\app\frontend\main;
/**
 * Test session
 * Data for this test:
 *  * DB-sheme - test_entity.mwb
 *  * DB-dump  - test_entity.sql
 * @version 05.02.001 (10.03.2014)
 */
class entity extends \fan\project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        $isCorrect = $this->_getBlock('check_db')->isCorrect();
        $this->view->isCorrect = $isCorrect;
        if (!$isCorrect) {
            return;
        }

        // ----------- Get DB-data / Получение DB-данных ----------- \\
        // Get row with id=16
        // Получить строку с id=16
        $oRow1 = gr('test\test_primary', 16);
        $this->view->row_by_id = print_r($oRow1->toArray(), true);

        // Get row by SQL-key "less_than"
        // Получить строку с помощью SQL-ключа "less_than"
        $oRow2  = ge('test\test_primary')->getRowByKey('less_than', array('id' => 18), 1, 'ORDER BY `id_test_primary` DESC');
        $this->view->row_by_key = print_r($oRow2->toArray(), true);

        // Get rowset by SQL-key "get_comby_rowset" (complex SQL-request)
        // Получить rowset с помощью SQL-ключа "get_comby_rowset" (комплексный SQL-запрос)
        $oRowset = ge('test\test_subtable')->getRowsetByKey('get_comby_rowset', array('date' => array('2014-01-01', '2014-01-02')));
        $this->view->rowset_by_key  = print_r($oRowset->toArray(true), true);
        $this->view->hash_by_rowset = print_r($oRowset->getArrayHash('__date', 'sub_content'), true);

        // Get Top row from linked table
        // Получить Top row из связанной таблицы
        $oRow3   = gr('test\test_subtable', 2);
        $oTopRow = $oRow3->getTopRow('id_test_primary');
        $this->view->top_row = $oTopRow ? print_r($oTopRow->toArray(), true) : 'null';

        // Get Bottom rowset by linked table
        // Получить Bottom rowset с помощью связанной таблицы
        $oRow4         = gr('test\test_primary', 1);
        $oBottomRowset = $oRow4->getBottomRowset('test_subtable');
        $this->view->bottom_rowset = $oBottomRowset ? print_r($oBottomRowset->toArray(true), true) : 'null';

        // Table description for "test_subtable".
        // Описание таблицы "test_subtable".
        $oDescr = ge('test\test_subtable')->description;
        /* @var $oDescr \fan\core\service\entity\description */
        $this->view->comment     = $oDescr->comment;
        $this->view->description = print_r($oDescr->toArray(), true);

        // ----------- Modify DB-data / Модифицирование DB-данных ----------- \\
        // Insert new row by entity "test"
        // Вставка новой строки при помощи entity "test"
        /*
        gr('test\test_subtable')->setFields(array(
            'id_test_primary' => 1,
            'sub_content'     => substr(md5(microtime()), 0, 8),
        ), true);
        /**/

        // UPDATE value of field "header", table "test_primary", for row id=1
        // UPDATE значения поля "header", таблицы "test_primary" для строки с id=1
        /*
        $oRow5 = gr('test\test_primary')->initIdOnly(1);
        $oRow5->set('header', md5(microtime()))->save();
        /**/

        // Set new commentary by entity "test_subtable"
        // Установка нового коментария припомощи entity "test_subtable"
        /*
        ge('test\test_subtable')->description->comment = 'La-la-la: ' . md5(microtime());
        /**/

        // Get Arbitrary entity by common class
        // Получить произвольный entity с помощью общего класса
        /*
        $oEntity = service('entity')->getAnonymous('\fan\model\any', array('tableName' => 'test_primary'));
        $oRow6 = $oEntity->getRowById(18);
        $oRow6->set('header', 'Random: ' . rand(1,1000))->save();
        $this->view->test_arbitrary = $oRow6;
        /**/

        // ----------- Special entity operation / Специальные операции с entity ----------- \\

        // Serialize/unserialize object of row-data
        // Сериализовать/десериализовать объект строки данных
        $sSerialized = serialize($oRow1);
        $this->view->serialize = $sSerialized;
        $oRow10 = unserialize($sSerialized);
        $this->view->test_unserialize = print_r($oRow10->toArray(), true);

    } // function init

} // class \fan\app\frontend\main\entity
?>