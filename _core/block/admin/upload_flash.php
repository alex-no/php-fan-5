<?php namespace fan\core\block\admin;
/**
 * Admin upload flash-file class for loader block
 *
 * This file is part PHP-FAN (php-framework of Alexandr Nosov)
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
class upload_flash extends base
{

    /**
     * @var array Flash param array
     */
    protected $aFlash = array();

    /**
     * @var string Error message
     */
    protected $sError = '';

    /**
     * Block constructor
     * @param string $sBlockName Block Name
     * @param \core\service\tab $oTab
     */
    public function finishConstruct($oContainer, $aContainerMeta, $bAllowSetEmbedded = true)
    {
        parent::finishConstruct($oContainer, $aContainerMeta, $bAllowSetEmbedded);

        $this->aFlash = service('request')->get('flash', 'F');
        if ($this->aFlash['error'] == UPLOAD_ERR_NO_FILE) {
            $this->aFlash = null;
        } elseif ($this->aFlash['error'] == UPLOAD_ERR_PARTIAL) {
            $this->aFlash = null;
            $this->sError = 'File was broken!';
        } elseif ($this->aFlash['error'] == UPLOAD_ERR_INI_SIZE || $this->aFlash['error'] == UPLOAD_ERR_FORM_SIZE) {
            $this->aFlash = null;
            $this->sError = 'Incorrect file size (there is limit ' . ini_get('upload_max_filesize') . ')!';
        } elseif (!$this->aFlash['tmp_name'] || $this->aFlash['error']) {
            $this->aFlash = null;
        }
    } // function __construct

    /**
     * Init output block data
     */
    public function init()
    {
        service('role')->setSessionRoles('admin', $this->getMeta('login_timeout'));

        if($this->sError) {
            $this->setText($this->sError);
            return;
        }

        $aData = $this->getData();
        $aMain = $this->getMeta('main_table');
        if(!isset($aMain['flash_id'])) {
            $aMain['flash_id'] = 'id_file_data';
        }
        $aLink = $this->getMeta('link_table');

        if (!$this->checkMainTableId($oMainEtt, $aData, $aMain, $aLink)) {
            $this->setText('Incorrect main table ID');
            return;
        }

        if ($aLink) {
            if (!$this->checkLinkTableId($oLinkEtt, $aData, $aMain, $aLink)) {
                $this->setText('Incorrect link table ID');
                return;
            }
        }
        $oFlash = gr(service('entity')->getFileNsSuffix() . 'flash', @$aData['flashId']);
        if ($aData['op'] == 'dl' && @$aData['flashId']) {
            if ($oFlash->checkIsLoad()) {
                if ($aLink) {
                    $oLinkEtt->delete();
                } else {
                    $oMainEtt->setFields(array($aMain['flash_id'] => null), true);
                }
                $oFlash->delete('flash', $aData['flashId']);
            }
        } elseif ($aData['op'] == 'ul' && $this->aFlash) {
            $oReq = service('request');
            $oFlash->setFormFile('flash', array(), $oReq->get('description', 'P', ''), $oReq->get('width', 'P', 100), $oReq->get('height', 'P', 100), $oReq->get('bgcolor', 'P', ''));
            if ($oFlash->checkIsLoad() && !@$aData['flashId']) {
                if ($aLink) {
                    $oLinkEtt->setFields(array($aLink['main_id'] => $aData['mId'], $aLink['flash_id'] => $oFlash->getId()), true);
                } else {
                    $oMainEtt->setFields(array($aMain['flash_id'] => $oFlash->getId()), true);
                }
            }
        }

        $aJsonData = @$aData['line'] ? $this->getFlashLineData($aData, $aLink) : $this->getFlashOneData($oMainEtt, $aMain, $aLink);
        if (!$oFlash->checkIsLoad() && $aJsonData['id']) {
            $oFlash->loadById($aJsonData['id']);
        }
        $aJsonData['filename'] = $oFlash->checkIsLoad() ? $oFlash->get_src_name() : '';
        $this->setJson(array('data' => $aJsonData));


        $this->setText('ok');
    }

    /**
     * Check Main Table Id
     */
    public function checkMainTableId(&$oMainEtt, &$aData, $aMain, $aLink)
    {
        $oMainEtt = gr($aMain['table_name'], @$aData['mId']);
        if (@$aData['flashId'] && !$aLink) {
            $sMethod = 'get_' . $aMain['flash_id'];
            return $oMainEtt->$sMethod(null, true) == $aData['flashId'];
        }
        return $oMainEtt->checkIsLoad();
    } // function checkMainTableId

    /**
     * Check Link Table Id
     */
    public function checkLinkTableId(&$oLinkEtt, &$aData, $aMain, $aLink)
    {
        if (!@$aData['flashId']) {
            $oLinkEtt = gr($aLink['table_name']);
            return true;
        } else {
            $oLinkEtt = gr($aLink['table_name'], array($aLink['main_id'] => $aData['mId'], $aLink['flash_id'] => $aData['flashId']));
            return $oLinkEtt->checkIsLoad();
        }
    } // function checkMainTableId

    /**
     * Get Flash Line Data
     */
    public function getFlashLineData($aData, $aLink)
    {
        $aRet = array();
        $aLstId = ge($aLink['table_name'])->getRowsetByParam(array($aLink['main_id'] => $aData['mId']))->getColumn($aLink['flash_id']);
        foreach ($aLstId as $v) {
            $aRet[] = $this->getFlashData($v);
        }
        return $aRet;
    } // function getFlashLineData

    /**
     * Get Flash Data
     */
    public function getFlashOneData($oMainEtt, $aMain, $aLink)
    {
        if ($aLink) {
            $aLstId = ge($aLink['table_name'])->getRowsetByParam($aLink['main_id'])->getColumn($aLink['flash_id']);
            return $this->getFlashData(@$aLstId[0]);
        } else {
            $sMethod = 'get_' . $aMain['flash_id'];
            return $this->getFlashData($oMainEtt->$sMethod());
        }
    } // function getFlashOneData

    /**
     * Get Flash Data
     * @param mixed $fileId
     * @return array
     */
    public function getFlashData($flashId)
    {
        if (!$flashId) {
            return null;
        }
        // To Do: Get full info about flash
        return array('id' => $flashId);
    } // function getFlashData
} // class \fan\core\block\admin\upload_flash
?>