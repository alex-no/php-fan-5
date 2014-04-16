<?php namespace fan\app\frontend\main;
/**
 * Test session
 * @version 05.02.001 (10.03.2014)
 */
class session extends \fan\project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        // Comment each "set-operation" after first call and try to get data from session
        // Закомментируйте каждую операцию "set" после первого вызова и попробуйте получить данные из сессии

        // Try to use standart session in namespace of current application
        // Используйте сессию локализованную в пространстве имен текущего приложения
        $oAppSes = service('session');
        $oAppSes->set('test1', 'Random number: ' . rand(10, 99));
        $this->view->test1 = $oAppSes->get('test1');

        // Try to use session localised in class of current block
        // Используйте сессию локализованную в классе текущего блока
        $this->setSessionData('test2', 'Current date: ' . date('Y-m-d H:i:s'));
        $this->view->test2 = $this->getSessionData('test2');

        // Try to use custom session is available in any point of project
        // Используйте пользовательскую сессию доступную в любой точке проекта
        $oCustomSes = service('session', 'ns1');
        $oCustomSes->set('test3', 'Random md5: ' . md5(microtime()));
        $this->view->test3 = $oCustomSes->get('test3');


    } // function init
} // class \fan\app\frontend\main\session
?>