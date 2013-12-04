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
 * @version of file: 05.001
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
     * @param boolean  $bCorLanguage
     * @param boolean  $bAddExt
     * @param boolean  $bAddQueryStr
     * @param boolean  $bAddFirstSlash
     * @return string
     */
    public function getCurrentURI($bCorLanguage = true, $bAddExt = true, $bAddQueryStr = true, $bAddFirstSlash = true)
    {
        $aParsedData = $this->oMatcher->getCurrentItem()->parsed;

        // Set Request path
        $aRequest = $aParsedData->both_request;
        // Add app prefix
        if ($aParsedData->app_prefix) {
            array_unshift($aRequest, trim($aParsedData->app_prefix, '/'));
        }
        // Add language
        if ($bCorLanguage && 0) {
            array_unshift($aRequest, $aParsedData->language);
        }

        $sCurRequest = implode('/', $aRequest);

        // Add extension
        if ($bAddExt) {
            $sCurRequest .= '.' . $this->getDefaultExtension();
        }

        // Add Query String
        if ($bAddQueryStr) {
            $sQueryStr    = $aParsedData->query;
	    // ToDo: not always replace & to &amp; there
            $sCurRequest .= $sQueryStr ? str_replace('&', $this->getConfig('GET_SEPARATOR', '&amp;'), $sQueryStr) : '';
        }

        return ($bAddFirstSlash ? '/' : '') . $sCurRequest;
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
            //ToDo: Redesign it
            $bUseSid = ($sType == 'link');
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
        if ($this->isUseHttps() && !is_null($bProtocol) && $bProtocol != (@$_SERVER['HTTPS'] == 'on')) {
            $sUrn = ($bProtocol ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $sUrn;
        }
        $oLocale = \project\service\locale::instance();
        return $sType == 'link' && $oLocale->isUrlParsing() ? $oLocale->modifyUrl($sUrn) : $sUrn;
    } // function getURI

    /**
     * Add Query to URL
     *
     * @param string $sUrn
     * @param string $sQuery
     * @return string
     */
    public function addQuery($sUrn, $sKey, $sVal)
    {
        if ($sKey && $sVal) {
            $sSep = $this->getConfig('GET_SEPARATOR', '&amp;');
            $sVal = htmlspecialchars($sVal);
            if (!preg_match('/(?:\?|' . $this->_addSlashes($sSep) . ')' . $this->_addSlashes($sKey) . '\=' . $this->_addSlashes($sVal) . '/', $sUrn)) {
                $sUrn .= (strstr($sUrn, '?') ? $sSep : '?') . $sKey . '=' . $sVal;
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