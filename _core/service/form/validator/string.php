<?php namespace core\service\form\validator;
/**
 * String class of validators
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
class string extends base
{

    /**
     * Check length of string
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function strlen($mValue, $aData)
    {
        $nLength = @$aData['is_mb'] ? mb_strlen($mValue) : strlen($mValue);
        if (isset($aData['min_value']) && $nLength < $aData['min_value']) {
            return false;
        }
        if (isset($aData['max_value']) && $nLength > $aData['max_value']) {
            return false;
        }
        return true;
    } // function strlen

    /**
     * Check up if a value consists of letters, numbers, _, @, ., - and begging from letter or number
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function isAlphalogin($mValue, $aData)
    {
        return preg_match('/^[a-z0-9][a-z0-9_@\.-]*$/i', $mValue) > 0;
    } // function isAlphalogin

    /**
     * Check up if a value consists of letters, numbers, _, - and begging from letter or number
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function isAlphanumeric($mValue, $aData)
    {
        return preg_match('/^[a-z0-9][a-z0-9_-]*$/i', $mValue) > 0;
    } // function isAlphanumeric

    /**
     * Check up if a value matchs with the regular expression
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function matchRegexp($mValue, $aData)
    {
        return preg_match($aData['regexp'], $mValue) > 0;
    } // function matchRegexp

} // class \core\service\form\validator\string
?>