<?php namespace fan\core\block\form;
/**
 * Usual form block abstract
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
 * @version of file: 05.02.001 (10.03.2014)
 * @abstract
 */
abstract class usual extends \fan\core\block\base
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
     * Array for template
     * @var array
     */
    protected $aFormTpl = array();

    /**
     * Get service of form
     * @return \fan\core\service\form
     */
    public function getForm()
    {
        if (empty($this->oForm)) {
            $this->oForm = \fan\project\service\form::instance($this);
            $this->oForm->addListener('onSubmit', array($this, 'onSubmit'));
            $this->oForm->addListener('onError',  array($this, 'onError'));
        }
        return $this->oForm;
    } // function getForm

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

/*
            // Create JS validation rule
            if (!$this->sRoleName || !role($this->sRoleName)) {
                $sValidateJS = $this->strForJsValidation();
                if ($sValidateJS) {
                    $this->getBlock('root')->setEmbedJs($sValidateJS);
                }
            }
 */
        }
    } // function finishConstruct

    /**
     * Get cache permission: true if cache enabled
     * @return boolean
     */
    public function getCachePermission()
    {
        $nMode = $this->getMeta(array('cache', 'mode'));
        if ($nMode == 0 || !empty($_POST) && $this->necessaryFormParsing(null, false)) {
            $this->disableCache();
            return false;
        } elseif($nMode == 1) {
            return false;
        }
        return true;
    } // function getCachePermission

    /**
     * Check Form Role
     * @return boolean
     */
    public function checkFormRole()
    {
        return $this->oForm->checkFormRole();
    } // function checkFormRole

    /**
     * User's function for the validate whole form before field validation
     * @access protected
     */
    public function checkBeforeValidation()
    {
        return true;
    } // function checkBeforeValidation

    /**
     * User's function for the validate whole form before field validation
     * @access protected
     */
    public function checkAfterValidation()
    {
        return true;
    } // function checkAfterValidation

    /**
     * User's function for the post submit permition
     * @access protected
     */
    public function onSubmit()
    {

    } // function onSubmit

    /**
     * User's function for the validation failed case
     * @access protected
     */
    public function onError()
    {

    } // function onError

    /**
     * Get All View data
     * @return array
     */
    public function getViewData()
    {
        $aResult     = parent::getViewData();
        $oForm       = $this->getForm();
        $aFieldValue = $oForm->getFieldValue();

        foreach ($this->getFieldsMeta() as $sFieldName => $aParameters) {

            if (!isset($this->aFormTpl[$sFieldName])) {
                //name of the form element
                $this->aFormTpl[$sFieldName]['name'] = $sFieldName;
                //type of the form element
                $this->aFormTpl[$sFieldName]['type'] = empty($aParameters['input_type']) ? null : $aParameters['input_type'];
                //label of the form element
                $this->aFormTpl[$sFieldName]['label'] = empty($aParameters['label']) ? null : $aParameters['label'];
                //value of the form element
                //it can be an array. if it is, it's mean that may be a few elements with same name and different indexes
                $this->aFormTpl[$sFieldName]['value'] = $oForm->isError() || isset($aFieldValue[$sFieldName]) ?
                        array_val($aFieldValue, $sFieldName) :
                        empty($aParameters['default_value']) ? null : $aParameters['default_value'];
                //parameters of the form element
                $this->aFormTpl[$sFieldName]['parameters'] = empty($aParameters['parameters']) ? null : $aParameters['parameters'];
            }
        }


        $aResult['aErrors']  = $oForm->getErrorMsg();
        $aResult['aFormTpl'] = $this->aFormTpl;
        $sActionUrl = $this->getFormMeta('action_url');
        if(empty($sActionUrl)) {
            $sActionUrl    = $this->oTab->getCurrentURI(false, true, strtoupper($this->getFormMeta('action_method')) != 'GET', false);
            $sDefaultHttps = $this->oTab->getTabMeta('page_https');
        } else {
            $sDefaultHttps = null;
        }
        $aResult['action_url'] = $this->oTab->getURI($sActionUrl, 'link', false, $this->getFormMeta('action_https', $sDefaultHttps));

        $sActionMethod = strtolower($this->getFormMeta('action_method'));
        if ($sActionMethod == 'file') {
            $sActionMethod = 'post" enctype="multipart/form-data';
        } elseif ($sActionMethod != 'get') {
            $sActionMethod = 'post';
        }
        $aResult['action_method']  = '"' . $sActionMethod . '"';
        $aResult['form_key_field'] = $this->getFormMeta('form_key_field');
        $aResult['form_id']        = $this->getFormMeta('form_id');

        return $aResult;
    } // function getViewData

    /**
     * Get Role Name
     * @return string
     */
    public function getRoleName()
    {
        return $this->sRoleName;
    } // function getRoleName

//============ Functions are usualy redefined at the children classes ================\\

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

            $this->setCacheRole($this->sRoleName);
        }
    } // function doRoleOperations

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
        return $this->getForm()->parseForm($bParceEmpty, $bParsingCondition, $bAllowTransfer);
    } // function _parseForm

} // class \fan\core\block\form\usual
?>