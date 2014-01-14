<?php namespace core\exception;
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
 * @version of file: 05.005 (14.01.2014)
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
    private $sDbOperation = null;

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

        $sDbOperation = $this->_getDbOperation();
        if (!empty($sDbOperation) && class_exists('\core\service\database', false)) {
            \project\service\database::fixAll($sDbOperation);
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
     * Log error by php
     * @param string $sErrMsg Logged error message
     * @param string $bExceptPos Fix or not exceptin position
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
     * @return \core\exception\base
     */
    protected function _logByService($sErrMsg, $sErrTitle = '', $sNote = '')
    {
        if (!$sNote) {
            $sNote = \project\service\request::instance()->getInfoString();
            if (!empty($_POST)) {
                $sNote .= "\nPOST = " . print_r($_POST, true);
            }
        }
        \project\service\error::instance()->logExceptionMessage($sErrMsg, $sErrTitle ? $sErrTitle : 'Log exception', $sNote);
        return $this;
    } // function _logByService

    /**
     * Get operation for Db (rollback, commit or nothing) when exception occured
     * @param string $sDbOper
     * @return null|string
     */
    protected function _getDbOperation($sDbOper = null)
    {
        if (in_array($sDbOper, array('rollback', 'commit'))) {
            return (string)$sDbOper;
        } elseif (!empty($sDbOper) && $sDbOper != 'nothing') {
            trigger_error('Incorret DB-operation name for Ecxeption ' . get_class($this), E_USER_WARNING);
        }
        return null;
    } // function _getDbOperation

} // class \core\exception\base
?>