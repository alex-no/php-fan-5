<?php namespace core\service\form\validator;
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
 * @version of file: 05.001 (29.09.2011)
 */
abstract class base
{
    /**
     * Form config
     * @var \core\service\form
     */
    protected $oFacade;

    /**
     * Form config
     * @var \core\service\config\row
     */
    protected $oConfig;

    /**
     * Set Facade
     * @param \core\service\form $oFacade
     */
    public function setFacade(\core\service\form $oFacade)
    {
        if (empty($this->oFacade)) {
            $this->oFacade = $oFacade;
            $this->oConfig = $oFacade->getConfig();
        }
        return $this;
    } // function setFacade
} // class \core\service\form\validator\base
?>