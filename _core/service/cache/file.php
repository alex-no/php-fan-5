<?php namespace fan\core\service\cache;
use fan\project\exception\service\fatal as fatalException;
/**
 * ADOdb wrapper for template engine
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
class file extends base
{
    /**
     * Path to File Data
     * @var string
     */
    private $sFileData = null;
    /**
     * Path to File Meta
     * @var string
     */
    private $sFileMeta = null;

    /**
     * Method for load data from cache
     * Must define property $this->mData and $this->aMetaData
     */
    protected function _loadData($bLoadMetaOnly)
    {
        list($sFileData, $sFileMeta) = $this->_getFilePath();
        if (!file_exists($sFileMeta)) {
            return false;
        }

        $aMetaData       = $this->_unserialize($this->_readFile($sFileMeta));
        $this->aMetaData = is_null($aMetaData) ? array() : $aMetaData;
        if (is_null($aMetaData) || !$this->_checkActual($aMetaData) || !file_exists($sFileData) || $bLoadMetaOnly) {
            return false;
        }

        if ($this->aMetaData['data_type'] == 'null') {
            $this->mData = null;
        } else {
            $sData       = $this->_readFile($sFileData);
            $this->mData = $this->aMetaData['data_type'] == 'string' ? $mData : $this->_unserialize($sData);
        }
        return true;
    }

    /**
     * Method for save data to cache
     * Must define property $this->mData and $this->aMetaData
     */
    protected function _saveData()
    {
        list($sFileData, $sFileMeta) = $this->_getFilePath();
        $this->_checkWritable($sFileMeta, 'meta');
        $this->_checkWritable($sFileData, 'data');

        file_put_contents($sFileMeta, serialize($this->aMetaData), LOCK_EX);
        if ($this->aMetaData['data_type'] == 'string') {
            file_put_contents($sFileData, $this->mData, LOCK_EX);
        } elseif ($this->aMetaData['data_type'] != 'null') {
            file_put_contents($sFileData, serialize($this->mData), LOCK_EX);
        }
        return $this;
    }

    /**
     * Delete cached data
     */
    protected function _deleteData()
    {
        list($sFileData, $sFileMeta) = $this->_getFilePath();
        if (file_exists($sFileMeta)) {
            unlink($sFileMeta);
        }
        if (file_exists($sFileData)) {
            unlink($sFileData);
        }
        parent::_deleteData();
        return $this;
    }

    protected function _getFilePath()
    {
        if (empty($this->sFileData) || empty($this->sFileMeta)) {
            if (empty($this->oConfig['BASE_DIR'])) {
                throw new fatalException($this->oFacade, 'Base cache doesn\'t set for "' . $this->sType . '".');
            }
            $sPath = rtrim(\bootstrap::parsePath($this->oConfig['BASE_DIR']), '/\\');
            if (!empty($this->sExtraPath)) {
                $sPath .= '/' . trim($this->sExtraPath, '/\\');
            }
            if (!is_dir($sPath)) {
                $nDirMode = empty($this->oConfig['DIR_MODE']) ? 0777 : $this->oConfig['DIR_MODE'];
                if (!mkdir($sPath, $nDirMode, true)) {
                    throw new fatalException($this->oFacade, 'Can\'t create cache directory for "' . $this->sType . '".');
                }
            } elseif (!is_writable($sPath)) {
                throw new fatalException($this->oFacade, 'Cache directory for "' . $this->sType . '" isn\'t writable.');
            }

            if (empty($this->oConfig['CODE_FILE_NAME'])) {
                $sFileName = $this->sKey;
                if (!preg_match('/^[a-z0-9\-_\(\)\!\.]+$/i', $sFileName) || substr($sFileName, -5) == '.meta') {
                    throw new fatalException($this->oFacade, 'Cache key "' . $this->sKey . '" can\'t be used for name of cache file.');
                }
            } else {
                $sFileName = md5($this->sKey);
            }

            $sFileExt = isset($this->oConfig['FILE_EXT']) ? $this->oConfig['FILE_EXT'] : 'cache';

            $this->sFileData = $sPath . '/' . $sFileName . (empty($sFileExt) ? '' : '.' . $sFileExt);
            $this->sFileMeta = $sPath . '/' . $sFileName . '.meta';
        }
        return array(
            $this->sFileData,
            $this->sFileMeta,
        );
    }

    protected function _checkWritable($sFilePath, $sType)
    {
        if (is_file($sFilePath) && !is_writable($sFilePath)) {
            throw new fatalException($this->oFacade, 'Cache ' . $sType . '-file "' . $sFilePath . '" isn\'t writable.');
        }
    }

    protected function _readFile($sFilePath)
    {
        $mResult = file_get_contents($sFilePath);
        if ($mResult === false) {
            throw new fatalException($this->oFacade, 'Cache file "' . $sFilePath . '" isn\'t readable.');
        }
        return $mResult;
    }

} // class \fan\core\service\cache\file
?>