<?php namespace core\block\admin;
/**
 * Admin upload file class for loader block
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
 * @version of file: 05.004 (31.12.2013)
 */
class upload_file extends base
{

    /**
     * @var array File param array
     */
    protected $aFile = array();

    /**
     * @var string Error message
     */
    protected $sError = '';

    /**
     * Block constructor
     * @param string $sBlockName Block Name
     * @param service_tab $oTab
     */
    public function finishConstruct($oContainer, $aContainerMeta, $bAllowSetEmbedded = true)
    {
        parent::finishConstruct($oContainer, $aContainerMeta, $bAllowSetEmbedded);

        $this->aFile = service('request')->get('file', 'F');
        if ($this->aFile['error'] == UPLOAD_ERR_NO_FILE) {
            $this->aFile = null;
        } elseif ($this->aFile['error'] == UPLOAD_ERR_PARTIAL) {
            $this->aFile = null;
            $this->sError = 'File was broken!';
        } elseif ($this->aFile['error'] == UPLOAD_ERR_INI_SIZE || $this->aFile['error'] == UPLOAD_ERR_FORM_SIZE) {
            $this->aFile = null;
            $this->sError = 'Incorrect file size (there is limit ' . ini_get('upload_max_filesize') . ')!';
        } elseif (!$this->aFile['tmp_name'] || $this->aFile['error']) {
            $this->aFile = null;
        }
    } // function __construct

    /**
     * Init output block data
     */
    public function init()
    {
        service('roles')->setSessionRoles('admin', $this->getMeta('login_timeout'));

        if($this->sError) {
            $this->setText($this->sError);
            return;
        }

        $aData = $this->getData();
        $aMain = $this->getMeta('main_table');
        if(!isset($aMain['file_id'])) {
            $aMain['file_id'] = 'id_file_data';
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

        $oFile = gr(service('entity')->getFileNsSuffix() . 'file_data', @$aData['fileId']);
        if ($aData['op'] == 'dl' && @$aData['fileId']) {
            if ($oFile->checkIsLoad()) {
                if ($aLink) {
                    $oLinkEtt->delete();
                } else {
                    $oMainEtt->setFields(array($aMain['file_id'] => null), true);
                }
                $oFile->delete('file_data', $aData['fileId']);
            }
        } elseif ($aData['op'] == 'ul' && $this->aFile) {
            $oFile->setFormFile('file', array(), 'other', service('request')->get('description', 'P', ''));
            $oFile->setAccessType($this->getMeta('access_type', null));
            if ($oFile->checkIsLoad() && !@$aData['fileId']) {
                if ($aLink) {
                    $oLinkEtt->setFields(array($aLink['main_id'] => $aData['mId'], $aLink['file_id'] => $oFile->getId()), true);
                } else {
                    $oMainEtt->setFields(array($aMain['file_id'] => $oFile->getId()), true);
                }
            }
        }

        $aJsonData = @$aData['line'] ? $this->getFileLineData($aData, $aLink) : $this->getFileOneData($oMainEtt, $aMain, $aLink);
        if (!$oFile->checkIsLoad() && $aJsonData['id']) {
            $oFile->loadById($aJsonData['id']);
        }
        $aJsonData['filename'] = $oFile->checkIsLoad() ? $oFile->get_src_name() : '';
        $this->setJson(array('data' => $aJsonData));

        $this->setText('ok');
    }

    /**
     * Check Main Table Id
     */
    public function checkMainTableId(&$oMainEtt, &$aData, $aMain, $aLink)
    {
        $oMainEtt = gr($aMain['table_name'], @$aData['mId']);
        if (@$aData['fileId'] && !$aLink) {
            $sMethod = 'get_' . $aMain['file_id'];
            return $oMainEtt->$sMethod(null, true) == $aData['fileId'];
        }
        return $oMainEtt->checkIsLoad();
    } // function checkMainTableId

    /**
     * Check Link Table Id
     */
    public function checkLinkTableId(&$oLinkEtt, &$aData, $aMain, $aLink)
    {
        if (!@$aData['fileId']) {
            $oLinkEtt = gr($aLink['table_name']);
            return true;
        } else {
            $oLinkEtt = gr($aLink['table_name'], array($aLink['main_id'] => $aData['mId'], $aLink['file_id'] => $aData['fileId']));
            return $oLinkEtt->checkIsLoad();
        }
    } // function checkMainTableId

    /**
     * Get File Line Data
     */
    public function getFileLineData($aData, $aLink)
    {
        $aRet = array();
        $aLstId = ge($aLink['table_name'])->getRowsetByParam(array($aLink['main_id'] => $aData['mId']))->getColumn($aLink['file_id']);
        foreach ($aLstId as $v) {
            $aRet[] = $this->getFileData($v);
        }
        return $aRet;
    } // function getFileLineData

    /**
     * Get File Data
     */
    public function getFileOneData($oMainEtt, $aMain, $aLink)
    {
        if ($aLink) {
            $aLstId = ge($aLink['table_name'])->getRowsetByParam($aLink['main_id'])->getColumn($aLink['file_id']);
            return $this->getFileData(@$aLstId[0]);
        } else {
            $sMethod = 'get_' . $aMain['file_id'];
            return $this->getFileData($oMainEtt->$sMethod());
        }
    } // function getFileLineData

    /**
     * Get File Data
     * @param mixed $fileId
     * @return array
     */
    public function getFileData($mFileId)
    {
        if (!$mFileId) {
            return null;
        }
        // To Do: Get full info about file
        return array('id' => $mFileId);
    } // function getFileData
} // class \core\block\admin\upload_file
?>