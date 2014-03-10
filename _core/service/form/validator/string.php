<?php namespace fan\core\service\form\validator;
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
 * @version of file: 05.02.001 (10.03.2014)
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
        $nLength = isset($aData['is_mb']) && empty($aData['is_mb']) ? strlen($mValue) : mb_strlen($mValue);
        if (isset($aData['min_length']) && $nLength < $aData['min_length']) {
            return false;
        }
        if (isset($aData['max_length']) && $nLength > $aData['max_length']) {
            return false;
        }
        return true;
    } // function strlen

    /**
     * Check coding of string is UTF-8
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    protected function isUtf8($mValue, $aData)
    {
        if (!mb_check_encoding($mValue, 'UTF-8')) {
            return false;
        }
        if (isset($aData['max_length']) || isset($aData['min_length'])) {
            $aData['is_mb'] = true;
            return $this->strlen($mValue, $aData);
        }
        return true;
    } // function isUtf8

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

} // class \fan\core\service\form\validator\string
?>