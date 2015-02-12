<?php namespace fan\core\block\form;
/**
 * Form block just for parse data, not for show form
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
 * @version of file: 05.02.005 (12.02.2015)
 * @abstract
 */
abstract class parser extends \fan\core\block\base
{
    /**
     * Is form
     * @var boolean
     */
    protected $bIsForm = true;

    /**
     * Form service
     * @var \fan\core\service\form
     */
    protected $oForm;

    /**
     * Role name form
     * @var string
     */
    protected $sRoleName = '';

    /**
     * Init Parts of current form (usually auto - before main parsing)
     * Flag protect from double init if it was runned early
     * @var boolean
     */
    protected $bPartsInit = false;

    /**
     * Finish Construction of block
     * @param \fan\core\block\base $oContainer
     * @param array $aContainerMeta
     * @param boolean $bAllowSetEmbedded
     */
    public function finishConstruct($oContainer = null, $aContainerMeta = array(), $bAllowSetEmbedded = true)
    {
        parent::finishConstruct($oContainer, $aContainerMeta, $bAllowSetEmbedded);
        if ($this->bIsForm && !$this->getRoleCondition()) {
            $this->_redefineFieldMeta();
            $this->_correctFieldMeta();
        }
    } // function finishConstruct

    /**
     * Check Form Role
     * @return boolean
     */
    public function checkFormRole()
    {
        return $this->oForm->checkFormRole();
    } // function checkFormRole

    /**
     * Get service of form
     * @return \fan\core\service\form
     */
    public function getForm()
    {
        if (empty($this->oForm)) {
            $this->oForm = \fan\project\service\form::instance($this);
            $this->oForm->addListener('onSubmit', array($this, 'onSubmitEvent'));
            $this->oForm->addListener('onError',  array($this, 'onErrorEvent'));
        }
        return $this->oForm;
    } // function getForm

    /**
     * Get Role Name
     * @return string
     */
    public function getRoleName()
    {
        return $this->sRoleName;
    } // function getRoleName

    /**
     * Get Form Meta
     * @param string $mKey
     * @param mixed $mDefault
     * @return \fan\core\base\meta\row
     */
    public function getFormMeta($mKey = null, $mDefault = null)
    {
        $oFormMeta = $this->getMeta('form');
        if (empty($oFormMeta)) {
            return null;
        }
        return empty($mKey) ? $oFormMeta : $oFormMeta->get($mKey, $mDefault);
    } // function getFormMeta

    /**
     * Get Fields Meta
     * @return \fan\core\base\meta\row
     */
     public function getFieldsMeta()
    {
        return $this->getMeta(array('form', 'fields'), array());
    } // function getFieldsMeta

    /**
     * Parser of Event "onSubmit"
     * @access public
     */
    public function onSubmitEvent($oBlock)
    {
        if ($oBlock === $this) {
            $this->onSubmit();
            $this->_broadcastEvent('onSubmit', $this->getForm()->getFieldValue());
        }
    } // function onSubmitEvent

    /**
     * Parser of Event "onError"
     * @access public
     */
    public function onErrorEvent($oBlock)
    {
        if ($oBlock === $this) {
            $this->onError();
            $this->_broadcastEvent('onError', $this->getForm()->getErrorMsg());
        }
    } // function onErrorEvent

//============ Functions are usualy redefined at the children classes ================\\

    /**
     * Function return true if need to validate form data
     * Redefine this method in child classes
     * @return boolean
     */
    public function checkBeforeValidation()
    {
        return true;
    } // function checkBeforeValidation

    /**
     * Function return true if to run onSubmit and onError events after validation
     * Redefine this method in child classes
     * @return boolean
     */
    public function checkAfterValidation()
    {
        return true;
    } // function checkAfterValidation

    /**
     * User's function for the post submit permition
     * @access protected
     */
    protected function onSubmit()
    {
    } // function onSubmit

    /**
     * User's function for the validation failed case
     * @access protected
     */
    protected function onError()
    {
    } // function onError


//============ Prived and Protected methods ================\\

    /**
     * Redefine role and do other operations with roles
     */
    protected function _doRoleOperations()
    {
        if ($this->bIsForm) {
            if(!$this->getFormMeta('form_id')) {
                $this->setMeta(array('form', 'form_id'), $this->sBlockName);
            }

            if (!$this->getFormMeta('not_role')) {
                $this->sRoleName = $this->getFormMeta('role_name') ? $this->getFormMeta('role_name') : 'form_submit_successful_' . $this->getFormMeta('form_id');
            }

            $this->_setCacheRole($this->sRoleName);
        }
    } // function doRoleOperations

    /**
     * Redefine Field Meta data
     * @access protected
     */
    protected function _redefineFieldMeta()
    {
    } // function redefineFieldMeta

    /**
     * Correct field meta: replace emty values to default value
     *
     */
    protected function _correctFieldMeta()
    {
        foreach ($this->getFieldsMeta() as $sFieldName => $aParameters) {
            if (!empty($aParameters['validate_rules']) || !empty($aParameters['is_required'])) {
                if (!isset($aParameters['is_required'])) {
                    $aParameters['is_required'] = false;
                    if (isset($aParameters['validate_rules'])) {
                        foreach ($aParameters['validate_rules'] as $aValidate) {
                            if (empty($aValidate['not_empty']) && (!isset($aValidate['rule_name']) || $aValidate['rule_name'] != 'is_required')) {
                                $aParameters['is_required'] = true;
                                break;
                            }
                        }
                    }
                }
                if (!isset($aParameters['label'])) {
                    $aParameters['label'] = $sFieldName;
                }
            }
            if (!isset($aParameters['trim_data'])) {
                 $aParameters['trim_data'] = isset($aParameters['input_type']) && $aParameters['input_type'] != 'password' ? true : false;
            }
            if (!isset($aParameters['is_required'])) {
                    $aParameters['is_required'] = false;
            }
        }
    } // function _correctFieldMeta

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
        if ($this->getMeta('auto_init_parts', true) && !$this->bPartsInit) {
            $this->_initFormParts($this);
        }
        return $this->getForm()->parseForm($bParceEmpty, $bParsingCondition, $bAllowTransfer);
    } // function _parseForm
    /**
     * Init Form Parts
     * Method is called in "init" of main part block
     * @param \fan\core\block\form\parser $oMainFormBlock Main form part block
     */
    protected function _initFormParts($oMainFormBlock = NULL)
    {
        $this->bPartsInit = true;
        $aParts = $this->getFormMeta('form_parts', array());
        foreach ($aParts as $v) {
            $oBlock = $this->getTab()->getTabBlock($v, false);
            if (empty($oBlock)) {
                trigger_error('Block "' . $v . '" (part of form) is not found', E_USER_WARNING);
            } elseif (method_exists($oBlock, 'partInit')) {
                $oBlock->partInit($oMainFormBlock);
                if (method_exists($oBlock, '_initFormParts') && is_callable(array($oBlock, '_initFormParts'))) {
                    $oBlock->_initFormParts($oMainFormBlock);

                }
            }
        }
        return $this;
    } // function _initFormParts

} // class \fan\core\block\form\parser
?>