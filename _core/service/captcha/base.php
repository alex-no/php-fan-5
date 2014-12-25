<?php namespace fan\core\service\captcha;
use fan\project\exception\service\fatal as fatalException;
/**
 * Description of captcha-engine
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
abstract class base
{
    /**
     * Service User
     * @var \fan\core\service\captcha
     */
    protected $oFacade;

    /**
     * Row of config
     * @var \fan\core\service\config\row
     */
    protected $oConfig;

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\

    /**
     * Set Facade
     * @param \fan\core\service\captcha $oFacade
     */
    public function setFacade(\fan\core\service\captcha $oFacade)
    {
        if (empty($this->oFacade)) {
            $this->oFacade = $oFacade;
        }
        return $this;
    } // function setFacade

    /**
     * Set Config
     * @param \fan\core\service\config\row $oConfig
     */
    public function setConfig(\fan\core\service\config\row $oConfig)
    {
        if (empty($this->oConfig)) {
            if (empty($oConfig)) {
                throw new fatalException($this->oFacade, 'Captcha Engine has empty config!');
            }
            $this->oConfig = $oConfig;
        }
        return $this;
    } // function setConfig

    // ======== Private/Protected methods ======== \\
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

} // class \fan\core\service\captcha\base
?>