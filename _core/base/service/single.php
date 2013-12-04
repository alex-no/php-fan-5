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
abstract class single extends \core\base\service
{
    /**
     * @var array service's Instances
     */
    private static $aInstances;

    // ======== Static methods ======== \\

    /**
     * Get service's instance by class name
     * @return object Aservice Service's instance
     */
    public static function instance()
    {
        $sName = self::checkName(get_called_class());
        if (!isset(self::$aInstances[$sName])) {
            $oInstance = new $sName();
            if (!isset(self::$aInstances[$sName])) {
                self::$aInstances[$sName] = $oInstance;
            }
        }
        return self::$aInstances[$sName];
    } // function instance

    // ======== Main Interface methods ======== \\

    /**
     * Is singleton
     * @return boolean
     */
    final public function isSingleton()
    {
        return true;
    } // function isSingleton

    // ======== Private/Protected methods ======== \\

    /**
     * Save service's Instance
     */
    protected function _saveInstance()
    {
        $sClassName = self::checkName(get_class($this));
        if(isset(self::$aInstances[$sClassName])) {
            throw new \project\exception\service\fatal($this, 'Dublicate of service init "' . $sClassName . '"');
        }
        self::$aInstances[$sClassName] = $this;
        return $this;
    } // function _saveInstance

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

} // class \core\base\service\single
?>