<?php namespace core\service;
use project\exception\service\fatal as fatalException;
use \project\exception\error500 as error500;
/**
 * user manager service
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
 * @version of file: 05.002 (17.12.2013)
 * @method mixed getId()
 * @method string getLogin()
 * @method string getNickName()
 * @method string getFullName()
 * @method string getFirstName()
 * @method string getLastName()
 * @method string getTitle()
 * @method string getGender()
 * @method string getEmail()
 * @method string getPhone()
 * @method string getLocale()
 * @method string|array getAddress()
 * @method string getStatus()
 * @method string getJoinDate()
 * @method string getVisitDate()
 * @method array|object getAllData()
 * @method \core\service\user setLogin()     setLogin(string $sLogin)
 * @method \core\service\user setNickName()  setNickName(string $sNickName)
 * @method \core\service\user setFirstName() setFirstName(string $sFirstName)
 * @method \core\service\user setLastName()  setLastName(string $sLastName)
 * @method \core\service\user setTitle()     setTitle(string $sTitle)
 * @method \core\service\user setGender()    setGender(string $sGender)
 * @method \core\service\user setEmail()     setEmail(string $sEmail)
 * @method \core\service\user setPhone()     setPhone(string $sPhone)
 * @method \core\service\user setLocale()    setLocale(string $sLocale)
 * @method \core\service\user setAddress()   setAddress(string|array $aAddress)
 * @method \core\service\user setStatus()    setStatus(string $sStatus)
 * @method \core\service\user setVisitDate() setVisitDate(string $sDate)
 * @method \core\service\user setPassword()  setPassword(string $sPassword)
 * @method string getPassword()
 * @method boolean checkPassword(string $sPassword)
 * @method \core\service\user load()
 * @method \core\service\user save()
 * @method boolean isValid()
 * @method boolean isNew()
 * @method boolean isChanged()
 */
class user extends \core\base\service\multi implements \Serializable
{
    /**
     *
     */
    const SES_NAMESPACE = 'user';

    /**
     * @var array Service's Instances
     */
    private static $aInstances = array();

    /**
     * @var \core\service\session
     */
    private static $oSession = null;

    /**
     * @var \core\service\user[]
     */
    private static $aCurrentUsers = null;

    /**
     * Priority User Space for application
     * @var array
     */
    private static $aPrioritySpace = null;

    /**
     * @var string
     */
    protected $sUserSpace = null;
    /**
     * @var mixed
     */
    protected $mIdentifyer = null;

    /**
     * @var \core\service\user\base
     */
    protected $oUserData = null;

    /**
     * @var array
     */
    protected $aDelegateRule = array(
        'userData' => array(
            // --- Getters method --- \\
            'getId',                                                                     // Main identifier
            'getLogin',    'getNickName',  'getFullName', 'getFirstName', 'getLastName', // Name data
            'getTitle',    'getGender',                                                  // Personal data
            'getEmail',    'getPhone',     'getLocale',   'getAddress',                  // Contact data
            'getStatus',   'getRoles',                                                   // Status-role data
            'getJoinDate', 'getVisitDate',                                               // Rating-visit data
            'getAllData',                                                                // All above and another data

            // --- Setters method --- \\
            'setLogin',     'setNickName', 'setFirstName', 'setLastName', // Name data
            'setTitle',     'setGender',                                  // Personal data
            'setEmail',     'setPhone',    'setLocale',    'setAddress',  // Contact data
            'setStatus',    'addRole',     'removeRole',                  // Status-role data
            'setVisitDate',                                               // Rating-visit data

            // --- Verifying/manipulation method --- \\
            'setPassword', 'getPassword', 'checkPassword',
            'load',        'save',
            'isValid',     'isNew',       'isChanged',
        ),
    );

    /**
     * Constructor of Service of user
     * @param mixed $mIdentifyer
     * @param string $sUserSpace
     */
    protected function __construct($mIdentifyer, $sUserSpace)
    {
        $this->sUserSpace  = $sUserSpace;
        $this->mIdentifyer = $mIdentifyer;

        parent::__construct();

        $oSpaceConfig = $this->_getSpaceConfig();
        $sEngine = $this->_getEngine($oSpaceConfig['ENGINE'], false);
        $this->oUserData = new $sEngine($mIdentifyer);
        $this->oUserData->setFacade($this)->setConfig($oSpaceConfig)->load();

        $this->_subscribeForService('application', 'setAppName', array($this, 'onSetAppName'));
    } // function __construct


    // ======== Static methods ======== \\
    /**
     * Get instance of service of user
     * @param mixed $mIdentifyer Identifyer of user
     * @param string $sUserSpace
     * @return \core\service\user
     */
    public static function instance($mIdentifyer, $sUserSpace = null)
    {
        $sUserSpace = self::_verifySpace($sUserSpace);
        if (is_null(self::$aCurrentUsers)) {
            self::_getCurrentUsers(); // If first call - pull users from session
        }

        if (!isset(self::$aInstances[$sUserSpace][$mIdentifyer])) {
            new self($mIdentifyer, $sUserSpace);
        }
        return self::$aInstances[$sUserSpace][$mIdentifyer];
    } // function instance

    /**
     * Check Logout condition and return current user OR null
     * @return null|\core\service\user
     */
    public static function checkLogout()
    {
        $oUser = self::getCurrent();
        if (!empty($oUser)) {
            $sField = $oUser->getConfig('LOGOUT_FIELD');
            if (!empty($sField)) {
                $sOrder  = $oUser->getConfig('LOGOUT_ORDER', 'GP');
                $bLogout = \core\service\request::instance()->get($sField, $sOrder);
                if (!empty($bLogout)) {
                    for($i = 0; $i < 100 && !empty($oUser); $i++) {
                        $oUser->logout();
                        $oUser = self::getCurrent();
                    }
                    if ($i > 99) {
                        throw new error500('Too many iteration for logout user.');
                    }
                    return null;
                }
            }
        }
        return $oUser;
    } // function checkLogout

    /**
     * Get instance of service of Current user
     * @param string $sReqUserSpace
     * @return \core\service\user
     */
    public static function getCurrent($sReqUserSpace = null)
    {
        $sUserSpace = self::_verifySpace($sReqUserSpace);
        $aCurUsers  = self::_getCurrentUsers();

        if (isset($aCurUsers[$sUserSpace])) {
            $oUser = $aCurUsers[$sUserSpace];
            return $oUser;
        }
        return null;
    } // function getCurrent

    /**
     * Get current "User Space" by Application name
     * @return string
     * @throws error500
     */
    public static function getCurrentSpace()
    {
        $oConfig   = \project\service\config::instance()->get('user');
        $sAppName  = \project\service\application::instance()->getAppName();
        $aCurUsers = self::_getCurrentUsers();

        // If is Priority Space and has current user - use it
        if (isset(self::$aPrioritySpace[$sAppName])) {
            $sPrioritySp = self::$aPrioritySpace[$sAppName];
            if (isset($aCurUsers[$sPrioritySp])) {
                return $sPrioritySp;
            }
        }

        // Use Space with first registered user
        $sFirstSp  = null;
        foreach ($oConfig->get('space', array()) as $k => $v) {
            if (in_array($sAppName, adduceToArray($v->APPLICATIONS))) {
                if (isset($aCurUsers[$k])) {
                    return $k;
                } elseif (empty($sFirstSp)) {
                    $sFirstSp = $k;
                }
            }
        }

        // Use Priority or First Space for current application
        if (!empty($sPrioritySp)) {
            return $sPrioritySp;
        }
        if (!empty($sFirstSp)) {
            return $sFirstSp;
        }

        // Use Default Space if another one is not defined
        $sUserSpace = $oConfig->get('DEFAULT_SPACE');
        if (empty($sUserSpace)) {
            throw new error500('Default user space is not set.');
        }
        return $sUserSpace;
    } // function getCurrentSpace

    // ======== Main Interface methods ======== \\

    /**
     * Set Current User
     * @return boolean
     */
    public function setCurrent()
    {
        if (empty($this->oUserData) || !$this->oUserData->isValid()) {
            return false;
        }
        if (!$this->isCurrent()) {
            self::$aCurrentUsers[$this->sUserSpace] = $this;
            self::_getSession()->set('currents', self::$aCurrentUsers);
            if ($this->_isCorrespondApp()) {
                $this->_broadcastMessage('currentUser', $this);
            }
        }
        return true;
    } // function setCurrent

    /**
     * Check Current User
     * @return boolean
     */
    public function isCurrent()
    {
        $aCurUsers = self::_getCurrentUsers();
        return isset($aCurUsers[$this->sUserSpace]) && $aCurUsers[$this->sUserSpace] === $this;
    } // function isCurrent

    /**
     * Set Priority User Space by current Valid User
     * @return boolean
     */
    public function setPrioritySpace($sAppName = null)
    {
        if ($this->isValid()) {
            $sAppName = $this->_isCorrespondApp($sAppName);
            if (!empty($sAppName)) {
                self::$aPrioritySpace[$sAppName] = $this->sUserSpace;
                self::_getSession()->set('priority', self::$aPrioritySpace);
                return true;
            }
        }
        return false;
    } // function setPrioritySpace

    /**
     * Make logout of Current User
     * @return boolean
     */
    public function logout()
    {
        if ($this->isCurrent()) {
            unset(self::$aCurrentUsers[$this->sUserSpace]);
            self::_getSession()->set('currents', self::$aCurrentUsers);
            if ($this->_isCorrespondApp()) {
                $this->oUserData->logout();
                $oUser = self::getCurrent();
                if (empty($oUser)) {
                    $this->_broadcastMessage('logoutUser', $this);
                } else {
                    $this->_broadcastMessage('currentUser', $oUser);
                }
            }
            return true;
        }
        return false;
    } // function logout

    /**
     * Add User Role
     * @param string $mRole
     * @param number $sExpiredTime - Date/time of expired in mysql-format ("Y-m-d H:i:s")
     * @return \core\service\user
     */
    public function addRole($mRole, $sExpiredTime = null)
    {
        return $this->addRoles(array($mRole => $sExpiredTime));
    } // function addRole

    /**
     * Add User Roles
     *   where array: role_name => expire time
     * @param array $mRole
     * @return \core\service\user
     */
    public function addRoles(array $mRole)
    {
        $bIsChange = false;
        $aCurRoles = $this->oUserData->getRoles();
        $sCurDate  = date('Y-m-d H:i:s');

        foreach ($mRole as $k => $v) {
            $bExpired = !is_null($v) && strcmp($v, $sCurDate) > 0;
            if (array_key_exists($k, $aCurRoles)) {
                if ($bExpired) {
                    $this->oUserData->removeRole($v);
                    $bIsChange = true;
                }
            } elseif ($bExpired) {
                $this->oUserData->addRole($v);
                $bIsChange = true;
            }
        }

        if ($bIsChange) {
            $this->_broadcastMessage('changeRoles', $this);
        }
        return $this;
    } // function addRoles

    /**
     * Remove User Role(s) - just names of role(s) only
     * @param string|array $mRole
     * @return \core\service\user
     */
    public function removeRole($mRole)
    {
        $bIsChange = false;
        $aCurRoles = $this->oUserData->getRoles();
        foreach ($this->_convertToArray($mRole) as $v) {
            if (in_array($v, $aCurRoles)) {
                $this->oUserData->removeRole($v);
                $bIsChange = true;
            }
        }
        if ($bIsChange) {
            $this->_broadcastMessage('changeRoles', $this);
        }
        return $this;
    } // function removeRole

    /**
     * Get User Roles
     * @param boolean $bForce
     * @return array
     */
    public function getRoles($bForce = false)
    {
        return $this->oUserData->getRoles($bForce);
    } // function getRoles

    /**
     * Make Hash for Password
     * @param string $sPassword
     * @return string
     */
    public function makePasswordHash($sPassword)
    {
        return $this->oUserData->makePasswordHash($sPassword);
    } // function makePasswordHash

    /**
     * Get Data
     * @return \core\service\user\base
     */
    public function getData()
    {
        return $this->oUserData;
    } // function getData

    /**
     * On event - Set Application Name
     * @param string $sAppName
     */
    public function onSetAppName($sAppName)
    {
        if ($this->sUserSpace == self::$sCurrentUserSpace && !$this->_isCorrespondApp($sAppName)) {
            self::$sCurrentUserSpace = null;
            self::getCurrent();
        }
    } // function onSetAppName

    // ======== Private/Protected methods ======== \\

    /**
     * Save service's Instance
     * @return \core\base\service
     */
    protected function _saveInstance()
    {
        self::$aInstances[$this->sUserSpace][$this->mIdentifyer] = $this;
        return $this;
    } // function _saveInstance

    /**
     * Get list of current users by all User Spaces
     * If it is not set - restore them from session
     * @return \core\service\user[]
     */
    protected static function _getCurrentUsers()
    {
        if (is_null(self::$aCurrentUsers)) {
            $oSes = self::_getSession();
            self::$aCurrentUsers  = $oSes->get('currents', array());
            self::$aPrioritySpace = $oSes->get('priority', array());
            foreach (self::$aCurrentUsers as $k => $v) {
                if (!isset(self::$aInstances[$k][$v->mIdentifyer])) {
                    self::$aInstances[$k][$v->mIdentifyer] = $v;
                }
            }
        }

        return self::$aCurrentUsers;
    } // function _getCurrentUsers

    /**
     * If User Space is Defined - verify it
     * Else get current User Space
     * @param type $sUserSpace
     * @return type
     * @throws error500
     */
    protected static function _verifySpace($sUserSpace)
    {
        if (empty($sUserSpace)) {
            return self::getCurrentSpace();
        }
        $oConfig = \project\service\config::instance();
        if (!$oConfig->get(array('user', 'space', $sUserSpace))) {
            throw new error500('Incorrect identifyer of user space - "' . $sUserSpace . '".');
        }
        return $sUserSpace;
    } // function _verifySpace

    /**
     * Get Session
     * @return \core\service\session
     */
    protected static function _getSession()
    {
        if (empty(self::$oSession)) {
            self::$oSession = \project\service\session::instance(self::SES_NAMESPACE, 'system');
        }
        return self::$oSession;
    } // function _getSession

    protected function _getDelegate($sClass)
    {
        if ($sClass == 'userData') {
            return $this->oUserData;
        }
        return parent::_getDelegate($sClass);
    } // function _getDelegate

    /**
     * Get Config of Current User Space
     * @return \core\service\config\row
     */
    protected function _getSpaceConfig()
    {
        return $this->oConfig->get(array('space', $this->sUserSpace));
    } // function _getSpaceConfig

    /**
     * Is User Space correspond to Application
     * @return boolean
     */
    protected function _isCorrespondApp($sAppName = null)
    {
        if (empty($sAppName)) {
            $sAppName = \project\service\application::instance()->getAppName();
        }
        $aSpaceConfig = $this->_getSpaceConfig()->toArray();
        return in_array($sAppName, $aSpaceConfig['APPLICATIONS']) ? $sAppName : null;
    } // function _isCorrespondApp

    /**
     * Convert data to Array
     * @param type $mData
     * @return type
     * @throws fatalException
     */
    protected function _convertToArray($mData)
    {
        if (is_array($mData)) {
            return $mData;
        }
        if (is_string($mData)) {
            return array($mData);
        }
        if (is_object($mData) && method_exists($mData, 'toArray')) {
            return $mData->toArray();
        }
        throw new fatalException($this, 'Incorrect data format "' . gettype($mData) . '"');
    } // function _convertToArray

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\

    public function serialize()
    {
        return serialize(array(
            'user_space' => $this->sUserSpace,
            'identifyer' => $this->mIdentifyer,
            'user_data'  => serialize($this->oUserData),
        ));
    }

    public function unserialize($sData)
    {
        $aData = unserialize($sData);

        $this->sUserSpace  = $aData['user_space'];
        $this->mIdentifyer = $aData['identifyer'];
        $this->_saveInstance()->_setConfig()->resetEnabled();

        $this->oUserData = unserialize($aData['user_data']);
        $this->oUserData->setFacade($this)->setConfig($this->_getSpaceConfig());
    }


} // class \core\service\user
?>