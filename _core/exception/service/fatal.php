<?php namespace fan\core\exception\service;
/**
 * Exception a service fatal error
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
 * @version of file: 05.02.003 (16.04.2014)
 */
class fatal extends \fan\core\exception\base
{

    /**
     * @var \fan\core\base\service Instance of class maked exception
     */
    protected $oService = null;

    /**
     * Exception's constructor
     * @param \fan\core\base\service $oService Object - instance of service
     * @param string $sLogErrMsg Log error message
     * @param numeric $nCode Error Code
     */
    public function __construct(\fan\core\base\service $oService, $sLogErrMsg, $nCode = E_USER_ERROR, $oPrevious = null)
    {
        $this->oService = $oService;

        parent::__construct($sLogErrMsg, $nCode, $oPrevious);

        $this->_logErrorMessage($oService->getExceptionLogType());
    }

    /**
     * Get Instance of service
     * @return \fan\core\base\service
     */
    public function getService()
    {
        return $this->oService;
    } // function getService

    /**
     * Remove property "oBlock" before "print_r" this object
     */
    public function clearProperty()
    {
        $this->_removeEmbededObject('oService');
    } // function clearProperty

    /**
     * Get Instance of service
     * @param string $sLogType
     * @return \fan\core\base\service
     */
    protected function _logErrorMessage($sLogType)
    {
        if (in_array($sLogType, array('php', 'service'))) {
            $sLogMethod = $sLogType == 'php' ? '_logByPhp' : '_logByService';
            $this->$sLogMethod('Service fatal error (' . get_class($this->oService) . '). ' . $this->sLogErrMsg);
        }
        return $this;
    }

    /**
     * Get operation for Db (rollback, commit or nothing) when exception occured
     * @param string $sDbOper
     * @return null|string
     */
    protected function _defineDbOper($sDbOper = null)
    {
        if (empty($sDbOper) && method_exists($this->oService, 'getExceptionDbOper')) {
            $sDbOper = $this->oService->getExceptionDbOper();
        }
        return parent::_defineDbOper($sDbOper);
    } // function _defineDbOper

} // class \fan\core\exception\service\fatal
?>