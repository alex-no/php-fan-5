<?php namespace fan\project\block\common;
/**
 * Pager quantifier class
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
 * @version of file: 03.003
 */
class html_pager_quantifier extends \fan\core\block\common\html_pager_quantifier
{
    /**
     * Init
     */
    public function init()
    {
        $aQuantifierParams = array();
        foreach ($_GET as $k => $v) {
            if ($k != 'pager_quantifier') {
                $aQuantifierParams[$k] = $v;
            }
        }
        $this->_setViewVar('_quantifier_params', $aQuantifierParams);

        $this->parseForm();
    } // function init


    /**
     * get the form elements' values from HTTP request
     *
     */
    protected function getFieldValuesFromRequest()
    {
        parent::getFieldValuesFromRequest();

        if (!$this->aFieldValue['pager_quantifier']) {
            $this->aFieldValue['pager_quantifier'] = $this->_getQuantifier();
        }
    } // function getFieldValuesFromRequest

    public function onsubmit()
    {
        $sKey = $this->getMeta('sessionKey');

        if ($sKey) {
            $this->aFieldValue['pager_quantifier'] = $this->_getQuantifier(true);

            if ($this->aFieldValue['pager_quantifier']) {
                $this->_getPagerSession()->set($sKey, $this->aFieldValue['pager_quantifier']);
            }
        }
    }

    /**
     * Returns session object
     * @return service_session
     */
    private function _getPagerSession()
    {
        static $session = null;

        if (is_null($session)) {
            $session = service_session::custom_instance('pager');
        }

        return $session;
    }

    /**
     * Returns quantifier value from session or default value
     * @return integer
     */
    private function _getQuantifier($getFromRequest = false)
    {
        $iQuantifier = null;
        $defaultValue = $this->getFormMeta(array('fields', 'pager_quantifier', 'default_value'));

        $sKey = $this->getMeta('sessionKey');
        if ($sKey) {
            if ($getFromRequest) {
                $iQuantifier = service('request')->get('pager_quantifier', 'GP');
            }

            if (empty($iQuantifier)) {
                $iQuantifier = $this->_getPagerSession()->get($sKey, $iQuantifier);
            }

            $aValues = $this->trimDataRecursive(explode(',', $this->getMeta('quantifier_values')), array('trim_data' => true));

            if (!in_array($iQuantifier, $aValues)) {
                $iQuantifier = $defaultValue;
            }
        }

        return $iQuantifier ? $iQuantifier : $defaultValue;
    }

} // class \fan\project\block\common\html_pager_quantifier
?>