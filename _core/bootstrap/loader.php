<?php namespace fan\core\bootstrap;
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
 * @version of file: 05.02.007 (31.08.2015)
 * @property-read string $core
 * @property-read string $project
 * @property-read string $app
 * @property-read string $model
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
     * Default Config values
     * @var array
     */
    protected $aDefaultConfig = array(
        'dir_separator' => '/',
        'app_dir'   => '{PROJECT_DIR}/app/',
        'model_dir' => '{PROJECT_DIR}/model/',
        'capp_dir'  => '{APP_DIR}/{APP_NAME}/',
        'main_dir'  => '{CAPP_DIR}/main/',
        'temp_dir'  => '{PROJECT_DIR}/../temp_data/',
        'zend_dir'  => '{PROJECT_DIR}/../libraries/Zend/',
    );

    /**
     * Namespace keys - correspondence to directories
     * @var array
     */
    protected $aNsKeys = array(
        'core'    => null, // Core directory
        'project' => null, // Project directory
        'app'     => null, // All applications directory
        'model'   => null, // Model directory
    );

    /**
     * Extra direrectory keys
     * @var array
     */
    protected $aExtraKeys = array(
        'capp' => null, // Current application directory
        'main' => null, // Current directory for Main-blocks
        'temp' => null, // Directory for temporary files
    );

    /**
     * Path to direrectory of last loaded blocks
     * @var array
     */
    protected $aLastBlock = array();

    /**
     * Flag - show process of autoloading is active
     * @var boolean
     */
    protected $bLoading = false;

    /**
     * Count of arguments for function "class_alias"
     * @var boolean
     */
    protected $iCntAliasArg = 0;

    /**
     * Construct of class
     * @param array $aConfig
     */
    public function __construct($aConfig)
    {
        $this->aConfig = array_merge($this->aDefaultConfig, $aConfig);

        if (!defined('DIR_SEPARATOR')) {
            $sSeparator = isset($aConfig['dir_separator']) ? $aConfig['dir_separator'] : self::DEFAULT_DIR_SEPARATOR;
            define('DIR_SEPARATOR', $sSeparator);
        }

        $this->aNsKeys['core']    = $this->getRealPath(CORE_DIR, false);
        $this->aNsKeys['project'] = $this->getRealPath(PROJECT_DIR, false);

        if (function_exists('class_alias')) {
            $this->iCntAliasArg = array_val($aConfig, 'cnt_alias_arg', 3);
        }

        $this->_setAppDir()           // Set Applications Directory by path from bootstrap-config
             ->_setModelDir()         // Set Model Directory by path from bootstrap-config
             ->_setTemporaryDir()     // Set Temporary Directory by path from bootstrap-config
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
            throw new \fan\project\exception\fatal($sErrorMsg);
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
        if (class_exists($sClass, false) || interface_exists($sClass, false)) {
            return true;
        }

        if (substr($sClass, 0, 4) != 'fan\\') {
            //trigger_error('Load unknown class "' . $sClass. '"', E_USER_WARNING); // It is conflicted with another loaders
            return false;
        }

        if (substr($sClass, 0, 8) == 'fan\app\\') {
            return $this->loadBlockByClass($sClass);
        }

        list($sKey, $sPath, $aParts) = $this->getPathByNS($sClass, false);
        if (empty($sPath)) {
            return false;
        }
        $sPath .= '.php';

        $sPrimePath = $this->aNsKeys[$sKey] . $sPath;
        if (is_readable($sPrimePath)) {
            $this->_requireFile($sPrimePath);
            if (class_exists($sClass, false) || interface_exists($sClass, false)) {
                return true;
            }
            trigger_error('Class "' . $sClass . '" isn\'t found in the file "' . $sPrimePath . '"', E_USER_WARNING);
            return false;
        }

        $sSecondPath = $this->aNsKeys['core'] . $sPath;
        if ($bMakeAlias && $sKey == 'project' && is_readable($sSecondPath)) {
            $sOriginal = 'fan\core\\' . implode('\\', $aParts);
            $this->_requireFile($sSecondPath);
            if (!class_exists($sOriginal, false) && !interface_exists($sOriginal, false)) {
                trigger_error('Class "' . $sOriginal . '" isn\'t found in the file "' . $sSecondPath . '"', E_USER_WARNING);
                return false;
            }
            if ($this->iCntAliasArg > 2) {
                class_alias($sOriginal, $sClass, false);
            } elseif ($this->iCntAliasArg > 0) {
                class_alias($sOriginal, $sClass);
            } else {
                $f = create_function('', 'class ' . $sClass . ' extends ' . $sOriginal . ' {}');
                $f();
            }
            return true;
        }

        return false;
    } // function loadClass

    /**
     * Load block by main request
     * @param sring $sAppName
     * @param array $aMainRequest
     * @return sring - class name
     */
    public function loadBlockByMR($sAppName, $aMainRequest)
    {
        if (empty($aMainRequest)) {
            return null;
        }
        $sPath = str_replace(
                '{CAPP_DIR}',
                $this->aNsKeys['app'] . DIR_SEPARATOR . $sAppName,
                $this->aConfig['main_dir']
        );
        $sPath .= implode(DIR_SEPARATOR, $aMainRequest) . '.php';
        return $this->loadBlockByPath($sPath);
    } // function loadBlockByMR

    /**
     * Load usual block by full class-name
     * @param sring $sClass
     * @return sring - class name
     */
    public function loadBlockByClass($sClass)
    {
        $sClass = trim($sClass, '\\');
        if (substr($sClass, 0, 8) != 'fan\app\\') {
            trigger_error('Block class "' . $sClass . '" has incorrect prefix.', E_USER_WARNING);
            return null;
        }

        $aKey = explode('\\', substr($sClass, 8));
        if (count($aKey) != 3) {
            trigger_error('Block class "' . $sClass . '" has incorrect name.', E_USER_WARNING);
            return null;
        }

        $sFile = $aKey[2] . '.php';
        if (isset($this->aLastBlock[$aKey[0]][$aKey[1]])) {
            $sPath = $this->aLastBlock[$aKey[0]][$aKey[1]] . DIR_SEPARATOR . $sFile;
            if (is_file($sPath)) {
                return $this->loadBlockByPath($sPath);
            }
        }

        $sDir  = $this->aNsKeys['app'] . DIR_SEPARATOR . $aKey[0] . DIR_SEPARATOR . $aKey[1];
        $sPath = $this->_findBlock($sDir, $sFile);
        if (!empty($sPath)) {
            return $this->loadBlockByPath($sPath);
        }
        return null;
    } // function loadBlockByClass

    /**
     * Load Block By Phisical Path (at the disc) or Path with placeholders
     * @param type $sSrcPath
     * @return sring - class name
     */
    public function loadBlockByPath($sSrcPath)
    {
        $sPath = $this->checkPath($sSrcPath);
        if (empty($sPath) || !is_readable($sPath)) {
            trigger_error('Incorrect block path "' . $sSrcPath . '".', E_USER_WARNING);
            return null;
        }

        $sApp = $this->aNsKeys['app'];
        if (substr($sPath, 0, strlen($sApp)) != $sApp) {
            trigger_error('Class file "' . $sPath . '" is out of app directory.', E_USER_WARNING);
            return null;
        }
        $aKey = explode(DIR_SEPARATOR, substr($sPath, strlen($sApp) + 1));
        if (count($aKey) < 3) {
            trigger_error('Block path "' . $sPath . '" isn\'t full.', E_USER_WARNING);
            return null;
        }
        $this->aLastBlock[$aKey[0]][$aKey[1]] = substr($sPath, 0, -strlen(end($aKey)) - 1);

        $this->_requireFile($sPath);
        $sClass = '\fan\app\\' . $aKey[0] . '\\' . $aKey[1] . '\\' . substr(end($aKey), 0, -4);
        if (!class_exists($sClass, false)) {
            trigger_error('Class "' . $sClass . '" isn\'t found in the file "' . $sPath . '"', E_USER_WARNING);
            return null;
        }

        return $sClass;
    } // function loadBlockByPath

    /**
     * Get path to file/dir by namespace
     * @param string $sNS
     * @param boolean $bFullPath
     * @return string|array
     */
    public function getPathByNS($sNS, $bFullPath = true)
    {
        $sNS = trim($sNS, '\\');
        if (substr($sNS, 0, 4) == 'fan\\') {
            $sNS = substr($sNS, 4);
        }

        $aParts = explode('\\', $sNS);
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
        foreach ($this->_getMixedKeys() as $k => $v) {
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
        return $this->getRealPath($sPath, true);
    }

    /**
     * Get real file/directory path
     * @param string $sPath
     * @param boolean $bIsFile
     * @return string | null
     */
    public function getRealPath($sPath, $bIsFile = true)
    {
        return ($bIsFile ? is_file($sPath) : is_dir($sPath)) ?
                str_replace(array('/', '\\'), array(DIR_SEPARATOR, DIR_SEPARATOR), realpath($sPath)) :
                null;
    } // function getRealPath

    /**
     * Define new application parameters
     * @param \fan\core\service\application $oApp
     */
    public function defineNewApp(\fan\core\service\application $oApp)
    {
        $sAppName = $oApp->getAppName();
        if (empty($sAppName)) {
            $this->aExtraKeys['capp'] = null;
            $this->aExtraKeys['main'] = null;
            return;
        }
        $aMask = array(
            '{CORE_DIR}'    => $this->aNsKeys['core'],
            '{PROJECT_DIR}' => $this->aNsKeys['project'],
            '{APP_DIR}'     => $this->aNsKeys['app'],
            '{APP_NAME}'    => $sAppName,
        );
        $this->aExtraKeys['capp'] = $this->getRealPath(
                str_replace(array_keys($aMask), array_values($aMask), $this->aConfig['capp_dir']),
                false
        );

        $aMask['{CAPP_DIR}']   = $this->aExtraKeys['capp'];
        $this->aExtraKeys['main'] =  $this->getRealPath(
                str_replace(array_keys($aMask), array_values($aMask), $this->aConfig['main_dir']),
                false
        );
    } // function defineNewApp

    /**
     * Return true if process Auto-loading is active
     * @return bulean
     */
    public function isLoading()
    {
        return $this->bLoading;
    } // function isLoading

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
        $sZendPath = trim(str_replace('\\', '/', $sZendPath), '/');
        set_include_path(substr($sZendPath, -5) == '/Zend' ? substr($sZendPath, 0, -5) : $sZendPath);
        require_once $sZendPath . '/Loader/Autoloader.php';

        $this->registerAutoload(array('Zend_Loader_Autoloader', 'autoload'), $bPrepend);
    } // function registerZend2

    // ======== Private/Protected methods ======== \\

    /**
     * Set Applications Directory
     * @return \fan\core\bootstrap\loader
     */
    protected function _setAppDir()
    {
        $sPath = $this->parsePath($this->aConfig['app_dir']);
        $this->aNsKeys['app'] = $this->getRealPath($sPath, false);
        return $this;
    } // function _setAppDir

    /**
     * Set Model Directory
     * @return \fan\core\bootstrap\loader
     */
    protected function _setModelDir()
    {
        $sPath = $this->parsePath($this->aConfig['model_dir']);
        $this->aNsKeys['model'] = $this->getRealPath($sPath, false);
        return $this;
    } // function _setModelDir

    /**
     * Set Temporary Directory
     * @return \fan\core\bootstrap\loader
     */
    protected function _setTemporaryDir()
    {
        $sPath = $this->parsePath($this->aConfig['temp_dir']);
        $this->aExtraKeys['temp'] = $this->getRealPath($sPath, false);
        return $this;
    } // function _setTemporaryDir

    /**
     * Set Basic Loader
     * @return \fan\core\bootstrap\loader
     */
    protected function _setBasicLoader()
    {
        $this->registerAutoload(array($this, 'loadClass'));
        return $this;
    } // function _setBasicLoader

    /**
     * Set Additional Loader(s)
     * @return \fan\core\bootstrap\loader
     */
    protected function _setAdditionalLoader()
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

    /**
     * Get Mixed Keys NS and Extra
     * @return array
     */
    protected function _getMixedKeys()
    {
        return array_merge($this->aNsKeys, $this->aExtraKeys);
    } // function _getMixedKeys

    /**
     * Find Block
     * @param string $sDir
     * @param string $sFile
     * @return string
     */
    protected function _findBlock($sDir, $sFile)
    {
        if (is_file($sDir . DIR_SEPARATOR . $sFile)) {
            return $sDir . DIR_SEPARATOR . $sFile;
        }

        $aDir = scandir($sDir);
        foreach ($aDir as $v) {
            $sNewDir = $sDir . DIR_SEPARATOR . $v;
            if ($v != '.' && $v != '..' && is_dir($sNewDir) && is_readable($sNewDir)) {
                $sNewFile = $this->_findBlock($sNewDir, $sFile);
                if (!empty($sNewFile)) {
                    return $sNewFile;
                }
            }
        }

        return null;
    } // function _findBlock

    /**
     * Require Once File and set flag of loading
     * @param string $sPath
     * @return \fan\core\bootstrap\loader
     */
    protected function _requireFile($sPath)
    {
        $this->bLoading = true;
        require_once $sPath;
        $this->bLoading = false;
        return $this;
    } // function _requireFile

    // ======== The magic methods ======== \\

    public function __set($sKey, $mValue)
    {
        return $this->offsetSet($sKey, $mValue);
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
        $aKeys = $this->_getMixedKeys();
        return isset($aKeys[$sKey]);
    }

    public function offsetUnset($sKey)
    {
        trigger_error('Error. It is forbidden to unset the value of property "' . $sKey . '".', E_USER_ERROR);
    }

    public function offsetGet($sKey)
    {
        $aKeys = $this->_getMixedKeys();
        return isset($aKeys[$sKey]) ? $aKeys[$sKey] : null;
    }

} // class \fan\core\bootstrap\loader
?>