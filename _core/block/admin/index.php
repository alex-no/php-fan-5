<?php namespace core\block\admin;
/**
 * Class admin index block
 *
 * This file is part PHP-FAN (php-framework of Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 02.005
 */
class index extends \core\block\common\simple
{

    /**
     * Init block data
     */
    public function init()
    {
        $this->initRequired();
    }

    /**
     * Init block data
     */
    public function initRequired()
    {
        $this->oTab->disableCache();
    }
} // class \core\block\admin\index
?>