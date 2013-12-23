<?php namespace app\frontend\main;
/**
 * Test service_request
 * @version 1.1
 */
class test_service_request extends \project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        //$oRequest = service('request');
        $oRequest = $this->request;
        /* @var $oRequest \core\service\request */
        $this->view->request0 = $this->array_values($oRequest->getAll('H'));

        $this->view->request1 = $this->array_values($oRequest->get('aaa', 'A'));
        $this->view->request2 = $this->array_values($oRequest->getAll('A'));
        $this->view->request3 = $this->array_values($oRequest->getAll('H'));

        $this->view->request4 = $this->array_values($oRequest->get('aaa', 'AG'));

    } // function init

    /**
     * Convert values to show format
     * @param mixed $mSource
     * @return string
     */
    public function array_values($mSource)
    {
        $sResult = print_r($mSource, true);
        return is_array($mSource) ? substr($sResult, 8, -2) : $mSource;

    } // function array_values

    /**
     * Get Title
     * @return string
     */
    public function getTitle()
    {
        return 'PHP-FAN.5: Main/Add request. Service of request';
    } // function getTitle
} // class \app\frontend\main\test_service_request
?>