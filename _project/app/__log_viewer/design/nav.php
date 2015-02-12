<?php namespace fan\app\__log_viewer\design;
/**
 * main_nav block
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
 * @version of file: 05.02.005 (12.02.2015)
 */
class nav extends \fan\project\block\base
{
    /**
     * Get Nav Url
     * @param string $sKey
     * @param string $sAddUrl
     * @return string
     */
    public function getNavUrl($sKey, $sAddUrl = '')
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
        foreach ($aTpl['aNav'] as $v) {
            $aList[] = $v['key'];
        }
        return $aList;
    } // function getVarieties

} // class \fan\app\__log_viewer\design\nav
?>