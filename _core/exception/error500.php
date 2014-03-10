<?php namespace fan\core\exception;
/**
 * Exception an error 500
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
class error500 extends base
{
    /**
     * Exception's constructor
     * @param string $sLogErrMsg Error message
     * @param numeric $nCode Error Code
     * @param \Exception $oPrevious Previous Exception
     */
    public function __construct($sLogErrMsg, $nCode = E_USER_ERROR, $oPrevious = null)
    {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }

        parent::__construct($sLogErrMsg, $nCode, $oPrevious);

        $this->_logByService($sLogErrMsg, 'Error 500');
    }
} // class \fan\core\exception\error500
?>