<?php namespace core\exception;
/**
 * Exception a fatal error
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
class fatal extends base
{
    /**
     * Exception's constructor
     * @param string $sLogMessage Log error message
     * @param string $sUserMessage User error message if file doesn't exist
     * @param string $sErrorFile File path for output User error message
     * @param error $nCcode Error Code
     */
    public function __construct($sLogMessage, $sUserMessage = '', $sErrorFile = '', $nCode = E_USER_ERROR)
    {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }
        if($sErrorFile) {
            $this->sErrorFile = $sErrorFile;
        }

        parent::__construct($sUserMessage, $nCode);

        $this->logByPhp('Fatal error (http://' . @$_SERVER['HTTP_HOST'] . @$_SERVER['REQUEST_URI'] . '). ' . $sLogMessage);
    }
} // class \core\exception\fatal
?>