<?php
/**
 * Check directories of V-host
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
class check_directories extends base
{
    /**
     * Directory separator
     * @var string
     */
    protected $sSeparator = '';
    /**
     * Path to Base V-host directory
     * @var string
     */
    protected $sBaseDir = null;
    /**
     * Path to CORE directory
     * @var string
     */
    protected $sCoreDir = null;
    /**
     * Path to PROJECT directory
     * @var string
     */
    protected $sProjectDir = null;
    /**
     * Path to TEMP-files directory
     * @var string
     */
    protected $sTempDir = null;
    /**
     * Path to Bootstrap config
     * @var string
     */
    protected $sBootstrapConfig = null;
    /**
     * Path to Service config
     * @var string
     */
    protected $sServiceConfig = null;
    /**
     * Flag is PROJECT dir specially defined
     * @var string
     */
    protected $bIsDefinedProjectDir = false;
    /**
     * Content of INI-file
     * @var array
     */
    protected $aIniContent = array(
        'bootstrap' => '',
        'service'   => '',
    );
    /**
     * Keys for parse path
     * @var array
     */
    protected $aPlaceholderMap = array(
        'BASE_DIR'    => 'sBaseDir',
        'CORE_DIR'    => 'sCoreDir',
        'PROJECT'     => 'sProjectDir',
        'PROJECT_DIR' => 'sProjectDir',
        'TEMP'        => 'sTempDir',
    );

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\
    public function runCheck()
    {
        $this->sSeparator = DIRECTORY_SEPARATOR;
        $bRes  = $this->_checkBaseDirectories();
        $bRes &= $this->_checkLogDirectories();
        $bRes &= $this->_checkCacheDirectories();

        $this->_setConst();
        return $bRes;
    } // function runCheck
    // ======== Private/Protected methods ======== \\
    /**
     * Check Base Directories
     * @return boolean
     */
    protected function _checkBaseDirectories()
    {
        $bResult = false;
        $this->sBaseDir = $this->_adaptPath($_SERVER['DOCUMENT_ROOT']);
        $sIndexDir = $this->_findIndexDir();
        $this->aView['sBaseDir']  = $this->sBaseDir;
        $this->aView['sIndexDir'] = $sIndexDir;

        if (!empty($sIndexDir)) {
            $bIsCoreDir = is_dir($this->sCoreDir);
            if  ($bIsCoreDir) {
                $this->sCoreDir = $this->_adaptPath(realpath($this->sCoreDir));
            }

            $bIsProjectDir = is_dir($this->sProjectDir);
            if  ($bIsProjectDir) {
                $this->sProjectDir = $this->_adaptPath(realpath($this->sProjectDir));
            }

            if (empty($this->sBootstrapConfig)) {
                $this->sBootstrapConfig = $this->sProjectDir . '/conf/bootstrap.ini';
            }
            $this->sBootstrapConfig = file_exists($this->sBootstrapConfig) ?  $this->_adaptPath(realpath($this->sBootstrapConfig)) : null;

            $this->aView['sCoreDir']     = $this->sCoreDir;
            $this->aView['bIsCoreDir']   = $bIsCoreDir;
            $this->aView['bIsCoreUnder'] = $this->_isUnderDir($this->sCoreDir);

            $this->aView['sProjectDir']     = $this->sProjectDir;
            $this->aView['bIsProjectDir']   = $bIsProjectDir;
            $this->aView['bIsProjectUnder'] = $this->_isUnderDir($this->sProjectDir);
            $this->aView['bIsDefinedProjectDir'] = $this->bIsDefinedProjectDir;

            $this->aView['sBootstrapConfig'] = $this->sBootstrapConfig;

            $bResult = !empty($this->sCoreDir) && !empty($this->sProjectDir);
        }

        $this->_parseTemplate('base_directories');

        return $bResult;
    } // function _checkBaseDirectories
    /**
     * Check Log Directories
     * @return boolean
     */
    protected function _checkLogDirectories()
    {
        $this->aIniContent['bootstrap'] = file_get_contents($this->sBootstrapConfig);

        $Matches = null;

        if (preg_match('/^\s*ini\.temp_dir\s*\=\s*\"(.+?)\"\s*$/m', $this->aIniContent['bootstrap'], $aMatches)) {
            $this->sTempDir = $this->_replacePlaceholder($aMatches[1]);
        }

        if (preg_match('/^\s*global_path\.config_source\s*\=\s*\"(.+?)\"\s*$/m', $this->aIniContent['bootstrap'], $aMatches)) {
            $this->sServiceConfig = $this->_replacePlaceholder($aMatches[1]);
        } else {
            $aTmp = pathinfo($this->sBootstrapConfig);
            $this->sServiceConfig = $aTmp['dirname'];
        }
        $this->sServiceConfig .= '/service.ini';

        $this->aIniContent['service'] = file_get_contents($this->sServiceConfig);

        $bResult = true;
        $aLogDir = array(
            'apache'    => array('file' => 'bootstrap', 'key' => 'global_path\\.apache_log'),
            'bootstrap' => array('file' => 'bootstrap', 'key' => 'global_path\\.bootstrap_log'),
            'data'      => array('file' => 'service',   'key' => 'LOG_DIR\\.data'),
            'error'     => array('file' => 'service',   'key' => 'LOG_DIR\\.error'),
            'message'   => array('file' => 'service',   'key' => 'LOG_DIR\\.message'),
        );

        foreach ($aLogDir as &$v) {
            if (preg_match('/^\s*' . $v['key'] . '\s*\=\s*\"(.+?)\"\s*$/m', $this->aIniContent[$v['file']], $Matches)) {
                $v['dir']      = $this->_replacePlaceholder($Matches[1]);
                $v['writable'] = is_dir($v['dir']) && is_writable($v['dir']);
                if (!$v['writable'] && !file_exists($v['dir'])) {
                    $aTmp = pathinfo($v['dir']);
                    if (is_writable($aTmp['dirname']) && mkdir($v['dir'])) {
                        $v['writable'] = true;
                    } else {
                        $v['parent'] = $aTmp['dirname'];
                    }
                }
            }
            $bResult &= $v['writable'];
        }

        $this->aView['aLogDir'] = $aLogDir;
        $this->_parseTemplate('log_directories');
        return $bResult;
    } // function _checkLogDirectories
    /**
     * Check Cache Directories
     * @return boolean
     */
    protected function _checkCacheDirectories()
    {
        $bResult = false;
        if (empty($this->sTempDir)) {
            $iIsTmp = 0;
        } elseif (!is_dir($this->sTempDir)) {
            $iIsTmp = -1;
        } else {
            $this->sTempDir = realpath($this->sTempDir);
            if (!is_writable($this->sTempDir)) {
                $iIsTmp = -2;
            } else {
                $iIsTmp = 1;

                $bResult = true;
                $aCacheDir = array(
                    'config'       => array('required' =>  1, 'file' => 'bootstrap', 'key' => '\[config_cache\][^\[]+BASE_DIR'),
                    'template'     => array('required' =>  1, 'file' => 'service',   'key' => '\[template\][^\[]+CACHE_DIR'),
                    'entity'       => array('required' =>  0, 'file' => 'service',   'key' => '\[entity\][^\[]+CACHE_DIR'),
                    'service-data' => array('required' =>  0, 'file' => 'service',   'key' => '\[cache\.TYPE\.service_data\][^\[]+BASE_DIR'),
                    'file-store'   => array('required' =>  0, 'file' => 'service',   'key' => '\[cache\.TYPE\.file_store\][^\[]+BASE_DIR'),
                    'img-nail'     => array('required' =>  0, 'file' => 'service',   'key' => '\[cache\.TYPE\.img_nail\][^\[]+BASE_DIR'),
                    'common'       => array('required' => -1, 'file' => 'service',   'key' => '\[cache\.TYPE\.common_by_file\][^\[]+BASE_DIR'),
                );

                $aUnset = array();
                foreach ($aCacheDir as $k => &$v) {
                    if (preg_match('/\s*' . $v['key'] . '\s*\=\s*\"(.+?)\"\s*/', $this->aIniContent[$v['file']], $Matches)) {
                        $v['dir']      = $this->_replacePlaceholder($Matches[1]);
                        $v['writable'] = is_dir($v['dir']) && is_writable($v['dir']);
                        if (!$v['writable'] && !file_exists($v['dir'])) {
                            if (mkdir($v['dir'], 0777, true)) {
                                $v['writable'] = true;
                            }
                        }
                    } else {
                        $v['writable'] = false;
                    }

                    if (!$v['writable'] && $v['required'] < 0) {
                        $aUnset[] = $k;
                    } else {
                        $v['img'] = $v['writable'] ? 'correct' : ($v['required'] > 0 ? 'incorrect' : 'need');
                    }
                    $bResult &= ($v['writable'] || $v['required'] < 1);
                }

                foreach ($aUnset as $k) {
                    unset($aCacheDir[$k]);
                }
            }
        }

        $this->aView['iIsTmp']    = $iIsTmp;
        $this->aView['sTempDir']  = $this->sTempDir;
        $this->aView['aCacheDir'] = $aCacheDir;

        $this->_parseTemplate('cache_directories');
        return $bResult;
    } // function _checkCacheDirectories

    /**
     * Find Directory with Index-file
     * @return null|string
     */
    protected function _findIndexDir()
    {
        $iLenRoot = strlen($this->sBaseDir);
        $aScript  = pathinfo($_SERVER['SCRIPT_FILENAME']);
        $sTmp = $this->_adaptPath($aScript['dirname']);

        if ($iLenRoot >= strlen($sTmp)) {
            return null;
        }
        $iPos = strrpos($sTmp, '/', $iLenRoot);
        if (!$iPos) {
            return null;
        }

        $sIndexDir = substr($sTmp, 0, $iPos);

        if ($this->_checkIndexFile($sIndexDir)) {
            return $sIndexDir;
        }
        return $this->_checkIndexFile($this->sBaseDir) ? $this->sBaseDir : null;
    } // function _findIndexDir

    /**
     * Check Index File by Directory
     * @param string $sDir
     * @return bool
     */
    protected function _checkIndexFile($sDir)
    {
        $sPath = $sDir . '/index.php';
        if (is_file($sPath)) {
            $sIndex   = file_get_contents($sPath);

            $aMatches1 = $aMatches2 = $aMatches3 = null;

            $bRes1 = preg_match('/require_once\s+.+?\'(.+\/_core)\/bootstrap\.php\'\;/', $sIndex, $aMatches1);
            $bRes2 = preg_match('/\\\\bootstrap\:\:run\((?:.+\'(.+\/bootstrap\.ini)\')?\)\;/', $sIndex, $aMatches2);

            if ($bRes1 && $bRes2) {
                $this->sCoreDir = $sDir . $aMatches1[1];
                if (preg_match('/define\s*\(\s*\'PROJECT_DIR\'\s*\,.+?\'(.+)\'\)\;/', $sIndex, $aMatches3)) {
                    $this->sProjectDir = $sDir . $aMatches3[1];
                    $this->bIsDefinedProjectDir = true;
                } else {
                    $this->sProjectDir = $this->sCoreDir . '/../_project';
                }
                if (isset($aMatches2[1])) {
                    $sTmp = $sDir . $aMatches2[1];
                    $this->sBootstrapConfig = file_exists($sTmp) ?  realpath($sTmp) : null;
                }
                return true;
            }
        }
        return false;
    } // function _checkIndexFile

    /**
     * Check - this Directory is Under root
     * @param string $sDir
     * @return boolean
     */
    protected function _isUnderDir($sDir)
    {
        $iPos1 = strrpos($this->sBaseDir, '/');
        $iPos2 = strrpos($sDir, '/');
        return substr($this->sBaseDir, 0, $iPos1) == substr($sDir, 0, $iPos2);
    } // function _isUnderDir

    /**
     * Adapt Path - replace Separator to "/"
     * @param string $sPath
     * @return string
     */
    protected function _adaptPath($sPath)
    {
        return $this->sSeparator == '/' ? $sPath : str_replace($this->sSeparator, '/', $sPath);
    } // function _adaptPath

    /**
     * Replace placeholders to real value in the Path
     * @param string $sPath
     * @return string
     */
    protected function _replacePlaceholder($sPath)
    {
        foreach ($this->aPlaceholderMap as $k => $v) {
            $sPath = str_replace('{' . $k . '}', $this->$v, $sPath);
        }
        return $sPath;
    } // function _adaptPath

    /**
     * Set Gloal Constants for all Paths
     * @return check_directories
     */
    protected function _setConst()
    {
        foreach ($this->aPlaceholderMap as $k => $v) {
            $sName = 'FAN_' . $k;
            if (!empty($this->$v) && !defined($sName)) {
                define($sName, $this->$v);
            }
        }
        return $this;
    } // function _adaptPath
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
} // class check_directories
?>