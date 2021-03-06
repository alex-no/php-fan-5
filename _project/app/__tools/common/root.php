<?php namespace fan\app\__tools\common;
/**
 * Viewer root block
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
 * @version of file: 05.02.001 (10.03.2014)
 */
class root extends \fan\project\block\root\html
{

    /**
     * Set tab title
     * @param string $sTitle - new title
     * @param boolean $bCheckIsSet - Check - if set - do not change
     */
    public function setTitle($sTitle, $bCheckIsSet = false)
    {
        parent::setTitle(service('application')->getProjectName() . ' - ' . $sTitle, $bCheckIsSet);
    } // function setTitle


} // class \fan\app\__tools\common\root
?>