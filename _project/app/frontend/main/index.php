<?php namespace app\frontend\main;
/**
 * Test class index
 * @version 1.1
 */
class index extends \project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        $sDir = \bootstrap::parsePath('{PROJECT}/app/frontend/main/test/');
        $this->view->test_dir = str_replace('\\', \core\bootstrap\loader::DEFAULT_DIR_SEPARATOR, $sDir);
        $this->view->tests = $this->getMeta('tests');
    } // function init

} // class \app\frontend\main\index
?>