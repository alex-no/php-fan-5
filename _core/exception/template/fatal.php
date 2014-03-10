<?php namespace fan\core\exception\template;
/**
 * Exception a template fatal error
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
class fatal extends  \fan\core\exception\base
{
    /**
     * Exception's constructor
     * @param object $oTemplate Object - instance of template
     * @param string $sLogMessage Log error message
     * @param numeric $nCode Error Code
     */
    public function __construct($oTemplate, $sLogMessage, $nCode = E_USER_ERROR)
    {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }

        parent::__construct($sLogMessage, $nCode);

        $this->_logByService($sLogMessage, 'Template\'s exception (' . get_class($oTemplate) . ').');
    }
} // class \fan\core\exception\template\fatal
?>