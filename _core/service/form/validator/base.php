<?php namespace fan\core\service\form\validator;
/**
 * Base class of validators
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
abstract class base
{
    /**
     * Form config
     * @var \fan\core\service\form
     */
    protected $oFacade;

    /**
     * Form config
     * @var \fan\core\service\config\row
     */
    protected $oConfig;

    /**
     * Set Facade
     * @param \fan\core\service\form $oFacade
     */
    public function setFacade(\fan\core\service\form $oFacade)
    {
        if (empty($this->oFacade)) {
            $this->oFacade = $oFacade;
            $this->oConfig = $oFacade->getConfig();
        }
        return $this;
    } // function setFacade
} // class \fan\core\service\form\validator\base
?>