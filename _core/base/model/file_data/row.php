<?php namespace fan\core\base\model\file_data;
use fan\project\exception\model\entity\fatal as fatalException;
/**
 * Row of file data
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
 * @version of file: 05.02.002 (31.03.2014)
 */
abstract class row extends \fan\core\base\model\row
{
    /**
     * @var \fan\core\model\access_type\row  entity access type
     */
    private $oAT = null;

    /**
     * @var string Path to Store-dir
     */
    protected $sStorePath = null;

    /**
     * @var string Path to Info-file
     */
    protected $sInfoPath = null;

    /**
     * @var boolean Allow to load Info-file
     */
    protected $bLoadInfo = true;
    /**
     * @var boolean Allow to save Info-file
     */
    protected $bSaveInfo = true;

    /**
     * @var string Namespace of file
     */
    protected $sFileNs = null;

    /**
     * Row-data constructor
     * @param \fan\core\base\model\entity $oEntity
     * @param array $aData
     */
    public function __construct(\fan\core\base\model\entity $oEntity, &$aData = array(), \fan\core\base\model\rowset $oRowset = null)
    {
        parent::__construct($oEntity, $aData, $oRowset);
        $this->sStorePath = \bootstrap::parsePath($oEntity->getConfig('file_store'));
        //$this->bSaveInfo  = $oEntity->getConfig('ALLOW_INFO_FILE', false);
        //$this->setAllowLoadInfo(@$_SERVER['HTTP_CACHE_CONTROL'] != 'no-cache' || !$oEntity->getConfig('ALLOW_CLEAR_INFO', false));
        $this->bSaveInfo = false;
        $this->bLoadInfo = false;
    } // function __construct

    /**
     * Get Path of Info File
     * @param mixed $mIdVal Record Id
     * @return string
     */
    protected function getInfoPath($mIdVal)
    {
        /*
        if (is_null($this->sInfoPath) && !empty($mIdVal)) {
            $sPath = $this->getMainFilePath($mIdVal);
            $this->sInfoPath = empty($sPath) || !$this->bSaveInfo ?
                '' :
                \bootstrap::parsePath($this->getConfig('INFO_FILE_PATH', '{TEMP}/file_data/file_info/')) . $sPath . '.php';
        }
         */
        $this->sInfoPath = '';
        return $this->sInfoPath;
    } // function getInfoPath

    /**
     * Enable/disable Quick Load file
     * @param boolean $bLoadInfo Allow to Quick Load entity
     */
    public function setAllowLoadInfo($bLoadInfo = true)
    {
        $this->bLoadInfo = false;
        //$this->bLoadInfo = $bLoadInfo && $this->bSaveInfo;
    } // function setAllowLoadInfo

    /**
     * Load DB-record
     * @param mixed $mIdVal Record Id
     * @param boolean $bIdIsEncrypt Flag for decript Id
     * @return boolean - true if is data loaded
     */
    public function loadById($mIdVal = null, $bIdIsEncrypt = false)
    {
        if (empty($mIdVal)) {
            return null;
        }

        if ($bIdIsEncrypt) {
            $mIdVal = $this->getEntity()->getService()->getEncapsulant()->decryptId($mIdVal);
        }
        $sPath = $this->getInfoPath($mIdVal);

        // Try to load data from InfoFile
        if ($this->bLoadInfo && !empty($sPath) && file_exists($sPath)) {
            $aRow = include($sPath);
            if (file_exists($this->getFilePath($aRow['id_file_data'], false, $aRow['src_name']))) {
                $this->setMainProperty($aRow);
                return true;
            }
            unlink($sPath);
        }

        // Load data from DB
        $bRet = parent::loadById($mIdVal, false);

        $this->saveInfoFile($sPath, $bRet);
        return $bRet;
    } // function loadById

    /**
     * Save Info File
     * @param mixed $sPath Record Id
     */
    protected function saveInfoFile($sPath, $bAddCond = true)
    {
        /*
        if (!empty($sPath)) {
            $bIsInfFile = file_exists($sPath);
            if ($bAddCond && $this->bSaveInfo) {
                $aRow = $this->getFields();
                if (!empty($aRow) && empty($aRow['id_file_access_type']) && empty($aRow['is_deleted']) && !empty($aRow['is_accessible'])) {
                    // Check for Save if content is renewed
                    if ($bIsInfFile) {
                        $aRowF = include($sPath);
                        $aComp = array_diff($aRow, $aRowF);
                        if (empty($aComp)) {
                            return true;
                        }
                    }
                    // Save new file
                    file_put_contents($sPath, '<?php
return ' . var_export($aRow, true) . ';
?>');
                    return true;
                }
            }
            if ($bIsInfFile) {
                unlink($sPath);
            }
        }
         */
        return false;
    }// function saveInfoFile

    /**
     * Inquire path to file at the disk
     * @param  integer $nId
     * @param  boolean $bCheckAddCondition
     * @return string | NULL
     * @access public
     */
    public function getFilePath($nId = null, $bCheckAddCondition = true, $sSrcName = '')
    {
        if ($nId) {
            $bCheckAddCondition = false;
        } else {
            $nId = $this->getId(false);
        }
        $sPath = $this->getMainFilePath($nId);
        if (!empty($sPath) && (!$bCheckAddCondition || $this->get_is_accessible() && !$this->get_is_deleted())) {
            $sPath = \bootstrap::parsePath($this->getConfig('file_store') . $sPath);
            $aParts = pathinfo($this->get_src_name($sSrcName, false));
            $sPath .= '.' . (empty($aParts['extension']) || $aParts['extension'] == 'php' ? $this->getConfig('file_ext') : $aParts['extension']);
            return $sPath;
        }
        return null;
    }// function getFilePath

    /**
     * Get main part of file path by ID
     * @param  integer $nId
     * @return string | NULL
     * @access protected
     */
    protected function getMainFilePath($nId)
    {
        $sPath = null;
        if (!empty($nId)) {
            $nTriadLen = strlen($nId) % 3;
            $sPath = str_repeat('0', ($nTriadLen ? 3 - $nTriadLen : 0)) . number_format((int)$nId, 0, '.', '/');
            if ($this->getConfig('path_with_connection')) {
                $sPath = $this->getEntity()->getConnection()->getConnectionName() . '/' . $sPath;
            }
         }
        return $sPath;
    }// function getMainFilePath

    /**
     * Check created dir
     * @param  string  $sFilePath
     * @return string
     * @throws fatalException
     * @access public
     */
    public function checkCreatedDir($sFilePath)
    {
        $sDir = dirname($sFilePath);

        // If path is link
        while (is_link($sDir)) {
            $sLnk = $sDir;
            for ($i = 0; $i < 10; $i++) {
                $sLnk = readlink($sLnk);
                if (is_dir($sLnk)) {
                    break 2;
                }
                if (!is_link($sLnk)) {
                    throw new fatalException($this, 'Incorrect link to file: "' . $sFilePath . '". This "' . $sLnk . '" isn\'t directory.');
                }
            }
            throw new fatalException($this, 'To many links to directory for file: "' . $sFilePath . '".');
        }

        // If directory already exists
        if (is_dir($sDir) || is_link($sDir)) {
            if (!is_writable($sDir)) {
                throw new fatalException($this, 'Directory: ' . $sDir . ' is not writable.');
            }
            return $sDir;
        }
        // If file exists instead of directory
        if (is_file($sDir)) {
            throw new fatalException($this, 'It is inpossible create directory: ' . $sDir . ', because it is file there.');
        }
        // Try to create directory if it is not exist
        $this->checkCreatedDir($sDir);
        if (!mkdir($sDir)) {
            throw new fatalException($this, 'Can\'t create directory: ' . $sDir . '.');
        }
        return $sDir;
    } // function checkCreatedDir

    /**
     * Get content disposition
     * @return boolean true - inline; false - attachment;
     * @access public
     */
    public function getContentDisposition()
    {
        return true;
    }// function getContentDisposition

    /**
     * Prepare data for output file
     * @param boolean $bContentDisposition - Content Disposition: true - inline, false - attachment, null - define by method getContentDisposition
     * @return string - path to file
     * @access public
     */
    public function prepareOutput($bContentDisposition = null)
    {
        $sFilePath = $this->getFilePath();
        if ($sFilePath && file_exists($sFilePath)) {
            $oSH = service('headers');
            /* @var $oSH \fan\core\service\header */
            $oSH->addHeader('contentType', $this->get_mime_type());
            $oSH->addHeader('filename',    $this->get_src_name());
            $oSH->addHeader('disposition', is_null($bContentDisposition) ? $this->getContentDisposition() : $bContentDisposition);
            $oSH->addHeader('length',      filesize($sFilePath));
            $oSH->addHeader('modified',    filemtime($sFilePath));
            $oSH->addHeader('cacheLimit',  0);
            return $sFilePath;
        }
        return null;
    }// function prepareOutput

    /**
     * Set file from form
     * @param string $sFormKey
     * @param array $aAddKeys
     * @param string $sFileType
     * @param string $sDecription
     * @return boolean true - if file is stored successful
     * @access public
     */
    public function setFormFile($sFormKey, $aAddKeys = array(), $sFileType = 'other', $sDecription = '')
    {
        if(!$this->getFileField('error', $sFormKey, $aAddKeys)) {
            $this->deleteCurrentFile();
            $sFilePath = $this->prepareUpdateFile($this->getFileField('name', $sFormKey, $aAddKeys), $this->getFileField('type', $sFormKey, $aAddKeys), $sFileType, $sDecription);
            if(move_uploaded_file($this->getFileField('tmp_name', $sFormKey, $aAddKeys), str_replace('\\', '/', $sFilePath))) {
                return true;
            }
            $this->set_is_deleted(1);
            $this->save();
        }
        return false;
    }// function getFormFile

    /**
     * Set file from foreign URL
     * @param string $sUrl
     * @param string $sFileType
     * @param string $sDecription
     * @return boolean true - if file is stored successful
     * @access public
     */
    public function setUrlFile($sUrl, $sFileType = 'other', $sDecription = '')
    {
        $oCurl = service('curl', $sUrl);
        $sData = $oCurl->exec();
        if (!$oCurl->getError() && $sData) {
            $this->deleteCurrentFile();
            if ($oCurl->getHeaders('Content-Disposition') && preg_match('/filename\s*\=\s*"?([^"]+)"?\s*$/', $oCurl->getHeaders('Content-Disposition'), $aMatch)) {
                $sName = $aMatch[1];
            } else {
                $aPI = pathinfo($sUrl);
                $sName = $aPI['basename'];
                if ($sName != urlencode($sName)) {
                    $sName = 'undefined';
                }
            }
            $sFilePath = $this->prepareUpdateFile($sName, $oCurl->getInfo(CURLINFO_CONTENT_TYPE), $sFileType, $sDecription);
            if (file_put_contents($sFilePath, $sData)) {
                return true;
            }
            $this->set_is_deleted(1);
            $this->save();
        }
        return false;
    }// function setUrlFile

    /**
     * Set file from local path
     * @param string $sUrl
     * @param string $sFileType
     * @param string $sDecription
     * @return boolean true - if file is stored successful
     * @access public
     */
    public function setLocalFile($sSrcPath, $sFileType = 'other', $sMimeType = 'application/octet-stream', $sDecription = '', $sName = null, $bDeleteOrigin = false)
    {
        if ($sSrcPath && file_exists($sSrcPath)) {
            $sFilePath = $this->prepareUpdateFile($sName ? $sName : basename($sSrcPath), $sMimeType, $sFileType, $sDecription);
            clearstatcache();
            if (file_exists($sFilePath)) {
                unlink($sFilePath);
            }
            clearstatcache();
            if ($bDeleteOrigin ? rename($sSrcPath, $sFilePath) : copy($sSrcPath, $sFilePath)) {
                return true;
            }
            $this->set_is_deleted(1);
            $this->save();
        }
        return false;
    }// function setUrlFile

    /**
     * Delete file
     * @access public
     */
    public function delete()
    {
        $sFilePath = $this->getFilePath();
        $sInfoPath = $this->getInfoPath($this->getId(true));
        if (parent::delete()) {
            $this->deleteCurrentFile($sFilePath, $sInfoPath);
        }
    }

    /**
     * Save record
     */
    public function save()
    {
        parent::save();
        $sPath = $this->getInfoPath($this->getId());
        $this->saveInfoFile($sPath);
    } // function save

    /**
     * Get fied from $_FILES arrays
     * @param  string  $sKeyType
     * @param  string  $sFormKey
     * @param  array   $aAddKeys
     * @return string
     * @access protected
     */
    protected function getFileField($sKeyType, $sFormKey, $aAddKeys = array())
    {
        $sRet = service('request')->get($sFormKey, 'F');
        $sRet = $sRet[$sKeyType];
        foreach ($aAddKeys as $k) {
            $sRet = $sRet[$k];
        }
        return $sRet;
    }// function getFileField

    /**
     * Delete current file from store
     * @param  string  $sFilePath
     * @access protected
     */
    protected function deleteCurrentFile($sFilePath = null, $sInfoPath = null)
    {
        if (!$sFilePath) {
            $sFilePath = $this->getFilePath();
            $sInfoPath = $this->getInfoPath($this->getId(false));
        }
        if ($sFilePath && file_exists($sFilePath)) {
            unlink($sFilePath);
        }
        if ($sInfoPath && file_exists($sInfoPath)) {
            unlink($sInfoPath);
        }
    }// function deleteCurrentFile



    // ======== Set/get access ======== \\

    /**
     * Set access type
     * @param  string  $sKey   type key
     * @param  boolean $bSave  save this entity
     * @access public
     */
    public function setAccessType($sKey, $bSave = true)
    {
        if ($sKey) {
            $this->oAT = ge($this->_getFileNs() . 'file_access_type')->getRowByParam(array('access_type' => $sKey));
            if (!$this->oAT->checkIsLoad()) {
                throw new fatalException($this, 'Incorrect Access Type Key!');
            }
            $this->set_id_file_access_type($this->oAT->getId());
        } else {
            $this->set_id_file_access_type(null);
        }
        if($bSave) {
            $this->save();
        }
    }// function setAccessType

    /**
     * Set personal access
     * @param string $sMembType   - Member Type: owner/guest
     * @param string $sExpireDate - Expire access data
     * @param number $nAccessQtt  - Max quantity of access
     * @param number $nMembId     - Memeber Id (NR. By defaulf current member)
     * @access public
     */
    public function setPersonalAccess($sMembType = 'owner', $sExpireDate = null, $nAccessQtt = -1, $nMembId = 0)
    {
        if (!$nMembId) {
            $nMembId = entity_member::getCurrentMember()->getId();
        }
        $oPA = $this->getEntityPA($nMembId);
        $oPA->setFields(array(
            'id_file_data' => $this->getId(),
            'id_member'    => $nMembId,
            'member_type'  => $sMembType,
            'expire_data'  => $sExpireDate,
            'access_qtt'   => $nAccessQtt,
        ), true);
    }// function setPersonalAccess

    /**
     * Remove personal access
     * @param string  $nRemoveType Remove Type: 0 - remove all; 1 -  remove all but not owner; 2 - remove pointed member; 3 -  remove all but not pointed member
     * @param integer $nMembId     Memeber Id (Required for type: 2 and 3)
     * @access public
     */
    public function removePersonalAccess($nRemoveType = 1, $nMembId = null)
    {
        $aPA = ge($this->_getFileNs() . 'file_personal_access')->getRowsetByParam(array('id_file_data' => $this->getId()));
        if ($nRemoveType > 1 && !$nMembId) {
            throw new fatalException($this, 'Member Id for remove access doesn\'t set!');
        }
        foreach ($aPA as $e) {
            if (!$nRemoveType || $nRemoveType == 1 && $e->get_member_type() != 'owner' || $nRemoveType == 2 && $e->get_id_member() == $nMembId || $nRemoveType == 3 && $e->get_id_member() != $nMembId) {
                $e->delete();
            }
        }
    }// function removePersonalAccess

    /**
     * Check access for read file
     * @return boolean true - if access is enabled
     * @access public
     */
    public function checkAccess()
    {
        $bRet = true;
        if ($this->get_id_file_access_type(false, true)) {
            if (!$this->oAT) {
                $this->oAT = gr($this->_getFileNs() . 'file_access_type', $this->get_id_file_access_type());
            }
            $sRule = $this->oAT->get_access_rule();
            if ($sRule) { // Check access by rule
                $bRet     = false;
                $aMatches = array();
                foreach (explode(',', $sRule) as $s) {
                    if (preg_match('/^(?:([^\:]+)\:)?(.+)?$/', $s, $aMatches)) {
                        if (role($aMatches[2], $aMatches[1])) {
                            $bRet = true;
                            break;
                        }
                    } else {
                        throw new fatalException($this, 'Incorret role rule "' . $sRule . '"');
                    }
                }
            }
            if (!$bRet) { // Check personal access
                $oPA = $this->getEntityPA();
                if ($oPA && $oPA->checkIsLoad()) {
                    $nQtt = $oPA->get_access_qtt();
                    if (!$nQtt || ($oPA->get_expire_data() && date('Y-m-d H:i:s') > $oPA->get_expire_data())) {
                        if ($oPA->get_member_type() != 'owner') {
                            $oPA->delete();
                        }
                    } else {
                        $bRet = true;
                        if ($nQtt > 0) {
                            $oPA->set_access_qtt($nQtt - 1);
                        }
                    }
                }
            }
        }
        return $bRet;
    }// function checkAccess

    /**
     * Chech Is current member Owner
     * @return boolean true - if member is owner
     * @access public
     */
    public function checkIsOwner()
    {
        $oPA = $this->getEntityPA();
        if (is_null($oPA) || !$oPA->checkIsLoad()) {
            return false;
        }
        return $oPA->get_member_type() == 'owner';
    }// function checkIsOwner

    /**
     * Get entity personal access
     * @return entity_file_personal_access | NULL
     * @access protected
     */
    protected function getEntityPA($nMembId = null)
    {
        if (!$nMembId) {
            $oMember = getUser();
            if (!$oMember || !$oMember->checkIsLoad()) {
                return null;
            }
            $nMembId = $oMember->getId();
        }
        $oPA = ge($this->_getFileNs() . 'file_personal_access')->getRowsetByParam(array(
            'id_file_data' => $this->getId(),
            'id_member'    => $nMembId,
        ));
        return $oPA;
    }// function getEntityPA

    /**
     * Prepare update file
     * @param string  $sName
     * @param string  $sMimeType
     * @param string  $sFileType
     * @param string  $sDecription
     * @return string
     * @access protected
     */
    protected function prepareUpdateFile($sName, $sMimeType, $sFileType, $sDecription)
    {
        $this->set_src_name($sName);
        $this->set_mime_type($sMimeType);
        $this->set_file_type($sFileType);
        $this->set_description($sDecription);
        $this->set_is_accessible(1);
        $this->set_is_deleted(0);

        if (!$this->checkIsLoad()) {
            $this->set_create_date(date('Y-m-d H:i:s'));
        }
        $this->set_update_date(date('Y-m-d H:i:s'));
        $this->save();

        $sFilePath = $this->getFilePath();
        $this->checkCreatedDir($sFilePath);
        return $sFilePath;
    }// function prepareUpdateFile

    /**
     * Get Suffix of namespace of file entity
     * @return string
     */
    protected function _getFileNs()
    {
        if (is_null($this->sFileNs)) {
            $this->sFileNs = service('entity')->getFileNsSuffix();
        }
        return $this->sFileNs;
    } // function _getFileNs

} // class \fan\core\base\model\file_data\row
?>