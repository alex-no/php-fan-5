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
 * @version of file: 05.001 (29.09.2011)
 */
class definer extends \core\base\data
{
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
        if (empty($this->aConfig['default_type'])) {
            $this->aConfig['default_type'] = 'html';
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
    public function getViewSuffix()
    {
        $aConditions = $this->_getConditions();
        foreach ($aConditions as $k1 => $v1) {
            foreach ($v1 as $v2) {
                if (eval($v2)) {
                    return $k1;
                }
            }
        }
        return $this->aConfig['default_type'];
    } // function getViewSuffix
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
            foreach ($this->aConfig['rule'] as $k1 => $v1) {
                foreach ($v1 as $k2 => $v2) {
                    if (preg_match_all('/([ACEFGHMPRS]+)\.(.+?)\.([binsr])\.(\d{1,2})/', $v2, $aMatches)) {
                        $aSearch = $aReplace = array();
                        foreach ($aMatches[3] as $k3 => $v3) {
                            // Data sources
                            $sSrc = '\'' . $aMatches[1][$k3] . '\'';
                            // Data key
                            $sKey = '\'' . addcslashes($aMatches[2][$k3], '\\\'') . '\'';
                            if (in_array($v3, array('n', 's', 'r'))) {
                                // Need data value
                                $mVal = $this->_getConditionValue($aMatches, $k3, $v2);
                                if (is_null($mVal)) {
                                    continue 2;
                                }

                            }
                            switch ($v3) {
                            case 'b':
                                $sExpr  = $aMatches[4][$k3] ? '' : '!';
                                $sExpr .= '(bool)$this->oRequest->get(' . $sKey . ', ' . $sSrc . ')';
                                break;
                            case 'i':
                                $sExpr  = '$this->oRequest->get(' . $sKey . ', ' . $sSrc . ')';
                                $sExpr .= '==' . $aMatches[4][$k3];
                                break;
                            case 'n':
                                if (!preg_match('/^(\=\=?|\>\=?|\<\=?)?(\-?[0-9\.]+)$/', $mVal, $aValMatches)) {
                                    trigger_error('Incorrect value:<br /><b>' . $mVal . '</b><br /> for VIEW-rule:<br /><b>' . $v2 . '</b>.', E_USER_WARNING);
                                    continue 2;
                                }
                                $sExpr  = '$this->oRequest->get(' . $sKey . ', ' . $sSrc . ')';
                                $sExpr .= (empty($aValMatches[1]) || $aValMatches[1] == '=' ? '==' : $aValMatches[1]) . $aValMatches[2];
                                break;
                            case 's':
                                $sExpr  = '$this->oRequest->get(' . $sKey . ', ' . $sSrc . ')';
                                $sExpr .= '==\'' . $mVal . '\'';
                                break;
                            case 'r':
                                $sExpr  = 'preg_match(\'' . $mVal . '\', ';
                                $sExpr .= '$this->oRequest->get(' . $sKey . ', ' . $sSrc . '))';
                                break;
                            }
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

    public function _getConditionValue($aMatches, $k3, $v2)
    {
        $k = $aMatches[4][$k3];
        if (!isset($this->aConfig['value'][$k])) {
            trigger_error('Value doesn\'t set for VIEW-rule:<br /><b>' . $v2 . '</b>.', E_USER_WARNING);
            return null;
        }
        return addcslashes($this->aConfig['value'][$k], '\\\'');
    } // function _getConditions

} // class \core\view\definer
?>