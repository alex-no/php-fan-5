<?php namespace core\view\parser;
/**
 * View parser Loader-type
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
class loader extends \core\view\parser
{
    // ======== Static methods ======== \\
    /**
     * Get View-type
     * @return string
     */
    final static public function getType() {
        return 'loader';
    } // function getType

    /**
     * Get View-Router for block
     * @param \core\block\base $oBlock
     * @return \core\view\router\simple
     */
    static public function getRouter(\core\block\base $oBlock) {
        return new \project\view\router\loader($oBlock);
    } // function getRouter

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    /**
     * Get Final Content Code
     * @return string
     */
    public function getFinalContent()
    {
        if (method_exists($this->oMainBlock, 'getDataLoader')) {
            $oLoader = $this->oMainBlock->getDataLoader();
        } else {
            require_once \bootstrap::parsePath('{CORE_DIR}/../libraries/dataLoader/winWrapperDataLoader.php');
            $oLoader = new \winWrapperDataLoader();
        }
        $oLoader->setJson($this->aResult['json'], true);
        $oLoader->setText($this->aResult['text'], true);
        $oLoader->setHtml($this->aResult['html'], true);
        $sResult = $oLoader->send(false);

        $this->_setHeaders($sResult, 'application/javascript');
        return $sResult;
    } // function getFinalContent

    /**
     * Get Final Content Code
     * @return string
     */
    public function getResultData(\core\block\base $oBlock)
    {
        $oViewRouter = $oBlock->getView();
        $aTplResult  = $this->_getTplResult($oBlock);
        return array(
            'json' => $oViewRouter->getJson(),
            'html' => end($aTplResult),
            'text' => $oViewRouter->getText(),
        );
    } // function getResultData

    // ======== Protected methods ======== \\
    /**
     * Get Final Content Code
     * @return string
     */
    public function _getTplResult(\core\block\base $oBlock)
    {
        $aTplVar = $oBlock->getView()->html->toArray();

        foreach ($oBlock->getEmbeddedBlocks() as $oEmbeddedBlock) {
            $aTmp = $this->_getTplResult($oEmbeddedBlock);
            $aTplVar[key($aTmp)] = reset($aTmp);
        }

        return array($oBlock->getBlockName() => $this->_parseTemplate($oBlock, $aTplVar));
    } // function _getTplResult
} // class \core\view\parser\loader
?>