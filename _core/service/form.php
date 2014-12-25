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
 * @version of file: 05.02.004 (25.12.2014)
 */
class form extends \fan\core\base\service\multi
{
    /**
     * Regexp for parse combi field name
     */
    const RE_COMBI = '/([^\[]+)?\[([^\]]+)\]/';

    /**
     * @var array Service's Instances
     */
    private static $aInstances = array();

    /**
     * @var \fan\core\block\form\parser
     */
    protected $oBlock;

    /**
     * @var \fan\core\base\meta\row
     */
    protected $oFormMeta;
    /**
     * Field description from meta data
     * @var array
     */
    protected $oFieldMeta = array();
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
     * @param \fan\core\block\form\parser $oBlock
     */
    protected function __construct(\fan\core\block\form\parser $oBlock)
    {
        parent::__construct(empty(self::$aInstances));

        $this->oBlock     = $oBlock;
        $this->oFormMeta  = $oBlock->getFormMeta();
        if (empty($this->oFormMeta)) {
            throw new fatalException($this, 'Form meta isn\'t set for block "' . get_class($oBlock) . '".');;
        }
        $this->oFieldMeta = $this->oFormMeta->get('fields');
        if (empty($this->oFieldMeta)) {
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

        $this->_presetFieldValue();

        self::$aInstances[$oBlock->getBlockName()] = $this;
    } // function __construct

    // ======== Static methods ======== \\
    /**
     * Get instance of form
     * @param \fan\core\block\form\parser $oBlock
     * @return \fan\core\service\form
     */
    public static function instance(\fan\core\block\form\parser $oBlock)
    {
        $sName = $oBlock->getBlockName();
        if (!isset(self::$aInstances[$sName])) {
            new self($oBlock);
        }
        return self::$aInstances[$sName];
    } // function instance

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
        // Check - is need to validate this form
        while ($this->necessaryFormParsing($bParsingCondition, true)) {
            // Get data for parsing
            $this->_defineFieldValue();

            if (!$bParceEmpty) {
                $bIsEmpty = true;
                foreach ($this->aFieldValue as $v) {
                    if (!empty($v)) {
                        $bIsEmpty = false;
                        break;
                    }
                }
                if ($bIsEmpty) {
                    break;
                }
            }
            // Validate data
            if ($this->oBlock->checkBeforeValidation()) {
                foreach ($this->oFieldMeta as $sFieldName => $aParameters) {
                    if (!empty($aParameters['not_check_by_data']) || $this->_autoCheckByData($sFieldName, isset($aParameters['label']) ? $aParameters['label'] : $sFieldName)) {
                        if (!array_key_exists($sFieldName, $this->aErrorMsg)) {
                            $this->aErrorMsg[$sFieldName] = null;
                        }
                        $this->_validateValueRecursive($sFieldName);
                    }
                }
            }

            $this->_parseFormParts($bParceEmpty);

            // Processing after validation
            if ($this->oBlock->checkAfterValidation()) {
                $sRoleName = $this->oBlock->getRoleName();

                if ($this->bIsError) {
                    service('role')->killSessionRoles($sRoleName);
                    $this->_broadcastMessage('onError', $this->oBlock);

                    break;
                } else {
                    // Set form roles
                    if (!$this->_getFormMeta('not_role')) {
                        service('role')->setFixQttRoles($sRoleName);
                    }
                    // Remove CSRF-protection code from session
                    if ((integer)$this->_getFormMeta('csrf_protection') >= 4) {
                        service('session', array(
                            $this->_getFormMeta('form_id'),
                            'form_key'
                        ))->remove('csrf');
                    }

                    //ToDo: Clear cache of some blocks there
                    //\fan\project\service\cache::instance()->clear($this->aFormMeta->get(array('cache', 'clear')));

                    $this->_broadcastMessage('onSubmit', $this->oBlock);

                    $this->_onSubmitTransfer(
                            $bAllowTransfer,
                            'commit',
                            strtoupper($this->_getFormMeta('action_method', 'POST')) != 'GET'
                    );
                    break;
                }
            }
            break;
        }
        return !$this->bIsError;
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
                $sCsrfCode   = service('session', array($sSrcKeyVal, 'form_key'))->get('csrf');
                if (empty($sCsrfCode)) {
                    $this->bIsError = true;
                    // ToDo: error message for user
                    return false;
                }
                $sSrcKeyVal .= '_' . $sCsrfCode;
            }
            $sKeyField = $oRequest->get('form_key_field', $sRequestType);
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
     * Preparing the string for javascript validation
     * @return string
     */
    public function strForJsValidation()
    {
        $sStr = '';
        $sReqMsg = $this->_getFormMeta($this->isMultiLanguage() ? 'required_msg' : 'required_msg_alt');
        foreach ($this->oFieldMeta as $sFieldName => $aParameters) {
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
            $oRoot  = service('tab')->getTabBlock('root');
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
        $sMsg = addslashes($this->_getErrorMesage($sLabel, $sMsg));
        return str_replace("\n", '\n', $sMsg);
    } // function reduceMessage

    /**
     * Get array field value
     * @param mixed $mFieldName
     * @return mixed
     */
    public function getFieldValue($mFieldName = null, $bUseSubform = true)
    {
        $aFieldValue = $this->aFieldValue;
        if ($bUseSubform) {
            foreach ($this->aPartFieldValue as $v) {
                $aFieldValue = array_merge_recursive_alt($v, $aFieldValue);
            }
        }
        if (empty($mFieldName)) {
            return $aFieldValue;
        }
        $bIsArray = is_array($mFieldName);
        $sValue   = $bIsArray ? array_get_element($aFieldValue, $mFieldName, false) : array_val($aFieldValue, $mFieldName);
        return $sValue;
    } // function getFieldValue

    /**
     * Set field value
     * @param mixed $mFieldName
     * @param mixed $mValue
     * @return \fan\core\service\form
     */
    public function setFieldValue($mFieldName, $mValue)
    {
        if ($this->_checkName($mFieldName)) {
            $this->aFieldValue[$mFieldName] = $mValue;
        }
        return $this;
    } // function setFieldValue

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

        $oMeta = $this->_getFormMeta(array('fields', $mKey));
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
     * @param mixed $mFieldData
     * @return \fan\core\service\form
     */
    public function setFieldData($mFieldName, $mFieldData)
    {
        $this->aFieldData[$mFieldName] = $mFieldData;
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
     * Check Depth of data
     * @param mixd $mVal
     * @param numeric $nDepth
     * @return boolean
     */
    public function checkDepth($mVal, $nDepth)
    {
        if (empty($nDepth)) {
            return is_scalar($mVal);
        }
        if (!is_array($mVal)) {
            return false;
        }
        foreach ($mVal as $v) {
            if (!$this->checkDepth($v, $nDepth - 1)) {
                return false;
            }
        }
        return true;
    } // function checkDepth

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

    /**
     * Compare value with data (can be used in validate-functions)
     * @param mixed $mValue
     * @param array $aData
     */
    public function checkByData($mValue, $aData)
    {
        $aTmp  = reset($aData);
        if (!array_diff_key($aTmp, array('value' => 0, 'text'  => 0))) {
            foreach ($aData as $v) {
                if (is_array($mValue) ? in_array($v['value'], $mValue) : $mValue == $v['value']) {
                    return true;
                }
            }
            return false;
        }
        return true;
    } // function checkByData


    // ======== Private/Protected methods ======== \\

    protected function _presetFieldValue()
    {
        foreach ($this->oFieldMeta as $k => $v) {
            if (preg_match_all(self::RE_COMBI, $k, $aMatch)) {
                $mKey  = array_merge(array($aMatch[1][0]), $aMatch[2]);
                $aDest =& array_get_element($this->aFieldValue, $mKey, true);
                if (is_null($aDest)) {
                    $aDest = $v->get('default_value', null);
                }
            } elseif (!isset($this->aFieldValue[$k])) {
                $this->aFieldValue[$k] = $v->get('default_value', null);
            }
        }
        return $this;
    } // function _presetFieldValue

    /**
     * Get form's meta data
     * @param string|array $mKey
     * @param mixed $mDefault
     * @return \fan\core\base\meta\row
     */
    protected function _getFormMeta($mKey, $mDefault = null, $bConvToArray = false)
    {
        $oMeta = $this->oFormMeta->get($mKey, $mDefault);
        return $bConvToArray && is_object($oMeta) && $oMeta instanceof \fan\core\base\meta\row ? $oMeta->toArray() : $oMeta;
    } // function _getFormMeta

    /**
     * get the form elements' values from HTTP request
     *
     */
    protected function _defineFieldValue()
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

        foreach ($this->oFieldMeta as $sFieldName => $aParameters) {
            $nDepth = $aMatch = null;
            $aComplex = array();
            if (preg_match_all(self::RE_COMBI, $sFieldName, $aMatch)) {
                // Complex field name, like: "foo[bar]", "foo[bar1][bar2]", etc
                $sBaseName = $aMatch[1][0];
                if (empty($sBaseName)) {
                    throw new fatalException($this, 'Incorrect Field Name "' . $sFieldName . '" - empty base-name.');
                }
                $mVal      = $oRequest->get($sBaseName, $sRequestType);
                $mCheckVal = array_val($this->aFieldValue, $aMatch[1][0]);
                foreach($aMatch[2] as $v) {
                    $aComplex[] = $v;
                    $mCheckVal  = array_val($mCheckVal, $v);
                    if (isset($mVal[$v])) {
                        $mVal = $mVal[$v];
                    } else {
                        $mVal = null;
                        break;
                    }
                }
                if (!is_null($mVal) && $mVal == $mCheckVal) {
                    continue;
                }
            } else {
                // Simple field name, like "foo"
                $sBaseName = $sFieldName;
                $mVal = $oRequest->get($sBaseName, $sRequestType);
            }

            // Check is file
            $aUploads = $this->getConfig('UPLOAD_TYPES', array('file', 'file_multiple'));
            if (in_array($aParameters['input_type'], adduceToArray($aUploads))) {
                $nDepth = empty($aParameters['depth']) ? 1 : $aParameters['depth'] + 1;
                if ($nDepth > 1 && !empty($mVal)) {
                    $aTmp = array();
                    foreach ($mVal as $k => $v) {
                        if (is_array($v)) {
                            $this->_transformFileData($aTmp, $k, $v);
                        }
                    }
                    $mVal = $aTmp;
                } else {
                    $mVal = empty($mVal) || $mVal['error'] == UPLOAD_ERR_NO_FILE ? null : $mVal;
                }
                $bIsFile = true;
            } else {
                $bIsFile = false;
            }

            $mKey  = empty($aComplex) ? $sBaseName : array_merge(array($sBaseName), $aComplex);
            $aDest =& array_get_element($this->aFieldValue, $mKey, true);
            $aDest = null;

            $aErr  =& $this->aErrorMsg[$sFieldName];
            $aErr  = null;

            if (!is_null($mVal)) {
                if (is_null($nDepth)) {
                    $nDepth = empty($aParameters['depth']) ? 0 : $aParameters['depth'];
                    if ($this->_isMultiVal($aParameters['input_type'])) {
                        $nDepth++;
                    }
                }

                if (($nDepth > 0 && count($aComplex) == $nDepth) || $this->checkDepth($mVal, $nDepth)) {
                    $aDest = $bIsFile ? $mVal : $this->_trimDataRecursive($mVal, $sBaseName);
                } else {
                    trigger_error(
                            'Field "' . $aParameters['label'] . '" of form "' . get_class_alt($this->oBlock) . '" has incorrect depth of value.',
                            E_USER_WARNING
                    );
                    $aErr = $this->_getErrorMesage($aParameters['label'], 'ERROR_FIELD_HAS_INCORRECT_DEPTH', 'Field "{combi_part}" has incorrect depth of value');
                }
            }
        }
        return $this;
    } // function _defineFieldValue

    /**
     * Delete whitespaces from the beginning and end of value
     *
     * @param mixed $mValue
     * @param array $aParam
     * @return string
     */
    protected function _trimDataRecursive($mValue, $sFieldName, $aIndex = array())
    {
        if (!is_null($mValue)) {
            if (is_array($mValue)) {
                foreach ($mValue as $k => &$v) {
                    $v = $this->_trimDataRecursive($v, $sFieldName, array_merge($aIndex, array($k)));
                }
            } else {
                $aParam  = $this->_getCombiParam($sFieldName, $aIndex);
                $mValue  = empty($aParam['trim_data']) ? $mValue : trim($mValue);

                $nMaxLen = empty($aParam['maxlength']) ? 0 : $aParam['maxlength'];
                $nLen    = function_exists('mb_strlen') ? mb_strlen($mValue) : strlen($mValue);
                if (!empty($nMaxLen) && $nLen > $nMaxLen) {
                    trigger_error(
                            'Data has been truncated in the form "' . get_class_alt($this->oBlock) . '" for field "' . $sFieldName . '". Length was ' . $nLen . '.',
                            E_USER_WARNING
                    );
                    $mValue = function_exists('mb_substr') ? mb_substr($mValue, 0, $nMaxLen) : substr($mValue, 0, $nMaxLen);
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
     * Compare value with preset data
     * @param mixed $mValue
     * @param array $aParameters
     * @param array|string $mErrMesage
     */
    protected function _autoCheckByData($sFieldName, $sLabel)
    {
        $aData = $this->getFieldData($sFieldName);
        if (!empty($aData)) {
            $mValue = array_val($this->aFieldValue, $sFieldName);
            if (isset($mValue)) {
                if (!$this->checkByData($mValue, $aData)) {
                    $this->bIsError = true;
                    trigger_error(
                            'Error in the form "' . get_class_alt($this->oBlock) . '". Value of "' . $sFieldName . '" doesn\'t correspond to source.',
                            E_USER_WARNING
                    );
                    $this->aErrorMsg[$sFieldName] = $this->_getErrorMesage($sLabel, 'ERROR_FIELD_HAS_INCORRECT_VALUE', 'Field "{combi_part}" has incorrect value');
                   return false;
                }
            }
        }
        return true;
    } // function _autoCheckByData

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
     * Validate elements value recursively
     * @param string $sFieldName
     * @param array $aIndex
     * @return \fan\core\service\form
     */
    protected function _validateValueRecursive($sFieldName, $aIndex = array())
    {
        $aMatch = null;
        if (preg_match_all(self::RE_COMBI, $sFieldName, $aMatch)) {
            $mKey = array_merge(array($aMatch[1][0]), $aMatch[2]);
        } else {
            $mKey = empty($aIndex) ? $sFieldName : array($sFieldName);
        }

        $mValue = $this->getFieldValue(empty($aIndex) ? $mKey : array_merge($mKey, $aIndex), false);
        $aParam = $this->_getCombiParam($sFieldName, $aIndex);

        $aUploads = $this->getConfig('UPLOAD_TYPES', array('file', 'file_multiple'));
        if(in_array(array_val($aParam, 'input_type'), adduceToArray($aUploads))) {
            // ToDo: Value by index
            if (!empty($mValue['tmp_name']) && is_array($mValue['tmp_name'])) {
                foreach ($mValue['tmp_name'] as $k => $v) {
                    $this->_validateValueRecursive($sFieldName, array_merge($aIndex, array($k)));
                }
                return;
            }
            $isEmpty = empty($mValue['tmp_name']);
        } else {
            if (is_array($mValue)) {
                foreach ($mValue as $k => $v) {
                    $this->_validateValueRecursive($sFieldName, array_merge($aIndex, array($k)));
                }
                return;
            }
            $isEmpty = is_array($mValue) ? empty($mValue) : $mValue == '';
        }

        $mErrMesage =& $this->aErrorMsg[$sFieldName];
        foreach ($aIndex as $i) {
            if (!isset($mErrMesage[$i])) {
                $mErrMesage[$i] = null;
            }
            $mErrMesage =& $mErrMesage[$i];
        }

        // Check required value
        if (!empty($aParam['is_required']) && $isEmpty) {
            $mErrMesage = $this->_getErrorMesage($aParam['label'], $this->_getFormMeta('required_msg'));
            $this->bIsError = true;
        // Check value by specified rules
        } elseif (isset($aParam['validate_rules'])) {
            foreach ($aParam['validate_rules'] as $aRules) {
                if(!$isEmpty || empty($aRules['not_empty'])) {
                    $sRule = $aRules['rule_name'];
                    $oValidator = $this->_getValidator($sRule);
                    if (!empty($oValidator)) {
                        if(!$oValidator->$sRule($mValue, array_val($aRules, 'rule_data'), $aIndex)) {
                            $mErrMesage = isset($aRules['error_msg']) ?
                                    $this->_getErrorMesage($aParam['label'], $aRules['error_msg']) :
                                    '';
                            $this->bIsError = true;
                            break;
                        }
                    }
                }
            }
        }
        return $this;
    } // function _validateValueRecursive

    /**
     * If the validation was successful the Transfer'll be performed on success url
     * @param boolean $bAllowTransfer
     * @param string $sDbOper - DB operation: "commit" or "rollback"
     * @param boolean $bAddQueryStr
     * @return \fan\core\service\form
     */
    protected function _onSubmitTransfer($bAllowTransfer, $sDbOper, $bAddQueryStr)
    {
        $bRedirReq = $this->_getFormMeta('redirect_required');
        $sRedirUri = $this->_getFormMeta('redirect_uri');

        if (is_null($bAllowTransfer)) {
            $bAllowTransfer = is_null($bRedirReq) ?
                    strtoupper(service('request')->get('REQUEST_METHOD', 'S')) == 'POST' :
                    !empty($bRedirReq);
        } elseif ($bAllowTransfer) {
            $bAllowTransfer = is_null($bRedirReq) || !empty($bRedirReq);
        }


        if ($bAllowTransfer) {
            if(empty($sRedirUri)) {
                service('request')->remove('form_key_field', 'G');
                $oTab = $this->oBlock->getTab();
                $sUri = $oTab->getCurrentURI($this->isMultiLanguage(), true, $bAddQueryStr, true);
            } else {
                $sUri = $sRedirUri;
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
    protected function _getErrorMesage($sLabel, $sMsg, $sAltMsg = null)
    {
        if (empty($sAltMsg)) {
            $sAltMsg = $sMsg;
        }
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

    protected function _checkName($sFieldName, $bReportErr = true)
    {
        if (isset($this->oFieldMeta[$sFieldName])) {
            return true;
        }
        if ($bReportErr) {
            trigger_error('Call incorrect field name "' . $sFieldName . '" in the form "' . $this->oBlock->getBlockName() . '"');
        }
        return false;
    } // function _getValidator

    // ------------ Functions for main parts ------------ \\

    /**
     * Parse Form Parts
     * @param mixed $bParceEmpty allow parse if form is empty
     */
    protected function _parseFormParts($bParceEmpty)
    {
        $aParts = $this->_getFormMeta('form_parts', array());
        if (!empty($aParts)) {
            try {
                $oTab = service('tab');
                /* @var $oTab \fan\core\service\tab */
                foreach ($aParts as $v) {
                    if ($oTab->isSetBlock($v)) {
                        $oSubForm = $oTab->getTabBlock($v)->getForm();
                        if ($oSubForm->parseForm($bParceEmpty, true, false)) {
                            $this->aPartFieldValue[$v] = $oSubForm->getFieldValue();
                        } else {
                            $this->bIsError = true;
                        }
                    }
                }
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

    /**
     * Get Combi Parameters
     * @param string $sFieldName
     * @param array $aIndex
     * @return array
     */
    protected function _getCombiParam($sFieldName, $aIndex)
    {
        $sCombiKey   = empty($aIndex) ? null : $sFieldName . '[' . implode('][', $aIndex) . ']';
        $aMainParam  = isset($this->oFieldMeta[$sFieldName]) ? adduceToArray($this->oFieldMeta[$sFieldName]) : array();
        $aExtraParam = isset($this->oFieldMeta[$sCombiKey])  ? adduceToArray($this->oFieldMeta[$sCombiKey])  : array();
        return array_merge_recursive_alt($aMainParam, $aExtraParam);
    } // function _getCombiParam

    /**
     * Transform Data from File-array
     * @param array $aData
     * @param string $sKey
     * @param array $aSrc
     * @return \fan\core\service\form
     */
    protected function _transformFileData(&$aData, $sKey, $aSrc)
    {
        foreach ($aSrc as $k => $v) {
            if (is_array($v)) {
                $aData[$k] = array();
                $this->_transformFileData($aData[$k], $sKey, $v);
            } else {
                $aData[$k][$sKey] = $v;
            }
        }
        return $this;
    } // function _transformFileData

    // ======== The magic methods ======== \\

    /**
     * Magic set method for data-array
     * @param mixed $sKey
     * @param mixed $mValue
     */
    public function __set($sKey, $mValue)
    {
        $this->setFieldValue($sKey, $mValue);
    } // function __set

    /**
     * Magic get method for data-array
     * @param mixed $sKey
     * @return mixed
     */
    public function __get($sKey)
    {
        return $this->getFieldValue($sKey);
    } // function __get

    /**
     * Magic isset method for data-array
     * @param mixed $sKey
     * @return boolean
     */
    public function __isset($sKey)
    {
        return isset($this->aFieldValue[$sKey]);
    } // function __isset
    // ======== Required Interface methods ======== \\

} // class \fan\core\service\form
?>