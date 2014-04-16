<?php namespace fan\app\frontend\design;
/**
 * footer class
 * @version 05.02.003 (16.04.2014)
 */
class footer extends \fan\project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        $this->view->cyear = date('Y');
    } // function init

} // class \fan\app\frontend\design\footer
?>