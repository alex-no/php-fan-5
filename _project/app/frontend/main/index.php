<?php namespace fan\app\frontend\main;
/**
 * Test class index
 * @version 05.02.001 (10.03.2014)
 */
class index extends \fan\project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        $this->view->hello = 'Hello world!';
    } // function init

} // class \fan\app\frontend\main\index
?>