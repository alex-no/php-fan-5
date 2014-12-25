<?php namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * Request service
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class request extends \fan\core\base\service\single
{
    /**
     * @var array Requested data
     */
    private $aData = array(
        'A0' => null, // Add(itional) request (See \fan\core\service\matcher\item\parsed)
        'A1' => null, // Extra Add(itional) request by delimiter: $key => $val
        'B'  => null, // Both = Main request + Add request (See \fan\core\service\matcher\item\parsed)
        'C'  => null, // Cookies:               $_COOKIE
        'E'  => null, // Environment variables: $_ENV
        'F'  => null, // Files (uploaded):      $_FILES
        'G'  => null, // Get parameters:        $_GET
        'H'  => null, // Headers
        'M'  => null, // Main request (See \fan\core\service\matcher\item\parsed)
        'O'  => null, // Option list in CLI-mode
        'P'  => null, // Post parameters:       $_POST
        'R'  => null, // Request parameters:    $_REQUEST
        'S'  => null, // Server data:           $_SERVER
    );

    /**
     * Data set by correspondence to global variables (static)
     * @var array
     */
    protected $aCorrespondence = array(
        'E' => '_ENV',
        'F' => '_FILES',
        'P' => '_POST',
        'R' => '_REQUEST',
        'S' => '_SERVER',
    );

    /**
     * Data set by special methods (dynamic)
     * @var array
     */
    protected $aMaker = array(
        'A' => '_makeAddRequest',
        'B' => '_makeBothRequest',
        'G' => '_makeGet',
        'M' => '_makeMainRequest',
    );
    /**
     * Data maker indexes (for internal/sham trnsfer)
     * @var array
     */
    protected $aMakerIndex = array(
        'A' => -2,
        'B' => -2,
        'G' => -2,
        'M' => -2,
    );

    /**
     * @var \fan\core\service\matcher
     */
    private $oMatcher = '';

    /**
     * @var string Default check order
     */
    private $sOrder;

    /**
     * Raw POST data
     * @var string
     */
    private $sRawPost = null;


    /**
     * Service's constructor
     */
    protected function __construct()
    {
        parent::__construct();
        $this->sOrder = strtoupper($this->getConfig('DEFAULT_ORDER', 'PAG'));

        // Ses all basic data
        $bIsMQ = get_magic_quotes_gpc();
        foreach ($this->aCorrespondence as $k => $v) {
            if (empty($GLOBALS[$v])) {
                $this->aData[$k] = array();
            } elseif ($bIsMQ && in_array($k, array('P', 'R'))) {
                $this->aData[$k] = $this->_stripSlashesDeep($GLOBALS[$v]);
            } else {
                $this->aData[$k] = $GLOBALS[$v];
            }
        }
        if (\bootstrap::isCli()) {
            $this->aData['O'] = $this->_makeOptions();
        } else {
            $this->aData['H'] = $this->_makeHeaders();
            $this->aData['C'] = $this->_makeCookies();
        }
    } // function __construct

    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\
    public function __get($sKey)
    {
        return $this->get($sKey);
    }
    public function __invoke($sKey, $sOrder = null, $mDefault = null)
    {
        return $this->get($sKey, $sOrder, $mDefault);
    }
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\

    /**
     * Get Request parameter
     *  Order keys:
     *   - A - Add(itional) request
     *   - B - Both = Main + Add(itional) request
     *   - C - Cookie
     *   - E - Environment
     *   - F - Files
     *   - G - Get data
     *   - H - Headers
     *   - M - Main request
     *   - P - Post data
     *   - R - Request data
     *   - S - Server data
     * @param string $sKey The Request key
     * @param string $sOrder Order of get values (For example: PGC - $_POST, $_GET, $_COOKIE). Possible letter "ACEFGHMPRS"
     * @param mixed $mDefault The Default value
     * @return mixed Request parameter's value
     */
    public function get($sKey, $sOrder = null, $mDefault = null, $bExtraAdd = true)
    {
        foreach ($this->_separateData($sOrder, $bExtraAdd) as $v) {
            if (isset($v[$sKey])) {
                return $v[$sKey];
            }
        }
        return $mDefault;
    } // function get

    /**
     * Get All Request parameter
     * @param string $sOrder Order keys see get
     * @param mixed $mDefault The Default value
     * @return mixed Request parameter's value
     */
    public function getAll($sOrder = null, $mDefault = array(), $bExtraAdd = true)
    {
        $aResult = array();
        foreach ($this->_separateData($sOrder, $bExtraAdd) as $v) {
            if (!empty($v)) {
                $aResult = array_merge_recursive_alt($v, $aResult);
            }
        }
        return empty($aResult) ? $mDefault : $aResult;
    } // function get_all

    /**
     * Get Raw Post-data and automatically convert them to array
     * @param string $sConvFormat
     * @return mixed
     */
    public function getRawPost($sConvFormat = 'json')
    {
        if (is_null($this->sRawPost)) {
            $this->sRawPost = file_get_contents('php://input'); // ToDo: Define different source there
        }
        switch (strtolower($sConvFormat)) {
        case 'json':
            return service('json')->decode($this->sRawPost);
        case 'xml':
            function conv($mItem)
            {
                if (is_object($mItem) || is_array($mItem)) {
                    return array_map('conv', (array)$mItem);
                }
                return $mItem;
            }
            return array_map('conv', (array)simplexml_load_string($this->sRawPost));
        }
        return $this->sRawPost;
    } // function getRawPost

    /**
     * Set Request (fake) parameter.
     * Recommended for debug only
     * @param string $sKey The Request key
     * @param mixed $mValue The Request parameter's value
     * @param string $sType Type of data (like $sOrder in get, but one symbol only)
     */
    public function set($sKey, $mValue, $sType = 'P')
    {
        if ($this->_isAllowToSet($sType)) {
            $this->aData[$sType][$sKey] = $mValue;
        }
    } // function set

    /**
     * Remove Request parameter.
     * @param string $sKey The Request key
     * @param string $sType Type of data (like $sOrder in get, but one symbol only)
     */
    public function remove($sKey, $sType = 'G')
    {
        if ($this->_isAllowToSet($sType)) {
            unset($this->aData[$sType][$sKey]);
        }
    } // function remove

    /**
     * Get query string
     * @return string Query string
     */
    public function getQueryString($bByGetData = true, $bCurrent =  true, $sSprtr = null)
    {
        $oMatcher = $this->_getMatcher();
        if ($bByGetData || empty($oMatcher)) {
            $aGet = $bCurrent && !empty($oMatcher) ? $this->getAll('G') : $_GET;
            return http_build_query($aGet, '', ($sSprtr ? : '&'));
        }
        $oItem = $bCurrent ? $oMatcher->getCurrentItem() : $oMatcher->getItem(0);
        return ltrim($oItem->parsed->query, '?');
    } // function getQueryString

    /**
     * Get short information about the request
     * @return string Request information
     */
    public function getInfoString()
    {
        $aInfo = array('HTTP_HOST', 'HTTP_REFERER', 'HTTP_USER_AGENT', 'REMOTE_ADDR', 'REMOTE_PORT', 'REQUEST_METHOD', 'QUERY_STRING', 'REQUEST_URI');
        $sInfo = '';
        foreach ($aInfo as $sKey) {
            if (isset($_SERVER[$sKey])) {
                $sInfo .= $sKey . ' = ' . $_SERVER[$sKey] . ";\n";
            }
        }
        return trim($sInfo);
    } // function getInfoString

    /**
     * Check: is there outer data
     * @param string $sOrder
     * @return boolean
     */
    public function checkIsData($sOrder = null, $bExtraAdd = true)
    {
        foreach ($this->_separateData($sOrder, $bExtraAdd) as $v) {
            if (!empty($v)) {
                return true;
            }
        }
        return false;
    } // function checkIsData

    /**
     * Get Delimiter for Add request
     * @return string
     */
    public function getAddDelimiter()
    {
        return $this->oConfig->get('ADD_REQUEST_DELIMITER', '-');
    } // function getAddDelimiter

    // ======== Private/Protected methods ======== \\

    /**
     * Separate Data into array By $sOrder
     * @param string $sOrder
     * @return array
     * @throws \fan\project\exception\service\fatal
     */
    protected function _separateData($sOrder, $bExtraAdd)
    {
        $aData    = array();
        $sOrder   = empty($sOrder) ? $this->sOrder : strtoupper($sOrder);
        $oMatcher = $this->_getMatcher();
        $nIndex   = empty($oMatcher) ? -1 : $oMatcher->getCurrentIndex();

        for ($i = 0; $i < strlen($sOrder); $i++) {
            $k0 = $k1 = $sOrder{$i};
            if ($k1 == 'A') {
                $k1 .= $bExtraAdd ? '0' : '1';
            }
            if (array_key_exists($k1, $this->aData)) {
                if (isset($this->aMaker[$k0]) && array_val($this->aMakerIndex, $k1) !== $nIndex) {
                    $this->aData[$k1] = call_user_func(array($this, $this->aMaker[$k0]), $bExtraAdd);
                    $this->aMakerIndex[$k1] = $nIndex;
                }
                $aData[$k0] = $this->aData[$k1];
            } else {
                throw new fatalException($this, 'Incorrect symbols in order "' . $sOrder . '". Possible symbols "' . implode('', array_keys($this->aData)) . '".');
            }
        }
        return $aData;
    } // function _separateData

    /**
     * Do stripslashes for each array elements
     * @param mixed $mValue Checked data
     * @return mixed Converted data
     */
    protected function _stripSlashesDeep($mValue)
    {
        if (is_array($mValue)) {
            return array_map(array($this, '_stripSlashesDeep'), $mValue);
        } else {
            return stripslashes($mValue);
        }
    } // function _stripSlashesDeep

    /**
     * Make Request Headers
     * @return array
     */
    protected function _makeHeaders()
    {
        if (function_exists('apache_request_headers')) {
            return apache_request_headers();
        }

        $aHeaders = array();
        foreach ($_SERVER as $k => $v) {
            if (substr($k, 0, 5) == 'HTTP_') {
                $k = substr($k, 5);
                $aKeys = explode('_', $k);
                if (true) { // ToDo: Disable for some $k
                    foreach ($aKeys as &$sKey) {
                        $sKey = ucfirst(strtolower($sKey));
                    }
                }
                $aHeaders[implode('-', $aKeys)] = $v;
            }
        }
        return $aHeaders;
    } // function _makeHeaders

    /**
     * Make Add Request
     * @return array
     */
    protected function _makeAddRequest($bExtraAdd)
    {
        $aAddRequest = $this->_getRequestData('add_request');
        if (empty($aAddRequest)) {
            return array();
        }

        $sDelimiter = $this->getAddDelimiter();
        if ($bExtraAdd && $sDelimiter != '') {
            foreach ($aAddRequest as $v) {
                $aTmp = explode($sDelimiter, $v, 2);
                if (count($aTmp) == 2 && !isset($aAddRequest[$aTmp[0]])) {
                    $aAddRequest[$aTmp[0]] = $aTmp[1];
                }
            }
        }
        return $aAddRequest;
    } // function _makeAddRequest

    /**
     * Make Main Request
     * @return array
     */
    protected function _makeMainRequest()
    {
        $aMainRequest = $this->_getRequestData('main_request');
        return empty($aMainRequest) ? array() : $aMainRequest;
    } // function _makeMainRequest

    /**
     * Make Both Request
     * @return array
     */
    protected function _makeBothRequest()
    {
        $aMain = $this->_makeMainRequest();
        $aAdd  = $this->_makeAddRequest(false);
        return array_merge($aMain, $aAdd);
    } // function _makeBothRequest

    /**
     * Make Get-data
     * @return array
     */
    protected function _makeGet()
    {
        $aData = array();
        $sQueryStr = $this->getQueryString(false);
        if (!empty($sQueryStr)) {
            parse_str($sQueryStr, $aData);
        }
        return $aData;
    } // function _makeGet

    /**
     * Make Cookies
     * @return array
     */
    protected function _makeCookies()
    {
        return \fan\project\service\cookie::instance()->getAll();
    } // function _makeCookies

    /**
     * Make makeOptions
     * @return array
     */
    protected function _makeOptions()
    {
        global $argv;
        $aOptions = $argv;
        array_shift($aOptions);
        return $aOptions;
    } // function _makeOptions

    /**
     * Get Parsed Request Data
     * @param string $sProp
     * @return array
     */
    protected function _getRequestData($sProp)
    {
        $oMatcher = $this->_getMatcher();
        return $oMatcher ? $oMatcher->getCurrentItem()->parsed->$sProp : array();
    } // function _getRequestData

    /**
     * Check - is allowed setting this Type
     * @param string $sType
     * @return boolean
     * @throws fatalException
     */
    protected function _isAllowToSet($sType)
    {
        if (in_array($sType, array('A', 'B', 'M'))) {
            return false;
        }
        if (strlen($sType) != 1 || !array_key_exists($sType, $this->aData)) {
            throw new fatalException($this, 'Incorrect type for set "' . $sType . '". Possible one of symbols "' . implode('', array_keys($this->aData)) . '".');
        }
        return !empty($this->oConfig['ALLOW_SET'][$sType]);
    } // function _isAllowToSet

    /**
     * Get object of Matcher
     * @return \fan\core\service\matcher
     */
    protected function _getMatcher()
    {
        if (empty($this->oMatcher) && class_exists('\fan\core\service\matcher', false)) {
            $this->oMatcher = \fan\project\service\matcher::instance();
        }
        return $this->oMatcher;
    } // function _getMatcher
} // class \fan\core\service\request
?>