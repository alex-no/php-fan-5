<?php namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * Parsing form service
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
 */
class form extends \fan\core\base\service\multi
{
    /**
     * @var array Service's Instances
     */
    private static $aInstances = array();

    /**
     * @var \fan\core\block\form\usual
     */
    protected $oBlock;

    /**
     * @var \fan\core\base\meta\row
     */
    protected $aFormMeta;
    /**
     * Field description from meta data
     * @var array
     */
    protected $aFieldMeta = array();
    /**
     * Field types
     * @var array
     */
    protected $aFieldTypes = array();

    /**
     * Form's data from HTTP request
     * @var array
     */
    protected $aFieldValue = array();

    /**
     * Form's data from Form parts
     * @var array
     */
    protected $aPartFieldValue = array();

    /**
     * Form's data for make field (select/radio/checkbox)
     * @var array
     */
    protected $aFieldData = array();
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
     * Form Validators
     * @var array
     */
    protected $aValidators = array();

    /**
     * Form Validators
     * @var array
     */
    protected $aValidatorClasses = array();

    /**
     * Role name form
     * @var string
     */
    protected $sRoleName = '';

    /**
     * Service's constructor
     * @param \fan\core\block\form\usual $oBlock
     */
    protected function __construct(\fan\core\block\form\usual $oBlock)
    {
        parent::__construct(empty(self::$aInstances));

        $this->oBlock     = $oBlock;
        $this->aFormMeta  = $oBlock->getFormMeta();
        if (empty($this->aFormMeta)) {
            throw new fatalException($this, 'Form meta isn\'t set for block "' . get_class($oBlock) . '".');;
        }
        $this->aFieldMeta = $this->aFormMeta->get('fields');
        if (empty($this->aFieldMeta)) {
            throw new fatalException($this, 'Form fields meta aren\'t set for block "' . get_class($oBlock) . '".');;
        }

        $aActiveElements = $this->getConfig('ACTIVE_ELEMENTS', array('input', 'checking', 'select', 'select_separated', 'select_multi', 'select_multi_separated'));
        foreach ($aActiveElements as $v) {
            foreach ($this->_getFormMeta(array('design', $v), array()) as $k => $tmp) {
                $this->aFieldTypes[$k] = $v;
            }
        }

        foreach ($this->oConfig['VALIDATORS'] as $k => $v0) {
            foreach ($v0 as $v1) {
                $this->aValidatorClasses[$v1] = $k;
            }
        }

        self::$aInstances[$oBlock->getBlockName()] = $this;
    } // function __construct

    // ======== Static methods ======== \\
    /**
     * Get instance of form
     * @param \fan\core\block\form\usual $oBlock
     * @return \fan\core\service\form
     */
    public static function instance(\fan\core\block\form\usual $oBlock)
    {
        $sName = $oBlock->getBlockName();
        if (!isset(self::$aInstances[$sName])) {
            new self($oBlock);
        }
        return self::$aInstances[$sName];
    } // function instance

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\

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
    public function parseForm($bParceEmpty = true, $bParsingCondition = null, $bAllowTransfer = null)
    {
        $bRet = null;
        // Check - is need to validate this form
        if (!empty($this->aFormMeta)) {
            if ($this->necessaryFormParsing($bParsingCondition, false)) {
                // Get data for parsing
                $this->getFieldValuesFromRequest();
            }
            while ($this->necessaryFormParsing($bParsingCondition, true)) {
                if (!$bParceEmpty && empty($this->aFieldValue)) {
                    break;
                }
                // Validate data
                if ($this->oBlock->checkBeforeValidation()) {
                    foreach ($this->aFieldMeta as $sFieldName => $aParameters) {
                        if ($this->_checkByData($sFieldName, isset($aParameters['label']) ? $aParameters['label'] : $sFieldName)) {
                            if (!array_key_exists($sFieldName, $this->aFieldValue)) {
                                $this->aFieldValue[$sFieldName] = null;
                            }
                            if (!array_key_exists($sFieldName, $this->aErrorMsg)) {
                                $this->aErrorMsg[$sFieldName] = null;
                            }
                            $this->_validateValueRecursive($this->aFieldValue[$sFieldName], $aParameters, $this->aErrorMsg[$sFieldName]);
                        }
                    }
                }

                $this->_parseFormParts($bParceEmpty);

                // Processing after validation
                if ($this->oBlock->checkAfterValidation()) {
                    if ($this->bIsError) {

                        $this->_broadcastMessage('onError', $this);

                        $bRet = false;
                        break;
                    } else {
                        $oRole = \fan\project\service\role::instance();
                        /* @var $oRole \fan\core\service\role */
                        if (!$this->_getFormMeta('not_role')) {
                            $oRole->setFixQttRoles($this->oBlock->getRoleName());
                        }

                        //ToDo: Clear cache of some blocks there
                        //\fan\project\service\cache::instance()->clear($this->aFormMeta->get(array('cache', 'clear')));

                        $this->_broadcastMessage('onSubmit', $this);

                        if (is_null($bAllowTransfer)) {
                            $bAllowTransfer = strtoupper($this->aFormMeta['action_method']) != 'GET';
                            // ToDo: Do not disable transfer there, but remove form_key_field from query string
                        }
                        if (!$this->bIsError) {
                            $oRole->killSessionRoles($this->sRoleName);
                        } elseif ($bAllowTransfer) {
                            if ((integer)$this->_getFormMeta('csrf_protection') >= 4) {
                                \fan\project\service\session::instance($this->_getFormMeta('form_id'), 'form_key')->remove('csrf');
                            }
                            $this->_onSubmitTransfer('commit');
                        }
                        $bRet = !$this->bIsError;
                        break;
                    }
                }
                break;
            }
        }
        return $bRet;
    } // function parseForm

    /**
     * Check it is necessary to parse the form.
     *
     * @param mixed $bParsingCondition
     * @return boolean
     */
    public function necessaryFormParsing($bParsingCondition = null, $bChkButton = true)
    {
        if (!is_null($bParsingCondition)) {
            return $bParsingCondition;
        }
        if ($this->_getFormMeta('always_parse')) {
            return true;
        }

        $oRequest     = \fan\project\service\request::instance();
        $sRequestType = $this->_getFormMeta('request_type', 'GP');

        // Analyse key field
        $sSrcKeyVal = $this->_getFormMeta('form_id');
        if (!empty($sSrcKeyVal)) {
            if ((integer)$this->_getFormMeta('csrf_protection') >= 4) {
                $sCsrfCode   = \fan\project\service\session::instance($sSrcKeyVal, 'form_key')->get('csrf');
                $sSrcKeyVal .= '_' . $sCsrfCode;
            }
            $sKeyField = $oRequest->get('form_key_field', 'GP');
            if ($sSrcKeyVal != $sKeyField) {
                return false;
            }
        }


        // Analyse submit buttons
        $mSubmit = $this->_getFormMeta('form_submit_name');
        if ($mSubmit) {
            if (is_array($mSubmit)) {
                foreach ($mSubmit as $v) {
                    if ($oRequest->get($v, $sRequestType)) {
                        return true;
                    }
                }
            } elseif ($oRequest->get($mSubmit, $sRequestType)) {
                return true;
            }
        }

        if ($bChkButton) {
            // Analyse exception buttons
            $mExceptions = $this->_getFormMeta('form_exceptions');
            if ($sKeyField && $mExceptions) {
                foreach ($mExceptions as $v) {
                    if ($oRequest->get($v, $sRequestType)) {
                        return false;
                    }
                }
            } elseif($mSubmit) {
                return false;
            }
        }

        // If doesn't set Key field, Submit button and Exception button - parse if $_POST doesn't empty
        return !empty($sKeyField) || !empty($_POST);
    } // function necessaryFormParsing

    /**
     * get the form elements' values from HTTP request
     *
     */
    public function getFieldValuesFromRequest()
    {
        $oRequest     = \fan\project\service\request::instance();
        $sRequestType = $this->_getFormMeta('request_type');

        if (!$sRequestType) {
            $aTmp = array('GET'=>'G', 'POST'=>'P', 'FILE'=>'PF');
            $sRequestType = $aTmp[$this->_getFormMeta('action_method', 'POST')];
            if (!$sRequestType) {
                $sRequestType = 'GPF';
            }
        }

        foreach ($this->aFieldMeta as $sFieldName => $aParameters) {
            $aMatch = null;
            if(preg_match_all('/([^\[]+)?\[([^\]]+)\]/', $sFieldName, $aMatch)){
                $checkValue = $oRequest->get($aMatch[1][0], $sRequestType);
                foreach($aMatch[2] as $v) {
                    if (isset($checkValue[$v])) {
                        $checkValue = $checkValue[$v];
                    } else {
                        $checkValue = null;
                        break;
                    }
                }
                $this->aFieldValue[$sFieldName] = $this->_trimDataRecursive($checkValue, $aParameters, $sFieldName);
            } else {
                $mVal = $oRequest->get($sFieldName, $sRequestType);
                $aUploads = $this->getConfig('UPLOAD_TYPES', array('file', 'file_multiple'));
                if (in_array($aParameters['input_type'], adduceToArray($aUploads))) {
                    $this->aFieldValue[$sFieldName] = $mVal;
                } else {
                    $bIsMulti = $this->_isMultiVal($aParameters['input_type']);
                    while (is_array($mVal)) {
                        $mTmp = reset($mVal);
                        if ($bIsMulti && is_scalar($mTmp)) {
                            break;
                        }
                        $mVal = $mTmp;
                    }
                    if (!($bIsMulti ? is_array($mVal) : is_scalar($mVal))) {
                        continue;
                    }
                    $this->aFieldValue[$sFieldName] = $this->_trimDataRecursive($mVal, $aParameters, $sFieldName);
                    //, array_val($aParameters, 'default_value')
                }
            }
            $this->aErrorMsg[$sFieldName] = null;
        }
    } // function getFieldValuesFromRequest

    /**
     * Preparing the string for javascript validation
     * @return string
     */
    public function strForJsValidation()
    {
        $sStr = '';
        $sReqMsg = $this->_getFormMeta($this->isMultiLanguage() ? 'required_msg' : 'required_msg_alt');
        foreach ($this->aFieldMeta as $sFieldName => $aParameters) {
            $sRules = '';
            if (@$aParameters['is_required']) {
                $sRules .= '{rule_name:\'is_required\', ';
                $sRules .= 'error_msg:\'' . $this->reduceMessage($sReqMsg, $aParameters['label']) . '\'}';
            }

            if(isset($aParameters['validate_rules'])) {
                foreach ($aParameters['validate_rules'] as $aRule) {
                    if (!@$aRule['not_js']) {
                        $sRules .= $sRules ? ',' : '';

                        $sRules .= '{rule_name:\'' . $aRule['rule_name'] . '\', ';
                        $sRules .= 'error_msg:\'' . $this->reduceMessage($aRule['error_msg'], $aParameters['label']) . '\'';
                        if (@$aRule['not_empty']) {
                            $sRules .= ',not_empty:1';
                        }
                        $sRuleData = '';
                        if (isset($aRule['rule_data'])) {
                            foreach ($aRule['rule_data'] as $sKey => $sValue) {
                                $sRuleData .= $sRuleData ? ',' : '';
                                $sRuleData .= strtolower($sKey) . ':';
                                if (is_bool($sValue)) {
                                    $sRuleData .= $sValue ? 1 : 0;
                                } elseif(preg_match ('/^(\/.+\/)([a-z]*)$/i', $sValue, $aMatches)) {
                                    $sRuleData .= $aMatches[1];
                                    if (@$aMatches[2]) {
                                        for ($i = 0; $i < strlen($aMatches[2]); $i++) {
                                            if (in_array($aMatches[2]{$i}, array('i', 'g', 'm'))) {
                                                $sRuleData .= $aMatches[2]{$i};
                                            }
                                        }
                                    }
                                } else {
                                    $sRuleData .= '\'' . addslashes($sValue) . '\'';
                                }
                            }
                        }
                        $sRules .= $sRuleData ? ',ruleData:{'.$sRuleData.'}}' : '}';
                    }
                }
            }
            if($sRules) {
                $sStr .= $sStr ? ',' : '';
                $sStr .= '\'' . $sFieldName . ($this->_isMultiVal($aParameters['input_type']) ? '[]' : '') . '\':[' . $sRules . ']';
            }
        } //foreach ($this->aFieldMeta as $sFieldName => $aParameters)

        if ($sStr) {
            $aJsUrl = $this->_getFormMeta('js_url');
            $oRoot  = $this->oTab->getTabBlock('root');
            $oRoot->setExternalJs($aJsUrl['js-wrapper']);
            $oRoot->setExternalJs($aJsUrl['validator']);

            $aLoaderData = $this->_getFormMeta('js_loader');
            if ($aLoaderData && $aLoaderData['fields']) {
                $sJsLoader = ',loader:{url:"' . $aLoaderData['url'] . '",fields:["' . implode('","', $aLoaderData['fields']) . '"]}';
                $oRoot->setExternalJs($aJsUrl['js-loader']);
            } else {
                $sJsLoader = '';
            }

            $sStr = 'var validation_' . $this->sBlockName . '=new ' . $this->_getFormMeta('js_validator') .
                '({form:"' . $this->_getFormMeta('form_id') . '"' .
                ',err_format:"' . $this->_getFormMeta('js_err_format') . '"' .
                ($this->_getFormMeta('form_submit_name') ? ',field:"' . $this->_getFormMeta('form_submit_name') . '"' : '') .
                $sJsLoader . '},{' . $sStr . '},_wrapper);';
        } else {
            $sStr = null;
        }
        return $sStr;
    } // function strForJsValidation

    /**
     * Return true if form use multilanguage
     * Method can be redefined in the child class
     * @return boolean
     */
    public function isMultiLanguage()
    {
        $bRet = $this->_getFormMeta('useMultiLanguage', null);
        return is_null($bRet) ? \fan\project\service\locale::instance()->isEnabled() : $bRet;
    } // function isMultiLanguage

    /**
     * Preparing the string for javascript validation
     * @return string
     */
    public function reduceMessage($sMsg, $sLabel)
    {
        $sMsg = preg_replace('/\<\/?(?:div|p|br).*?\>/', "\n", $sMsg);
        $sMsg = preg_replace('/\<.*?\>/', '', $sMsg);
        $sMsg = addslashes($this->isMultiLanguage() ? msg($sMsg, $sLabel) : msgAlt($sMsg, $sLabel));
        return str_replace("\n", '\n', $sMsg);
    } // function reduceMessage

    /**
     * Get array field value
     * @param mixed $mFieldName
     * @return mixed
     */
    public function getFieldValue($mFieldName = null)
    {
        if (empty($mFieldName)) {
            return $this->aFieldValue;
        }
        $bIsArray = is_array($mFieldName);
        $sValue   = $bIsArray ? array_get_element($this->aFieldValue, $mFieldName, false) : array_val($this->aFieldValue, $mFieldName);
        if (is_null($sValue)) {
            $sValue = $this->aFormMeta->get(array('fields', $bIsArray ? $mFieldName[0] : $mFieldName, 'default_value'));
        }
        return $sValue;
    } // function getFieldValue

    /**
     * Get field data
     * @param mixed $mFieldName
     * @return mixed
     */
    public function getFieldData($mFieldName)
    {
        $mKey = \is_array($mFieldName) ? $mFieldName[0] : $mFieldName;
        if (!empty($this->aFieldData[$mKey])) {
            return $this->aFieldData[$mKey];
        }

        $oMeta = $this->aFormMeta->get(array('fields', $mKey));
        if (isset($oMeta->dataSource->method)) {
            $aCallback = array(
                isset($oMeta->dataSource->class) ? $oMeta->dataSource->class : $this->oBlock,
                $oMeta->dataSource->method
            );
            $aResult = \call_user_func($aCallback, $mFieldName);
        } else {
            $aResult = null;
        }
        if (is_null($aResult)) {
            $aResult = \adduceToArray($oMeta->data);
        }
        return $aResult;
    } // function getFieldData

    /**
     * Set field data
     * @param string $mFieldName
     * @param mixed $mFieldValue
     * @return \fan\core\service\form
     */
    public function setFieldData($mFieldName, $mFieldValue)
    {
        $this->aFieldData[$mFieldName] = $mFieldValue;
        return $this;
    } // function setFieldData

    /**
     * Make field data for select, radio, checkbox e.g.
     * @param \fan\core\base\model\rowset $aRowset
     * @param string $sFormField
     * @param string $sTextKey
     * @param string $sValueKey
     */
    public function makeSelectTagData($aRowset, $sFormField, $sTextKey, $sValueKey = null)
    {
        if (empty($aRowset)) {
            return;
        }
        foreach ($aRowset as $oRow) {
            $this->aFieldData[$sFormField][] = array(
                'value' => $sValueKey ? $oRow->getOneField($sValueKey) : $oRow->getId(),
                'text'  => $oRow->getOneField($sTextKey),
            );
        }
    } // function makeFieldData

    /**
     * Get error message
     * @param mixed $mFieldName
     * @return string
     */
    public function getErrorMsg($mFieldName = null)
    {
        return empty($mFieldName) ? $this->aErrorMsg : array_get_element($this->aErrorMsg, $mFieldName, false);
    } // function getErrorMsg

    /**
     * Set flag of error
     */
    public function setError()
    {
        $this->bIsError = true;
    } // function setError

    /**
     * Get flag of error
     * @return boolean
     */
    public function isError()
    {
        return $this->bIsError;
    } // function isError

    /**
     * Check Form Role
     * @return boolean
     */
    public function checkFormRole()
    {
        $sRoleName = $this->oBlock->getRoleName();
        return empty($sRoleName) ? false : role($sRoleName);
    } // function checkFormRole


    // ======== Private/Protected methods ======== \\

    /**
     * Get form's meta data
     * @param string|array $mKey
     * @param mixed $mDefault
     * @return \fan\core\base\meta\row
     */
    protected function _getFormMeta($mKey, $mDefault = null)
    {
        return $this->aFormMeta->get($mKey, $mDefault);
    } // function _getFormMeta

    /**
     * Delete whitespaces from the beginning and end of value
     *
     * @param mixed $mValue
     * @param array $aParam
     * @return string
     */
    protected function _trimDataRecursive($mValue, $aParam, $sFieldName)
    {
        if (!is_null($mValue)) {
            if (is_array($mValue)) {
                foreach ($mValue as &$mSubValue) {
                    $mSubValue = $this->_trimDataRecursive($mSubValue, $aParam, $sFieldName);
                }
            } else {
                $mValue = empty($aParam['trim_data']) ? $mValue : trim($mValue);
                if (!empty($aParam['maxlength']) && strlen($mValue) > $aParam['maxlength']) {
                    trigger_error(
                            'Data has been truncated in the form "' . get_class($this->oBlock) . '" for field "' . $sFieldName . '". Length was ' . strlen($mValue) . '.',
                            E_USER_WARNING
                    );
                    $mValue = substr($mValue, 0, $aParam['maxlength']);
                }
                if (!isset($aParam['trim_tag']) || $aParam['trim_tag']) {
                    $aRepl = array(
                        '&'  => '&amp;',
                        '"'  => '&quot;',
                        '\'' => '&#039;',
                        '<'  => '&lt;',
                        '>'  => '&gt;',
                        '\\' => '\\\\',
                    );
                    $sCurRepl = $this->_getFormMeta('trim_tag_val', '&"\'<>');
                    for ($i = 0; $i < strlen($sCurRepl); $i++) {
                        if (isset($aRepl[$sCurRepl{$i}])) {
                            $mValue = str_replace($sCurRepl{$i}, $aRepl[$sCurRepl{$i}], $mValue);
                        }
                    }
                }
            }
        }
        return $mValue;
    } // function _trimDataRecursive

    /**
     * -----> ToDo: move it to validate\select <-----
     * Compare value with preset data
     * @param mixed $mValue
     * @param array $aParameters
     * @param array|string $mErrMesage
     */
    protected function _checkByData($sFieldName, $sLabel)
    {
        $aData = $this->getFieldData($sFieldName);
        if (!empty($aData)) {
            $mValue = array_val($this->aFieldValue, $sFieldName);
            if (isset($mValue)) {
                $aTmp = reset($aData);
                if (!array_diff_key($aTmp, array('value' => 0, 'text'  => 0))) {
                    foreach ($aData as $v) {
                        if (is_array($mValue) ? in_array($v['value'], $mValue) : $mValue == $v['value']) {
                            return true;
                        }
                    }
                    $this->bIsError = true;
                    trigger_error(
                            'Error in the form "' . get_class($this->oBlock) . '". Value of "' . $sFieldName . '" doesn\'t correspond to source.',
                            E_USER_WARNING
                    );
                    $this->aErrorMsg[$sFieldName] = $this->isMultiLanguage() ?
                            msg('ERROR_FIELD_HAS_INCORRECT_VALUE', $sLabel) :
                            msgAlt('Field "{combi_part}" has incorrect value', $sLabel);
                    return false;
                }
            }
        }
        return true;
    } // function _checkByData

    /**
     * Check - is Multi-value element
     * @param string $sInpType
     * @return boolean
     */
    protected function _isMultiVal($sInpType)
    {
        if (!isset($this->aFieldTypes[$sInpType])) {
            return false;
        }
        $sFieldType = $this->aFieldTypes[$sInpType];
        $aTypeMulty = adduceToArray($this->getConfig('MULTIVAL_TYPES', array('select_multi', 'select_multi_separated')));
        return in_array($sFieldType, $aTypeMulty);
    } // function _isMultiVal

    /**
     * functions which the element value check, if the element value is an array
     *
     * @param mixed $mValue
     * @param array $aParameters
     * @param array|string $mErrMesage
     */
    protected function _validateValueRecursive($mValue, $aParameters, &$mErrMesage, $nIndex = null)
    {
        $aUploads = $this->getConfig('UPLOAD_TYPES', array('file', 'file_multiple'));
        if(in_array(array_val($aParameters, 'input_type'), adduceToArray($aUploads))) {
            if (!empty($mValue['tmp_name']) && is_array($mValue['tmp_name'])) {
                foreach ($mValue['tmp_name'] as $iKey => $dummy) {
                    $aSubValue = array();
                    foreach ($mValue as $k => $v) {
                        $aSubValue[$k] = $v[$iKey];
                    }
                    $this->_validateValueRecursive($aSubValue, $aParameters, $mErrMesage[$iKey], $iKey);
                }
                return;
            }
            $isEmpty = empty($mValue['tmp_name']);
        } else {
            if (is_array($mValue) && !array_val($aParameters, 'group_rule', false)) {
                foreach ($mValue as $iKey => $aSubValue) {
                    $this->_validateValueRecursive($aSubValue, $aParameters, $mErrMesage[$iKey], $iKey);
                }
                return;
            }
            $isEmpty = is_array($mValue) ? empty($mValue) : $mValue == '';
        }

        // Check required value
        if (!empty($aParameters['is_required']) && $isEmpty) {
            $mErrMesage = $this->_getErrorMesage($this->_getFormMeta('required_msg'), $aParameters['label']);
            $this->bIsError = true;
        // Check value by specified rules
        } elseif (isset($aParameters['validate_rules'])) {
            foreach ($aParameters['validate_rules'] as $aRules) {
                if(!$isEmpty || empty($aRules['not_empty'])) {
                    $sRule = $aRules['rule_name'];
                    $oValidator = $this->_getValidator($sRule);
                    if (!empty($oValidator)) {
                        if(!$oValidator->$sRule($mValue, array_val($aRules, 'rule_data'), $nIndex)) {
                            $mErrMesage = isset($aRules['error_msg']) ?
                                    $this->_getErrorMesage($aRules['error_msg'], $aParameters['label']) :
                                    '';
                            $this->bIsError = true;
                            break;
                        }
                    }
                }
            }
        }
    } // function _validateValueRecursive

    /**
     * If the validation was successful the Transfer'll be performed on success url
     * @param string $sDbOper - DB operation: "commit" or "rollback"
     * @return \fan\core\service\form
     */
    protected function _onSubmitTransfer($sDbOper = null)
    {
        $aForm = $this->aFormMeta;
        if (!isset($aForm['redirect_required']) || $aForm['redirect_required']) {
            $oTab = $this->oBlock->getTab();
            if(empty($aForm['redirect_uri'])) {
                $sUri          = $oTab->getCurrentURI(true, true, strtoupper($aForm['action_method']) != 'GET', true);
                $sDefaultHttps = $oTab->getTabMeta('page_https');
            } else {
                $sUri          = $aForm['redirect_uri'];
                $sDefaultHttps = null;
            }
            transfer_out($sUri, null, $sDbOper);
        }
        return $this;
    } // function _onSubmitTransfer

    /**
     * Get error mesage with label name
     * @param string $sMsg
     * @param string $sLabel
     * @return string
     */
    protected function _getErrorMesage($sMsg, $sLabel)
    {
        return $this->isMultiLanguage() ?
                msg($sMsg, $sLabel) :
                msgAlt($sMsg, $sLabel);
    } // function _getErrorMesage

    /**
     * Get validator name
     * @param string $sValidatorName
     * @return string
     * @throws fatalException
     */
    protected function _getValidator($sValidatorName)
    {
        if (!isset($this->aValidators[$sValidatorName])) {
            if (method_exists($this->oBlock, $sValidatorName) && is_callable(array($this->oBlock, $sValidatorName))) {
                $this->aValidators[$sValidatorName] = $this->oBlock;
            } else {
                if (!isset($this->aValidatorClasses[$sValidatorName])) {
                    throw new fatalException($this, 'Unknown Validator Name "' . $sValidatorName . '".');
                }
                $sName = $this->aValidatorClasses[$sValidatorName];
                $oEngine = $this->_getEngine('validator\\' . $sName);
                $this->aValidators[$sValidatorName] = empty($oEngine) ? null : $oEngine->setFacade($this);
            }
        }
        return $this->aValidators[$sValidatorName];
    } // function _getValidator

    // ------------ Functions for main parts ------------ \\
    /**
     * Init Form Parts
     * Method is called in "init" of main part block
     * @param mixed $mData data for form parts
     */
    protected function _initFormParts($mData = NULL)
    {
        $aParts = $this->_getFormMeta('form_parts', array());
        foreach ($aParts as $v) {
            if ($this->oTab->isSetBlock($v)) {
                $this->getBlock($v)->partInit($mData);
            }
        }
    } // function _initFormParts

    /**
     * Init Form Parts
     * Method is called in "init" of main part block
     * @param mixed $bParceEmpty  allow parse if form is empty
     */
    protected function _parseFormParts($bParceEmpty)
    {
        $aParts = $this->_getFormMeta('form_parts', array());
        if (!empty($aParts)) {
            try {
                $aFieldValue = array();
                foreach ($aParts as $v) {
                    if ($this->oTab->isSetBlock($v)) {
                        $this->aPartFieldValue[$v] = $this->getBlock($v)->getForm()->parseForm($bParceEmpty, true, false);
                        $aFieldValue = array_merge_recursive_alt(
                            $aFieldValue,
                            $this->aPartFieldValue[$v]
                        );
                    }
                }
                $this->aFieldValue = array_merge_recursive_alt($this->aFieldValue, $aFieldValue);
            } catch (exception_error_form_part $e) {
                $this->bIsError = true;
                foreach ($e->getErrorMessages() as $k => $v) {
                    if (!empty($v)) {
                        if (empty($this->aErrorMsg[$k])) {
                            $this->aErrorMsg[$k] = $v;
                        } elseif (is_scalar($this->aErrorMsg[$k])) {
                            $this->aErrorMsg[$k] .= $v;
                        } else {
                            throw new fatalException($this, 'Can\'t set error message "' . $v . '".');
                        }
                    }
                }
            }
        }
    } // function _parseFormParts


} // class \fan\core\service\form
?>