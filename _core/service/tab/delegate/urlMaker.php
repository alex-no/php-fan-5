<?php namespace fan\core\service\tab\delegate;
/**
 * Description of urlMaker
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
 * @version of file: 05.02.007 (31.08.2015)
 */
class urlMaker extends \fan\core\service\tab\delegate
{
    /**
     * @var \fan\core\service\matcher
     */
    protected $oMatcher = null;

    public function __construct()
    {
        $this->oMatcher = \fan\project\service\matcher::instance();
    } // function __construct
    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\
    /**
     * Is Used Https-protocol
     * @return boolean
     */
    public function isUseHttps()
    {
        return !empty($this->oConfig['USE_HTTPS']);
    } // function isUseHttps

    /**
     * Get Current Parsed URI
     *
     * $oTab->getCurrentURI(array(
     *     'correct_language' => $val1,
     *     'add_extension'    => $val2,
     *     'add_query_string' => $val3,
     *     'add_session_id'   => $val4,
     *     'query_separator'  => $val5
     * ));
     * - such variant of call allows you to pass the arguments in any order
     *
     * @param boolean  $bCorLanguage
     * @param boolean  $bAddExt
     * @param boolean  $bAddQueryStr
     * @param boolean  $bAddSid
     * @param string  $sSprtr
     * @return string
     */
    public function getCurrentURI($bCorLanguage = true, $bAddExt = true, $bAddQueryStr = true, $bAddSid = null, $sSprtr = null)
    {
        if (is_array($bCorLanguage)) {
            return $this->getCurrentURI(
                    array_val($bCorLanguage, 'correct_language', true),
                    array_val($bCorLanguage, 'add_extension',    true),
                    array_val($bCorLanguage, 'add_query_string', true),
                    array_val($bCorLanguage, 'add_session_id',   null),
                    array_val($bCorLanguage, 'query_separator',  null)
            );
        }

        $oReq    = \fan\project\service\request::instance();
        /* @var $oReq \fan\core\service\request */
        $oParsed = $this->oMatcher->getCurrentItem()->parsed;
        /* @var $oParsed \fan\core\service\matcher\item\parsed */

        // Set Request path
        $aRequest = $oReq->getAll('B'); // ToDo: Do non receive empty elemenents there.
        if (empty($aRequest) && $oParsed->src_path != '') {
            // If sham transter from fake URN (for example by alias)
            $aRequest = explode('/', trim($oParsed->src_path, '/'));
            $sLast    =& $aRequest[count($aRequest) - 1];
            $aMatche  = null;
            if (preg_match('/^(.+)\.\w{1,5}$/', $sLast, $aMatche)) {
                $sLast = $aMatche[1];
            }
        }
        foreach ($aRequest as &$v) {
            $v = urlencode($v);
        }

        // Add app prefix
        if ($oParsed->app_prefix) {
            array_unshift($aRequest, trim($oParsed->app_prefix, '/'));
        }
        // Add language
        if (is_null($bCorLanguage)) {
            $bCorLanguage = \fan\project\service\locale::instance()->isEnabled();
        }
        if ($bCorLanguage) {
            $sLng = \fan\project\service\locale::instance()->getLanguage();
            if (!empty($sLng)) {
                array_unshift($aRequest, $sLng);
            }
        }

        $sCurRequest = '/' . implode('/', $aRequest);

        // Add extension
        if ($bAddExt && substr($sCurRequest, -1) != '/') {
            $sCurRequest .= '.' . $this->getDefaultExtension();
        }

        if (is_null($sSprtr)) {
            $sSprtr = $this->getConfig('GET_SEPARATOR', '&amp;');
        }

        // Add Query String
        if ($bAddQueryStr) {
            $sQueryStr = $oReq->getQueryString(true, true, $sSprtr);
            $sCurRequest .= empty($sQueryStr) ? '' : '?' . $sQueryStr;
        }

        // Add Session ID
        if (is_null($bAddSid)) {
            $bAddSid = $this->getConfig('ALLOW_GET_SID', true);
        }
        if ($bAddSid) {
            $oSes = \fan\project\service\session::instance();
            if (!$oSes->isByCookies()) {
                $sCurRequest = $this->addQuery($sCurRequest, $oSes->getSessionName(), $oSes->getSessionId(), $sSprtr);
            }
        }

        return $sCurRequest;
    } // function getCurrentURI


    /**
     * Get Modified Current URI
     * This method allow to change current URI - include or exclude elements to add-request or GET
     *   Structure of $aModifier:
     *   array(
     *      'exclude' => array(
     *          'A' => array(...), // Keys of "add request" for exclude
     *          'G' => array(...), // Keys of "GET" for exclude
     *      ),
     *      'include' => array(
     *          'A' => array(...), // Array of elements for add to "add request"
     *          'G' => array(...), // Array of elements for add to "GET"
     *      ),
     *   )
     * @param array $aModifier
     * @param type $bAddExt
     * @param type $bAddSid
     * @param type $bProtocol
     * @return type
     */
    public function getModifiedCurrentURI($aModifier, $bAddExt = true, $bAddSid = null, $bProtocol = null)
    {
        $oRequest = service('request');
        /* @var $oRequest \fan\core\service\request */
        $aMain  = $oRequest->getAll('M', array());
        $aAdd   = $oRequest->getAll('A', array(), false);
        $aGet   = $oRequest->getAll('G', array());
        $sDelim = $oRequest->getAddDelimiter();

        // --- Modifying Add-request --- \\
        // Exclude data
        if (!empty($aAdd) && !empty($aModifier['exclude']['A'])) {
            foreach ($aModifier['exclude']['A'] as $k0) {
                if (is_int($k0)) {
                    unset($aAdd[$k0]);
                } else {
                    $k0 .= $sDelim;
                    foreach ($aAdd as $k1 => $v) {
                        if (substr($v, 0, strlen($k0)) == $k0) {
                            unset($aAdd[$k1]);
                        }
                    }
                }
            }
        }
        // Include data
        if (!empty($aModifier['include']['A'])) {
            foreach ($aModifier['include']['A'] as $k => $v) {
                if (is_int($k)) {
                    $aAdd[$k] = $v;
                }
            }
            $aAdd = array_merge($aAdd);
            foreach ($aModifier['include']['A'] as $k => $v) {
                if (!is_int($k)) {
                    $aAdd[] = $k . $sDelim . urlencode($v);
                }
            }
        }

        // --- Modifying GET --- \\
        // Exclude data
        if (!empty($aGet) && !empty($aModifier['exclude']['G'])) {
            foreach ($aModifier['exclude']['G'] as $k0) {
                unset($aGet[$k0]);
            }
        }
        // Include data
        if (!empty($aModifier['include']['G'])) {
            foreach ($aModifier['include']['G'] as $k => $v) {
                $aGet[$k] = $v;
            }
        }

        // --- Make URN --- \\
        $sUrn = '~/' . implode('/', $aMain);
        if (!empty($aAdd)) {
            $sUrn .= '/' . implode('/', $aAdd);
        }
        if ($bAddExt) {
            $sExt  = service('tab')->getDefaultExtension();
            $sUrn .= empty($sExt) ? '' : '.' . $sExt;
        }
        if (!empty($aGet)) {
            $sUrn .= '?' . http_build_query($aGet);
        }

        return $this->getURI($sUrn, 'link', $bAddSid, $bProtocol);
    } // function getModifiedCurrentURI

    /**
     * Get full URL
     * @param string $sUrn
     * @param string $sType
     * @param boolean $bAddSid - add SID to URL
     * @param boolean $bProtocol - consider PROTOCOL in transfer URL (null: use current protocol; false: use "http" only; true: use "https" only;)
     * @return string
     */
    public function getURI($sUrn = '', $sType = 'link', $bAddSid = null, $bProtocol = null)
    {
        if (is_null($bAddSid)) {
            $bAddSid = ($sType == 'link') && $this->getConfig('ALLOW_GET_SID', true);
        }

        if (!$sUrn) {
            $sUrn = $this->oMatcher->getCurrentUri();
        }

        if (substr($sUrn, 0, 1) == \fan\core\service\tab::URN_AP) {
            $sUrnPrefix = $this->getConfig(array('URN_prefix', $sType));
            $sAppPrefix = trim($this->oMatcher->getCurrentItem()->parsed['app_prefix'], '/');
            $sUrn = (empty($sAppPrefix) ? '' : '/' . $sAppPrefix) . $sUrnPrefix . substr($sUrn, 1);
        }

        if ($bAddSid) {
            $oSes = \fan\project\service\session::instance();
            if (!$oSes->isByCookies()) {
                $sUrn = $this->addQuery($sUrn, $oSes->getSessionName(), $oSes->getSessionId());
            }
        }

        if (!preg_match('/^https?\:\/\/\w/i', $sUrn)) {
            $oLocale = \fan\project\service\locale::instance();
            if ($sType == 'link' && $oLocale->isUriParsing()) {
                $sUrn = $oLocale->modifyUrn($sUrn);
            }

            if ($this->isUseHttps() && !is_null($bProtocol) && $bProtocol != (@$_SERVER['HTTPS'] == 'on')) {
                $sUrn = ($bProtocol ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $sUrn;
            }
        }
        return $sUrn;
    } // function getURI

    /**
     * Add Query to URL
     *
     * @param string $sUrn
     * @param string $sQuery
     * @return string
     */
    public function addQuery($sUrn, $sKey, $sVal, $sSprtr = null)
    {
        if ($sKey && $sVal) {
            if (is_null($sSprtr)) {
                $sSprtr = $this->getConfig('GET_SEPARATOR', '&amp;');
            }

            $sVal = htmlspecialchars($sVal);
            if (!preg_match('/(?:\?|' . $this->_addSlashes($sSprtr) . ')' . $this->_addSlashes($sKey) . '\=' . $this->_addSlashes($sVal) . '/', $sUrn)) {
                $sUrn .= (strstr($sUrn, '?') ? $sSprtr : '?') . urlencode($sKey) . '=' . urlencode($sVal);
            }
        }
        return $sUrn;
    } // function addQuery

    /**
     * Returns default extension
     *
     * @return string
     */
    public function getDefaultExtension()
    {
        return trim($this->oFacade->getConfig('DEFAULT_EXT', 'html'), ' .');
    } // function getDefaultExtension

    /**
     * Reduce extantion from URL-request
     * @param string $sUrl
     * @return string
     */
    public function reduceExt($sUrl, $nMinLen = 2, $nMaxLen = 4)
    {
        $aMatches = null;
        if (preg_match('/^(.+?)(\.\w{' . $nMinLen. ',' . $nMaxLen . '})?$/', $sUrl, $aMatches)) {
            return $aMatches[1];
        }
        return $sUrl;
    } // function reduceExt

    // ======== Private/Protected methods ======== \\

    /**
     * Add slashes for regexp
     * @param string $sVal
     * @return string
     */
    protected function _addSlashes($sVal)
    {
        return addcslashes($sVal, '~@#$%^&|\\.,!?:;-+*/=<>()[]{}`"\'');
    } // function addSlashes
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
} // class \fan\core\service\tab\delegate\urlMaker
?>