<?php namespace core\service;
/**
 * Service defines several parameters of locale:
 *  - language
 *  - country
 *  - time-zone
 *  - character-set
 *  - currency-code
 *  - currency-sign
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
 */
class locale extends \core\base\service\single
{
    /**
     * List of Available Languages
     * @var array
     */
    protected $aAvailableLng = array();

    /**
     * Default language code
     * @var string
     */
    protected $sDefaultLng = null;
    /**
     * Current language code
     * @var string
     */
    protected $sCurrentLanguage = null;
    /**
     * Current Country key
     * @var string
     */
    protected $sCurrentCountry = null;
    /**
     * Current Time Zone (integer value from -12 to +13)
     * @var integer
     */
    protected $iCurrentTimeZone = null;
    /**
     * Current Character Set
     * @var string
     */
    protected $sCharacterSet = 'utf-8';
    /**
     * Current Currency Code
     * @var string
     */
    protected $sCurrencyCode = null;

    /**
     * Service session
     * @var \core\service\session
     */
    protected $oSession = null;

    /**
     * Constructor of service
     * @param boolean $bAllowIni
     */
    protected function __construct($bAllowIni = true)
    {
        parent::__construct($bAllowIni);
        $this->_getSession($this->getConfig('USE_SESSION4LNG', false));
        $this->sCharacterSet = $this->getConfig('CHARACTER_SET', 'utf-8');
        $this->sDefaultLng   = $this->getConfig('DEFAULT_LANGUAGE', 'en');

        // Define Available languages and Curren Language
        $oAvailableLng = $this->getConfig('AVAILABLE_LANGUAGE');
        if (!$this->isEnabled() || empty($oAvailableLng)) {
            $this->oConfig['ENABLED'] = false;
            $this->aAvailableLng    = $this->_getDefultLanguages($oAvailableLng);
            $this->sCurrentLanguage = $this->sDefaultLng;
        } else {
            $this->aAvailableLng = $oAvailableLng->toArray();
            $this->_defineLanguage();
            $this->_subscribeForService('matcher', 'setNewUri', array($this, 'onSetNewUri'));
        }

        // Dedine country, time-zone and currency-code
        $this->_defineExtraData();

        $this->_subscribeForService('session', 'sesson_start', array($this, 'onSessonStart'));
    } // function __construct

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\

    /**
     * Get Available Languages
     * @return array
     */
    public function getAvailableLanguages()
    {
        return $this->aAvailableLng;
    } // function getAvailableLanguages

    /**
     * Get Short Names of Available Languages
     * @return array
     */
    public function getLanguageShortNames()
    {
        return $this->getConfig('SHORT_NAME', array());
    } // function getLanguageShortNames

    /**
     * Set current Language
     * @param string $sLanguage
     * @return \core\service\locale
     */
    public function setLanguage($sLanguage)
    {
        if ($this->isEnabled() && isset($this->aAvailableLng[$sLanguage])) {
            \project\service\cookie::instance('/')->setByTime($this->getConfig('LANGUAGE_KEY', 'lng'), $sLanguage, $this->getConfig('COOKIE_TIME', 2592000));
            if ($this->sCurrentLanguage != $sLanguage && $this->_setCurrentLanguage($sLanguage)) {
                $this->_broadcastMessage('setNewLanguage', $this);
            }
            return true;
        }
        return false;
    } // function setLanguage
    /**
     * Get current Language
     * @return string
     */
    public function getLanguage()
    {
        return $this->sCurrentLanguage;
    } // function getLanguage
    /**
     * Get Default Language
     * @return string
     */
    public function getDefaultLanguage()
    {
        return $this->sDefaultLng;
    } // function getDefaultLanguage

    /**
     * Add new Language
     * @param string $sCode Language Code
     * @param string $sName Language Name
     * @param string $sShortName Language Short Name
     * @return \core\service\locale
     */
    public function addLanguage($sCode, $sName, $sShortName)
    {
        if (!isset($this->aAvailableLng[$sCode])) {
            $this->aAvailableLng[$sCode] = $sName;
            $this->getConfig('SHORT_NAME')->set($sCode, $sShortName);
        }
        return $this;
    } // function addLanguage
    /**
     * Remove Language
     * @param string $sCode Language Code
     * @return \core\service\locale
     */
    public function removeLanguage($sCode)
    {
        return $this;
    } // function removeLanguage

    /**
     * Set current CharacterSet
     * @param string $sCharacterSet
     * @return \core\service\locale
     */
    public function setCharacterSet($sCharacterSet)
    {
        if ($this->sCharacterSet != $sCharacterSet) {
            $this->sCharacterSet = $sCharacterSet;
            $this->_broadcastMessage('setCharacterSet', $this);
        }
        return $this;
    } // function setCharacterSet
    /**
     * Get current Character Set
     * @return string
     */
    public function getCharacterSet()
    {
        return $this->sCharacterSet;
    } // function getCharacterSet

    /**
     * Set current Time Zone
     * @param string $iTimeZone
     * @return \core\service\locale
     */
    public function setTimeZone($iTimeZone)
    {
        if ($this->iCurrentTimeZone != $iTimeZone) {
            $this->iCurrentTimeZone = $iTimeZone;
            $this->_getSession()->set('current_time_zone', $iTimeZone);
            $this->_broadcastMessage('setTimeZone', $this);
        }
        return $this;
    } // function setTimeZone
    /**
     * Get current Time Zone
     * @return string
     */
    public function getTimeZone()
    {
        return $this->iCurrentTimeZone;
    } // function getTimeZone

    /**
     * Set current Currency Code
     * @param string $sCurrencyCode
     * @return \core\service\locale
     */
    public function setCurrencyCode($sCurrencyCode)
    {
        if ($this->sCurrencyCode != $sCurrencyCode) {
            $this->sCurrencyCode = $sCurrencyCode;
            $this->_getSession()->set('currency_code', $sCurrencyCode);
            $this->_broadcastMessage('setCurrencyCode', $this);
        }
        return $this;
    } // function setCurrencyCode
    /**
     * Get current Character Set
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->sCurrencyCode;
    } // function getCurrencyCode

    /**
     * Set current Country
     * @param string $sCountry
     * @return \core\service\locale
     */
    public function setCountry($sCountry)
    {
        if ($this->sCurrentCountry != $sCountry) {
            $this->sCurrentCountry  = $sCountry;
            $this->_getSession()->set('current_country', $sCountry);
            $this->_broadcastMessage('setCountry', $this);
        }
        return $this;
    } // function setCountry
    /**
     * Get current Country
     * @return string
     */
    public function getCountry()
    {
        return $this->sCurrentCountry;
    } // function getCountry

    /**
     * Is need to Parse URL for get/set language
     */
    public function isUrlParsing()
    {
        return $this->isEnabled() && !empty($this->aConfig['REQUEST_HAS_LNG']);
    } // function isUrlParsing

    /**
     * Is need to Parse URL for get/set language
     */
    public function onSessonStart()
    {
        $this->_getSession();
        $this->_defineExtraData();
    } // function onSessonStart

    /**
     * Parse event matcher:onNewItem
     * @param \core\service\matcher $oMatcher
     */
    public function onSetNewUri(\core\service\matcher $oMatcher)
    {
        $sLanguage = $this->_getLanguageByMatcher($oMatcher);
        $this->_setCurrentLanguage($sLanguage);
    } // function onSetNewUri

    // ======== Private/Protected methods ======== \\

    /**
     * Get Service Session if it is already define
     * @param type $bForse
     * @return \core\service\session
     */
    protected function _getSession($bForse = true)
    {
        if (empty($this->oSession) && (class_exists('\core\service\session', false) || $bForse)) {
            $this->oSession = \project\service\session::instance('locale', 'service');
        }
        return $this->oSession;
    } // function _getSession

    /**
     * Define current Language
     * @return string
     */
    protected function _defineLanguage()
    {
        if (class_exists('\core\service\matcher', false)) {
            $sLanguage = $this->_getLanguageByMatcher(\project\service\matcher::instance());
            if ($this->_setCurrentLanguage($sLanguage)) {
                return;
            }
        }

        $oReq = \project\service\request::instance();
        $sLngKey = $this->getConfig('LANGUAGE_KEY', 'lng');
        // Define by GET or POST key
        if ($this->_setCurrentLanguage($oReq->get($sLngKey, 'GP'))) {
            return;
        }

        // Define by SESSION or COOKIES
        $sLanguage = $oReq->get($sLngKey, 'C');
        $oSes = $this->_getSession(false);
        if (!empty($oSes)) {
            $sLanguage = $oSes->get('current_language', $sLanguage);
        }
        if ($this->_setCurrentLanguage($sLanguage)) {
            return;
        }

        // Define by HTTP_ACCEPT_LANGUAGE
        $sAcceptLng = $oReq->get('HTTP_ACCEPT_LANGUAGE', 'S');
        if (!empty($sAcceptLng)) {
            $aLng = explode(',', $sAcceptLng);
            foreach ($aLng as $v) {
                if (preg_match('/^(\w+)(?:[\-_](\w+))?/', $v, $aMatches)) {
                    $sLanguage = strtolower($aMatches[0]);
                    if ($this->_setCurrentLanguage($sLanguage)) {
                        if (!empty($aMatches[1])) {
                            $this->setCountry($aMatches[1]);
                        }
                        return;
                    }
                }
            }
        }

        $this->sCurrentLanguage = $this->sDefaultLng;
        return $this;
    } // function _defineLanguage

    /**
     * Set current Language
     * @param string $sLanguage
     * @return boolean
     */
    public function _setCurrentLanguage($sLanguage)
    {
        if (!empty($sLanguage) && $this->isEnabled() && isset($this->aAvailableLng[$sLanguage])) {
            if ($this->sCurrentLanguage != $sLanguage) {
                if ($this->getConfig('USE_SESSION4LNG', false)) {
                    $this->_getSession()->set('current_language', $sLanguage);
                }
                $this->sCurrentLanguage = $sLanguage;
            }
            return true;
        }
        return false;
    } // function _setCurrentLanguage

    /**
     * Get reduced array of Defult Languages
     * @param array $aAvailableLng
     * @return array
     */
    public function _getDefultLanguages($aAvailableLng)
    {
        $k = $this->sDefaultLng;
        return isset($aAvailableLng[$k]) ?
                array($k => $aAvailableLng[$k]) :
                array($k => $k);
    } // function _getDefultLanguages

    /**
     * Define Extra Data of locale: CurrentCountry, CurrentTimeZone, CurrencyCode,
     * @return \core\service\locale
     */
    public function _defineExtraData()
    {
        $oSes = $this->_getSession(false);
        if (!empty($oSes)) {
            $aMap = array(
                'current_country'   => 'sCurrentCountry',
                'current_time_zone' => 'iCurrentTimeZone',
                'currency_code'     => 'sCurrencyCode',
            );
            foreach ($aMap as $k => $v) {
                if (empty($this->$v)) {
                    $this->$v = $oSes->get($k);
                }
            }
        }
        return $this;
    } // function _defineExtraData

    public function _getLanguageByMatcher(\core\service\matcher $oMatcher)
    {
        $oParsed = $oMatcher->getLastItem()->parsed;
        return empty($oParsed['language']) ? null : $oParsed['language'];
    } // function _defineExtraData

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\


} // \core\service\locale
?>