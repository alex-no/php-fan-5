<?php namespace app\__tools\main;
/**
 * Error 403 class
 * @version 1.1
 */
class error403 extends \project\block\error\error403
{
    /**
     * Init block data
     */
    public function init()
    {
        $this->setViewVars('Error 403', 'Access to this page is denied.', 'Access is denied.');
    } // function init
} // class \app\__tools\main\error403
?>