<?php namespace fan\core\service\template\type;
/**
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
 * @version of file: 05.02.006 (20.04.2015)
 */
abstract class form extends base
{
    /**
     * Regexp for parse combi field name
     */
    const RE_NAME = '/^(\w+)((?:\[[^\]]+\])+)$/';

    /**
     * Form service
     * @var \fan\core\service\form
     */
    protected $oForm;

    /**
     * @var array Numbers of Each Form
     */
    private static $aFormNumber = array();

    /**
     * @var integer Curent Form Number
     */
    private $iCurNum;

    /**
     * Template variables
     * @var \fan\core\base\meta\row
     */
    protected $oFormMeta;

    /**
     * @var array Template variables
     */
    protected $aFieldType;

    /**
     * @var integer Index of Separated Select
     */
    protected $iSeparateInd = 0;

    /**
     * @var array of used Tab Indexes
     */
    private $aTabIndex = array();

    /**
     * @var boolean Is Multi-language
     */
    protected $bMultiLng;

    /**
     * Template block constructor
     * @param \fan\core\block\form\usual $oBlock
     */
    public function __construct(\fan\core\block\form\usual $oBlock)
    {
        parent::__construct($oBlock);

        $this->oForm     = $oBlock->getForm();
        $this->oFormMeta = $oBlock->getFormMeta();
        foreach (array('input', 'checking', 'select', 'select_separated', 'select_multi', 'select_multi_separated') as $sType) {
            foreach ($this->_getFormMeta(array('design', $sType), array()) as $k => $v) {
                $this->aFieldType[$k] = $sType;
            }
        }

        for ($i = $this->oFormMeta['formNumber']; $i < 500; $i++) {
            if (!isset(self::$aFormNumber[$i])) {
                self::$aFormNumber[$i] = $this->sBlockName;
                $this->iCurNum = $i;
                break;
            }
        }

        $this->bMultiLng = $this->oForm->isMultiLanguage();
    } // function __construct

    /**
     * Get Engine List
     * @return array
     */
    public static function getEngineList()
    {
        return array('main', 'form');
    } // function getEngineList

    /**
     * Get Auto-parse data
     * @return array
     */
    public static function getAutoParseTag()
    {
        return array(
            'form_row'    => array('method' => 'getFormRow',  'require' => array('name')),
            'form_label'  => array('method' => 'getLabel',    'require' => array('name')),
            'form_field'  => array('method' => 'getField',    'require' => array('name')),
            'form_error'  => array('method' => 'getErrorMsg', 'require' => array('name')),
            'form_note'   => array('method' => 'getNote',     'require' => array()),
            'form_button' => array('method' => 'getButton',   'require' => array('text')),
        );
    } // function getAutoParseTag

    /**
     * Get hidden field form key
     * @return string
     */
    public function getKeyField()
    {
        $sKeyValue = $this->oFormMeta['form_id'];
        $nCsrfLen  = (integer)$this->oFormMeta['csrf_protection'];
        if ($nCsrfLen >= 4) {
            $sCsrfCode = substr(md5(microtime() . $sKeyValue), 0, min(32, $nCsrfLen));
            service('session', array($sKeyValue, 'form_key'))->set('csrf', $sCsrfCode);
            $sKeyValue .= '_' . $sCsrfCode;
        }
        return '<input type="hidden" name="form_key_field" value="' . $sKeyValue . '" />' . $this->getSidField();
    } // function getKeyField

    /**
     * Get hidden field SID if cookies is disabled
     * @return string
     */
    public function getSidField()
    {
        $oSes = \fan\project\service\session::instance();
        return $oSes->isByCookies() ? '' : '<input type="hidden" name="' . $oSes->getSessionName() . '" value="' . $oSes->getSessionId() .'" />';
    } // function getSidField

    /**
     * Get Form Row
     * @param array $aData
     * @return string
     */
    public function getFormRow($aData)
    {
        $sPattern = $this->_getFormMeta(array(
            'design',
            'formRow',
            empty($aData['type']) ? $this->_getFormMeta(array('default_type', 'formRow')) : $aData['type'],
        ), '<div>{LABEL}{FORM_FIELD}{ERROR}{NOTE}</div>');
        $aFind = $aReplace = array();
        foreach (array(
            array('{LABEL}',      'getLabel',    'label_type'),
            array('{FORM_FIELD}', 'getField',    'field_type'),
            array('{ERROR}',      'getErrorMsg', 'error_type'),
            array('{NOTE}',       'getNote',     'note_type')
        ) as $v) {
            if (strstr($sPattern, $v[0])) {
                $aFind[]    = $v[0];
                $sMethod    = $v[1];
                $aReplace[] = $this->$sMethod(array_merge($aData, array('type' => array_val($aData, $v[2]))));
            }
        }
        return str_replace($aFind, $aReplace, $sPattern);
    } // function getFormRow

    /**
     * Get Form Label
     * @param array $aData
     * @return string
     */
    public function getLabel($aData)
    {
        $aFieldMeta = $this->_getFieldMeta($aData);
        if (empty($aFieldMeta['label'])) {
            return '#!Label isn\'t set!#';
        }

        $bRequired = false;
        if (!empty($aFieldMeta['is_required'])) {
            $bRequired = true;
        } elseif (isset($aFieldMeta['validate_rules'])){
            foreach ($aFieldMeta['validate_rules'] as $aRule){
                if (!empty($aRule['rule_name']) && $aRule['rule_name'] == 'is_required'){
                    $bRequired = true;
                    break;
                }
            }
        }

        $sType     = empty($aData['type']) ? $this->_getFormMeta(array('default_type', 'label')) : $aData['type'];
        $aPatterns = $this->_getFormMeta(array('design', 'label'));
        $sPattern  = $bRequired && isset($aPatterns[$sType . '_required']) ? $aPatterns[$sType . '_required'] : array_val($aPatterns, $sType, '<span>{LABEL}:</span>');

        return str_replace('{LABEL}', $this->_getMsgByLng($aFieldMeta['label'], $aData), $sPattern);
    } // function getLabel

    /**
     * Get Form Error Message
     * @param array $aData
     * @return string
     */
    public function getErrorMsg($aData)
    {
        $mError = $this->oForm->getErrorMsg($aData['name']);
        if(empty($mError)) {
            $mError = $this->oForm->getErrorMsg($this->_parseCombiName($aData));
        }
        if(empty($mError)) {
            return '';
        }
        $sPattern = $this->_getFormMeta(array(
            'design',
            'error',
            empty($aData['type']) ? $this->_getFormMeta(array('default_type', 'error')) : $aData['type'],
        ), '<div>{TEXT}</div>');

        if (!is_array($mError)) {
            $sText = $mError;
        } elseif (isset($aData['index'])) {
            $sText = array_val($mError, $aData['index'], '');
        } else {
            $sText = '';
            foreach ($mError as $v) {
                if (!empty($sText) && !empty($v)) {
                    $sText .= '<br />';
                }
                $sText .= $v;
            }
        }
        return str_replace('{TEXT}', $sText, $sPattern);
    } // function getErrorMsg

    /**
     * Get Form Note
     * @param array $aData
     * @return string
     */
    public function getNote($aData)
    {
        $sText = array_val($aData, 'note');
        if(empty($sText)) {
            $sText = $this->_getFieldMeta($aData, 'note');
        }
        if(empty($sText)) {
            return '';
        }

        $bMultiLng = array_val($aData, 'multiLng', $this->bMultiLng);
        $sPattern  = $this->_getFormMeta(array(
            'design',
            'note',
            empty($aData['type']) ? $this->_getFormMeta(array('default_type', 'note')) : $aData['type'],
        ), '<div>{TEXT}</div>');

        return str_replace(array(
            '{NOTE}',
            '{TEXT}'
        ), array(
            $this->_getMsgByLng($bMultiLng ? 'NOTE_FORM_ROW' : 'Note', $aData),
            $this->_getMsgByLng($sText, $aData)
        ), $sPattern);
    } // function getNote

    /**
     * Get Form Button
     * @param array $aData
     * @return string
     */
    public function getButton($aData)
    {
        $sPattern = $this->_getFormMeta(array(
            'design',
            'button',
            empty($aData['type']) ? $this->_getFormMeta(array('default_type', 'button')) : $aData['type'],
        ), '<input type="submit"{NAME} value="{VALUE}"{TABINDEX} />');
        return str_replace(' name=""', '', str_replace(array(
            '{TEXT}',
            '{NAME}',
            '{ID}',
            '{VALUE}',
            '{CLASS}',
        ), array(
            $this->_getMsgByLng($aData['text'], $aData),
            isset($aData['name'])  ? ' name="' . $aData['name'] . '"' : '',
            empty($aData['id'])    ? '' : 'id="' . $aData['id'] . '"',
            array_val($aData, 'value', 1),
            isset($aData['class']) ? ' class="' . $aData['class'] . '"' : '',
        ), $this->_setAttributes($sPattern, $aData)));
    } // function getButton

    /**
     * Get Form element for Input/Select data
     * @param array $aData
     * @return string
     */
    public function getField($aData)
    {
        if (empty($aData['type'])) {
            $aData['type'] = $this->_getFieldMeta($aData, 'input_type');
        }

        if (!isset($this->aFieldType[$aData['type']])) {
            return '<!-- Undefined field type -->';
        }
        switch ($this->aFieldType[$aData['type']]) {
        case 'input':
            $sMetod = 'getInput';
            break;
        case 'checking':
            $sMetod = 'getChecking';
            break;
        case 'select':
        case 'select_multi':
            $sMetod = 'getSelect';
            break;
        case 'select_separated':
        case 'select_multi_separated':
            $sMetod = 'getSeparatedSelect';
            break;
        }
        return $this->$sMetod($aData);
    } // function getField

    /**
     * Get Form element for Input data
     * @param array $aData
     * @return string
     */
    public function getInput($aData)
    {
        $sPattern = $this->_getFormMeta(
                array('design', 'input', empty($aData['type']) ? 'text' : $aData['type']),
                '<input type="text" name="{NAME}" value="{VALUE}"{MAXLENGTH}{ATTRIBUTES}{TABINDEX} />'
        );

        $sMaxLength = $this->_getFieldMeta($aData, 'maxlength');
        if (!empty($sMaxLength)) {
            $sMaxLength = ' maxlength="' . (int)$sMaxLength . '"';
            unset($aData['attributes']['maxlength']);
        }
        $mVal = $this->_getFieldValue($aData);
        return str_replace(array(
            '{NAME}',
            '{ID}',
            '{VALUE}',
            '{MAXLENGTH}',
        ), array(
            $aData['name'],
            empty($aData['id']) ? '' : ' id="' . $this->_getIdByName($aData['name']) . '"',
            is_scalar($mVal) ? $mVal : '',
            $sMaxLength,
        ), $this->_setAttributes($sPattern, $aData));
    } // function getInput

    /**
     * Get Form element for checkbox or radio
     * @param array $aData
     * @return string
     */
    public function getChecking($aData)
    {
        $sPattern = $this->_getFormMeta(
                array('design', 'checking', empty($aData['type']) ? 'checkbox' : $aData['type']),
                '<input type="text" name="{NAME}" value="1"{CHECKED}{ATTRIBUTES}{TABINDEX} />'
        );
        //$aFldMeta = $this->_getFieldMeta($aData);
        $mVal = $this->_getFieldValue($aData);
        return str_replace(array(
            '{NAME}',
            '{ID}',
            '{CHECKED}',
        ), array(
            $aData['name'],
            empty($aData['id']) ? '' : 'id="' . $this->_getIdByName($aData['name']) . '"',
            empty($mVal)        ? '' : ' checked="checked"',
        ), $this->_setAttributes($sPattern, $aData));
    } // function getChecking

    /**
     * Get Form element for Select data
     * @param array $aData
     * @return string
     */
    public function getSelect($aData)
    {
        $sFieldType = isset($this->aFieldType[$aData['type']]) ? $this->aFieldType[$aData['type']] : null;
        if (!$sFieldType || $sFieldType == 'input') {
            $sFieldType = 'select';
        }
        $sPattern = $this->_getFormMeta(
                array('design', $sFieldType, empty($aData['type']) ? 'select' : $aData['type']),
                '<select name="{NAME}"{ATTRIBUTES}{TABINDEX}>[<option value="{VALUE}"{SELECTED}>{TEXT}</option>]</select>'
        );
        $aMatches = array();
        if (preg_match('/^(?:[^\[]+|\[\])*\[(.+?)(?<!\[)\].*$/', $sPattern, $aMatches)) {
            $sSubPattern = $aMatches[1];
            $sPattern = str_replace('[' . $sSubPattern . ']', '{SUB_PATTERN}', $sPattern);
        } else {
            $sSubPattern = '';
        }

        $mVal = $this->_getFieldValue($aData);
        $mFdt = $this->_getFieldData($aData);
        return  str_replace(array(
            '{NAME}',
            '{ID}',
            '{SUB_PATTERN}',
        ), array(
            $aData['name'],
            empty($aData['id']) ? '' : 'id="' . $this->_getIdByName($aData['name']) . '"',
            empty($sSubPattern) ? '' : $this->_parseSubPattern(
                    $sSubPattern,
                    (is_scalar($mVal) || $sFieldType == 'select_multi') ? $mVal : '',
                    $mFdt,
                    $aData,
                    $sFieldType
            ),
        ), $this->_setAttributes($sPattern, $aData));
    } // function getSelect

    /**
     * Get Form element for Select data
     * @param array $aData
     * @return string
     */
    public function getSeparatedSelect($aData)
    {
        $sFieldType = isset($this->aFieldType[$aData['type']]) ? $this->aFieldType[$aData['type']] : null;
        $sPattern   = $this->_getFormMeta(
                array('design', $sFieldType, empty($aData['type']) ? 'checkbox_alone' : $aData['type']),
                '<input type="checkbox" name="{NAME}[]" id="{ID}" value="{VALUE}"{CHECKED}{ATTRIBUTES}{TABINDEX} />'
        );

        $mFdt = $this->_getFieldData($aData);
        if (is_scalar($mFdt)) {
            return 'Incorrect data';
        }

        if (empty($mFdt[$this->iSeparateInd++])) {
            return '';
        }
        $nInd = $this->iSeparateInd - 1;
        $mCdt = $mFdt[$nInd];
        $mCdt['value'] = array_val($mCdt, 'value');
        $mCdt['text']  = array_val($mCdt, 'text');

        $mVal = $this->_getFieldValue($aData, array());
        if ($sFieldType == 'select_multi_separated' && !is_array($mVal)) {
            $mVal = array();
        }

        return  str_replace(array(
            '{NAME}',
            '{ID}',
            '{VALUE}',
            '{TEXT}',
            '{CHECKED}',
        ), array(
            $aData['name'],
            $this->_getIdByName($aData['name']) . '_' . ($nInd),
            array_val($mCdt, 'value'),
            array_val($mCdt, 'text'),
            isset($mCdt['value']) && ($sFieldType == 'select_separated' ? $mCdt['value'] == $mVal : in_array($mCdt['value'], $mVal)) ?
                ' checked="checked"' :
                '',
        ), $this->_setAttributes($sPattern, $aData));
    } // function getSeparatedSelect


    // ---------------------------------------------------- \\

    /**
     * Get text by langeage setting
     * @param string $sText - sourse text
     * @param array $aData - tag data
     * @return mixed - value of meta var
     */
    protected function _getMsgByLng($sText, $aData)
    {
        return array_val($aData, 'multiLng', $this->bMultiLng) ? msg($sText) : $sText;
    } // function _getMsgByLng

    /**
     * Preliminary parsing of "select"
     * @param string $sSubPattern
     * @param string $mValue
     * @param mixed $mFdt
     * @param array $aData
     * @param string $sFieldType
     */
    protected function _parseSubPattern($sSubPattern, $mValue, $mFdt, $aData, $sFieldType)
    {
        $sRet = '';
        if (is_array($mFdt)) {
            foreach ($mFdt as $k =>$d) {
                $d['value'] = array_val($d, 'value');
                $d['text']  = array_val($d, 'text');
                $bSelected = !is_null($mValue) && ($sFieldType == 'select_multi' ? in_array($d['value'], adduceToArray($mValue)) : strcmp($d['value'], $mValue) == 0);
                $sRet .= str_replace(array(
                    '{NAME}',
                    '{ID}',
                    '{VALUE}',
                    '{TEXT}',
                    '{SELECTED}',
                    '{CHECKED}',
                ), array(
                    $aData['name'],
                    $this->_getIdByName($aData['name']) . '_' . $k,
                    $d['value'],
                    $d['text'],
                    ($bSelected ? ' selected="selected"' : ''),
                    ($bSelected ? ' checked="checked"' : ''),
                ), $this->_setAttributes($sSubPattern, $aData));
            }
        }
        return $sRet;
    } // function _parseSubPattern

    /**
     * Set Attributes in the Pattern
     * @param string $sPattern
     * @param array $aData
     * @param boolean $bIsTabInd
     * @return string
     */
    protected function _setAttributes($sPattern, $aData, $bIsTabInd = true)
    {
        if ($bIsTabInd) {
            $sPattern = $this->_setTabIndex($sPattern, $aData);
        }
        $sAttrRepl = '';
        if (!empty($aData['name'])) {
            $aAttr = $this->_getFieldMeta($aData, 'attributes', array_val($aData, 'attributes'));
            if ($aAttr) {
                foreach ($aAttr as $k => $v) {
                    $sAttrRepl .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        return str_replace('{ATTRIBUTES}', $sAttrRepl, $sPattern);
    } // function _setAttributes

    /**
     * Set current TabIndex in the Pattern
     * @param string $sPattern
     * @param array $aData
     * @return string
     */
    protected function _setTabIndex($sPattern, $aData)
    {
        if (!strstr($sPattern, '{TABINDEX}') || (isset($aData['tabindex']) && empty($aData['tabindex']))) {
            return $sPattern;
        }
        $nTabIndex = array_val($aData, 'tabindex', empty($this->aTabIndex) ? 1 : max($this->aTabIndex) + 1);
        if (in_array($nTabIndex, $this->aTabIndex)) {
            trigger_error('Duplicate TabIndex ' . $nTabIndex . ' at the form "' . $this->sBlockName . '".', E_USER_NOTICE);
        } else {
            $this->aTabIndex[] = $nTabIndex;
        }
        return str_replace('{TABINDEX}', ' tabindex="' . ($this->iCurNum * 100 + $nTabIndex) . '"', $sPattern);
    } // function _setTabIndex

    /**
     * Get Id By Name (replace "[" and "]" to "_")
     * @param string $sName
     * @return string
     */
    protected function _getIdByName($sName)
    {
        return str_replace(']', '', str_replace('[', '_', str_replace('][', '_', $sName)));
    } // function _getIdByName

    /**
     * Get meta-element variable value
     * @param mixed $mKey - key of var
     * @param mixed $mDefault - default value
     * @return mixed - value of meta var
     */
    protected function _getFormMeta($mKey, $mDefault = null)
    {
        if (is_array($mKey)) {
            $aDest = array_get_element($this->oFormMeta, $mKey, false);
            return is_null($aDest) ? $mDefault : $aDest;
        }
        return array_val($this->oFormMeta, $mKey, $mDefault);
    } // function _getFormMeta

    /**
     * Get Meta-data by combi Field-name
     * @param array $aData
     * @param string $sKey
     * @param mixed $mDefault - default value
     * @return mixed
     */
    protected function _getFieldMeta($aData, $sKey = null, $mDefault = null)
    {
        $sName = $aData['name'];
        $aMatches = array();
        for ($i = 0; $i < 2; $i++) {
            $aKey = is_null($sKey) ? array('fields', $sName) : array('fields', $sName, $sKey);
            $mVal = $this->_getFormMeta($aKey);
            if (!is_null($mVal)) {
                return $mVal;
            }
            if (!empty($i) || !preg_match(self::RE_NAME, $sName, $aMatches)) {
                break;
            }
            $sName = $aMatches[1];
        }
        return $mDefault;
    } // function _getFieldMeta

    /**
     * Get Value of field by combi Field-name
     * @param array $aData
     * @param mixed $mDefault - default value
     * @return mixed
     */
    protected function _getFieldValue($aData, $mDefault = null)
    {
        $mVal = $this->oForm->getFieldValue($this->_parseCombiName($aData), false);
        return is_null($mVal) ? $mDefault : $mVal;
    } // function _getFieldValue

    /**
     * Get Data of field by combi Field-name
     * @param array $aData
     * @param mixed $mDefault - default value
     * @return mixed
     */
    protected function _getFieldData($aData, $mDefault = null)
    {
        $mName = $aData['name'];
        $aMatches = array();
        for ($i = 0; $i < 2; $i++) {
            $mVal = $this->oForm->getFieldData($mName);
            if (!is_null($mVal)) {
                return $mVal;
            }
            if (!empty($i) || !preg_match(self::RE_NAME, $mName, $aMatches)) {
                break;
            }
            $mName = $aMatches[1];
        }
        return $mDefault;
    } // function _getFieldData

    protected function _parseCombiName($aData)
    {
        $mName = $aData['name'];
        $aMatches = array();
        if (preg_match(self::RE_NAME, $mName, $aMatches)) {
            $mName = explode('][', substr($aMatches[2], 1, -1));
            array_unshift($mName, $aMatches[1]);
        }
        return $mName;
    } // function _getFieldValue

} // class \fan\core\service\template\type\form
?>
