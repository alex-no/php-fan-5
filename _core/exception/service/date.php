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
 * @version of file: 05.02.009 (23.09.2015)
 */
class date extends \fan\core\exception\base
{

    /**
     * Exception's constructor
     * @param string $sLogErrMsg Log error message
     * @param numeric $nCode Error Code
     * @param \Exception $oPrevious Previous exception
     */
    public function __construct($sLogErrMsg, $nCode = E_USER_ERROR, $oPrevious = null)
    {
        parent::__construct($sLogErrMsg, $nCode, $oPrevious);
        $this->_logByPhp($sLogErrMsg);
    } // function __construct
} // class \fan\core\exception\service\date
?>