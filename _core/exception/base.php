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
 * @version of file: 05.001 (29.09.2011)
 * @abstract
 */
abstract class base extends \Exception
{
    /**
     * @var string Error file which be show to client
     */
    protected $sErrorFile = null;

    /**
     * @var string Public error message
     */
    protected $sErrorMessage = '';

    /**
     * Exception's constructor
     * @param string $sMessage Error message
     * @param error $nCcode Error Code
     */
    public function __construct($sMessage, $nCode = E_USER_ERROR, $sDbOperation = 'rollback')
    {
        if ($sDbOperation) {
            \project\service\database::fixAll($sDbOperation);
        }
        if (empty($this->sErrorFile)) {
            $this->sErrorFile = 'error_500';
        }
        $this->sErrorMessage = $sMessage;
        parent::__construct((string)$sMessage, $nCode);
    } // function __construct

    /**
     * Log error by php
     * @param string $sErrMsg Logged error message
     * @param string $bExceptPos Fix or not exceptin position
     */
    protected function logByPhp($sErrMsg, $bExceptPos = TRUE)
    {
        if($bExceptPos) {
            $sErrMsg .= ' Error at the ' . str_replace('\\', '/', $this->file) . ', line ' . $this->line;
        }
        \bootstrap::logError($sErrMsg);
    } // function logByPhp

    /**
     * Log error by service
     * @param string $sErrMsg Logged error message
     * @param string $sErrTitle Error title
     */
    protected function logByService($sErrMsg, $sErrTitle = '', $sNote = '')
    {
        if (!$sNote) {
            $sNote = \project\service\request::instance()->getInfoString();
            if ($_POST) {
                $sNote .= "\nPOST = " . print_r($_POST, true);
            }
        }
        \project\service\error::instance()->logExceptionMessage($sErrMsg, $sErrTitle ? $sErrTitle : 'Log exception', $sNote);
    } // function logByService

    /**
     * Get Error File
     * @return string
     */
    public function getErrorFile()
    {
        return $this->sErrorFile;
    } // function getErrorFile

    /**
     * Get error-message for log
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->sErrorMessage;
    } // function getErrorMessage

    /**
     * Get error-message for show
     * @return string
     */
    public function getMessageForShow()
    {
        return 'Please visit the site later.';
    } // function getMessageForShow

} // class \core\exception\base
?>