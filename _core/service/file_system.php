<?php namespace fan\core\service;
/**
 * File-system service
 *
 * This file is part PHP-FAN (php-framework of Alexandr Nosov)
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
class file_system extends \fan\core\base\service\multi
{
    /**
     * @var array Service's Instances
     */
    private static $aInstances;

    /**
     * @var string full Path
     */
    protected $sFullPath = '';

    /**
     * @var boolean true/false or NULL if file/dir doesn't exist yet
     */
    protected $bIsFile = null;

    /**
     * @var file/dir handler
     */
    protected $oHandle = null;

    /**
     * @var array inside parameters
     */
    protected $aParam = '';

    /**
     * Service's constructor
     */
    protected function __construct($sFullPath)
    {
        parent::__construct(false);
        $this->sFullPath = $sFullPath;
        if (file_exists($sFullPath)) {
            $this->bIsFile = is_file($sFullPath);
        } else {
            trigger_error('File "' . $sFullPath . '" isn\'t found.', E_NOTICE);
        }
    } // function __construct

    /**
     * Get Service's instance of current service
     * @param string $sSrcPath full Path
     * @return \fan\core\service\file_system
     */
    public static function instance($sSrcPath = null)
    {
        if(!$sSrcPath) {
            return null;
        }
        $sFullPath = \bootstrap::parsePath($sSrcPath);
        if (!isset(self::$aInstances[$sFullPath])) {
            self::$aInstances[$sFullPath] = new self($sFullPath);
        }
        return self::$aInstances[$sFullPath];
    } // function instance

    /**
     * Check is file exitts
     * @return boolean
     */
    public function isFile()
    {
        return $this->bIsFile;
    } // function isFile

    /**
     * Check is file exitts
     * @return boolean
     */
    public function isRreadable()
    {
        return $this->bIsFile && is_readable($this->getFullPath());
    } // function isRreadable

    /**
     * Get full path to file/dir
     * @return string
     */
    public function getFullPath()
    {
        return $this->sFullPath;
    } // function getFullPath

    /**
     * Set parameters of Parts
     * @param numeric $nRowsQtt
     * @param string $sRowSeparator
     * @param string $sColSeparator
     * @return \fan\core\service\file_system
     */
    public function setReadByPart($nRowsQtt = 100, $sRowSeparator = "\n", $sColSeparator = "\t", $bOpenFile = true)
    {
        if ($this->isRreadable()) {
            $this->aParam['rowsQtt']      = $nRowsQtt;
            $this->aParam['rowSeparator'] = $sRowSeparator;
            $this->aParam['colSeparator'] = $sColSeparator;
            $this->aParam['dataPart']     = array();
            if ($bOpenFile) {
                $this->openFile();
            }
        }
        return $this;
    } // function setReadByPart

    /**
     * Close file
     * @return \fan\core\service\file_system
     */
    public function openFile()
    {
        $this->closeFile();
        $this->oHandle = fopen($this->sFullPath, 'r');
        return $this;
    } // function openFile

    /**
     * Close file
     * @return \fan\core\service\file_system
     */
    public function closeFile()
    {
        if ($this->oHandle) {
            fclose($this->oHandle);
            $this->oHandle = null;
        }
        return $this;
    } // function closeFile

    /**
     * Get Data Part As String
     * @return array
     */
    public function getPartAsString()
    {
        $aData = &$this->aParam['dataPart'];
        $nQtt  = $this->aParam['rowsQtt'];

        $nPartSize = $this->getConfig('APPROX_ROW_LENGTH', 64) * $nQtt;
        if ($nPartSize > $this->getConfig('PART_SIZE', 8192)) {
            $nPartSize = $this->getConfig('PART_SIZE', 8192);
        }
        $aRet = array();

        while (count($aRet) < $nQtt) {
            if ($this->oHandle && count($aData) < $nQtt) {
                $sTmp = fread($this->oHandle, $nPartSize);
                $sSrcEnc  = $this->getConfig('SOURCE_ENCODING');
                $sBaseEnc = $this->getConfig('BASE_ENCODING', 'UTF-8');
                if ($sSrcEnc && $sSrcEnc != $sBaseEnc) {
                    $sTmp = iconv($sSrcEnc, $sBaseEnc, $sTmp);
                }
                if (feof($this->oHandle)) {
                    fclose($this->oHandle);
                    $this->oHandle = null;
                }
                $aTmp = explode($this->aParam['rowSeparator'], $sTmp);
                if ($aData) {
                    $aData[count($aData) - 1] .= array_shift($aTmp);
                    $aData = array_merge($aData, $aTmp);
                } else {
                    $aData = $aTmp;
                }
            }

            while (count($aData) > ($this->oHandle ? 1 : 0) && count($aRet) < $nQtt) {
                $aRet[] = array_shift($aData);
            }

            if (!$this->oHandle && !count($aData)) {
                break;
            }
        }
        return $aRet ? $aRet : null;
    } // function getPartAsString

    /**
     * Get Data Part As Array
     * @return array
     */
    public function getPartAsArray()
    {
        $aData = $this->getPartAsString();
        if (is_null($aData)) {
            return null;
        }
        $aRet = array();
        foreach ($aData as $v) {
            $aRet[] = explode($this->aParam['colSeparator'], $v);
        }
        return $aRet;
    } // function getPartAsArray

} // class \fan\core\service\file_system
?>