<?php namespace core\view\parser;
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
 * @version of file: 05.001 (29.09.2011)
 */
class xml extends \core\view\parser
{
    // ======== Static methods ======== \\
    /**
     * Get View-type
     * @return string
     */
    final static public function getType() {
        return 'xml';
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
        $oDom = new \DOMDocument('1.0', 'iso-8859-1');
        $oEelement = new \DOMElement($this->oRootBlock->getBlockName());
        $oEelement = $oDom->appendChild($oEelement);
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
     * @return \core\view\parser\xml
     */
    protected function _makeDomElements(\DOMNode $oParent, $aData)
    {
        foreach ($aData as $k => $v) {
            $oEelement = new \DOMElement($k);
            $oEelement = $oParent->appendChild($oEelement);
            if (is_scalar($v)) {
                $oEelement->appendChild(new \DOMText($v));
            } elseif (is_array($v)) {
                $this->_makeDomElements($oEelement, $v);
            }
        }
        return $this;
    } // function _makeDomElements

} // class \core\view\parser\xml
?>