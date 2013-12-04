<?php namespace core\service;
use project\exception\service\fatal as fatalException;
/**
 * Class of plain handler
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
class plain extends \core\base\service\single
{

    /**
     * Instance of matcher
     * @var \core\service\matcher
     */
    protected $oMatcher = null;

    /**
     * Used Application Names
     * @var \core\service\plain\base
     */
    protected $oController = null;

    /**
     * @var numeric - error code
     */
    protected $nErrCode = null;

    /**
     * @var string - error message
     */
    protected $sErrMsg = null;

    /**
     * Lict of headers
     * @var array
     */
    protected $aHeaders = array(
        'response'    => 200,
        'contentType' => null,
        'encoding'    => null,
        'filename'    => null,
        'disposition' => true,
        'length'      => null,
        'legthRange'  => 'bytes',
        'modified'    => null,
        'cacheLimit'  => 0,
    );

    /**
     * Service plain constructor
     * @param boolean $bAllowIni
     */
    protected function __construct($bAllowIni = true)
    {
        parent::__construct($bAllowIni);
        $this->oMatcher = \project\service\matcher::instance();
        //$oTmp = $this->oMatcher->getCurrentItem()->handler->toArray();
    } // function __construct

    // ======== Static methods ======== \\

    /**
     * Get Plain Content
     * @param string $sControllerClass
     * @param string $sMethod
     * @return string|array
     */
    public static function getContent($sKey, $sControllerClass, $sMethod)
    {
        $oInstance = \project\service\plain::instance();
        return $oInstance->_setController($sKey, $sControllerClass)->_getFinalContent($sMethod);
    }

    // ======== Main Interface methods ======== \\

    /**
     * Set value of Header for current stack
     * @param string $sKey
     * @param string $mValue
     * @return \core\service\plain
     * @throws fatalException
     */
    public function setHeader($sKey, $mValue)
    {
        if (!array_key_exists($sKey, $this->aHeaders)) {
            throw new fatalException($this, 'Unknown header key "' . $sKey . '"');
        }
        $this->aHeaders[$sKey] = $mValue;
        return $this;
    } // function setHeader

    /**
     * Set Error Message
     * @param string $sErrMsg
     * @param numeric $nErrCode
     * @return \core\service\plain
     * @throws fatalException
     */
    public function setErrorMessage($sErrMsg, $nErrCode = 404)
    {
        if (is_numeric($nErrCode) && $nErrCode >= 400 && $nErrCode <= 599) {
            $this->nErrCode = $nErrCode;
        } else {
            throw new fatalException($this, 'Error code has incorrect value "' . $nErrCode . '". It must be number between 400 and 599');
        }

        if (!empty($sErrMsg)) {
            $this->sErrMsg = $sErrMsg;
        }

        return $this;
    } // function setErrorMessage

    /**
     * Is Error
     * @return boolean
     */
    public function isError()
    {
        return !empty($this->sErrMsg);
    } // function isError

    // ======== Private/Protected methods ======== \\

    /**
     * Get Final Content
     * @return string|array
     * @throws fatalException
     */
    protected function _getFinalContent($sMethod)
    {
        if (empty($this->oController)) {
            throw new fatalException($this, 'Engine for plain content isn\'t set.');
        }
        $mResult = $this->oController->$sMethod();
        if ($this->isError()) {
            $this->_defineErrorHeaders();
            $mResult = $this->sErrMsg;
        }
        $this->_assignHeaders();
        return $mResult;
    } // function _getFinalContent

    /**
     * Set Engine for plain output
     * @param string $sControllerClass
     * @return \core\service\plain
     * @throws fatalException
     */
    protected function _setController($sControllerKey, $sControllerClass)
    {
        if (!class_exists($sControllerClass)) {
            throw new fatalException($this, 'Can\'t find class "' . $sControllerClass . '" for plain content.');
        }
        $this->oController = new $sControllerClass($this, $sControllerKey);
        if (method_exists($this->oController, 'setConfig')) {
            $oConfig = \core\service\config::instance('plain')->getControllerConfig($this->oController, $sControllerKey);
            $this->oController->setConfig($oConfig);
        }
        return $this;
    } // function _setController

    protected function _assignHeaders()
    {
        \project\service\header::instance()->setHeaders($this->aHeaders);
        return $this;
    } // function _assignHeaders

    /**
     * Set value of Headers by current stack
     * @return \core\service\plain
     */
    protected function _defineErrorHeaders()
    {
        $this->aHeaders = array(
            'response'    => 404,
            'contentType' => 'text/plain',
            'encoding'    => 'charset=utf-8',
            'filename'    => 'Error 404',
            'disposition' => true,
            'length'      => strlen($this->sErrMsg),
            'legthRange'  => 'bytes',
            'modified'    => null,
            'cacheLimit'  => 0,
        );
        return $this;
    } // function _defineErrorHeaders

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\

} // class \core\service\plain
?>