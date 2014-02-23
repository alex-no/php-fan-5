<?php namespace core\service;
use project\exception\service\fatal as fatalException;
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
 * @version of file: 05.007 (23.02.2014)
 */
// ToDo: redesign this class
class date extends \core\base\service\multi
{
    /**
     * @var boolean Is Global init
     */
    private static $bIsInit = false;
    /**
     * @var array
     */
    private static $aMapping = array(
        'y' => 'y',
        'Y' => 'y',
        'm' => 'm',
        'n' => 'm',
        'd' => 'd',
        'j' => 'd',

        'a' => 'a',
        'A' => 'a',
        'g' => 'h',
        'h' => 'h',

        'G' => 'H',
        'H' => 'H',
        'i' => 'i',
        's' => 's',
    );

    /**
     * @var array Service's Instances
     */
    private static $aInstances = array();

    /**
     * @var string Sourse Date
     */
    private $sDate;

    /**
     * @var string Date Format
     */
    private $sFormat;

    /**
     * @var string Timezone
     */
    private $sTimezone;

    /**
     * @var array Date as array
     */
    private $aDate = array();
    /**
     * @var number Date as timestamp
     */
    private $nTimeStamp = null;

    /**
     * @var boolean is Used time
     */
    private $bIsTime = true;

    /**
     * @var boolean This is Valid Date
     */
    private $bIsValid = true;

    /**
     * Service's constructor
     * @param string $sDate date
     * @param string $sFormat date format
     */
    protected function __construct($sDate, $sFormat, $sTimezone) {
        parent::__construct();

        if (!self::$bIsInit) {
            self::$bIsInit = $this->_globalInit();
        }

        $this->sDate     = $sDate;
        $this->sTimezone = $sTimezone;

        $aResult = $aTmp = null;
        if ($sFormat) {
            $this->setFormat($sFormat);
            if(preg_match($this->oConfig['FORMAT'][$sFormat]['regexp'], $sDate, $aTmp)) {
                $aResult = $this->_extract($aTmp, $sFormat);
            }
        } else {
            $aMatched = array();
            foreach ($this->oConfig['FORMAT'] as $k => $v) {
                if (preg_match($v['regexp'], $sDate, $aTmp)) {
                    $aMatched[$k] = $this->_extract($aTmp, $k);
                }
            }

            $sFormat = $this->oConfig['DEFAULT_FORMAT'];
            if (count($aMatched) == 1) {
                $aResult       = reset($aMatched);
                $this->sFormat = key($aMatched);
            } elseif (count($aMatched) > 1) {
                if (isset($aMatched[$sFormat])) {
                    $aResult       = $aMatched[$sFormat];
                    $this->sFormat = $sFormat;
                } else {
                    // ToDo: If one of variants has time, but another don't have it - use this variant
                    $aResult       = reset($aMatched);
                    $this->sFormat = key($aMatched);
                }
            }
        }

        if (empty($aResult)) {
            $this->bIsValid = false;
        } else {
            $this->bIsValid   = true;
            $this->bIsTime    = $aResult[0];
            $this->aDate      = $aResult[1];
            $this->nTimeStamp = $this->bIsTime ?
                mktime($aResult[1]['H'], $aResult[1]['i'], $aResult[1]['s'], $aResult[1]['m'], $aResult[1]['d'], $aResult[1]['y']) :
                mktime(0, 0, 0, $aResult[1]['m'], $aResult[1]['d'], $aResult[1]['y']);
        }

        self::$aInstances[$sDate] = $this; //ToDo: Use "FORMAT" there?
    } // function __construct

    // ======== Static methods ======== \\

    /**
     * Get Service's instance of specific date
     * @param string $sDate date
     * @param string $sFormat date format
     * @return \core\service\date
     */
    public static function instance($sDate = null, $sFormat = null, $sTimezone = null)
    {
        if (is_null($sTimezone)) {
            $sTimezone = \project\service\config::instance()->get('date')->get('TIMEZONE', 'Europe/Kiev');
        }
        if (empty(self::$aInstances)) {
            date_default_timezone_set($sTimezone);
        }
        if (is_null($sDate)) {
            $sDate = date('Y-m-d');
        }
        if (!isset(self::$aInstances[$sDate])) {
            new self($sDate, $sFormat, $sTimezone);
        }
        return self::$aInstances[$sDate];
    } // function instance

    /**
     * get days quantity (in the month)
     * @param string $nMonth
     * @param string $nYear
     * @return string
     */
    public static function getDaysQtt($nMonth, $nYear = null) {
        if (!$nYear) {
            $nYear = date('Y');
        }
        return date('d', mktime(0, 0, 0, $nMonth + 1, 0, $nYear));
    } // function getDaysQtt

    // ======== Main Interface methods ======== \\

    /**
     * get Validate of date
     * @return boolean
     */
    public function isValid($bFullValidate = false) {
        return $bFullValidate ? $this->bIsValid && $this->_validate($this->aDate, $this->bIsTime) : $this->bIsValid;
    } // function isValid

    /**
     * Set base format of date
     */
    public function setFormat($sFormat) {
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
    public function isTime() {
        return $this->bIsTime;
    } // function isTime

    /**
     * get date as array
     * @param boolean $bFullValidate validate this date
     * @return array
     */
    public function getDateAsArray($bFullValidate = false) {
        return $this->isValid($bFullValidate) ? $this->aDate : null;
    } // function getDateAsArray

    /**
     * get Unix timestamp by Date as string
     * @param boolean $bFullValidate validate this date
     * @return number
     */
    public function getTimeStamp($bFullValidate = false) {
        return $this->isValid($bFullValidate) ? $this->nTimeStamp : null;
    } // function getTimeStamp

    /**
     * get Difference (in second) between two dates
     * @param string $sDate2
     * @param boolean $bFullValidate validate this date
     * @return number
     */
    public function getDifference($sDate2, $bFullValidate = false, $bNotAbs = false) {
        if (!$this->isValid($bFullValidate)) {
            return null;
        }
        $oDate2 = \project\service\date::instance($sDate2, $this->sFormat);
        $nRet = $this->getTimeStamp() - $oDate2->getTimeStamp($bFullValidate);
        return $oDate2->isValid($bFullValidate) ? ($bNotAbs ? $nRet : abs($nRet)) : null;
    } // function getDifference

    /**
     * Shift Date to some second Before or Later
     * @param number $nShift (in second)
     * @param boolean $bFullValidate validate this date
     * @return string
     */
    public function shiftDate($nShift, $bFullValidate = false) {
        if (!$this->isValid($bFullValidate)) {
            return null;
        }
        return date($this->_getPattern($this->sFormat), $this->getTimeStamp() + $nShift);
    } // function shiftDate

    /**
     * Conver Date from MySQL-format to local-format
     * @param string $sFormat
     * @param boolean $bFullValidate validate this date
     * @return string
     */
    public function get($sFormat = null, $bFullValidate = false) {
        if (!$sFormat) {
            $sFormat = $this->sFormat;
        } elseif (!isset($this->oConfig['FORMAT'][$sFormat])) {
            throw new fatalException($this, 'Unknown data format "' . $sFormat . '"');
        }

        if (!$this->isValid($bFullValidate)) {
            return null;
        }

        return date($this->_getPattern($sFormat), $this->getTimeStamp());
    } // function get

    /**
     * Get string of date by Custom (arbitrary) pattern
     * @param string $sPattern
     * @param boolean $bFullValidate
     * @return string
     */
    public function getCustom($sPattern, $bFullValidate = false) {
        if (!$this->isValid($bFullValidate)) {
            return null;
        }

        return date($sPattern, $this->getTimeStamp());
    } // function get

    /**
     * Get source data as array
     * @return array
     */
    public function toArray() {
        return $this->aDate;
    } // function toArray

    // ======== Private/Protected methods ======== \\

    /**
     * Init config data for set ORDER-list by pattern
     * @return boolean
     * @throws fatalException
     */
    protected function _globalInit()
    {
        foreach ($this->oConfig['FORMAT'] as $sFormat => $oConf) {
            if (!isset($oConf['short_pattern'])) {
                throw new fatalException($this, 'Date format "' . $sFormat . '" doesn\'t have "short_pattern".');
            }
            $this->_parsePattern($sFormat, $oConf, 'short');
            $aTmp = array_flip($oConf['short_order']->toArray());
            if (!isset($aTmp['y']) || !isset($aTmp['m']) || !isset($aTmp['d'])) {
                throw new fatalException($this, 'Date format "' . $sFormat . '" has incomplete "short_pattern".');
            }

            $this->_parsePattern($sFormat, $oConf, 'full');
        }
        return true;
    }

    /**
     * Parse Pattern of config Date-format
     * @param string $sFormat
     * @param \core\service\config\row $oConf
     * @param string $sType
     * @throws fatalException
     */
    protected function _parsePattern($sFormat, \core\service\config\row $oConf, $sType)
    {
        if ($oConf[$sType . '_pattern']) {
            $aMatches = array();
            if (preg_match_all('/([djmnYyaAghGHis])\W*/', $oConf[$sType . '_pattern'], $aMatches)) {
                $aTmp = array();
                foreach ($aMatches[1] as $nPos => $sLetter) {
                    $aTmp[$nPos + 1] = self::$aMapping[$sLetter];
                }
                $oConf[$sType . '_order'] = $aTmp;
            } else {
                throw new fatalException($this, 'Date format "' . $sFormat . '" has incorrect "' . $sType . '_pattern".');
            }
        }
    }

    protected function _between($nVal, $nMin, $nMax)
    {
        return is_numeric($nVal) && $nVal >= $nMin && $nVal >= $nMax;
    }

    protected function _validate($aData, $bWithTime = null)
    {
        if (is_null($bWithTime)) {
            $bWithTime = isset($aData['H']) && isset($aData['m']) &&isset($aData['s']);
        }
        $bResult = $this->_between($aData['Y'], 100, 3000) && $this->_between($aData['m'], 1, 12) && $this->_between($aData['d'], 1, 31);
        $bResult = $bResult && checkdate($aData['m'], $aData['d'], $aData['Y']);
        if ($bWithTime && $bResult) {
            $bResult = $this->_between($aData['H'], 0, 23) && $this->_between($aData['m'], 0, 59) && $this->_between($aData['s'], 0, 59);
        }
        return $bResult;
    }

    /**
     * Extract date value from preg_match-result
     * @param array $aData
     * @param string $sFormat
     * @return array
     */
    protected function _extract($aData, $sFormat)
    {
        $oConfig = $this->oConfig['FORMAT'][$sFormat];
        $bIsTime = !empty($oConfig['full_order']);
        do {
            if ($bIsTime) {
                $aResult = $this->_extractByOrder($aData, $oConfig['full_order']);
                if (!empty($aResult)) {
                    break;
                }
            }
            $bIsTime = false;
            $aResult = $this->_extractByOrder($aData, $oConfig['short_order']);
        } while(false);

        if (empty($aResult)) {
            return null;
        }

        if (isset($aResult['h'])) {
            $aResult['H'] += isset($aResult['a']) && (strtolower($aResult['a']) == 'p' || strtolower($aResult['a']) == 'pm') ? 12 : 0;
        }
        isset($aResult['a']);
        isset($aResult['h']);

        if ($aResult['y'] < 99) {
            $aResult['y'] += $aResult['y'] <= $this->oConfig['THIS_CENTURY_TO'] ? 2000 : 1900;
        }
        return array($bIsTime, $aResult);
    }

    protected function _extractByOrder($aData, $aOrder)
    {
        $aResult = array();
        foreach ($aOrder as $k => $v) {
            if (!isset($aData[$k])) {
                return null;
            }
            $aResult[$v] = $aData[$k];
        }
        return $aResult;
    }

    protected function _getPattern($sFormat)
    {
        return $this->oConfig['FORMAT'][$sFormat][$this->bIsTime ? 'full_pattern' : 'short_pattern'];
    }

    // ======== The magic methods ======== \\

    public function __toString()
    {
        return $this->get(null, false);
    }

    // ======== Required Interface methods ======== \\

} // class \core\service\date
?>