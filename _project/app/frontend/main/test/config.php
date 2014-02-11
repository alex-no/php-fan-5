<?php namespace app\frontend\main\test;
/**
 * Test config
 * @version 1.1
 */
class config extends \project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        $oApp = service('application');
        $this->view['name1'] = $oApp->getProjectName();
        $this->view['name2'] = $oApp->getConfig('PROJECT_NAME', 'None');
        // Note: Try to change Project name in "service.ini" and reload page
        //   Next step: Try to change ENGINE for "config_cache" in "bootstrap.ini" and repeat previous test
    } // function init
} // class \app\frontend\main\test\_config
?>