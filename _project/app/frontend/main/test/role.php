<?php namespace app\frontend\main\test;
/**
 * Test role
 * @version 1.1
 */
class role extends \project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        $oRole = service('role');

        //$oRole->setSessionRoles(array('test2', 'test3'), 60);

        $this->view['session'] = print_r($oRole->getSessionRoles(), true);
        $this->view['static']  = print_r($oRole->getStaticRoles(), true);
        $this->view['all']     = print_r($oRole->getRoles(), true);

        //$oUser = getUser('tool');
        //$oUser = getUser('tool', 'test_usr');
        //$oUser->checkPassword('123');
        //$oUser->setCurrent();

        $oUser = getUser();

        $this->view['session_U'] = print_r($oRole->getSessionRoles(), true);
        $this->view['static_U']  = print_r($oRole->getStaticRoles(), true);
        $this->view['all_U']     = print_r($oRole->getRoles(), true);
        $this->view['userId']    = empty($oUser) ? '' : $oUser->getId();
    } // function init
} // class \app\frontend\main\test\role
?>