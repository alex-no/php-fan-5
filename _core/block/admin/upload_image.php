<?php namespace fan\core\block\admin;
/**
 * Admin upload image file class for loader block
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
class upload_image extends base
{

    /**
     * @var array Image param array
     */
    protected $aImage = array();

    /**
     * @var string Error message
     */
    protected $sError = '';

    /**
     * @var string Namespace of image
     */
    protected $sFileNs = null;

    /**
     * Block constructor
     * @param string $sBlockName Block Name
     * @param \core\service\tab $oTab
     */
    public function finishConstruct($oContainer, $aContainerMeta, $bAllowSetEmbedded = true)
    {
        parent::finishConstruct($oContainer, $aContainerMeta, $bAllowSetEmbedded);
        $this->aImage = service('request')->get('image', 'F');
        if ($this->aImage['error'] == UPLOAD_ERR_NO_FILE) {
            $this->aImage = null;
        } elseif ($this->aImage['error'] == UPLOAD_ERR_PARTIAL) {
            $this->aImage = null;
            $this->sError = 'File was broken!';
        } elseif ($this->aImage['error'] == UPLOAD_ERR_INI_SIZE || $this->aImage['error'] == UPLOAD_ERR_FORM_SIZE) {
            $this->aImage = null;
            $this->sError = 'Incorrect file size (there is limit ' . ini_get('upload_max_filesize') . ')!';
        } elseif (!$this->aImage['tmp_name'] || $this->aImage['error']) {
            $this->aImage = null;
        } else {
            $par = getimagesize($this->aImage['tmp_name']);
            if (!$par) {
                $this->aImage = null;
                $this->sError = 'It isn\'t image!';
            }
        }
        if (!$this->sError && $this->aImage && $this->getMeta('max_size')) {
            $par = getimagesize($this->aImage['tmp_name']);
            $w = $this->getMeta(array('max_size', 'width'));
            $h = $this->getMeta(array('max_size', 'height'));

            $nColor = $this->getMeta('b_color', 0XFFFFFF);
            $oImg = service('image', $this->aImage['tmp_name']);
            if ($par[0] > $w || $par[1] > $h) {
                $oImg->scal($w, $h, $this->getMeta('mode', 1), $nColor);
            } elseif ($this->getMeta('allow_relocate', false)) {
                $oImg->relocate($w, $h, $nColor);
            }
            $sMarkerMode = $this->getMeta(array('water_mark', 'mode'));
            $nOpacity = $this->getMeta(array('water_mark', 'opacity'), 10);
            if ($sMarkerMode) {
                $oImg->markering($sMarkerMode, $nOpacity);
            }
            $oImg->saveAndReplace(null);
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
        if(!isset($aMain['img_id'])) {
            $aMain['img_id'] = 'id_file_data';
        }
        $aLink = $this->getMeta('link_table');

        $oMainEtt = null;
        if (!$this->checkMainTableId($oMainEtt, $aData, $aMain, $aLink)) {
            $this->setText('Incorrect main table ID');
            return;
        }

        if ($aLink) {
            if (!$this->checkLinkTableId($oLinkEtt, $aData, $aMain, $aLink)) {
                $this->setText('Incorrect link table ID');
                return;
            }
        } else {
            $oLinkEtt = null;
        }

        $oImg = $this->_getRow($this->_getEttImageName(), @$aData['imgId']);
        if ($aData['op'] == 'dl' && !empty($aData['imgId'])) { // Operation: Delete
            $this->operationDeleteImage($aData, $oMainEtt, $oLinkEtt, $oImg, $aMain, $aLink);
        } elseif ($aData['op'] == 'ul' && $this->aImage) { // Operation: Upload
            $this->operationUploadImage($aData, $oMainEtt, $oLinkEtt, $oImg, $aMain, $aLink);
        } elseif ($aData['op'] == 'sa' && !empty($aData['imgId'])) { // Operation: Set attributes
            $this->operationSetAttributes($aData, $oImg);
        }

        $this->setJson(array(
            'data'    => empty($aData['line']) ?
                    $this->getImageOneData($oMainEtt, $aMain, $aLink):
                    $this->getImageLineData($aData, $aLink),
            'refresh' => $aData['op'] != 'ad' && $aData['op'] != 'sa',
            'op'      => $aData['op'],
        ));

        $this->setText('ok');
    } // function init

    /**
     * Operation: Delete Image
     * @param array $aData
     * @param entity_base $oMainEtt
     * @param entity_base $oLinkEtt
     * @param entity_image $oImg
     * @param array $aMain
     * @param array $aLink
     */
    public function operationDeleteImage(&$aData, $oMainEtt, $oLinkEtt, $oImg, $aMain, $aLink)
    {
        if ($oImg->checkIsLoad()) {
            if ($aLink) {
                $oLinkEtt->delete();
            } else {
                $oMainEtt->setFields(array($aMain['img_id'] => null), true);
            }
            $oImg->delete($this->_getEttImageName(), $aData['imgId']);
        }
    } // function operationDeleteImage

    /**
     * Operation: Upload Image
     * @param array $aData
     * @param entity_base $oMainEtt
     * @param entity_base $oLinkEtt
     * @param entity_image $oImg
     * @param array $aMain
     * @param array $aLink
     */
    public function operationUploadImage(&$aData, $oMainEtt, $oLinkEtt, $oImg, $aMain, $aLink)
    {
        $oReq = service('request');
        $oImg->setFormFile('image', array(), $oReq->get('description', 'P', ''), $oReq->get('alt_txt', 'P', ''));
        if ($oImg->checkIsLoad() && !@$aData['imgId']) {
            if ($aLink) {
                $oLinkEtt->setFields(array($aLink['main_id'] => $aData['mId'], $aLink['img_id'] => $oImg->getId()), true);
            } else {
                $oMainEtt->setFields(array($aMain['img_id'] => $oImg->getId()), true);
            }
        }
    } // function operationUploadImage

    /**
     * Operation: Set Attributes
     * @param array $aData
     * @param entity_base $oLinkEtt
     * @param entity_image $oImg
     * @param array $aMain
     * @param array $aLink
     */
    public function operationSetAttributes(&$aData, $oImg)
    {
        if ($oImg->checkIsLoad()) {
            $oImg->setFields(array('alt' => $aData['alt']), true);
            $oImg->getEntityFile()->setFields(array('description' => $aData['description']), true);
        } else {
            $aData['op'] = null;
        }
    } // function operationSetAttributes

    /**
     * Check Main Table Id
     */
    public function checkMainTableId(&$oMainEtt, &$aData, $aMain, $aLink)
    {
        $oMainEtt = $this->_getRow($aMain['entity'], @$aData['mId']);
        if (@$aData['imgId'] && !$aLink) {
            $sMethod = 'get_' . $aMain['img_id'];
            return $oMainEtt->$sMethod(null, true) == $aData['imgId'];
        }
        return $oMainEtt->checkIsLoad();
    } // function checkMainTableId

    /**
     * Check Link Table Id
     */
    public function checkLinkTableId(&$oLinkEtt, &$aData, $aMain, $aLink)
    {
        if (!@$aData['imgId']) {
            $oLinkEtt = $this->_getRow($aLink['entity']);
            return true;
        } else {
            $oLinkEtt = $this->_getRow($aLink['entity'], array($aLink['main_id'] => $aData['mId'], $aLink['img_id'] => $aData['imgId']));
            return $oLinkEtt->checkIsLoad();
        }
    } // function checkMainTableId

    /**
     * Get Image Line Data
     */
    public function getImageLineData($aData, $aLink)
    {
        $aRet = array();
        $i = 0;
        $aLstId = $this->_getEntity($aLink['entity'])->getRowsetByParam(array($aLink['main_id'] => $aData['mId']))->toArray();
        foreach ($aLstId as $v) {
            $aRet[$i] = $this->getImageData($v[$aLink['img_id']]);
            if (isset($v['order_num'])) {
                $aRet[$i]['order_num'] = $v['order_num'];
            }
            $i++;
        }
        return $aRet;
    } // function getImageLineData

    /**
     * Get Image Data
     */
    public function getImageOneData($oMainEtt, $aMain, $aLink)
    {
        if ($aLink) {
            $aLstId = $this->_getEntity($aLink['entity'])->getRowsetByParam($aLink['main_id'])->getColumn($aLink['img_id']);
            return $this->getImageData(@$aLstId[0]);
        } else {
            $sMethod = 'get_' . $aMain['img_id'];
            return $this->getImageData($oMainEtt->$sMethod());
        }
    } // function getImageOneData

    /**
     * Get Image Data
     */
    public function getImageData($imgId)
    {
        if (!$imgId) {
            return null;
        }
        $oImg = $this->_getRow($this->_getEttImageName(), $imgId);
        if (!$oImg->checkIsLoad()) {
            return null;
        }
        return $oImg->getImageData();
    } // function getImageData

    /**
     * Get Row By Connection
     * @param sring $sEttName
     * @param mixed $mId
     * @return \fan\core\base\model\row
     */
    private function _getRow($sEttName, $mId = null)
    {
        $oRow = gr($sEttName);
        $sCon = $this->getMeta('connection');
        if ($sCon) {
            $oRow->setConnection($sCon);
        }
        $oRow->loadById($mId);
        return $oRow;
    } // function _getRow

    /**
     * Get Entity By Connection
     * @param sring $sEttName
     * @return \fan\core\base\model\entity
     */
    private function _getEntity($sEttName)
    {
        $mConnection = $this->getMeta('connection');
        return empty($mConnection) ? ge($sEttName) : ge($sEttName, 1)->setConnection($mConnection);
    } // function getAggrByCon

    /**
     * Get Suffix of namespace of file entity
     * @return string
     */
    private function _getEttImageName()
    {
        if (is_null($this->sFileNs)) {
            $this->sFileNs = service('entity')->getFileNsSuffix() . 'image';
        }
        return $this->sFileNs;
    } // function _getEttImageName


} // class \fan\core\block\admin\upload_image
?>