<?php namespace fan\core\service;
/**
 * SOAP operation service
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
 * @version of file: 05.02.007 (31.08.2015)
 */
class soap extends \fan\core\base\service\multi
{
    /**
     * @var SoapClient
     */
    private $oSoapObj;
    /**
     * @var \SoapFault
     */
    private $oSoapFault;
    /**
     * @var boolean - enable/disable error-logging
     */
    private $bLogEnabled;

    /**
     * Soap Headers
     * @var array
     */
    private $aSoapHeaders = array();

    /**
     * Service's constructor
     */
    protected function __construct($bLogEnabled)
    {
        parent::__construct(false);
        $bEnableCache = $this->oConfig['CACHE_ENABLED'] ? 1 : 0;
        ini_set('soap.wsdl_cache_enabled', $bEnableCache);
        if ($bEnableCache) {
            if ($this->oConfig['CACHE_DIR']) {
                ini_set('soap.wsdl_cache_dir', $this->oConfig['CACHE_DIR']);
            }
            if ($this->oConfig['CACHE_TTL']) {
                ini_set('soap.wsdl_cache_ttl', $this->oConfig['CACHE_TTL']);
            }
        }
        if ($this->oConfig['TRACE_ENABLED']) {
            $this->oConfig['PARAM']['trace'] = 1;
        }
        $this->bLogEnabled = $bLogEnabled;
    } // function __construct

    /**
     * Get Service's instance of current service
     * @param string $sWsdlFile wsdl-file name
     * @param array $aParam parameter to create SOAP
     * @return \core\service\soap
     */
    public static function instance($sWsdlFile, $aParam = null, $bLogEnabled = true)
    {
        $oInstance = new self($bLogEnabled);
        $oInstance->_initSoapObj($sWsdlFile, $aParam);
        return $oInstance;
    } // function instance

    /**
     * Init Path to image and check exist file
     * @param string $sFuncName SOAP function name
     * @param array $aArguments SOAP function arguments
     * @param array $aOptions SOAP options
     * @return object - Soap object if operation is successful
     */
    public function call($sFuncName, $aArguments = array(), $aOptions = null)
    {
        if (!is_object($this->oSoapObj)) {
            $this->_makeServiceException('Soap Object is not set');
        }
        if (!is_string($sFuncName)) {
            $this->_makeServiceException('Error! Function name is not string there: (' . gettype($sFuncName) . ') "' . strval($sFuncName) . '"');
        }

        $oErr = service('error');
        /* @var $oErr \fan\core\service\error */
        if (!is_array($aArguments)) {
            $oErr->logErrorMessage('Error! Arguments is not array there: (' . gettype($aArguments) . ') "' . strval($aArguments) . '"', 'SOAP: incorrect arguments.', null, true);
            $aArguments = array();
        }
        if (!is_null($aOptions) && !is_array($aOptions)) {
            $oErr->logErrorMessage('Error! Options is not array there: (' . gettype($aOptions) . ') "' . strval($aOptions) . '"', 'SOAP: incorrect options.', null, true);
            $aOptions = array();
        }
        try {
            $this->oSoapFault   = null;
            $aSoapHeaders       = $this->aSoapHeaders;
            $this->aSoapHeaders = array();
            $oErr->setErrorBuffering();
            $mRet = $this->oSoapObj->__soapCall($sFuncName, $aArguments, $aOptions, empty($aSoapHeaders) ? null : $aSoapHeaders);
            $aErr = $oErr->offErrorBuffering();
            if ($aErr) {
                $aLastErr = end($aErr);
                $oErr->logErrorMessage($aLastErr['sys_err_message'], 'Soap call error', 'SOAP method: ' . $sFuncName, true);
            }
            return $mRet;
        } catch (\SoapFault $oSoapErr) {
            $this->oSoapFault = $oSoapErr;
            if ($this->bLogEnabled) {
                $oErr->logSoapError($oSoapErr);
            }
            return null;
        }
    } // function call

    /**
     * Set SOAP header
     * @param string $sNameSpace SOAP name-space
     * @param array $sName SOAP name of key
     * @param array $mData SOAP header data
     */
    public function setHeader($sNameSpace, $sName, $mData = null)
    {
        $this->aSoapHeaders[] = new \SoapHeader($sNameSpace, $sName, $mData);
    } // function setHeader


    /**
     * Set SOAP var
     * @param array $mData header data
     * @param array $aVarParam
     * @param array $aLevels
     * @return mixed
     */
    public function setSoapVar($mData, $aVarParam = array(), $aLevels = array(0))
    {
        sort($aLevels);
        return $this->_setSoapVarRecursive($mData, $aVarParam, $aLevels, 0);
    } // function setSoapVar

    /**
     * Allow Logging of SOAP-error
     * @param boolean $bLogEnabled
     */
    public function allowErrLogging($bLogEnabled)
    {
        $this->bLogEnabled = !empty($bLogEnabled);
    } // function allowErrLogging

    /**
     * Check is SOAP error;
     * @return boolean - true if error occurred
     */
    public function isError()
    {
        return !is_null($this->oSoapFault);
    } // function isError

    /**
     * Get object of SoapFault;
     * @return \SoapFault
     */
    public function getSoapFault()
    {
        return $this->oSoapFault;
    } // function getSoapFault

    /**
     * Get debug info.
     */
    public function getDebugInfo()
    {
        if (!$this->oSoapObj) {
            return null;
        } else if (!$this->oConfig['TRACE_ENABLED']) {
            return '';
        }

        $sMsg  = '<fieldset class="soap_log"><legend>Sent Request DATA</legend>';
        $sMsg .= '<fieldset><legend>Headers</legend><div>' . trim($this->oSoapObj->__getLastRequestHeaders()) . '</div></fieldset>';
        $sMsg .= '<fieldset><legend>Request</legend><pre>' . $this->_format4log($this->oSoapObj->__getLastRequest()) . '</pre></fieldset>';
        $sMsg .= '</fieldset>';

        $sMsg .= '<fieldset class="soap_log"><legend>Received Response DATA</legend>';
        $sMsg .= '<fieldset><legend>Headers</legend><div>' . trim($this->oSoapObj->__getLastResponseHeaders()) . '</div></fieldset>';
        $sMsg .= '<fieldset><legend>Response</legend><pre>' . $this->_format4log($this->oSoapObj->__getLastResponse()) . '</pre></fieldset>';
        $sMsg .= '</fieldset>';
        return $sMsg;
    } // function getDebugInfo

    /**
     * Init SOAP Connect
     * @param string $sWsdlFile wsdl-file name
     * @param array $aParam parameter to create SOAP
     * @return \core\service\soap
     */
    protected function _initSoapObj($sWsdlFile, $aParam = null)
    {
        $bIsURL = preg_match('/^https?:\/\//', $sWsdlFile);
        $sWsdlFile_Full = $bIsURL ? $sWsdlFile : \bootstrap::parsePath($this->oConfig['WSDL_DIR']) . $sWsdlFile;

        if (isset($this->oConfig['PARAM'])) {
            if (!is_array($aParam)) {
                $aParam = array();
            }
            foreach ($this->oConfig['PARAM'] as $k => $v) {
                if (!array_key_exists($k, $aParam)) {
                    $aParam[$k] = $v;
                }
            }
        }

        if ($this->getConfig('BLOCK_SSL_VERIFY', false)) {
            if (!is_array($aParam)) {
                $aParam = array();
            }
            $aParam['stream_context'] = stream_context_create(array(
                'ssl' => array(
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ))
            );
        }

        if ($bIsURL || file_exists($sWsdlFile_Full)) {
            try {
                if (isset($aParam['soap_version'])) {
                    if (is_numeric($aParam['soap_version'])) {
                        $aParam['soap_version'] = (int)$aParam['soap_version'];
                    } else {
                        $aConst = get_defined_constants();
                        $aParam['soap_version'] = $aConst[array_val($aParam, 'soap_version')];
                    }
                }
                $this->oSoapObj = $aParam && is_array($aParam) ? new \SoapClient($sWsdlFile_Full, $aParam) : new \SoapClient($sWsdlFile_Full);
                return $this->oSoapObj;
            } catch (\SoapFault $oErr) {
                $this->oSoapFault = $oErr;
                service('error')->logSoapError($oErr);
                return;
            }
        } else {
            service('error')->logErrorMessage('Error. WSDL-file "' . $sWsdlFile_Full . '" isn\'t exist.');
            return;
        }
    } // function _initSoapObj

    /**
     * Set SOAP var recursive
     * @param array $mData
     * @param array $aVarParam
     * @param array $aLevels
     * @param integer $iCurrentLevel
     * @return mixed
     */
    protected function _setSoapVarRecursive($mData, $aVarParam, $aLevels, $iCurrentLevel)
    {
        if (is_array($mData)) {
            foreach ($mData as &$v) {
                $v = $this->_setSoapVarRecursive($v, $aVarParam, $aLevels, $iCurrentLevel + 1);
            }
        }
        if (in_array($iCurrentLevel, $aLevels) && !is_scalar($mData)) {
            $mData = new \SoapVar(
                $mData,
                SOAP_ENC_OBJECT,
                array_val($aVarParam, 'type_name'),
                array_val($aVarParam, 'type_namespace'),
                array_val($aVarParam, 'node_name'),
                array_val($aVarParam, 'node_namespace')
            );
        }
        return $mData;
    } // function _setSoapVarRecursive

    /**
     * Format XML-code
     * @param string $sXml
     * @return string
     */
    protected function _format4log($sXml)
    {
        if ($sXml == '') {
            return '';
        }
        $oXml = new \DOMDocument();
        $oXml->loadXML($sXml);
        $oXml->formatOutput = true;
        return htmlspecialchars($oXml->saveXML());
    } // function _format4log
} // class \fan\core\service\soap
?>