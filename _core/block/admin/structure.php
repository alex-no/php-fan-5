<?php namespace core\block\admin;
/**
 * Admin structure class for loader block
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
 * @version of file: 05.005 (14.01.2014)
 */
class structure extends base
{
    /**
     * Init output block data
     */
    public function init()
    {
        service('role')->setSessionRoles('admin', $this->getMeta('login_timeout'));

        $aData = $this->getData();
        $aJson = array();

        // Prepare template
        $this->initTplVar();

        $sHtml = $this->getTemplateCode();
        if (!empty($sHtml)) {
            $aJson['condition']['code'] = $sHtml;
        }
        // Prepare param
        $aAddParam = $this->getAddParam();
        if ($aAddParam) {
            $aJson['condition']['param'] = $aAddParam;
        }
        // Prepare Extra data
        $aExtra = $this->getExtraData();
        if ($aExtra) {
            $aJson['condition']['extra'] = $aExtra;
        }
        // Prepare condition Data
        $aCondition = $this->getCondition();
        if ($aCondition) {
            $aJson['condition']['cond'] = $aCondition;
        }

        if ($aJson) {
            $this->setJson($aJson);
        }

        $this->setText('ok');
    }

    /**
     * Init Template Vars
     */
    public function initTplVar()
    {
    } // function initTplVar

    /**
     * Get Condition Parameters
     */
    public function getAddParam()
    {
        return $this->getMeta('addParam', array());
    } // function getAddParam

    /**
     * Get Condition ExtraData
     */
    public function getExtraData()
    {
        return $this->getMeta('extra', array());
    } // function getExtraData

    /**
     * Get Condition Data
     */
    public function getCondition()
    {
        return $this->getMeta('cond', array());
    } // function getCondition
} // class \core\block\admin\structure
?>