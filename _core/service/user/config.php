<?php namespace fan\core\service\user;
use fan\project\exception\service\fatal as fatalException;
/**
 * User-data engine by data from config-file
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
 */
class config extends base
{

    /**
     * Config of Authentication Data
     * @var \fan\core\service\config\row
     */
    protected $oAuthConfig;

    /**
     * Constructor of user engine
     * @param mixed $mIdentifyer
     * /
    public function __construct($mIdentifyer)
    {
        parent::__construct($mIdentifyer);
    } // function __construct */

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    /**
     * Convert text of password to text of hash
     * @param string $sPassword
     * @return string
     */
    public function makePasswordHash($sPassword)
    {
        $sLogin = array_val($this->aData, 'login', $this->mIdentifyer);
        return $sLogin ? md5($sLogin . $sPassword . $this->oConfig->get('ENGINE_KEY')) : '';
    } // function makePasswordHash

    // ======== Private/Protected methods ======== \\

    /**
     * Load and Save User Data and return TRUE if success
     * @return boolean
     */
    protected function _loadData()
    {
        $this->aData = array();

        $sFile = $this->oConfig->get('ENGINE_SOURCE', 'auth');
        $sKey  = $this->oConfig->get('ENGINE_KEY');
        if (empty($sKey)) {
            return false;
        }

        $this->oAuthConfig = \fan\project\service\config::instance($sFile)->get($sKey);

        $oRule = $this->_getAccessRule();
        if (empty($oRule)) {
            return false;
        }

        $sMainRole = $this->oAuthConfig->main_role;
        if (empty($sMainRole) || !is_string($sMainRole)) {
            throw new fatalException($this->oFacade, 'Main role isn\'t set in config-file "' . $sFile . '" for "' . $sKey . '"!');
        }

        if ($this->mIdentifyer == 'anonymous') {
            if (!empty($oRule['is_anonymous'])) {
                $this->aData = $this->_getAnonymousData($oRule);
            }
        } else {
            $this->aData = $this->_getAuthorizedData($oRule);
        }
        return !empty($this->aData);
    } // function _loadData

    /**
     * Get Data of Anonymous user
     * @param \fan\core\service\config\row $oRule
     * @return array
     */
    protected function _getAnonymousData(\fan\core\service\config\row $oRule)
    {
        $this->bIsValid = true;

        $aData = array(
            'id'       => 'anonymous',
            'login'    => 'anonymous',
            'password' => $this->makePasswordHash(''),
            'roles'    => array(),
        );

        $aData['roles'][$this->oAuthConfig->main_role] = null;

        $this->_mergeRoles($aData['roles'], $oRule->add_roles);
        return $aData;
    } // function _getAnonymousData

    protected function _getAuthorizedData($oRule)
    {
        $oAuth = $this->_getAuthentication();
        if (empty($oAuth)) {
            return array();
        }

        $aData = array(
            'id'       => $oAuth->login,
            'roles'    => array(),
        );
        foreach ($this->_getKeyList() as $k) {
            if (isset($oAuth->$k) && !isset($aData[$k])) {
                $aData[$k] = $oAuth->$k;
            }
        }

        $aData['roles'][$this->oAuthConfig->main_role] = null;

        $this->_mergeRoles($aData['roles'], $oRule->add_roles);
        $this->_mergeRoles($aData['roles'], $oAuth->roles);
        return $aData;
    } // function _getAuthorizedData

    /**
     * Save User Data and return TRUE if success
     * @return boolean
     */
    protected function _saveData()
    {
        return false;
    } // function _saveData

    /**
     * Validate User Data before saving
     * @return boolean
     */
    protected function _validateForSave()
    {
        return false;
    } // function _validateForSave

    /**
     * Get Access Rule from config
     * @return \fan\core\service\config\row
     */
    protected function _getAccessRule()
    {
        $aKeys = array('re_domain' => 'SERVER_NAME', 're_server_ip' => 'SERVER_ADDR', 're_client_ip' => 'REMOTE_ADDR');
        if (!empty($this->oAuthConfig['RULE'])) {
            foreach ($this->oAuthConfig['RULE'] as $oRule) {
                foreach ($aKeys as $k0 => $k1) {
                    if (!empty($oRule[$k0]) && !preg_match($oRule[$k0], $_SERVER[$k1])) {
                        continue 2;
                    }
                }
                return $oRule;
            }
        }
        return null;
    } // function _getAccessRule

    protected function _getAuthentication()
    {
        foreach ($this->oAuthConfig['AUTHENTICATION'] as $oAuth) {
            foreach ($this->oConfig['IDENTIFYERS'] as $v) {
                if ($oAuth->$v == $this->mIdentifyer) {
                    return $oAuth;
                }
            }
        }
        return null;
    } // function _getAuthentication

    /**
     * Merge Rule
     * @param array $aTarget
     * @param mixed $mSource
     */
    protected function _mergeRoles(&$aTarget, $mSource)
    {
        if (!empty($mSource) && is_string($mSource)) {
            $aRules = array($mSource);
        } elseif (!empty($mSource) && is_array($mSource)) {
            $aRules = $mSource;
        } elseif (is_object($mSource) && method_exists($mSource, 'toArray')) {
            $aRules = $mSource->toArray();
        } else {
            return;
        }

        foreach ($aRules as $v) {
            $aTarget[$v] = null;
        }
    } // function _mergeRoles

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

} // class \fan\core\service\user\config
?>