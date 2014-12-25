<?php namespace fan\core\service\form\validator;
/**
 * Phone class of validators
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class phone extends base
{
    /**
     * Rule phone is correct
     * @return bool
     */
    public function isUkrainian($mValue)
    {
        $sPhone = preg_replace('/\D+/', '', $mValue);
        if (strlen($sPhone) == 9) {
            $sPhone = '380' . $sPhone;
        } elseif (strlen($sPhone) == 10) {
            $sPhone = '38' . $sPhone;
        } elseif (strlen($sPhone) != 12) {
            return false;
        }
        if (preg_match('/^380\d{9}$/', $sPhone)) {
            $sPhone = '+' . $sPhone;
            return true;
        }
       return false;
    } // function phone_is_correct




} // class \fan\core\service\form\validator\phone
?>