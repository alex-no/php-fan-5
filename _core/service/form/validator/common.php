<?php namespace fan\core\service\form\validator;
/**
 * Common class of validators
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
class common extends base
{

    /**
     * Check up if a value is not empty
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function isRequired($mValue, $aData)
    {
        return $mValue != '';
    } // function isRequired

} // class \fan\core\service\form\validator\common
?>