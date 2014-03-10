<?php namespace fan\app\frontend\extra;
/**
 * test class
 * @version 05.02.001 (10.03.2014)
 */
class test extends \fan\project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        $this->_broadcastEvent('someEvent', array('xxx-1' => 'yyy-1'));
    } // function init
} // class \fan\app\frontend\extra\test
?>