<?php namespace fan\core\exception\service;
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
 * @version of file: 05.02.001 (10.03.2014)
 */
class database extends fatal
{
    /**
     * @var numeric Code of Service database Operation
     */
    protected $nOperCode;
    /**
     * @var string Service database Operation
     */
    protected $sOperMessage;

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
     * Exception's constructor
     * @param \fan\core\service\database $oDatabase
     * @param numeric $nOperCode
     * @param string  $sOperMessage
     * @param numeric $nErrorCode
     * @param string  $sErrorMessage
     * @param string  $sParsedSql
     */
    public function __construct(\fan\core\service\database $oDatabase, $nOperCode, $sOperMessage, $nErrorCode, $sErrorMessage, $sParsedSql)
    {
        $this->nOperCode    = $nOperCode;
        $this->sOperMessage = $sOperMessage;
        $this->nErrorNum    = $nErrorCode;
        $this->sShowErrMsg  = $sErrorMessage;
        $this->sParsedSql   = $sParsedSql;

        $sLogErrMsg = $sOperMessage . "\n" . trim($sErrorMessage) . (empty($nErrorCode) ? '' : "\nError No: " . $nErrorCode . '.');
        parent::__construct($oDatabase, $sLogErrMsg, E_USER_WARNING, null);
    }

    /**
     * Get Code of Operation
     * @return numeric
     */
    public function getOperationCode()
    {
        return $this->nOperCode;
    } // function getOperationCode

    /**
     * Get Operation
     * @return string
     */
    public function getOperation()
    {
        return $this->sOperMessage;
    } // function getOperation

    /**
     * Get ErrorNum
     * @return string
     */
    public function getErrorNum()
    {
        return $this->nErrorNum;
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
     * Get Instance of service
     * @param string $sLogType
     * @return \fan\core\base\service
     */
    protected function _logErrorMessage($sLogType)
    {
        if ($this->nOperCode < 3) {
            parent::_logErrorMessage($sLogType);
        } elseif ($sLogType != 'nothing') {
            \fan\project\service\error::instance()->logDatabaseError(
                    $this->oService->getConnectionName(),
                    $this->sOperMessage,
                    $this->sShowErrMsg,
                    $this->nErrorNum,
                    $this->sParsedSql
            );
        }
        return $this;
    }

} // class \fan\core\exception\service\database
?>