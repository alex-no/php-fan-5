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
 * @version of file: 05.007 (23.02.2014)
 */
class uri extends base
{

    /**
     * Check up a value is e-mail address
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function isEmail($mValue, $aData)
    {
        return filter_var($mValue, FILTER_VALIDATE_EMAIL) !== false;
    } // function isEmail

    /**
     * Check URI
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function isUri($mValue, $aData)
    {
        if ($aData['is_path']) {
            $mResult = filter_var($mValue, FILTER_FLAG_PATH_REQUIRED);
        } elseif ($aData['is_query']) {
            $mResult = filter_var($mValue, FILTER_FLAG_QUERY_REQUIRED);
        } else {
            $mResult = filter_var($mValue, FILTER_VALIDATE_URL);
        }
        return $mResult !== false;
    } // function isUri

} // class \core\service\form\validator\uri
?>