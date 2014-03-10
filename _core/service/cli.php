<?php namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * Class of cli handler
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
class cli extends \fan\core\base\service\single
{
    /**
     * Service tab constructor
     * @param boolean $bAllowIni
     */
    protected function __construct($bAllowIni = true)
    {
        parent::__construct($bAllowIni);
        $this->oMatcher = \fan\project\service\matcher::instance();
    } // function __construct

    // ======== Static methods ======== \\

    /**
     * Get Final Content
     * @return string|array
     */
    public static function getContent($sControllerClass, $sMethod)
    {
        $oInstance = \fan\project\service\cli::instance();
        return $oInstance->_setController($sControllerClass)->_getFinalContent($sMethod);
    }

    // ======== Main Interface methods ======== \\

    // ======== Private/Protected methods ======== \\

    /**
     * Get Final Content
     * @return string|array
     * @throws fatalException
     */
    protected function _getFinalContent($sMethod)
    {
        if (empty($this->oController)) {
            throw new fatalException($this, 'Engine for CLI content isn\'t set.');
        }
        if (!method_exists($this->oController, $sMethod)) {
            throw new fatalException($this, 'Engine of CLI content don\'t have method "' . $sMethod . '".');
        }
        $mResult = $this->oController->$sMethod();
        return $mResult;
    } // function _getFinalContent

    /**
     * Set Engine for plain output
     * @param string $sController
     * @return \fan\core\service\plain
     * @throws fatalException
     */
    protected function _setController($sController)
    {
        $sController = ltrim($sController, '\\');
        $sController = (substr($sController, 0, 12) == 'fan\project\\' ? '\\' : '\fan\project\cli\\') . $sController;
        if (!class_exists($sController)) {
            throw new fatalException($this, 'Can\'t find class "' . $sController . '" for CLI content.');
        }
        $this->oController = new $sController($this);
        if (method_exists($this->oController, 'setConfig')) {
            $oConfig = \fan\core\service\config::instance('cli')->getControllerConfig($this->oController);
            $this->oController->setConfig($oConfig);
        }
        return $this;
    } // function _setController

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\

} // class \fan\core\service\cli
?>