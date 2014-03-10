<?php namespace fan\app\frontend\extra;
/**
 * Test format class
 * @version 05.02.001 (10.03.2014)
 */
class test_format extends \fan\project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        $this->view->xxx = 'embedded';
        $this->view->yyy = array('aaa' => 1, 'bbb');
    } // function init
} // class \fan\app\frontend\extra\test_format
?>