<?php namespace core\view\parser;
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
 * @version of file: 05.001 (29.09.2011)
 */
class html extends \core\view\parser
{
    // ======== Static methods ======== \\
    /**
     * Get View-type
     * @return string
     */
    final static public function getType() {
        return 'html';
    } // function getType

    /**
     * Get View-Router for block
     * @param \core\block\base $oBlock
     * @return \core\view\router\simple
     */
    static public function getRouter(\core\block\base $oBlock) {
        return new \project\view\router\html($oBlock);
    } // function getRouter

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    /**
     * Get Final Content Code
     * @return string
     */
    public function getResultData(\core\block\base $oBlock)
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
     * @return \core\view\parser
     */
    protected function _setHeaders($sResult, $sContentType = 'text/html', $sEncoding = null)
    {
        return parent::_setHeaders($sResult, $sContentType, $sEncoding);
    } // function _setHeaders
} // class \core\view\parser\html
?>