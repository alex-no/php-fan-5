<?php namespace fan\core\view\parser;
/**
 * View parser HTML-type
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
class html extends \fan\core\view\parser
{
    // ======== Static methods ======== \\
    /**
     * Get View-Format
     * @return string
     */
    final static public function getFormat() {
        return 'html';
    } // function getFormat

    /**
     * Get View-Router for block
     * @param \fan\core\block\base $oBlock
     * @return \fan\core\view\router\simple
     */
    static public function getRouter(\fan\core\block\base $oBlock) {
        return new \fan\project\view\router\html($oBlock);
    } // function getRouter

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    /**
     * Get Final Content Code
     * @return string
     */
    public function getResultData(\fan\core\block\base $oBlock)
    {
        $aTplVar = $oBlock->getViewData();

        foreach ($oBlock->getEmbeddedBlocks() as $oEmbeddedBlock) {
            $aTmp = $this->getResultData($oEmbeddedBlock);
            $aTplVar[key($aTmp)] = reset($aTmp);
        }

        return array($oBlock->getBlockName() => $this->_parseTemplate($oBlock, $aTplVar));
    } // function getResultData

    // ======== Protected methods ======== \\
    /**
     * Set Response Headers
     * @param type $sResult
     * @return \fan\core\view\parser
     */
    protected function _setHeaders($sResult, $sContentType = 'text/html', $sEncoding = null)
    {
        return parent::_setHeaders($sResult, $sContentType, $sEncoding);
    } // function _setHeaders
} // class \fan\core\view\parser\html
?>