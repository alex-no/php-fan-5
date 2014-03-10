<?php namespace fan\app\frontend\main;
/**
 * Test msg
 * @version 05.02.001 (10.03.2014)
 */
class msg extends \fan\project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        $this->view['simple_msg'] = msg('OTHER_TEST');
        $this->view['comby_msg']  = msg('ERROR_FIELD_LABEL_IS_REQUIRED', 'OTHER_TEST');
        $this->view['tag_msg']    = msg('OTHER_TEST_TAG');
    } // function init
} // class \fan\app\frontend\main\msg
?>