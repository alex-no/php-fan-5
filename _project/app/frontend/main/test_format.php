<?php namespace app\frontend\main;
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
        $this->view['sUserAgent'] = $this->request->get('User-Agent', 'H');

        if ($this->getViewType() == 'loader') {
            $this->view->setJson(array('zzz', 'val_1'), 111);
            $this->view->setText('Test loader');
        }
    } // function init

    public function getViewParserName()
    {
        $sFormat = $this->request->get('format', 'PAG');
        return in_array($sFormat, array('custom1', 'custom2')) ? $sFormat : parent::getViewParserName();
    } // function getViewParserName
} // class \app\frontend\main\test_format
?>