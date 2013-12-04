<?php namespace core\service\form\validator;
/**
 * Uri class of validators
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
class uri extends base
{

    /**
     * Check up if a value contains e-mail address
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function isEmail($mValue, $aData)
    {
        return preg_match('/^[a-z_0-9!#*=.-]+@([a-z0-9-]+\.)+[a-z]{2,4}$/i', $mValue) > 0;
    } // function isEmail

    /**
     * Check URI
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function checkUri($mValue, $aData)
    {
        return preg_match('/^(?:(?:https?|ftp)\:\/\/)?[^\/]+\/.+/', $mValue) > 0;
    } // function checkUri

} // class \core\service\form\validator\uri
?>