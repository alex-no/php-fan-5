<?php namespace core\base\service;
/**
 * Base abstract service
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
 * @abstract
 */
abstract class multi extends \core\base\service
{
    // ======== Static methods ======== \\

    /**
     * Reset flag of enabled for all instances
     * Redefine this function in children and set argument there - list os instances
     */
    public static function resetEnabledAll()
    {
        foreach (func_get_arg(0) as $v) {
            $v->resetEnabled();
        }
    } // function resetEnabledAll

    // ======== Main Interface methods ======== \\

    /**
     * Is singleton
     * @return boolean
     */
    final public function isSingleton()
    {
        return false;
    } // function isSingleton

    // ======== Private/Protected methods ======== \\
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

} // class core\base\service\multi
?>