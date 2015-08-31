<?php namespace fan\app\frontend\main;
/**
 * Block class index
 * @version 05.02.007 (31.08.2015)
 */
class index extends \fan\project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        $sDir = \bootstrap::parsePath('{PROJECT}/app/frontend/main/test/');
        $this->view->test_dir = str_replace('\\', \fan\core\bootstrap\loader::DEFAULT_DIR_SEPARATOR, $sDir);
        $this->view->tests = $this->getMeta('tests');
    } // function init

} // class \fan\app\frontend\main\index
?>