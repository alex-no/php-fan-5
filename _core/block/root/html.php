<?php namespace fan\core\block\root;
/**
 * Base abstract root html block
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
 * @version of file: 05.02.005 (12.02.2015)
 * @abstract
 */
abstract class html extends \fan\core\block\base
{
    /**
     * Name of block
     * @var string
     */
    protected $sModalWin = '';

    /**
     * External CSS
     * @var array
     */
    protected $aExternalCSS = array(
        'link'  => array(
            'all' => array(),
        ),
        'style' => array(
            'all' => array(),
        ),
        'ie'    => array(
            'all' => array(),
        ),
    );

    /**
     * Embeded CSS-data by media-type
     * @var array
     */
    protected $aEmbedCSS = array('all' => '');

    /**
     * External JS
     * @var array
     */
    protected $aExternalJS = array(
        'head' => array(),
        'body' => array(),
    );

    /**
     * Embeded JS-data for head and body
     * @var type
     */
    protected $aEmbedJS = array(
        'head' => null,
        'body' => null,
    );

    /**
     * Init block data
     */
    public function init()
    {
        $sBrowserClass = '';
        foreach ($this->getMeta('browserClasses', array()) as $sBrowser => $aParam) {
            $aMatch = null;
            if (preg_match($aParam['regExp'], $this->request->get('HTTP_USER_AGENT', 'H', ''), $aMatch)) {
                $sBrowserClass = $sBrowser;
                if (!empty($aMatch[1])) {
                    foreach ($aParam['olderVer'] as $sAddClass => $nBeforeVer) {
                        if ($aMatch[1] < $nBeforeVer) {
                            $sBrowserClass .= ' ' . $sAddClass;
                        }
                    }
                }
                break;
            }
        }
        $this->_setViewVar('bodyClass', $sBrowserClass);
        $this->_setViewVar('poweredBy', $this->getMeta('show_power', true) ? \fan\project\service\application::instance()->getCoreVersion() : null);
    } // function init

    /**
     * Additional Init for root-block
     */
    public function runAfterInit()
    {
        if (!$this->view['title']) {
            $oMain = $this->_getBlock('main', false);
            if ($oMain) {
                if (method_exists($oMain, 'getTitle')) {
                    $sTitle = $oMain->getTitle();
                }
                if (empty($sTitle)) {
                    $sTitle = $oMain->getMeta('title');
                }
            }
            if (empty($sTitle)) {
                $oApp = service('application');
                /* @var $oApp \fan\core\service\application */
                $sTitle  = $oApp->getConfig('PROJECT_NAME');
                $sTitle .= (empty($sTitle) ? '' : ' | ') . $oApp->getAppName();
                if ($oMain) {
                    $sTitle .= ' | ' . get_class_name($oMain);
                }
            }
            $this->view['title'] = $sTitle;
        }
    } // function runAfterInit

    /**
     * Set tab title
     * @param string $sTitle - new title
     * @param boolean $bCheckIsSet - Check - if set - do not change
     * @return \fan\core\block\root\html
     */
    public function setTitle($sTitle, $bCheckIsSet = false)
    {
        if(!$bCheckIsSet || !$this->view['title']) {
            $this->view['title'] = $sTitle;
        }
        return $this;
    } // function setTitle

    /**
     * Get tab title
     * @return string Tab title
     */
    public function getTitle()
    {
        return $this->view['title'];
    } // function getTitle

    /**
     * Set meta tag
     * @param array $aMeta Array with meta parameters
     */
    public function setMetaTag($aMeta)
    {
        if (is_object($aMeta) && method_exists($aMeta, 'toArray')) {
            $aMeta = $aMeta->toArray();
        }
        if (!is_array($aMeta)) {
            error_log('Incorrect value for meta-tag.', E_USER_NOTICE);
            return;
        }

        $aMetaData = $this->view->get('meta', array());
        foreach ($aMetaData as $v) {
            if($this->_compareArray($v, $aMeta, array('name', 'property', 'content', 'http_equiv', 'scheme', 'id'))) {
                return;
            }
        }
        $aMetaData[] = $aMeta;
        $this->view->set('meta', $aMetaData);
    } // function setMetaTag

    /**
     * Get meta tag
     * @return array $aMeta
     */
    public function getMetaTag()
    {
        return empty($this->view['meta']) ? array() : $this->view['meta'];
    } // function getMetaTag

    /**
     * Set meta tag
     * @param string $sRel   relation
     * @param string $sType  type
     * @param string $sHref  href
     * @param string $sTitle title
     * @return \fan\core\block\root\html
     */
    public function setLinkTag($sRel, $sType, $sHref, $sTitle = '')
    {
        $aLink = array('rel' => $sRel, 'type' => $sType, 'href' => $sHref);
        if ($sTitle) {
            $aLink['title'] = $sTitle;
        }
        $aTagLink   = $this->view->get('tagLink', array());
        $aTagLink[] = $aLink;
        $this->view->set('tagLink', $aTagLink);
        return $this;
    } // function setLinkTag

    /**
     * Set external css by includes.
     * Possible value of type:
     *   - style|style_all|style_braille|style_handheld|style_print|style_screen|style_speech|style_projection|style_tty|style_tv
     *   - link |link_all |link_braille |link_handheld |link_print |link_screen |link_speech |link_projection |link_tty |link_tv
     * @param mixed $aCssFile array of files
     * @param string $sType type of css file (it is need set if first argument is not array)
     * @return \fan\core\block\root\html
     */
    public function setExternalCss($aCssFile, $sType = 'style')
    {
        if (is_object($aCssFile) && method_exists($aCssFile, 'toArray')) {
            $aCssFile = $aCssFile->toArray();
        } elseif (!is_array($aCssFile)) {
            $aCssFile = array($sType => array($aCssFile));
        }

        $aCSS =& $this->aExternalCSS;
        foreach ($aCssFile as $k => $v1) {

            $tmp = explode('_', $k, 2);
            if (empty($tmp[1])) {
                $tmp[1] = 'all';
            } elseif (!in_array($tmp[1], array('all', 'braille', 'handheld', 'print', 'screen', 'speech', 'projection', 'tty', 'tv'))) {
                trigger_error('Unknown media "' . $tmp[1] .'" for External CSS "' . $sType .'".', E_USER_WARNING);
                continue;
            }
            list($k0, $k1) = $tmp;
            if (!isset($aCSS[$k0][$k1])) {
                $aCSS[$k0][$k1] = array();
            }

            foreach ($v1 as $v2) {
                if(empty($v2)) {
                    continue;
                }
                $v2 = $this->oTab->getURI($v2, 'css', false);
                if(!in_array($v2, $aCSS[$k0][$k1])) {
                    $aCSS[$k0][$k1][] = $v2;
                }
            }
        }
        return $this;
    } // function setExternalCss

    /**
     * Set embed css
     * @param string $sCss - array of css code
     * @return \fan\core\block\root\html
     */
    public function setEmbedCss($sCss, $sMedia = 'all')
    {
        if (is_object($sCss)) {
            if (!method_exists($sCss, '__toString')) {
                error_log('Incorrect value for Embed Css.', E_USER_NOTICE);
                return;
            }
            $aCssFile = $aCssFile->__toString();
        }
        if (isset($this->aEmbedCSS[$sMedia])) {
            $this->aEmbedCSS[$sMedia] = '';
        }

        $sEmbedCss =& $this->aEmbedCSS[$sMedia];
        if (!strstr($sEmbedCss, $sCss)) {
            $sEmbedCss .= empty($sEmbedCss) ? $sCss : "\n" . $sCss;
        }
        return $this;
    } // function setEmbedCss

    /**
     * Set external JavaScript
     * @param mixed $aJsFile array of files
     * @param string $sPos position of JavaScript (it is need set if first argument is not array)
     * @return \fan\core\block\root\html
     */
    public function setExternalJs($aJsFile, $sPos = 'head')
    {
        if (is_object($aJsFile) && method_exists($aJsFile, 'toArray')) {
            $aJsFile = $aJsFile->toArray();
        } elseif(!is_array($aJsFile)) {
            $aJsFile = array($sPos => array($aJsFile));
        }

        foreach ($aJsFile as $k => $v1) {
            if (!isset($this->aExternalJS[$k])) {
                trigger_error('Unknown position key "' . $k .'" for External JS.', E_USER_WARNING);
                continue;
            }
            foreach ($v1 as $v2) {
                if(empty($v2)) {
                    continue;
                }
                $v2 = $this->oTab->getURI($v2, 'js', false);
                $this->_addJsFile($v2, $k);
            }
        }
        return $this;
    } // function setExternalJs

    /**
     * Set embed JavaScript
     * @param mixed $mJs
     * @param string $sPos position of JavaScript (it is need set if first argument is not array)
     * @param numeric $nOrd order run (-1 - before all, 0 - as default, 1 - after all)
     * @return \fan\core\block\root\html
     */
    public function setEmbedJs($mJs, $sPos = 'head', $nOrd = 0, $bAllowDebug = true)
    {
        if (!array_key_exists($sPos, $this->aEmbedJS)) {
            trigger_error('Unknown position key "' . $sPos .'" for Embeded JS.', E_USER_WARNING);
            return $this;
        }
        if (empty($this->aEmbedJS[$sPos])) {
            $this->aEmbedJS[$sPos] = array('', '', '');
        }

        if (is_object($mJs) && method_exists($mJs, 'toArray')) {
            $mJs = $mJs->toArray();
        }

        if(is_array($mJs)) {
            $sJs = array_shift($mJs) . '(';
            $oJson = \fan\project\service\json::instance();
            foreach ($mJs as $v) {
                $sJs .= $oJson->encode($v) . ', ';
            }
            $sJs = substr($sJs, 0, -2) . ');';
        } else {
            $sJs = $mJs;
        }

        $sEmbedJS =& $this->aEmbedJS[$sPos][$nOrd < 0 ? 0 : ($nOrd > 0 ? 2 : 1)];
        if (!empty($sEmbedJS)) {
            $sEmbedJS .= "\n";
        }
        if (!preg_match('/^\s*try\s*\{.+?\}\s*catch\s*\(.*?\)\s*\{.*?\}\s*$/is', $sJs)) {
            $sJs = 'try{' . $sJs . '}catch(e){' . ($this->tab->isDebugAllowed() && $bAllowDebug ? 'alert((e.fileName ? "Error in " + e.fileName : "") + (e.lineNumber ? " line " + e.lineNumber : "")+ (e.fileName || e.lineNumber ? "\n" : "") + (e.name ? e.name + ": " : "") + e.message);' : '') . '}';
        }
        $sEmbedJS .= $sJs;
        return $this;
    } // function setEmbedJs

    /**
     * Set head Before
     * @param string $sHtmlCode
     * @return \fan\core\block\root\html
     */
    public function setHeadBefore($sHtmlCode)
    {
        $sCodeBefore = $this->view->get('headBefore', '');
        $this->view->set('headBefore', $sCodeBefore . $sHtmlCode);
        return $this;
    } // function setHeadBefore

    /**
     * Set head After
     * @param string $sHtmlCode
     * @return \fan\core\block\root\html
     */
    public function setHeadAfter($sHtmlCode)
    {
        $sCodeAfter = $this->view->get('headAfter', '');
        $this->view->set('headAfter', $sCodeAfter . $sHtmlCode);
        return $this;
    } // function setHeadAfter

    /**
     * Set modal window
     * @param string $sFilePath
     * @param array $aTplVars
     * @return \fan\core\block\root\html
     */
    public function setModalWindow($sFilePath, $aTplVars = array(), $sCssFile = '/css/modal_win.css', $sJsFile = null)
    {
        if(!empty($sFilePath)) {
            if (!\is_file($sFilePath)) {
                $sFilePath = \bootstrap::parsePath($sFilePath);
            }
            if (\is_readable($sFilePath)) {
                $oTemplate = \fan\project\service\template::instance()->get($sFilePath, null, $this);
                /* @var $oTemplate \fan\core\service\template\type\base */

                foreach ($aTplVars as $k => $v) {
                    $oTemplate->assign($k, $v);
                }

                $this->sModalWin .= $oTemplate->fetch();

                if (!empty($sCssFile)) {
                    $this->setExternalCss($sCssFile);
                }
                if (!empty($sJsFile)) {
                    $this->setExternalJs($sJsFile);
                }
            } else {
                trigger_error('Unknown path to modal template "' . $sFilePath . '"', E_USER_WARNING);
            }
        }
        return $this;
    } // function setModalWindow

    /**
     * Check is available CSS-files for this type of css
     * @param string $sKey
     * @return boolean
     */
    public function isExtCSS($sKey)
    {
        $aCSS = $this->view->get('externalCSS', array());
        if (!isset($aCSS[$sKey])) {
            return false;
        } elseif (!is_array($aCSS[$sKey])) {
            trigger_error('Values of CSS for key "' . $sKey . '" must be as array.', E_USER_WARNING);
            return false;
        }
        foreach ($aCSS[$sKey] as $v) {
            if (!empty($v)) {
                return true;
            }
        }
        return false;
    } // function isExtCSS
    // ==================== protected methods ==================== \\

    /**
     * Add new JS-file path
     * @param string $sUri
     * @param string $sType
     * @return \fan\core\block\root\html
     */
    protected function _addJsFile($sUri, $sType)
    {
        $aJS =& $this->aExternalJS[$sType];
        if(!in_array($sUri, $aJS)) {
            if (is_readable(BASE_DIR . $sUri) && preg_match('/\/\*\*include\s*(.+?)\s*\*\//is', file_get_contents(BASE_DIR . $sUri), $aMatches)) {
                $aScripts = explode("\n", $aMatches[1]);
                foreach ($aScripts as $sScr) {
                    list($sScrFile) = explode(';', $sScr, 2);
                    $this->_addJsFile($sScrFile, $sType);
                }
            }
            if(!in_array($sUri, $aJS)) {
                $aJS[] = $sUri;
            }
        }
        return $this;
    } // function _addJsFile

    /**
     * Method for redefine in child class
     * Method if run before output-view operation
     */
    protected function _preOutput()
    {
        if (!empty($this->sModalWin)) {
            $this->view->set('modal_win', $this->sModalWin);
        }
        $this->view->set('externalCSS', $this->aExternalCSS);
        $this->view->set('embedCSS',    $this->aEmbedCSS);
        $this->view->set('externalJS',  $this->aExternalJS);
        $this->view->set('embedJS',     $this->aEmbedJS);
    } // function _preOutput
    /**
     * Compare two array by keys
     * @param array $aArray1
     * @param array $aArray2
     * @param array $aKeys
     * @return boolean
     */
    protected function _compareArray($aArray1, $aArray2, $aKeys)
    {
        foreach ($aKeys as $k) {
            if (!isset($aArray1[$k]) && !isset($aArray2[$k])) {
                continue;
            }
            if (!isset($aArray1[$k]) || !isset($aArray2[$k]) || $aArray1[$k] != $aArray2[$k]) {
                return false;
            }
        }
        return true;
    } // function _compareArray
} // class \fan\core\block\root\html
?>