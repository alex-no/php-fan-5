<?php namespace core\service\template\parser;
use project\exception\service\fatal as fatalException;
/**
 * Template parser engine base
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
abstract class base
{
    /**
     * @var \core\service\template Service template instance
     */
    protected $oFacade;

    /**
     * @var array Defined tpl-tag list
     */
    protected $aTagList = array();

    /**
     * @var array Automatic defined Tag List
     */
    protected $aAutoTagList = array();


    /**
     * Set Facade
     * @param core\service\template $oFacade
     */
    public function setFacade(\core\service\template $oFacade)
    {
        $this->oFacade = $oFacade;
    } // function setFacade

    /**
     * Parse form label
     * @param string $sData
     * @return string
     */
    public function __call($sMethod, $aArgs)
    {
        $sTagName = substr($sMethod, 6);
        $aAutoData = @$this->aAutoTagList[$sTagName];
        if(substr($sMethod, 0, 6) == 'parse_' && $aAutoData) {
            return '$sReturnHtmlVal.=$this->' . $aAutoData['method'] . '(' . $this->getDynamicArray($aArgs[0], @$aAutoData['require']) . ');' . "\n";
        }
        $aParseData = $this->oFacade->getParseData();
        throw new fatalException($this->oFacade, 'Call for undefined method "' . $sMethod . '".
At the file "' . $aParseData['template'] . '".
Near "' . $aParseData['part'] . '". At the file "');
    } // function __call

    /**
     * Get Tag List
     * @return array
     */
    public function getTagList()
    {
        return $this->aTagList;
    } // function getTagList

    /**
     * Set Auto-parse tag
     * @return array
     */
    public function setAutoTag($sTagName, $aData)
    {
        $this->aAutoTagList[$sTagName] = $aData;
        return true;
    } // function setAutoTag

    /**
     * Get Simple Parameters
     * @param string $sData
     * @return array
     */
    protected function getSimpleParam($sData)
    {
        $aRet = array();
        if (preg_match_all(\project\service\template::paramSimplePcre, $sData, $aMatches)) {
            foreach ($aMatches[0] as $i => $val) {
                $aRet[] = $aMatches[2][$i] == '"' ?
                    stripslashes(substr($aMatches[1][$i], 1, -1)) :
                    (empty($aMatches[2][$i]) && substr($aMatches[1][$i], 0, 1) != '$' ?
                        '\'' . $aMatches[1][$i] . '\'' :
                        $aMatches[1][$i]
                    );
            }
        }
        return $aRet;
    } // function getSimpleParam

    /**
     * Get Standard Parameters
     * @param string $sData
     * @param array $aRequire required parameters
     * @param array $aStrip list of name of parameters where quotes is need to strip
     * @return array
     */
    protected function getStandardParam($sData, $aRequire = array(), $aStrip = array())
    {
        $aRet = array();
        if (preg_match_all(\project\service\template::paramStandardPcre, $sData, $aMatches)) {
            foreach ($aMatches[0] as $i => $val) {
                $aRet[$aMatches[1][$i]] = $aMatches[3][$i] == '"' ?
                    stripslashes(substr($aMatches[2][$i], 1, -1)) :
                    (empty($aMatches[3][$i]) && substr($aMatches[2][$i], 0, 1) != '$' ?
                        '\'' . $aMatches[2][$i] . '\'' :
                        $aMatches[2][$i]
                    );
            }
        }
        if ($aRequire) {
            foreach ($aRequire as $k) {
                if (!isset($aRet[$k])) {
                    $aParseData = $this->oFacade->getParseData();
                    throw new fatalException($this->oFacade, 'Undefined required control key "' . $k . '".
At the file "' . $aParseData['template'] . '".
Near "' . $aParseData['part'] . '". At the file "');
                }
            }
        }
        foreach ($aStrip as $k) {
            if (isset($aRet[$k]) && substr($aRet[$k], 0, 1) == '\'') {
                $aRet[$k] = substr($aRet[$k], 1, -1);
            }
        }
        return $aRet;
    } // function getStandardParam

    /**
     * Get dynamic array for custom function
     * @param string $sData
     * @param array $aRequire required parameters
     * @param array $aStrip list of name of parameters where quotes is need to strip
     * @return string
     */
    protected function getDynamicArray($sData, $aRequire = array())
    {
        if (!$sData) {
            return '';
        }
        $sRet = 'array(';
        foreach ($this->getStandardParam($sData, $aRequire) as $k => $v) {
            $sRet .= '\'' . $k . '\'=>' . $v . ',';
        }
        return substr($sRet, 0, -1) . ')';
    } // function getDynamicArray

} // class \core\service\template\parser\base
?>