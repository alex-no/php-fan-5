<?php namespace fan\app\frontend\main;
/**
 * Test class test_view_data
 * @version 05.02.001 (10.03.2014)
 */
class view_data extends \fan\project\block\common\simple
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

} // class \fan\app\frontend\main\view_data
?>