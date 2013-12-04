<?php namespace core\exception\block;
/**
 * Exception a block fatal error
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
class fatal extends local
{
    /**
     * Exception's constructor
     * @param \core\block\base $oBlock Object - instance of block
     * @param string $sLogMessage Log error message
     * @param error $nCcode Error Code
     */
    public function __construct($oBlock, $sLogMessage, $nCode = E_USER_ERROR)
    {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }
        $this->logByService($sLogMessage, 'Block\'s exception (CLASS: ' . get_class($oBlock) . ').');
        parent::__construct($oBlock, $sLogMessage, $nCode);
    } // function __construct
} // class \core\exception\block\fatal
?>