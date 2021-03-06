<?php namespace fan\core\service\cache;
use fan\project\exception\service\fatal as fatalException;
/**
 * Description of cache-engine base
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
 * @version of file: 05.02.008 (15.09.2015)
 */
abstract class base
{
    /**
     * Facade of service
     * @var \fan\core\service\cache
     */
    protected $oFacade;

    /**
     * service's configuration data
     * @var \fan\core\service\config\row
     */
    protected $oConfig;

    /**
     * Type of cached data
     * @var string
     */
    protected $sType;

    /**
     * Current cached data
     * @var string
     */
    protected $sKey;

    /**
     * Current cached data
     * @var array
     */
    protected $mData = null;
    /**
     * Current meta data
     * @var array
     */
    protected $aMetaData = array();

    /**
     * Extra Path for cache directory
     * @var string
     */
    protected $sExtraPath = null;

    /**
     * Is loaded cached data
     * @var boolean
     */
    protected $bLoaded = false;

    /**
     * Is saved cached data
     * @var boolean
     */
    protected $bSaved = true;

    /**
     * Constructor of cache-engine
     * @param \fan\core\service\cache $oFacade
     * @param string $sType
     * @param string $sKey
     * @param \fan\core\service\config\row $oConfig
     */
    public function __construct(\fan\core\service\cache $oFacade, $sType, $sKey, $oConfig)
    {
        $this->setFacade($oFacade);
        $this->sType   = $sType;
        $this->sKey    = $sKey;
        $this->oConfig = $oConfig;
    } // function __construct

   /**
     * Destructor of cache-engine
     */
    public function __destruct()
    {
        if ($this->bLoaded && !$this->bSaved) {
            $this->_saveData();
        }
    } // function __destruct

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\

    /**
     * Get data value
     * @param mixed $mDefault
     * @return mixed
     */
    public function get($mDefault = null)
    {
        if (!$this->isLoaded()) {
            if ($this->_loadData(false)) {
                $this->bLoaded = true;
                $this->bSaved  = true;
            }
        }
        return is_null($this->mData) ? $mDefault : $this->mData;
    } // function get

    /**
     * Set data value
     * @param mixed $mValue
     * @param boolean $bAutoSave
     * @return \fan\core\service\cache\base
     */
    public function set($mValue, $bAutoSave)
    {
        $this->mData   = $mValue;
        $this->bLoaded = true;
        $this->bSaved  = false;
        $this->_makeNewMeta();
        if ($bAutoSave) {
            $this->save();
        }
        return $this;
    } // function set

    /**
     * Add Meta-data
     * @param mixed $mMetaData
     * @return \fan\core\service\cache\base
     * @throws \fan\core\exception\service\fatal
     */
    public function addMeta($mMetaData)
    {
        if (is_object($mMetaData) && method_exists($mMetaData, 'toArray')) {
            $mMetaData = $mMetaData->toArray();
        } elseif (!is_array($mMetaData)) {
            throw new fatalException($this->oFacade, 'Incorrect cache Meta-date.');
        }
        $this->aMetaData = array_merge($this->aMetaData, $mMetaData);
        return $this;
    } // function addMeta

    /**
     * Get Meta-data
     * @param boolean $bLoadMetaOnly
     * @return array
     */
    public function getMeta($bLoadMetaOnly)
    {
        if (!$this->isLoaded()) {
            if ($this->_loadData($bLoadMetaOnly)) {
                $this->bLoaded = true;
                $this->bSaved  = true;
            }
        }
        return $this->aMetaData;
    } // function getMeta

    /**
     * Get Extra Meta-data
     * @param string $sParam
     * @return mixed
     */
    public function getExtraMeta($sParam)
    {
        $aMeta = $this->getMeta(false);
        return isset($aMeta['extra'][$sParam]) ? $aMeta['extra'][$sParam] : null;
    } // function getExtraMeta

    /**
     * Set Extra Meta-data
     * @param string $sParam
     * @param mixed $mValue
     * @return \fan\core\service\cache\base
     */
    public function setExtraMeta($sParam, $mValue)
    {
        $aMeta = $this->getMeta(false);
        if (empty($aMeta)) {
            $this->aMetaData['extra'] = array();
        }
        $this->aMetaData['extra'][$sParam] = $mValue;
        $this->_saveData();
        return $this;
    } // function setExtraMeta

    /**
     * Set Lifetime
     * @param integer $iTime
     * @return \fan\core\service\cache\base
     */
    public function setLifetime($iTime)
    {
        $this->aMetaData['lifetime'] = (int)$iTime;
        $this->isActual();
        return $this;
    } // function setLifetime

    /**
     * Start time of create cache must be later than pointed
     * @param string $sDateTime
     * @return \fan\core\service\cache\base
     */
    public function setStartLimit($sDateTime)
    {
        $this->_checkDateFormat($sDateTime);

        $aMeta = $this->getMeta(false);
        if ($aMeta['create_date'] < $sDateTime) {
            $this->delete();
        }
        return $this;
    } // function setStartLimit

    /**
     * Check is data Actual
     * @return boolean
     */
    public function isActual()
    {
        return $this->_checkActual($this->getMeta(true));
    } // function isActual

    /**
     * Save cache-data
     * @return \fan\core\service\cache\base
     */
    public function save()
    {
        if ($this->bLoaded && !$this->bSaved) {
            $this->_saveData();
            $this->bSaved = true;
        }
        return $this;
    } // function save

    /**
     * Delete cache-data
     * @return \fan\core\service\cache\base
     */
    public function delete()
    {
        $this->bLoaded = true;
        $this->bSaved  = true;
        $this->_deleteData();
        return $this;
    } // function delete

    /**
     * Set Extra Path for cahce directory
     * @param string $sExtraPath
     * @return \fan\core\service\cache\base
     */
    public function setExtraPath($sExtraPath)
    {
        $this->sExtraPath = $sExtraPath;
        return $this;
    } // function setExtraPath

    /**
     * Check is data loaded
     * @return boolean
     */
    public function isLoaded()
    {
        return $this->bLoaded;
    } // function isLoaded

    /**
     * Check is data saved
     * @return boolean
     */
    public function isSaved()
    {
        return $this->bSaved;
    } // function isSaved

    /**
     * Set Facade
     * @param \fan\core\base\service $oFacade
     * @return \fan\core\service\database\base
     */
    public function setFacade(\fan\core\base\service $oFacade)
    {
        if (empty($this->oFacade)) {
            $this->oFacade = $oFacade;
        }
        return $this;
    } // function setFacade

    // ======== Private/Protected methods ======== \\
    /**
     * Method for load data from cache
     * Must define property $this->mData and $this->aMetaData
     * @param boolean $bLoadMetaOnly
     */
    abstract protected function _loadData($bLoadMetaOnly);

    /**
     * Method for save data to cache
     * Must define property $this->mData and $this->aMetaData
     */
    abstract protected function _saveData();


    /**
     * Delete cached data
     * @return \fan\core\service\database\base
     */
    protected function _deleteData()
    {
        $this->mData     = null;
        $this->aMetaData = array();
        return $this;
    } // function _deleteData

    /**
     * Make New Meta
     * @return \fan\core\service\database\base
     */
    protected function _makeNewMeta()
    {
        $this->aMetaData['data_type']   = strtolower(gettype($this->mData));
        $this->aMetaData['create_date'] = date('Y-m-d H:i:s');
        if (!isset($this->aMetaData['lifetime'])) {
            $this->aMetaData['lifetime'] = isset($this->oConfig['LIFETIME']) ? (int)$this->oConfig['LIFETIME'] : 0;
        }
        return $this;
    } // function _makeNewMeta

    /**
     * Unserialize string
     * @param string $sData
     * @return mixed
     */
    protected function _unserialize($sData)
    {
        $mResult = @unserialize($sData);
        return $mResult === false && $sData != serialize(false) ? null : $mResult;
    } // function _unserialize

    /**
     * @param array $aMeta
     * @param boolean $bDeleteExired
     * @return boolean
     */
    protected function _checkActual($aMeta, $bDeleteExired = true)
    {
        if (!isset($aMeta['create_date']) || (!empty($aMeta['lifetime']) && $aMeta['create_date'] < date('Y-m-d H:i:s', time() - $aMeta['lifetime']))) {
            if ($bDeleteExired) {
                $this->_deleteData();
            }
            return false;
        }
        return true;
    } // function _checkActual

    /**
     * Check Date Format
     * @param string $sDateTime
     * @return \fan\core\service\cache\base
     * @throws \fan\core\exception\service\fatal
     */
    protected function _checkDateFormat($sDateTime)
    {
        if (!preg_match('/^(\d{4})\-(\d{2})\-(\d{2})\s(\d{2})\:(\d{2})\:(\d{2})$/', $sDateTime, $aMatches)) {
            throw new fatalException($this->oFacade, 'Incorrect date format.');
        }
        // ToDo: Check values of number
        return $this;
    } // function _checkDateFormat

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\

} // class \fan\core\service\cache\base
?>