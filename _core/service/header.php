<?php namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * Description of header
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
 *
 * @property string  $response
 * @property string  $protocol
 * @property string  $contentType
 * @property string  $encoding
 * @property string  $filename
 * @property string  $disposition
 * @property string  $length
 * @property string  $legthRange
 * @property string  $modified
 * @property string  $expired
 * @property string  $cacheLimit
 *
 */
class header extends \fan\core\base\service\single
{
    /**
     * Mapping of methods for send headers
     * @var array
     */
    protected $aSendMethodMap = array(
        'response'    => array('sendResponseType', 0),
        'protocol'    => array('sendResponseType', 1),

        'contentType' => array('sendContentType', 0),
        'encoding'    => array('sendContentType', 1),

        'filename'    => array('sendFilename', 0),
        'disposition' => array('sendFilename', 1),

        'length'      => array('sendLength', 0),
        'legthRange'  => array('sendLength', 1),

        'modified'    => array('sendTime', 0),
        'expired'     => array('sendTime', 1),

        'cacheLimit'  => array('sendCache', 0),

        //'' => array('', 0),
    );

    /**
     * Mapping of special methods for set headers
     * @var array
     */
    protected $aSetMethodMap = array(
        'response' => 'setResponseType',
    );

    /**
     * If requested code isn't present there, they will be loaded automatically
     * @var array Frequently Response Codes
     */
    protected $aResponseCodes = array(
        200 => 'OK',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error',
    );

    /**
     * Stack of Headers ordered by MethodMap
     * @var array
     */
    protected $aHeaderData = array();

    /**
     * service's constructor
     * @param boolean $bAllowIni
     */
    protected function __construct($bAllowIni = true)
    {
        parent::__construct($bAllowIni);

        $this->clearHeaders();
    } // function __construct

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\

    // ------ Prepare of headers setting ------ \\
    /**
     * Add header to stack
     * @param string $sParam Parameter of Header
     * @param string $sValue Value of Parameter
     * @return \fan\core\service\header
     */
    public function addHeader($sParam, $sValue)
    {
        if (in_array($sParam, $this->aSetMethodMap)) {
            $sMethod = $this->aSetMethodMap[$sParam];
            $this->$sMethod($sValue);
        } else {
            $this->_setHeadStack($sParam, $sValue);
        }
        return $this;
    } // function addHeader

    /**
     * Set headers to stack
     * @param array $aHeaders Name=>Parameters for call
     * @return \fan\core\service\header
     */
    public function setHeaders(array $aHeaders)
    {
        $this->clearHeaders();
        foreach ($aHeaders as $k => $v) {
            $this->addHeader($k, $v);
        }
        return $this;
    } // function setHeaders

    /**
     * Remove Parameter of Header from stack
     * @param string $sParam
     * @return \fan\core\service\header
     */
    public function removeHeader($sParam)
    {
        unset($this->aHeaderData[$sParam]);
        return $this;
    } // function removeHeader

    /**
     * Get header(s) from stack
     * @param string $sParam
     * @return array of stack data
     */
    public function getHeader($sParam = null)
    {
        return empty($sParam) ? $this->aHeaderData : $this->aHeaderData[$sParam];
    } // function getHeader

    /**
     * Output headers
     * @return array - old stack data
     */
    public function sendHeaders()
    {
        $sFileName = $nLineNum = null;
        if (headers_sent($sFileName, $nLineNum)) {
            trigger_error('Headers have been sent in "' . $sFileName . '" at the line ' . $nLineNum, E_USER_WARNING);
            return null;
        }

        if (empty($this->aHeaderData['response'])) {
            $this->setResponseType();
        }

        foreach ($this->_prepareFunctions() as $k => $v) {
            $aArg = $this->_orderArguments($v);
            call_user_func_array(array($this, $k), $aArg);
        }

        return $this->clearHeaders();
    } // function sendHeaders

    /**
     * Clear headers stack
     * @return array - old stack data
     */
    public function clearHeaders()
    {
        $aRet = $this->aHeaderData;
        $this->aHeaderData = array(
            'protocol' => empty($_SERVER['SERVER_PROTOCOL']) ? 'HTTP/1.1' : $_SERVER['SERVER_PROTOCOL'],
        );
        $this->setResponseType();
        return $aRet;
    } // function clearHeaders

    // ------ Sepecial header setter/getter ------ \\
    /**
     * Set response type
     * @param integer $iCode
     * @return \fan\core\service\header
     */
    public function setResponseType($iCode = null)
    {
        if (is_null($iCode)) {
            $iCode = 200;
        } else {
            $this->_checkResponseCode($iCode);
        }

        $this->_setHeadStack('response', $iCode);

        return $this;
    } // function setResponseType
    /**
     * Get response code
     * @return integer
     */
    public function getResponseCode()
    {
        return $this->aHeaderData['response'];
    } // function getResponseCode

    /**
     * Get protocol
     * @return string
     */
    public function getProtocol()
    {
        return isset($this->aHeaderData['protocol']) ? $this->aHeaderData['protocol'] : 'HTTP/1.1';
    } // function getProtocol

    // ------ Senders of header ------ \\
    /**
     * Send response type
     * @param integer $iCode
     * @return string - full text of response header
     */
    public function sendResponseType($iCode = null, $sProtocol = null)
    {
        header($this->_getResponseText($iCode, $sProtocol));
    } // function sendResponseType

    /**
     * Set Content-Type
     * @param string $sValue
     * @param string $sEncoding
     * @return \fan\core\service\header
     */
    public function sendContentType($sValue = null, $sEncoding = null)
    {
        if (!empty($sValue) || !empty($sEncoding)) {
            if (empty($sValue)) {
                $sValue = 'text/html';
            }
            header('Content-Type: ' . $sValue . (empty($sEncoding) ? '' : '; ' . $sEncoding));
        }
        return $this;
    } // function sendContentType

    /**
     * Get Request parameter
     * @param string $nLen Set file legth
     * @param string $sRanges Set ranges of legth
     * @return \fan\core\service\header
     */
    public function sendLength($nLen, $sRanges = null)
    {
        if (!empty($nLen)) {
            if (empty($sRanges)) {
                $sRanges = 'bytes';
            }
            header('Accept-Ranges: ' . $sRanges);
            header('Content-Length: ' . $nLen);
        }
        return $this;
    } // function sendLength

    /**
     * Set time parameters
     * @param number $nModified Set modified timestamp
     * @param number $nExpired Set expired timestamp
     * @return \fan\core\service\header
     */
    public function sendTime($nModified = NULL, $nExpired = NULL)
    {
        if (!is_null($nModified)) {
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $nModified) . ' GMT');
        }
        if (!is_null($nExpired)) {
            header('Expires: ' . gmdate('D, d M Y H:i:s', $nExpired) . ' GMT');
            header('Cache-Control: post-check=1,pre-check=1');
        }
        return $this;
    } // function sendTime

    /**
     * Get Request parameter
     * @param string $sFileName Set file name
     * @param boolean $bIsInline if TRUE - inline, ELSE - attachment
     * @return \fan\core\service\header
     */
    public function sendFilename($sFileName, $bIsInline = true)
    {
        header('Content-Disposition: ' . ($bIsInline ? 'inline' : 'attachment') . '; filename="' . ($sFileName ? $sFileName : 'no_name') . '"');
        return $this;
    } // function sendFilename

    /**
     * Sends headers for enabling/disabling of caching file by browser/proxy
     * @param number $nTimeExpires Time period of expires (if "0" - disable cache)
     * @return \fan\core\service\header
     */
    public function sendCache($nTimeExpires = 0)
    {
        $nTime = time();
        if ($nTimeExpires > 0) {
            // Enable cache
            $this->sendTime(isset($this->aHeaderData['modified']) ? null : $nTime, $nTime + $nTimeExpires);
        } else {
            // Disable cache
            $this->sendTime($nTime);
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

            if ($this->getProtocol() == 'HTTP/1.0') {
                header('Pragma: no-cache');
            } else {
                header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0'); //  max-age=0
            }
        }
        return $this;
    } // function sendCache

    /**
     * Send header location
     * @param string $sUrl new location
     * @return \fan\core\service\header
     */
    public function sendLocation($sUrl, $bContinueExec = false)
    {
        header('Location: ' . str_replace('&amp;', '&', $sUrl));
        if (!$bContinueExec) {
            exit;
        }
        return $this;
    } // function sendLocation

    /**
     * Send header location 301
     * @param string $sUrl new location
     * @return \fan\core\service\header
     */
    public function sendLocation301($sUrl, $bContinueExec = false)
    {
        header('Location: ' . str_replace('&amp;', '&', $sUrl), true, 301);
        if (!$bContinueExec) {
            exit;
        }
        return $this;
    } // function sendLocation301

    /**
     * Send Arbitrary header
     * @param string $sType
     * @param string $sValue
     * @param string $sExtraData Extra Data after ";"
     * @return \fan\core\service\header
     */
    public function sendArbitrary($sType, $sValue, $sExtraData = '')
    {
        header($sType . ': ' . $sValue . (empty($sExtraData) ? '' : '; ' . $sExtraData));
        return $this;
    } // function sendArbitrary

    // ------ Frequently response headers set ------ \\
    /**
     * Set header of ok 200
     * @return \fan\core\service\header
     */
    public function ok200($bSend = false)
    {
        return $this->_setSpecialType(200, $bSend);
    } // function ok200

    /**
     * Set header of error 403
     * @return \fan\core\service\header
     */
    public function error403($bSend = false)
    {
        return $this->_setSpecialType(403, $bSend);
    } // function error403

    /**
     * Set header of error 404
     * @return \fan\core\service\header
     */
    public function error404($bSend = false)
    {
        return $this->_setSpecialType(404, $bSend);
    } // function error404

    /**
     * Set header of error 500
     * @return \fan\core\service\header
     */
    public function error500($bSend = false)
    {
        return $this->_setSpecialType(500, $bSend);
    } // function error500

    // ======== Protected methods ======== \\
    /**
     * Get Map of Methods used for Stack
     * @return array
     */
    protected function _getSendMethodMap()
    {
        return $this->aSendMethodMap;
    } // function _getMethodMap

    /**
     * Set value to Head-Stack
     * @param string $sParam Parameter of Header
     * @param strng $sValue
     * @return \fan\core\service\header
     */
    protected function _setHeadStack($sParam, $sValue)
    {
        $aParameters = $this->_getSendMethodMap();
        if (!isset($aParameters[$sParam])) {
            throw new fatalException($this, 'Incorrect header parameter "' . $sParam . '" for stack');
        }
        $this->aHeaderData[$sParam] = $sValue;
        return $this;
    } // function _setHeadStack

    /**
     * Prepare Functions fro send headers
     * @return array
     */
    protected function _prepareFunctions()
    {
        $aResult = array();
        foreach ($this->_getSendMethodMap() as $k => $v) {
            if (isset($this->aHeaderData[$k])) {
                $aResult[$v[0]][$v[1]] = $this->aHeaderData[$k];
            }
        }
        return $aResult;
    } // function _prepareFunctions

    /**
     * Order Arguments of method
     * @param array $aArg
     * @return null
     */
    protected function _orderArguments(array $aArg)
    {
        for ($i = 0; $i < max(array_keys($aArg)); $i++) {
            if (!isset($aArg[$i])) {
                $aArg[$i] = null;
            }
        }
        ksort($aArg);
        return $aArg;
    } // function _ModifyArguments

    /**
     * Get Text of Response-header
     * @param integer $iCode
     * @param string $sProtocol
     * @return string
     * @throws \fan\project\exception\service\fatal
     */
    protected function _getResponseText($iCode, $sProtocol)
    {
        $this->_checkResponseCode($iCode);
        if (empty($sProtocol)) {
            $sProtocol = $this->getProtocol();
        }
        return $sProtocol . ' ' . $iCode . ' ' . $this->aResponseCodes[$iCode];
    } // function _getResponseText

    /**
     * Check Response Code and autoload not finded code
     * @param integer $iCode
     * @return \fan\core\service\header
     * @throws \fan\project\exception\service\fatal
     */
    protected function _checkResponseCode($iCode)
    {
        if (!isset($this->aResponseCodes[$iCode])) {
            if ($iCode >= 100 && $iCode <= 599) {
                $sClass = $this->_getEngine('code', false);
                $this->aResponseCodes = array_merge_recursive_alt(
                        $this->aResponseCodes,
                        call_user_func(array($sClass, 'getCodes' . substr($iCode, 0, 1)))
                );
            }
            if (!isset($this->aResponseCodes[$iCode])) {
                throw new fatalException($this, 'Unknown response code "' . $iCode . '"');
            }
        }
        return $this;
    } // function _checkResponseText

    /**
     * Set special response type
     * @param integer $iCode
     * @param boolean $bSend
     * @return \fan\core\service\header
     */
    protected function _setSpecialType($iCode, $bSend)
    {
        $this->setResponseType($iCode);
        if ($bSend) {
            $this->sendResponseType($iCode, null);
        }
        return $this;
    } // function _setSpecialType

    // ======== The magic methods ======== \\

    public function __set($sKey, $mValue)
    {
        return $this->addHeader($sKey, $mValue);
    }

    public function __get($sKey)
    {
        return $this->getHeader($sKey);
    }

    // ======== Required Interface methods ======== \\
} // class \fan\core\service\header
?>