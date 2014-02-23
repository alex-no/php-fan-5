<?php namespace core\block\admin;
/**
 * Admin data class for loader block
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
 * @version of file: 05.007 (23.02.2014)
 */
abstract class data extends base
{
    /**
     * Form error
     * @var array
     */
    protected $aErrorMsg = array();

    /**
     * Form error
     * @var boolean
     */
    protected $bIsError = false;

    /**
     * JSON-parameters of admin data
     * @var array
     */
    protected $aAddParam = array();

    /**
     * JSON-extr data for admin
     * @var array
     */
    protected $aExtraData = array();

    /**
     * Init output block data
     */
    public function init()
    {
        service('role')->setSessionRoles('admin', $this->getMeta('login_timeout'));

        $aData = $this->getData();


        // ====== Parse gotten data ======= \\
        service('error')->setParseDBerror(false);
        do {
            if (isset($aData['edit']) || isset($aData['ins'])) {
                if (empty($aData['edit'])) {
                    $aData['edit'] = array();
                } elseif (empty($aData['ins'])) {
                    $aData['ins'] = array();
                }

                if (!$this->validateData($aData['edit'], $aData['ins'])) {
                    $this->bIsError = true;
                    break;
                }

                $this->parseData($aData['edit'], $aData['ins']);
                if ($this->bIsError) {
                    break;
                }
            }

            if (isset($aData['del'])) { // Cancel edit if delete impossible?
                $this->deleteData($aData['del']);
                if ($this->bIsError) {
                    break;
                }
            }
        } while (false);
        service('error')->setParseDBerror(true);
        if ($this->bIsError) {
            $this->setText(implode("\n", $this->aErrorMsg));
            return;
        }

        // ====== Prepare output data ======= \\
        $aJson = $this->getMainData($aData);

        $aJson['data'] = $this->getContentData();
        $this->setJson($aJson);

        $this->setText('ok');
    } // function init

    /**
     * Parse changed/inserted Data
     */
    public function validateData(&$aEdit, &$aInsert)
    {
        return true;
    } // function validateData

    /**
     * Parse changed/inserted Data
     * @param array $aEdit
     * @param array $aInsert
     * @return \core\block\admin\data
     */
    public function parseData($aEdit, $aInsert)
    {
        return $this;
    } // function parseData

    /**
     * Save Entity Data
     * @param \core\base\model\row $oRow
     * @param array $aData
     * @param array $aFields
     * @param array $aAddFields
     * @return \core\block\admin\data
     */
    public function saveRow($oRow, $aData, $aFields, $aAddFields = array())
    {
        $aEdtType = $this->getMeta('editableTypes', array());
        $aData2 = array();
        foreach ($aFields as $v) {
            $k = $v['field'];
            if (@$aEdtType[$v['type']] && array_key_exists($k, $aData)) {
                $aData2[$k] = $aData[$k];
            }
        }
        if (!$oRow->checkIsLoad()) {
            foreach ($aAddFields as $k) {
                $aData2[$k] = $aData[$k];
            }
        }
        $oRow->setFields($aData2, true);
        return $this;
    } // function saveRow

    /**
     * Parse delete Data
     * @return \core\block\admin\data
     */
    public function deleteData($aDel)
    {
        return $this;
    } // function deleteData

    /**
     * Check DataBase Error
     * @param \core\base\model\row $oRow
     * @return boolean
     */
    protected function checkDBerror(\core\base\model\row $oRow, $sErrPref = '')
    {
        $oCon = $oRow->getEntity()->getConnection();
        if ($oCon->getIsError()) {
            $sErrMsg = $oCon->getErrorMessage();
            if (preg_match('/^(?:.+\:)?([^(]+)/', $sErrMsg, $aMatches)) {
                $sErrMsg = trim($aMatches[1]);
            }
            $this->aErrorMsg[] = $sErrPref . $sErrMsg;
            $this->bIsError = true;
            return false;
        }
        return true;
    } // function checkDBerror

    /**
     * Get Main Data
     * @param array $aData
     * @param array $aForce
     * @return boolean
     */
    protected function getMainData($aData, $aForce = array())
    {
        $aJson      = array();
        $aForceMeta = $this->getMeta('force', array());
        $bIsFirst   = !empty($aData['first']);

        // Prepare template
        $bForceTpl = isset($aForce['template']) ? !empty($aForce['template']) : !empty($aForceMeta['template']);
        if ($bIsFirst || $bForceTpl) {
            $this->initTplVar();
            if (!$this->getTemplate()) {
                $this->setTemplate($this->getMeta('default_tpl'));
            }
            $sHtml = $this->getTemplateCode(array());
            if ($sHtml) {
                $aJson['code'] = $sHtml;
            }
        }

        // Prepare param
        $bForceAddParam = isset($aForce['add_param']) ? !empty($aForce['add_param']) : !empty($aForceMeta['add_param']);
        if ($bIsFirst || $bForceAddParam) {
            $aAddParam = $this->getAddParam();
            if ($aAddParam) {
                $aJson['param'] = $aAddParam;
            }
        }

        // Prepare Extra data
        $bForceExtraData = isset($aForce['extra_data']) ? !empty($aForce['extra_data']) : !empty($aForceMeta['extra_data']);
        if ($bIsFirst || $bForceExtraData) {
            $aExtra = $this->getExtraData();
            if ($aExtra) {
                $aJson['extra'] = $aExtra;
            }
        }

        // Flag for use Main Page
        if ($bIsFirst || $this->getMeta('useMainPage')) {
            $aJson['useMainPage'] = 1;
        }

        return $aJson;
    } // function getMainData

    /**
     * Init Template Vars
     * @return \core\block\admin\data
     */
    public function initTplVar()
    {
        return $this;
    } // function initTplVar

    /**
     * Get Additional Parameters
     * @return array
     */
    public function getAddParam()
    {
        $aRet    = adduceToArray($this->getMeta('addParam', array()));
        $sEntity = $this->getMeta('entity', array());
        if ($sEntity) {
            $aRet['id_name'] = ge($sEntity)->getDescription()->getPrimeryKey();
        }
        return array_merge_recursive_alt($aRet, $this->aAddParam);
    } // function getAddParam

    /**
     * Get Content ExtraData
     */
    public function getExtraData()
    {
        $aRet = array();
        $sTagId = $this->getMeta('tagId');
        if ($sTagId) {
            $aRet['tagId'] = 'cont_' . $sTagId;
        }
        return array_merge_recursive_alt($aRet, $this->aExtraData);
    } // function getExtraData

    /**
     * Get Condition
     * @return array
     */
    public function getCondition()
    {
        $aCond = $this->getMeta('condition', array());
        $aData = $this->getData();
        if (@$aData['cond']) {
            $aCond = array_merge_recursive_alt($aCond, $aData['cond']);
        }
        return $aCond;
    } // function getCondition

    /**
     * Get Content Data
     */
    public function getContentData()
    {
        return array();
    } // function getContentData


    /**
     * Get field label
     * @param string $sName
     * @return string
     */
    public function getFieldLabel($sName)
    {
        return $sName;
    } // function getFieldLabel

    // ================================ Validate data ================================ \\
    /**
     * Do validate data
     * @param array $aData
     * @param string $sType
     * @param number $nId - id
     * @return aray of error messages for each field
     */
    public function doValidate(&$aData, $sType, $nId = null)
    {
        $sReq = $this->getMeta('validateRequiredMsg');
        $aErr = array();
        foreach ($this->getMeta('validation', array()) as $fld => $vld) {
            if (isset($aData[$fld]) && (!isset($vld['trim_data']) || $vld['trim_data'])) {
                $aData[$fld] = trim($aData[$fld]);
            }
            if (@$vld['is_required'] && ($sType == 'ins' ? !@$aData[$fld] : isset($aData[$fld]) && !$aData[$fld])) {
                $aErr[$fld] = str_replace('{FIELD_LABEL}', $this->getFieldLabel($fld), $sReq);
                continue;
            }
            if(@$vld['validate_rules']) {
                foreach ($vld['validate_rules'] as $rule) {
                    $sMethod = 'rule_' . $rule['rule_name'];
                    if ((isset($aData[$fld]) || $sType == 'ins') && (@$aData[$fld] || @!$rule['not_empty'])) {
                        if (!isset($aData[$fld])) {
                            $aData[$fld] = null;
                        }
                        if (!$this->$sMethod($aData[$fld], @$rule['rule_data'], $sType, $nId)) {
                            $aErr[$fld] = str_replace('{FIELD_LABEL}', $this->getFieldLabel($fld), @$rule['error_msg'] ? $rule['error_msg'] : 'Error');
                            continue 2;
                        }
                    }
                }
            }
        }
        return $aErr;
    } // function doValidate



    /**
     * Check up if a value is not empty
     *
     * @param mixed $mValue
     * @param array $aData
     * @return mixed value
     */
    protected function rule_is_required($mValue)
    {
        return $mValue != '';
    } // function rule_is_required

    /**
     * Check up if a value is a integer number
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    protected function rule_is_int($mValue, $aData)
    {
        if (!preg_match('/^\-?\d+$/', $mValue)) {
            return false;
        }
        if (isset($aData['min_value']) && $mValue < $aData['min_value']) {
            return false;
        }
        if (isset($aData['max_value']) && $mValue > $aData['max_value']) {
            return false;
        }
        return true;
    } // function rule_is_int

    /**
     * Check up if a value is a real number
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    protected function rule_is_float($mValue, $aData)
    {
        $value = str_replace(',', '.', $mValue);
        if (!is_numeric($mValue)) {
            return false;
        }
        if (isset($aData['min_value']) && $mValue < $aData['min_value'] - 0.000001) {
            return false;
        }
        if (isset($aRule['max_value']) && $mValue > $aData['max_value'] + 0.000001) {
            return false;
        }
        return true;
    } // function rule_is_float

    /**
     * Check up if a value is a date and is in given interval
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    protected function rule_is_date($mValue, $aData)
    {
        $mValue = str_replace(',', '.', $mValue);

        //$oDate = service('date', $mValue, 'mysql');
        $oDate = service('date', $mValue);
        if (!$oDate->isValid()) {
            return false;
        }
        $mValue = $oDate->convertLocal2Mysql();
        return (!isset($aData['min_value']) || $mValue >= $aData['min_value']) && (!isset($aData['max_value']) || $mValue <= $aData['max_value']);
    } // function rule_is_date

    /**
     * Check up if a value contains e-mail address
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    protected function rule_is_email($mValue, $aData)
    {
        if (!preg_match('/^[a-z_0-9!#*=.-]+@([a-z0-9-]+\.)+[a-z]{2,4}$/i', $mValue)) {
            return false;
        }
        return true;
    } // function rule_is_email

    /**
     * Check up if a value consists of letters, numbers, _, @, ., - and begging from letter or number
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    protected function rule_is_alphalogin($mValue, $aData)
    {
        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_@\.-]*$/', $mValue)) {
            return false;
        }
        return true;
    } // function rule_is_alphalogin

    /**
     * Check up if a value consists of letters, numbers, _, - and begging from letter or number
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    protected function rule_is_alphanumeric($mValue, $aData)
    {
        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]*$/', $mValue)) {
             return false;
        }
        return true;
    } // function rule_is_alphanumeric

    /**
     * Check up if a value matchs with the regular expression
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    protected function rule_match_regexp($mValue, $aData)
    {
        if (!preg_match($aData['regexp'], $mValue)) {
            return false;
        }
        return true;
    } // function rule_match_regexp

    /**
     * Check up if a value is equal to compare field
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    protected function rule_equal_to($mValue, $aData)
    {
        $mValue2 = null;
        if (isset($aData['compare_field'])) {
            $mValue2 = @$this->aFieldValue[$aData['compare_field']];
        }
           if ($mValue != $mValue2) {
            return false;
        }
        return true;
    } // function rule_equal_to

    /**
     * Check up if a value is not equal to compare field
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    protected function rule_not_equal_to($mValue, $aData)
    {
        $mValue2 = null;
        if (isset($aData['compare_field'])) {
            $mValue2 = @$this->aFieldValue[$aData['compare_field']];
        }
        if ($mValue == $mValue2) {
            return false;
        }
        return true;
    } // function rule_not_equal_to

    /**
     * Check up if a value is greater then compare field
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    protected function rule_greater_than($mValue, $aData)
    {
        $mValue2 = null;
        if (isset($aData['compare_field'])) {
            $mValue2 = @$this->aFieldValue[$aData['compare_field']];
        }
        if (@$aField['data_type']=='DATE' ||  @$aField['data_type']=='DATETIME') {
            $mValue = service('string_format')->date_local2mysql($mValue, false);
            $mValue2 = service('string_format')->date_local2mysql($mValue2, false);
        }
        if ($mValue <= $mValue2) {
            return false;
        }
        return true;
    } // function rule_greater_than

    /**
     * Check up if a value is lesser then compare field
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    protected function rule_lesser_than($mValue, $aData)
    {
        $mValue2 = null;
        if (isset($aData['compare_field'])) {
            $mValue2 = @$this->aFieldValue[$aData['compare_field']];
        }
        if (@$aField['data_type']=='DATE' ||  @$aField['data_type']=='DATETIME') {
            $mValue = service('string_format')->date_local2mysql($mValue, false);
            $mValue2 = service('string_format')->date_local2mysql($mValue2, false);
        }
        if ($mValue >= $mValue2) {
            return false;
        }
        return true;
    } // function rule_lesser_than

    /**
     * Check up if a value is greater or equal to compare field
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    protected function rule_greater_or_equal_to($mValue, $aData)
    {
        $mValue2 = null;
        if (isset($aData['compare_field'])) {
            $mValue2 = @$this->aFieldValue[$aData['compare_field']];
        }
        if ($mValue < $mValue2) {
            return false;
        }
        return true;
    } // function rule_greater_or_equal_to

    /**
     * Check up if a value is lesser or equal to compare field
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    protected function rule_lesser_or_equal_to($mValue, $aData)
    {
        $mValue2 = null;
        if (isset($aData['compare_field'])) {
            $mValue2 = @$this->aFieldValue[$aData['compare_field']];
        }
        if ($mValue > $mValue2) {
            return false;
        }
        return true;
    } // function rule_lesser_or_equal_to

} // class \core\block\admin\data
?>