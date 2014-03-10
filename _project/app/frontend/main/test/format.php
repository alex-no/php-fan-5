<?php namespace fan\app\frontend\main;
/**
 * Test format class
 * @version 05.02.001 (10.03.2014)
 */
class format extends \fan\project\block\common\simple
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
} // class \fan\app\frontend\main\format
?>