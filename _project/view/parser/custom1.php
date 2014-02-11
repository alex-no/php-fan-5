<?php namespace project\view\parser;
/**
 * View parser Custom1-type
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.001 (29.09.2011)
 */
class custom1 extends \core\view\parser
{
    // ======== Static methods ======== \\
    /**
     * Get View-type
     * @return string
     */
    final static public function getType() {
        return 'custom-1';
    } // function getType

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    /**
     * Get Final Content Code
     * @return string
     */
    public function getFinalContent()
    {
        $sResult  = "Plain text by \"print_r\"\n\n";
        $sResult .= print_r($this->aResult, true);
        $this->_setHeaders($sResult, 'text/plain');
        return $sResult;
    } // function getFinalContent

    // ======== Protected methods ======== \\

} // class \project\view\parser\custom1
?>