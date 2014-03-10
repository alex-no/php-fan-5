<?php namespace fan\project\view\parser;
/**
 * View parser XML-type
 *
 * This file is part PHP-FAN (php-framework from Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.001 (10.03.2014)
 */
class custom2 extends \fan\core\view\parser
{
    // ======== Static methods ======== \\
    /**
     * Get View-type
     * @return string
     */
    final static public function getType() {
        return 'custom-2';
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
        $sResult = "Plain text by \"tabulators\"\n\n" . $this->_makeText($this->aResult, 0);
        $this->_setHeaders($sResult, 'text/plain');
        return $sResult;
    } // function getFinalContent

    // ======== Protected methods ======== \\
    /**
     * Make Text
     * @param array $aData
     * @param numeric $nLevel
     * @return string
     */
    protected function _makeText($aData, $nLevel)
    {
        $sResult = '';
        foreach ($aData as $k => $v) {
            $sResult .= empty($sResult) ? '' : "\n";
            $sResult .= str_repeat("\t", $nLevel) . $k . ':';
            if (is_scalar($v)) {
                $sResult .= ' ' . $v;
            } elseif (is_array($v)) {
                $sResult .= "\n" . $this->_makeText($v, $nLevel + 1);
            }
        }
        return $sResult;
    } // function _makeText

    // ======== Protected methods ======== \\

} // class \fan\project\view\parser\custom2
?>