<?php namespace fan\core\service;
/**
 * Template manager service
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
class template extends \fan\core\base\service\single
{
    /**
     * Constant of PCRE for plain code
     */
    const plainPcre   = '/(.*?)(?:\{(?:(\@?)(\$)|(\-\>)|\=)([^\}]+)\s*\}|$)/s';

    /**
     * Constant of PCRE for control structure
     */
    const controlPcre = '/(.*?)(?:\{(?:({TAG_LIST})(?:\s+([^\}]*))?|(else)|\/(if|for|foreach))\s*\}|$)/s';

    /**
     * Constant of PCRE for simple parameters
     */
    const paramSimplePcre   = '/\s*(([\'\"])?(?(2).+?(?<!\\\\)\2|\$?\w+(?:\[[^\]]+\])*))(?:\s+|$)/si';

    /**
     * Constant of PCRE for standard parameters (as hash)
     */
    const paramStandardPcre = '/([a-z_0-9]+)\s*\=\s*(([\'\"])?(?(3).+?(?<!\\\\)\3|\$?\w+(?:\[[^\]]+\])*))(?:\s+|$)/si';

    /**
     * List of corresspond tags to Engines
     * @var array
     */
    protected $aTags = array();

    /**
     * List of literals
     * @var array
     */
    protected $aLiterals = array();

    /**
     * Parse data
     * @var array
     */
    protected $aParseData = array();

    /**
     * @var boolean Auto strip
     */
    private $sTemplateClass = '<?php namespace {NAMESPACE};
//SRC: {SOURCE_PATH}
class {CLASS_NAME} extends {PARENT_CLASS}{
protected function parseHtml(){
foreach($this->aTplVar as $sAssignTplKey=>$sAssignTplVal){$$sAssignTplKey =& $this->aTplVar[$sAssignTplKey];}
$sReturnHtmlVal = \'\';
{COMPILE_CODE}
return $sReturnHtmlVal;}
{ADD_METHODS}
}
?>';

    /**
     * Get object of block template
     * @param string $sTemplatePath
     * @param \fan\core\block\base $mBlock
     * @param string $sParent
     * @return template_base
     */
    public function get($sTemplatePath, $sParent = null, $mBlock = null)
    {
        list ($sNameSpace, $sClassName, $sFileName) = $this->_getClassAttributes($sTemplatePath, $mBlock);

        $sCompilePath = \bootstrap::parsePath($this->getConfig('CACHE_DIR')) . $sFileName . '.php';
        if (!file_exists($sCompilePath) || filemtime($sCompilePath) <  filemtime($sTemplatePath)) {
            $this->_makeClass($sTemplatePath, $sCompilePath, $sNameSpace, $sClassName, ($sParent ? $sParent : $this->getConfig('PARENT_CLASS')));
        }
        require_once $sCompilePath;
        $sClassName = '\\' . $sNameSpace . '\\' . $sClassName;
        return new $sClassName($mBlock);
    } // function get

    /**
     * Get object of block template
     * @return array
     */
    public function getParseData()
    {
        return $this->aParseData;
    } // function getParseData

    /**
     * Disable Strip-operation for template
     * @param boolean $bAllowStrip
     */
    public function disableStrip($bAllowStrip = false)
    {
        $this->oConfig['USE_STRIP'] = !empty($bAllowStrip);
    } // function disableStrip

    // ------------------------- \\

    /**
     * Parse "literal" code
     * @param string $sData
     * @return string
     */
    protected function parse_literal($sData)
    {
        return @$this->aLiterals[$sData] ? '$sReturnHtmlVal.=' . $this->_addSlashes($this->aLiterals[$sData]) . ";\n" : '';
    } // function parse_literal

    /**
     * Parse plain code
     * @param string $sData
     * @return string
     */
    protected function parse_plain($sData)
    {
        $sRet = '';
        if (preg_match_all(self::plainPcre, $sData, $aMatches)) {
            foreach ($aMatches[0] as $i => $val) {
                if (!empty($aMatches[1][$i])) {
                    $sRet .=  $this->_addSlashes($aMatches[1][$i]) . '.';
                }
                if (!empty($aMatches[5][$i])) {
                    $sRet .= '(';
                    if (!empty($aMatches[3][$i])) {
                        $sRet .= @$aMatches[2][$i] . '$';
                    } elseif (!empty($aMatches[4][$i])) {
                        $sRet .= '$this->';
                    }
                    $sRet .= $aMatches[5][$i] . ').';
                }
            }
        }
        return $sRet ? '$sReturnHtmlVal.=' . substr($sRet, 0, -1) . ";\n" : '';
    } // function parse_plain

    // ------------------------- \\

    /**
     * Get Attributes of Class
     * @param string $sTemplatePath
     * @param \fan\core\block\base $mBlock
     * @return array
     */
    protected function _getClassAttributes($sTemplatePath, $mBlock)
    {
        if (is_object($mBlock) || is_string($mBlock)) {
            $sMainName = get_class_name($mBlock);
        } else {
            $sMainName = basename($sTemplatePath, '.tpl');
            $sMainName = preg_replace('/\W/', '_', $sMainName);
        }
        $sClassName = $sMainName . '__' . substr(md5($sTemplatePath), 0, $this->getConfig('UNIQUE_KEY_LENGH'));
        $sFileName  = $sClassName;

        $sNameSpace = $this->getConfig('NameSpace');

        return array($sNameSpace, $sClassName, $sFileName);
    } // function _getClassAttribute

    /**
     * Make of compiled template class
     * @param string $sTemplatePath
     * @param string $sCompilePath
     * @param string $sNameSpace
     * @param string $sClassName
     * @param string $sType
     */
    protected function _makeClass($sTemplatePath, $sCompilePath, $sNameSpace, $sClassName, $sType)
    {
        $this->aParseData['template'] = $sTemplatePath;
        // Prepare engines
        foreach (call_user_func(array($sType, 'getEngineList')) as $sEngineName) {
            $oEngine = $this->_getEngine('parser\\' . $sEngineName);
            foreach ($oEngine->getTagList() as $sTagName) {
                $this->aTags[$sTagName] = $oEngine;
            }
        }
        foreach (call_user_func(array($sType, 'getAutoParseTag')) as $sTagName => $aAutoData) {
            if (!isset($this->aTags[$sTagName]) && $oEngine->setAutoTag($sTagName, $aAutoData)) {
                $this->aTags[$sTagName] = $oEngine;
            }
        }
        $this->aTags['literal'] = $this;

        $sSrcCode = file_get_contents($sTemplatePath);
        // Get methods
        $sAddMethods = '';
        if (preg_match_all('/\{method:\s*(.*?)[\n\r\s]+endmethod\}/s', $sSrcCode, $aMatches)) {
            foreach ($aMatches[0] as $i => $v) {
                $sAddMethods .= $aMatches[1][$i];
                $sSrcCode = str_replace($aMatches[0][$i], '', $sSrcCode);
            }
        }

        // Remove comments
        $sSrcCode = preg_replace('/\{\*.*?\*\}/s', '', $sSrcCode);

        // Save literals
        if (preg_match_all('/\{literal\}(.*?)\{\/literal\}/s', $sSrcCode, $aMatches)) {
            foreach ($aMatches[0] as $i => $v) {
                $this->aLiterals[$i] = $aMatches[1][$i];
                $sSrcCode = str_replace($aMatches[0][$i], '{literal ' . $i . '}', $sSrcCode);
            }
        }
        // Strip code, except "nostrip"
        if ($this->getConfig('USE_STRIP')) {
            $aNoStrip = array();
            if (preg_match_all('/\{nostrip\}(.*?)\{\/nostrip\}/s', $sSrcCode, $aMatches)) {
                foreach ($aMatches[0] as $i => $v) {
                    $k = '{nostrip ' . $i . '}';
                    $aNoStrip[$k] = $aMatches[1][$i];
                    $sSrcCode = str_replace($aMatches[0][$i], $k, $sSrcCode);
                }
            }
            $sSrcCode = preg_replace('/\s{2,}/', ' ', str_replace(array("\r", "\n"), array('', ' '), $sSrcCode));
            foreach ($aNoStrip as $k => $v) {
                $sSrcCode = str_replace($k, $v, $sSrcCode);
            }
        }
        // get Compile Code by other tags
        $sCode = '';
        if (preg_match_all(str_replace('{TAG_LIST}', implode('|', array_keys($this->aTags)), self::controlPcre), $sSrcCode, $aMatches)) {
            foreach ($aMatches[0] as $i => $val) {
                $this->aParseData['part'] = $val;
                if (!empty($aMatches[1][$i])) {
                    $sCode .= $this->parse_plain($aMatches[1][$i]);
                }
                if (!empty($aMatches[2][$i])) {
                    $sCode .= call_user_func(array($this->aTags[$aMatches[2][$i]], 'parse_' . $aMatches[2][$i]), @$aMatches[3][$i]);
                }
                if (!empty($aMatches[4][$i])) {
                    $sCode .= $aMatches[4][$i] . ":\n";
                }
                if (!empty($aMatches[5][$i])) {
                    $sCode .= 'end' . $aMatches[5][$i] . ";\n";
                }
            }
        }
        $sCode = str_replace(array('{ldelim}', '{rdelim}'), array('{', '}'), $sCode);

        // Save file
        file_put_contents($sCompilePath, str_replace(array('{SOURCE_PATH}', '{NAMESPACE}', '{CLASS_NAME}', '{PARENT_CLASS}', '{COMPILE_CODE}', '{ADD_METHODS}'), array($sTemplatePath, $sNameSpace, $sClassName, $sType, $sCode, $sAddMethods), $this->sTemplateClass));
        $this->aTags = $this->aLiterals = array();
    } // function _makeClass

    /**
     * Add Slashes
     * @param string $sData
     * @return string
     */
    protected function _addSlashes($sData)
    {
        return '\'' . str_replace(array('\\', '\''), array('\\\\', '\\\''), $sData) . '\'';
    } // function addSlashes
} // class \fan\core\service\template
?>