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
class debug2 extends \fan\core\view\parser
{
    /**
     * @var \fan\core\service\debug Root block
     */
    protected $oDebug;

    /**
     * View meta constructor
     * @param fan\core\block\base $oBlock
     */
    public function __construct(\fan\core\block\base $oMainBlock)
    {
        parent::__construct($oMainBlock);
        $this->oDebug = \fan\project\service\debug::instance();
    } // function __construct

    // ======== Static methods ======== \\
    /**
     * Get View-Format is disabled for this view
     * @throws \fan\project\exception\error500
     */
    final static public function getFormat() {
        throw new \fan\project\exception\error500('Class "\fan\core\view\parser\debug2" can\'t be use for define View-type');
    } // function getFormat

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    /**
     * Get Final Content Code
     * @return string
     */
    public function getResultData(\fan\core\block\base $oBlock)
    {
        $sBlockInfo = $this->_getInternalResultData($oBlock, false);
        return array(
            $oBlock->getBlockName() => $this->oDebug->getSecondDebugCode(
                    $sBlockInfo, method_exists($oBlock, 'getTitle') ?
                    $oBlock->getTitle() :
                    'Debug Info'
                )
            );
    } // function getResultData

    // ======== Protected methods ======== \\
    /**
     * Get Internal Result Data
     * @return array
     */
    public function _getInternalResultData(\fan\core\block\base $oBlock, $isView)
    {
        $aIncl = array();
        foreach ($oBlock->getEmbeddedBlocks() as $oEmbeddedBlock) {
            $aIncl[] = $this->_getInternalResultData($oEmbeddedBlock, true);
        }

        return $this->oDebug->getSecondDebugRow($oBlock, $aIncl, $isView);
    } // function _getInternalResultData
    /**
     * Set Response Headers
     * @param type $sResult
     * @return \fan\core\view\parser
     */
    protected function _setHeaders($sResult, $sContentType = 'text/html', $sEncoding = null)
    {
        return parent::_setHeaders($sResult, $sContentType, $sEncoding);
    } // function _setHeaders
} // class \fan\core\view\parser\debug2
?>