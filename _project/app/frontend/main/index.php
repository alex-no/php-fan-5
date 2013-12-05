<?php namespace app\www_global\main;
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
        $this->view['User-Agent'] = $this->request->get('User-Agent', 'H');
        $this->view->date = date('Y-m-d H:m:s');
    } // function init

} // class \app\www_global\main\index
?>