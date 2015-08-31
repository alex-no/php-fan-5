<?php
/**
 * Main Load-runner of PHP-FAN files
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
 */
class bootstrap
{
    const MIN_PHP_VERSION = '5.3.0';

    const PHP_FAN_VERSION = '5.1.001';

    /**
     * Flag - is Load-runner already init
     * @var boolean
     */
    private static $bIsInit = false;

    /**
     * Flag - is CLI
     * @var boolean
     */
    private static $bIsCli = false;

    /**
     * Bootstrap configuration
     * @var array
     */
    private static $aConfig = array();

    /**
     * Replacement path elements
     * @var array
     */
    private static $aReplacement = array();

    /**
     * Path to Error-log file
     * @var string
     */
    private static $sLogDir = '{CORE_DIR}/../logs/bootstrap_log/';

    /**
     * Configurator of PHP parameters
     * @var object
     */
    private static $oInitializer;

    /**
     * File Loader - also set autoload filles
     * @var object
     */
    private static $oLoader;

    /**
     * Runner processing request
     * @var object
     */
    private static $oRunner;

    /**
     * @var number Process ID
     */
    private static $nPID = null;

    /**
     * Init bootstrap
     * @param string $sIniPath
     * @return bool
     */
    public static function init($sIniPath = null)
    {
        if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION) < 0) {
            die('PHP-FAN can\'t work with version less than "' . self::MIN_PHP_VERSION . '". Actually your version is "' . PHP_VERSION . '".');
        }
        if (self::$bIsInit) {
            return false;
        }
        self::$bIsInit = true;
        self::$bIsCli  = self::$bIsCli ||  strtolower(php_sapi_name()) == 'cli';

        // Define base const
        define('CORE_DIR', __DIR__);
        if (!defined('PROJECT_DIR')) {
            define('PROJECT_DIR', realpath(CORE_DIR . '/../_project'));
        }
        if (!defined('BASE_DIR')) {
            $sDocRoot = getenv('DOCUMENT_ROOT');
            if (empty($sDocRoot)) {
                $sDocRoot = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : dirname($_SERVER['SCRIPT_FILENAME']);
            }
            define('BASE_DIR', $sDocRoot);
        }
        self::$aReplacement =array(
            '{BASE_DIR}'    => BASE_DIR,
            '{CORE_DIR}'    => CORE_DIR,
            '{PROJECT_DIR}' => PROJECT_DIR,
        );

        // Include additional functions
        require_once CORE_DIR . '/functions.php';
        if (is_readable(PROJECT_DIR . '/functions.php')) {
            require_once PROJECT_DIR . '/functions.php';
        }

        // Load and preparse bootstrap configuration
        self::_setConfig($sIniPath);

        if (!defined('ADMIN_EMAIL')) {
            define('ADMIN_EMAIL', self::$aConfig['bootstrap']['admin_email']);
        }

        // Set initial error handler
        self::_setErrorHandler();

        // Perform the preparation procedures
        self::$oInitializer = self::_defineObj('initializer', '\fan\core\bootstrap\initializer', '{CORE_DIR}/bootstrap/initializer.php');
        self::$oLoader      = self::_defineObj('loader',      '\fan\core\bootstrap\loader',      '{CORE_DIR}/bootstrap/loader.php');
        self::$oInitializer->initAfterLoader();
        self::$oRunner      = self::_defineObj('runner',      '\fan\core\bootstrap\runner',      '{CORE_DIR}/bootstrap/runner.php');

        return true;
    } // function init

    /**
     * Run process to execute
     * @param string $sIniPath
     * @param boolean $bIsEcho allow to output data
     * @return mixed Output data
     */
    public static function run($sIniPath = null, $bIsEcho = true)
    {
        self::init($sIniPath);
        return self::getRunner()->run($bIsEcho);
    } // function run

    /**
     * Run process to execute from CLI
     * @param string $sClassName
     * @param string $sMethodName
     * @return mixed Output data
     */
    public static function runCli($sClassName, $sMethodName = 'init')
    {
        if (php_sapi_name() != 'cli') {
            die('This script can be run in CLI mode only');
        }
        self::$bIsCli = true;
        return self::getRunner()->runCli($sClassName, $sMethodName);
    } // function run

    /**
     * Get initializer
     * @return \fan\core\bootstrap\initializer
     */
    public static function getInitializer()
    {
        if (empty(self::$oInitializer)) {
            self::init();
        }
        return self::$oInitializer;
    } // function getLoader

    /**
     * Get loader
     * @return \fan\core\bootstrap\loader
     */
    public static function getLoader()
    {
        if (empty(self::$oLoader)) {
            self::init();
        }
        return self::$oLoader;
    } // function getLoader

    /**
     * Get loader
     * @return \fan\core\bootstrap\runner
     */
    public static function getRunner()
    {
        if (empty(self::$oRunner)) {
            self::init();
        }
        return self::$oRunner;
    } // function getRunner

    /**
     * Get parameters of Config-Cache
     * @return array
     */
    public static function getConfigCache()
    {
        return isset(self::$aConfig['config_cache']) ? self::$aConfig['config_cache'] : array();
    } // function getConfigCache

    /**
     * Load Class by name (with namespace)
     * Return true if class is loaded
     * @param string $sClass
     * @param boolean $bMakeAlias - allow to make alias in project namespace from core namespace
     * @return boolean
     */
    public static function loadClass($sClass, $bMakeAlias = true)
    {
        return self::getLoader()->loadClass($sClass, $bMakeAlias);
    } // function loadClass

    /**
     * Load File by path to file
     * Return data from file
     * @param string $sFile
     * @param integer $iHandleError Flag of handling error: 0 - do nothing, 1 - set warning, 2 - make Exception
     * @param integer $iWay Way of loading: 0 - include, 1 - include_once, 2 - require, 3 - require_onse
     * @return mixed
     */
    public static function loadFile($sFile, $iHandleError = 0, $iWay = 0)
    {
        return self::getLoader()->loadFile($sFile, $iHandleError, $iWay);
    } // function loadFile

    /**
     * Parse Path
     * Replace placeholders in $sPath to real Data
     * @param string $sPath
     * @return string
     */
    public static function parsePath($sPath)
    {
        return self::getLoader()->parsePath($sPath);
    } // function parsePath

    /**
     * Load File by path to file
     * Return data from file
     * @param string $sLogPath
     * @return string
     */
    public static function getGlobalPath($sKey, $sAltPath = null)
    {
        $aPaths = self::$aConfig['bootstrap']['global_path'];
        $sPath  = empty($aPaths[$sKey]) ? $sAltPath : $aPaths[$sKey];
        return empty($sPath) ? null : self::_fillPlaceholder($sPath);
    } // function parsePath

    /**
     * Error handler
     * @param numeric $nErrNo
     * @param string $sErrMsg
     * @param string $sFileName
     * @param numeric $nLineNum
     */
    public static function handleError($nErrNo, $sErrMsg, $sFileName, $nLineNum, $aErrContext)
    {
        self::logError('Error No ' . $nErrNo . ': ' . $sErrMsg . ' in ' . $sFileName . ' on line ' . $nLineNum . '. Context: ' . var_export($aErrContext, true));
    } // function handleError

    /**
     * Error log
     * @param string $sMessage
     */
    public static function logError($sMessage)
    {
        if (!empty($sMessage)) {
            $sLogPath = self::$sLogDir;
            if (@is_dir($sLogPath) && @is_writable($sLogPath)) {
                $sLogPath .= '/' . date('Y-m-d') . '_000.log';
                if (!file_exists($sLogPath) || @is_writable($sLogPath)) {
                    $sRow = date('H:i:s') . "\t" . addcslashes($sMessage, "\\\t\r\n\0") . "\n";
                    error_log($sRow, 3, $sLogPath);
                    return;
                }
            }
            error_log($sMessage, 0);
        }
    } // function logError

    /**
     * Get process ID
     * @return number
     */
    public static function getPid()
    {
        if (!self::$nPID) {
            self::$nPID = uniqid();
        }
        return self::$nPID;
    } // function getPid

    /**
     * Return true when script is run from CLI
     * @return boolean
     */
    public static function isCli()
    {
        return self::$bIsCli;
    } // function isCli

    /**
     * Set Bootstrap Config
     * @param string $sIniPath
     */
    protected static function _setConfig($sIniPath)
    {
        if (is_null($sIniPath)) {
            $sIniPath = PROJECT_DIR . '/conf/bootstrap.ini';
        }
        self::$aConfig = file_exists($sIniPath) ? parse_ini_file($sIniPath, true) : array();
        foreach (self::$aConfig as &$v1) {
            foreach ($v1 as $k => $v2) {
                if (strpos($k, '.')) {
                    unset($v1[$k]);
                    list($k1, $k2) = explode('.', $k, 2);
                    $v1[$k1][$k2] = $v2;
                }
            }
        }
    } // function _setConfig

    /**
     * Set Simple Error Handler of Bootstrap
     */
    protected static function _setErrorHandler()
    {
        self::$sLogDir = self::getGlobalPath('bootstrap_log', self::$sLogDir);

        if (!ini_get('date.timezone')) {
            ini_set('date.timezone', 'Europe/Helsinki');
        }
        set_error_handler(array(__CLASS__, 'handleError'));
    } // function _setErrorHandler


    /**
     * Replace Placeholder in the path
     * @param string $sPath
     * @return object
     */
    protected static function _fillPlaceholder($sPath)
    {
        foreach (self::$aReplacement as $k => $v) {
            $sPath = str_replace($k, $v, $sPath);
        }
        return $sPath;
    } // function _fillPlaceholder

    /**
     * Define Object
     * @param string $sKey
     * @param string $sClass - Default value
     * @param string $sPath - Default value
     * @return object
     */
    protected static function _defineObj($sKey, $sClass, $sPath)
    {
        if (isset(self::$aConfig[$sKey])) {
            $aConf  = self::$aConfig[$sKey];
            $sClass = empty($aConf['class']) ? $sClass : $aConf['class'];
            $sPath  = empty($aConf['path'])  ? $sPath  : $aConf['path'];
        }
        require_once self::_fillPlaceholder($sPath);
        return new $sClass(isset($aConf['ini']) ? $aConf['ini'] : null);
    } // function _defineObj

} // class \bootstrap
?>