<?php namespace core\bootstrap;
/**
 * Description of runner
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
 * @version of file: 05.007 (23.02.2014)
 */

class runner
{
    const MAIN_ERROR_DEMONSTRATOR    = '{CORE_DIR}/error/demonstrator.php';
    const PROJECT_ERROR_DEMONSTRATOR = '{PROJECT_DIR}/error/demonstrator.php';

    /**
     * Ini-config data
     * @var array
     */
    protected $aConfig;

    /**
     * Construct of class
     * @param array $aConfig
     */
    public function __construct($aConfig)
    {
        $this->aConfig = $aConfig;
    }

    /**
     * Run process to execute
     * @param boolean $bIsEcho allow to output data
     * @param string|array $mProcedure procedure for run
     * @param array $aParameters Parameters for call
     * @return mixed Output data
     */
    public function run($bIsEcho = true, $mProcedure = null, $aParameters = array())
    {
        try {
            ob_start(array($this, 'handleOb'));

            if (empty($mProcedure)) {
                list($mProcedure, $aParameters) = $this->getHandler();
            }
            $mRet = call_user_func_array($mProcedure, empty($aParameters) ? array() : $aParameters);

            ob_end_clean();

            if ($bIsEcho) {
                \project\service\header::instance()->sendHeaders();

                if (is_array($mRet) && is_callable($mRet)) {
                    call_user_func($mRet);
                } elseif (is_scalar($mRet)) {
                    echo $mRet;
                }
            }

            return $mRet;
        } catch (\core\exception\base $e) {
        } catch (\Exception $e) {
            \bootstrap::logError("Unrecognized fatal error.\n" . print_r($e, true)); // ToDo: There can be out of memory when $e too bir or has recourcive properties
        }

        ob_end_clean();
        $this->_parseException($e, $bIsEcho);
        return null;
    } // function run

    public function runCli($sClassName, $sMethodName)
    {
        try {
            list($mProcedure, $aAddParameters) = $this->getHandler();
            $aParameters = array($sClassName, $sMethodName);
            if (!empty($aAddParameters)) {
                $aParameters = array_merge($aParameters, $aAddParameters);
            }
            $mRet = call_user_func_array($mProcedure, $aParameters);

            if (is_array($mRet) && is_callable($mRet)) {
                call_user_func($mRet);
            } elseif (is_scalar($mRet)) {
                echo $mRet;
            }

            return $mRet;
        } catch (\core\exception\base $e) {
        } catch (\Exception $e) {
            \bootstrap::logError("Unrecognized fatal error.\n" . print_r($e, true));
        }

        $this->_parseException($e, true);
        return null;
    } // function runCli

    /**
     * Interception uncontrolled errors
     * @param string $sMessage
     * @return string
     */
    public function getHandler()
    {
        $aHandler = \project\service\matcher::instance()->getCurrentHandler();
        return array($aHandler['method'], $aHandler['param']);
    } // function getHandler

    /**
     * Interception uncontrolled errors
     * @param string $sMessage
     * @return string
     */
    public function handleOb($sMessage)
    {
        if (trim($sMessage)) {
            if (preg_match('/\w+\s+error.+$/', $sMessage, $aMatches)) {
                $sErrMessage = trim(strip_tags($aMatches[0]));
                $sErrNote    = htmlspecialchars($sMessage);
            } elseif (trim(strip_tags($sMessage)) == '') {
                $sErrMessage = htmlspecialchars($sMessage);
                $sErrNote    = '';
            } else {
                $sErrMessage = trim(strip_tags($sMessage));
                $sErrNote    = htmlspecialchars($sMessage);
            }
            if ($sErrMessage == $sErrNote) {
                $sErrNote = '';
            }

            $sErrNote .= \project\service\request::instance()->getInfoString();
            \project\service\error::instance()->logErrorMessage($sErrMessage, 'Intercepted fatal error', $sErrNote, false, true);

            $sRet = $this->showError(null, 'error_500', false);
            return $sRet ? $sRet : 'Error 500';
        }
    } // function ob_handler

    /**
     * Show error message
     * @param mixed $mErrMsg
     * @param string $sTplName
     * @param boolean $bIsEcho
     */
    public function showError($mErrMsg, $sTplName = 'error_500', $bIsEcho = true)
    {
        if(empty($mErrMsg)) {
            //ToDo: make this message by special file
            $mErrMsg = array(
                'aErrMsg' => array(
                    'Please could you send a message about this error to <a href="mailto:' . ADMIN_EMAIL . '?subject=Error%20reporting&amp;body=Fatal%20Error%20at%20the%20request%20' . urlencode('http://' . @$_SERVER['HTTP_HOST'] . @$_SERVER['REQUEST_URI']) . '">' . ADMIN_EMAIL . '</a>',
                    'We will do everything we can to get this fixed ASAP.'
                )
            );
        } elseif (!is_array($mErrMsg)) {
            $mErrMsg = array('aErrMsg' => array(strval($mErrMsg)));
        }
        if ($bIsEcho) {
            while (ob_get_status()) {
                ob_end_clean();
            }
        }

        include str_replace('{CORE_DIR}', CORE_DIR, self::MAIN_ERROR_DEMONSTRATOR);
        $sProjectPath = str_replace('{PROJECT_DIR}', PROJECT_DIR, self::PROJECT_ERROR_DEMONSTRATOR);
        if (is_file($sProjectPath)) {
            include $sProjectPath;
            $oDemonstrator = new \project\error\demonstrator($mErrMsg, $sTplName);
        } else {
            $oDemonstrator = new \core\error\demonstrator($mErrMsg, $sTplName);
        }

        if ($bIsEcho) {
            return $oDemonstrator->showTplContent();
        }
        return $oDemonstrator->getTplContent();
    } // function showError

    public function _parseException(\Exception $e, $bIsEcho)
    {
        if (method_exists($e, 'getMessageForShow')) {
            $mErrMsg = $e->getMessageForShow();
        } elseif (method_exists($e, 'getErrorMessage')) {
            $mErrMsg = $e->getErrorMessage();
        } else {
            $mErrMsg = '';
        }

        $sErrFile = method_exists($e, 'getErrorFile') ? $e->getErrorFile() : 'error_500';

        $this->showError($mErrMsg, $sErrFile, $bIsEcho);

        if ($bIsEcho) {
            exit();
        }
    } // function _parseException

} // class \core\bootstrap\runner
?>