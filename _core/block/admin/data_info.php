<?php namespace fan\core\block\admin;
/**
 * Admin info data class for loader block
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
class data_info extends data
{

    /**
     * Get Main Data
     * @param array $aData
     * @param array $aForce
     * @return boolean
     */
    protected function getMainData($aData, $aForce = array())
    {
        if (!isset($aForce['template'])) {
            $aForce['template'] = 1;
        }
        return parent::getMainData($aData, $aForce);
    } // function getMainData

    /**
     * Get Content ExtraData
     */
    public function getExtraData()
    {
        $aRet = parent::getExtraData();
        $sPS = $this->getMeta('parsingScript');
        if ($sPS) {
            $aRet['parsingScript'] = $sPS;
        }
        return $aRet;
    } // function getExtraData
} // class \fan\core\block\admin\data_info
?>