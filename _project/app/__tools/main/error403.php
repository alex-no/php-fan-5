<?php namespace fan\app\__tools\main;
/**
 * Error 403 class
 * @version 05.02.001 (10.03.2014)
 */
class error403 extends \fan\project\block\error\error403
{
    /**
     * Init block data
     */
    public function init()
    {
        $this->setViewVars('Error 403', 'Access to this page is denied.', 'Access is denied.');
    } // function init
} // class \fan\app\__tools\main\error403
?>