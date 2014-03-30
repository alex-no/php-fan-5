<?php namespace fan\core\service\user;
use fan\project\exception\service\fatal as fatalException;
/**
 * Description of user-data
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
 * @version of file: 05.02.002 (31.03.2014)
 * @method string getLogin()
 * @method string getNickName()
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
 * @method \fan\core\service\user setLogin()     setLogin(string $sLogin)
 * @method \fan\core\service\user setNickName()  setNickName(string $sNickName)
 * @method \fan\core\service\user setFirstName() setFirstName(string $sFirstName)
 * @method \fan\core\service\user setLastName()  setLastName(string $sLastName)
 * @method \fan\core\service\user setTitle()     setTitle(string $sTitle)
 * @method \fan\core\service\user setGender()    setGender(string $sGender)
 * @method \fan\core\service\user setEmail()     setEmail(string $sEmail)
 * @method \fan\core\service\user setPhone()     setPhone(string $sPhone)
 * @method \fan\core\service\user setLocale()    setLocale(string $sLocale)
 * @method \fan\core\service\user setAddress()   setAddress(string|array $aAddress)
 * @method \fan\core\service\user setStatus()    setStatus(string $sStatus)
 * @method string getPassword()
 */
abstract class base implements \Serializable
{
    /**
     * Service User
     * @var \fan\core\service\user
     */
    protected $oFacade;

    /**
     * Row of config
     * @var \fan\core\service\config\row
     */
    protected $oConfig;

    /**
     * Full data of user. Used keys:
     *   'id'         => !number|string
     *   'password'   => !string
     *   'login'      => !string
     *   'nickname'   => string
     *   'first_name' => string
     *   'last_name'  => string
     *   'title'      => string
     *   'gender'     => integer
     *   'email'      => string
     *   'phone'      => string
     *   'locale'     => string
     *   'address'    => string|array
     *   'status'     => string
     *   'roles'      => !array
     *   'join_date'  => string
     *   'visit_date' => string
     *  "!" - required parameter
     * @var array|object
     */
    protected $mData;

    /**
     * @var mixed
     */
    protected $mIdentifyer = null;

    /**
     * Flag shows is user valid:
     *  - for new user TRUE if set all identifier and password;
     *  - for exists user TRUE if check one of identifier and password;
     * @var boolean
     */
    protected $bIsValid = false;
    /**
     * This flag is TRUE if user created as new and isn't saved yet
     * @var boolean
     */
    protected $bIsNew = true;
    /**
     * This array contain modified data
     * @var array
     */
    protected $aChanged = array();

    /**
     * Constructor of user engine
     * @param mixed $mIdentifyer
     */
    public function __construct($mIdentifyer)
    {
        $this->mIdentifyer = $mIdentifyer;
    } // function __construct

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\
    /**
     * Convert text of password to text of hash
     * @param string $sPassword
     * @return string
     */
    abstract public function makePasswordHash($sPassword);

    /**
     * Set Facade
     * @param \fan\core\service\user $oFacade
     */
    public function setFacade(\fan\core\service\user $oFacade)
    {
        if (empty($this->oFacade)) {
            $this->oFacade = $oFacade;
        }
        return $this;
    } // function setFacade

    /**
     * Set Config
     * @param \fan\core\service\config\row $oConfig
     */
    public function setConfig(\fan\core\service\config\row $oConfig)
    {
        if (empty($this->oConfig)) {
            if (empty($oConfig)) {
                throw new fatalException($this->oFacade, 'User Engine has empty config!');
            }
            $this->oConfig = $oConfig;
/*
            if (empty($this->mData)) {
                $aIdent = adduceToArray($this->oConfig['IDENTIFYERS']);
                if (count($aIdent) == 1) {
                    $this->mData[$aIdent[0]] = $this->mIdentifyer;
                }
            }
 */
        }
        return $this;
    } // function setConfig

    // --- Getters method --- \\

    /**
     * Get Id
     * @return mixed
     */
    public function getId()
    {
        return array_val($this->mData, 'id', $this->mIdentifyer);
    } // function getId

    /**
     * Get Full User Name (with title OR not)
     * @param boolean $bWithTitle
     * @return string
     */
    public function getFullName($bWithTitle = true)
    {
        $sResult  = $bWithTitle ? $this->getTitle() . ' ' : '';
        $sResult .= $this->getFirstName() . ' ';
        $sResult .= $this->getLastName();
        return trim($sResult);
    } // function getFullName

    /**
     * Get User Roles
     * @param boolean $bForce
     * @return array
     */
    public function getRoles($bForce = false)
    {
        return ($this->bIsValid || $bForce) && isset($this->mData['roles']) ? $this->mData['roles'] : array();
    } // function getRoles

    /**
     * Get All user data
     * @return array|object
     */
    public function getAllData()
    {
        return $this->mData;
    } // function getAllData

    // --- Setters method --- \\
    /**
     * Set Visit Date as string in format "Y-m-d"
     * @param string $sDate
     * @return \fan\core\service\user
     */
    public function setVisitDate($sDate = null)
    {
        if (is_null($sDate)) {
            $sDate = date('Y-m-d');
        }
        if (!isset($this->mData['visit_date']) || $this->mData['visit_date'] != $sDate) {
            $this->mData['visit_date'] = $this->aChanged['visit_date'] = $sDate;
        }
        return $this->oFacade;
    } // function setVisitDate

    // --- Verifying/manipulation method --- \\
    /**
     * Set Password
     * @param string $sPassword
     * @return \fan\core\service\user
     */
    public function setPassword($sPassword)
    {
        $sHash = $this->makePasswordHash($sPassword);
        if (!isset($this->mData['password']) || $this->mData['password'] != $sHash) {
            $this->mData['password'] = $this->aChanged['password'] = $sHash;
        }
        $this->bIsValid = true;
        return $this->oFacade;
    } // function setPassword

    /**
     * Check Password
     * @param string $sPassword
     * @return boolean
     */
    public function checkPassword($sPassword)
    {
        $sHash = $this->makePasswordHash($sPassword);
        $this->bIsValid = !empty($this->mData['password']) && $this->mData['password'] == $sHash;

        // Log Error Authentication if it is allowed
        if (!$this->bIsValid && $this->oConfig['LOG_ERR_AUTH']) {
            if (empty($this->mData)) {
                $sErrMsg = 'Data for "' . $this->mIdentifyer . '" isn\'t present.';
                $sNote   = '';
            } else {
                $sErrMsg = 'Error password for "' . $this->mIdentifyer . '".';
                $sNote   = 'Hash: ' . $sHash . "\n" . 'NS: ' . $this->oFacade->getUserSpace();
            }
            $sErrMsg .= "\nTime: " . date('Y-m-d H:i:s') . "\nClient IP: " . $_SERVER['REMOTE_ADDR'];
            service('error')->logErrorMessage($sErrMsg, 'Error authentication', $sNote);
        }

        return $this->bIsValid;
    } // function checkPassword

    /**
     * Load User data
     * @return \fan\core\service\user
     */
    public function load()
    {
        $this->bIsValid = false;
        if ($this->_loadData()) {
            $this->bIsNew   = false;
            $this->aChanged = array();
        }
        return $this->oFacade;
    } // function load
    /**
     * Logout User
     * @return \fan\core\service\user
     */
    public function logout()
    {
        return $this->oFacade;
    } // function logout

    /**
     * Save User data
     * @return \fan\core\service\user
     */
    public function save()
    {
        if ($this->bIsNew) {
            $this->mData['join_date'] = date('Y-m-d');
        }

        if ($this->isChanged() && $this->_validateForSave() && $this->_saveData()) {
            $this->bIsNew   = false;
            $this->aChanged = array();
        }
        return $this->oFacade;
    } // function save

    /**
     * Get flag: is User-data valid
     * @return boolean
     */
    public function isValid()
    {
        return $this->bIsValid;
    } // function isValid
    /**
     * Get flag: is New User
     * @return boolean
     */
    public function isNew()
    {
        return $this->bIsNew;
    } // function isNew
    /**
     * Get flag: is User-data valid
     * @return boolean
     */
    public function isChanged()
    {
        return !empty($this->aChanged);
    } // function isChanged

    // ======== Private/Protected methods ======== \\

    /**
     * Save User Data and return TRUE if success
     * Method must set property $this->mData
     * @return boolean
     */
    abstract protected function _loadData();
    /**
     * Save User Data and return TRUE if success
     * @return boolean
     */
    abstract protected function _saveData();
    /**
     * Validate User Data before saving
     * @return boolean
     */
    abstract protected function _validateForSave();

    /**
     * Get List of Keys
     * @return array
     */
    protected function _getKeyList()
    {
        return array(
            'id',
            'password',
            'login',
            'nickname',
            'first_name',
            'patronymic',
            'last_name',
            'title',
            'gender',
            'email',
            'phone',
            'locale',
            'address',
            'status',
            'roles',
            'join_date',
            'visit_date',
        );
    } // function _getKeyList

    /**
     * Set any data
     * @param string $sKey
     * @param mixed $mVal
     * @return \fan\core\service\user
     */
    protected function _set($sKey, $mVal)
    {
        if (!isset($this->mData[$sKey]) && !is_null($mVal) || $this->mData[$sKey] != $mVal) {
            $this->aChanged[$sKey] = $mVal;
        }
        $this->mData[$sKey] = $mVal;
        return $this->oFacade;
    } // function _set
    /**
     * Get any data
     * @param string $sKey
     * @return mixed
     */
    protected function _get($sKey)
    {
        return isset($this->mData[$sKey]) ? $this->mData[$sKey] : null;
    } // function _get

    /**
     * Convert Camel Case string to format "separated by _"
     * @param string $sStr
     * @return string
     */
	protected function _convCamelCase($sStr)
    {
        return strtolower(implode('_', preg_split('/(?<=\\w)(?=[A-Z])/', $sStr)));
    } // function _convCamelCase

    // ======== The magic methods ======== \\

    /**
     * Call set/get methods for control of data
     * @param string $sMethod
     * @param array $aArgs
     * @return mixed
     * @throws fatalException
     */
    public function __call($sMethod, $aArgs)
    {
        $sKey = $this->_convCamelCase(substr($sMethod, 3));
        if(substr($sMethod, 0, 3) == 'set') {
            return $this->_set($sKey, isset($aArgs[0]) ? $aArgs[0] : null);
        } elseif (substr($sMethod, 0, 3) == 'get') {
            return $this->_get($sKey);
        }
        throw new fatalException($this->oFacade, 'Incorrect call of User Engine!');
    } // function __call

    // ======== Required Interface methods ======== \\

    public function serialize()
    {
        return serialize(array(
            'flags' => array(
                'valid'   => $this->bIsValid,
                'new'     => $this->bIsNew,
                'changed' => $this->aChanged,
            ),
            'identifyer' => $this->mIdentifyer,
            'main'       => serialize($this->mData),
        ));
    }

    public function unserialize($sData)
    {
        $aData = unserialize($sData);

        $this->bIsValid = $aData['flags']['valid'];
        $this->bIsNew   = $aData['flags']['new'];
        $this->aChanged = $aData['flags']['changed'];

        $this->mIdentifyer = $aData['identifyer'];
        $this->mData       = unserialize($aData['main']);
    }

} // class \fan\core\service\user\base
?>