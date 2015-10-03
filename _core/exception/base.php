<?php namespace fan\core\exception;
/**
 * Exception base class
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
 * @version of file: 05.02.011 (03.10.2015)
 * @abstract
 */
abstract class base extends \Exception
{
    /**
     * File to show the error for the user
     * @var string
     */
    protected $sShowErrFile = null;

    /**
     * Public error message for the user
     * @var string
     */
    protected $sShowErrMsg = '';

    /**
     * Public error message for the user
     * @var string
     */
    protected $sLogErrMsg = '';

    /**
     * Operation with DB when exception occured
     * Possible values: 'rollback', 'commit' or NULL
     * @var string
     */
    private $sDbOper = null;

    /**
     * Vars are excluded for Logging
     * @var array
     */
    protected $aExcludeLogVars = array(
        'aExcludeLogVars',
        'message',
        'file',
        'line',
        'xdebug_message',
    );

    /**
     * Exception's constructor
     * @param string $sLogErrMsg Error message for log
     * @param numeric $nCode Error Code
     * @param \Exception $oPrevious Previous Exception
     */
    public function __construct($sLogErrMsg, $nCode = E_USER_ERROR, $oPrevious = null)
    {
        $this->sLogErrMsg = $sLogErrMsg;
        if (empty($this->sShowErrMsg)) {
            $this->sShowErrMsg = 'Please visit the site later.';
        }
        if (empty($this->sShowErrFile)) {
            $this->sShowErrFile = 'error_500';
        }

        $this->sDbOper = $this->_defineDbOper();
        if (!empty($this->sDbOper) && class_exists('\fan\core\service\database', false)) {
            \fan\project\service\database::fixAll($this->sDbOper);
        }

        if (!empty($oPrevious) && $oPrevious instanceof \Exception) {
            parent::__construct((string)$sLogErrMsg, $nCode, $oPrevious);
        } else {
            parent::__construct((string)$sLogErrMsg, $nCode);
        }
    } // function __construct

    /**
     * Get Error File
     * @return string
     */
    public function getErrorFile()
    {
        return $this->sShowErrFile;
    } // function getErrorFile

    /**
     * Get error-message for log
     * @return string
     */
    public function getMessageForLog()
    {
        return $this->sLogErrMsg;
    } // function getErrorMessage

    /**
     * Get error-message for show
     * @return string
     */
    public function getMessageForShow()
    {
        return $this->sShowErrMsg;
    } // function getMessageForShow

    /**
     * Get Database Operation
     * @return string
     */
    public function getDbOper()
    {
        return $this->sDbOper;
    } // function getDbOper

    /**
     * Make property of Exception for logging them
     * @return string
     */
    public function getLogVars()
    {
        $aVars = array();
        $aTmp  = get_object_vars($this);
        foreach ($aTmp as $k => $v) {
            if (in_array($k, $this->aExcludeLogVars)) {
                continue;
            } elseif (is_scalar($v)) {
                $aVars[$k] = $v;
            } elseif (is_null($v)) {
                $aVars[$k] = 'NULL';
            } elseif (is_array($v)) {
                $aVars[$k] = 'array[' . count($v) . ']';
            } elseif (is_object($v)) {
                $aVars[$k] = 'object of "' . get_class($v) . '"';
            } else {
                $aVars[$k] = strval($v);
            }
        }
        return var_export($aVars, true);
    } // function getLogVars

    /**
     * Log error by php
     * @param string $sErrMsg Logged error message
     * @param string $bExceptPos Fix or not exceptin position
     * @return \fan\core\exception\base
     */
    protected function _logByPhp($sErrMsg, $bExceptPos = true)
    {
        if($bExceptPos) {
            $sErrMsg .= ' Error at the ' . str_replace('\\', '/', $this->file) . ', line ' . $this->line;
        }
        \bootstrap::logError($sErrMsg);
        return $this;
    } // function _logByPhp

    /**
     * Log error by service
     * @param string $sErrMsg Logged error message
     * @param string $sErrTitle Error title
     * @param string $sNote
     * @return \fan\core\exception\base
     */
    protected function _logByService($sErrMsg, $sErrTitle = '', $sNote = '')
    {
        if (!$sNote) {
            $sNote = service('request')->getInfoString();
            if (!empty($_POST)) {
                $sNote .= "\nPOST = " . var_export($_POST, true);
            }
        }
        service('error')->logExceptionMessage($sErrMsg, $sErrTitle ? $sErrTitle : 'Log exception', $sNote);
        return $this;
    } // function _logByService

    /**
     * Get operation for Db (rollback, commit or nothing) when exception occured
     * @param string $sDbOper
     * @return null|string
     */
    protected function _defineDbOper($sDbOper = null)
    {
        if (in_array($sDbOper, array('rollback', 'commit'))) {
            return (string)$sDbOper;
        } elseif (!empty($sDbOper) && $sDbOper != 'nothing') {
            trigger_error('Incorret DB-operation name for Ecxeption ' . get_class($this), E_USER_WARNING);
        }
        return null;
    } // function _defineDbOper

} // class \fan\core\exception\base
?>