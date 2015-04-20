<?php namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
use \fan\project\exception\error500 as error500;
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
 * @version of file: 05.02.006 (20.04.2015)
 * @method mixed getId()
 * @method string getLogin()
 * @method string getNickName()
 * @method string getFullName()
 * @method string getFirstName()
 * @method string getPatronymic()
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
 * @method \fan\core\service\user setLogin()      setLogin(string $sLogin)
 * @method \fan\core\service\user setNickName()   setNickName(string $sNickName)
 * @method \fan\core\service\user setFirstName()  setFirstName(string $sFirstName)
 * @method \fan\core\service\user setPatronymic() setPatronymic(string $sPatronymic)
 * @method \fan\core\service\user setLastName()   setLastName(string $sLastName)
 * @method \fan\core\service\user setTitle()      setTitle(string $sTitle)
 * @method \fan\core\service\user setGender()     setGender(string $sGender)
 * @method \fan\core\service\user setEmail()      setEmail(string $sEmail)
 * @method \fan\core\service\user setPhone()      setPhone(string $sPhone)
 * @method \fan\core\service\user setLocale()     setLocale(string $sLocale)
 * @method \fan\core\service\user setAddress()    setAddress(string|array $aAddress)
 * @method \fan\core\service\user setStatus()     setStatus(string $sStatus)
 * @method \fan\core\service\user setVisitDate()  setVisitDate(string $sDate)
 * @method \fan\core\service\user setPassword()   setPassword(string $sPassword)
 * @method string getPassword()
 * @method boolean checkPassword(string $sPassword)
 * @method \fan\core\service\user load()
 * @method \fan\core\service\user save()
 * @method boolean isValid()
 * @method boolean isNew()
 * @method boolean isChanged()
 */
class user extends \fan\core\base\service\multi implements \Serializable
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
     * @var \fan\core\service\session
     */
    private static $oSession = null;

    /**
     * @var \fan\core\service\user[]
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
     * @var \fan\core\service\user\base
     */
    protected $oUserData = null;

    /**
     * @var array
     */
    protected $aDelegateRule = array(
        'userData' => array(
            // --- Getters method --- \\
            'getId',                                                     // Main identifier
            'getLogin',    'getNickName',  'getFullName', 'getFirstName', 'getPatronymic', 'getLastName', // Name data
            'getTitle',    'getGender',                                  // Personal data
            'getEmail',    'getPhone',     'getLocale',   'getAddress',  // Contact data
            'getStatus',   /*getRoles*/                                  // Status-role data
            'getJoinDate', 'getVisitDate',                               // Rating-visit data
            'getAllData',                                                // All above and another data

            // --- Setters method --- \\
            'setLogin',     'setNickName', 'setFirstName', 'setPatronymic', 'setLastName', // Name data
            'setTitle',     'setGender',                                  // Personal data
            'setEmail',     'setPhone',    'setLocale',    'setAddress',  // Contact data
            'setStatus',    /*setRoles*/   /*addRole*/     /*removeRole*/ // Status-role data
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
     * @param string $sReqSpace RequestedUser-space
     * @return \fan\core\service\user
     */
    public static function instance($mIdentifyer, $sReqSpace = null)
    {
        $sUserSpace = self::_verifySpace($sReqSpace);
        if (is_null(self::$aCurrentUsers)) {
            self::_getCurrentUsers(); // If first call - pull users from session
        }

        if (!isset(self::$aInstances[$sUserSpace][$mIdentifyer])) {
            new \fan\project\service\user($mIdentifyer, $sUserSpace);
        }
        return self::$aInstances[$sUserSpace][$mIdentifyer];
    } // function instance

    /**
     * Check Logout condition and return current user OR null
     * @return null|\fan\core\service\user
     */
    public static function checkLogout()
    {
        $oUser = self::getCurrent();
        if (!empty($oUser)) {
            $sField = $oUser->getConfig('LOGOUT_FIELD');
            if (!empty($sField)) {
                $sOrder  = $oUser->getConfig('LOGOUT_ORDER', 'GP');
                $bLogout = \fan\project\service\request::instance()->get($sField, $sOrder);
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
     * @param string $sReqSpace Requested User-space
     * @return \fan\core\service\user|null
     */
    public static function getCurrent($sReqSpace = null)
    {
        $sUserSpace = self::_verifySpace($sReqSpace);
        $aCurUsers  = self::_getCurrentUsers();
        return isset($aCurUsers[$sUserSpace]) ? $aCurUsers[$sUserSpace] : null;
    } // function getCurrent

    /**
     * Get current "User Space" by Application name
     * @return string
     * @throws error500
     */
    public static function getCurrentSpace()
    {
        $oConfig   = \fan\project\service\config::instance()->get('user');
        $sAppName  = \fan\project\service\application::instance()->getAppName();
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
     * Get User Roles
     * If don't "Force" - returns just list of "Curren User" else All user's roles
     * @param boolean $bForce
     * @return array
     */
    public function getRoles($bForce = false)
    {
        return $this->oUserData->getRoles($bForce);
    } // function getRoles

    /**
     * Add User Role
     * @param string $mRole
     * @param number $sExpiredTime - Date/time of expired in mysql-format ("Y-m-d H:i:s")
     * @return \fan\core\service\user
     */
    public function addRole($mRole, $sExpiredTime = null)
    {
        $aCurRoles = $this->getRoles(true);
        $aCurRoles[$mRole] = $sExpiredTime;
        return $this->setRoles($aCurRoles);
    } // function addRole

    /**
     * Remove User Role(s) - just names of role(s) only
     * @param string|array $mRole
     * @return \fan\core\service\user
     */
    public function removeRole($mRole)
    {
        $aCurRoles = $this->getRoles(true);
        if (array_key_exists($mRole, $aCurRoles)) {
            unset($aCurRoles[$mRole]);
        }
        return $this->setRoles($aCurRoles);
    } // function removeRole

    /**
     * Set User Roles
     *   where array: role_name => expire time
     * @param array $aNewRoles
     * @return \fan\core\service\user
     */
    public function setRoles(array $aNewRoles)
    {
        $aCurRoles = $this->getRoles(true);
        $sCurDate  = date('Y-m-d H:i:s');

        foreach ($aNewRoles as $k => $v) {
            if (!is_null($v) && strcmp($v, $sCurDate) < 0) {
                unset($aNewRoles[$k]);
            }
        }

        if (array_diff_assoc($aNewRoles, $aCurRoles) || array_diff_assoc($aCurRoles, $aNewRoles)) {
            $this->oUserData->setRoles($aNewRoles);
            if ($this->isCurrent()) {
                $this->_broadcastMessage('changeRoles', $this);
            }
        }
        return $this;
    } // function setRoles

    /**
     * Get User Space
     * @return string
     */
    public function getUserSpace()
    {
        return $this->sUserSpace;
    } // function getUserSpace

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
     * Set Data
     * @param array $aData
     * @return \fan\core\service\user
     */
    public function setData($aData)
    {
        foreach ($aData as $k => $v) {
            $this->set($k, $v);
        }
        return $this;
    } // function setData

    /**
     * Get Data
     * @return array
     */
    public function getData()
    {
        return $this->toArray();
    } // function getData

    /**
     * Conver Data to array
     * @return array
     */
    public function toArray()
    {
        return $this->oUserData->getAllData();
    } // function toArray

    /**
     * Get Data
     * @return \fan\core\service\user\base
     */
    public function getEngine()
    {
        return $this->oUserData;
    } // function getEngine

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

    /**
     * Set any parameter
     * @param string $sKey
     * @param mixed $mValue
     * @return \fan\core\service\user
     */
    public function set($sKey, $mValue)
    {
        list($oObject, $sMethod) = $this->_checkKey('set', $sKey);
        if (!empty($oObject)) {
            $oObject->$sMethod($mValue);
        }
        return $this;
    } // function set
    /**
     * Get any parameter
     * @param string $sKey
     * @return mixed
     */
    public function get($sKey)
    {
        list($oObject, $sMethod) = $this->_checkKey('get', $sKey);
        return empty($oObject) ? null : $oObject->$sMethod();
    } // function get

    // ======== Private/Protected methods ======== \\

    /**
     * Save service's Instance
     * @return \fan\core\service\user
     */
    protected function _saveInstance()
    {
        self::$aInstances[$this->sUserSpace][$this->mIdentifyer] = $this;
        return $this;
    } // function _saveInstance

    /**
     * Get list of current users by all User Spaces
     * If it is not set - restore them from session
     * @return \fan\core\service\user[]
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
        $oConfig = \fan\project\service\config::instance();
        if (!$oConfig->get(array('user', 'space', $sUserSpace))) {
            throw new error500('Incorrect identifyer of user space - "' . $sUserSpace . '".');
        }
        return $sUserSpace;
    } // function _verifySpace

    /**
     * Get Session
     * @return \fan\core\service\session
     */
    protected static function _getSession()
    {
        if (empty(self::$oSession)) {
            self::$oSession = \fan\project\service\session::instance(self::SES_NAMESPACE, 'system');
        }
        return self::$oSession;
    } // function _getSession

    /**
     * Get delegate class
     * @param string $sClass
     * @return object
     */
    protected function _getDelegate($sClass)
    {
        if ($sClass == 'userData') {
            return $this->oUserData;
        }
        return parent::_getDelegate($sClass);
    } // function _getDelegate

    /**
     * Get Config of Current User Space
     * @return \fan\core\service\config\row
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
            $sAppName = \fan\project\service\application::instance()->getAppName();
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

    protected function _checkKey($sType, $sKey)
    {
        $aData = $this->oUserData->getAllData();
        if (!array_key_exists($sKey, $aData)) {
            return array(null, null);
        }

        $aTmp    = array_map('ucfirst', explode('_', $sKey));
        $sMethod = $sType . implode('', $aTmp);

        $oObject = method_exists($this, $sMethod) ? $this : $this->oUserData;
        return array($oObject, $sMethod);
    } // function _checkKey

    // ======== The magic methods ======== \\
    /**
     * Magic method for set property
     * @param string $sKey
     * @param mixed $mValue
     */
    public function __set($sKey, $mValue)
    {
        $this->set($sKey, $mValue);
    } // function __set
    /**
     * Magic method for set property
     * @param string $sKey
     * @return mixed
     */
    public function __get($sKey)
    {
        return $this->get($sKey, $sValue);
    } // function __get

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


} // class \fan\core\service\user
?>