<?php namespace app\__tools\main;
/**
 * index block
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
 * @version of file: 05.005 (14.01.2014)
 */
class index extends \project\block\common\simple
{
    /**
     * Init block
     */
    public function init()
    {
        if (!role('tools_access')) {
            //$oUser = service('user', array('anonymous', 'tools_by_config'));
            $oUser = getUser('anonymous', 'tools_by_config');
            $oUser->setCurrent();
            if (!role('tools_access')) {
                transfer_int('~/request_password.html');
            } else {
                transfer_sham($this->oTab->getCurrentURI(false, true, true, true));
            }
        }
    } // init

} // class \app\__tools\main\index
?>