<?php namespace fan\core\block\admin;
/**
 * Admin form data class for loader block
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
class data_form extends data
{
    /**
     * Edited or Inserted entity
     * @var object entity
     */
    protected $oRow;

    /**
     * Validate Data
     */
    public function validateData(&$aEdit, &$aInsert)
    {
        if ($aEdit) {
            $aErr = $this->doValidate($aEdit, 'edit');
        } elseif ($aInsert) {
            $aErr = $this->doValidate($aInsert, 'ins');
        }
        if ($aErr) {
            $this->aErrorMsg[] = implode("\n", $aErr);
            return false;
        }
        return true;
    } // function validateData

    /**
     * Parse changed/inserted Data
     * @param array $aEdit
     * @param array $aInsert
     * @return \fan\core\block\admin\data
     */
    public function parseData($aEdit, $aInsert)
    {
        //$sEttAcces = $this->getMeta('check_access4edit'); // ToDo: Check for use it
        $aFields   = $this->getMeta(array('form_struct', 'rows'));
        if ($aEdit) {
            $this->oRow = ge($this->getMeta('entity'))->getRowByParam($this->getCondition());
            $this->saveRow($this->oRow, $aEdit, $aFields);
            $this->checkDBerror($this->oRow, 'Can\'t update data: ');
        } elseif ($aInsert) {
            $aAddFields = array_keys($this->getMeta(array('addParam', 'default_val'), array()));
            $this->oRow = gr($this->getMeta('entity'));
            $this->saveRow($this->oRow, $aInsert, $aFields, $aAddFields);
            $this->checkDBerror($this->oRow, 'Can\'t insert data: ');
        }
        return $this;
    } // function parseData

    /**
     * Init Template Vars
     */
    public function initTplVar()
    {
        $aTplRows  = array();
        $aMetaRows = $this->getMeta(array('form_struct', 'rows'), array());
        foreach ($aMetaRows as $k => $v) {
            $aTplRows[$v['field']] = $v;
        }
        $this->setTemplateVar('rows', $aTplRows);
    } // function initTplVar

    /**
     * Get Main Data
     * @param array $aData
     * @param array $aForce
     * @return boolean
     */
    protected function getMainData($aData, $aForce = array())
    {
        $aJson = parent::getMainData($aData, $aForce);
        $oEtt  = $this->getCurrentRow(true);
        $aJson['ei_mode'] = !empty($oEtt) && $oEtt->checkIsLoad() ? 'edit' : 'ins';
        return $aJson;
    } // function getMainData


    /**
     * Get Content Data
     */
    public function getContentData($bCacheEnable = true)
    {
        $oRow = $this->getCurrentRow($bCacheEnable);

        $aDataSrc = $oRow && $oRow->checkIsLoad() ? $oRow->getFields() : array();
        $aData = array();
        foreach ($this->getMeta(array('form_struct', 'rows'), array()) as $v) {
            if(@$v['field'] && !(@$v['notSQL'])) {
                $aData[$v['field']] = @$aDataSrc[$v['field']];
            }
        }
        return $aData;
    } // function getContentData


    /**
     * Get field label
     * @param string $sName
     * @return string
     */
    public function getFieldLabel($sName)
    {
        foreach ($this->getMeta(array('form_struct', 'rows'), array()) as $v) {
            if ($v['field'] == $sName && @$v['label']) {
                return $v['label'];
            }
        }
        return $sName;
    } // function getFieldLabel

    /**
     * Get current entity
     * @return \fan\core\base\model\row
     */
    public function getCurrentRow($bCacheEnable)
    {
        $oEtt = ge($this->getMeta('entity'));
        $sEttKey = $this->getMeta('entity_key', null);
        return $sEttKey ?
            $oEtt->getRowByKey($sEttKey, $this->getCondition()) :
            $oEtt->getRowByParam($this->getCondition());
    } // function getCurrentRow
} // class \fan\core\block\admin\data_form
?>