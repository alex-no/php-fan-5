<?php namespace fan\project\block\carcass;
/**
 * Block of carcass
 * @version of file: 05.02.001 (10.03.2014)
 */
class common extends \fan\project\block\base
{

    /**
     * Special message before main content
     * @var string
     */
    protected $sMessBefore = '';

    /**
     * Special message after main content
     * @var string
     */
    protected $sMessAfter = '';

    /**
     * Set special message before main content
     * @param string $sMess - message
     * @param number $nPosition - position ( -1 - before, 0 - replase, 1 - after)
     * @param string $sType
     */
    public function setMessageBefore($sMess, $nPosition = 1, $sType = 'error')
    {
        if ($sMess) {
            $sMess = '<div class="' . $sType . 'Msg">' . $sMess . '</div>';
            $this->sMessBefore = !$nPosition ? $sMess : ($nPosition > 0 ? $this->sMessBefore . $sMess : $sMess . $this->sMessBefore);
            if($this->sMessBefore) {
                $this->_setViewVar('messBefore', '<div id="messBefore">' . $this->sMessBefore . '</div>');
            }
        }
    } // function set_message_before

    /**
     * Set special message after main content
     * @param string $sMess - message
     * @param number $nPosition - position ( -1 - before, 0 - replase, 1 - after)
     * @param string $sType
     */
    public function setMessageAfter($sMess, $nPosition = 1, $sType = 'error')
    {
        if ($sMess) {
            $sMess = '<div class="' . $sType . 'Msg">' . $sMess . '</div>';
            $this->sMessAfter = !$nPosition ? $sMess : ($nPosition > 0 ? $this->sMessAfter . $sMess : $sMess . $this->sMessAfter);
            if($this->sMessAfter) {
                $this->_setViewVar('messAfter', '<div id="messAfter">' . $this->sMessAfter . '</div>');
            }
        }
    } // function set_message_after
} // class \fan\project\block\carcass\common
?>