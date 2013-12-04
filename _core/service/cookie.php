<?php namespace core\service;
use project\exception\service\fatal as fatalException;
/**
 * Cookie service
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
 * @version of file: 05.001 (29.09.2011)
 */
class cookie extends \core\base\service\multi
{
    /**
     * @var array Service's Instances
     */
    private static $aInstances = array();

    /**
     * Cookie data
     * @var array
     */
    protected static $aData = null;

    /**
     * @var string Cookie path
     */
    protected $sPath = null;

    /**
     * @var string Cookie domain
     */
    protected $sDomain = null;

    /**
     * @var string Cookie should only be transmitted over a secure HTTPS connection
     */
    protected $bSecure = false;

    /**
     * @var string Cookie will be made accessible for server only (not for JavaScript, etc
     */
    protected $bHttpOnly = false;

    /**
     * Service's constructor
     */
    protected function __construct($sPath, $sDomain, $bSecure)
    {
        parent::__construct(false);

        $this->sPath   = $sPath;
        $this->sDomain = $sDomain;
        $this->bSecure = $bSecure;
        if (is_null(self::$aData)) {
            self::$aData = &$_COOKIE;
        }
    } // function __construct

    /**
     * Get Service's instance of current service by $sConnectionName
     * If $sConnectionName isn't set - Get defaul instance
     * @param string $sPath
     * @param string $sDomain
     * @param boolean $bSecure
     * @return \core\service\cookie
     */
    public static function instance($sPath = null, $sDomain = null, $bSecure = false)
    {
        $oConfig = \project\service\config::instance()->get('cookie');

        if (is_null($sPath)) {
            $sPath = $oConfig->get('DEFAULT_PATH', '/');
        }
        if (is_null($sDomain)) {
            $sDomain = $oConfig->get('DEFAULT_DOMAIN');
        }

        $k0 = empty($sDomain) ? '' : $sDomain;
        $k1 = empty($sPath)   ? '' : $sPath;
        $k2 = empty($sPath)   ? '' : $sPath;
        if (empty(self::$aInstances[$k0][$k1][$k2])) {
            self::$aInstances[$k0][$k1][$k2] = new self($sPath, $sDomain, !empty($bSecure));
        }

        return self::$aInstances[$k0][$k1][$k2];
    } // function instance


    /**
     * Get cookie value
     * @param string $sName
     * @param string $mDefaultVal
     * @return mixed
     */
    public function get($sName, $mDefaultVal = null)
    {
        if (!isset(self::$aData[$sName])) {
            return $mDefaultVal;
        }

        $mV0 = self::$aData[$sName];
        $mV1 = @unserialize($mV0); // ToDo: Use "serialize" OR "JSON" format by Config-parameter
        return $mV1 === false && $mV0 !== serialize(false) ? $mV0 : $mV1;
    } // function get

    /**
     * Get all cookie values
     * @param string $mDefaultVal
     * @return array
     */
    public function getAll($mDefaultVal = null)
    {
        $aResult = array();
        foreach (self::$aData as $k => $v) {
            $aResult[$k] = $this->get($k, $mDefaultVal);
        }
        return $aResult;
    } // function getAll

    /**
     * Set session(!) cookie
     * @param string $sName
     * @param mixed $mValue
     * @return boolean
     */
    public function set($sName, $mValue)
    {
        return $this->setByTime($sName, $mValue, 0);
    } // function set

    /**
     * Set cookie by time in second relative to the current moment
     * @param string $sName
     * @param mixed $mValue
     * @param integer $nTime
     * @return boolean
     */
    public function setByTime($sName, $mValue, $nTime)
    {
        if (!is_scalar($mValue) && !is_null($mValue)) {
            $mValue = serialize($mValue);
        }

        if (setcookie($sName, $mValue, ($nTime ? $nTime + time() : 0), $this->sPath, $this->sDomain, $this->bSecure, $this->bHttpOnly)) {
            if ($nTime < 0) {
                unset(self::$aData[$sName]);
            } elseif (!$this->bSecure || !empty($_SERVER['HTTPS'])) {
                self::$aData[$sName] = $mValue;
            }
            return true;
        }

        return false;
    } // function setByTime

    /**
     * Set cookie by date-string (Format: "Y-m-d H:m:s" or "Y-m-d" or "H:m:s")
     * @param string $sName
     * @param mixed $mValue
     * @param string $sDate
     * @return boolean
     */
    public function setByDate($sName, $mValue, $sDate)
    {
        $sDate = trim($sDate);
        if (empty($sDate)) {
            throw new fatalException($this, 'Date isn\'t set');
        }

        $aMatches = array();
        if (preg_match('/^((\d{4})\-(\d{2})\-(\d{2}))?\s?((\d{2})\:(\d{2})\:(\d{2}))?$/', $sDate, $aMatches)) {
            if (empty($aMatches[1])) {
                $aMatches[2] = date('Y');
                $aMatches[3] = date('m');
                $aMatches[4] = date('d');
            }
            if (empty($aMatches[5])) {
                $aMatches[6] = 23;
                $aMatches[7] = 59;
                $aMatches[8] = 59;
            }

            $nTime = mktime($aMatches[6], $aMatches[7], $aMatches[8] - 1, $aMatches[3], $aMatches[4], $aMatches[2]) - time();
            return $this->setByTime($sName, $mValue, $nTime);
        }
        throw new fatalException($this, 'Date contains incorrect format: "' . $sDate . '"');
    } // function setByTime

    /**
     * Delete cookie
     * @param string $sName
     * @return boolean
     */
    public function delete($sName)
    {
        return $this->setByTime($sName, null, -86400);
    } // function delete

    /**
     * Set HttpOnly flag
     * @param boolean $bHttpOnly
     */
    public function setHttpOnlyFlag($bHttpOnly)
    {
        $this->bHttpOnly = !empty($bHttpOnly);
    } // function setHttpOnlyFlag

} // class \core\service\cookie
?>