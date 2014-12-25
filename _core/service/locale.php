<?php namespace fan\core\service;
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class locale extends \fan\core\base\service\single
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
     * @var \fan\core\service\session
     */
    protected $oSession = null;

    /**
     * If is locale defined
     * @var boolean
     */
    protected $bIsDefined = false;

    /**
     * Constructor of service
     * @param boolean $bAllowIni
     */
    protected function __construct($bAllowIni = true)
    {
        parent::__construct($bAllowIni);
        $this->_setBasicProp();

        $this->_subscribeForService('application', 'setAppName',   array($this, 'onAppChange'));
        $this->_subscribeForService('matcher',     'setNewUri',    array($this, 'onSetNewUri'));
        $this->_subscribeForService('session',     'sesson_start', array($this, 'onSessonStart'));
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
     * @return \fan\core\service\locale
     */
    public function setLanguage($sLanguage)
    {
        if ($this->isEnabled() && isset($this->aAvailableLng[$sLanguage])) {
            $this->_defineLocale();
            return $this->_setCurrentLanguage($sLanguage, true);
        }
        return false;
    } // function setLanguage
    /**
     * Get current Language
     * @return string
     */
    public function getLanguage()
    {
        return $this->_defineLocale()->sCurrentLanguage;
    } // function getLanguage

    /**
     * Get Id of Current Language
     * @return numeric
     */
    public function getLanguageId()
    {
        $oServ = service('entity');
        if (!$oServ->getConfig(array('delegate', 'getLngByName'), false)) {
            return null;
        }
        return $oServ->getLngByName($this->getLanguage())->getId(false);
    } // function getLanguageId
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
     * @return \fan\core\service\locale
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
     * @return \fan\core\service\locale
     */
    public function removeLanguage($sCode)
    {
        return $this;
    } // function removeLanguage

    /**
     * Set current CharacterSet
     * @param string $sCharacterSet
     * @return \fan\core\service\locale
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
     * @return \fan\core\service\locale
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
     * @return \fan\core\service\locale
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
     * @return \fan\core\service\locale
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
    public function isUriParsing()
    {
        return $this->isEnabled() && !empty($this->oConfig['REQUEST_HAS_LNG']);
    } // function isUriParsing

    /**
     * modify Url for substitution language key
     * @param string $sUrn - Sourse Url
     * @param string $sLng - new language code
     * @return string modified URL
     */
    public function modifyUrn($sUrn, $sLng = null)
    {
        if ($this->isEnabled()  && $this->isUriParsing()) {
            $aMatches = $this->checkUriLng($sUrn);
            if (is_null($aMatches)) {
                if (empty($sLng) || !isset($this->aAvailableLng[$sLng])) {
                    $sLng = $this->getLanguage();
                }
                $sUrn = '/' . $sLng . $sUrn;
            }
        }
        return $sUrn;
    } // function modifyUri

    /**
     * Get array of Url for language switcher
     * @param string $sUrl - Sourse Url
     * @return arrae
     */
    public function getSwitcherLinks($sUrl = null, $sLng = null)
    {
        $aRet = array();
        $oTab = service('tab');
        /* @var $oTab \fan\core\service\tab */
        if (empty($sUrl)) {
            $sUrl = $oTab->getCurrentURI(false, true, true, true);
        }

        $sSep = $oTab->getConfig('GET_SEPARATOR', '&amp;');
        foreach ($this->getAvailableLanguages() as $k => $v) {
            $sNewUrn = empty($this->oConfig['REQUEST_HAS_LNG']) ?
                $sUrl . (strpos($sUrl, '?') === false ? '?' : $sSep) . $this->getConfig('LANGUAGE_KEY', 'lng') . '=' . $k :
                '/' . $k . $sUrl;
            $aRet[$k] = array(
                'key'     => $k,
                'urn'     => $sNewUrn,
                'f_name'  => $v,
                's_name'  => $this->oConfig['SHORT_NAME'][$k],
                'current' => $k == $this->getLanguage(),
            );
        }
        return $aRet;
    } // function getSwitcherLinks

    /**
     * Is need to Parse URL for get/set language
     */
    public function onSessonStart()
    {
        $this->_getSession();
        $this->_defineLocale();
    } // function onSessonStart

    /**
     * Parse event matcher: onNewItem
     * @param \fan\core\service\matcher $oMatcher
     */
    public function onSetNewUri(\fan\core\service\matcher $oMatcher)
    {
        if ($this->bIsDefined) {
            $sLanguage = $this->_getLanguageByMatcher($oMatcher);
            $this->_setCurrentLanguage($sLanguage);
        } else {
            $this->_defineLocale();
        }
    } // function onSetNewUri

    /**
     * Check is Url contain URL
     * @param string $sUrl - Sourse Url
     * @return array modified URL
     */
    public function checkUriLng($sUrl)
    {
        $sRegExp = '/^((\~?\/)(' . implode('|', array_keys($this->aAvailableLng)) . '))(?:\/|$)/';
        $aMatches = null;
        if (preg_match($sRegExp, $sUrl, $aMatches)) {
            return $aMatches;
        }
        return null;
    } // function checkUrlLng

    /**
     * Parse event application - apply new config
     */
    public function onAppChange()
    {
        $this->_setBasicProp();
        if (!in_array($this->sCurrentLanguage, $this->aAvailableLng)) {
            $this->_defineLanguage();
        }
    } // function onAppChange

    // ======== Private/Protected methods ======== \\

    /**
     * Get Service Session if it is already define
     * @param type $bForse
     * @return \fan\core\service\session
     */
    protected function _getSession($bForse = true)
    {
        if (empty($this->oSession) && (class_exists('\fan\core\service\session', false) || $bForse)) {
            $this->oSession = \fan\project\service\session::instance('locale', 'service');
        }
        return $this->oSession;
    } // function _getSession

    /**
     * Set Basic Property by config
     * @return \fan\core\service\locale
     */
    protected function _setBasicProp()
    {
        $this->sCharacterSet = $this->getConfig('CHARACTER_SET', 'utf-8');
        $this->sDefaultLng   = $this->getConfig('DEFAULT_LANGUAGE', 'en');

        // Define Available languages and Curren Language
        $oAvailableLng = $this->getConfig('AVAILABLE_LANGUAGE');
        if (!$this->isEnabled() || empty($oAvailableLng)) {
            $this->oConfig['ENABLED'] = false;
            $this->aAvailableLng      = $this->_getDefultLanguages($oAvailableLng);
            $this->sCurrentLanguage   = $this->sDefaultLng;
        } else {
            $this->aAvailableLng = $oAvailableLng->toArray();
        }
        return $this;
    } // function _setBasicProp

    /**
     * Define current Language
     * @return string
     */
    protected function _defineLocale()
    {
        if (!$this->bIsDefined) {
            if ($this->isEnabled()) {
                $this->_defineLanguage(true);
            } else {
                $this->sCurrentLanguage = $this->sDefaultLng;
            }
            // Dedine country, time-zone and currency-code
            $this->_defineExtraData();
            $this->bIsDefined = true;
        }
        return $this;
    } // function _defineLocale

    /**
     * Define current Language
     * @return string
     */
    protected function _defineLanguage($bForse = false)
    {
        // Define by request in the matcher
        if (class_exists('\fan\core\service\matcher', false)) {
            if ($this->_setCurrentLanguage($this->_getLanguageByMatcher(), $bForse)) {
                return 1;
            }
        }

        // Define by GET or POST key
        $oReq    = \fan\project\service\request::instance();
        $sLngKey = $this->getConfig('LANGUAGE_KEY', 'lng');
        if ($this->_setCurrentLanguage($oReq->get($sLngKey, 'GP'), $bForse)) {
            return 2;
        }

        // Define by SESSION
        if ($this->getConfig('USE_SESSION4LNG', false)) {
            $oSes = $this->_getSession(true);
            if (!empty($oSes) && $this->_setCurrentLanguage($oSes->get('current_language'), $bForse)) {
                return 3;
            }
        }

        // Define by COOKIES
        if ($this->_setCurrentLanguage($oReq->get($sLngKey, 'C'), $bForse)) {
            return 4;
        }

        // Define by HTTP_ACCEPT_LANGUAGE
        $sAcceptLng = $oReq->get('HTTP_ACCEPT_LANGUAGE', 'S');
        if (!empty($sAcceptLng)) {
            $aLng = explode(',', $sAcceptLng);
            foreach ($aLng as $v) {
                if (preg_match('/^(\w+)(?:[\-_](\w+))?/', $v, $aMatches)) {
                    if ($this->_setCurrentLanguage(strtolower($aMatches[0]), $bForse)) {
                        if (!empty($aMatches[1])) {
                            $this->setCountry($aMatches[1]);
                        }
                        return 5;
                    }
                }
            }
        }

        $this->sCurrentLanguage = $this->sDefaultLng;
        return 0;
    } // function _defineLanguage

    /**
     * Set current Language
     * @param string $sLanguage
     * @return boolean
     */
    public function _setCurrentLanguage($sLanguage, $bForse = false)
    {
        if (!empty($sLanguage) && $this->isEnabled() && isset($this->aAvailableLng[$sLanguage])) {
            $bIsNew = $this->sCurrentLanguage != $sLanguage;
            if ($bIsNew || $bForse) {
                if ($this->getConfig('USE_SESSION4LNG', false)) {
                    $this->_getSession()->set('current_language', $sLanguage);
                }

                \fan\project\service\cookie::instance('/')->setByTime(
                        $this->getConfig('LANGUAGE_KEY', 'lng'),
                        $sLanguage,
                        $this->getConfig('COOKIE_TIME', 2592000)
                );
            }

            if ($bIsNew) {
                $this->sCurrentLanguage = $sLanguage;
                $this->_broadcastMessage('setNewLanguage', $this);
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
     * @return \fan\core\service\locale
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

    public function _getLanguageByMatcher($oMatcher = null)
    {
        if (empty($oMatcher)) {
            $oMatcher = \fan\project\service\matcher::instance();
        }
        $sLanguage = $oMatcher->getLastItem()->parsed->language;
        return empty($sLanguage) ? null : $sLanguage;
    } // function _defineExtraData

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\


} // class \fan\core\service\locale
?>