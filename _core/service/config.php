<?php namespace core\service;
use \core\service\config\row as row;
use project\exception\service\fatal as fatalException;
/**
 * Configuration manager service
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
 * @version of file: 05.006 (11.02.2014)
 */
final class config extends \core\base\service\multi
{
    /**
     * @var array Service's Instances
     */
    protected static $aInstances = array();
    /**
     * Service's Egines by file types
     * @var array
     */
    protected static $aEgines = array();
    /**
     * Instance of Cache servise
     * @var \core\service\cache
     */
    protected static $oCache = null;

    /**
     * Config of this Service
     * @var \core\service\config\row
     */
    private static $oThisConf = null;
    /**
     * List of Application-depended configuration files
     * @var array
     */
    private static $aAppDepended = array();

    /**
     * @var string Type of configuration data
     */
    private $sConfigType = null;
    /**
     * @var string Type of Source file (ini, xml, yaml, etc)
     */
    private $sSourceType = null;

    /**
     * @var \core\service\config\row Row of configuration data
     */
    private $oConfData;

    /**
     * Constructor of Service config
     * @param string $sConfigType
     * @param string $sSourceType
     * @throws \project\exception\service\fatal
     */
    protected function __construct($sConfigType, $sSourceType)
    {
        $this->sConfigType = $sConfigType;
        $this->sSourceType = $sSourceType;

        self::$aInstances[$sConfigType] = $this;

        $sMethod = $sConfigType == 'service' ? '_initServiceConfig' : '_initOtherConfig';
        $this->$sMethod();

        parent::__construct();
    } // function __construct

    // ======== Static methods ======== \\
    /**
     * Get Service's instance of current service by $sConfigType
     * @param string $sConfigType Config Type - by default = 'service'
     * @param string $sSourceType Type of Source - by default = 'ini'
     * @return config
     */
    public static function instance($sConfigType = 'service', $sSourceType = 'ini') {
        if (!isset(self::$aInstances[$sConfigType])) {
            new self($sConfigType, $sSourceType);
        }

        return self::$aInstances[$sConfigType];
    } // function instance

    /**
     * Merge all configuration file by application
     * @param string $sAppName
     */
    public static function mergeByApp($sAppName)
    {
        foreach (self::$aAppDepended as $k => $v) {
            $sConfFile = str_replace('{APP_NAME}', $sAppName, $v);
            self::instance($k)->_mergeConfig($sConfFile, true, false);
        }
    } // function mergeByApp

    // ======== Main Interface methods ======== \\
    /**
     * Get configuration data for $sName
     * @param string $sName - name of section of config-file
     * @param string|array $mKey - key of variable
     * @return array
     */
    public function get($sName, $mKey = null)
    {
        $oConf = $this->oConfData[$sName];
        return empty($oConf) ? null : (is_null($mKey) ? $oConf : $oConf->get($mKey));
    } // function get

    /**
     * Get source configuration data for $sName
     * @param string $sName
     * @param mixed $mKey - key of variable
     * @return array
     */
    public function getSrc($sName, $mKey = null)
    {
        $oConf = $this->oConfData[$sName];
        if (empty($oConf)) {
            return null;
        }
        if (is_null($mKey)) {
            return $oConf->getSources();
        }
        $oSubConf = $oConf->get($mKey);
        return empty($oSubConf) ? null : $oSubConf->getSources();
    } // function getSrc

    /**
     * Set configuration data for $sName
     * @param string $sName
     * @param string $mKey Key of variable
     * @param string $mValue Value of variable
     * @param boolean $bRewriteExisting
     * @return \core\service\config
     */
    public function set($sName, $mKey, $mValue, $bRewriteExisting = true)
    {
        $oConf = $this->oConfData[$sName];
        if (empty($oConf)) {
            $oConf = $this->oConfData->set($sName, array());
        }
        $oConf->set($mKey, $mValue, $bRewriteExisting, true);
        return $this;
    } // function set

    /**
     * Merge new config data with previous values
     * @param array|\core\service\config\row $aData
     * @param booulean $bPriority
     * @return \core\service\config
     */
    public function merge($aData, $bPriority = true)
    {
        if (!is_array($aData) && !$this->_isRow($aData)) {
            throw new fatalException($this, 'Incorrect data for merge configs');
        }
        if (!empty($aData)) {
            $this->oConfData->mergeData($aData, $bPriority);
        }
        return $this;
    } // function merge

    /**
     * Reset Data applicaiton's config
     * @param string $sName Service's name
     * @param mixed $mKey Key of parameter
     * @return \core\service\config
     */
    public function reset($sName = null, $mKey = null)
    {
        if (is_null($sName)) {
            $this->oConfData->reset(null);
        } else {
            $oConf = $this->oConfData[$sName];
            if ($this->_isRow($oConf)) {
                if (is_array($mKey)) {
                    $sKey = array_pop($mKey);
                    if (!empty($mKey)) {
                        $oConf = $oConf->get($mKey);
                    }
                    if ($this->_isRow($oConf)) {
                        $oConf->reset($sKey);
                    }
                } else {
                    $oConf->reset($mKey);
                }
            }
        }
        return $this;
    } // function reset

    /**
     * Get Type of current Config
     * @return string
     */
    public function getConfigType()
    {
        return $this->sConfigType;
    } // function getConfigType

    /**
     * Get Service Config by Instace of Service
     * @param \core\base\service $oService
     * @return \core\service\config\row
     */
    public function getServiceConfig(\core\base\service $oService)
    {
        $sName = get_class_name($oService);
        if (empty($this->oConfData)) {
            throw new \core\exception\error500('Data row isn\'t for config "' . $this->sConfigType . '"');
        }
        if (!$this->oConfData[$sName]) {
            $this->oConfData->set($sName, array());
            /*
            // ToDo: Check code above
            $this->oConfData[$sName] = new \project\service\config\row(array(), $sName, $this->oConfData);
            $this->oConfData[$sName]->setFacade($this);
             */
        }
        $this->oConfData[$sName]->setServiceOwner($oService);
        return $this->oConfData[$sName];
    } // function getServiceConfig
    /**
     * Get configs of plain/cli controllers
     * @param object $oCtrl
     * @param string $sName
     * @return \core\service\config\row
     * @throws \core\exception\error500
     */
    public function getControllerConfig($oCtrl, $sName)
    {
        if (empty($this->oConfData)) {
            throw new \core\exception\error500('Data row isn\'t for config "' . $this->sConfigType . '"');
        }
        if (!$this->oConfData[$sName]) {
            $this->oConfData->set($sName, array());
        }
        if (is_object($oCtrl)) {
            $this->oConfData[$sName]->setPlainOwner($oCtrl, $sName);
        }
        return $this->oConfData[$sName];
    } // function getControllerConfig
    /**
     * Get Entity Config by Instace of Entity
     * @param \core\base\model\entity $oEntity
     * @return \core\service\config\row
     */
    public function getEntityConfig(\core\base\model\entity $oEntity, $sName = null)
    {
        if (is_null($sName)) {
            $sName = $oEntity->getTableName();
        }

        $oEttConf    = $this->oConfData['entity'];
        $oCommonConf = $this->oConfData['common'];

        if (is_null($oEttConf->get($sName))) {
            $oEttConf->set($sName, array());
        }
        $oEttConf[$sName]->setEntityOwner($oEntity, $sName);
        if (!empty($oCommonConf)) {
            $oEttConf[$sName]->mergeData($oCommonConf, false);
        }
        return $oEttConf[$sName];
    } // function getEntityConfig


    // ======== Private/Protected methods ======== \\
    /**
     * Init Service Config
     * @return config
     */
    protected function _initServiceConfig()
    {
        self::$oCache    = \project\service\cache::configInstance();

        $this->oConfData = new row($this->_getData('service'));
        $this->oConfData->setFacade($this);

        self::$oThisConf = $this->getServiceConfig($this);
        $this->oConfig   = self::$oThisConf;

        if ($this->oConfig['app_file']) {
            self::$aAppDepended = $this->oConfig['app_file']->toArray();
        }
        $this->_subscribeForService('application', 'setAppName', array(get_class($this), 'mergeByApp'));

        return $this;
    } // function _initServiceConfig

    /**
     * Init Config Other type
     * @return config
     */
    protected function _initOtherConfig()
    {
        if (empty(self::$oThisConf)) {
            config::instance('service');
        }
        $this->oConfig   = clone self::$oThisConf;

        $sFileName       = $this->getConfig(array('file', $this->sConfigType), $this->sConfigType);
        $this->oConfData = new row($this->_getData($sFileName));
        $this->oConfData->setFacade($this);

        return $this;
    } // function _initOtherConfig

    /**
     * Get Data from cache or source file
     * @param string $sFileName
     * @param boolean $bCheckExist
     * @return array
     */
    protected function _getData($sFileName, $bCheckExist = true)
    {
        $oEngine   = $this->_getConfigEngine();
        $sFilePath = $oEngine->getFilePath($sFileName, $bCheckExist);
        if (!empty(self::$oCache)) {
            $aData = self::$oCache->get($sFileName);
            if (!empty($aData) && self::$oCache->checkSourceFile($sFileName, $sFilePath)) {
                return $aData;
            }
        }

        $aData = $oEngine->loadFile($sFilePath, $this->sConfigType);
        if (!empty($aData) && !empty(self::$oCache)) {
            self::$oCache->set($sFileName, $aData);
            self::$oCache->setExtraMeta($sFileName, 'file_size', filesize($sFilePath));
        }

        return $aData;
    } // function _getData

    /**
     * Set service's Config
     * @return \core\service\config
     */
    protected function _setConfig()
    {
        return $this;
    } // function _setConfig

    /**
     * Get Config Engine
     * @return \core\service\config\ini
     */
    protected function _getConfigEngine()
    {
        $sType = $this->sSourceType;
        if (!isset(self::$aEgines[$sType])) {
            $oEngine = parent::_getEngine($sType, true);
            if (empty($oEngine)) {
                throw new \project\exception\service\fatal($this, 'Unknown engine type!');
            }
            self::$aEgines[$sType] = $oEngine;
            $oEngine->setDirPath(
                \bootstrap::getGlobalPath('config_source', '{PROJECT_DIR}/conf')
            );
        }
        return self::$aEgines[$sType];
    } // function _getConfigEngine

    /**
     * Load Applicaiton's config and merge it with global configuration
     * @param string $sFileName Configuration file name
     * @param string $bResetConf Allow to reset configuration file before marging
     * @param string $bCheckExist Check - is file name
     * @return \core\service\config
     */
    protected function _mergeConfig($sFileName, $bResetConf, $bCheckExist)
    {
        if ($bResetConf) {
            $this->reset();
        }
        $this->oConfData->mergeData($this->_getData($sFileName, $bCheckExist), $bResetConf);
        return $this;
    } // function _mergeConfig

    /**
     * Check is instance of row
     * @param \core\service\config\row $oObj
     * @return boolean
     */
    protected function _isRow($oObj)
    {
        return is_object($oObj) && $oObj instanceof row;
    } // function _isRow

} // class \core\service\config
?>