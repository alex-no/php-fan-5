<?php namespace app\frontend\main;
/**
 * Test class index
 * @version 1.1
 */
class index extends \project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        $sDir = \bootstrap::parsePath('{PROJECT}\app\frontend\main\\');
        $this->view->test_dir = str_replace('\\', \core\bootstrap\loader::DEFAULT_DIR_SEPARATOR, $sDir);
        $this->view->tests    = array(
            'test_view_data' => array(
                'ru' => 'Проверка установки данных для view',
                'en' => 'Test of setting view data',
            ),
            'test_service_request' => array(
                'ru'   => 'Main/Add request. Сервис request',
                'en'   => 'Main/Add request. Service of request',
                'link' => 'test_service_request/aaa-bbb',
            ),
            'test_format' => array(
                'ru' => 'Проверка форматов view',
                'en' => 'Test of view format',
            ),
        );
    } // function init

} // class \app\frontend\main\index
?>