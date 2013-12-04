<?php namespace core\service\config;
use project\exception\service\fatal as fatalException;
/**
 * Description of base
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
 */
abstract class base
{
    /**
     * Facade of service
     * @var \core\service\config
     */
    protected $oFacade = null;
    /**
     * File extention
     * @var string
     */
    protected $sFileExtention = '';

    /**
     * Set Facade
     * @param \core\service\config $oFacade
     */
    public function setFacade(\core\service\config $oFacade)
    {
        if (empty($this->oFacade)) {
            $this->oFacade = $oFacade;
        }
        return $this;
    } // function setFacade

    public function getFilePath($sFileName, $bCheckExist = true)
    {
        $sFilePath = $this->sSourceDir . $sFileName . (empty($this->sFileExtention) ? '' : '.' . $this->sFileExtention);
        if (file_exists($sFilePath)) {
            return $sFilePath;
        }
        if ($bCheckExist) {
            throw new fatalException($this->oFacade, 'Configuration file "' . $sFilePath . '" is not found!');
        }
        return null;
    } // function getFilePath

    /**
     * Load Source data as array
     * @param string $sFilePath ini-file path
     * @return array parsed data
     */
    public function loadFile($sFilePath)
    {
        if (file_exists($sFilePath)) {
            return $this->_loadSourceData($sFilePath);
        }
        return array();
    } // function loadFile

    /**
     * Set Source Directory path
     * @param string $sSourceDir
     * @return \core\service\config\base
     */
    public function setDirPath($sSourceDir)
    {
        $this->sSourceDir = rtrim($sSourceDir, '/\\') . '/';
        return $this;
    } // function setDirPath

    /**
     * Load Source Data
     * @param string $sSrcFilePath
     * @return array
     */
    protected function _loadSourceData($sSrcFilePath)
    {
        return array();
    } // function _loadSourceData
} // class \core\service\config\base
?>