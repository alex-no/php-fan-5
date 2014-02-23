<?php namespace core\service\tab\delegate;
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
 * @version of file: 05.007 (23.02.2014)
 */
class urlMaker extends \core\service\tab\delegate
{
    /**
     * @var \core\service\matcher
     */
    protected $oMatcher = null;

    public function __construct()
    {
        $this->oMatcher = \project\service\matcher::instance();
    } // function __construct
    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\
    /**
     * Is Used Https-protocol
     * @return boolean
     */
    public function isUseHttps()
    {
        return !empty($this->aConfig['USE_HTTPS']);
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

        $oReq    = \project\service\request::instance();
        /* @var $oReq \core\service\request */
        $oParsed = $this->oMatcher->getCurrentItem()->parsed;
        /* @var $oParsed \core\service\matcher\item\parsed */

        // Set Request path
        $aRequest = $oReq->getAll('B');
        foreach ($aRequest as &$v) {
            $v = urlencode($v);
        }

        // Add app prefix
        if ($oParsed->app_prefix) {
            array_unshift($aRequest, trim($oParsed->app_prefix, '/'));
        }
        // Add language
        if ($bCorLanguage) {
            $sLng = \core\service\locale::instance()->getLanguage();
            if (!empty($sLng)) {
                array_unshift($aRequest, $sLng);
            }
        }

        $sCurRequest = '/' . implode('/', $aRequest);

        // Add extension
        if ($bAddExt) {
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
            $oSes = \project\service\session::instance();
            if (!$oSes->isByCookies()) {
                $sCurRequest = $this->addQuery($sCurRequest, $oSes->getSessionName(), $oSes->getSessionId(), $sSprtr);
            }
        }

        return $sCurRequest;
    } // function getCurrentURI

    /**
     * Get full URL
     *
     * @param string $sUrn
     * @param string $sType
     * @param boolean $bUseSid - use SID in URL
     * @param boolean $bProtocol - consider PROTOCOL in transfer URL (null: use current protocol; false: use "http" only; true: use "https" only;)
     * @return string
     */
    public function getURI($sUrn = '', $sType = 'link', $bUseSid = null, $bProtocol = null)
    {
        if (is_null($bUseSid)) {
            $bUseSid = ($sType == 'link') && $this->getConfig('ALLOW_GET_SID', true);
        }

        if (!$sUrn) {
            $sUrn = $this->oMatcher->getCurrentUri();
        }

        if (substr($sUrn, 0, 1) == \core\service\tab::URN_AP) {
            $sUrnPrefix = $this->getConfig(array('URN_prefix', $sType));
            $sAppPrefix = trim($this->oMatcher->getCurrentItem()->parsed['app_prefix'], '/');
            $sUrn = (empty($sAppPrefix) ? '' : '/' . $sAppPrefix) . $sUrnPrefix . substr($sUrn, 1);
        }

        if ($bUseSid) {
            $oSes = \project\service\session::instance();
            if (!$oSes->isByCookies()) {
                $sUrn = $this->addQuery($sUrn, $oSes->getSessionName(), $oSes->getSessionId());
            }
        }

        $oLocale = \project\service\locale::instance();
        if ($sType == 'link' && $oLocale->isUrlParsing()) {
            $oLocale->modifyUrl($sUrn);
        }

        if ($this->isUseHttps() && !is_null($bProtocol) && $bProtocol != (@$_SERVER['HTTPS'] == 'on')) {
            $sUrn = ($bProtocol ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $sUrn;
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
} // class \core\service\tab\delegate\urlMaker
?>