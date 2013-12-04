<?php namespace core\service;
/**
 * Session service
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
class session extends \core\base\service\multi
{
    /**
     * @var array Service's Instances
     */
    private static $aInstances = array();
    /**
     * @var stdclass Session engine
     */
    private static $oEngine = null;
    /**
     * Flag: Session is got by cookie
     * @var boolean
     */
    private static $bByCookie = null;
    /**
     * Flag: Session is expired
     * @var boolean
     */
    private static $bIsExpired = false;

    /**
     * Session name-space in the group
     * @var string
     */
    private $sNameSpace = null;

    /**
     * Session group for several Session name-space
     * @var string
     */
    private $sGroup = null;

    /**
     * Service's constructor
     * @param string $sNameSpace
     * @param string $sGroup
     */
    protected function __construct($sNameSpace, $sGroup)
    {
        parent::__construct(empty(self::$aInstances));
        self::$aInstances[$sGroup][$sNameSpace] = $this;

        if ($this->bEnabled) {
            $this->sNameSpace = $sNameSpace;
            $this->sGroup     = $sGroup;

            if (is_null(self::$oEngine)) {

                $this->_prepareParameters();

                // ========= {START session engine} ========= \\
                self::$oEngine = $this->_getEngine($this->oConfig['ENGINE']);

                // Compare Urer's system
                if (!$this->_compareSystem()) {
                    $this->setSessionId(md5($this->getSessionId() . microtime()));
                    $this->_killAll();
                }

                // Check Last visit time
                $this->_checkSessionTimeout();

                // Broadcast Message about start session
                $this->_broadcastMessage('sesson_start', array($sNameSpace, $sGroup));
            }

        } // check enabling status
    } // function __construct

    // ======== Static methods ======== \\
    /**
     * Get instance of Session service
     * @param string $sNameSpace
     * @param string $sGroup
     * @return \core\service\session
     * @throws \project\exception\fatal
     */
    public static function instance($sNameSpace = null, $sGroup = 'app')
    {
        if (is_null($sGroup)) {
            throw new \project\exception\fatal('Unset group name for \core\service\session.');
        }
        if (is_null($sNameSpace)) {
            $oConfig  = \project\service\config::instance()->get('session');
            if ($sGroup == 'app' || $sGroup == 'role') {
                $sNameSpace = \project\service\application::instance()->getAppName();
                $sRepName = $oConfig->get(array('REPLACE_APP', $sNameSpace));
                if ($sRepName) {
                    $sNameSpace = $sRepName;
                }
            } else {
                $sNameSpace = $oConfig->get('DEFAULT_NAME', 'DEFAULT_SESSION');
            }
        }
        if (!isset(self::$aInstances[$sGroup][$sNameSpace])) {
            new self($sNameSpace, $sGroup);
        }
        return self::$aInstances[$sGroup][$sNameSpace];
    } // function instance

    /**
     * Get Service's instance of current service as "Custom-group"
     * @return \core\service\session
     */
    public static function instanceCustom($sSesName = null)
    {
        return \project\service\session::instance($sSesName, 'custom');
    } // function instance

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    /**
     * Get Session parameter
     * @param array|string $mKey The Session key
     * @param mixed $mDefaultValue The default value
     * @param boolean $bRemoveFromSes Remove after read
     * @return mixed Session parameter
     */
    public function get($mKey, $mDefaultValue = null, $bRemoveFromSes = false)
    {
        if (self::$oEngine) {
            $aData = $this->_getEngineData();
            $mResult = array_get_element($aData, $mKey, false);
            if ($bRemoveFromSes) {
                $this->remove($mKey);
            }
            return is_null($mResult) ? $mDefaultValue : $mResult;
        }
        return null;
    } // function get

    /**
     * Get Session parameter by link
     * @param mixed $mKey The Session key
     * @param mixed $mDefaultValue The default value
     * @return mixed Session parameter
     */
    public function &getByLink($mKey, $mDefaultValue = null)
    {
        if (self::$oEngine) {
            $aData   =& $this->_getEngineData();
            $mResult =& array_get_element($aData, $mKey, true);
            if (is_null($mResult)) {
                $mResult = $mDefaultValue;
            }
        } else {
            $mResult = null;
        }
        return $mResult;
    } // function getByLink
    /**
     * Get All data in current Name-space
     * @return array
     */
    public function getAll()
    {
        return $this->_getEngineData();
    } // function getAll

    /**
     * Set Session parameter
     * @access public
     * @param mixed $mKey The Session key
     * @param mixed $mValue The Session value
     * @return boolean True if parameter set
     */
    public function set($mKey, $mValue)
    {
        if (self::$oEngine) {
            if (is_array($mKey)) {
                $aData = &$this->getByLink($mKey, null);
                $aData = $mValue;
                return true;
            } elseif (is_scalar($mKey)) {
                $aData = &$this->_getEngineData();
                $aData[$mKey] = $mValue;
                return true;
            }
            return false;
        }
        return null;
    } // function set

    /**
     * UnSet Session parameter
     * @param string $sKey The Session key
     * @return boolean True if parameter removed
     */
    public function remove($mKey)
    {
        if (self::$oEngine) {
            $aData =& $this->_getEngineData();
            if (is_array($mKey) && count($mKey) == 1) {
                $mKey = reset($mKey);
            }
            if (is_array($mKey)) {
                $sKey = array_pop($mKey);
                $aDest =& array_get_element($aData, $mKey, false);
                if ($aDest) {
                    unset($aDest[$sKey]);
                }
            } else {
                unset($aData[$mKey]);
            }
        }
    } // function remove

    /**
     * UnSet all Session parameters
     * @return boolean True if parameters removed
     */
    public function removeAll()
    {
        if (self::$oEngine) {
            $aData = &$this->_getEngineData();
            $aData = null;
            return true;
        }
        return null;
    } // function removeAll

    /**
     * Get Session Id
     * @return string Session Id
     */
    public function getSessionId()
    {
        if (self::$oEngine) {
            return self::$oEngine->getSessionId();
        }
        return null;
    } // function getSessionId

    /**
     * Session is Get By Cookie
     * @return boolean
     */
    public function isByCookies()
    {
        return self::$bByCookie;
    } // function isByCookies

    /**
     * Set Session Id
     * @param string $sSid
     */
    public function setSessionId($sSid)
    {
        if (self::$oEngine) {
            if ($this->_checkSessionId($sSid)) {
                self::$oEngine->setSessionId($sSid);
                $this->_setCookie($this->getSessionName(), $sSid);
                return true;
            }
        }
        return false;
    } // function setSessionId

    /**
     * Get Session Id
     * @return string
     */
    public function getSessionName()
    {
        if (self::$oEngine) {
            return self::$oEngine->getSessionName();
        }
        return null;
    } // function getSessionName

    /**
     * Get Group
     * @return string
     */
    public function getGroup()
    {
        return $this->sGroup;
    } // function getGroup

    /**
     * Get NameSpace
     * @return string
     */
    public function getNameSpace()
    {
        return $this->sNameSpace;
    } // function getNameSpace

    /**
     * Check session is expired
     * @return boolean
     */
    public function isExpired()
    {
        return self::$bIsExpired;
    } // function isExpired

    /**
     * Reset Expired
     * @return boolean
     */
    public function resetExpired($bClearAll = true)
    {
        if ($bClearAll && self::$bIsExpired) {
            $this->_killAll();
        }
        self::$bIsExpired = false;
        return $this;
    } // function resetExpired

    /**
     * Destroy the session
     */
    public function destroy()
    {
        if (self::$oEngine) {
            self::$oEngine->destroy();

            self::$aInstances = array();
            self::$oEngine    = null;
            self::$bByCookie  = null;
        }
    } // function destroy


    // ======== Private/Protected methods ======== \\
    /**
     * Prepare session parameters
     */
    protected function _prepareParameters()
    {
        $oConfig = $this->oConfig;
        $oSR     = \project\service\request::instance();

        $sSesName   = $oConfig->get('SESSION_NAME', 'SID');
        $sCookieSid = $oSR->get($sSesName, 'C');
        self::$bByCookie = !empty($sCookieSid);

        // Check session ID by GET/POST
        $sSid = $oSR->get(strtoupper($sSesName), 'GP', $oSR->get(strtolower($sSesName), 'GP'));
        if ($this->_checkSessionId($sSid, $sSesName) && $oConfig->get('IS_GET_PRIORITY', false)) {
            self::$bByCookie = self::$bByCookie && $sCookieSid == $sSid;
            $this->_setCookie($sSesName, $sSid);
        } elseif (self::$bByCookie && !$this->_checkSessionId($sCookieSid)) {
            self::$bByCookie = false;
            $this->_setCookie($sSesName, md5($sCookieSid . microtime()));
        }

        // Check conf - Session MAXLIFETIME
        if ($oConfig['MAXLIFETIME']){
            ini_set('session.gc_maxlifetime', $oConfig['MAXLIFETIME']);
        }

        // Set main session parameters
        if ($oConfig['COOKIE_DOMAIN']) {
            session_set_cookie_params (0, '/', $oConfig['COOKIE_DOMAIN']);
        } else {
            session_set_cookie_params (0, '/');
        }
        session_cache_limiter($oConfig['CACHE_LIMITER']);
        session_name($sSesName);

        return $this;
    } // function _prepareParameters

    /**
     * Set session cookie
     * @param string $sVar
     * @param string $sVal
     */
    protected function _setCookie($sVar, $sVal)
    {
        \project\service\cookie::instance('/', $this->oConfig['COOKIE_DOMAIN'])->set($sVar, $sVal);
        return $this;
    } // function _setCookie

    /**
     * Get engine session data
     * @return mixed link to session data
     */
    protected function &_getEngineData()
    {
        return self::$oEngine->getData($this->sGroup, $this->sNameSpace);
    } // function _getEngineData

    /**
     * Check Session Id
     * @param string $sSid
     */
    protected function _checkSessionId(&$sSid, $sSesName = null)
    {
        $sSidSrc = $sSid;
        $sSid = substr(preg_replace('/\W/', '', $sSid), 0, 32);
        if ($sSidSrc == $sSid && strlen($sSid) > 16) {
            return true;
        }
        if ($sSesName) { // ToDo: Make this by service request
            unset($_GET[$sSesName]);
            unset($_POST[$sSesName]);
            unset($_REQUEST[$sSesName]);
        }
        $sSid = null;
        return false;
    } // function _checkSessionId

    /**
     * Check and clear session data by timeout
     * @return boolean True if session checked
     */
    protected function _compareSystem()
    {
        $bResult = true;
        $aCheck = $this->oConfig['CHECK_SYSTEM'];
        if ($aCheck) {
            $aServer = \project\service\request::instance()->getAll('S', array());
            $oSes    = \project\service\session::instance('data', 'session');
            $aParam  = &$oSes->getByLink('param');
            if ($oSes->get('is_fill', false)) {
                foreach ($aCheck as $v) {
                    if (isset($aServer[$v]) && (!isset($aParam[$v]) || $aParam[$v] != @$aServer[$v])) {
                        $bResult = false;
                        break;
                    }
                }
            }

            foreach ($aCheck as $v) {
                if (isset($aServer[$v])) {
                    $aParam[$v] = $aServer[$v];
                }
            }
            $oSes->set('is_fill', true);
        }
        return $bResult;
    } // function _compareSystem

    /**
     * Check and clear session data by timeout
     * @return boolean True if session checked
     */
    protected function _checkSessionTimeout()
    {
        $oConf = $this->oConfig;
        if ($oConf['KILL_BY_TIMEOUT']) {
            $oSes = \project\service\session::instance('time', 'session');

            self::$bIsExpired = &$oSes->getByLink('isKilled');
            $sNowDt = date('Y-m-d H:i:s');
            $oNow = \project\service\date::instance($sNowDt);
            $nDiffer = $oNow->getDifference($oSes->get('reload', $sNowDt));

            if ($nDiffer > $oConf['MAXLIFETIME']) {
                $this->_killAll();
                self::$bIsExpired = true;
            }
            $oSes->set('reload', $sNowDt);
            return !self::$bIsExpired;
        }
        return true;
    } // function _checkSessionTimeout

    /**
     * Kill all session data
     */
    protected function _killAll()
    {
        $aSes = &self::$oEngine->getRoot();
        foreach ($aSes as $sGroup => &$aGr) {
            if ($sGroup != 'ses' && @is_array($aGr)) {
                foreach ($aGr as &$aDt) {
                    $aDt = array();
                }
            }
        }
    } // function _killAll

} // class \core\service\session
?>