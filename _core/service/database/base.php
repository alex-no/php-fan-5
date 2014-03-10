<?php namespace fan\core\service\database;
/**
 * Description of base
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
 * @version of file: 05.02.001 (10.03.2014)
 */
abstract class base
{
    /**
     * Facade of service
     * @var fan\core\base\service
     */
    protected $oFacade = null;

    /**
     * @var integer Result Types
     */
    protected $iResultType = MYSQL_ASSOC;

    /**
     * Connection Parameters
     * @var string
     */
    protected $aParam = null;

    /**
     * @var string Error message
     */
    protected $aErrorData = null;

    /**
     * Constructor of Database engine
     * @param \fan\core\base\service $oFacade
     * @param array $aParam
     */
    public function __construct(\fan\core\service\database $oFacade, array $aParam)
    {
        $this->oFacade = $oFacade;
        $this->aParam  = $aParam;
        $this->setResultTypes();
    } // function __construct

    /**
     * Set Facade
     * @param \fan\core\base\service $oFacade
     * @return \fan\core\service\database\base
     */
    public function setFacade(\fan\core\base\service $oFacade)
    {
        if (empty($this->oFacade)) {
            $this->oFacade = $oFacade;
        }
        return $this;
    } // function setFacade

    /**
     * Set Result Types for methods: execute, getRow, getAll, getAllLimit
     * @param integer $iResultType
     * @return \fan\core\service\database
     */
    public function setResultTypes($iResultType = MYSQL_ASSOC)
    {
        if ($this->_isValidType($iResultType)) {
            $this->iResultType = $iResultType;
        } // ToDo: Maybe exception there if point incorrect type
        return $this;
    } // function setResultTypes

    /**
     * Reconnect to Db
     * @param array $aParam
     * @param boolean $bMakeException Make Exception if connection impossible
     * @return boolean
     */
    abstract public function reconnect($aParam, $bMakeException = true);

    /**
     * Return last error data
     * @return array Error data
     */
    public function getErrorData()
    {
        return $this->aErrorData;
    } // function getErrorData

    /**
     * Reset error message
     * @return \fan\core\service\database\mysql
     */
    public function resetError()
    {
        $this->aErrorData = null;
        return $this;
    } // function resetError

    /**
     * Validate
     * @param string $iResultType
     * @return boolean
     */
    protected function _isValidType($iResultType)
    {
        $aValidTypes = array(
            MYSQL_ASSOC,
            MYSQL_NUM,
            MYSQL_BOTH
        );
        return in_array($iResultType, $aValidTypes);
    } // function _isValidType

    /**
     * Fix Error
     * @param numeric $nOperCode
     * @param string  $sOperMessage
     * @param numeric $nErrorCode
     * @param string  $sErrorMessage
     * @param boolean $bMakeException
     */
    protected function _fixError($nOperCode, $sOperMessage, $nErrorCode, $sErrorMessage, $bMakeException = false)
    {
        $this->aErrorData = array(
            'oper_code' => $nOperCode,
            'oper_msg'  => $sOperMessage,
            'err_code'  => $nErrorCode,
            'err_msg'   => iconv('','UTF-8', $sErrorMessage),
            'sql'       => $this->sParsedSql,
        );
        $this->oFacade->fixError($this, $bMakeException);
    } // function _fixError
} // class \fan\core\service\database\base
?>