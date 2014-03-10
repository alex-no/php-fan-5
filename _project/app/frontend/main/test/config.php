<?php namespace fan\app\frontend\main;
/**
 * Test config
 * @version 05.02.001 (10.03.2014)
 */
class config extends \fan\project\block\common\simple
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
} // class \fan\app\frontend\main\_config
?>