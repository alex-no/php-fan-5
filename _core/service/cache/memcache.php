<?php namespace core\service\cache;
use project\exception\service\fatal as fatalException;
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
 * @version of file: 05.001 (29.09.2011)
 */
class memcache extends base
{
    /**
     * Keepers of Memcache
     * @var string
     */
    private static $aKeepers = array();

    /**
     * Method for load data from cache
     * Must define property $this->mData and $this->aMetaData
     */
    protected function _loadData($bLoadMetaOnly)
    {
        $oKeeper         = $this->_getKeeper();
        $aMetaData       = $oKeeper->get($this->_getKey('meta'));
        $this->aMetaData = !$aMetaData ? array() : $aMetaData;
        if (!$this->_checkActual($this->aMetaData) || $bLoadMetaOnly) {
            return false;
        }

        $this->mData = $oKeeper->get($this->_getKey('data'));
        return true;
    }

    /**
     * Method for save data to cache
     * Must define property $this->mData and $this->aMetaData
     */
    protected function _saveData()
    {
        $oKeeper = $this->_getKeeper();
        $oKeeper->set($this->_getKey('meta'), $this->aMetaData, 0, (int)$this->aMetaData['lifetime']);
        $oKeeper->set($this->_getKey('data'), $this->mData,     0, (int)$this->aMetaData['lifetime']);
    }

    /**
     * Delete cached data
     */
    protected function _deleteData()
    {
        $oKeeper = $this->_getKeeper();
        $oKeeper->delete($this->_getKey('meta'));
        $oKeeper->delete($this->_getKey('data'));
        parent::_deleteData();
        return $this;
    }

    /**
     * Get Keeper - instance of Memcache
     * @return \Memcache
     * @throws fatalException
     */
    protected function _getKeeper()
    {
        if (empty(self::$aKeepers[$this->sType])) {
            if (!class_exists('\Memcache')) {
                $sErrMsg = 'Memcache doesn\'t setup there.';
                if ($this->sType == 'config') {
                    throw new \core\exception\fatal($sErrMsg);
                } else {
                    throw new fatalException($this->oFacade, $sErrMsg);
                }
            }
            self::$aKeepers[$this->sType] = new \Memcache();
            self::$aKeepers[$this->sType]->addServer(
                isset($this->aConfig['HOST']) ? $this->aConfig['HOST'] : 'localhost',
                isset($this->aConfig['PORT']) ? $this->aConfig['PORT'] : 11211
            );
        }
        return self::$aKeepers[$this->sType];
    }

    /**
     * Get key for save data
     * @param string $sSuffix
     * @return string
     */
    protected function _getKey($sSuffix)
    {
        return $this->sType . '-' . $this->sKey . '-' . $sSuffix;
    }

} // class \core\service\cache\memcache
?>