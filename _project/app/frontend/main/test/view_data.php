<?php namespace app\frontend\main\test;
/**
 * Test class test_view_data
 * @version 1.1
 */
class view_data extends \project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        $this->view->date = date('Y-m-d H:m:s');
        $this->view['user-agent'] = $this->request->get('User-Agent', 'H');
        $this->view->set('meta-data', $this->getMeta('some-data'));
    } // function init

} // class \app\frontend\main\test\view_data
?>