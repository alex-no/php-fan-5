<?php namespace fan\core\plain;
//use fan\project\exception\plain\fatal as fatalException;
/**
 * Base access for plain files (uploaded to the server) class
 *
 * This file is part PHP-FAN (php-framework of Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.005 (12.02.2015)
 */

class captcha
{
    /**
     * Handler object
     * @var \fan\core\service\plain
     */
    protected $oHandler;

    /**
     * Plain config object
     * @var \fan\core\service\config\row
     */
    protected $oConfig;

    /**
     * Plain config object
     * @var \fan\core\service\captcha
     */
    protected $oCaptcha;

    /**
     * Key of plain controller
     * @var string
     */
    protected $sKey;

    /**
     * @var
     */
    protected $sApp = null;

    /**
     * Constructor of Plain controller captcha
     * @param boolean $bAllowIni
     */
    public function __construct(\fan\core\service\plain $oHandler, $sKey)
    {
        $this->oHandler = $oHandler;
        $this->sKey     = $sKey;

        $aHandle = $oHandler->getHandleData();
        $this->oCaptcha = service('captcha', $aHandle['mReqKey']);
    } // function __construct

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    /**
     * Get Captcha
     * @return array|string
     */
    public function getCaptcha()
    {
        //return 'getCaptcha-' . $this->oCaptcha->getText() . '!!!!';
        //return $this->_prepare()->_init()->_getContent();
        return $this->_init()->_getContent();
    } // getCaptcha

    /**
     * Get Key
     * @return string
     */
    public function getKey()
    {
        return $this->sKey;
    } // getKey

    // ======== Private/Protected methods ======== \\

    /**
     * Init data
     * @return \fan\core\plain\captcha
     */
    protected function _init()
    {
        //$this->oHandler->setErrorMessage(msg('ERROR_REQUESTED_FILE_IS_NOT_FOUND'));
        $aHeaders = $this->oCaptcha->getHeaders();
        foreach ($aHeaders as $k => $v) {
            $this->oHandler->addHeader($k, $v);
        }
        return $this;
    } // function _init

    /**
     * Get content OR content outputer
     * @return array|string
     */
    protected function _getContent()
    {
        return $this->oCaptcha->getBinaryData();
    } // function _getContent

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

    /**
     * Method required for interface of plain controller
     * @param \fan\core\service\config\row $oConfig
     * @return \fan\core\plain\captcha
     */
    public function setConfig(\fan\core\service\config\row $oConfig)
    {
        if (empty($this->oConfig)) {
            $this->oConfig = $oConfig;
        }
        return $this;
    } // setConfig

} // class \fan\core\plain\captcha
?>