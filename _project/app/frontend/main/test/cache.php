<?php namespace fan\app\frontend\main;
/**
 * Test cache
 * @version 05.02.001 (10.03.2014)
 */
class cache extends \fan\project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        // Try to use caching by file
        // Пробуйте использовать кеширование в специальный файл
        $oCache1 = service('cache', 'common_by_file');
        // Comment this row after first call and try to get data from cache
        // Закомментируйте эту строку после первого вызова и попробуйте получить данные из кэша
        $oCache1->set('t1', array('a' => 1, 'b' => 2));
        $this->view['file_value'] = print_r($oCache1->get('t1'), true);
        $this->view['file_meta']  = print_r($oCache1->getMeta('t1'), true);
        // Uncomment this row after several calls and try to delete data from cache
        // Раскомментируйте эту строку после нескольких вызовов и попробуйте удалить данные из кэша
        //$oCache1->delete('t1');

        if (class_exists('\Memcache')) {
            // Try to use caching by "memcache"
            // Пробуйте использовать кеширование с помощью "memcache"
            $oCache2 = service('cache', 'common_by_memcache');
            // Comment this row after first call and try to get data from cache
            // Закомментируйте эту строку после первого вызова и попробуйте получить данные из кэша
            $oCache2->set('t2', array('x' => 8, 't' => 9));
            $this->view['memory_value'] = print_r($oCache2->get('t2'), true);
            $this->view['memory_meta']  = print_r($oCache2->getMeta('t2'), true);
            // Uncomment this row after several calls and try to delete data from cache
            // Раскомментируйте эту строку после нескольких вызовов и попробуйте удалить данные из кэша
            //$oCache2->delete('t2');
            $this->view['is_memcache']  = true;
        }
    } // function init

    /**
     * Get Title
     * @return string
     */
    public function getTitle()
    {
        return 'PHP-FAN.5: Test of cache';
    } // function getTitle
} // class \fan\app\frontend\main\cache
?>