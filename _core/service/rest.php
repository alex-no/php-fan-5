<?php namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * REST-client service
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class rest extends \fan\core\base\service\multi
{
    /**
     * @var \fan\core\service\rest[] Service's Instances
     */
    private static $aInstances;

    /**
     * @var string Default Connection Name
     */
    private static $sDefaultName;

    /**
     * @var string Connection Name
     */
    private $sConnectionName;

    /**
     * Service's constructor
     */
    protected function __construct($sConnectionName)
    {
        parent::__construct(false);

        if (empty(self::$sDefaultName)) {
            self::$sDefaultName = $this->oConfig['DEFAULT_CONNECTION'];
        }
        if (empty($sConnectionName)) {
            $sConnectionName = self::$sDefaultName;
        }
        if(!isset($this->oConfig['CONNECTION'][$sConnectionName])) {
            $this->sErrorMessage = 'Undefind connection name: ' . $sConnectionName;
            throw new fatalException($this, 'Undefined connection name <b>' . $sConnectionName . '</b>');
        }

        $this->sConnectionName = $sConnectionName;

        self::$aInstances[$sConnectionName] = $this;
    } // function __construct

    /**
     * Service's destructor
     */
    public function __destruct() {
    } // function __destruct

    /**
     * Get Service's instance of current service by $sConnectionName
     * If $sConnectionName isn't set - Get defaul instance
     * @param string $sConnectionName Connection name
     * @return \fan\core\service\rest
     */
    public static function instance($sConnectionName = NULL)
    {
        if (empty($sConnectionName)) {
            $sConnectionName = self::$sDefaultName;
        }
        if (!isset(self::$aInstances[$sConnectionName])) {
            $sClassName = __CLASS__;
            new $sClassName($sConnectionName);
        }
        if (empty($sConnectionName)) {
            $sConnectionName = self::$sDefaultName;
        }

        return self::$aInstances[$sConnectionName];
    } // function instance

    /**
     * РЎall Get Request
     * @param string $sUrlSuffix
     * @param string $mData
     * @return mixed
     */
    public function get($sUrlSuffix, $mData = null)
    {
        if (!empty($mData)) {
            if (is_array($mData)) {
                $sUrlSuffix .= '?' . http_build_query($mData, '', '&');
            } else {
                $sUrlSuffix .= '/' . urlencode($mData);
            }
        }
        $oCurl = $this->_getCurl($sUrlSuffix);

        $oCurl->setOption(CURLOPT_SSL_VERIFYPEER, false);

        return $this->_getResponse($oCurl);
    } // function _callGetRequest

    /**
     * РЎall Post Request
     * @param string $sUrlSuffix
     * @param mixed $mData
     * @return mixed
     */
    public function post($sUrlSuffix, $mData)
    {
        $oCurl = $this->_getCurl($sUrlSuffix);

        $oCurl->setHeaders(array('Content-Type: application/json', 'charset=utf-8'));
        $oCurl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $sPost = service('json')->encode($mData);
        return $this->_getResponse($oCurl, $sPost);
    } // function _callPostRequest

    /**
     * РЎall Delete Request
     * @param string $sUrlSuffix
     * @param string $sData
     * @return mixed
     */
    public function delete($sUrlSuffix, $sData)
    {
        $oCurl = $this->_getCurl($sUrlSuffix . '/' . $sData );

        $oCurl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $oCurl->setOption(CURLOPT_CUSTOMREQUEST, 'DELETE');

        return $this->_getResponse($oCurl);
    } // function _callDeleteRequest

    /**
     * РЎall Put Request
     * @param string $sUrlSuffix
     * @param string $sData
     * @return mixed
     */
    public function _callPutRequest($sUrlSuffix, $sData)
    {
        $oCurl = $this->_getCurl($sUrlSuffix . '/' . $sData );

        $oCurl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $oCurl->setOption(CURLOPT_CUSTOMREQUEST, 'PUT');
        $aPayCode = array('pay_code' => $sData);
        $oCurl->setOption(CURLOPT_POSTFIELDS, http_build_query($aPayCode));

        return $this->_getResponse($oCurl);
    } // function _callPutRequest


    /**
     * Get Connection name
     * @return string Connection name
     */
    public function getConnectionName() {
        return $this->sConnectionName;
    } // function getConnectionName

    /**
     * Get CURL-service
     * @param string $sUrlSuffix
     * @return \fan\core\service\curl
     */
    protected function _getCurl($sUrlSuffix)
    {
        $aConf = $this->getConfig(array('CONNECTION', $this->sConnectionName, 'url'));
        $sUrl  = $aConf['server'] . '/' . $aConf['request'];
        if (!empty($aConf['user'])) {
            $sUrl = $aConf['user'] . ':' . $aConf['pass'] . '@' . $sUrl;
        }
        $sUrl = (empty($aConf['proyocol']) ? 'http' : $aConf['proyocol']) . '://' . $sUrl;
        if (!empty($sUrlSuffix)) {
            $sUrl .= '/' . $sUrlSuffix;
        }
        return service('curl', $sUrl);
    } // function _getCurl

    /**
     * Get Response from CURL
     * @param \fan\core\service\curl $oCurl
     * @param mixed $mPost
     * @return mixed
     * @throws fatalException
     */
    protected function _getResponse(\fan\core\service\curl $oCurl, $mPost = null)
    {
        $sURL      = $oCurl->getInfo(CURLINFO_EFFECTIVE_URL);
        $sResponse = $oCurl->exec($mPost);
        $oCurl->close();

        $oJson = service('json');
        /* @var $oJson \fan\core\service\json */
        $mDecoded  = $oJson->decode($sResponse, true);
        if ($oJson->getError() > 0) {
            if (is_string($mPost)) {
                $mPost = preg_replace('/\"Password\"\:\"[^"]+?\"/', '"Password":"*******"', $mPost);
            } elseif (isset($mPost['Password'])) {
                $mPost['Password'] = '*******';
            }
            $sErrMsg  = $oJson->getErrorText();
            $sErrMsg .= '<br /><br />URL: ' . $sURL .'<br />Request:<br /><pre>' . (is_string($mPost) ? $mPost : var_export($mPost, true)) . '</pre>';
            service('error')->logErrorMessage($sErrMsg, 'REST response error', htmlentities($sResponse));
            throw new fatalException($this, 'Illegal response for REST "' . $sURL . '".');
        }
        return $mDecoded;
    } // function _getResponse

} // class \fan\core\service\rest
?>