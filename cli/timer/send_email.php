<?php namespace fan\project\cli\timer;
/**
 * Timer manager service
 * @version 05.02.007 (31.08.2015)
 */
class send_email extends \fan\core\base\timer_program
{

    /**
     * send Email
     */
    public function sendEmail($sSubject, $sMessage, $sMailTo, $sNameTo, $aMailCC)
    {
        $oServEmail = service('email', "timer_email");
        $oServEmail->clear_all_recipients();
        if ($aMailCC) {
            foreach ($aMailCC as $v) {
                list($sEmail, $sName) = explode("/", $v, 2);
                $oServEmail->add_cc($sEmail, $sName);
            }
        }
        $oServEmail->send($sSubject, $sMessage, $sMailTo, $sNameTo);

    } // function sendEmail

} // class \fan\project\cli\timer\send_email
?>