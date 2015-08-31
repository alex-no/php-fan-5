<?php namespace fan\project\cli\timer;
/**
 * Timer send packet of error email
 * @version 05.02.007 (31.08.2015)
 */
class error_email extends \fan\core\base\timer_program
{
    /**
     * send Email
     */
    public function sendPacketEmais()
    {
        service('error')->sendPacketEmais();
    } // function sendPacketEmais

} // class \fan\project\cli\timer\error_email
?>