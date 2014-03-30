<?php namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * CURL service
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
 * @version of file: 05.02.002 (31.03.2014)
 */
class curl extends \fan\core\base\service\multi
{
    /**
     * @var array Service's Instances
     */
    private static $aInstances;

    /**
     * @var handle CURL instance
     */
    protected $oCurl;

    /**
     * @var string URL
     */
    protected $sUrl = '';

    /**
     * @var array getted headers
     */
    protected $aHeaders = array();

    /**
     * Content (result of CURL-request)
     * @var string
     */
    protected $sContent = null;

    /**
     * @var string line Separator
     */
    protected $sSeparator = "\n";

    /**
     *
     * @var boolean Response
     */
    protected $bSeparateResponse = true;

    /**
     * Service's constructor
     */
    protected function __construct($sUrl)
    {
        parent::__construct(true);

        $this->sUrl  = $sUrl;
        $this->oCurl = curl_init($sUrl);

        $this->setOption(CURLOPT_RETURNTRANSFER, 1);
        $this->setOption(CURLOPT_HEADER, 1);

        $oConf = $this->oConfig;
        if ($oConf['CURLOPT_PROXY']) {
           $this->setOption(CURLOPT_PROXY, $oConf['CURLOPT_PROXY']);
        }
        if ($oConf['CURLOPT_PROXYUSERPWD']) {
           $this->setOption(CURLOPT_PROXYUSERPWD, $oConf['CURLOPT_PROXYUSERPWD']);
        }
    } // function __construct

    /**
     * Service's destructor
     *
     */
    public function __destruct()
    {
        $this->close();
    } // function __destruct

    /**
     * Get Service's instance of current service
     * @return service_curl
     */
    public static function instance($sUrl = null)
    {
        if(!$sUrl) {
            return null;
        }
        if (!isset(self::$aInstances[$sUrl])) {
            $sClassName = __CLASS__;
            self::$aInstances[$sUrl] = new $sClassName($sUrl);
        }
        return self::$aInstances[$sUrl];
    } // function instance

    /**
     * Set CURL-option
     * @param integer $nKey
     * @param mixed $mVal
     */
    public function setOption($nKey, $mVal)
    {
        curl_setopt($this->oCurl, $nKey, $mVal);
    } // function setOption

    /**
     * Set curl headers
     * @param array $aHeaders array of additional Headers
     */
    public function setHeaders($aHeaders = array())
    {
        if ($aHeaders) {
            $this->setOption(CURLOPT_HTTPHEADER, $aHeaders);
        }
    } // function setHeaders

    /**
     * Set CURL-timeout
     * @param integer $nTimeout
     */
    public function setTimeout($nTimeout)
    {
        $this->setOption(CURLOPT_TIMEOUT, $nTimeout);
    } // function setTimeout

    /**
     * Set CURL-Cookies
     * @param string $mCookies
     */
    public function setCookies($mCookies)
    {
        if (is_array($mCookies)) {
            $sCookies = '';
            foreach ($mCookies as $k => $v) {
                if (!empty ($sCookies)) {
                    $sCookies .= '; ';
                }
                $sCookies .= $k . '=' . $v;
            }
        } else {
            $sCookies = $mCookies;
        }
        $this->setOption(CURLOPT_COOKIE, $sCookies);
    } // function setCookies

    /**
     * Close
     */
    public function close()
    {
        if (!is_null($this->oCurl)) {
            curl_close($this->oCurl);
            $this->oCurl = null;
            self::$aInstances[$this->sUrl] = null;
        }
    } // function close

    /**
     * Get curl information
     * @param number $nOption
     * @return unknown
     */
    public function getInfo($nOption = null)
    {
        return is_null($nOption) ? curl_getinfo($this->oCurl) : curl_getinfo($this->oCurl, $nOption);
    } // function getInfo

    /**
     * Get curl error
     * @return string
     */
    public function getError()
    {
        return curl_error($this->oCurl);
    } // function getError

    /**
     * Execute request
     * @param mixed $mPostData Post data
     * @return string Content
     */
    public function exec($mPostData = null, $bAllowExcept = true)
    {

        if (!is_null($mPostData)) {
            if (is_array($mPostData)) {
                $mOptData = array();
                foreach ($mPostData as $k => $v) {
                    if (is_array($v)) {
                        $this->_convPostArray($mOptData, $k, $v);
                    } else {
                        $mOptData[$k] = $v;
                    }
                }
            } else {
                $mOptData = $mPostData;
            }
            $this->setOption(CURLOPT_POST, 1);
            $this->setOption(CURLOPT_POSTFIELDS, $mOptData);
        }

        $this->aHeaders = array();
        $this->sContent = null;

        $sData = curl_exec($this->oCurl);
        if ($sData) {
            $sSeparator = $this->_getSeparator($sData);
            list($sHeaders, $sBody) = explode($sSeparator . $sSeparator, $sData, 2);
            $sHeaders1 = '';
            while (trim($sHeaders) == 'HTTP/1.1 100 Continue') {
                list($sHeaders, $sBody) = explode($sSeparator . $sSeparator, $sBody, 2);
                $sHeaders1 .= $sHeaders . $sSeparator;
            }
            foreach (explode($sSeparator, $sHeaders1 . $sHeaders) as $v0) {
                if (strstr($v0, ':')) {
                    list($k, $v) = explode(':', $v0, 2);
                    $this->aHeaders[trim($k)] = trim($v);
                } elseif (substr($v0, 0, 5) == 'HTTP/') {
                    $this->aHeaders['HTTP'] = trim($v0);
                }
            }
            $this->sContent = $sBody;
        }

        $sErr = $this->getError();
        if ($sErr && $bAllowExcept) {
            throw new fatalException($this, 'There is CURL error ocured: <b>' . $sErr . '</b>');
        }

        return $this->getContent();
    } // function exec

    /**
     * Set bSeparateResponse
     * @return \service_curl
     */
    public function setSeparateResponse()
    {
        $this->bSeparateResponse = false;
        return $this;
    }

    /**
     * Get Headers
     * @param string $sKey - name of header
     * @return mixed array of headers or value of header
     */
    public function getHeaders($sKey = null)
    {
        return $sKey ? @$this->aHeaders[$sKey] : $this->aHeaders;
    } // function getHeaders

    /**
     * Get Content (result of CURL-request)
     * @return string
     */
    public function getContent()
    {
        return $this->sContent;
    } // function getContent

    /**
     * Get Separator
     * @param string $sData - CURL-data
     * @return string
     */
    protected function _getSeparator($sData = null)
    {
        if ($sData) {
            $nPos = strpos($sData, "\r");
            if ($nPos) {
                $this->sSeparator = $nPos && $sData{$nPos + 1} == "\n" ? "\r\n" : "\r";
            }
        }
        return $this->sSeparator;
    } // function _getSeparator

    /**
     * Convertation array for POST-data
     * @param array $aOptData - Converted data
     * @param string $sKey - element key
     * @param mixed $mData - element value
     */
    protected function _convPostArray(&$aOptData, $sKey, $mData)
    {
        foreach ($mData as $k => $v) {
            if (is_array($v)) {
                $this->_convPostArray($aOptData, $sKey . '[' . $k . ']', $v);
            } else {
                $aOptData[$sKey . '[' . $k . ']'] = $v;
            }
        }
    } // function _convPostArray

} // class \fan\core\service\curl
?>