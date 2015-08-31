<?php namespace fan\project\block\error;
/**
 * Block for show error 500
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
 * @version of file: 05.02.007 (31.08.2015)
 */
class error500 extends \fan\core\block\error\error500
{
    /**
     * Init block
     */
    public function init()
    {
        $this->view->sHomeUri = $this->oTab->getURI('~/');
    } // function init
} // class \fan\project\block\error\error500
?>