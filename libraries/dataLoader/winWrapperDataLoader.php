<?php
/**
 * winWrapperDataLoader - Server side of JavaScript WinWrapperDataLoader
 * Copyright (C) 2005-2006 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *         http://www.opensource.org/licenses/lgpl-license.php
 *
 * This programm make Upload file for winWrapperDataLoader (JavaScript-object, html-file, plain text).
 * For further information visit:
 *     http://www.alex.4n.com.ua/win_wrapper/
 *
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version:  3.01.06
 * @modified: 2013-12-13 01:20:00
 */
class winWrapperDataLoader {
    /**
     * Keys of parameters
     * @var array
     */
    private $aParamKeys = array(
        "dataKey"    => "dl_data",
        "controlKey" => "dl_ctrl",
    );

    /**
     * Data file of JavaScript object
     * @var array
     */
    private $aJsonData = array();

    /**
     * XML-text of JavaScript object
     * @var string
     */
    private $sXmlData = "";

    /**
     * Text for JavaScript object
     * @var string
     */
    private $sTextData = "";

    /**
     * Type of transport for file transmit (xml, js, frm, img)
     * @var string
     */
    private $sTransport;

    /**
     * JavaScript file handler
     * @var string
     */
    private $sHandler;

    /**
     * Set error for img-transport
     * @var boolean
     */
    private $bImgError = false;

    /**
     * Scanned Value for replace before sending in JavaScript
     * @var array
     */
    private static $aScanVal = array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"');
    /**
     * Replaced Value after scan
     * @var array
     */
    private static $aReplVal = array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"');

    /**
     * Module constructor
     * @param array $aData - sent data array with keys ("json", "html", "text")
     * @param boolean $bAutoSend - send data automatically
     */
    public function __construct($aData = array(), $bAutoSend = false)
    {
        if($bAutoSend) {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Cache-Control: no-cache, must-revalidate");
            header("Cache-Control: post-check=0,pre-check=0");
            header("Cache-Control: max-age=0");
            header("Pragma: no-cache");
        }

        $sCtrl = @$_REQUEST[$this->aParamKeys["controlKey"]];
        if($sCtrl) {
            list($this->sTransport, $this->sHandler) = explode("-", $sCtrl, 3);
        }

        if (!in_array($this->sTransport, array("xml", "js", "frm", "img"))) {
            $this->sTransport =  "xml";
        }

        if($aData) {
            if(isset($aData["json"])) {
                $this->setJson($aData["json"]);
            }
            if(isset($aData["html"])) {
                $this->setHtml($aData["html"]);
            }
            if(isset($aData["text"])) {
                $this->setText($aData["text"]);
            }
        }
        if($bAutoSend) {
            $this->send();
        }
    } // function winWrapperLoader

    /**
     * Get data from JS
     * @return array Data as hash
     */
    public function getData()
    {
        if (isset($_REQUEST[$this->aParamKeys["dataKey"]])) {
            $aRet = $_REQUEST[$this->aParamKeys["dataKey"]];
            if (is_array($aRet) && get_magic_quotes_gpc()) {
                array_walk_recursive($aRet, array($this, 'stripslashes'));
            }
            return $aRet;
        }
        return null;
    }// function getData

    /**
     * Get Name of transport
     * @return string
     */
    public function getTransport()
    {
        return $this->sTransport;
    }// function getTransport

    /**
     * Get handler key
     * @return string
     */
    public function getHandler()
    {
        return $this->sHandler;
    }// function getHandler

    /**
     * Get JS-Object
     * @param array $mData Source Hache array
     * @return string
     */
    public function getJsObject($mData)
    {
        return $this->makeJsObject($mData);
    }// function getJsObject

    /**
     * Set object data
     * @param array $aJsonData Data as hash
     */
    public function setJson($aJsonData, $clearPrev = false)
    {
        if(!is_array($aJsonData)) {
            $this->errorLog("set_json", "Data isn't array.");
        }
        $this->aJsonData = $clearPrev ? $aJsonData : array_merge_recursive($this->aJsonData, $aJsonData);
    }// function setJson

    /**
     * Set text data
     * @param string $sText text to send
     */
    public function setText($sTextData, $clearPrev = false)
    {
        $this->sTextData = ($clearPrev ? "" : $this->sTextData) . $sTextData;
    }// function setText

    /**
     * Set xml-text data
     * @param mixed $sXmlData html-text to send
     */
    public function setHtml($sXmlData, $clearPrev = false)
    {
        $this->sXmlData = ($clearPrev ? "" : $this->sXmlData) . $sXmlData;
    }// function setHtml

    /**
     * Set html-text data from file
     * @param string $sFilePath patch to html-file
     */
    public function setHtmlFile($sFilePath)
    {
        if(!is_file($sFilePath)) {
            $this->errorLog("set_html_file", "File " . $sFilePath . " doesn't exist.");
        } else {
            $this->sXmlData = file_get_contents($sFilePath);
        }
    }// function setHtmlFile

    /**
     * Set error event (for img-transport)
     */
    public function setImgError()
    {
        $this->bImgError = thrue;
    }// function setImgError

    /**
     * Send preparing data
     */
    public function send($bIsEcho = true)
    {
        $sMethod = "send_" . $this->sTransport;
        $sRet = $this->$sMethod();
        if ($bIsEcho) {
            echo $sRet;
        }
        return $sRet;
    }// function send

    /**
     * Send preparing data by xml-transport
     */
    protected function send_xml()
    {
        header("Content-Type: application/json; charset=utf-8");
        return '[' . $this->getHandlerVal() . ',' . $this->makeJsObject($this->aJsonData) . ',' . $this->makeTextStr($this->sXmlData) . ',' . $this->makeTextStr($this->sTextData) . ']';
    }// function send_xml

    /**
     * Send preparing data by js-transport
     */
    protected function send_js()
    {
        header("Content-Type: text/javascript; charset=utf-8");
        return 'try {ldWrHandler(' . $this->getHandlerVal() . ',' . $this->makeJsObject($this->aJsonData) . ',' . $this->makeTextStr($this->sXmlData) . ',' . $this->makeTextStr($this->sTextData) . ');} catch(e) {if(window.loadWrapper) {loadWrapper.prototype.errMsg("Error!\n"+e.message);}}';
    }// function send_js

    /**
     * Send preparing data by frm-transport
     */
    protected function send_frm()
    {
        header("Content-Type: text/html; charset=utf-8");
        return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Data</title>
</head>
<body><div>' . $this->sXmlData . '</div>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
try {parent.ldWrHandler(' . $this->getHandlerVal() . ',' . $this->makeJsObject($this->aJsonData) . ',document,' . $this->makeTextStr($this->sTextData) . ');} catch(e) {if(window.parent && parent.loadWrapper) {parent.loadWrapper.prototype.errMsg("Error!\n"+e.message);}}' .
'//--><!]]>
</script>
</body></html>';
    }// function send_frm

    /**
     * Send preparing data by img-transport
     */
    protected function send_img()
    {
        if($this->bImgError) {
            header("Content-Type: text/plain; charset=utf-8");
            return "error";
        } else {
            header('Content-Type: image/gif');
            return file_get_contents(dirname(__FILE__) . '/1x1.gif');
        }
    }// function send_img

    /**
     * Make_JS-Object
     * @param array $mData Source Hache array
     */
    protected function makeTextStr($sStr)
    {
        return '"' . str_replace(self::$aScanVal, self::$aReplVal, $sStr) . '"';
    } // function makeTextStr
    /**
     * Make_JS-Object
     * @param array $mData Source Hache array
     */
    protected function makeJsObject($mData)
    {
        if (is_null($mData))  return 'null';
        if ($mData === false) return 'false';
        if ($mData === true)  return 'true';

        if (is_scalar($mData)) {
            if (is_float($mData)) {
                return str_replace(",", ".", strval($mData));
            }
            return is_integer($mData) ? $mData : $this->makeTextStr($mData);
        }

        $isHash = false;
        $i = 0;

        if (is_object($mData)) {
            $isHash = true;
        } else {
            foreach (array_keys($mData) as $v) {
                if ($v !== $i++) {
                    $isHash = true;
                    break;
                }
            }
        }

        $aRet = array();
        if ($isHash) {
            foreach ($mData as $k => $v) {
                $aRet[] = $this->makeTextStr($k) . ':' . $this->makeJsObject($v);
            }
            return '{' . implode(',', $aRet) . '}';
        }
        foreach ($mData as $v) {
            $aRet[] = $this->makeJsObject($v);
        }
        return '[' . implode(',', $aRet) . ']';
    } // function makeJsObject

    /**
     * Set error
     * @param string $sType Tipe of error
     * @param string $sMessage error message
     */
    protected function errorLog($sType, $sMessage)
    {
        if(version_compare(PHP_VERSION, "5.0", "ge")) {
            eval('throw new Exception($sType . ". " . $sMessage);');
        } else {
            error_log(date("d.m.y H:i:s ") . $sType . ":\n\t" . $sMessage, 0);
            exit();
        }
    } // function errorLog

    /**
     * Strip slashes
     * @param string $mElm element
     */
    protected function stripslashes(&$mElm)
    {
        $mElm = stripslashes($mElm);
    } // function stripslashes

    /**
     * Get Handler Value
     * @return string
     */
    protected function getHandlerVal()
    {
        return is_null($this->sHandler) ? 'null' : $this->sHandler;
    } // function getHandlerVal
} // class winWrapperLoader
?>