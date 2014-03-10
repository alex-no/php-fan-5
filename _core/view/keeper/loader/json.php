<?php namespace fan\core\view\keeper\loader;
/**
 * View-data keeper of Block data for loader JSON-data
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
class json extends \fan\core\view\keeper
{
    /**
     * View meta constructor
     * @param fan\core\block\base $oRouter
     */
    public function __construct(\fan\core\view\router $oRouter)
    {
        parent::__construct($oRouter);
        $this->bFullRewrite = true;
    } // function __construct

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\
    /**
     * Add Router
     * @param \fan\core\view\router\loader $oRouter
     * @return \fan\core\view\keeper\loader\text
     */
    public function addRouter(\fan\core\view\router\loader $oRouter)
    {
        $this->_setSetter($oRouter);
        $this->_setSetter($oRouter->getBlock());
    } // function addRouter

    // ======== Private/Protected methods ======== \\

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\

} // class \fan\core\view\keeper\loader\json
?>