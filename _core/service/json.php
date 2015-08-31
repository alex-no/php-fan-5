<?php namespace fan\core\service;
/**
 * Description of JSON
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
class json extends \fan\core\base\service\multi
{
    const DECODE_OPT_PHP_VERSION = '5.4.0';
    /**
     * Service's Instances
     * @var \fan\core\service\json[]
     */
    private static $aInstances = array();

    /**
     * Error Code
     * @var integer
     */
    protected $iErrorCode;

    /**
     * Use Base64
     * @var boolean
     */
    protected $bUseBase64 = false;

    /**
     * Service's constructor
     * @param boolean $bUseBase64
     */
    protected function __construct($bUseBase64)
    {
        parent::__construct(true);
        $this->bUseBase64 = $bUseBase64;
    } // function __construct

    // ======== Static methods ======== \\
    /**
     * Get instance of JSON
     * @param boolean $bUseBase64
     * @return \fan\core\service\json
     */
    public static function instance($bUseBase64 = false)
    {
        $nKey = empty($bUseBase64) ? 0 : 1;
        if (!isset(self::$aInstances[$nKey])) {
            self::$aInstances[$nKey] = new self((bool)$nKey);
        }
        return self::$aInstances[$nKey];
    } // function instance
    // ======== Main Interface methods ======== \\

    /**
     * Decode JSON-string to object/array
     * @param string $sJson
     * @param boolean $bArray
     * @param integer $iDepth
     * @param integer $iOptions
     * @return array|stdClass
     */
    public function decode($sJson, $bArray = true, $iDepth = null, $iOptions = null)
    {
        $this->iErrorCode = null;
        if (is_null($iDepth)) {
            $iDepth = $this->getConfig('DEPTH', 25);
        }
        if (is_null($iOptions)) {
            $iOptions = $this->getConfig('DECODE_OPTIONS', 0);
        }
        if ($this->getConfig('ALLOW_INTERNAL', true)) {
            $mResult = version_compare(PHP_VERSION, self::DECODE_OPT_PHP_VERSION) < 0 ?
                    json_decode($sJson, $bArray, $iDepth) :
                    json_decode($sJson, $bArray, $iDepth, $iOptions);
            $this->iErrorCode = json_last_error();
            if ($this->bUseBase64 && is_array($mResult)) {
                array_walk_recursive($mResult, array($this, '_code64'), 'decode');
            }
            return $mResult;
        }
        // ToDo: make special procedures for JSON-decode
        trigger_error('JSON-decode is supported by internal functions yet.', E_USER_WARNING);
        return $bArray ? array() : new \stdClass();
    } // function decode

    /**
     * Encode object/array to JSON-string
     * @param mixed $mSourse
     * @param integer $iOptions
     * @return string
     */
    public function encode($mSourse, $iOptions = null, $bLogError = true)
    {
        $this->iErrorCode = JSON_ERROR_NONE;
        if (is_null($iOptions)) {
            $iOptions = $this->getConfig('ENCODE_OPTIONS', 0);
        }
        if ($this->getConfig('ALLOW_INTERNAL', true)) {
            if ($this->bUseBase64 && is_array($mSourse)) {
                array_walk_recursive($mSourse, array($this, '_code64'), 'encode');
                // ToDo: This method doesn't work with object
            }
            $sResult = @json_encode($mSourse, $iOptions);
            $this->iErrorCode = json_last_error();
            if ($this->iErrorCode != JSON_ERROR_NONE && $bLogError) {
                service('error')->logErrorMessage(
                        $this->getErrorText(),
                        'JSON error',
                        '',
                        true,
                        false
                );
            }
            return $sResult;
        }
        // ToDo: make special procedures for JSON-encode
        trigger_error('JSON-encode is supported by internal functions yet.', E_USER_WARNING);
        return '';
    } // function encode

    /**
     * Encode XML-file to JSON-string
     * @param type $sXml
     * @param type $bIgnoreXmlAttributes
     */
    public function fromXml($sXml, $bIgnoreXmlAttributes = true)
    {
        // ToDo: make procedures for encode XML to JSON
    } // function fromXml

    /**
     * Encode YAML-file to JSON-string
     * @param type $sYaml
     */
    public function fromYaml($sYaml)
    {
        // ToDo: make procedures for encode YAML to JSON
    } // function fromYaml

    /**
     * Is Error
     * @return boolean
     */
    public function isError()
    {
        return $this->iErrorCode != JSON_ERROR_NONE;
    } // function isError.

    /**
     * Get Error-code
     * @return integer
     */
    public function getError()
    {
        return $this->iErrorCode;
    } // function getError

    /**
     * Get Error-text
     * @return integer
     */
    public function getErrorText()
    {
        switch ($this->getError()) {
        case JSON_ERROR_DEPTH:
            return 'The maximum stack depth has been exceeded';
        case JSON_ERROR_STATE_MISMATCH:
            return 'Invalid or malformed JSON';
        case JSON_ERROR_CTRL_CHAR:
            return 'Control character error, possibly incorrectly encoded';
        case JSON_ERROR_SYNTAX:
            return 'Syntax error';
        case JSON_ERROR_UTF8:
            return 'Malformed UTF-8 characters, possibly incorrectly encoded';
        }
        return 'No error has occurred';
    } // function getErrorText

    /**
     * Make string for Pretty Print JSON
     * @param type $sJson
     * @param type $sIndent
     * @return string
     */
    public function prettyPrint($sJson, $sIndent = "\t")
    {
        $aTokens  = preg_split('/([\{\}\]\[,])/', $sJson, -1, PREG_SPLIT_DELIM_CAPTURE);
        $sResult  = '';
        $nIndSize = 0;

        foreach($aTokens as $v) {
            if ($v == '') {
                continue;
            }
            $sPrefix = str_repeat($sIndent, $nIndSize);
            if ($v == '{' || $v == '[') {
                $nIndSize++;
                if($sResult != '' && $sResult[strlen($sResult) - 1] == "\n") {
                    $sResult .= $sPrefix;
                }
                $sResult .= $v . "\n";
            } else if($v == '}' || $v == ']') {
                $nIndSize--;
                $sPrefix = str_repeat($sIndent, $nIndSize);
                $sResult .= "\n" . $sPrefix . $v;
            } else if($v == ',') {
                $sResult .= $v . "\n";
            } else {
                $sResult .= $sPrefix . $v;
            }
        }
        return $sResult;
   } // function prettyPrint

    // ======== Private/Protected methods ======== \\
   /**
    * Code/decode by "base64" elements of array
    * @param mixed $v
    * @param mixed $k
    * @param string $sOp
    */
    protected function _code64(&$v, $k, $sOp)
    {
        if (is_scalar($v)) {
            $v = $sOp == 'encode' ? base64_encode($v) : base64_decode($v);
        }
    } // function _code64
} // class \fan\core\service\json
?>