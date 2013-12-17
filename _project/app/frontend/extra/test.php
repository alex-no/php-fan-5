<?php namespace app\frontend\extra;
/**
 * test class
 * @version 1.1
 */
class test extends \project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        $this->_broadcastEvent('someEvent', array('xxx-1' => 'yyy-1'));
    } // function init
} // class \app\frontend\extra\test
?>