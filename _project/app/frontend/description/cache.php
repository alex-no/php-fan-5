<?php namespace fan\app\frontend\description;
/**
 * cache class
 * @version 05.02.001 (10.03.2014)
 */
class cache extends \fan\project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        $this->view->adv = $this->container->isAdvanced();
    } // function init
} // class \fan\app\frontend\description\cache
?>