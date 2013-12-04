<?php namespace core\service\template\parser;
/**
 * Template parser engine main
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
class main extends base
{
    /**
     * @var array Defined tpl-tag list
     */
    protected $aTagList = array('assign', 'if', 'elseif', 'foreach', 'for', 'msg', 'get_url');

    /**
     * Parse assign code
     * @param string $sData
     * @return string
     */
    public function parse_assign($sData)
    {
        $aParam = $this->getStandardParam($sData, array('var', 'value'), array('var'));
        $sCode = '';
        if (preg_match('/^[a-z]\w+/i', $aParam['var'], $aMatches)) {
            $sCode .= '$' . $aMatches[0] . '=&$this->linkForAssign(\'' . $aMatches[0] . '\');';
        }
        return $sCode . '$' . $aParam['var'] . '=' . $aParam['value'] . ';' . "\n";
    } // function parse_assign

    /**
     * Parse "if" code
     * @param string $sData
     * @return string
     */
    public function parse_if($sData)
    {
        return 'if (' . $sData . "):\n";
    } // function parse_if

    /**
     * Parse "elseif" code
     * @param string $sData
     * @return string
     */
    public function parse_elseif($sData)
    {
        return 'elseif (' . $sData . "):\n";
    } // function parse_elseif

    /**
     * Parse "foreach" code
     * @param string $sData
     * @return string
     */
    public function parse_foreach($sData)
    {
        $aParam = $this->getStandardParam($sData, array('from', 'item'), array('item', 'key'));
        $sCode = empty($aParam['name']) ? '' : '$this->setObjectData(\'foreach\', ' . $aParam['name'] . ', ' . $aParam['from'] . ');';
        $sCode .= 'foreach (' . $aParam['from'] . ' as ' . (empty($aParam['key']) ? '' : '$' . $aParam['key'] . '=>') . '$' . $aParam['item'] . '):' . "\n";
        if (!empty($aParam['name'])) {
            $sCode .= '$this->setIteration(' . $aParam['name'] . ');' . "\n";
        }
        return $sCode;
    } // function parse_foreach

    /**
     * Parse "for" code
     * @param string $sData
     * @return string
     */
    public function parse_for($sData)
    {
        $aParam = $this->getStandardParam($sData);
        $sCode = empty($aParam['name']) ? '' : '$this->setObjectData(\'for\', ' . $aParam['name'] . ');';
        $sCode .= 'for (' . (empty($aParam['start']) ? '' : $aParam['start']) . ';';
        $sCode .= (empty($aParam['condition']) ? '' : $aParam['condition']) . ';';
        $sCode .= (empty($aParam['each']) ? '' : $aParam['each']) . '):' . "\n";
        if (!empty($aParam['name'])) {
            $sCode .= '$this->setIteration(' . $aParam['name'] . ');' . "\n";
        }
        return $sCode;
    } // function parse_for

    /**
     * Parse "msg" code
     * @param string $sData
     * @return string
     */
    public function parse_msg($sData)
    {
        return '$sReturnHtmlVal.=msg(' . implode(',', $this->getSimpleParam($sData)) . ");\n";
    } // function parse_if

    /**
     * Parse getURI
     * @param string $sData
     * @return string
     */
    public function parse_getURI($sData)
    {
        return $this->parse_get_url($sData);
    } // function parse_getURI

    /**
     * Parse get_url
     * @param string $sData
     * @return string
     */
    public function parse_get_url($sData)
    {
        $aParam = $this->getStandardParam($sData, array('url'), array('type'));
        if (!isset($aParam['type'])) {
            $aParam['type'] = 'link';
        }
        $aParam['use_sid']  = array_key_exists('use_sid',  $aParam) ? (empty($aParam['use_sid'])  ? 'false' : 'true') : 'null';
        $aParam['protocol'] = array_key_exists('protocol', $aParam) ? (empty($aParam['protocol']) ? 'false' : 'true') : 'null';
        return '$sReturnHtmlVal.=\project\service\tab::instance()->getURI(' . $aParam['url'] . ',\'' . $aParam['type'] . '\',' . $aParam['use_sid'] . ',' . $aParam['protocol'] . ");\n";
    } // function parse_get_url

} // class \core\service\template\parser\main
?>