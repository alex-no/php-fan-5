<?php namespace fan\project\block\form;
/**
 * Form for send data to server block abstract
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
 * @version of file: 05.02.007 (31.08.2015)
 * @abstract
 */
abstract class injector extends \fan\core\block\form\usual
{
    /**
     * Validate form. You need run (!) this method in your init method
     *
     * Returned values:
     *  - null  - validation wasn't done
     *  - true  - validation was correct
     *  - false - validation wasn't correct
     * @param boolean $bParceEmpty allow parse if form is empty
     * @param boolean $bParsingCondition (null - parse by Meta-condition, true - always parse, false - don't parse )
     * @param boolean $bAllowTransfer allow Transfer after submit
     * @return boolean
     */
    protected function _parseForm($bParceEmpty = true, $bParsingCondition = null, $bAllowTransfer = null)
    {
        $sIdForm = $this->getMeta(array('form', 'form_id'));
        $oRoot   = $this->_getBlock('root');
        $isUseJs = false;

        $aFields = $this->getFormMeta('fields');
        if (!empty($aFields)) {
            foreach ($aFields as $key => $aField) {
                if (!empty($aField['fill_empty'])) {
                    // prepare embedded JS init
                    $oRoot->setEmbedJs(
                        sprintf(
                            'new triggerEmpty("%s", "%s", "%s");',
                            $sIdForm,
                            $key,
                            str_replace(array('"', "\n"), array('&quot;', '\n'), $aField['fill_empty'])
                        ),
                        'head',
                        -1
                    );
                    $isUseJs = true;
                }
            }
        }
        if ($isUseJs) {
            $oRoot->setExternalJs('/js/js-wrapper.js');
            $oRoot->setExternalJs('/js/extra/trigger_empty.js');
        }

        return parent::_parseForm($bParceEmpty, $bParsingCondition, $bAllowTransfer);
    }
} // class \fan\project\block\form\injector
?>