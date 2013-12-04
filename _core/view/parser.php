<?php namespace core\view;
/**
 * Base abstract html type of block
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
 * @abstract
 */
abstract class parser
{
    /**
     * @var \core\block\base Root block
     */
    protected $oRootBlock;
    /**
     * @var \core\block\base Main block
     */
    protected $oMainBlock;

    /**
     * Array - result of parsing process
     * @var array
     */
    protected $aResult;


    /**
     * View meta constructor
     * @param core\block\base $oBlock
     */
    public function __construct(\core\block\base $oMainBlock)
    {
        $this->oMainBlock = $oMainBlock;
    } // function __construct

    // ======== Static methods ======== \\
    /**
     * Get View-type
     * @return string
     */
    static public function getType() {
     throw new \core\exception\error500('Class "' . get_called_class() . '" can\'t be use for define View-type');
    } // function getType

    /**
     * Get View-Router for block
     * @param \core\block\base $oBlock
     * @return \core\view\router\simple
     */
    static public function getRouter(\core\block\base $oBlock) {
        return new \project\view\router\simple($oBlock);
    } // function getRouter

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    /**
     * Start Parsing View data
     * @param \core\block\base $oRootBlock
     * @return \core\view\parser
     */
    public function startParsing(\core\block\base $oRootBlock)
    {
        $this->oRootBlock = $oRootBlock;
        $this->aResult = $this->getResultData($this->oRootBlock);
        return $this;
    } // function startParsing

    /**
     * Get Final Content Result
     * @return string
     */
    public function getFinalContent()
    {
        $sResult = end($this->aResult);
        $this->_setHeaders($sResult);
        return $sResult;
    } // function getFinalContent

    /**
     * Get Final Content Code
     * @return string
     */
    public function getResultData(\core\block\base $oBlock)
    {
        return $this->_assembleToArray($oBlock);
    } // function getResultData

    // ======== Protected methods ======== \\
    /**
     * Assemble View data to Array
     * @param \core\block\base $oBlock
     * @return array
     */
    protected function _assembleToArray(\core\block\base $oBlock)
    {
        $aViewData = $oBlock->getViewData();

        foreach ($oBlock->getEmbeddedBlocks() as $oEmbeddedBlock) {
            $aViewData[$oEmbeddedBlock->getBlockName()] = $this->getResultData($oEmbeddedBlock);
        }

        return $aViewData;
    } // function _assembleToArray

    /**
     * Mix View data of Block with View data of Embeded blocks
     * @param array $aBlockData
     * @param array $aEmbededData
     */
    protected function _mixEmbededData($aBlockData, $aEmbededData)
    {
        $aMixedData = array();
        foreach ($aEmbededData as $v) {
            $aMixedData = array_merge($aMixedData, $v);
        }
        return array_merge($aMixedData, $aBlockData);
    } // function _mixEmbededData

    /**
     * Parse Template
     * @param \core\block\base $oBlock
     * @param type $aTplVar
     * @return string
     */
    protected function _parseTemplate(\core\block\base $oBlock, $aTplVar)
    {
        $sTemplate = $oBlock->getTemplate();
        if ($sTemplate) {
            // If template exists - assign variables and parse template
            $sTplParentClass = $oBlock->getMeta('tpl_parent_class');

            $oTemplate = \project\service\template::instance()->get($sTemplate, $sTplParentClass, $oBlock);
            foreach ($aTplVar as $k => $v) {
                $oTemplate->assign($k, $v);
            }
            $sTplResult = $oTemplate->fetch();
        } else {
            // else - concatenate variables
            $sTplResult = '';
            foreach ($aTplVar as $v) {
                if (is_scalar($v) || is_object($v) && method_exists($v, '__toString')) {
                    $sTplResult .= (string)$v;
                }
            }
        }

        return $sTplResult;
    } // function _parseTemplate

    /**
     * Parse Template
     * @param \core\block\base $oBlock
     * @param array $aSrcData
     * @param string $sTplResult
     * @return mixed
     */
    protected function _formatResultData(\core\block\base $oBlock, $aSrcData, $sTplResult)
    {
        return array($oBlock->getBlockName() => $sTplResult);
    } // function _formatResultData

    /**
     * Set Response Headers
     * @param string $sResult
     * @param string $sContentType
     * @param boolean $sEncoding
     * @return \core\service\header
     */
    protected function _setHeaders($sResult, $sContentType = 'text/plain', $sEncoding = null)
    {
        $oHeader = \project\service\header::instance();
        $oHeader->addHeader('length', strlen($sResult));

        if (!empty($sContentType)) {
            if (is_null($sEncoding)) {
                $sEncoding = \core\service\locale::instance()->getCharacterSet();
            }
            $oHeader->addHeader('contentType', $sContentType);
            if (!empty($sEncoding)) {
                $oHeader->addHeader('encoding', 'charset=' . $sEncoding);
            }
        }

         // ToDo: Add another header there. For example - cache headers
        return $oHeader;
    } // function _setHeaders

} // class \core\view\parser
?>