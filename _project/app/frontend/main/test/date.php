<?php namespace app\frontend\main\test;
/**
 * Test date
 * @version 1.1
 */
class date extends \project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        $oDate1 = service('date', '51.05.1965');
        $oDate2 = service('date', '20.05.1965 12:05:37');

        $this->view['date1']  = $oDate1;
        $this->view['shift1'] = $oDate1->shiftDate(60*60*24*1);

        $this->view['date2']  = $oDate2;
        $this->view['shift2'] = $oDate2->shiftDate(60*60*24*31);
    } // function init
} // class \app\frontend\main\test\date
?>