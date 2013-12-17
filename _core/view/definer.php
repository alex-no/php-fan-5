<?php namespace core\view;
/**
 * Definer type of View
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
 * @version of file: 05.002 (17.12.2013)
 */
class definer
{
    /**
     * Regexp for parse key string of rule
     */
    const RULE_REG_EXP = '/([ACEFGHMPRS]+)\.(.+?)\.([binsr])\.(\d{1,2})/';
    /**
     * Regexp for check value of numeric type
     */
    const NUM_REG_EXP = '/^(\=\=|\!\=|\>\=?|\<\=?)?(\-?[0-9]+(\.[0-9]+)?)$/';

    /**
     * @var array
     */
    protected $aExprMaker = array(
        's' => '_getStringExpr',
        'i' => '_getIntegerExpr',
        'n' => '_getNumericExpr',
        'b' => '_getBooleanExpr',
        'r' => '_getRegexpExpr',
    );

    /**
     * @var array
     */
    protected $aConfig = array();
    /**
     * @var array
     */
    protected $aConditions = null;
    /**
     * @var \core\service\request
     */
    protected $oRequest = null;

    /**
     * View-definer constructor
     */
    public function __construct(array $aConfig)
    {
        $this->aConfig = $aConfig;
        if (empty($this->aConfig['default_format'])) {
            $this->aConfig['default_format'] = 'html';
        }
    } // function __construct
    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    /**
     * Get suffix of class-name of View-parcer
     * @return string
     */
    public function getViewParserName()
    {
        $aConditions = $this->_getConditions();
        foreach ($aConditions as $k1 => $v1) {
            foreach ($v1 as $v2) {
                if (eval($v2)) {
                    return $k1;
                }
            }
        }
        $oTab = service('tab');
        /* @var $oTab \core\service\tab */
        return $oTab->getTabMeta('default_view_format', $this->aConfig['default_format']);
    } // function getViewParserName
    // ======== Private/Protected methods ======== \\
    /**
     * Get Conditions
     * @return array
     */
    public function _getConditions()
    {
        if (is_null($this->aConditions)) {
            $this->oRequest    = \project\service\request::instance();
            $this->aConditions = array();
            $aMatches    = null;
            foreach ($this->aConfig['rule'] as $k1 => $v1) { // $k1 - view format
                foreach ($v1 as $k2 => $v2) { // $k2 - index of rule // $v2 - string of rule
                    if (preg_match_all(self::RULE_REG_EXP, $v2, $aMatches)) {
                        $aSearch = $aReplace = array(); // Array Search/Replace for convert check rule to execute
                        foreach ($aMatches[3] as $k3 => $v3) { // $k3 - index of rule-string // $v3 - element of rule-string
                            // Data sources
                            $sSrc = '\'' . $aMatches[1][$k3] . '\'';
                            // Data key
                            $sKey = '\'' . addcslashes($aMatches[2][$k3], '\\\'') . '\'';

                            // Validate Value for rule
                            $mVal = $this->_getConditionValue(array_val($aMatches, array(4, $k3)), $v3, $v2);
                            if (is_null($mVal)) {
                                continue 2;
                            }

                            // Expression for Validate
                            $sMethod = array_val($this->aExprMaker, $v3);
                            if (empty($sMethod) || !method_exists($this, $sMethod)) {
                                trigger_error('Unknown metod key <b>' . $v3 . '</b><br /> for VIEW-rule:<br /><b>' . $v2 . '</b>.', E_USER_WARNING);
                                continue 2;
                            }
                            $sExpr = $this->$sMethod($sKey, $sSrc, $mVal);

                            $aSearch[$k3]  = $aMatches[0][$k3];
                            $aReplace[$k3] = $sExpr;
                        }
                        $sFinalExpr = str_replace($aSearch, $aReplace, $v2) . ';';
                        if (@eval('$_xxx = ' . $sFinalExpr . ' return true;')) {
                            $this->aConditions[$k1][$k2] = 'return ' . $sFinalExpr;
                        } else {
                            trigger_error('Incorrect PHP-expression:<br /><b>' . $sFinalExpr . '</b><br /> in VIEW-rule:<br /><b>' . $v2 . '</b>.', E_USER_WARNING);
                        }
                    } else {
                        trigger_error('Can\'t parse VIEW-rule:<br /><b>' . $v2 . '</b>.', E_USER_WARNING);
                    }
                }
            }
        }
        return $this->aConditions;
    } // function _getConditions

    /**
     * Get Condition Value for validation
     * @param mixed $mVal
     * @param string $v3
     * @param string $v2
     * @return null
     */
    protected function _getConditionValue($mVal, $v3, $v2)
    {
        if (is_null($mVal)) {

        }
        if ($v3 == 'b') {
            return (boolean)$mVal;
        }
        if ($v3 == 'i') {
            return (integer)$mVal;
        }
        // Need data value
        if (!isset($this->aConfig['value'][$mVal])) {
            trigger_error('Value doesn\'t set for VIEW-rule:<br /><b>' . $v2 . '</b>.', E_USER_WARNING);
            return null;
        }
        $mVal = addcslashes($this->aConfig['value'][$mVal], '\\\'');
        if ($v3 == 'n' && !preg_match(self::NUM_REG_EXP, $mVal)) {
            trigger_error('Incorrect value:<br /><b>' . $mVal . '</b><br /> for VIEW-rule:<br /><b>' . $v2 . '</b>.', E_USER_WARNING);
            return null;
        }
        return $mVal;
    } // function _getConditionValue

    /**
     * Get Validation Expression by String
     * @param string $sKey
     * @param string $sSrc
     * @param mixed $mVal
     * @return type
     */
    protected function _getStringExpr($sKey, $sSrc, $mVal)
    {
        return '$this->oRequest->get(' . $sKey . ', ' . $sSrc . ')==\'' . $mVal . '\'';
    } // function _getStringExpr

    /**
     * Get Validation Expression by Integer value
     * @param string $sKey
     * @param string $sSrc
     * @param mixed $mVal
     * @return type
     */
    protected function _getIntegerExpr($sKey, $sSrc, $mVal)
    {
        return '(integer)$this->oRequest->get(' . $sKey . ', ' . $sSrc . ')==' . $mVal;
    } // function _getIntegerExpr

    /**
     * Get Validation Expression by Numeric condition
     * @param string $sKey
     * @param string $sSrc
     * @param mixed $mVal
     * @return type
     */
    protected function _getNumericExpr($sKey, $sSrc, $mVal)
    {
        $aMatches = array();
        preg_match(self::NUM_REG_EXP, $mVal, $aMatches);
        return '$this->oRequest->get(' . $sKey . ', ' . $sSrc . ')' . (empty($aMatches[1]) ? '==' : $aMatches[1]) . $aMatches[2];
    } // function _getNumericExpr

    /**
     * Get Validation Expression by Boolean value
     * @param string $sKey
     * @param string $sSrc
     * @param mixed $mVal
     * @return type
     */
    protected function _getBooleanExpr($sKey, $sSrc, $mVal)
    {
        return ($mVal ? '' : '!') . '(bool)$this->oRequest->get(' . $sKey . ', ' . $sSrc . ')';
    } // function _getBooleanExpr

    /**
     * Get Validation Expression by Regexp
     * @param string $sKey
     * @param string $sSrc
     * @param mixed $mVal
     * @return type
     */
    protected function _getRegexpExpr($sKey, $sSrc, $mVal)
    {
        return 'preg_match(\'' . $mVal . '\', $this->oRequest->get(' . $sKey . ', ' . $sSrc . '))';
    } // function _getRegexpExpr

} // class \core\view\definer
?>