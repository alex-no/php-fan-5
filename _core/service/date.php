<?php namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
use fan\project\exception\service\date as dateException;
/**
 * Timer manager service
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
 * @version of file: 05.02.009 (23.09.2015)
 */
// ToDo: redesign this class
class date extends \fan\core\base\service\multi
{
    /**
     * @var boolean Is Global init
     * @var \fan\core\service\config\row
     */
    private static $oGlobalConfig = null;

    /**
     * @var array Service's Instances
     */
    private static $aInstances = array();

    /**
     * @var string Source Date
     */
    protected $oDate;

    /**
     * @var string Date Format
     */
    protected $sFormat;

    /**
     * @var boolean is Used time
     */
    protected $bIsTime = true;

    /**
     * @var string Timezone
     */
    protected $sTimezone;

    /**
     * @var boolean This is Valid Date
     */
    protected $bSave = true;

    /**
     * Service's constructor
     * @param \DateTime $oDate date
     * @param string $sFormat date format
     * @param boolean $bIsTime Timezone
     * @param string $sTimezone Timezone
     * @param boolean $bSave Flag - allows to save this instance
     */
    protected function __construct(\DateTime $oDate, $sFormat, $bIsTime, $sTimezone, $bSave)
    {
        $this->oDate     = $oDate;
        $this->sFormat   = $sFormat;
        $this->bIsTime   = $bIsTime;
        $this->sTimezone = $sTimezone;
        $this->bSave     = $bSave;
        parent::__construct();

    } // function __construct

    // ======== Static methods ======== \\

    /**
     * Get Service's instance of specific date
     * @param string $sDate date
     * @param string $sFormat date format
     * @param string $sTimezone Timezone
     * @param boolean $bSave Save instance there
     * @return \fan\core\service\date
     * @throws \fan\core\exception\service\date
     */
    public static function instance($sDate = null, $sFormat = null, $sTimezone = null, $bSave = true)
    {
        $oConfig = self::_getGlobalConfig();
        $sTimezoneDefault = $oConfig->get('TIMEZONE', 'Europe/Kiev');
        if (empty(self::$aInstances)) {
            date_default_timezone_set($sTimezoneDefault);
        }
        if (is_null($sTimezone)) {
            $sTimezone = $sTimezoneDefault;
        }

        if (is_null($sFormat)) {
            $oDate = null;
            foreach ($oConfig->get('DEFAULT_FORMAT', array()) as $v) {
                list($bIsTime, $oDate) = self::_getDate($oConfig, $sDate, $v, $sTimezone);
                if (!is_null($oDate)) {
                    $sFormat = $v;
                    break;
                }
            }
        } else {
            list($bIsTime, $oDate) = self::_getDate($oConfig, $sDate, $sFormat, $sTimezone);
        }

        if (is_null($oDate)) {
            throw new dateException('Can\'t get date by "' . $sDate . '" format "' . $sFormat . '".');
        }

        $sKey0 = $bIsTime ? 1 : 0;
        $sKey3 = $oDate->format('YmdHisu');
        if (!$bSave || !isset(self::$aInstances[$sKey0][$sTimezone][$sFormat][$sKey3])) {
            return new self($oDate, $sFormat, $bIsTime, $sTimezone, $bSave);
        }
        return self::$aInstances[$sKey0][$sTimezone][$sFormat][$sKey3];
    } // function instance
    /**
     * Get Global Config
     * @return \fan\core\service\config\row
     */
    protected static function _getGlobalConfig()
    {
        if (empty(self::$oGlobalConfig)) {
            self::$oGlobalConfig = service('config')->get('date');
        }
        return self::$oGlobalConfig;
    } // function _getGlobalConfig
    /**
     * Get Date object
     * @param \fan\core\service\config\row $oConfig Config
     * @param string $sDate date
     * @param string $sFormat date format
     * @param string $sTimezone Timezone
     * @return \DateTime
     * @throws \fan\core\exception\service\date
     */
    protected static function _getDate($oConfig, $sDate, $sFormat, $sTimezone)
    {
        $oConfFormat = $oConfig->get(array('FORMAT', $sFormat));
        if (is_null($oConfFormat)) {
            throw new dateException('Requested format "' . $sFormat . '" isn\'t found.');
        }

        $oTimezone = new \DateTimeZone($sTimezone);

        $sFullFormat = $oConfFormat->get('full_pattern');
        $oDate = \DateTime::createFromFormat($sFullFormat, $sDate, $oTimezone);
        if (!is_bool($oDate)) {
            return array(true, $oDate);
        }

        $sShortFormat = $oConfFormat->get('short_pattern') . ' H:i:s';
        $oDate = \DateTime::createFromFormat($sShortFormat, $sDate . ' 00:00:00', $oTimezone);
        return is_bool($oDate) ? array(null, null) : array(false, $oDate);
    } // function _getDate

    // ======== Main Interface methods ======== \\

    /**
     * Conver Date from MySQL-format to local-format
     * @param string $sFormat
     * @return string
     * @throws \fan\core\exception\service\fatal
     */
    public function get($sFormat = null)
    {
        return $this->oDate->format($this->_getPattern($sFormat));
    } // function get

    /**
     * Get string of date by Custom (arbitrary) pattern
     * @param string $sPattern
     * @return string
     */
    public function getCustom($sPattern)
    {
        return $this->oDate->format($sPattern);
    } // function getCustom

    /**
     * Set base format of date
     * @param string $sFormat
     * @return \fan\core\service\date
     * @throws \fan\core\exception\service\fatal
     */
    public function setFormat($sFormat)
    {
        if ($this->bSave) {
            throw new fatalException($this, 'You can change format only for not saved date.');
        }
        if (!isset($this->oConfig['FORMAT'][$sFormat])) {
            throw new fatalException($this, 'Unknown date format "' . $sFormat . '"');
        }
        $this->sFormat = $sFormat;
        return $this;
    } // function setFormat

    /**
     * get Is the time in this date
     * @return boolean
     */
    public function isTime()
    {
        return $this->bIsTime;
    } // function isTime

    /**
     * Get date as array
     * @return array
     */
    public function getDateAsArray()
    {
        $aResult = array();
        $sPattern = $this->_getPattern();
        preg_match_all('/\w/', $sPattern, $aMatches);
        foreach ($aMatches[0] as $v) {
            $aResult[$v] = $this->oDate->format($v);
        }
        return $aResult;
    } // function getDateAsArray

    /**
     * get Unix timestamp by Date as string
     * @return number
     */
    public function getTimeStamp()
    {
        return $this->oDate->getTimestamp();
    } // function getTimeStamp

    /**
     * get Difference (in second) between two dates
     * @param string $sDate2
     * @param boolean $bAbs true - absolute value
     * @return number
     */
    public function getDifference($sDate2, $bAbs = true)
    {
        $oDate2 = service('date', array($sDate2, $this->sFormat, $this->sTimezone, $this->bSave));
        $nRet = $this->getTimeStamp() - $oDate2->getTimeStamp();
        return $bAbs ? abs($nRet) : $nRet;
    } // function getDifference

    /**
     * Shift Date to some second Before or Later
     * Return formated string with new date
     * @param number $nShift (in second)
     * @return string
     */
    public function shiftDate($nShift)
    {
        return date($this->_getPattern(), $this->getTimeStamp() + $nShift);
    } // function shiftDate

    /**
     * Make New object of Date by "modify string"
     * @param string $sModify modify string
     * @return \fan\core\service\date
     */
    public function modify($sModify)
    {
        $oDate = clone $this->oDate;
        $oResult = $oDate->modify($sModify);
        if (is_bool($oResult)) {
            throw new fatalException($this, 'Can\'t modify date by "' . $sModify . '"');
        }

        $sKey0 = $this->bIsTime ? 1 : 0;
        $sKey3 = $oDate->format('YmdHisu');
        if (!$this->bSave || !isset(self::$aInstances[$sKey0][$this->sTimezone][$this->sFormat][$sKey3])) {
            return new self($oResult, $this->sFormat, $this->bIsTime, $this->sTimezone, $this->bSave);
        }
        return self::$aInstances[$sKey0][$this->sTimezone][$this->sFormat][$sKey3];
    } // function modify

    /**
     * Get source data as array
     * @return array
     */
    public function toArray()
    {
        return $this->getDateAsArray();
    } // function toArray

    /**
     * Get Validate of date
     * Deprecated function - for compatibility with old version
     * @return boolean
     */
    public function isValid()
    {
        return true;
    } // function isValid

    // ======== Private/Protected methods ======== \\

    /**
     * Save service's Instance
     * @return \fan\core\service\date
     */
    protected function _saveInstance()
    {
        if ($this->bSave) {
            $sKey0 = $this->bIsTime ? 1 : 0;
            $sKey3 = $this->oDate->format('YmdHisu');
            self::$aInstances[$sKey0][$this->sTimezone][$this->sFormat][$sKey3] = $this;
        }
        return $this;
    } // function _saveInstance

    /**
     * Get pattern fo r format date
     * @param string|null $sFormat
     * @return string
     */
    protected function _getPattern($sFormat = null)
    {
        if (is_null($sFormat)) {
            $sFormat = $this->sFormat;
        } elseif (!isset($this->oConfig['FORMAT'][$sFormat])) {
            throw new fatalException($this, 'Unknown data format "' . $sFormat . '"');
        }
        return $this->oConfig['FORMAT'][$sFormat][$this->bIsTime ? 'full_pattern' : 'short_pattern'];
    } // function _getPattern

    // ======== The magic methods ======== \\
    /**
     * Convert this object to string
     * @return string
     */
    public function __toString()
    {
        return $this->get(null);
    } // function __toString

    // ======== Required Interface methods ======== \\

} // class \fan\core\service\date
?>