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
 * @version of file: 05.02.005 (12.02.2015)
 */
class json extends \fan\core\base\service\single
{
    const DECODE_OPT_PHP_VERSION = '5.4.0';

    /**
     * Error Code
     * @var integer
     */
    protected $iErrorCode;

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
    } // function fromXml.

    /**
     * Encode YAML-file to JSON-string
     * @param type $sYaml
     */
    public function fromYaml($sYaml)
    {
        // ToDo: make procedures for encode YAML to JSON
    } // function fromXml.

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
    } // function fromXml.

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
    } // function fromXml.

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
} // class \fan\core\service\json
?>