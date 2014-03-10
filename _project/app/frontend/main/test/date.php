<?php namespace fan\app\frontend\main;
/**
 * Test date
 * @version 05.02.001 (10.03.2014)
 */
class date extends \fan\project\block\common\simple
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
} // class \fan\app\frontend\main\date
?>