<?php namespace core\service\matcher;
use project\exception\service\fatal as fatalException;
/**
 * Description of item
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
 * @version of file: 05.001 (29.09.2011)
 *
 * @property-read \core\service\matcher\item\source  $source
 * @property-read \core\service\matcher\item\uri     $uri
 * @property-read \core\service\matcher\item\handler $handler
 * @property-read \core\service\matcher\item\parsed  $parsed
 */
class item implements \ArrayAccess
{
    /**
     * Index of this item
     * @var array
     */
    protected $iIndex;

    /**
     * Allowed property
     * @var array
     */
    protected $aData = array(
        // \core\service\matcher\item\source
        'source'  => null,
        // \core\service\matcher\item\uri
        'uri'     => null,
        // \core\service\matcher\item\handler
        'handler' => null,
        // \core\service\matcher\item\parsed
        'parsed'  => null,
    );

    /**
     * Facade of service
     * @var core\service\matcher
     */
    protected $oFacade = null;

    public function __construct($iIndex)
    {
        $this->iIndex = $iIndex;
        foreach ($this->aData as $k => &$v) {
            $sClass = '\project\service\matcher\item\\' . $k;
            $v = new $sClass($this);
        }
    }

    // ========== Public interface functions ========== \\
    /**
     * Init item by Out request
     * @param string $sRequest
     * @param string $sHost
     */
    public function initOut($sRequest, $sHost)
    {
        // Save current source data
        $this->aData['source']['request'] = $sRequest;
        $this->aData['source']['host']    = $sHost;

        // Save current array of URI
        foreach ($this->_parseRequestedUri($sRequest, $sHost) as $k => $v) {
            $this->aData['uri'][$k] = $v;
        }
    } // function initOut
    /**
     * Init item by Command Line Interface
     * @param string $sFile
     * @param string $sPath
     */
    public function initCli($sFile, $sPath)
    {
        // Save current source data
        $this->aData['source']['file'] = $sFile;
        $this->aData['source']['path'] = $sPath;

        // Save current array of CLI
        foreach ($this->_parseRequestedCli($sFile, $sPath) as $k => $v) {
            $this->aData['cli'][$k] = $v;
        }
    } // function initCli

    /**
     * Set Facade
     * @param core\service\matcher $oFacade
     */
    public function setFacade(\core\service\matcher $oFacade)
    {
        $this->oFacade = $oFacade;
        foreach ($this->aData as $v) {
            $v->setFacade($oFacade);
        }
        return $this;
    } // function setFacade

    /**
     * Get Facade
     * @return core\service\matcher
     */
    public function getFacade()
    {
        return $this->oFacade;
    } // function getFacade

    /**
     * Get Index
     * @return integer
     */
    public function getIndex()
    {
        return $this->iIndex;
    } // function getIndex

    /**
     * Get handler
     * @return item/handler
     */
    public function getHandler()
    {
        if (empty($this->aData['handler']['method'])){
            foreach ($this->_defineHandler() as $k => $v) {
                $this->aData['handler'][$k] = $v;
            }
        }
        return $this->aData['handler'];
    } // function getIndex


    /**
     * There is defined:
     *  - Application name
     *  - Requested locale
     *  - URL-prefix of application
     *  - Cleaned request path (without app and locale)
     *  - query string
     */
    public function preParseRequest($bShiftCurrent)
    {
        $aUri = $this['uri'];
        $aSubject = array(
            'request' => $aUri['path'] . (empty($aUri['query']) ? '' : '?' . $aUri['query']),
            'host'    => $aUri['host'],
            'full'    => $aUri['full'],
        );

        $sAppName = null;
        $aReqData = array();
        $sPathPos = null;
        $oLocale  = \project\service\locale::instance();
        $aLanguages      = $oLocale->getAvailableLanguages();
        $sRegexpLanguage = implode('|', array_keys($aLanguages));

        $sPrefix = '';
        foreach ($this->oFacade->getConfig('app', array()) as $k => $v) {
            $sRegexp = str_replace('{LANGUAGE}', $sRegexpLanguage, $v['regexp']);
            $sWay    = isset($v['way']) ? $v['way'] : 'path';
            if (preg_match($sRegexp, $aSubject[$sWay], $aMatches)) {
                if (isset($v['language']) && !empty($aMatches[$v['language']])) {
                    // ToDo: Redesign Language defining there (by subscribe)
                    $oLocale->setLanguage($aMatches[$v['language']]);
                }
                $sAppName = $k;
                $aReqData = $aMatches;
                $sPathPos = empty($v['path']) ? null : $v['path'];
                $sPrefix  = empty($v['prefix']) ? '' : (empty($aMatches[$v['prefix']]) ? '' : $aMatches[$v['prefix']]);
                break;
            }
        }

        if (empty($aReqData)) {
            $sSrcPath = $aSubject['request'];
        } elseif (empty($sPathPos)) {
            $nLen     = strlen($aReqData[0]);
            $sSubj    = $aSubject[$sWay];
            $sSrcPath = substr($sSubj, 0, $nLen) == $aReqData[0] ? substr($sSubj, $nLen) : $aSubject['request'];
        } else {
            $sSrcPath = '';
            $aParts   = explode('-', $sPathPos);
            foreach ($aParts as $v) {
                if (!empty($aReqData[$v])) {
                    $sSrcPath .= $aReqData[$v];
                }
            }
        }

        $oParsed = $this->aData['parsed'];
        $oParsed['app_name']   = $sAppName;
        $oParsed['app_prefix'] = $sPrefix;
        $oParsed['language']   = $oLocale->getLanguage();

        $nQueryPos = strpos ($sSrcPath, '?');
        if ($nQueryPos === false) {
            $oParsed['src_path'] = $sSrcPath;
            $oParsed['query']    = '';
        } else {
            $oParsed['src_path'] = substr($sSrcPath, 0, $nQueryPos);
            $oParsed['query']    = substr($sSrcPath, $nQueryPos);
        }

        // ToDo: Check all paths
        //   - local (disk-paths) must point by last item;
        //   - outer (URN) must point by current item;
        // If this is departed from a rule - will be big error when AppName is changed
        \project\service\application::instance()->setAppName($sAppName);
        return $this;
    } // function preParseRequest


    /**
     * Parse Request
     */
    public function parseRequest()
    {
        $oParsed = $this->aData['parsed'];
        $aData   = explode('/', $oParsed['src_path']);

        $sRegExp = $this->_getConfig(array('app', $oParsed['app_name'], 'regexp_trim_ext'));
        if (empty($sRegExp)) {
            $sRegExp = $this->_getConfig('default_regexp_trim_ext', '/^(.+)\\.(?:php|html?)/');
        }
        $aMatches = null;
        if (!empty($aData) && preg_match($sRegExp, end($aData), $aMatches)) {
            $aData[count($aData) - 1] = $aMatches[1];
        }

        $sHandlerKey = ucfirst(strtolower($this->getHandler()->key));
        $sMethodName = empty($sHandlerKey) || !method_exists($this, '_parseRequestFor' . $sHandlerKey) ? null : '_parseRequestFor' . $sHandlerKey;
        if (empty($sMethodName)) {
            $oParsed['main_request'] = array();
            $oParsed['add_request']  = array();
        } else {
            $this->$sMethodName($oParsed, $aData);
        }
        return $this;
    } // function parseRequest

    /**
     * Return all data
     * @return array
     */
    public function toArray()
    {
        return $this->aData;
    } // function toArray

    // ========== Private/protected functions ========== \\
    /**
     * Parse requested URI - return elelements URI as array
     * @param string $sRequest
     * @param string $sHost
     * @return array
     * @throws fatalException
     */
    protected function _parseRequestedUri($sRequest, $sHost)
    {
        // Prepare global URI-parameters
        if (empty($this->iIndex)) {
            $sScheme   = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
            $sUserName = $sPassword = $sAnchor = null; // ToDo: Set start default values there
            if (empty($sHost)) {
                $sHost = $_SERVER['HTTP_HOST'];
            }
        } else {
            $aPrevUri  = $this->oFacade->getUri($this->iIndex - 1);
            $sScheme   = $aPrevUri['scheme'];
            $sUserName = $aPrevUri['user'];
            $sPassword = $aPrevUri['pass'];
            $sAnchor   = $aPrevUri['fragment'];
            if (empty($sHost)) {
                $sHost = $aPrevUri['host'];
            } elseif (!$this->oFacade->getConfig('allow_switch_host', false)) {
                throw new fatalException($this->oFacade, 'Host switching isn\'t allowed there');
            }
        }

        // Prepare Path and Query
        if (strpos($sRequest, '?') === false) {
            $sPath  = $sRequest;
            $sQuery = empty($this->iIndex) ? null : $aPrevUri['query'];
        } else {
            list($sPath, $sQuery) = explode('?', $sRequest, 2);
        }

        $aUri = array (
            'scheme'   => $sScheme,
            'host'     => $sHost,
            'user'     => $sUserName,
            'pass'     => $sPassword,
            'path'     => $sPath,
            'query'    => empty($sQuery) ? null : $sQuery,
            'fragment' => $sAnchor,
        );

        // Make full URI
        $sUri = $sScheme . '://' . $sUserName;
        if ($sPassword) {
            $sUri .= ':' . $sPassword;
        }
        if ($sUserName || $sPassword) {
            $sUri .= '@';
        }
        $sUri .= $sHost . $sPath;
        if ($sQuery) {
            $sUri .= '?' . $sQuery;
        }
        if ($sAnchor) {
            $sUri .= '#' . $sAnchor;
        }
        $aUri['full'] = $sUri;

        return $aUri;
    } // function _parseRequestedUri
    /**
     * Parse requested CLI - return elelements CLI as array
     * @param string $sFile
     * @param string $sPath
     * @return array
     * @throws fatalException
     */
    protected function _parseRequestedCli($sFile, $sPath)
    {
        $aCli = array (
            'file' => $sFile,
            'path' => $sPath,
            'argv' => empty($_SERVER['argv']) ? array() : $_SERVER['argv'],
        );

        return $aCli;
    } // function _parseRequestedCli

    /**
     * Define Handler of request
     * @return array
     */
    protected function _defineHandler()
    {
        if (\bootstrap::isCli()) {
            return $this->_handlerDefinerSapiName();
        } elseif (empty($this->iIndex)) {
            // Find handler by RegExp
            foreach ($this->oFacade->getConfig('plain', array()) as $k => $v) {
                $sMethod = '_handlerDefiner' . ucfirst($v['definer']);
                if (!method_exists($this, $sMethod)) {
                    $this->_makeException('Incorrect Handler definer at the Config of Matcher');
                }
                $aResult = $this->$sMethod($k, $v);

                if (!empty($aResult)) {
                    return $aResult;
                }
            }

            // Set default handler if it doesn't macth any RegExp
            $aDefault = $this->oFacade->getConfig('default_handler', array(
                'key'    => 'tab',
                'method' => '\project\service\tab::getCode',
                'param'  => null
            ));
            return array(
                'key'    => $aDefault['key'],
                'method' => $aDefault['method'],
                'param'  => empty($aDefault['param']) ? null : $aDefault['param'],
            );
        }
        // Copy handler from first item
        $oFirstHandler = $this->oFacade->getHandler(0);
        return array(
            'key'    => $oFirstHandler['key'],
            'method' => $oFirstHandler['method'],
            'param'  => $oFirstHandler['param'],
        );
    } // function _defineHandler

    /**
     * Definer of handler for SapiName
     * @return array
     */
    protected function _handlerDefinerSapiName()
    {
        return array(
            'key'     => 'cli',
            'method'  => '\project\service\cli::getContent',
            'param'   => array(),
            'ctrlKey' => null,
        );
    } // function _handlerDefinerSapiName

    /**
     * Definer of handler for Request
     * @param array $aData
     * @param string $sKey
     */
    protected function _handlerDefinerRequest($sKey, $aData)
    {
        $aMatches = array();
        if (preg_match($aData['regexp'], $this->aData['uri']['path'], $aMatches)) {
            return array(
                'key'     => 'plain',
                'method'  => '\project\service\plain::getContent',
                'param'   => array($sKey, $aData['class'], $this->_getControllerMethod($aMatches, $aData['method'])),
                'ctrlKey' => $sKey,
                'mReqKey' => isset($aMatches[1]) ? $aMatches[1] : null,
            );
        }
        return null;
    } // function _handlerDefinerRequest

    /**
     *
     * @param \core\service\matcher\item\parsed $oParsed
     * @param array $aData
     * @return \core\service\matcher\item
     */
    protected function _parseRequestForTab(\core\service\matcher\item\parsed $oParsed, array $aData)
    {
        $sPath  = \bootstrap::getLoader()->project;
        $sPath .= '/app/' . $oParsed['app_name'] . '/' . $this->_getConfig('main_block_dir', 'main');

        $aMainRequest = array();
        foreach ($aData as $k => $v) {
            if (empty($v)) {
                unset($aData[$k]);
            } else {
                if (is_file($sPath . '/' . $v . '.php')) {
                    $aMainRequest[] = $v;
                    unset($aData[$k]);
                    if (!isset($aData[$k + 1]) || !is_dir($sPath . '/' . $v) || !is_dir($sPath . '/' . $aData[$k + 1]) && !is_file($sPath . '/' . $aData[$k + 1] . '.php')) {
                        $oParsed['main_request'] = $aMainRequest;
                        $oParsed['add_request']  = array_merge(array(), $aData);
                        return;
                    }
                } elseif (is_dir($sPath . '/' . $v)) {
                    $sPath .= '/' . $v;
                    $aMainRequest[] = $v;
                    unset($aData[$k]);
                } else {
                    break;
                }
            }
        }

        if (empty($aData) && is_file($sPath . '/' . $this->_getConfig('directory_index', 'index.php'))) {
            $oParsed['main_request'] = array_merge($aMainRequest, array('index'));
            $oParsed['add_request']  = array();
        }

        return $this;
    } // function _parseRequestForTab
    /**
     *
     * @param \core\service\matcher\item\parsed $oParsed
     * @param array $aData
     * @return \core\service\matcher\item
     */
    protected function _parseRequestForPlain(\core\service\matcher\item\parsed $oParsed, array $aData)
    {
        $sMainRequstPref = $this->aData['handler']->mReqKey;
        if (empty($sMainRequstPref)) {
            $oParsed['main_request'] = array(array_shift($aData));
            $oParsed['add_request']  = $aData;
        } else {
            $aMR = explode('/', $sMainRequstPref);
            $oParsed['main_request'] = $aMR;
            foreach ($aMR as $v) {
                if ($aData[0] == $v) {
                    array_shift($aData);
                }
            }
            $oParsed['add_request']  = $aData;
        }
        return $this;
    } // function _parseRequestForPlain
    /**
     *
     * @param \core\service\matcher\item\parsed $oParsed
     * @param array $aData
     * @return \core\service\matcher\item
     */
    protected function _parseRequestForCli(\core\service\matcher\item\parsed $oParsed, array $aData)
    {
        return $this;
    } // function _parseRequestForCli

    /**
     * Check Key
     * @param string $sKey
     */
    protected function _checkKey($sKey)
    {
        if (!array_key_exists($sKey, $this->aData)) {
            $this->_makeException('Invalid key "' . $sKey . '" while accessing the item of matcher.');
        }
    } // function _checkKey

    /**
     * Make Exception
     * @param string $sErrMsg
     * @throws fatalException
     * @throws \project\exception\fatal
     */
    protected function _makeException($sErrMsg)
    {
        if ($this->oFacade) {
            throw new fatalException($this->oFacade, $sErrMsg);
        }
        throw new \project\exception\fatal($sErrMsg);
    } // function _makeException

    /**
     * Get Method name of Controller
     * @param array $aMatches
     * @param string $sPattern
     * @return string
     */
    protected function _getControllerMethod($aMatches, $sPattern)
    {
        for ($i = 1; $i < count($aMatches); $i++) {
            $sPattern = str_replace('{\\' . $i . '}', ucfirst(strtolower($aMatches[$i])), $sPattern);
        }

        $aTmp = explode('_', $sPattern);
        $sRes = array_shift($aTmp);
        foreach ($aTmp as $v) {
            $sRes .= ucfirst($v);
        }

        return $sRes;
    } // function _getControllerMethod

    /**
     * Get Config row of Matcher
     * @param mixed $mKey
     * @param mixed $mDefault
     * @return mixed
     */
    public function _getConfig($mKey, $mDefault = null)
    {
        return $this->oFacade->getConfig()->get($mKey, $mDefault);
    } // function _getConfig

    // ========== Magic functions ========== \\
    /**
     * Offset Set for array access
     * @param string $sKey
     * @param mixed $mValue
     */
    public function offsetSet($sKey, $mValue)
    {
        $this->_checkKey($sKey);
        $this->_makeException('Isn\'t allowed direct set property of item of matcher. Try to set "' . $mValue . '" for "' . $sKey . '"');
    }

    public function offsetExists($sKey)
    {
        $this->_checkKey($sKey);
        return !is_null($this->aData[$sKey]);
    }

    public function offsetUnset($sKey)
    {
        $this->_checkKey($sKey);
        $this->_makeException('Isn\'t allowed unset property of item of matcher.');
    }

    public function offsetGet($sKey)
    {
        $this->_checkKey($sKey);
        $sMethod = 'get' . ucfirst($sKey);
        return method_exists($this, $sMethod) ? $this->$sMethod() : $this->aData[$sKey];
    }

    public function __set($sKey, $mValue)
    {
        return $this->offsetSet($sKey, $mValue);
    }

    public function __get($sKey)
    {
        return $this->offsetGet($sKey);
    }

    // ======== Required Interface methods ======== \\
} // class \core\service\matcher\item
?>