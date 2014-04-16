<?php namespace fan\app\frontend\description;
/**
 * form2 class
 * @version 05.02.001 (10.03.2014)
 */
class form2 extends \fan\project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        $aPaths = \fan\project\service\reflector::instance()->getParentPaths($this->_getBlock('test_form'));
        $this->view->form_block = pathinfo(reset($aPaths));
        //$this->view->form_block = realpath(end($aPaths));
    } // function init
} // class \fan\app\frontend\description\form2
?>