<?php namespace core\bootstrap;
/**
 * Description of loader
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
 * @property-read string $core
 * @property-read string $project
 * @property-read string $app
 * @property-read string $capp
 * @property-read string $main
 * @property-read string $temp
 */
class loader implements \ArrayAccess
{
    /**
     * Default directory separator
     */
    const DEFAULT_DIR_SEPARATOR = '/';

    /**
     * Ini-config data
     * @var array
     */
    protected $aConfig;

    /**
     * Namespace keys
     * @var array
     */
    protected $aNsKeys;

    /**
     * Construct of class
     * @param array $aConfig
     */
    public function __construct($aConfig)
    {
        $this->aConfig = $aConfig;
        if (!defined('DIR_SEPARATOR')) {
            $sSeparator = isset($aConfig['dir_separator']) ? $aConfig['dir_separator'] : self::DEFAULT_DIR_SEPARATOR;
            define('DIR_SEPARATOR', $sSeparator);
        }
        $this->aNsKeys = array(
            'core'    => $this->getRealPath(CORE_DIR,    false), // Core directory
            'project' => $this->getRealPath(PROJECT_DIR, false), // Project directory
            'app'     => NULL, // All applications directory
            'capp'    => NULL, // Current application directory
            'main'    => NULL, // Current directory for Main-blocks
            'temp'    => NULL, // Directory for temporary files
        );

        $this->_setTemporaryDir()     // Set Temporary Directory by path from bootstrap-config
             ->_setAppDir()           // Set Applications Directory by path from bootstrap-config
             ->_setBasicLoader()      // Set Basic Loader for FAN-classes: $this->loadClass()
             ->_setAdditionalLoader();// Set Additional Loader(s) by bootstrap-config
    }

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\

    /**
     * Load File by path to file
     * Return data from file
     * @param string $sPath
     * @param integer $iHandleError Flag of handling error: 0 - do nothing, 1 - set warning, 2 - make Rxception
     * @param integer $iWay Way of loading: 0 - include, 1 - include_once, 2 - require, 3 - require_onse
     * @return mixed
     */
    public function loadFile($sPath, $iHandleError = 0, $iWay = 0)
    {
        $sConvPath = $this->checkPath($sPath);
        if ($sConvPath) {
            if(is_readable($sConvPath)) {
                switch ($iWay) {
                case 0:
                    return include      $sConvPath;
                case 1:
                    return include_once $sConvPath;
                case 2:
                    return require      $sConvPath;
                case 3:
                    return require_once $sConvPath;
                default:
                    $sErrorMsg = 'Set incorrect way "' . $iWay . '" for load file';
                }
            } else {
                $sErrorMsg = 'File "' . $sPath . '" isn\'t readable';
            }
        } else {
            $sErrorMsg = 'File "' . $sPath . '" doesn\'t exists';
        }
        if ($iHandleError == 1) {
            trigger_error($sErrorMsg, E_USER_WARNING);
        } elseif ($iHandleError > 1) {
            throw new \project\exception\fatal($sErrorMsg);
        }
        return null;
    } // function loadFile

    /**
     *
     * @param mixed $mFunction
     * @param boolean $bThrow
     * @param boolean $bPrepend
     */
    public function registerAutoload($mFunction, $bPrepend = false)
    {
        try {
            spl_autoload_register($mFunction, true, $bPrepend);
        } catch (Exception $e) {
            \bootstrap::logError('Can\'t register autoloader: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    } // function registerAutoload

    /**
     *
     * @param mixed $mFunction
     */
    public function unregisterAutoload($mFunction)
    {
        spl_autoload_unregister($mFunction);
    } // function unregisterAutoload

    /**
     * Load Class by name (with namespace from root)
     * Return true if class is loaded
     * @param string $sClass
     * @param boolean $bMakeAlias - allow to make alias in project namespace from core namespace
     * @return boolean
     */
    public function loadClass($sClass, $bMakeAlias = true)
    {
        //global $aPoints, $nStart; $aPoints[$sClass] = microtime(true) - $nStart;
        $sClass = trim($sClass, '\\');
        if (class_exists($sClass, false)) {
            return true;
        }

        list($sKey, $sPath, $aParts) = $this->getPathByNS($sClass, false);
        if (empty($sPath)) {
            return false;
        }
        $sPath .= '.php';
        if (is_readable($this->aNsKeys[$sKey] . $sPath)) {
            require_once $this->aNsKeys[$sKey] . $sPath;
            return true;
        }
        if ($bMakeAlias && $sKey == 'project' && is_readable($this->aNsKeys['core'] . $sPath)) {
            require_once $this->aNsKeys['core'] . $sPath;
            class_alias('core\\' . implode('\\', $aParts), $sClass);
            return true;
        }

        return false;
    } // function loadClass

    /**
     * Get path to file/dir by namespace
     * @param string $sNS
     * @param boolean $bFullPath
     * @return string|array
     */
    public function getPathByNS($sNS, $bFullPath = true)
    {
        $aParts = explode('\\', trim($sNS, '\\'));
        $sKey   = array_shift($aParts);
        if (!in_array($sKey, array_keys($this->aNsKeys))) {
            return $bFullPath ? null : array($sKey, null, $aParts);
        }
        $sPath = DIR_SEPARATOR . implode(DIR_SEPARATOR, $aParts);
        return $bFullPath ? $this->aNsKeys[$sKey] . $sPath : array($sKey, $sPath, $aParts);
    } // function getPathByNS

    /**
     * Parse Path
     * Replace placeholders in $sPath to real Data
     * @param string $sPath
     * @return string
     */
    public function parsePath($sPath)
    {
        foreach ($this->aNsKeys as $k => $v) {
            $nCount = 0;
            $k = strtoupper($k);
            $sPath = str_replace(array('{' . $k . '}', '{' . $k . '_DIR}'), array($v, $v), $sPath, $nCount);
            if ($nCount > 0) {
                break;
            }
        }
        return $sPath;
    }

    /**
     * Parse ang check Path
     * Replace placeholders in $sPath to real Data
     * @param string $sPath
     * @return string
     */
    public function checkPath($sPath)
    {
        $sPath = $this->parsePath($sPath);
        return file_exists($sPath) ? $sPath : null;
    }

    /**
     * Get real file/directory path
     * @param string $sPath
     * @param boolean $bIsFile
     * @return string | null
     */
    public function getRealPath($sPath, $bIsFile = true)
    {
        return ($bIsFile ? is_file($sPath) : is_dir($sPath)) ? str_replace('\\', DIR_SEPARATOR, realpath($sPath)) : NULL;
    } // function getRealPath

    /**
     * Define new application parameters
     * @param \core\service\application $oApp
     */
    public function defineNewApp(\core\service\application $oApp)
    {
        $sAppName = $oApp->getAppName();
        if (empty($sAppName)) {
            $this->aNsKeys['capp'] = NULL;
            $this->aNsKeys['main'] = NULL;
            return;
        }
        $aMask = array(
            '{CORE_DIR}'    => $this->aNsKeys['core'],
            '{PROJECT_DIR}' => $this->aNsKeys['project'],
            '{APP_DIR}'     => $this->aNsKeys['app'],
            '{APP_NAME}'    => $sAppName,
        );
        $this->aNsKeys['capp'] = $this->getRealPath(
                str_replace(array_keys($aMask), array_values($aMask), $this->aConfig['capp_dir']),
                false
        );

        $aMask['{CAPP_DIR}']   = $this->aNsKeys['capp'];
        $this->aNsKeys['main'] =  $this->getRealPath(
                str_replace(array_keys($aMask), array_values($aMask), $this->aConfig['main_dir']),
                false
        );
    } // function defineNewApp

    /**
     * Register autolod of classes Zend2
     * @param string $sZendPath
     * @param boolean $bPrepend
     */
    public function registerZend2($sZendPath = null, $bPrepend = false)
    {
        if (is_null($sZendPath)) {
            $sZendPath = $this->parsePath($this->aConfig['zend_dir']);
        }
        set_include_path($sZendPath);
        require_once $sZendPath . '/Zend/Loader/Autoloader.php';

        $this->registerAutoload(array('Zend_Loader_Autoloader', 'autoload'), $bPrepend);
    } // function registerZend2

    // ======== Private/Protected methods ======== \\

    /**
     * Set Applications Directory
     * @return \core\bootstrap\loader
     */
    public function _setAppDir()
    {
        $sPath = $this->parsePath($this->aConfig['app_dir']);
        $this->aNsKeys['app'] = $this->getRealPath($sPath, false);
        return $this;
    } // function _setAppDir

    /**
     * Set Temporary Directory
     * @return \core\bootstrap\loader
     */
    public function _setTemporaryDir()
    {
        $sPath = $this->parsePath($this->aConfig['temp_dir']);
        $this->aNsKeys['temp'] = $this->getRealPath($sPath, false);
        return $this;
    } // function _setTemporaryDir

    /**
     * Set Basic Loader
     * @return \core\bootstrap\loader
     */
    public function _setBasicLoader()
    {
        $this->registerAutoload(array($this, 'loadClass'));
        return $this;
    } // function _setBasicLoader

    /**
     * Set Additional Loader(s)
     * @return \core\bootstrap\loader
     */
    public function _setAdditionalLoader()
    {
        foreach ($this->aConfig as $k => $v) {
            if (substr($k, 0, 11) != 'add_loader.') {
                continue;
            } elseif (method_exists($this, $v)) {
                $this->$v();
            } else {
                trigger_error('Incorrect method name "' . $v . '" for activate autoloader.', E_USER_WARNING);
            }
        }
        return $this;
    } // function _setAdditionalLoader

    // ======== The magic methods ======== \\

    public function __set($sKey, $value)
    {
        return $this->offsetSet($sKey, $value);
    }

    public function __get($sKey)
    {
        return $this->offsetGet($sKey);
    }

    // ======== Required Interface methods ======== \\

    public function offsetSet($sKey, $mValue)
    {
        trigger_error('Error. It is forbidden to set directly the value of property "' . $sKey . '".', E_USER_ERROR);
    }

    public function offsetExists($sKey)
    {
        return isset($this->aNsKeys[$sKey]);
    }

    public function offsetUnset($sKey)
    {
        trigger_error('Error. It is forbidden to unset the value of property "' . $sKey . '".', E_USER_ERROR);
    }

    public function offsetGet($sKey)
    {
        return isset($this->aNsKeys[$sKey]) ? $this->aNsKeys[$sKey] : null;
    }


} // class \core\bootstrap\loader
?>