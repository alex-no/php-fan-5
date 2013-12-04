<?php namespace core\base;
use project\exception\service\fatal as fatalException;
/**
 * Base abstract service
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
 * @abstract
 */
abstract class service
{
    /**
     * Listeners of all services
     * @var array
     */
    private static $aListeners = array();

    /**
     * service's configuration data
     * @var \core\service\config\row
     */
    protected $oConfig = null;

    /**
     * @var boolean Enabled status of the service
     */
    protected $bEnabled = true;

    /**
     * @var array
     */
    protected $aDelegate = array();

    /**
     * @var array
     */
    protected $aDelegateRule = array();

    /**
     * service's constructor
     * @param boolean $bAllowIni
     */
    protected function __construct($bAllowIni = true)
    {
        if ($bAllowIni) {
            \bootstrap::getInitializer()->setServiceParam(get_class($this));
        }
        $this->_saveInstance()->_setConfig()->resetEnabled();
    } // function __construct

    // ======== Static methods ======== \\

    /**
     * Get service's instance by class name
     * @return object Aservice Service's instance
     */
    public static function checkName($sName)
    {
        return substr($sName, 0, 4) == 'core' ? 'project' . substr($sName, 4) : $sName;
    } // function checkName

    // ======== Main Interface methods ======== \\

    /**
     * Is singleton
     * @return boolean
     */
    abstract public function isSingleton();

    /**
     * Check is service enabled
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->bEnabled;
    } // function isEnabled

    /**
     * Reset flag of enabled
     */
    public function resetEnabled()
    {
        $this->bEnabled = (boolean)$this->getConfig('ENABLED', true);
    } // function resetEnabled

    /**
     * Get service's Config
     * @param string $mKey Config key
     * @return mixed
     */
    public function getConfig($mKey = null, $mDefault = null)
    {
        return is_null($mKey) || is_null($this->oConfig) ? $this->oConfig : $this->oConfig->get($mKey, $mDefault);
    } // function getConfig

    /**
     * Add listener to service
     * @param string $sEventName
     * @param callback $mCallBack
     * @return \core\base\service
     */
    public function addListener($sEventName, $mCallBack)
    {
        $this->_subscribeForService(get_class_name($this), $sEventName, $mCallBack);
        return $this;
    } // function addListener

    // ======== Private/Protected methods ======== \\

    /**
     * Save service's Instance
     * @return \core\base\service
     */
    protected function _saveInstance()
    {
        return $this;
    } // function _saveInstance

    /**
     * Set service's Config
     * @return object Aservice Service's instance
     */
    protected function _setConfig()
    {
        $this->oConfig = $this->_getConfigurator()->getServiceConfig($this);
        return $this;
    } // function setConfig

    /**
     * Get Configurator
     * @return \core\service\config
     */
    protected function _getConfigurator()
    {
        return \project\service\config::instance();
    } // function _getConfigurator

    /**
     * Get Service Engine
     * @param string $sClass
     * @return object|string
     */
    protected function _getEngine($sName, $bObject = true)
    {
        $sClass = get_class($this) . '\\' . $sName;
        if (substr($sClass, 0, 5) == 'core\\') {
            $sClass = 'project\\' . substr($sClass, 5);
        }

        if (!\bootstrap::loadClass($sClass, true)) {
            return null;
        }

        $sClass = '\\' . $sClass;
        if (!$bObject) {
            return $sClass;
        }

        $oObject = new $sClass();
        if (method_exists($oObject, 'setFacade')) {
            $oObject->setFacade($this);
        }
        return $oObject;
    } // function _getEngine

    /**
     * Get delegate class
     * @param string $sClass
     * @return \core\service\tab\delegate
     * @throws \project\exception\service\fatal
     */
    protected function _getDelegate($sClass)
    {
        if (empty($this->aDelegate[$sClass])) {
            $this->aDelegate[$sClass] = $this->_getEngine('delegate\\' . $sClass);
            if (empty($this->aDelegate[$sClass])) {
                throw new fatalException($this, 'Delegate service class "' . $sClass . '" isn\'t found!');
            }
        }
        return $this->aDelegate[$sClass];
    } // function _getDelegate

    /**
     * Subscribe For Service
     * @param string $sServiceName
     * @param string $sEventName
     * @param callback $mCallBack
     * @throws \project\exception\service\fatal
     */
    protected function _subscribeForService($sServiceName, $sEventName, $mCallBack)
    {
        if (!is_callable($mCallBack)) {
            throw new \project\exception\service\fatal($this, 'Incorrect callback-function for subscribing.');
        }
        if (!isset(self::$aListeners[$sServiceName][$sEventName])) {
            self::$aListeners[$sServiceName][$sEventName] = array();
        }
        self::$aListeners[$sServiceName][$sEventName][] = $mCallBack;
    } // function _broadcastMessage
    /**
     * Broadcast Message for listeners
     * @param string $sEventName
     * @param mixed $mData
     */
    protected function _broadcastMessage($sEventName, $mData)
    {
        $sServiceName = get_class_name($this);
        if (isset(self::$aListeners[$sServiceName][$sEventName])) {
            foreach (self::$aListeners[$sServiceName][$sEventName] as $v) {
                call_user_func($v, $mData);
            }
        }
    } // function _broadcastMessage

    // ======== The magic methods ======== \\

    /**
     * Call to unset tab method
     * @param string $sMethod method name
     * @param array $aArgs arguments
     * @return mixed Value return by engine
     */
    public function __call($sMethod, $aArgs)
    {
        foreach ($this->aDelegateRule as $sClass => $aMethods) {
            if (in_array($sMethod, $aMethods)) {
                $aCallBack = array($this->_getDelegate($sClass), $sMethod);
                return is_null($aCallBack) ? null : call_user_func_array($aCallBack, empty($aArgs) ? array() : $aArgs);
            }
        }
        throw new fatalException($this, 'Incorrect call of service - unknown method "' . $sMethod . '"!');
    } // function __call

    // ======== Required Interface methods ======== \\

} // class \core\base\service
?>