<?php namespace fan\core\view\parser;
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class xml extends \fan\core\view\parser
{
    // ======== Static methods ======== \\
    /**
     * Get View-Format
     * @return string
     */
    final static public function getFormat() {
        return 'xml';
    } // function getFormat

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    /**
     * Get Final Content Code
     * @return string
     */
    public function getFinalContent()
    {
        $oDom = new \DOMDocument('1.0', 'iso-8859-1');
        $oEelement = new \DOMElement($this->oRootBlock->getBlockName());
        $oDom->appendChild($oEelement);
        $this->_makeDomElements($oEelement, $this->aResult);
        $sResult = $oDom->saveXML();
        $this->_setHeaders($sResult, 'text/xml', '');
        return $sResult;
    } // function getFinalContent

    // ======== Protected methods ======== \\
    /**
     * Make Dom Elements
     * @param \DOMNode $oParent
     * @param type $aData
     * @return \fan\core\view\parser\xml
     */
    protected function _makeDomElements(\DOMNode $oParent, $aData)
    {
        foreach ($aData as $k => $v) {
            if (is_numeric($k)) { // It is mend. //ToDo: Do numeric keys as several elements with the same name
                continue;
            }
            $oEelement = new \DOMElement($k);
            $oParent->appendChild($oEelement);
            if (is_scalar($v)) {
                $oEelement->appendChild(new \DOMText($v));
            } elseif (is_array($v)) {
                $this->_makeDomElements($oEelement, $v);
            }
        }
        return $this;
    } // function _makeDomElements

} // class \fan\core\view\parser\xml
?>