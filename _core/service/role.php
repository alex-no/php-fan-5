<?php namespace core\service;
use project\exception\service\fatal as fatalException;
/**
 * Description of Role
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
 * @version of file: 05.005 (14.01.2014)
 */
class role extends \core\base\service\single
{
    /**
     * Key for mark common User space
     */
    const COMMON_KEY = '_common_';

    /**
     * @var \core\service\user Current User
     */
    private $oCurrentUser;

    /**
     * Static Roles of current user:
     *   array('role1' => 'expire_date_1', 'role2' => 'expire_date_2', ...);
     * @var array
     */
    private $aStaticRoles;

    /**
     * Session Roles (without user's link), "_common_" OR by User space:
     *   array(
     *       '_common_'     => array('role1' => 'expire_date_1', 'role2' => 'expire_date_2', ...),
     *       'user_space_1' => array('role3' => 'expire_date_3', 'role4' => 'expire_date_4', ...),
     *       'user_space_2' => array('role5' => 'expire_date_5', 'role6' => 'expire_date_6', ...),
     *       ...
     *   );
     * @var array
     */
    private $aSessionRoles;

    /**
     * Limits of Session Roles with Fix Qtt access:
     *   array(
     *       'role1' => array(
     *           'qtt' => (int)'qtt_1',
     *           'urn' => (str)'regexp_1',
     *           'main_request' => array((str)'regexp_2', ...),
     *           'add_request'  => array((str)'regexp_3', ...),
     *           'both_request' => array((str)'regexp_4', ...),
     *       ),
     *       'role2' => array(
     *           'qtt' => (int)'qtt_2',
     *           'urn' => (str)'regexp_5',
     *           'main_request' => array((str)'regexp_6', ...),
     *           'add_request'  => array((str)'regexp_7', ...),
     *           'both_request' => array((str)'regexp_8', ...),
     *       ),
     *       ...
     *   );
     * @var array
     */
    private $aFixQttRoles;

    /**
     * All current (merged) Roles: simple array('role1', 'role2', ...);
     * @var array
     */
    private $aAllRoles = array();

    /**
     * Current User Space
     * @var string
     */
    protected $sUserSpace;

    /**
     * Service's constructor
     */
    protected function __construct()
    {
        parent::__construct();

        // Define Current User and his (static) roles
        $this->oCurrentUser = $this->oConfig->get('CHECK_LOGOUT', true) ?
                \project\service\user::checkLogout() :
                \project\service\user::getCurrent();
        $this->sUserSpace = $this->_getUserSpace();
        $this->_setStaticRoles();

        // Define Session roles
        $oSes = \project\service\session::instance('role', 'system');
        $this->aSessionRoles =& $oSes->getByLink('session',       array());
        $this->aFixQttRoles  =& $oSes->getByLink('fix_qtt_roles', array());
        $this->_removeSessionExpired();

        // Make subscribing
        $this->_subscribeForService('user',        'currentUser', array($this, 'onCurrentUserSet'));
        $this->_subscribeForService('user',        'changeRoles', array($this, 'onUserRolesChange'));
        $this->_subscribeForService('user',        'logoutUser',  array($this, 'onLogoutUser'));
        $this->_subscribeForService('application', 'setAppName',  array($this, 'onAppChange'));


        $this->_setCurrentRoles(true);
    } // function __construct

    /**
     * Service's destructor
     */
    public function __destruct() {
        foreach ($this->aFixQttRoles as $k => &$v) {
            if($v['qtt'] > 0) {
                if (true) { //ToDo: Check corresponding of all transfers to conditions
                    $v['qtt']--;
                }
            } else {
                $this->killSessionRoles($k);
            }
        }
    } // function __destruct

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\

    /**
     * Get all Roles
     * @return array
     */
    public function getRoles()
    {
        return $this->aAllRoles;
    } // function getRoles

    /**
     * Set new session Role/s
     * @param mixed $mNewRoles array/string of new roles
     * @param number|string $mExpiredTime - live time of setted role (in second)
     * @param boolean $bInUserSpace - Set Role In User Space OR "_common_"
     * @return array User Roles
     */
    public function setSessionRoles($mNewRoles, $mExpiredTime = null, $bInUserSpace = true)
    {
        $sKey = $bInUserSpace ? $this->_getUserSpace() : self::COMMON_KEY;
        $sVal = $this->_defineExpiredDate($mExpiredTime);
        if (!is_null($sVal) && strcmp($sVal, date('Y-m-d H:i:s')) <= 0) {
            $this->killSessionRoles($mNewRoles, $bInUserSpace ? 1 : 2);
            return $this;
        }

        if (!isset($this->aSessionRoles[$sKey])) {
            $this->aSessionRoles[$sKey] = array();
        }
        $bChanged = false;
        foreach ($this->_convValToArray($mNewRoles) as $v) {
            $v = trim($v);
            $bChanged = $bChanged || !array_key_exists($v, $this->aSessionRoles[$sKey]) || $this->aSessionRoles[$sKey][$v] != $sVal;
            $this->aSessionRoles[$sKey][$v] = $sVal;
        }

        if ($bChanged) {
            $this->_setCurrentRoles(true);
        }
        return $this;
    } // function setSessionRoles

    /**
     * Kill session Role(s)
     * @param string|array $mKillRoles array/string of killed roles
     * @param integer $iDestination 0 - everywhere; 1 - in User-Space; 2 - in "_common_"; 3 - in User-Space and in "_common_"
     * @return array Session Roles
     */
    public function killSessionRoles($mKillRoles = null, $iDestination = 3)
    {
        $bChanged = false;
        if (empty($mKillRoles)) {
            $this->aSessionRoles = array();
            $this->aFixQttRoles  = array();
            $bChanged = true;
        } else {
            foreach ($this->_convValToArray($mKillRoles) as $v0) {
                $sRole = trim($v0);
                if(!empty($sRole)) {
                    foreach ($this->aSessionRoles as &$v1) {
                        if(array_key_exists($sRole, $v1)) {
                            unset($v1[$sRole]);
                            $bChanged = true;
                        }
                    }
                    if(array_key_exists($sRole, $this->aFixQttRoles)) {
                        unset($this->aFixQttRoles[$sRole]);
                    }
                }
            }
        }

        if ($bChanged) {
            $this->_setCurrentRoles(true);
        }
        return $this;
    } // function killSessionRoles

    /**
     * Get session Roles
     * @return array User Roles
     */
    public function getSessionRoles()
    {
        return $this->aSessionRoles;
    } // function getSessionRoles

    /**
     * Set role for fixed quantity of requsts to site
     * @param mixed $mNewRoles array/string of new roles
     * @param number $nQtt - quantity of requsts
     * @param number $nExpiredTime - live time of setted role in second
     * @return array User Roles
     */
    public function setFixQttRoles($mNewRoles, $nQtt = 1, $aRules = array(), $nExpiredTime = null)
    {
        $aRoles = array();
        foreach ($this->_convValToArray($mNewRoles) as $v) {
            $v = trim($v);
            if(!empty($v)) {
                $this->aFixQttRoles[$v] = array(
                    'qtt'          => $nQtt,
                    'urn'          => isset($aRules['urn'])          ? $aRules['urn']          : null,
                    'main_request' => isset($aRules['main_request']) ? $aRules['main_request'] : null,
                    'add_request'  => isset($aRules['add_request'])  ? $aRules['add_request']  : null,
                    'both_request' => isset($aRules['both_request']) ? $aRules['both_request'] : null,
                );
                $aRoles[] = $v;
            }
        }
        return $this->setSessionRoles($aRoles, $nExpiredTime, true);
    } // function setFixQttRoles



    /**
     * Get Current User
     * @return \core\service\user
     */
    public function getCurrentUser()
    {
        return $this->oCurrentUser;
    } // function getCurrentUser

    /**
     * Set new static Role/s
     * @param mixed $mNewRoles array/string of new roles
     * @param number $mExpiredTime - live time of setted role (in second) OR Expired date as string
     * @return \core\service\user
     */
    public function setStaticRoles($mNewRoles, $mExpiredTime = null)
    {
        $oUser = $this->getCurrentUser();
        if (!empty($oUser) && !empty($mNewRoles)) {
            $aRoles = array();
            $sDate  = $this->_defineExpiredDate($mExpiredTime);
            foreach ($this->_convValToArray($mNewRoles, 'Incorrect value of static role') as $v) {
                $aRoles[$v] = $sDate;
            }
            $oUser->addRoles($aRoles);
        }
        return $oUser;
    } // function setStaticRoles

    /**
     * Get static Roles of current User
     * @return array User Roles
     */
    public function getStaticRoles()
    {
        return $this->aStaticRoles;
    } // function getStaticRoles

    /**
     * Check roles. Roles as logic string
     * @param string $sRolesRule Roles which need check
     * @return bolean True if user have Roles to get this object
     */
    public function check($sRolesRule)
    {
        if (empty($sRolesRule)) {
            return true;
        }
        if (!is_string($sRolesRule)) {
            \project\service\error::instance()->error_message(print_r($sRolesRule, false), 'Role is not string');
            return false;
        }
        $bRet = false;
        $sR0  = preg_replace('/\w+/i', "\$this->isRole('\${0}')", $sRolesRule);
        $sR1  = preg_replace(array('/\&+/','/\|+/'), array('&&','||'), $sR0);

        ob_start();
        eval('$bRet=' . $sR1 . ';');
        $sOut = ob_get_contents();
        ob_end_clean();

        if ($sOut) {
            \project\service\error::instance()->error_message($sRolesRule, 'Incorrect role set');
            return false;
        }

        return $bRet;
    } // function check

    /**
     * Check - is exist role with defined name
     * @param string $sRole Roles which need check
     * @return bolean True if user have Roles to get this object
     */
    public function isRole($sRole)
    {
        return in_array($sRole, $this->aAllRoles);
    } // function isRole

    /**
     * Subscribe for event - Set Current User
     * @param \core\service\user $oUser
     */
    public function onCurrentUserSet(\core\service\user $oUser)
    {
        if ($this->getCurrentUser() !== $oUser) {
            $this->oCurrentUser = $oUser;
            $this->_setStaticRoles();
            $this->_setCurrentRoles(true);
        }
    } // function onCurrentUserSet

    /**
     * Subscribe for event - Change Static role of Current User
     * @param \core\service\user $oUser
     */
    public function onUserRolesChange(\core\service\user $oUser)
    {
        if ($this->getCurrentUser() === $oUser) {
            $this->_setStaticRoles();
            $this->_setCurrentRoles(true);
        }
    } // function onUserRolesChange

    /**
     * Subscribe for event - Logout User
     */
    public function onLogoutUser()
    {
        $this->oCurrentUser = null;
        $this->_setStaticRoles();
        $this->_setCurrentRoles(true);
    } // function onLogoutUser

    /**
     * Subscribe for event - Application i changed
     * @param string $sAppName
     */
    public function onAppChange($sAppName)
    {
        $this->_setCurrentRoles();
    } // function onAppChange

    // ======== Private/Protected methods ======== \\

    /**
     * Convert value to array
     * @param mixed $mVal
     * @param string $sExceptionMessage
     * @return array
     * @throws fatalException
     */
    protected function _convValToArray($mVal, $sExceptionMessage = null)
    {
        if (empty($mVal)) {
            return array();
        }
        if (is_string($mVal)) {
            return array($mVal);
        }
        if (is_array($mVal)) {
            return $mVal;
        }
        if (is_object($mVal)) {
            if (method_exists($mVal, 'toArray')) {
                return $mVal->toArray();
            }
            if (method_exists($mVal, '__toString')) {
                return array($mVal->__toString());
            }
        }
        if (!empty($sExceptionMessage)) {
            throw new fatalException($this, $sExceptionMessage);
        }
        return array();
    } // function _convValToArray

    /**
     * Set All Current Roles
     * @param type $bForce
     * @return \core\service\role
     */
    protected function _setCurrentRoles($bForce = false)
    {
        $sUserSpace = $this->_getUserSpace();
        if ($this->sUserSpace != $sUserSpace || $bForce) {
            $this->sUserSpace = $sUserSpace;

            $aTmp = array_merge(
                isset($this->aSessionRoles[self::COMMON_KEY])  ? $this->aSessionRoles[self::COMMON_KEY]  : array(),
                isset($this->aSessionRoles[$sUserSpace]) ? $this->aSessionRoles[$sUserSpace] : array(),
                empty($this->aStaticRoles) ? array() : $this->aStaticRoles
            );
            $aAllRoles = array();
            foreach ($aTmp as $k => $v) {
                $aAllRoles[] = (string)$k;
            }

            $bChanged  = array_diff($this->aAllRoles, $aAllRoles) || array_diff($aAllRoles, $this->aAllRoles);
            $this->aAllRoles = $aAllRoles;
            if ($bChanged) {
                $this->_broadcastMessage('rolesChanged', $aAllRoles);
            }
        }
        return $this;
    } // function _setCurrentRoles

    /**
     * Set Static Roles
     * @return \core\service\role
     */
    protected function _setStaticRoles()
    {
        if (empty($this->oCurrentUser)) {
            $this->aStaticRoles = array();
        } else {
            $this->aStaticRoles = $this->oCurrentUser->getRoles();
            $this->_removeStaticExpired();
        }
        return $this;
    } // function _setStaticRoles

    /**
     * Remove expired Static roles
     * @return \core\service\role
     */
    protected function _removeStaticExpired()
    {
        if (!empty($this->aStaticRoles)) {
            $aRemoved = $this->_checkRoleDate($this->aStaticRoles);
            if (!empty($aRemoved)) {
                $this->getCurrentUser()->removeRole($aRemoved);
            }
        }
        return $this;
    } // function _removeStaticExpired

    /**
     * Remove expired Session roles
     * @return \core\service\role
     */
    protected function _removeSessionExpired()
    {
        $aRemoved = array();
        foreach ($this->aSessionRoles as &$v0) {
            $aRemoved = array_merge($aRemoved, $this->_checkRoleDate($v0));
        }

        foreach ($aRemoved as $v1) {
            if (isset($this->aFixQttRoles[$v1])) {
                foreach ($this->aSessionRoles as $v2) {
                    if (isset($v2[$v1])) {
                        continue 2;
                    }
                }
                unset($this->aFixQttRoles[$v1]);
            }
        }
        return $this;
    } // function _removeSessionExpired

    /**
     * Check Expire Date of Roles
     * @param array $aRoles
     * @return array
     */
    protected function _checkRoleDate(&$aRoles)
    {
        $aRemoved = array();
        $sCurDate = date('Y-m-d H:i:s');
        foreach ($aRoles as $sRole => $sExpire) {
            if (!is_null($sExpire) && strcmp($sExpire, $sCurDate) <= 0) {
                $aRemoved[] = $sRole;
                unset($aRoles[$sRole]);
            }
        }
        return $aRemoved;
    } // function _checkRoleDate

    /**
     * Get User Space
     * @return string
     */
    protected function _getUserSpace()
    {
        return \project\service\user::getCurrentSpace();
    } // function _getUserSpace

    /**
     * Define Expired Date-Time
     * @param number|string $mExpiredTime - live time of setted role (in second)
     * @return string
     */
    protected function _defineExpiredDate($mExpiredTime)
    {
        if (is_null($mExpiredTime)) {
            return null;
        }
        if (is_numeric($mExpiredTime)) {
            return \project\service\date::instance(date('Y-m-d H:i:s'), 'mysql')->shiftDate($mExpiredTime);
        }
        return \project\service\date::instance(date($mExpiredTime))->get('mysql');
    } // function _defineExpiredDate

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\

} // class \core\service\role
?>