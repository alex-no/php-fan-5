<?php namespace fan\app\__tools\main;
/**
 * Error 404 class
 * @version 05.02.001 (10.03.2014)
 */
class error404 extends \fan\project\block\error\error404
{
    /**
     * Init block data
     */
    public function init()
    {
        $this->setViewVars('Error 404', 'Such page is\'t available.', 'Page is\'t available.');
    } // function init
} // class \fan\app\__tools\main\error404
?>