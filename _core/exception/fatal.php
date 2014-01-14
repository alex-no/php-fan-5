<?php namespace core\exception;
/**
 * Exception a fatal error. This exception must be caught in bootstrap
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
 * @version of file: 05.005 (14.01.2014)
 */
class fatal extends base
{
    /**
     * Exception's constructor
     * @param string $sLogErrMsg Log error message
     * @param string $sShowErrMsg User error message if file doesn't exist
     * @param string $sErrorFile File path for output User error message
     * @param numeric $nCode Error Code
     * @param \Exception $oPrevious Previous Exception
     */
    public function __construct($sLogErrMsg, $sShowErrMsg = '', $sErrorFile = '', $nCode = E_USER_ERROR, $oPrevious = null)
    {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }

        $this->sShowErrMsg = $sShowErrMsg;
        if($sErrorFile) {
            $this->sShowErrFile = $sErrorFile;
        }

        parent::__construct($sShowErrMsg, $nCode, $oPrevious);

        $this->_logByPhp('Fatal error (http://' . @$_SERVER['HTTP_HOST'] . @$_SERVER['REQUEST_URI'] . '). ' . $sLogErrMsg);
    }
} // class \core\exception\fatal
?>