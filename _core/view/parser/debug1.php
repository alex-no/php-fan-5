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
class debug1 extends html
{
    /**
     * @var \core\service\debug Root block
     */
    protected $oDebug;

    /**
     * View meta constructor
     * @param core\block\base $oBlock
     */
    public function __construct(\core\block\base $oMainBlock)
    {
        parent::__construct($oMainBlock);
        $this->oDebug = \project\service\debug::instance();
    } // function __construct

    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    /**
     * Get Final Content Code
     * @return string
     */
    public function getResultData(\core\block\base $oRootBlock)
    {
        $this->oDebug->setExtFiles($oRootBlock, 1);

        $aTplVar = $oRootBlock->getViewData();

        $bIsWrap = false;
        foreach ($oRootBlock->getEmbeddedBlocks() as $oEmbeddedBlock) {
            $aTmp = $this->_getInternalResultData($oEmbeddedBlock);
            if ($bIsWrap) {
                $aTplVar[key($aTmp)] = reset($aTmp);
            } else {
                $aTplVar[key($aTmp)] = $this->oDebug->wrapHtmlCode(reset($aTmp), $oRootBlock);
                $bIsWrap = true;
            }
        }

        return array($oRootBlock->getBlockName() => $this->_parseTemplate($oRootBlock, $aTplVar));
    } // function getResultData

    // ======== Protected methods ======== \\
    /**
     * Get Internal Result Data
     * @return array
     */
    public function _getInternalResultData(\core\block\base $oBlock)
    {
        $aTplVar = $oBlock->getViewData();

        foreach ($oBlock->getEmbeddedBlocks() as $oEmbeddedBlock) {
            $aTmp = $this->_getInternalResultData($oEmbeddedBlock);
            $aTplVar[key($aTmp)] = reset($aTmp);
        }

        return array($oBlock->getBlockName() => $this->oDebug->wrapHtmlCode($this->_parseTemplate($oBlock, $aTplVar), $oBlock));
    } // function _getInternalResultData
} // class \core\view\parser\debug1
?>