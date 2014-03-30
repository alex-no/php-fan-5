<?php namespace fan\core\service;
/**
 * Description of error
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
 */
class error extends \fan\core\base\service\single
{
    /**
     * Types of system error
     * @var array
     */
    protected $aSysErrorType = array (
        E_ERROR           => 'Error',
        E_WARNING         => 'Warning',
        E_PARSE           => 'Parsing Error',
        E_NOTICE          => 'Notice',
        E_CORE_ERROR      => 'Core Error',
        E_CORE_WARNING    => 'Core Warning',
        E_COMPILE_ERROR   => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR      => 'User Error',
        E_USER_WARNING    => 'User Warning',
        E_USER_NOTICE     => 'User Notice',
        E_STRICT          => 'Runtime Notice',
    );

    /**
     * Types of system error
     * @var array
     */
    protected $aSysErrWithoutFile = array (
        E_USER_ERROR,
        E_USER_WARNING,
        E_USER_NOTICE,
    );

    /**
     * Path of system error which ignored
     * @var array
     */
    protected $aIgnorePath = array();

    /**
     * Mask of system error which need parse
     * @var number
     */
    protected $nSysMask;

    /**
     * Flag of system error
     * @var boolean
     */
    protected $bIsSysError = false;
    /**
     * Flag of buffering of system error
     * @var boolean
     */
    protected $bBufSysError = false;
    /**
     * Backup value of system error mask
     * @var number
     */
    protected $nBufBakSysMask;
    /**
     * Bufering system errors
     * @var array
     */
    protected $aBufSysError;

    /**
     * Service log
     * @var \fan\project\service\log
     */
    protected $oServLog = null;

    /**
     * Service email
     * @var \fan\project\service\email
     */
    protected $oServEmail = null;

    /**
     * Enable Parse DB-error
     * @var boolean
     */
    private $bParseDBerror = true;

    /**
     * Enable Duplicate errors by email
     * @var boolean
     */
    private $bDuplicateByEmail = false;

    /**
     * Service's constructor
     * @param boolean $bAllowIni
     */
    protected function __construct($bAllowIni = true)
    {
        parent::__construct($bAllowIni);
        $oConfig = $this->oConfig;

        if (\bootstrap::isCli()) {
            $this->bDuplicateByEmail = false;
        } elseif (!empty($oConfig['DUPLICATE_BY_EMAIL'])) {
            if (!is_array($oConfig['DUPLICATE_BY_EMAIL']) && !($oConfig['DUPLICATE_BY_EMAIL'] instanceof \fan\core\service\config\row)) {
                $oConfig['DUPLICATE_BY_EMAIL'] = array($oConfig['DUPLICATE_BY_EMAIL']);
            }
            foreach ($oConfig['DUPLICATE_BY_EMAIL'] as $v) {
                if (preg_match($v, $_SERVER['SERVER_NAME'])) {
                    $this->bDuplicateByEmail = true;
                    break;
                }
            }
        }

        if (defined('E_RECOVERABLE_ERROR')) {
            $this->aSysErrorType[E_RECOVERABLE_ERROR] = 'Catchable fatal error';
        }

        $this->nSysMask = $oConfig->get('SYS_MASK', E_ALL);

        foreach ($oConfig->get('IGNORE_PATH', array()) as $v) {
            if (isset($v['path']) && isset($v['mask'])) {
                foreach ($v['path'] as $p) {
                    $this->addIgnorePath($v['mask'], $p);
                }
            }
        }
    } // function __construct

    /**
     * System error handler
     * @param number $nErrNo Error number
     * @param string $sErrMsg Error message
     * @param string $sFileName file name
     * @param number $nLineNum line number
     * @param array $aErrConText An array that points to the active symbol table
     */
    public function handleError($nErrNo, $sErrMsg, $sFileName, $nLineNum, $aErrConText)
    {
        if (!error_reporting() || !($nErrNo & $this->nSysMask)) {
            return;
        }

        $sFileName = str_replace('\\', '/', $sFileName);
        foreach ($this->aIgnorePath as $m => $v) {
            if ($nErrNo & $m) {
                foreach ($v as $p) {
                    if (substr($sFileName, 0, strlen($p)) == $p){
                        return;
                    }
                }
            }
        }

        $sErrType = 'debug';
        foreach ($this->oConfig['SYS_ERR'] as $k => $v) {
            if ($nErrNo & $v) {
                $sErrType = $k;
                break;
            }
        }

        $sMessage = in_array($nErrNo, $this->aSysErrWithoutFile) ? $sErrMsg : $sErrMsg . ' in ' . $sFileName . ' on line ' . $nLineNum;
        $sHeader  = isset($this->aSysErrorType[$nErrNo]) ? $this->aSysErrorType[$nErrNo] : 'Unknown system error ' . $nErrNo;

        if ($this->bIsSysError) {
            \bootstrap::logError($sHeader . "\n" . $sMessage);
            return;
        }
        $this->bIsSysError = true;

        if ($this->bBufSysError) {
            $this->aBufSysError[] = array(
                'sys_err_no'          => $nErrNo,
                'sys_err_message'     => $sErrMsg,
                'sys_err_file_name'   => $sFileName,
                'sys_err_line_number' => $nLineNum,
                'sys_err_context'     => $aErrConText,
                'service_err_type'    => $sErrType,
                'service_message'     => $sMessage,
                'service_header'      => $sHeader
            );
        } else {
            array_walk_recursive($aErrConText, function(&$mItem)
            {
                if (is_object($mItem)) {
                    $mItem = 'Object of class "' . @get_class($mItem) . '"';
                }
            });
            $aNote = array();
            $t1 = '<i style="color:#999999; font-size:9px;">';
            $t2 = '</i>';
            foreach ($aErrConText as $v) {
                if (is_null($v)) {
                    $aNote[] = $t1 . 'NULL';
                } elseif (is_bool($v)) {
                    $aNote[] = $t1 . 'boolean' . $t2 . ' ' . ($v ? 'true' : 'false');
                } elseif (is_scalar($v)) {
                    $aNote[] = $t1 . gettype($v) . $t2 . ' ' . (strlen($v) > 48 ? substr($v, 0, 48) . '...' : $v);
                } elseif (is_array($v)) {
                    $aNote[] = $t1 . 'array' . $t2  . '[' . count($v) . ']';
                } elseif (is_object($v)) {
                    $aNote[] = $t1 . 'object' . $t2 . '[' . get_class($v) . ']';
                } else {
                    $aNote[] = $t1 . 'var' . $t2    . '[' . gettype($v) . ']';
                }
            }
            $this->_logError($sErrType, $sMessage, $sHeader, implode(', ', $aNote), false, $sErrType != 'warn');
        }
        $this->bIsSysError = false;
    } // function handleError

    /**
     * Set Buffering of Error
     * @param type $nSysMask
     * @return boolean
     */
    public function setErrorBuffering($nSysMask = null)
    {
        if (!$this->bBufSysError) {
            $this->aBufSysError = array();
            $this->bBufSysError = true;
            $this->nBufBakSysMask = $this->nSysMask;
            $this->nSysMask = is_null($nSysMask) ? E_ALL : $nSysMask;
            return true;
        }
        return false;
    } // function setErrorBuffering

    /**
     * Get Buffered Error data
     * @return type
     */
    public function getErrorBuffering()
    {
        return empty($this->aBufSysError) ? null : $this->aBufSysError;
    } // function getErrorBuffr

    /**
     * Off Buffering of Error and return buffered data
     * @return type
     */
    public function offErrorBuffering()
    {
        $aResult = $this->getErrorBuffering();
        $this->bBufSysError = false;
        $this->nSysMask = $this->nBufBakSysMask;
        return $aResult;
    } // function offErrorBuffering

    /**
     * Enter description here...
     * @param unknown_type $nMask
     * @param unknown_type $sPath
     */
    public function addIgnorePath($nMask, $sPath)
    {
        if (!empty($sPath)) {
            $sPath = \bootstrap::parsePath($sPath);
            if (@is_dir($sPath)) {
                $sPath = str_replace('\\', '/', realpath($sPath));
                if (!isset($this->aIgnorePath[$nMask]) || !in_array($sPath, $this->aIgnorePath[$nMask])) {
                    $this->aIgnorePath[$nMask][] = $sPath;
                }
            }
        }
    } // function addIgnorePath

    /**
     * Set parse Data Base error as enabled/disabled
     */
    public function setParseDBerror($bValj)
    {
        $this->bParseDBerror = $bValj ? true : false;
    } // function setParseDBerror

    /**
     * Parse Data Base error
     * @param string $sDBType Data Base Type
     * @param string $sOperation Operation generate error
     * @param number $nErrorNum Number of error
     * @param string $sErrMsg Error message
     * @param mixed $mMainParam Main parameters
     * @param mixed $mAddParam Add parameters
     * @param object $oObj link to current object
     */
    public function logDatabaseError($sConnectionName, $sOperation, $sErrorMessage, $nErrorNum, $sParsedSql)
    {
        if (!$this->bParseDBerror) {
            return;
        }

        $sMessage = '<div style="color: #990000;">' . htmlentities(preg_replace('/\s*(\n*\r+|\r*\n+)+\s*/s', ' ', $sErrorMessage)) . '</div>';
        $sHeader  = 'Data Base Error: ' . $sConnectionName . ' - ' . $sOperation . ', Error No ' . $nErrorNum;
        $sNote    = $sParsedSql ? htmlentities($sParsedSql) : '';

        $this->_logError('sql', $sMessage, $sHeader, $sNote, true, true);
    } // function logDatabaseError

    /**
     * Parse Soap error
     * @param object $oSoapError link to Soap Error object
     */
    public function logSoapError($oSoapError)
    {
        $sErrMsg = 'Error ' . $oSoapError->getCode() . ': ' . $oSoapError->getMessage();
        $this->_logError('soap', $sErrMsg, 'Soap Error', '', true, true);
        return $sErrMsg;
    } // function logSoapError


    /**
     * Show errors of different types
     * @param string $sType
     * @param string $sMessage
     * @param string $sHeader
     * @param string $sNote
     * @param boolean $bIsTrace
     * @param boolean $bDuplicateByEmail
     */
    public function logErrorMessage($sMessage, $sHeader = '', $sNote = '', $bIsTrace = false, $bDuplicateByEmail = false)
    {
        $this->_logError('custom', $sMessage, $sHeader ? $sHeader : 'Custom error', $sNote, $bIsTrace, $bDuplicateByEmail);
    } // function logErrorMessage

    /**
     * Show errors of exception
     * @param string $sMessage
     * @param string $sHeader
     * @param string $sNote
     */
    public function logExceptionMessage($sMessage, $sHeader = '', $sNote = '')
    {
        $this->_logError('exception', $sMessage, $sHeader ? $sHeader : 'Custom error', $sNote, true, true);
    } // function logExceptionMessage

    /**
     * Log error-message
     * @param string $sType
     * @param string $sMessage
     * @param string $sHeader
     * @param string $sNote
     * @param boolean $bIsTrace
     * @param boolean $bDuplicateByEmail
     */
    protected function _logError($sType, $sMessage, $sHeader, $sNote, $bIsTrace, $bDuplicateByEmail)
    {
        if (isset($_SERVER['REQUEST_METHOD']) && !in_array(strtoupper($_SERVER['REQUEST_METHOD']), array('GET', 'POST'))) {
            if (!empty($sNote)) {
                $sNote .= '<br />';
            }
            $sNote .= '$_SERVER = ' . print_r($_SERVER, true);
        }

        if (!$this->oServLog) {
            $this->oServLog = \fan\project\service\log::instance();
        }
        $this->oServLog->logError($sType, $sMessage, $sHeader, $sNote, $bIsTrace);

        if ($bDuplicateByEmail && $this->bDuplicateByEmail) {
            $this->makeErrorEmail($sType, $sHeader, $sMessage);
        }
    } // function _logError

    /**
     * Send message about error by email
     * @param string $sSubject
     * @param string $sMessage
     * @param boolean $bDirectMail - send email directly
     */
    public function makeErrorEmail($sType, $sSubject, $sMessage)
    {
        $aConfig = $this->oConfig;
        if (@$aConfig['MAIL_TO']) {
            $sFile = @$aConfig['MAIL_FILE'];
            if (strstr($sSubject, 'fatal') === false && $sFile) {
                $nCtime = time();
                $sFile = \bootstrap::parsePath($sFile) . $sType . '.log.php';
                $bFileExists = file_exists($sFile);

                $aData = $bFileExists ? include($sFile) : array('start' => $nCtime);
                $sKey = md5($sMessage);
                if(isset($aData[$sKey])) {
                    $aData[$sKey]['qtt']++;
                } else {
                    $aData[$sKey] = array(
                        'subject' => $sSubject,
                        'message' => $sMessage,
                        'qtt'     => 1,
                    );
                }

                if ($bFileExists && ($aData['start'] + $aConfig['SENT_TIME_LIMIT'] < $nCtime)) {
                    @unlink($sFile);
                    $aData['start'] = date('d F Y H:i:s.', $aData['start']);
                    $this->_sendErrorEmail('Packet email of ' . $sType, print_r($aData, true));
                } else {
                    file_put_contents($sFile, '<?php' . "\nreturn " . @var_export($aData, true) . ";\n" . '?>');
                    if (!$bFileExists) {
                        @chmod($sFile, 0666);
                    }
                }
            } else {
                $this->_sendErrorEmail($sSubject, $sMessage);
            }
        }
    } // function makeErrorEmail


    /**
     * Check of packet files
     */
    public function sendPacketEmais()
    {
        $aConfig = $this->oConfig;
        if (@$aConfig['MAIL_TO'] && @$aConfig['MAIL_FILE']) {

            $nCtime = time();

            $sPath = \bootstrap::parsePath(@$aConfig['MAIL_FILE']);

            $sDirName = dirname($sPath);
            $sPrefix = basename($sPath);
            $nLen = strlen($sPrefix);

            foreach (scandir($sDirName) as $v) {
                $sFile = $sDirName . '/' . $v;
                if (substr($v, 0, $nLen) == $sPrefix && file_exists($sFile)) {
                    $aData = include($sFile);
                    if ($aData['start'] + $aConfig['SENT_TIME_LIMIT'] < $nCtime) {
                        @unlink($sFile);
                        $aData['start'] = date('d F Y H:i:s.', $aData['start']);
                        $this->_sendErrorEmail('Packet email of ' . substr($v, $nLen, -8), print_r($aData, true));
                    }
                }
            }
        }
    } // function sendPacketEmais

    /**
     * Send message about error by email
     * @param string $sSubject
     * @param string $sMessage
     */
    protected function _sendErrorEmail($sSubject, $sMessage)
    {
        $aConfig = $this->oConfig;
        if (!$this->oServEmail) {
            $this->oServEmail = \fan\project\service\email::instance('err_message');
        }
        $this->oServEmail->clearAllRecipients();
        if (isset($aConfig['MAIL_CC'])) {
            foreach ($aConfig['MAIL_CC'] as $v) {
                $v = trim($v);
                if ($v) {
                    if (strpos($v, '/') > 0) {
                        list($sEmail, $sName) = explode('/', $v, 2);
                    } else {
                        $sEmail = $v;
                        $sName  = '';
                    }
                    $this->oServEmail->addCc($sEmail, $sName);
                }
            }
        }
        $this->oServEmail->send($sSubject, $sMessage, $aConfig['MAIL_TO'], @$aConfig['NAME_TO']);
    } // function _sendErrorEmail

} // class \fan\core\service\error
?>