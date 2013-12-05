<?php namespace app\__log_viewer\main;
/**
 * Error 404 class
 * @version 1.1
 */
class error404 extends \project\block\error\error404
{
    /**
     * Init block data
     */
    public function init()
    {
        $this->setViewVars('Error 404', 'Such page is\'t available.', 'Page is\'t available.');
    } // function init
} // class \app\__log_viewer\main\error404
?>