<?php namespace fan\core\service\database;
/**
 * ADOdb wrapper for template engine
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
class adodb extends base
{

    /**
     * @var object Connection instance
     */
    private $oConn;

    /**
     * @var string Error message
     */
    private $sErrorMsg;

    /**
     * Constructor
     * @param array $aConfig Configuration data
     */
    public function __construct(\fan\core\base\service $oFacade, \fan\core\service\config\base $oConfig)
    {
        parent::__construct($oFacade, $oConfig, false);
        if (!defined('ADODB_ERROR_HANDLER')) {
            define('ADODB_ERROR_HANDLER', 'adodb_error_handler');
            require_once \bootstrap::parsePath('{CORE_DIR}/../libraries/ADOdb/adodb.inc.php');
        }

        $this->oConn = &ADONewConnection($oConfig['DRIVER']);
        $this->reconnect($oConfig->toArray());
        $this->_handleSql();
    } // function __construct

    /**
     * Quotes a string to be sent to the database
     * @param string $s string to be sent to the database
     * @param bool $magic_quotes_gpc pass get_magic_quotes_gpc() as value
     * @return string Quoted string
     */
    public function qstr($s, $magic_quotes_gpc = false)
    {
        return $this->oConn->qstr($s, $magic_quotes_gpc);
    } // function qstr

    /**
     * Start Transaction
     */
    public function start_transaction()
    {
        $this->oConn->BeginTrans();
    } // function start_transaction

    /**
     * Commit Transaction
     */
    public function commit()
    {
        $this->oConn->CommitTrans();
    } // function commit

    /**
     * Rollback Transaction
     */
    public function rollback()
    {
        $this->oConn->RollbackTrans();
    } // function rollback

    /**
     * Close connection
     */
    public function connectionClose()
    {
        $this->oConn->close();
    } // function connectionClose

    /**
     * Restore connection
     */
    public function reconnect($aConfig)
    {
        $this->oConn->NConnect($aConfig['HOST'], $aConfig['USER'], $aConfig['PASSWORD'], $aConfig['DATABASE']);
    } // function reconnect

    /**
     * Execute SQL query
     * @param string $sSql SQL query
     * @param array $aParams Input parameters
     * @return object Result set
     */
    public function execute($sSql, $aParams = null)
    {
        return $this->_handleSql("Execute", $sSql, $aParams, false);
    } // function execute

    /**
     * Get last insert id
     * @return int Id
     */
    public function getInsertId()
    {
        $cResult = $this->oConn->Insert_ID();
        if ($this->_handleSql()) {
            return $cResult;
        }
        return false;
    } // function getInsertId

    /**
     * Get one value
     * @param string $sSql SQL query
     * @param array $aParams Input parameters
     * @return object Result set
     */
    public function getOne($sSql, $aParams = null)
    {
        return $this->_handleSql("GetOne", $sSql, $aParams, false);
    } // function getOne

    /**
     * Get row
     * @param string $sSql SQL query
     * @param array $aParams Input parameters
     * @return object Result set
     */
    public function getRow($sSql, $aParams = null)
    {
        return $this->_handleSql("GetRow", $sSql, $aParams);
    } // function getRow

    /**
     * Get row assoc
     * @param string $sSql SQL query
     * @param array $aParams Input parameters
     * @return object Result set
     */
    public function getRowAssoc($sSql, $aParams = null)
    {
        $rs = $this->oConn->Execute($sSql, $aParams);
        if ($rs && !$rs->EOF) {
            $cResult = $rs->GetRowAssoc(false);
            if ($this->_handleSql(null, $sSql)) {
                return $cResult;
            }
        } // check result set
        return array();
    } // function getRowAssoc

    /**
     * Get col
     * @param string $sSql SQL query
     * @param array $aParams Input parameters
     * @return object Result set
     */
    public function getCol($sSql, $aParams = null)
    {
        return $this->_handleSql("GetCol", $sSql, $aParams);
    } // function getCol

    /**
     * Get assoc
     * @param string $sSql SQL query
     * @param array $aParams Input parameters
     * @return object Result set
     */
    public function getAssoc($sSql, $aParams = null)
    {
        return $this->_handleSql("GetAssoc", $sSql, $aParams);
    } // function getAssoc

    /**
     * Get all
     * @param string $sSql SQL query
     * @param array $aParams Input parameters
     * @return object Result set
     */
    public function getAll($sSql, $aParams = null)
    {
        return $this->_handleSql("GetAll", $sSql, $aParams);
    } // function getAll

    /**
     * Get all
     * @param string $sSql SQL query
     * @param array $aParams Input parameters
     * @return object Result set
     */
    public function getAllLimit($sSql, $aParams = null, $nQtt = -1, $nOffset = -1)
    {
        $cResult = $this->oConn->SelectLimit($sSql, $nQtt, $nOffset, $aParams);
        $this->aErrorData = $this->oConn->ErrorMsg();
        if ($this->aErrorData || !$cResult) {
            if (!$this->aErrorData) {
                $this->aErrorData = "No result!";
            }
            return array();
        } // if Is Error
        $aRet = $cResult->GetArray();
        return $aRet ? $aRet : array();
    } // function getAllLimit

    /**
     * Get ADOdb version
     * @return string Parameter value
     */
    public function getVersion()
    {
        return @$GLOBALS['ADODB_vers'];
    } // function getVersion

    /**
     * Handler of SQL: Execute request and check error
     * It shows SQL-error or writes it to log-file or sends it to email
     * @param string $sMethod Method of SQL engine
     * @param string $sSql SQL query
     * @param array $aParams Input parameters
     * @param boolean $bRetArr Return result as array (true) or boolean (false)
     * @return "Result" if NO Error else return False
     */
    protected function _handleSql($sMethod = null, $sSql = null, $aParams = null, $bRetArr = true)
    {
        $cResult = $sMethod ? $this->oConn->$sMethod($sSql, $aParams) : true;

        $this->aErrorData = $this->oConn->ErrorMsg();

        if ($this->aErrorData) {
            return $sSql && $bRetArr ? array() : null;
        } // if Is Error

        return $sSql && $bRetArr && !$cResult ? array() : $cResult;
    } // function _handleSql

    /**
     * Log
     */
    protected function _logTime($t, $sSql)
    {
        $dt = microtime(true) - $t;
        if ($dt > 0.5) {
            error_log(date("d/m H-i-s") . ":\t" . $dt . "\t" . $sSql . "\t" . $_SERVER['REQUEST_URI'] . "\n\n", 3, __DIR__ . "/../../../_logs/sql.log");
        }
    } // function log_time
} // class \fan\core\service\database\adodb

/**
 * Parse Data Base error and output message to screen, logfile or email
 * @param string $sDBType Data Base Type
 * @param string $sOperation Operation generate error
 * @param number $nErrorNum Number of error
 * @param string $sErrMsg Error message
 * @param mixed $mMainParam Main parameters
 * @param mixed $mAddParam Add parameters
 * @param object $oObj link to current object
 * @return True if it may parse error else return False
 */
function adodb_error_handler($sDBType, $sOperation, $nErrorNum, $sErrMsg, $mMainParam, $mAddParam, $oObj)
{
    \fan\project\service\error::instance()->database_error($sDBType, $sOperation, $nErrorNum, $sErrMsg, $mMainParam, $mAddParam, $oObj);
} // function adodb_error_handler
?>