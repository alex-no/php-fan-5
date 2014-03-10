<?php namespace fan\core\exception\plain;
/**
 * Exception a plain controller fatal error
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
class fatal extends \fan\core\exception\base
{

    /**
     * @var object Instance of class maked exception
     */
    protected $oController = null;

    /**
     * Exception's constructor
     * @param object $oController Object - instance of service
     * @param string $sLogMessage Log error message
     * @param numeric $nCode Error Code
     */
    public function __construct($oController, $sLogMessage, $nCode = E_USER_ERROR, $oPrevious = null)
    {
        /*
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }
         */
        $this->oController = $oController;

        parent::__construct($sLogMessage, $nCode, $nCode, $oPrevious);

        $this->_logByService('Plain controller fatal error (' . get_class($oController) . '). ' . $sLogMessage);
    }

    /**
     * Get Instance of service
     * @return \fan\core\service\base
     */
    public function getController()
    {
        return $this->oController;
    }

} // class \fan\core\exception\plain\fatal
?>