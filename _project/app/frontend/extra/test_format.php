<?php namespace app\frontend\extra;
/**
 * Test format class
 * @version 1.1
 */
class test_format extends \project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        $this->view->xxx = 'embedded';
        $this->view->yyy = array('aaa' => 1, 'bbb');
    } // function init
} // class \app\frontend\extra\test_format
?>