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
abstract class delegate extends engine
{
    /**
     * Facade of service
     * @var \fan\core\service\config\base
     */
    protected $oConfig = null;

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    /**
     * Set Facade
     * @param \fan\core\base\service $oFacade
     */
    public function setFacade(\fan\core\base\service $oFacade)
    {
        parent::setFacade($oFacade);
        $this->oConfig = $oFacade->getConfig();
        return $this;
    } // function setFacade

    /**
     * Get service's Config
     * @param string $mKey Config key
     * @return mixed
     */
    public function getConfig($mKey = null, $mDefault = null)
    {
        return is_null($mKey) || !is_object($this->oConfig) ? $this->oConfig : $this->oConfig->get($mKey, $mDefault);
    } // function getConfig

    // ======== Private/Protected methods ======== \\

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
} // class \fan\core\service\tab\delegate
?>