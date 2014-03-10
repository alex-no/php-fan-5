<?php namespace fan\app\__log_viewer\design;
/**
 * main_menu block
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
class menu extends \fan\project\block\base
{
    /**
     * Get Menu Url
     * @param string $sKey
     * @param string $sAddUrl
     * @return string
     */
    public function getMenuUrl($sKey, $sAddUrl = '')
    {
        return $this->oTab->getURI('/' . $sKey . $sAddUrl . '.html', 'link', null, null);
    }

    /**
     * Get Variety List
     * @return array
     */
    public function getVarieties()
    {
        $aList = array();
        $aTpl = $this->getMeta('tplVars');
        foreach ($aTpl['aMenu'] as $v) {
            $aList[] = $v['key'];
        }
        return $aList;
    } // function getVarieties

} // class \fan\app\__log_viewer\design\menu
?>