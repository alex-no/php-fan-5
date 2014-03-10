<?php namespace fan\app\frontend\main;
/**
 * Test subscribing
 * @version 05.02.001 (10.03.2014)
 */
class subscribing extends \fan\project\block\common\simple
{
    /**
     * Test data
     * @var array
     */
    private $aTestData = array();

    /**
     * Subscribe for event after create
     */
    public function _postCreate()
    {
        parent::_postCreate();
        $this->_subscribeByName('someEvent', 'test1', 'testData');
        //$this->_subscribeByClass('someEvent', '\app\frontend\extra\test', 'testData');
        //$this->_subscribeForEvent('someEvent', 'testData');
    } // function _postCreate

    /**
     * Init block data
     */
    public function init()
    {
    } // function init

    public function testData($oBroadcaster, $aData)
    {
        $this->aTestData = $aData;
    } // function testData

    public function _preOutput()
    {
        parent::_preOutput();
        $this->view->event = print_r($this->aTestData, true);
    } // function _preOutput
} // class \fan\app\frontend\main\subscribing
?>