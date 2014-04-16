<?php namespace fan\app\frontend\description;
/**
 * form1 class
 * @version 05.02.001 (10.03.2014)
 */
class form1 extends \fan\project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        $this->view->meta_example = realpath(\bootstrap::parsePath('{PROJECT}/../doc/meta_example/form.meta.php'));
    } // function init
} // class \fan\app\frontend\description\form1
?>