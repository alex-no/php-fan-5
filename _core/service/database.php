<?php namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * Database manager service
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
class database extends \fan\core\base\service\multi
{
    /**
     * @var array Service's Instances
     */
    private static $aInstances = array();

    /**
     * Index of Current instance
     * @var \fan\core\service\database
     */
    private static $oCurrentInstance;

    /**
     * Index of Default instance
     * @var \fan\core\service\database
     */
    private static $oDefaultInstance;

    private static $aParamKeys = array('ENGINE', 'PERSISTENT', 'HOST', 'DATABASE', 'USER', 'PASSWORD', 'SCENARIO');


    /**
     * @var string Connection Name
     */
    protected $sConnectionName;

    /**
     * @var \fan\core\service\database\base Current Engine
     */
    protected $oEngine = null;

    /**
     * @var \fan\core\exception\service\database Database exception
     */
    protected $oException = null;

    /**
     * Levels marked by number:
     *   0 - AUTOCOMMIT (NO TRANSACTION);
     *   1 - READ UNCOMMITTED;
     *   2 - READ COMMITTED;
     *   3 - REPEATABLE READ;
     *   4 - SERIALIZABLE;
     * @var integer Isolation Level of Transaction
     */
    protected $iIsolationLevel = 0;

    /**
     * @var boolean Flag - allow automatically start Transaction before first opration like INSERT,UPDATE,DELETE,REPLACE
     */
    protected $bAutoTransaction = true;

    /**
     * @var boolean Flag - show "Transaction is already started"
     */
    protected $bIsTransaction = false;

    /**
     * @var bool Flag - true if Error exists
     */
    protected $bIsError = false;

    /**
     * @var string Error message
     */
    protected $sErrorMessage = null;

    /**
     * Service's constructor
     */
    protected function __construct($sConnectionName = null, $nExtraKey = 0, $aParam = null)
    {
        $this->sExceptionDbOper = 'nothing';
        parent::__construct();
        $oConfig = $this->getConfig();

        if (empty($aParam)) {
            if (!$sConnectionName) {
                $sConnectionName = $oConfig->get('DEFAULT_CONNECTION');
            } elseif(!$oConfig->get(array('DATABASE', $sConnectionName))) {
                $this->sErrorMessage = 'Undefind connection name: "' . $sConnectionName . '"';
            }
            if (empty($this->sErrorMessage)) {
                $oTmp = $oConfig->get(array('DATABASE', $sConnectionName));
                if ($oTmp instanceof \fan\core\service\config\row) {
                    $aParam = $oTmp->toArray();
                } else {
                    $this->sErrorMessage = 'Can\'t find Parameters for connection name: "' . $sConnectionName . '"';
                }
            }
        }

        if ($this->sErrorMessage) {
            $this->bIsError = true;
            throw new fatalException($this, $this->sErrorMessage);
        }

        $this->sConnectionName = $sConnectionName;

        self::$aInstances[$sConnectionName][$nExtraKey] = $this;
        if ($sConnectionName == $oConfig->get('DEFAULT_CONNECTION')) {
            self::$oDefaultInstance = $this;
        }

        $sEngine = empty($aParam['ENGINE']) ? $oConfig->get('DEFAULT_ENGINE') : $aParam['ENGINE'];
        $sClass  = $this->_getEngine($sEngine, false);
        $this->oEngine = new $sClass($this, $aParam);
        $this->oEngine->reconnect($aParam, true);

        if(!$this->isError()) {
            $sScenario = isset($aParam['SCENARIO']) ? $aParam['SCENARIO'] : $oConfig->get('DEFAULT_SCENARIO');
            if ($sScenario) {
                $this->runScenario($sScenario);
            }
        }
    } // function __construct

    /**
     * Service's destructor
     */
    public function __destruct()
    {
        $this->commit();
    } // function __destruct

    // ======== Static methods ======== \\

    /**
     * Get Service's instance of current service by $sConnectionName
     * If $sConnectionName isn't set - Get defaul instance
     * @param string $sConnectionName Connection name
     * @param string $nExtraKey Allow to create alternate connection
     * @return \fan\core\service\database
     */
    public static function instance($sConnectionName = null, $nExtraKey = 0)
    {
        if ($sConnectionName) {
            if (!isset(self::$aInstances[$sConnectionName][$nExtraKey])) {
                new self($sConnectionName, $nExtraKey);
            }
            self::$oCurrentInstance = self::$aInstances[$sConnectionName][$nExtraKey];
        } else {
            if (!self::$oDefaultInstance) {
                self::$oDefaultInstance = new self(null, $nExtraKey);
            }
            self::$oCurrentInstance = self::$oDefaultInstance;
        }
        return self::$oCurrentInstance;
    } // function instance

    /**
     *
     * @param type $aParam
     * @param type $nExtraKey
     * @return \fan\core\service\database
     */
    public static function instanceByParam($aParam, $nExtraKey = 0)
    {
        if (is_object($aParam) && method_exists($aParam, 'toArray')) {
            $aParam = $aParam->toArray();
        }
        $sConnectionName = self::_getConnectionName($aParam);
        if (!isset(self::$aInstances[$sConnectionName][$nExtraKey])) {
            new self($sConnectionName, $nExtraKey, $aParam);
        }
        return self::$aInstances[$sConnectionName][$nExtraKey];
    } // function instanceByParam

    /**
     * Get Service's instance of current service by $sConnectionName
     * If $sConnectionName isn't set - Get defaul instance
     * @param string $sConnectionName Connection name
     * @return object Conponent's instance
     */
    public static function close()
    {
        if(self::$aInstances) {
            foreach (self::$aInstances as $k => $aInstanses) {
                foreach($aInstanses as $oInst) {
                    $oInst->commit();
                    $oInst->connectionClose();
                }
                unset(self::$aInstances[$k]);
            }
        }
    } // function close

    /**
     * Get current Service's instance
     * @return \fan\core\service\database
     */
    public static function getCurrentInstance()
    {
        return self::$oCurrentInstance ? self::$oCurrentInstance : \fan\project\service\database::instance();
    } // function getCurrentInstance

    /**
     * Get all instances
     * @return array of instances
     */
    public static function getAllInstances()
    {
        return self::$aInstances;
    } // function getAllInstances

    /**
     * Commit all instances
     */
    public static function commitAll()
    {
        self::fixAll('commit', false);
    } // function commitAll
    /**
     * Rollback all instances
     * @param boolean $bSetError Set error flag
     */
    public static function rollbackAll($bSetError = true)
    {
        self::fixAll('rollback', $bSetError);
    } // function rollbackAll
    /**
     * Commit/rollback all instances of this class
     * @param string $sOper operation name
     * @param boolean $bSetError Set error flag
     */
    public static function fixAll($sOper, $bSetError = true)
    {
        if($sOper && in_array($sOper, array('commit', 'rollback')) && self::$aInstances) {
            foreach(self::$aInstances as $aInstanses) {
                foreach($aInstanses as $oInst) {
                    if ($sOper == 'commit') {
                        $oInst->commit();
                    } else {
                        $oInst->rollback(null, $bSetError);
                    }
                }
            }
        }
    } // function fixAll


    // ======== Main Interface methods ======== \\
    /**
     * Get Connection name
     * @return string
     */
    public function getConnectionName()
    {
        return $this->sConnectionName;
    } // function getConnectionName
    /**
     * Get Connection Parameters
     * @return string
     */
    public function getConnectionParam()
    {
        return $this->oConfig->get(array('DATABASE', $this->sConnectionName))->toArray();
    } // function getConnectionParam

    /**
     * Set Result Types for methods: execute, getRow, getAll, getAllLimit
     * Possible values: MYSQL_ASSOC, MYSQL_NUM, MYSQL_BOTH
     * @param type $iResultType
     * @return \fan\core\service\database
     */
    public function setResultTypes($iResultType = MYSQL_ASSOC)
    {
        $this->oEngine->setResultTypes($iResultType);
        return $this;
    } // function setResultTypes

    /**
     * Return last error message
     * @return string Error message
     */
    public function getErrorMessage()
    {
        if (is_null($this->sErrorMessage)) {
            $aErrData = $this->oEngine->getErrorData();
            $this->sErrorMessage = array_val($aErrData, 'err_msg');
        }
        return $this->sErrorMessage;
    } // function getErrorMessage

    /**
     * Return flag of error: true if is Error
     * @return boolean
     */
    public function isError()
    {
        return $this->bIsError;
    } // function isError

    /**
     * Reset error flag
     * @return \fan\core\service\database
     */
    public function resetError()
    {
        $this->bIsError      = false;
        $this->sErrorMessage = null;
        $this->oException    = null;
        $this->oEngine->resetError();
        return $this;
    } // function resetError

    /**
     * Start transaction
     * @return \fan\core\service\database
     */
    public function startTransaction()
    {
        if ($this->iIsolationLevel > 0 && !$this->bIsError) {
            $this->oEngine->startTransaction();
            $this->bIsTransaction = true;
        }
        return $this;
    } // function startTransaction

    /**
     * Set Autostart Transaction for Modification SQL
     * @return \fan\core\service\database
     */
    public function setAutoTransaction($bAutoTransaction)
    {
        $this->bAutoTransaction = !empty($bAutoTransaction);
        return $this;
    } // function setAutoTransaction

    /**
     * Set SavePoint
     * @param string $sSavePoint
     * @return mixed
     */
    public function setSavePoint($sSavePoint)
    {
        if ($this->bIsTransaction && !$this->bIsError) {
            $this->oEngine->setSavePoint($sSavePoint);
        }
        return $this;
    } // function setSavePoint

    /**
     * Commit transaction
     * @return \fan\core\service\database
     */
    public function commit()
    {
        if ($this->bIsTransaction && !$this->bIsError) {
            $this->oEngine->commit();
            $this->bIsTransaction = false;
        }
        return $this;
    } // function commit

    /**
     * Rollback current transaction
     * @param string $sSavePoint
     * @param boolean $bSetError
     * @return \fan\core\service\database
     */
    public function rollback($sSavePoint = null, $bSetError = true)
    {
        if ($this->bIsTransaction) {
            $this->oEngine->rollback($sSavePoint);
            $this->bIsTransaction = false;
        }

        if (!$this->bIsError && $bSetError) {
            $this->bIsError      = true;
            $this->sErrorMessage = 'ROLLBACK';
        }
        //\fan\project\service\entity::fullClearEntity();
        return $this;
    } // function rollback

    /**
     * Run Scenario from Config-file
     * @param type $sScenario
     * @param type $aData
     * @return \fan\core\service\database
     */
    public function runScenario($sScenario, $aData = array())
    {
        $oConf = $this->getConfig(array('SCENARIO', $sScenario));
        if ($oConf && $oConf['SQL']) {
            $bAutoTransaction = $this->bAutoTransaction;
            $this->bAutoTransaction = false;
            if(!is_null($oConf['ISOLATION_LEVEL'])) {
                $iLevel = (int)$oConf['ISOLATION_LEVEL'];
                if ($iLevel < 0 || $iLevel > 4) {
                    throw new fatalException($this, 'Incorrect isolation level: "' . $oConf['ISOLATION_LEVEL'] . '"');
                }
                $this->iIsolationLevel = $iLevel;
            }
            foreach ($oConf['SQL'] as $k => $v) {
                $this->execute($v, isset($aData[$k]) ? $aData[$k] : array());
            }
            $this->bAutoTransaction = $bAutoTransaction;
        }
        return $this;
    } // function runScenario

    /**
     * Execute SQL query
     * @param string $sSql SQL query
     * @param array $aParam Input parameters
     * @param string $sConnectionName Connection name
     * @param bool $bIsDebug true for debug mode
     * @return mixed Result set
     */
    public function execute($sSql, $aParam = null)
    {
        $this->_checkSql($sSql);
        if ($this->bIsError) {
            return null;
        }

        if (!$this->bIsTransaction && $this->bAutoTransaction && preg_match('/^\s*(:?INSERT|UPDATE|DELETE|REPLACE)/i', $sSql)) {
            $this->startTransaction();
        }

        $sSql    = $this->_languageCorrection($sSql, false);
        $nTime   = $this->oConfig['LOG_MORE_THAN'] || $this->oConfig['MAIL_MORE_THAN'] ? microtime(true) : 0;
        $mResult = $this->oEngine->execute($sSql, $aParam);
        $this->_fixExecuteTime($nTime, $sSql, $aParam);

        $sErrorMessage = $this->getErrorMessage();
        if ($sErrorMessage) {
            $this->bIsError = true;
            $this->_setErrorMessage($sErrorMessage);
            $this->rollback();
        }

        return $mResult;
    } // function execute

    /**
     * Get last insert id
     * @return mixed
     */
    public function getInsertId()
    {
        return $this->oEngine->getInsertId();
    } // function getInsertId

    /**
     * Get one value
     * @param string $sSql SQL query
     * @param string $sFieldName Field Name
     * @param array $aParam Input parameters
     * @return mixed
     */
    public function getOne($sSql, $sFieldName, $aParam = null)
    {
        return $this->_executeEngine('getOne', $sSql, array($sFieldName, $aParam));
    } // function getOne

    /**
     * Get row of Data
     * @param string $sSql SQL query
     * @param array $aParam Input parameters
     * @param integer $iResultType
     * @return array Result set
     */
    public function getRow($sSql, $aParam = null, $iResultType = null)
    {
        return $this->_executeEngine('getRow', $sSql, array($aParam, $iResultType));
    } // function getRow

    /**
     * Get row assoc
     * @param string $sSql SQL query
     * @param array $aParam Input parameters
     * @return array Result set
     */
    public function getRowAssoc($sSql, $aParam = null)
    {
        return $this->_executeEngine('getRowAssoc', $sSql, array($aParam));
    } // function getRowAssoc

    /**
     * Get column
     * @param string $sSql SQL query
     * @param array $aParam Input parameters
     * @param string $sColName Column Name
     * @return array Result set
     */
    public function getCol($sSql, $sColName, $aParam = null)
    {
        return $this->_executeEngine('getCol', $sSql, array($sColName, $aParam));
    } // function getCol

    /**
     * Get assoc
     * @param string $sSql SQL query
     * @param array $aParam Input parameters
     * @param array $aColNames Column Name
     * @return array Result set
     */
    public function getAssoc($sSql, $aParam = null)
    {
        return $this->_executeEngine('getAssoc', $sSql, array($aParam));
    } // function getAssoc

    /**
     * Get all
     * @param string $sSql SQL query
     * @param array $aParam Input parameters
     * @return array Result set
     */
    public function getAll($sSql, $aParam = null)
    {
        return $this->_executeEngine('getAll', $sSql, array($aParam));
    } // function getAll

    /**
     * Get DB version
     * @return string
     */
    public function getVersion()
    {
        return $this->oEngine->getVersion();
    } // function getVersion

    /**
     * Get all with limits
     * @param string $sSql SQL query
     * @param array $aParam Input parameters
     * @param number $nQtt Quantity of rows (-1 - no limit)
     * @param number $nOffset line
     * @return object Result set
     */
    public function getAllLimit($sSql, $aParam = null, $nQtt = -1, $nOffset = -1)
    {
        return $this->_executeEngine('getAllLimit', $sSql, array($aParam, $nQtt, $nOffset));
    } // function getAllLimit

    /**
     * Get Status of table
     * Result array has next fields:
     *   Name, Engine, Version, Row_format, Rows, Avg_row_length, Data_length,
     *   Max_data_length, Index_length, Index_length, Data_free, Auto_increment,
     *   Create_time, Update_time,Check_time, Collation, Checksum, Create_options, Comment
     * @param string $sTableName Name of Table
     * @return array
     */
    public function getTableStatus($sTableName)
    {
        return $this->oEngine->getTableStatus($sTableName);
    } // function getTableStatus

    /**
     * Close database connection
     */
    public function connectionClose()
    {
        $this->oEngine->connectionClose();
    } // function connectionClose

    /**
     * Restore closed database connection
     * @param boolean $bMakeException Make Exception if connection impossible
     */
    public function reconnect($bMakeException = true)
    {
        return $this->oEngine->reconnect($this->oConfig['DATABASES'][$this->sConnectionName]->toArray(), $bMakeException);
    } // function reconnect

    /**
     * Modifiy SQL-Query - replace Placeholders by parameters
     * @param string $sSql
     * @param array $aParam
     * @return string
     */
    public function parseSql($sSql, $aParam)
    {
        return $this->oEngine->parseSql($sSql, $aParam);
    } // function parseSql

    /**
     * Return last parsed and executed SQL
     * @return string
     */
    public function getParsedSql()
    {
        return $this->oEngine->getParsedSql();
    } // function getParsedSql

    /**
     * Fix information about Error
     * @param \fan\core\service\database\base $oEngine
     * @param boolean $bMakeException
     * @return \fan\core\service\database
     * @throws \fan\core\exception\service\database
     */
    public function fixError(\fan\core\service\database\base $oEngine, $bMakeException)
    {
        $oErr = \fan\project\service\error::instance();
        /* @var $oErr \fan\core\service\error */
        $aErrData = $oEngine->getErrorData();
        if (!empty($aErrData) && empty($this->oException)) {
            $this->bIsError      = true;
            $this->sErrorMessage = $aErrData['err_msg'];
            if ($bMakeException) {
                $this->oException = new \fan\project\exception\service\database(
                        $this,
                        $aErrData['oper_code'],
                        $aErrData['oper_msg'],
                        $aErrData['err_code'],
                        $aErrData['err_msg'],
                        $aErrData['sql']
                );
                throw $this->oException;
            }
            $oErr->logDatabaseError($this->sConnectionName, $aErrData['oper_msg'], $aErrData['err_msg'], $aErrData['err_code'], $aErrData['sql']);
        } else {
            $oErr->logErrorMessage('Incorrect call method "fixError". Error data is empty.', 'Incorrect call Database service');
        }
        return $this;
    } // function fixError

    // ======== Private/Protected methods ======== \\
    /**
     * Get Connection Name
     * @param array $aParam
     * @return string
     */
    protected static function _getConnectionName(&$aParam)
    {
        $aTmp = array();
        foreach (self::$aParamKeys as $k) {
            $aTmp[$k] = isset($aParam[$k]) ? $aParam[$k] : null;
        }
        $aParam = $aTmp;

        $aNameParam = array();
        foreach (array('HOST', 'DATABASE', 'USER', 'SCENARIO') as $k) {
            if (!empty($aParam[$k])) {
                $aNameParam[] = $aParam[$k];
            }
        }
        return 'ByParameters:' . implode('/', $aNameParam);
    } // function _getConnectionName

    /**
     * Execute Engine
     * @param string $sMethodName
     * @param string $sSql
     * @param array $aArguments
     * @return mixed
     */
    protected function _executeEngine($sMethodName, $sSql, $aArguments)
    {
        $this->_checkSql($sSql);
        $sSql = $this->_languageCorrection($sSql, true);
        array_unshift($aArguments, $sSql);
        $nTime   = $this->oConfig['LOG_MORE_THAN'] || $this->oConfig['MAIL_MORE_THAN'] ? microtime(true) : 0;
        $mResult = call_user_func_array(array($this->oEngine, $sMethodName), $aArguments);
        $this->_fixExecuteTime($nTime, $this->getParsedSql());

        return $mResult;
    } // function _executeEngine

    /**
     * Language correction for queries
     * @param string $sSql SQL query
     * @param bool $bIsCoalesce True for SELECT query with multilingual support
     * @return correcting SQL query
     */
    protected function _languageCorrection($sSql, $bIsCoalesce = true)
    {
        $aMatches = array();
        if ($this->getConfig('SQL_LNG_CORRECTION', true) && preg_match_all('/\W(\{((?:\w+\.)?\`?\w+)(\`?)\})\W/', $sSql, $aMatches, PREG_SET_ORDER)) {
            $oSL = \fan\project\service\locale::instance();
            $sLngCur = '_' . $oSL->getLanguage();
            $sLngDef = '_' . $oSL->getDefaultLanguage();

            foreach ($aMatches as $v) {
                if (0 && $sLngCur != $sLngDef && $bIsCoalesce) { // "0 && " - is temporary hack: don't use "COALESCE"
                    $sNewVal = 'COALESCE(' . $v[2] . $sLngCur . $v[3] . ', ' . $v[2] . $sLngDef . $v[3] . ')';
                } else {
                    $sNewVal = $v[2] . $sLngCur . $v[3];
                }
                $sSql = str_replace($v[1], $sNewVal, $sSql);
            }
        }
        return $sSql;
    } // function _languageCorrection

    /**
     * Check SQL-request
     * @param string $sSql
     * @return \fan\core\service\database
     * @throws \fan\core\exception\service\database
     * @throws fatalException
     */
    protected function _checkSql($sSql)
    {
        if (!empty($this->oException)) {
            throw $this->oException;
        }
        if (empty($sSql)) {
            throw new fatalException($this, 'SQL-request is empty.');
        }
        return $this;
    } // function _checkSql

    /**
     * Set Error message
     * @param string $sErrorMessage Error Massage
     */
    protected function _setErrorMessage($sErrorMessage)
    {
        $this->sErrorMessage = $sErrorMessage;
    } // function _setErrorMessage

    /**
     * Fix Execute-Time
     * @param number $nTime
     * @param string $sSql
     */
    protected function _fixExecuteTime($nTime, $sSql)
    {
        if ($nTime > 0) {
            $nTime   = microtime(true) - $nTime;

            $sMsg    = 'Time=<b>' . $nTime . 's;</b>';
            $sSql    = '<pre style="color:#444444;">' . htmlentities($sSql, ENT_NOQUOTES, 'UTF-8') . '</pre>';

            $oConfig = $this->oConfig;
            if ($oConfig['LOG_MORE_THAN'] && $nTime * 1000 >= $oConfig['LOG_MORE_THAN']) {
                \fan\project\service\log::instance()->logMessage('sql_execute', $sMsg, 'SQL overtime', $sSql);
            }
            if ($oConfig['MAIL_MORE_THAN'] && $nTime * 1000 >= $oConfig['MAIL_MORE_THAN']) {
                \fan\project\service\error::instance()->makeErrorEmail('overtime', 'SQL overtime', $sMsg . '<br /><br />' . $sSql);
            }
        }
    } // function _fixExecuteTime

} // class \fan\core\service\database
?>