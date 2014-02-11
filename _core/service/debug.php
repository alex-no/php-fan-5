<?php namespace core\service;
/**
 * debug manager service
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
 * @version of file: 05.006 (11.02.2014)
 */
class debug extends \core\base\service\single {

    /**
     * @var \core\service\tab
     */
    protected $oTab;
    /**
     * @var HTML-code of blocks
     */
    protected $aBlockCode;

    protected function __construct($bAllowIni = true)
    {
        parent::__construct($bAllowIni);

        $this->oTab = \project\service\tab::instance();
        $this->oConfig['ENABLED'] = $this->isEnabled() && preg_match($this->getConfig('DEBUG_IP', '/^127\.0\.0\.1$/'), @$_SERVER['SERVER_ADDR']);
    } // function __construct

    /**
     * Set external files (css and js)
     */
    public function setExtFiles($oRoot, $nMode)
    {
        if ($this->isEnabled()) {
            if (method_exists($oRoot, 'setExternalCss')) {
                $oRoot->setExternalCss($this->getConfig('CSS_CONTROL',  '/__debug_trace/css/debug_control.css'));
                if ($nMode) {
                    $oRoot->setExternalCss($this->getConfig('CSS_DEBUG0',  '/__debug_trace/css/debAcug_common.css'));
                    $oRoot->setExternalCss($this->getConfig('CSS_DEBUG1',  '/__debug_trace/css/debug_mode1.css'));
                }
            }
            if (method_exists($oRoot, 'setExternalJs')) {
                $oRoot->setExternalJs($this->getConfig('JS_WRAPPER', '/js/js-wrapper.js'));
                $oRoot->setExternalJs($this->getConfig('JS_FILE',    '/__debug_trace/js/debug_trace.js'));
                $oRoot->setExternalJs('/js/debug.js');
            }
            if (method_exists($oRoot, 'setEmbedJs')) {
                $oRoot->setEmbedJs('debug_trace.init(' . $nMode . ');');
                $oRoot->setEmbedJs('basicBroadcaster.prototype.config.DebugMode = true', 'head', -1);
            }
        }
    } // function setExtFiles

    /**
     * Set external files (css and js)
     */
    public function setBlockCode($sName, $sCode)
    {
        $this->aBlockCode[$sName] = $sCode;
    } // function setBlockCode

    /**
     * Wrap html-code
     * @param string $sCode
     * @param \core\block\base $oBlock
     * @return string
     */
    public function wrapHtmlCode($sCode, $oBlock)
    {
        if (!$this->isEnabled()) {
            return $sCode;
        }
        $sIntColor = $oBlock->getBlockName() == 'main' ? $this->getConfig('BORDER_MAIN', '#6600FF') : $this->getConfig('BORDER_INT', '#7F7971');

        $sCode = '<div><div style="background-color: ' . $sIntColor . '; color: ' . $this->getConfig('HEAD_TEXT', '#D6D1CA') . ';" class="debug_header"><b>' . $oBlock->getMeta('initOrder', $this->oTab->getDefaultInitNum()) . ':</b> ' . $oBlock->getBlockName() . '</div>' . $this->_getBlockDetail($oBlock) . '</div>' . $sCode;

        $sCode = '<div class="debug_block">' . $sCode . '</div>';

        return $sCode;
    } // function wrapHtmlCode

    /**
     * Wrap html-code
     * @param block_html_root_base $oRoot
     * @return string
     */
    public function getSecondDebugCode($sBlockInfo, $sTitle)
    {
        return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>' . $sTitle . '</title>
<style type="text/css">
<!--/*--><![CDATA[/*><!--*/
@import url(/css/main.css);
@import url(' . substr($this->getConfig('CSS_CONTROL', '/__debug_trace/css/debug_control.css'), 1) . ');
@import url(' . substr($this->getConfig('CSS_DEBUG0',  '/__debug_trace/css/debug_common.css'), 1) . ');
@import url(' . substr($this->getConfig('CSS_DEBUG2',  '/__debug_trace/css/debug_mode2.css'), 1) . ');
/*]]>*/-->
</style>
<script type="text/javascript" src="/js/debug.js"></script>
<script type="text/javascript" src="' . substr($this->getConfig('JS_WRAPPER', '/js/js-wrapper.js'),  1) . '"></script>
<script type="text/javascript" src="' . substr($this->getConfig('JS_FILE',    '/__debug_trace/js/debug_trace.js'), 1) . '"></script>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
basicBroadcaster.prototype.config.DebugMode = true;
debug_trace.init(2);
//--><!]]>
</script>
</head><body>
<div id="debug2"><div>
<ul class="debug2_list">' . $sBlockInfo . '</ul>
</div></div>
</body></html>';
    } // function getSecondDebugCode

    /**
     * Get block description for second debug mode
     * @param \core\block\base $oBlock
     * @return string
     */
    public function getSecondDebugRow($oBlock, $aIncl, $isView)
    {
        $sName = $oBlock->getBlockName();
        $sRet = '<li class="debug2_row"><span class="debug2_label"><b>' . $oBlock->getMeta('initOrder', $this->oTab->getDefaultInitNum()) . ':</b> ' . $sName . '</span>';
        $sRet .= '<div class="debug2_button debug_info_but">info</div>' . $this->_getBlockDetail($oBlock);

        if (isset($this->aBlockCode[$sName])) {
            $sBlockCode = htmlspecialchars($this->aBlockCode[$sName]);
            $sBlockCode = str_replace(array('[[[[div]]]]', '[[[[/div]]]]'), array('<div class="debug2_incl">', '</div>'), $sBlockCode);
        } else {
            $sBlockCode = '';
        }
        $sRet .= '<div class="debug2_button debug_html_but">html</div><div class="debug_html">' . str_replace("\n", "<br />\n", $sBlockCode) . '</div>';
        if ($isView) {
            if (isset($this->aBlockCode[$sName])) {
                $sBlockCode = $this->aBlockCode[$sName];
                $sBlockCode = str_replace(array('[[[[div]]]]', '[[[[/div]]]]'), array('<div class="debug2_incl">', '</div>'), $sBlockCode);
                $sBlockCode = preg_replace('/\<script([^>]+\/\>|[^<]+\<\/script\>)/i', '[JavaScript]', $sBlockCode);
            } else {
                $sBlockCode = '';
            }
            $sRet .= '<div class="debug2_button debug_view_but">view</div><div class="debug_view">' . $sBlockCode . '</div>';
        }

        if ($aIncl) {
            $sRet .= '<ul class="debug2_list">';
            foreach ($aIncl as $v) {
                $sRet .= $v;
            }
            $sRet .= '<li class="debug2_clear">&nbsp;</li></ul>';
        }

        return $sRet . '</li>';
    } // function getSecondDebugRow



    /**
     * Get block description
     * @param \core\block\base $oBlock
     * @return string
     */
    protected function _getBlockDetail($oBlock)
    {
        $aRefl = array(
            new \ReflectionClass($oBlock)
        );

        $aDebug = $oBlock->getDebugInfo();
        $sCode = '';
        $sCode .= $this->_getFileInfo('php-file', $aRefl[0]->getFileName());
        $sCode .= $this->_getFileInfo('meta-file', $aDebug['metaFile']);
        $sCode .= $this->_getFileInfo('tpl-file', $aDebug['templateFile']);

        for ($i = 1; $i <= 10; $i++) {
            $aRefl[$i] = $aRefl[$i-1]->getParentClass();
            if(!$aRefl[$i]) {
                unset($aRefl[$i]);
                break;
            }
        }
        $sCode .= '<div class="debug_parents">List of parents: <ul>';
        foreach ($aRefl as $k => $v) {
            $sCode .= $this->_getParentInfo($v, $k != 0);
        }
        $sCode .= '</ul></div>';

        $sCode .= $this->_getMetaData('Result merged META-data',  $aDebug['meta'], null);
        $sCode .= $this->_getMetaData('Container META-data',      $aDebug['containerMeta'], $aDebug['meta']);
        $sCode .= $this->_getMetaData('Block file META-data',     $this->_reduceMetaArray($aDebug['fileMeta']),   $aDebug['meta']);
        $sCode .= $this->_getMetaData('Parent classes META-data', $this->_reduceMetaArray($aDebug['parentMeta']), $aDebug['meta']);
        $sCode .= $this->_getMetaData('Folder META-data',         $aDebug['folderMeta'], $aDebug['meta']);


        return '<div class="debug_detail">' . $sCode . '</div>';
    } // function _getBlockDetail

    /**
     * Reduce meta array
     * @param array $aMeta
     * @return array
     */
    protected function _reduceMetaArray($aMeta)
    {
        foreach (adduceToArray($aMeta) as $k => $v) {
            if ($k != 'common' && $k != 'own') {
                unset($aMeta[$k]);
            }
        }

        return $aMeta;
    } // function _reduceMetaArray

    /**
     * Get data about file
     * @param string $sLabel
     * @param string $sFile
     * @return string
     */
    protected function _getFileInfo($sLabel, $sFile)
    {
        $sFile = $sFile ? $this->_correctPath($sFile) . '<b>' . basename($sFile) . '</b> &nbsp;' : '<b class="debug_darkred">NONE</b>';
        return '<div class="debug_row"><label>' . $sLabel . ':</label><span>' . $sFile . '</span></div>';
    } // function getFileInfo

    /**
     * Get data about file
     * @param ReflectionClass $oRefl
     * @return string
     */
    protected function _getParentInfo($oRefl, $bIsParent)
    {
        if ($bIsParent) {
            $sRet = $bIsParent ? '-&gt; ' : '';
            $sRet .= '<i class="debug_parent_class">' . $oRefl->getName() . '</i>';

            $sFile = $oRefl->getFileName();
            $sRet .= '<div>';
            $sRet .= '<span>' . $this->_correctPath($sFile) . '<b>' . basename($sFile) . '</b> &nbsp;</span>';
            $sFile = substr($sFile, 0, -4) . '.meta.php';
            if (is_file($sFile)) {
                $sRet .= '<span>' . $this->_correctPath($sFile) . '<b>' . basename($sFile) . '</b> &nbsp;</span>';
            }
            $sRet .= '</div>';
        } else {
            $sRet = '<i>' . $oRefl->getName() . '</i>';
        }
        return '<li>' . $sRet . '</li>';
    } // function getParentInfo

    /**
     * Get meta - data
     * @param string $sLabel
     * @param array $aMeta
     * @param array $aResultMeta
     * @return string
     */
    protected function _getMetaData($sLabel, $aMeta, $aResultMeta)
    {
        if (!is_null($aResultMeta) && (!$aMeta || $aMeta == array('common' => array(), 'own' => array()) || $aMeta == array('common' => array()) || $aMeta == array('own' => array()))) {
            return '<div class="debug_meta"><div class="debug_meta_none">' . $sLabel . '</div></div>';
        }
        return '<div class="debug_meta' . (is_null($aResultMeta) ? ' debug_result_meta' : '') . '" title="Important! There are data has been formed after &quot;init-operation&quot;"><div class="debug_meta_label">' . $sLabel . ':</div><div class="debug_array">' . $this->_showMetaArray($aMeta, $aResultMeta, array()) . '</div></div>';
    } // function _getMetaData

    /**
     * Show meta-array
     * @param array $aMeta
     * @param array $aResultMeta
     * @param array $aKeys
     * @return string
     */
    protected function _showMetaArray($aMeta, $aResultMeta, $aKeys)
    {
        if (empty($aMeta)) {
            return 'array()';
        }
        $sRet = 'array(<ul>';
        foreach ($aMeta as $k => $v) {
            $sRet .= '<li' . (!is_null($aResultMeta) && !is_array($v) && $this->_checkRedefine($aResultMeta, $aKeys, $k, $v) ? ' class="debug_redefined"' : '') . '><span class="debug_array_key">' . htmlspecialchars($k) . '</span> =&gt; ';
            if (is_null($v)) {
                $sRet .= 'NULL';
            } elseif (is_scalar($v)) {
                if (is_bool($v)) {
                    $v = $v ? 'true' : 'false';
                } elseif (is_string($v) && !is_numeric($v)) {
                    $v = '"' . str_replace('"', '\\"', $v) . '"';
                }
                $sRet .= '<span class="debug_array_val">' . htmlspecialchars($v) . '</span>';
            } elseif (is_array($v)) {
                $sRet .= $this->_showMetaArray($v, $aResultMeta, array_merge($aKeys, array($k)));
            } elseif (is_object($v)) {
                $sRet .= '<span class="debug_array_instance">Instance of <b>' . get_class($v) . '</b> class</span>';
            } else {
                $sRet .= print_r($v, true);
            }
            $sRet .= '</li>';
        }
        return $sRet . '</ul>)';
    } // function _showMetaArray

    /**
     * Show meta-array
     * @param array $aResultMeta
     * @param array $aKeys
     * @param mixed $key
     * @param mixed $val
     * @return boolean
     */
    protected function _checkRedefine($aResultMeta, $aKeys, $key, $val)
    {
        $aKeys[] = $key;
        array_shift($aKeys);
        foreach ($aKeys as $v) {
            if (is_array($aResultMeta) && !array_key_exists($v, $aResultMeta)) {
                return true;
            }
            $aResultMeta = $aResultMeta[$v];
        }
        return $aResultMeta != $val;
    } // function _checkRedefine

    /**
     * Correct path
     * @param string $sPath
     * @return string
     */
    protected function _correctPath($sPath)
    {
        $sPath = dirname($sPath) . DIRECTORY_SEPARATOR;
        return str_replace('/', DIRECTORY_SEPARATOR, $sPath);
    } // function _correctPath

} // class \core\service\debug
?>