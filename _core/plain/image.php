<?php namespace fan\core\plain;
use fan\project\exception\plain\fatal as fatalException;
/**
 * Class of controller for show image, nail, etc
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
 * @abstract
 */
class image extends db_file
{
    /**
     * @var integer - Width
     */
    protected $nWidth;

    /**
     * @var integer - Height
     */
    protected $nHeight;

    /**
     * Path to directory with QuickNail
     * @var string
     */
    protected $sNailDir = null;

    /**
     * Image type
     * @var string
     */
    protected $sImageType = null;

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    /**
     * Get File of Image
     * @return array|string
     */
    public function getImage()
    {
        $this->sImageType = 'image';
        return $this->getFile();
    } // function getImage

    /**
     * Get Nail of Image
     * @return array|string
     */
    public function getNail()
    {
        $this->sImageType = 'nail';
        return $this->_prepareNail()->_init()->_getContent();
    } // function getNail

    /**
     * Get Adm Nail of Image
     * @return array|string
     */
    public function getAdmNail()
    {
        $this->sImageType = 'adm_nail';
        return $this->_prepareNail()->_init()->_getContent();
    } // function getAdmNail

    // ======== Private/Protected methods ======== \\

    /**
     * Set properties file output: mId, mPos, sApp, sErrMsg
     */
    protected function _prepareNail()
    {
        $this->_prepare();

        if (!empty($this->mId)) {
            list($this->nWidth, $this->nHeight) = $this->_getNailSize();
            if (empty($this->nWidth) && empty($this->nHeight)) {
                throw new fatalException($this, 'There isn\'t point width or height of nail.');
            } else {
                $sDirMask = $this->oConfig->get('nail_dir', '{TEMP}/nail');
                $this->sNailDir = $this->_getNailDir($sDirMask, false);
            }
        }
        return $this;
    } // function _prepareNail

    /**
     * Get Nail Size
     * @return array
     */
    protected function _getNailSize()
    {
        if ($this->sImageType == 'adm_nail') {
            return array(60, 60);
        }
        $oSR = \fan\project\service\request::instance();
        return array($oSR->get('w', 'GPA'), $oSR->get('h', 'GPA'));
    } // function _getNailSize

    /**
     * Get file data
     * file data or null - if the file is not valid
     * @param boolean $bIdIsEncrypt
     * @return array|null
     */
    protected function _getFileData($bIdIsEncrypt = null)
    {
        $aFileData = $this->sImageType == 'image' ? parent::_getFileData($bIdIsEncrypt) : $this->_getNailFileData($bIdIsEncrypt);
        if ($this->sImageType == 'adm_nail' && empty($aFileData)) {
            $aFileData = $this->_getStubFileData();
        }
        return $aFileData;
    } // function _getFileData

    /**
     * Get file data of nail
     * file data or null - if the file is not valid
     * @param boolean $bIdIsEncrypt
     * @return array|null
     */
    protected function _getNailFileData($bIdIsEncrypt = null)
    {
        $aMainData = parent::_getFileData($bIdIsEncrypt);

        $bIsSize = !empty($this->nWidth) || !empty($this->nHeight);
        if ($bIsSize && !empty($this->sNailDir)) {
            list($oCache, $sCacheKey, $aData) = $this->_getCacheData($aMainData);
            if (!empty($aData) && is_file($aData['filePath'])) {
                $this->sPlainContent = $aData['content'];
                return $aData;
            }
        }

        if (!empty($aMainData)) {
            if ($bIsSize) {
                $aResultData = $this->_getNailData($aMainData);
                if (!empty($aResultData['filePath'])) {
                    $oCache->set($sCacheKey, $aResultData);
                }
                $this->sPlainContent = $aResultData['content'];
            } else {
                $aResultData = $aMainData;
            }
            return $aResultData;
        }
        if (!empty($aData)) {
            $oCache->delete($sCacheKey);
        }
        return null;
    } // function _getNailFileData

    /**
     * Get stub of nail for admin
     * file data or null - if the file is not valid
     * @return array|null
     */
    protected function _getStubFileData()
    {
        $sNailStub = \bootstrap::parsePath($this->oConfig->get('nail_stub', '{PROJECT}/data/image/empti_nail.gif'));
        if (!empty($sNailStub) && is_readable($sNailStub)) {
            $aImgData = getimagesize($sNailStub);
            if (!empty($aImgData)) {
                $aPathInfo = pathinfo($sNailStub);
                return array(
                    'filePath' => $sNailStub,
                    'content' => null,
                    'headers' => array(
                        'contentType' => $aImgData['mime'],
                        'filename'    => $aPathInfo['basename'],
                        'length'      => filesize($sNailStub),
                        'legthRange'  => 'bytes',
                        'modified'    => filemtime($sNailStub),
                        'cacheLimit'  => 300,
                    )
                );
            }
        }
        return null;
    } // function _getStubFileData

    /**
     * Get Directory for save Nail
     * @param string $sDirMask
     * @return - array file data, null - if the file is not valid
     */
    protected function _getNailDir($sDirMask, $bIsException = false)
    {
        $sNailDir = empty($sDirMask) ? null : rtrim(\bootstrap::parsePath($sDirMask), '/\\');

        if (!empty($sNailDir)) {
            if (is_file($sNailDir)) {
                throw new fatalException($this, 'Incorrect path for nail. Is file there "' . $sNailDir . '"');
            } elseif (!is_dir($sNailDir)) {
                if (!mkdir($sNailDir, 0744, true)) {
                    if ($bIsException) {
                        throw new fatalException($this, 'Can\'t create directory "' . $sNailDir . '"');
                    }
                    $sNailDir = null;
                }
            } elseif (!is_writable($sNailDir)) {
                if ($bIsException) {
                    throw new fatalException($this, 'Directory "' . $sNailDir . '" isn\'t writable');
                }
                $sNailDir = null;
            }
        }

        return $sNailDir;
    } // function _getNailDir

    /**
     * Get Cache Data
     * @return array
     */
    protected function _getCacheData($aMainData)
    {
        $oCache = \fan\project\service\cache::instance('img_nail');
        /* @var $oCache \fan\core\service\cache */

        $sCacheKey = $this->mId;
        if (!empty($this->nWidth)) {
            $sCacheKey .= 'w' . $this->nWidth;
        }
        if (!empty($this->nHeight)) {
            $sCacheKey .= 'h' . $this->nHeight;
        }

        $aData = $oCache->get($sCacheKey);
        if ($aMainData['headers']['modified'] != $aData['headers']['modified']) {
            $aData = null;
        }

        return array($oCache, $sCacheKey, $aData);
    } // function _getCacheData

    /**
     * Get Nail Data
     * @param array $aMainData
     * @return array
     */
    protected function _getNailData($aMainData)
    {
        $oImg = \fan\project\service\image::instance($aMainData['filePath']);
        $oImg->scal($this->nWidth, $this->nHeight);
        $aImgData = $oImg->getImageInfo(300, empty($this->sNailDir));

        if (!empty($this->sNailDir)) {
            $sNailPath = $this->sNailDir . '/' . $this->mId . '_w' . $this->nWidth . '_h'. $this->nHeight . '.' . $oImg->getType();
            $oImg->saveAsNew($sNailPath);
        } else {
            $sNailPath = null;
        }

        return array(
            'filePath' => $sNailPath,
            'content'  => empty($this->sNailDir) ? $aImgData['content'] : null,
            'headers'  => array_merge($aImgData['headers'], array(
                'filename' => 'nail_' . $aMainData['headers']['filename'],
                'modified' => $aMainData['headers']['modified'],
            ))
        );
    } // function _getNailData

} // class \fan\core\service\plain\nail
?>