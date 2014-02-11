<?php namespace app\frontend\main\test;
/**
 * Test subscribing
 * @version 1.1
 */
class subscribing extends \project\block\common\simple
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
} // class \app\frontend\main\test\subscribing
?>