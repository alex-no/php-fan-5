<?php namespace fan\app\frontend\main;
/**
 * Error404 class
 * @version 05.02.001 (10.03.2014)
 */
class error404 extends \fan\project\block\error\error404
{
    /**
     * Init block data
     */
    public function init()
    {
        $this->setViewVars('Error 404', 'Requested page is not found.', 'Error 404. Requested page isn\'t found.');
    } // function init
} // class \fan\app\frontend\main\error404
?>