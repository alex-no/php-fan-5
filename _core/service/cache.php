<?php namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
use fan\project\exception\error500 as error500;
/**
 * Cache service
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
class cache extends \fan\core\base\service\multi
{
    /**
     * Type of cache for config
     */
    const CONFIG_TYPE = 'config';

    /**
     * Service's Instances
     * @var \fan\core\service\cache[]
     */
    private static $aInstances = array();

    /**
     * Current cached data
     * @var string
     */
    protected $sType;

    /**
     * Engines of current cache type
     * @var \fan\core\service\cache\base[]
     */
    protected $aEngine = null;

    /**
     * Configuration of Cache-config
     * @var array
     */
    protected $aConfigCache = null;

    /**
     * Service's constructor
     * @param string $sType
     * @param string $sGroup
     */
    protected function __construct($sType)
    {
        if ($sType == self::CONFIG_TYPE) {
            $this->aConfigCache = \bootstrap::getConfigCache();
            if (empty($this->aConfigCache)) {
                throw new \Exception('Config Cache in bootstrap isn\'t defined.', E_USER_ERROR);
            }
        } else {
            parent::__construct(empty(self::$aInstances));
        }
        if (!isset(self::$aInstances[$sType])) {
            self::$aInstances[$sType] = $this;
        }
        $this->sType = $sType;
    } // function __construct

    // ======== Static methods ======== \\
    /**
     * Get Instance of cache for service of config
     * @return \fan\core\service\cache
     * @throws error500
     */
    public static function configInstance()
    {
        try {
            if (!empty(self::$aInstances)) {
                throw new error500('It\'s inpossible to get config-Instance after make another Instances.', E_USER_ERROR);
            }
            $oConfigCache = new self(self::CONFIG_TYPE);
            $oConfigCache->get('service');
        } catch (\Exception $oExc) {
            \bootstrap::logError($oExc->getMessage());
            return null;
        }
        return self::$aInstances[self::CONFIG_TYPE];
    } // function instance

    /**
     * Get instance of cache service
     * @param string $sType
     * @return \fan\core\service\cache
     * @throws fatalException
     */
    public static function instance($sType = null)
    {
        if (is_null($sType)) {
            $oConfig = \fan\project\service\config::instance();
            $sType   = $oConfig->get('cache')->get('DEFAULT_TYPE');
            if (empty($sType)) {
                throw new fatalException($oConfig, 'Default CACHE-type doesn\'t set in config-file.');
            }
        }
        if ($sType == self::CONFIG_TYPE) {
            throw new error500('It\'s inpossible to get config-Instance by usual way.', E_USER_ERROR);
        }
        if (!isset(self::$aInstances[$sType])) {
            new self($sType);
        }
        return self::$aInstances[$sType];
    } // function instance

    // ======== Main Interface methods ======== \\

    /**
     * Get data value
     * @param string $sKey
     * @param mixed $mDefault
     * @return mixed
     */
    public function get($sKey, $mDefault = null)
    {
        return $this->getEngine($sKey)->get($mDefault);
    } // function get

    /**
     * Set data value
     * @param string $sKey
     * @param mixed $mValue
     * @param boolean $bAutoSave
     * @return \fan\core\service\cache
     */
    public function set($sKey, $mValue, $bAutoSave = true)
    {
        $this->getEngine($sKey)->set($mValue, $bAutoSave);
        return $this;
    } // function set

    /**
     * Get Cached value by Key OR if not exists Make New data by callback-function
     * @param string $sKey
     * @param mixed $mCallBack
     * @param boolean $bAutoSave
     * @return mixed
     * @throws fatalException
     */
    public function getOrDefine($sKey, $mCallBack, $bAutoSave = true)
    {
        $oEngine = $this->getEngine($sKey);
        if ($oEngine->isLoaded()) {
            $mResult = $oEngine->get();
        } elseif (is_callable($mCallBack)) {
            $mResult = call_user_func($mCallBack, $sKey);
            $oEngine->set($mResult, $bAutoSave);
        } else {
            throw new fatalException($this, 'Callback for cache is not callable.');
        }
        return $mResult;
    } // function getOrDefine

    /**
     * Get Meta-data
     * @param string $sKey
     * @param boolean $bLoadMetaOnly
     * @return array
     */
    public function getMeta($sKey, $bLoadMetaOnly = false)
    {
        return $this->getEngine($sKey)->getMeta($bLoadMetaOnly);
    } // function getMeta

    /**
     * Get Extra Meta-data
     * @param string $sKey
     * @param string $sParam
     * @return mixed
     */
    public function getExtraMeta($sKey, $sParam)
    {
        return $this->getEngine($sKey)->getExtraMeta($sParam);
    } // function getExtraMeta

    /**
     * Set Extra Meta-data
     * @param string $sKey
     * @param string $sParam
     * @param mixed $mValue
     * @return \fan\core\service\cache
     */
    public function setExtraMeta($sKey, $sParam, $mValue)
    {
        $this->getEngine($sKey)->setExtraMeta($sParam, $mValue);
        return $this;
    } // function setExtraMeta

    /**
     * Set Extra Meta-data
     * @param string $sKey
     * @param string $sParam
     * @param mixed $mValue
     * @return \fan\core\service\cache
     */
    public function checkExtraMeta($sKey, $sParam, $mValue, $sMethod = 'equal', $bAllowDelete = false)
    {
        $mSrcValue = $this->getExtraMeta($sKey, $sParam);
        if (is_null($mSrcValue)) {
            return true;
        }

        switch ($sMethod) {
        case 'equal':
            $bResult = $mValue == $mSrcValue;
            break;

        case 'not_equal':
            $bResult = $mValue != $mSrcValue;
            break;

        case 'less':
            $bResult = $mValue < $mSrcValue;
            break;

        case 'more':
            $bResult = $mValue > $mSrcValue;
            break;

        case 'less_or_equal':
            $bResult = $mValue <= $mSrcValue;
            break;

        case 'more_or_equal':
            $bResult = $mValue >= $mSrcValue;
            break;

        default:
            throw new fatalException($this, 'Incorrect check method "' . $sMethod . '", for verify Extra Meta.');
        }

        if (!$bResult && $bAllowDelete) {
            $this->delete($sKey);
        }
        return $bResult;
    } // function checkExtraMeta

    /**
     * Check is data Actual
     * @return boolean
     */
    public function isActual($sKey)
    {
        return $this->getEngine($sKey)->isActual();
    } // function isActual

    /**
     * Set Lifetime
     * @param string $sKey
     * @param integer $iTime
     * @return \fan\core\service\cache
     */
    public function setLifetime($sKey, $iTime)
    {
        $this->getEngine($sKey)->setLifetime($iTime);
        return $this;
    } // function setLifetime

    /**
     * Start time of create cache must be later than pointed
     * @param string $sKey
     * @param string $sDateTime
     * @return boolean
     */
    public function setStartLimit($sKey, $sDateTime)
    {
        $this->getEngine($sKey)->setStartLimit($sDateTime);
        return $this->isActual($sKey);
    } // function setStartLimit

    /**
     * Compare date/time of Source File with date/time of cache
     * @param string $sKey
     * @param string $sFilePath
     * @return boolean
     * @throws fatalException
     */
    public function checkSourceFile($sKey, $sFilePath)
    {
        if (!is_file($sFilePath)) {
            throw new fatalException($this, 'Incorrect path to  file "' . $sFilePath . '".');
        }
        return  $this->checkExtraMeta($sKey, 'file_size', filesize($sFilePath), 'equal', true) &&
                $this->setStartLimit($sKey, date ('Y-m-d H:i:s', filemtime($sFilePath)));
    } // function checkSourceFile

    /**
     * Save cahe-data
     * @param string $sKey
     * @return \fan\core\service\cache
     */
    public function save($sKey)
    {
        $this->getEngine($sKey)->save();
        return $this;
    } // function save

    /**
     * Delete cahe-data
     * @param string $sKey
     * @return \fan\core\service\cache
     */
    public function delete($sKey)
    {
        $this->getEngine($sKey)->delete();
        return $this;
    } // function delete

    /**
     * Set Extra Path for cahce directory
     * Allows to separate data for subdirectories
     * @param type $sKey
     * @param type $sExtraPath
     * @return \fan\core\service\cache
     */
    public function setExtraPath($sKey, $sExtraPath)
    {
        $this->getEngine($sKey)->setExtraPath($sExtraPath);
        return $this;
    } // function setExtraPath

    /**
     * Check is data saved
     * @param string $sKey
     * @return boolean
     */
    public function isSaved($sKey)
    {
        return $this->getEngine($sKey)->isSaved();
    } // function isSaved

    /**
     * Get Data-engine
     * @param string $sKey
     * @return \fan\core\service\cache\base
     */
    public function getEngine($sKey)
    {
        if (!isset($this->aEngine[$sKey])) {
            if ($this->sType == self::CONFIG_TYPE) {
                $aConfig = $this->aConfigCache;
            } elseif (($oConfig = $this->getConfig(array('TYPE', $this->sType)))) {
                $aConfig = $oConfig->toArray();
            } else {
                throw new fatalException($this, 'Not found configuration for "' . $this->sType . '".');
            }

            if (empty($aConfig['ENGINE'])) {
                throw new fatalException($this, 'Cache engine isn\'t defined.');
            }
            $sClass  = $this->_getEngine($aConfig['ENGINE'], false);
            $this->aEngine[$sKey] = new $sClass($this, $this->sType, $sKey, $aConfig);
        }
        return $this->aEngine[$sKey];
    } // function getEngine


    // ======== Private/Protected methods ======== \\

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\

} // class \fan\core\service\cache
?>