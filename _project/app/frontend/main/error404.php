<?php namespace app\frontend\main;
/**
 * Error404 class
 * @version 1.1
 */
class error404 extends \project\block\error\error404
{
    /**
     * Init block data
     */
    public function init()
    {
        $this->setViewVars('Error 404', 'Requested page is not found.', 'Error 404. Requested page isn\'t found.');
    } // function init
} // class \app\frontend\main\error404
?>