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
        $this->view->hello = 'Hello world!';
    } // function init

} // class \app\www_global\main\index
?>