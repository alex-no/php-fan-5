<?php namespace fan\core\bootstrap;
/**
 * Description of initializer
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
 * @version of file: 05.02.001 (10.03.2014)
 */

class initializer
{
    /**
     * Ini-config data
     * @var array
     */
    protected $aConfig = array();

    /**
     * Construct of class
     * @param array $aConfig
     */
    public function __construct($aConfig)
    {
        $this->setConfig($aConfig);

        $this->initBeforeLoader();
    } // function __construct

    /**
     * Set Config-data
     * @param array $aConfig
     * @return initializer
     */
    public function setConfig($aConfig)
    {
        foreach ($aConfig as $k => $v) {
            if (preg_match('/^(?:(main)|(check|app|service)_(.*?))_(\d+)$/', $k, $a)) {
                if (empty($a[1])) {
                    $this->aConfig[$a[2]][$a[3]][$a[4]] = explode(':', $v, 2);
                } else {
                    $this->aConfig['main'][$a[4]]       = explode(':', $v, 2);
                }
            }
        }
        return $this;
    } // function setConfig

    /**
     * Init before loader
     * @return initializer
     */
    public function initBeforeLoader()
    {
        $this->checkRequiredParam();
        $this->checkAdvisedParam();
        $this->setMainParam();
        return $this;
    } // function initBeforeLoader

    /**
     * Init after loader
     * @return initializer
     */
    public function initAfterLoader()
    {
        set_error_handler('handleError');
        $oMatcher = \fan\project\service\matcher::instance();
        if (\bootstrap::isCli()) {
            $aPathParts = pathinfo($GLOBALS['argv'][0]);
            $oMatcher->setCli($aPathParts['basename'], $aPathParts['dirname']);
        } else {
            $oMatcher->setUri($_SERVER['REQUEST_URI'], $_SERVER['HTTP_HOST']);
        }
        return $this;
    } // function initAfterLoader


    /**
     * Check Required Parameters
     */
    public function checkRequiredParam()
    {
        $this->_checkPhpConf('req', true);
    } // function checkRequiredParam

    /**
     * Check Advised Param
     */
    public function checkAdvisedParam()
    {
        $this->_checkPhpConf('adv', false);
    } // function checkAdvisedParam

    /**
     * Set Main Parameters
     */
    public function setMainParam()
    {
        foreach ($this->aConfig['main'] as $v) {
            ini_set(trim($v[0]), trim($v[1]));
        }
    } // function setMainParam

    /**
     * Set Application Parameters
     * @param string $sName Name of Application
     */
    public function setAppParam($sName)
    {
        return $this->_setPhpConf('app', $sName);
    } // function setAppParam

    /**
     * Set Service Parameters
     * @param string $sName Name of Service
     */
    public function setServiceParam($sName)
    {
        return $this->_setPhpConf('service', $sName);
    } // function setServiceParam

    /**
     * Check php-configuration parameter
     * @param string $sType Type of php_conf
     * @param boolean $bSetErr Set error
     */
    protected function _checkPhpConf($sType, $bSetErr = false)
    {
        if (isset($this->aConfig['check'][$sType])) {
            foreach ($this->aConfig['check'][$sType] as $v) {
                $val = ini_get(trim($v[0]));
                if ($val != trim($v[1])) {
                    $sErrMsg = 'Incorrect value of param "' . $v[0] . ' = <b>' . $val . '</b>". Need value = <b>' . $v[1] . '</b><br />';
                    if ($bSetErr) {
                        trigger_error($sErrMsg, E_USER_ERROR);
                    } else {
                        \bootstrap::logError($sErrMsg);
                    }
                }
            }
        }

    } // function _checkPhpConf

    /**
     * Set php-configuration parameter
     * @param string $sType Type of php_conf
     * @param string $sName Name of Type
     * @return array
     */
    protected function _setPhpConf($sType, $sName)
    {
        if (isset($this->aConfig[$sType][$sName])) {
            foreach ($this->aConfig[$sType][$sName] as $v) {
                ini_set(trim($v[0]), trim($v[1]));
            }
            return $this->aConfig[$sType][$sName];
        }
        return null;
    } // function _setPhpConf

} // class \fan\core\bootstrap\initializer
?>