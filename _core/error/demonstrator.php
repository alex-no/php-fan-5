<?php namespace fan\core\error;
/**
 * Description of demonstrator
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
 */
class demonstrator
{

    const CORE_TEMPLATE    = '{CORE_DIR}/error/template/{TPL_NAME}.html';
    const PROJECT_TEMPLATE = '{PROJECT_DIR}/error/template/{TPL_NAME}.html';
    const DEFAULT_NAME     = 'default';

    /**
     * @var array Used Response Codes
     */
    protected $aResponseCode = array(
        200 => 'HTTP/1.1 200 OK',
        400 => 'HTTP/1.1 400 Bad Request',
        403 => 'HTTP/1.1 403 Forbidden',
        404 => 'HTTP/1.1 404 Not Found',
        500 => 'HTTP/1.1 500 Internal Server Error',
    );

    /**
     * @var array Used Content Types
     */
    protected $aContentType = array(
        'text'  => 'text/plain',
        'html'  => 'text/html',
        'xhtml' => 'application/xhtml+xml',
        'xml'   => 'text/html',
    );

    /**
     * @var string Content Type
     */
    protected $sContentType = null;

    /**
     * @var string Template charset
     */
    protected $sCharset = 'utf-8';

    /**
     * @var string Data file name
     */
    protected $sDataFile = null;
    /**
     * @var string Template file name
     */
    protected $sTplFile = null;

    /**
     * @var array Template Variables
     */
    protected $aTplVars = array();

    /**
     * @var array Headers
     */
    protected $aHeaders = array(
        'Response'      => null,
        'ContentType'   => null,
        'AcceptRanges'  => 'Accept-Ranges: bytes',
        'ContentLength' => null,
    );

    /**
     * Constructor of error demonstrator
     * @param array $aTplVars Template variables
     * @param error $sTplName Template file name
     */
    public function __construct($aTplVars = array(), $sTplName = 'error_500')
    {
        $this->setTplVars($aTplVars)
             ->setTplName($sTplName);
    } // function __construct


    /**
     * Set Template Variables
     * @param mixed $mTplVars
     * @return \fan\core\error\demonstrator
     */
    public function setTplVars($mTplVars)
    {
        if (!is_array($mTplVars)) {
            $mTplVars = is_scalar($mTplVars) ? array('var' => $mTplVars) : (array)$mTplVars;
        }
        $this->aTplVars = array_merge($this->aTplVars, $mTplVars);
        return $this;
    } // function setTplVars

    /**
     * Get Template Variables
     * @param string $sKey
     * @return mixed
     */
    public function getTplVar($sKey = null)
    {
        return empty($sKey) ? $this->aTplVars : (isset($this->aTplVars[$sKey]) ? $this->aTplVars[$sKey] : '');
    } // function getTplVars

    /**
     * Set Template Name
     * @param mixed $sTplFile
     * @return \fan\core\error\demonstrator
     */
    public function setTplName($sTplFile = null)
    {
        if (empty($sTplFile)) {
            $aMath = array(
                str_replace(array('{CORE_DIR}', '{TPL_NAME}'), array(CORE_DIR, self::DEFAULT_NAME), self::CORE_TEMPLATE),
            );
        } else {
            $aMath = array(
                str_replace(array('{PROJECT_DIR}', '{TPL_NAME}'), array(PROJECT_DIR, $sTplFile), self::PROJECT_TEMPLATE),
                str_replace(array('{CORE_DIR}',    '{TPL_NAME}'), array(CORE_DIR,    $sTplFile), self::CORE_TEMPLATE),
                $sTplFile,
                str_replace(array('{CORE_DIR}',    '{TPL_NAME}'), array(CORE_DIR,    self::DEFAULT_NAME), self::CORE_TEMPLATE),
            );
        }
        foreach ($aMath as $v) {
            if (file_exists($v)) {
                $this->sDataFile = substr($v, 0, -4) . 'php';
                $this->sTplFile  = $v;
                break;
            }
        }
        return $this;
    } // function setTplName

    public function setResponseHeader($nCode)
    {
        if (!isset($this->aResponseCode[$nCode])) {
            $nCode = 500;
        }
        $this->aHeaders['Response'] = $this->aResponseCode[$nCode];
        return '';
    } // function setResponseHeader

    public function setContentType($sType)
    {
        if (!isset($this->aContentType[$sType])) {
            $sType = 'text';
        } elseif($sType == 'xhtml') {
            if (strstr(@$_SERVER['HTTP_USER_AGENT'], 'Opera') || isset($_REQUEST['notX'])) {
                $sType = 'html';
            } elseif(preg_match('/application\/xhtml\+xml(?:\s*\;\s*q=(1|0\.[0-9]+))?/i', @$_SERVER['HTTP_ACCEPT'], $aMatches1)) {
                $aMatches1[1] = isset($aMatches1[1]) ? floatval($aMatches1[1]) : 1;

                if(preg_match('/text\/html(?:\s*\;\s*q=(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT'], $aMatches2)) {
                    $aMatches2[1] = isset($aMatches2[1]) ? floatval($aMatches2[1]) : 1;
                } elseif(preg_match('/\*\/\*(?:\s*\;\s*q=(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT'], $aMatches2)) {
                    $aMatches2[1] = isset($aMatches2[1]) ? floatval($aMatches2[1]) : 1;
                } else {
                    $aMatches2[1] = 0;
                }

                if ($aMatches1[1] < $aMatches2[1]) {
                    $sType = 'html';
                }
            } else {
                $sType = 'html';
            }
        }
        $this->sContentType = $sType;
        $this->aHeaders['ContentType'] = 'Content-Type: ' . $this->aContentType[$sType] . '; charset=' . $this->sCharset;
        return '';
    } // function setContentType

    public function setOptionalHeader($sHeader)
    {
        if(!empty($sHeader)) {
            $this->aHeaders[] = $sHeader;
        }
        return $this;
    } // function setOptionalHeader

    public function outputHeaders()
    {
        if (!headers_sent()) {
            foreach ($this->aHeaders as $v) {
                if (!empty($v)) {
                    @header($v);
                }
            }

        }
    } // function outputHeaders

    public function getTplContent()
    {
        if (empty($this->sTplFile)) {
            return null;
        }
        $aData   = empty($this->sDataFile) || !file_exists($this->sDataFile) ? array() : include $this->sDataFile;
        $sResult = file_get_contents($this->sTplFile);
        foreach ($aData as $k => $v) {
            $sResult = str_replace('{{' . strtoupper($k) . '}}', $v, $sResult);
        }
        $this->aHeaders['ContentLength'] = 'Content-Length: ' . strlen($sResult);
        return $sResult;
    } // function getTplContent

    public function showTplContent()
    {
        $sContent = $this->getTplContent();
        $this->outputHeaders();
        echo $sContent;
        return $sContent;
    } // function showTplContent

    /**
     * Set Doctype
     * @return string
     */
    public function setDoctype()
    {
        if($this->sContentType == 'xhtml') {
            if(!strstr(@$_SERVER['HTTP_USER_AGENT'], 'MSIE 6')) {
                return '<?xml version="1.0" encoding="' . $this->sCharset . '"?>';
            }
            return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
        } elseif($this->sContentType == 'html') {
            return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
        }
        return '';
    } // function setDoctype

    /**
     * Convert Array (recurceive) To Sting
     * @param mixed $mSrc
     * @param string $sGlue
     * @return string
     */
    public function convArrayToSting($mSrc, $sGlue = "\n")
    {
        $sRet = '';
        if (is_array($mSrc)) {
            foreach ($mSrc as $v) {
                if (!empty($sRet)) {
                    $sRet .= $sGlue;
                }
                if (is_scalar($v)) {
                    $sRet .= $v;
                } elseif (is_array($v)) {
                    $sRet .= $this->convArrayToSting($v, $sGlue);
                } else {
                    $sRet .= strval($v);
                }
            }
        } elseif (is_scalar($mSrc)) {
            $sRet = (string)$mSrc;
        }
        return $sRet;
    } // function setDoctype
} // class \fan\core\error\demonstrator
?>