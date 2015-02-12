<?php namespace fan\core\block\common;
/**
 * Base class for all kind of dynamic menu, wich formed by data base
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
 * @version of file: 02.014
 * @abstract
 */
abstract class html_nav_db extends html_nav
{

    /**
     * @var array menu entity objects
     */
    private $aNavElements = array();

    /**
     * @var array menu entity objects
     */
    protected $aSrcElements = array();

    /**
     * Get menu from data base
     * @param string $sGroupKey - group key of meta file
     * @return array
     */
    protected function _getNav($sGroupKey = null)
    {
        /*
        $oRowset = ge('menu_element')->getRowsetByKey('get_menu_list', array(
            'group_key'        => $sGroupKey ? $sGroupKey : $this->getMeta('group_key'),
            'id_site_language' => service('language')->getIdSiteLanguage(),
        ));
         */
        $oRowset = service('entity')->getMenuElement($sGroupKey);

        $aRet  = array();
        $aChld = array();

        foreach ($oRowset as $e) {
            $id = $e->getId();
            $this->aNavElements[$id] = $e;
            if (role($e->get___url_role())) {
                $v = $e->getFields();
                $this->aSrcElements[$id] = array(
                    'order_key'     => $v['order_key'],
                    'condition_key' => $v['condition_key'],
                    'target'        => $v['target'] == 'self' ? null : '_' . $v['target'],
                    'url_value'     => $this->getMenuURL($v['menu_type'] == 'local' ? $v['__url_value'] : $v['__foreign_url'], $v['menu_type'], $v['__protocol']),
                    'menu_name'     => $v['__menu_name'],
                    'current'       => $this->checkCurrentElement($v['menu_key']),
                    'children'      => array(),
                );
                if ($v['id_menu_element_parent'] && $v['id_menu_element_parent'] != $v['id_menu_element']) {
                    $aChld[$id] =& $this->aSrcElements[$id];
                    $aChld[$id]['parent'] = $v['id_menu_element_parent'];
                } else {
                    $aRet[$id] =& $this->aSrcElements[$id];
                }
            }
        }

        foreach ($aChld as $id => &$v) {
            $this->aSrcElements[$v['parent']]['children'][] =& $v;
        }
        return $aRet;
    } // function _getNav

    /**
     * Get navigation name
     * @param string $sKey - group key of meta file
     * @return bolean
     */
    protected function _getNavName($sKey = 'group_key')
    {
        /*
        $oMenuGroup = se('entity_menu_group')->loadByParam(array('group_key' => $this->getMeta($sKey)));
         */
        $oMenuGroup = service('entity')->getMenuGroup($sKey);
        if ($oMenuGroup->checkIsLoad()) {
            return $oMenuGroup->group_name;
        }
        return false;
    } // function _getNavName

    /**
     * Get nav row
     * @param number $nId menu_element id
     * @return menu_element
     */
    protected function _getNavRow($nId)
    {
        return isset($this->aNavElements[$nId]) ? $this->aNavElements[$nId] : null;
    } // function _getNavRow

} // class \fan\core\block\common\html_nav_db
?>