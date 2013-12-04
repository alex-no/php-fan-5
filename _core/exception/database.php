<?php namespace core\exception;
/**
 * Exception an error 500
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
class database extends base
{
    /**
     * @var \core\service\database Service database
     */
    protected $oDatabase;

    /**
     * @var string Service database Operation
     */
    protected $sOperation;

    /**
     * @var string Service database Error Num
     */
    protected $nErrorNum;

    /**
     * @var string Service database Error Message
     */
    protected $sErrorMessage;

    /**
     * @var string Service database Parsed SQL
     */
    protected $sParsedSql;

    /**
     * @var boolean Allow Log
     */
    protected $bAllowLog = true;

    /**
     * Exception's constructor
     * @param string $sMessage Error message
     * @param error $nCcode Error Code
     */
    public function __construct(\core\service\database $oDatabase, $sOperation, $nErrorNum, $sErrorMessage, $sParsedSql)
    {
        parent::__construct($sOperation . '. ' . $sErrorMessage . '.' . (empty($nErrorNum) ? '' : ' Error No ' . $nErrorNum), E_USER_WARNING, null);

        $this->oDatabase     = $oDatabase;
        $this->sOperation    = $sOperation;
        $this->nErrorNum     = $nErrorNum;
        $this->sErrorMessage = $sErrorMessage;
        $this->sParsedSql    = $sParsedSql;
    }

    public function __destruct()
    {
        if ($this->bAllowLog) {
            \project\service\error::instance()->logDatabaseError(
                    $this->oDatabase->getConnectionName(),
                    $this->sOperation,
                    $this->sErrorMessage,
                    $this->nErrorNum,
                    $this->sParsedSql
            );
        }
    }

    /**
     * Get Operation
     * @return string
     */
    public function getOperation()
    {
        return $this->sOperation;
    } // function getOperation

    /**
     * Get ErrorMessage
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->sErrorMessage;
    } // function getErrorMessage

    /**
     * Get ErrorNum
     * @return string
     */
    public function getErrorNum()
    {
        return $this->sErrorNum;
    } // function getErrorNum

    /**
     * Get ParsedSql
     * @return string
     */
    public function getParsedSql()
    {
        return $this->sParsedSql;
    } // function getParsedSql

    /**
     * Allow Log
     * @return \core\exception\database
     */
    public function allowLog()
    {
        $this->bAllowLog = true;
        return $this;
    } // function allowLog

    /**
     * Disable Log
     * @return \core\exception\database
     */
    public function disableLog()
    {
        $this->bAllowLog = false;
        return $this;
    } // function disableLog


} // class \core\exception\database
?>