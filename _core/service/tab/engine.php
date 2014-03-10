<?php namespace fan\core\service\tab;

/**
 * Description of delegate
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
abstract class engine
{
    /**
     * Facade of service
     * @var fan\core\base\service
     */
    protected $oFacade = null;

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    /**
     * Set Facade
     * @param \fan\core\base\service $oFacade
     */
    public function setFacade(\fan\core\base\service $oFacade)
    {
        if (empty($this->oFacade)) {
            $this->oFacade = $oFacade;
        }
        return $this;
    } // function setFacade

    // ======== Private/Protected methods ======== \\
    /**
     * Make Exception
     * @param type $sMessage
     * @throws \fan\project\exception\service\fatal
     */
    protected function _makeException($sMessage)
    {
        throw new \fan\project\exception\service\fatal($this->oFacade, $sMessage);
    } // function _makeException

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
} // class \fan\core\service\tab\engine
?>