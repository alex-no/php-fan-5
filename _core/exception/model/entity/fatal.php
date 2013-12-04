<?php namespace core\exception\model\entity;
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
class fatal extends  \core\exception\base
{
    /**
     * Exception's constructor
     * @param object $oEntity Object - instance of entity
     * @param string $sLogMessage Log error message
     * @param error $nCcode Error Code
     */
    public function __construct($oEntity, $sLogMessage, $nCode = E_USER_ERROR)
    {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }
        parent::__construct($sLogMessage, $nCode);
        $this->logByService($sLogMessage, 'Entity fatal error (' . get_class($oEntity) . ').');
    }
} // class \core\exception\model\entity\fatal
?>