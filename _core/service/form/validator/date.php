<?php namespace fan\core\service\form\validator;
/**
 * Date class of validators
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
class date extends base
{

    /**
     * Check up if a value is a date and is in given interval
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function isDate($mValue, $aData)
    {
        $mValue = str_replace(',', '.', $mValue);

        $oDate = \fan\project\service\date::instance($mValue);
        /* @var $oDate \fan\core\service\date */
        if (!$oDate->isValid()) {
            return false;
        }

        $sDate = $oDate->get('mysql');
        return (!isset($aData['min_value']) || $sDate >= $aData['min_value']) && (!isset($aData['max_value']) || $sDate <= $aData['max_value']);
    } // function isDate

} // class \fan\core\service\form\validator\date
?>