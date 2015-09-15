<?php namespace fan\core\service;
use fan\core\exception\service\fatal as fatalException;
/**
 * Paiment-maker service
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
 * @author: Alex Nosov (alex@4n.com.ua)
 * @version of file: 05.02.008 (15.09.2015)
 */
class obfuscator extends \fan\core\base\service\multi
{
    /**
     * @var array Service's Instances
     */
    private static $aInstances = array();
    /**
     * List of Engines by TA Types
     * @var array
     */
    private $aFileType = array(
        'css',
        'js',
    );
    /**
     * Current Type of File (css or js)
     * @var numeric
     */
    protected $sType = null;
    /**
     * Path to directory with content files
     * @var string
     */
    protected $sContentDir = null;
    /**
     * Path to directory with META-files
     * @var string
     */
    protected $sMetaDir = null;
    /**
     * Keys for check/make directories
     * @var array
     */
    protected $aDirKeys = array(
        'CONTENT' => 'sContentDir',
        'META'    => 'sMetaDir',
    );

    /**
     * Service's constructor
     * @param string $sType
     * @throws \fan\core\exception\service\fatal
     */
    protected function __construct($sType)
    {
        if (in_array($sType, $this->aFileType)) {
            $this->sType = $sType;
        } else {
            throw new fatalException(0, 'Incorrect file type for obfuscator "' . $sType . '"', 3008);
        }

        parent::__construct(true);

        $this->_defineDir();
    } // function __construct

    // ======== Static methods ======== \\

    /**
     * Get Service's instance for obfuscate JS or CSS
     * @param string $sType css|js
     * @return \fan\core\service\obfuscator
     */
    public static function instance($sType)
    {
        $sType = strtolower($sType);
        if (!isset(self::$aInstances[$sType])) {
            new self($sType);
        }
        return self::$aInstances[$sType];
    } // function instance

    // ======== Main Interface methods ======== \\

    /**
     * Get New List of file (css or js)
     * @param array $aFileList
     * @return array
     */
    public function getNewList($aFileList)
    {
        if (!$this->isEnabled()) {
            return $aFileList;
        }

        $sMethod = '_makeNew' . ucfirst($this->sType) . 'List';
        return $this->$sMethod($aFileList);
    } // function getNewList

    /**
     * Obfuscate string of Content
     * @param string $sText
     * @return string
     */
    public function obfuscate($sText)
    {
        $sEngine = $this->getConfig('ENGINE');
        return empty($sEngine) ? $sText : $this->_getEngine($sEngine)->obfuscate($sText);
    } // function obfuscate

    /**
     * Return content of Obfuscated data
     * @param string $sName File name
     * @return string
     */
    public function getFileData($sName)
    {
        $sContentFile = $this->sContentDir . '/' . $sName;
        return is_file($sContentFile) ? file_get_contents($sContentFile) : 'Error 404! File not found.';
    } // function getFileData

    /**
     * Return content of Obfuscated data
     * @param string $sName File name
     * @param integer $iLength Content length
     * @return string
     */
    public function getHeaders($sName, $iLength = null)
    {
        $sContentFile = $this->sContentDir . '/' . $sName;
        if (is_file($sContentFile)) {
            return array(
                'contentType' => $this->sType == 'css' ? 'text/css' : 'application/javascript',
                'filename'    => $this->sType . '_' . $sName,
                'length'      => empty($iLength) ? filesize($sContentFile) : $iLength,
                'modified'    => filemtime($sContentFile),
                //'cacheLimit'  => 0,
            );
        }
        return array(
            'response'    => 404,
            'contentType' => 'text/plain',
            'filename'    => 'error_404',
            'length'      => empty($iLength) ? null : $iLength,
        );
    } // function getHeaders

    /**
     * Get service's Config
     * @param string $mKey Config key
     * @param mixed $mDefault Default value
     * @return mixed
     */
    public function getConfig($mKey = null, $mDefault = null)
    {
        return parent::getConfig(is_array($mKey) ? $mKey : array($this->sType, $mKey), $mDefault);
    } // function getConfig

    /**
     * Check is service enabled
     * @return boolean
     */
    public function isEnabled()
    {
        return (boolean)$this->getConfig('ENABLED', false);
    } // function isEnabled

    /**
     * Reset flag of enabled
     * @return \fan\core\service\obfuscator
     */
    public function resetEnabled()
    {
        $this->_getConfigurator()->reset('obfuscator', array($this->sType, 'ENABLED'));
        return $this;
    } // function resetEnabled

    // ======== Private/Protected methods ======== \\

    /**
     * Save service's Instance
     * @return \fan\core\base\service
     */
    protected function _saveInstance()
    {
        self::$aInstances[$this->sType] = $this;
        return $this;
    } // function _saveInstance

    /**
     * Check/create Directories for save obfuscate files
     * @return $this
     * @throws fatalException
     */
    protected function _defineDir()
    {
        if (!$this->isEnabled()) {
            return $this;
        }
        foreach ($this->aDirKeys as  $k => $v) {
            $sTmp = $this->getConfig('PATH_' . $k, '{TEMP}/obfuscator/' . $this->sType . '/' . strtolower($k));
            $this->$v = \bootstrap::parsePath($sTmp);
            if (!is_dir($this->$v)) {
                if (!mkdir ($this->$v, 0750, true)) {
                    throw new fatalException('Can\'t create directory "' . $this->$v . '" for obfuscator.');
                }
            }
        }
        return $this;
    } // function _defineDir

    /**
     * Make New List of CSS-files
     * @param array $aFileList
     * @return array
     */
    protected function _makeNewCssList($aFileList)
    {
        $aNewList = array();
        foreach ($aFileList as $sType => $aTmp) {
            foreach ($aTmp as $sMedia => $aList) {
                $aNewList[$sType][$sMedia] = $this->_makeNewList($aList);
            }
        }
        return $aNewList;
    } // function _makeNewCssList
    /**
     * Make New List of JS-files
     * @param array $aFileList
     * @return array
     */
    protected function _makeNewJsList($aFileList)
    {
        $aNewList = array();
        foreach ($aFileList as $sType => $aList) {
            $aNewList[$sType] = $this->_makeNewList($aList);
        }
        return $aNewList;
    } // function _makeNewJsList
    /**
     * Make New List of JS-files
     * @param array $aFileList
     * @return array
     */
    protected function _makeNewList($aList)
    {
        if (empty($aList)) {
            return array();
        }

        $bGlue   = (boolean)$this->getConfig('GLUE', true);
        $sPrefix = ''; // Url prefix,
        $aNames  = array();
        $sKey    = 0;
        foreach ($aList as $k => $v) {
            if (preg_match('/^https?\:\/\/\w+\.\w+/', $v)) {
                if (!empty($aNames[$sKey])) {
                    $sKey++;
                }
                $aNames[$sKey] = $v;
                $sKey++;
            } else{
                $aNames[$sKey][] =  substr($v, 0, 1) == '/' ? $v : $sPrefix . $v;
                if (!$bGlue) {
                    $sKey++;
                }
            }
        }

        $aNewList = array();
        $sHandler = $this->getConfig('HANDLER', '/get_' . $this->sType . '/');
        foreach ($aNames as $v1) {
            if (is_string($v1)) {
                $aNewList[] = $v1;
            } else {
                $sName = md5(implode('-', $v1));
                $aNewList[] = $sHandler . $sName;
                $this->_makeFile($v1, $sName);
            }
        }

        return $aNewList;
    } // function _makeNewList

    protected function _makeFile($aList, $sName)
    {
        $bCheckObsolete = $this->getConfig('CHECK_OBSOLETE', true);
        $sContentFile   = $this->sContentDir . '/' . $sName;
        $sMetaFile      = $this->sMetaDir . '/' . $sName;

        // Check - is content exists and isn't obsolete
        if (is_file($sContentFile)) {
            if (!$bCheckObsolete) {
                return $this;
            }
            if (is_file($sMetaFile)) {
                $bObsolete = false;
                $aData = include $sMetaFile;
                foreach ($aList as $v) {
                    $sSrcPath = BASE_DIR . '/' . $v;
                    if (!is_file($sSrcPath)) {
                        continue;
                    }
                    if (!isset($aData[$v]['time']) || !isset($aData[$v]['size'])) {
                        $bObsolete = true;
                        break;
                    }
                    if ($aData[$v]['time'] != filemtime($sSrcPath) || $aData[$v]['size'] != filesize($sSrcPath)) {
                        $bObsolete = true;
                        break;
                    }
                }
                if (!$bObsolete) {
                    return $this;
                }
            }
        }

        // Make content of files
        $sContent = '';
        $aData    = array();
        foreach ($aList as $v) {
            $sSrcPath = BASE_DIR . '/' . $v;
            if (!is_readable($sSrcPath)) {
                trigger_error('File "' . $v . '" isn\'t readable. Can\'t obfuscate it.', E_USER_WARNING);
                continue;
            }

            $sTmp      = file_get_contents($sSrcPath);
            $sContent .= $this->obfuscate($sTmp);
            if ($bCheckObsolete) {
                $aData[$v] = array(
                    'time' => filemtime($sSrcPath),
                    'size' => filesize($sSrcPath),
                );
            }
        }

        if (file_put_contents($sContentFile, $sContent) === false) {
            trigger_error('Obfuscator error. Can\'t save file "' . $sContentFile . '".', E_USER_WARNING);
        }
        if ($bCheckObsolete) {
            if (file_put_contents($sMetaFile, '<?php
return ' . var_export($aData, true) .';
?>') === false) {
                trigger_error('Obfuscator error. Can\'t save file "' . $sMetaFile . '".', E_USER_WARNING);
            }
        }

        return $this;
    } // function _makeFile

    /**
     * Get delegate class
     * @param string $sClass
     * @return object
     * @throws \fan\core\exception\service\fatal
     */
    protected function _getDelegate($sClass)
    {
        if (empty($this->oEngine)) {
            $this->oEngine = $this->_getEngine($this->sType);
        }
        return $this->oEngine;
    } // function _getDelegate
} // class \fan\core\service\obfuscator
?>