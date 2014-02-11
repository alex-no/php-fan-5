<?php namespace app\frontend\main\test;
/**
 * Test msg
 * @version 1.1
 */
class msg extends \project\block\common\simple
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
} // class \app\frontend\main\test\msg
?>