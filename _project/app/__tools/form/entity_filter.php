<?php namespace fan\app\__tools\form;
/**
 * entity_filter block
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
class entity_filter extends \fan\project\block\form\filter
{
    /**
     * Init block
     */
    public function init()
    {
        $this->_parseForm(true, true);
    } // function init

    /**
     * Get List of databases
     * @return array
     */
    public function getDbList()
    {
        $aDB   = array();
        $aConf = service('database')->getConfig();
        foreach ($aConf['DATABASE'] as $k => $v) {
            $aDB[] = array(
                'text'  => $v['DATABASE'],
                'value' => $k,
            );
        }
        return $aDB;
    } // function getDbList

    /**
     * Get List of model-directories
     * @return array
     */
    public function getDirList()
    {
        $sPrefix = rtrim(service('entity')->getNsPrefix(), '\\');
        $sText   = str_replace('\\', '/', $sPrefix);
        $aDir = array(array(
            'text'  => $sText,
            'value' => $sPrefix,
        ));
        $sSep  = \fan\core\bootstrap\loader::DEFAULT_DIR_SEPARATOR;
        $sPath = \bootstrap::getLoader()->getPathByNS($sPrefix);
        foreach (scandir($sPath) as $v) {
            if (
                    $v != '.' &&
                    $v != '..' &&
                    is_dir($sPath . $sSep . $v) &&
                    !is_file($sPath . $sSep . $v . $sSep . 'entity.php')
            ) {
                $aDir[] = array(
                    'text'  => $sText . '/' . $v,
                    'value' => $sPrefix . '\\' . $v,
                );
            }
        }
        return $aDir;
    } // function getDirList
} // class \fan\app\__tools\form\entity_filter
?>