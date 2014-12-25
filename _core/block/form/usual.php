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
 * @version of file: 05.02.004 (25.12.2014)
 * @abstract
 */
abstract class usual extends parser
{

    /**
     * Array for template
     * @var array
     */
    protected $aFormTpl = array();

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
        if ($nMode == 0 || !empty($_POST) && $this->getForm()->necessaryFormParsing(null, false)) {
            $this->disableCache();
            return false;
        } elseif($nMode == 1) {
            return false;
        }
        return true;
    } // function getCachePermission

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

} // class \fan\core\block\form\usual
?>