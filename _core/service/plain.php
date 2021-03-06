<?php namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
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
 * @version of file: 05.02.008 (15.09.2015)
 */
class plain extends \fan\core\base\service\single
{

    /**
     * Instance of matcher
     * @var \fan\core\service\matcher
     */
    protected $oMatcher = null;

    /**
     * Used Application Names
     * @var \fan\core\service\plain\base
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
        $this->oMatcher = \fan\project\service\matcher::instance();
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
        $oInstance = \fan\project\service\plain::instance();
        /* @var $oInstance \fan\core\service\plain */
        return $oInstance->_setController($sKey, $sControllerClass)->_getFinalContent($sMethod);
    }

    // ======== Main Interface methods ======== \\

    /**
     * Get Current Handle Data
     * @return array
     */
    public function getHandleData()
    {
        return $this->oMatcher->getCurrentItem()->handler->toArray();
    } // function getHandleData

    /**
     * Transfer to another URI and get content there
     * ATTENTION!!!
     *    This method do not control the same URI. So there is possible an infinite recursion.
     * @param string $sRequest
     * @param string $sHost
     * @param string $bShiftCurrent
     * @return string
     */
    public function transfer($sRequest, $sHost = null, $bShiftCurrent = true)
    {
        $this->oMatcher->setUri($sRequest, $sHost, $bShiftCurrent);
        $aHandler = $this->oMatcher->getCurrentHandler(true)->toArray();
        return call_user_func_array($aHandler['method'], empty($aHandler['param']) ? array() : $aHandler['param']);
    } // function transfer

    /**
     * Add value of Header for current stack
     * @param string $sKey
     * @param string $mValue
     * @return \fan\core\service\plain
     * @throws fatalException
     */
    public function addHeader($sKey, $mValue)
    {
        if (!array_key_exists($sKey, $this->aHeaders)) {
            throw new fatalException($this, 'Unknown header key "' . $sKey . '"');
        }
        $this->aHeaders[$sKey] = $mValue;
        return $this;
    } // function addHeader

    /**
     * Set several Headers by array-hash
     * @param $aHeaders
     * @return \fan\core\service\plain
     */
    public function setHeaders($aHeaders)
    {
        foreach ($aHeaders as $k => $v) {
            $this->addHeader($k, $v);
        }
        return $this;
    } // function setHeaders

    /**
     * Set Error Message
     * @param string $sErrMsg
     * @param numeric $nErrCode
     * @return \fan\core\service\plain
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
            $this->_defineError404();
            $mResult = $this->sErrMsg;
        }
        $this->_assignHeaders();
        return $mResult;
    } // function _getFinalContent

    /**
     * Set Engine for plain output
     * @param string $sControllerClass
     * @return \fan\core\service\plain
     * @throws fatalException
     */
    protected function _setController($sControllerKey, $sControllerClass)
    {
        if (!class_exists($sControllerClass)) {
            throw new fatalException($this, 'Can\'t find class "' . $sControllerClass . '" for plain content.');
        }
        $this->oController = new $sControllerClass($this, $sControllerKey);
        if (method_exists($this->oController, 'setConfig')) {
            $oConfig = \fan\core\service\config::instance('plain')->getControllerConfig($this->oController, $sControllerKey);
            $this->oController->setConfig($oConfig);
        }
        return $this;
    } // function _setController

    protected function _assignHeaders()
    {
        service('header')->setHeaders($this->aHeaders);
        return $this;
    } // function _assignHeaders

    /**
     * Set value of Headers by current stack
     * @return \fan\core\service\plain
     */
    protected function _defineError404()
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
    } // function _defineError404

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\

} // class \fan\core\service\plain
?>