<?php namespace core\block\loader;
/**
 * Base abstract loader block
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
abstract class base extends \core\block\base
{
    /**
     * @var array Getted Data from loader
     */
    private $aGetData = array();
    /**
     * @var boolean Is set Getted Data
     */
    private $bIsGetData = false;

    /**
     * @var \winWrapperDataLoader
     */
    private $oLoader = null;

    /**
     * Finish Construction of block
     * @param \core\block\base $oContainer
     * @param array $aContainerMeta
     * @param boolean $bAllowSetEmbedded
     */
    public function finishConstruct($oContainer = null, $aContainerMeta = array(), $bAllowSetEmbedded = true)
    {
        parent::finishConstruct($oContainer, $aContainerMeta, $bAllowSetEmbedded);

        $mJson = $this->getMeta('json');
        if(!empty($mJson)) {
            $this->setJson($mJson);
        }

        $sText = $this->getMeta('text');
        if(!empty($sText)) {
            $this->setText($sText);
        }

    } // function finishConstruct

    /**
     * Get loader data
     */
    public function getData()
    {
        if (!$this->bIsGetData) {
            $oEngine = $this->getDataLoader();
            if ($oEngine) {
                $this->aGetData   = $oEngine->getData();
                $this->bIsGetData = true;
            }
        }
        return $this->aGetData;
    }// function getData

    /**
     * Get data loader
     */
    public function getDataLoader()
    {
        if (!$this->oLoader) {
            require_once \bootstrap::parsePath('{CORE_DIR}/../libraries/dataLoader/winWrapperDataLoader.php');
            $this->oLoader = new \winWrapperDataLoader();
        }
        return $this->oLoader;
    }// function getDataLoader

    /**
     * Set object data
     */
    public function setJson($aJson, $bMerge = true)
    {
        $aJson = adduceToArray($aJson);
        if ($bMerge) {
            $aJson = array_merge_recursive_alt(adduceToArray($this->view->json), $aJson);
        }
        $this->view->json = $aJson;
        return $this;
    }// function setJson

    /**
     * Set HTML-text data
     */
    public function setHtml($sHtml, $bMerge = true)
    {
        $this->view->html = $bMerge ? $this->view->html . $sHtml : $sHtml;
        return $this;
    }// function setHtml

    /**
     * Set text data
     * @param string $sText text to send
     */
    public function setText($sText, $bMerge = true)
    {
        $this->view->text = $bMerge ? $this->view->text . $sText : $sText;
        return $this;
    }// function setText

    /**
     * Check Run Init: true if cache isn't used or "alwaysInit" in meta
     * @return boolean
     */
    public function checkRunInit()
    {
        return true;
    } // function checkRunInit

    /**
     * Get loader data
     */
    public function getOutcome()
    {
        return $this->view->toArray();
    }// function getOutcome
} // class \core\block\loader\base
?>