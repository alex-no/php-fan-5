<?php namespace fan\core\block\common;
/**
 * Base class for all kind of meta nav
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
 * @version of file: 02.025
 * @abstract
 */
abstract class html_nav extends \fan\core\block\base
{
    /**
     * Current Request
     * @var array
     */
    protected $aCurrentRequest = array();

    /**
     * Init block
     */
    public function init()
    {
        $this->view->aNavList = $this->_getNav();
    } // function init

    /**
     * Get nav from data base
     * @param string $sKey - group key of meta file
     * @return object array
     */
    protected function _getNav($sKey = 'nav')
    {
        return $this->_parseNav($this->getMeta($sKey, array(), true));
    } // function _getNav

    /**
     * Parse Nav (recursive)
     * @param array $aNav
     * @return array
     */
    protected function _parseNav($aNav)
    {
        $aResult = array();
        foreach ($aNav as $k => $v){
            if (!isset($v['role']) || role($v['role'])) {
                $aResult[$k] = array(
                    'nav_name'  => array_val($v, 'nav_name', '&nbsp;'),
                    'url_value' => $this->_getNavURI($v['url_value'], array_val($v, 'url_type', 'local'), array_val($v, 'protocol', '')),
                    'current'   => isset($v['nav_key'])   ? $this->_checkCurrentElement($v['nav_key']) : false,
                    'children'  => !empty($v['children']) ? $this->_parseNav($v['children']) : array(),
                );
            }
        }
        return $aResult;
    } // function _parseNav

    /**
     * Get nav URL subject to type and protocol
     * @param string $sUrl
     * @param string $sType
     * @param string $sProtocol
     * @return string
     */
    protected function _getNavURI($sUrl, $sType = 'local', $sProtocol = null)
    {
        if ($sType == 'dummy') {
            return '#';
        }
        if ($sType == 'foreign') {
            return $sUrl;
        }
        return $this->oTab->getURI($sUrl, 'link', null, $sProtocol);
    } // function _getNavURI

    /**
     * Check current element of nav
     * Compare elements by "AND"
     * @param string $sKey
     * @return boolean
     */
    protected function _checkCurrentElement($sKey)
    {
        $aRequest = $this->_getCurrentRequest();
        $bRet = false;
        foreach (explode(',', $sKey) as $v) {
            @list($k1, $k2) = explode(':', $v, 2);
            if (!$k2) {
                $k2 = trim($k1);
                $k1 = 0;
            } else {
                $k1 = (int)trim($k1);
                $k2 = trim($k2);
            }
            if (isset($aRequest[$k1])) {
                if ($aRequest[$k1] != $k2) {
                    return false;
                }
                $bRet = true;
            }
        }
        return $bRet;
    } // function _checkCurrentElement

    /**
     * Get Current Request
     * @param boolean $bForce
     * @return array
     */
    protected function _getCurrentRequest($bForce = false)
    {
        if (empty($this->aCurrentRequest) || $bForce) {
            $sCurReq  = $this->oTab->getCurrentURI(false, false, false, true);
            $aRequest = explode('/', trim($sCurReq, '/'));
            if ($aRequest[0] == 'static_pages') {
                array_shift($aRequest);
            }
            if ($this->getMeta('allowUrlPrefix', false)) {
                $oMatcher = service('matcher');
                /* @var $oMatcher \fan\core\service\matcher */
                $sPrefix = $oMatcher->getCurrentItem()->parsed->app_prefix;
                if ($sPrefix) {
                    foreach (explode('/', $sPrefix) as $v) {
                        if ($v) {
                            array_unshift($aRequest, $v);
                        }
                    }
                }
            }
            $this->aCurrentRequest = $aRequest;
        }
        return $this->aCurrentRequest;
    } // function _getCurrentRequest

} // class \fan\core\block\common\html_nav
?>