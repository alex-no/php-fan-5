<?php namespace core\service;
/**
 * Description of log
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
 */
class log extends \core\base\service\single
{
    /**
     * Array of log parcers
     * @var array
     */
    protected $aParcers;

    /**
     * File Directory of each type
     * @var string
     */
    protected $aDir;

    /**
     * File name of each type
     * @var string
     */
    protected $aFile;

    /**
     * Log file is new
     * @var bolean
     */
    protected $bIsNewFile = false;

    /**
     * Get Log Parser
     * @param string $sVariety - variety of log-file
     * @param string $sFile - log-file name
     * @return log_parser_base
     */
    public function getLogParser($sVariety, $sFile)
    {
        if (!isset($this->aParcers[$sVariety][$sFile])) {
            $oEngine = $this->_getEngine('parser_'  . $sVariety);
            $this->aParcers[$sVariety][$sFile] = $oEngine;
            if (empty($oEngine)) {
                throw new \project\exception\service\fatal($this, $sVariety ? 'Incorrect Variety of log-file: "' . $sVariety . '"' : 'Unset Variety of log-file.');
            }
            $oEngine->setFilePath($sVariety, $sFile);
        } else {
            $this->aParcers[$sVariety][$sFile]->checkIndex();
        }
        return $this->aParcers[$sVariety][$sFile];
    } // function getLogParser

    /**
     * Log data
     * @param string $sType
     * @param mixed $mData
     * @param string $sTitle
     * @param string $sNote
     * @param number $nDataDepth
     * @param boolean $bIsTrace
     * @param string $sFile
     */
    public function logData($sType, $mData, $sTitle, $sNote = '', $nDataDepth = null, $bIsTrace = true, $sFile = null)
    {
        if (is_null($nDataDepth) || $nDataDepth < 1) {
            $nDataDepth = $this->getConfig('DATA_DEPTH', 4);
        }
        $aRow = $this->_setAttribute($sTitle);
        $aRow['data'] = $this->_setNewData($mData, $nDataDepth);
        $this->_setNote($aRow, $sNote);
        if ($bIsTrace) {
            $aRow['trace'] = $this->_getTrace();
        }
        $this->_saveLog('data', $sType, $aRow, $sFile);
    } // function logData

    /**
     * Log error-message
     * @param string $sType
     * @param string $sMessage
     * @param string $sTitle
     * @param string $sNote
     * @param boolean $bIsTrace
     * @param string $sFile
     */
    public function logError($sType, $sMessage, $sTitle, $sNote = '', $bIsTrace = true, $sFile = null)
    {
        $aRow = $this->_setAttribute($sTitle);
        $aRow['main_msg'] = $sMessage;
        $this->_setNote($aRow, $sNote);
        if ($bIsTrace) {
            $aRow['trace'] = $this->_getTrace();
        }
        $this->_saveLog('error', $sType, $aRow, $sFile);
    } // function logError

    /**
     * Log error-message
     * @param string $sType
     * @param string $sMessage
     * @param string $sTitle
     * @param string $sNote
     * @param string $sFile
     */
    public function logMessage($sType, $sMessage, $sTitle, $sNote = '', $sFile = null)
    {
        $aRow = $this->_setAttribute($sTitle);
        $aRow['main_msg'] = $sMessage;
        $this->_setNote($aRow, $sNote);
        $this->_saveLog('message', $sType, $aRow, $sFile);
    } // function logMessage

    /**
     * Set new record tag
     * @param string $sType
     */
    protected function _setAttribute($sTitle)
    {
        $aRow = array();
        $aRow['method'] = !isset($_SERVER['REQUEST_METHOD']) ? 'CLI' : $_SERVER['REQUEST_METHOD'];
        if (@$this->oConfig['SET_PROTOCOL']) {
            $aRow['protocol'] = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
        }
        if ($aRow['method'] != 'CLI' && (@$this->oConfig['SET_DOMAIN'] || @$this->oConfig['SET_PROTOCOL'])) {
            $aRow['domain'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null;
        }
        $aRow['request'] = $aRow['method'] != 'CLI' ? @$_SERVER['REQUEST_URI'] : (isset($_SERVER['argv']) ? @implode(' ', $_SERVER['argv']) : 'No path');

        $aRow['header']  = $sTitle;
        return $aRow;
    } // function _setAttribute

    /**
     * Set Data Element
     * @param number $nDataDepth
     * @return DOMElement
     */
    protected function _setNewData($mData, $nDataDepth)
    {
        $bIsEntity = is_object($mData) && is_subclass_of($mData, 'entity_base');
        $aDtEl = array(
            'type'  => $bIsEntity ? get_class($mData) . ':' : (is_object($mData) ? 'object:' . get_class($mData) : gettype($mData)),
        );

        if (is_null($mData)) {
            $aDtEl['singular'] = 'NULL';
        } elseif (is_scalar($mData)) {
            $aDtEl['singular'] = is_bool($mData) ? ($mData ? 'true' : 'false') : $this->_checkIncorrectSymbol($mData, 'scalar_val', 2048);
        } elseif (is_array($mData) || is_object($mData)) {
            if ($nDataDepth < 1) {
                $aDtEl['singular'] = is_array($mData) ? 'array[' . count($mData) . ']' : 'object:' . get_class($mData);
            } else {
                $aDtEl['multiple'] = array();
                if ($bIsEntity) {
                    foreach ($mData->getDebugInfo() as $k => $v) {
                        $aDtEl['multiple'][$k] = $this->_setNewData($v, $nDataDepth);
                    }
                } else {
                    foreach ($mData as $k => $v) {
                        $aDtEl['multiple'][$this->_checkIncorrectSymbol($k, 'mp_key', 64)] = $this->_setNewData($v, $nDataDepth - 1);
                    }
                }
                if (empty($aDtEl['multiple'])) {
                    $aDtEl['singular'] = is_array($mData) ? 'Empty array' : 'Object:' . get_class($mData) . ' without public property.';
                    unset($aDtEl['multiple']);
                }
            }
        } else {
            $aDtEl['singular'] = $this->_checkIncorrectSymbol(print_r($mData, true), 'any_var', 4096);
        }

        return $aDtEl;
    } // function _setNewData

    /**
     * Set Note
     * @param array $aRow
     * @param string $sNote
     */
    protected function _setNote(&$aRow, $sNote)
    {
        if ($sNote) {
            $aRow['note'] = $this->_checkIncorrectSymbol($sNote, 'note', 4096);
        }
    } // function _setNote

    /**
     * Get Trace
     * @return array
     */
    protected function _getTrace()
    {
        $aRet = array();
        $aTmp = debug_backtrace();
        $aBackTrace = array();
        for ($i = count($aTmp) - 1; $i >= 0; $i--) {
            $aBackTrace[$i]['file'] = isset($aTmp[$i]['file']) ? $aTmp[$i]['file'] : '';
            $aBackTrace[$i]['line'] = isset($aTmp[$i]['line']) ? $aTmp[$i]['line'] : '';
            $aBackTrace[$i]['function'] = (isset($aTmp[$i]['class']) ? $aTmp[$i]['class'] . $aTmp[$i]['type'] : '') . $aTmp[$i]['function'];
            $aBackTrace[$i]['args'] = isset($aTmp[$i]['args']) ? $aTmp[$i]['args'] : array();
            if (isset($aTmp[$i]['class']) && preg_match('/^(?:core|project)\\\\service\\\\(?:log|error)$/', $aTmp[$i]['class'])) {
                break;
            }
        }
        ksort($aBackTrace);
        foreach ($aBackTrace as $v) {
            $aCall = array();
            if ($v['file']) {
                $aCall['file'] = $v['file'];
            }
            if ($v['file']) {
                $aCall['line'] = $v['line'];
            }
            $aCall['func'] = $v['function'];
            if ($v['args']) {
                $aCall['arg'] = array();
                foreach ($v['args'] as $arg) {
                    if (is_null($arg)) {
                        $s = 'NULL';
                    } elseif (is_scalar($arg)) {
                        $s = is_bool($arg) ? ($arg ? 'true' : 'false') : $this->_checkIncorrectSymbol($arg, 'argument', 128);
                    } elseif (is_array($arg)) {
                        $s = 'Array[' . count($arg) . ']';
                    } elseif (is_object($arg)) {
                        $s = 'Object:' . get_class($arg);
                    } else {
                        $s = $this->_checkIncorrectSymbol(print_r($arg, true), 'argument', 128);
                    }
                    $aCall['arg'][] = array(gettype($arg), str_replace ('&', '&amp;',$s));
                }
            }
            $aRet[] = $aCall;
        }
        return $aRet;
    } // function _getTrace

    /**
     * Set Xml data (from file or create)
     * @param string $sVariety
     * @param string $sFile
     * @return boolean
     */
    protected function _saveLog($sVariety, $sType, $aRow, $sFile)
    {
        $sRow  = date('H:i:s') . "\t" . $sType . "\t";
        if ($this->getConfig(array('USE_PID', $sVariety), false)) {
            $sRow .= \bootstrap::getPid() . "\t";
        }
        $sRow .= addcslashes(serialize($aRow), "\\\t\r\n\0") . "\n";
        error_log($sRow, 3, $this->_getFullPath($sVariety, $sFile));
    } // function _saveLog

    /**
     * Eeset Xml-file
     * @param string $sVariety
     * @return sring
     */
    protected function _getFullPath($sVariety, $sFile)
    {
        if (!isset($this->aDir[$sVariety])) {
            $this->aDir[$sVariety] = \bootstrap::parsePath($this->oConfig['LOG_DIR'][$sVariety]);
            if (!@is_writable($this->aDir[$sVariety])) {
                throw new \project\exception\fatal('Directory "' . $sFile . '" isn\'t writable.');
            }
        }

        if ($sFile) {
            $sDir = dirname($sFile);
            if ($sDir) {
                if (!@is_dir($sDir)) {
                    $sDir = $this->aDir[$sVariety] . '/' . $sDir;
                    if (!@is_dir($sDir)) {
                        throw new \project\exception\fatal('Incorrect log-file path "' . $sFile . '".');
                    }
                }
                if (!@is_writable($sDir)) {
                    throw new \project\exception\fatal('Directory "' . $sFile . '" isn\'t writable.');
                }
                return $sDir . '/' . $sFile;
            }
            return $this->aDir[$sVariety] . '/' . $sFile;
        }

        for ($i = 0; $i < 1000; $i++) {
            $sFile = date('Y-m-d') . '_' . str_pad($i, 3, '0', STR_PAD_LEFT) . '.log';
            $sFullPath = $this->aDir[$sVariety] . '/' . $sFile;
            if (!file_exists($sFullPath) || is_writable($sFullPath) && filesize($sFullPath) < $this->getConfig('MAX_FILE_SIZE', 1000000)) {
                break;
            }
        }
        return $sFullPath;
    } // function _getFullPath

    /**
     * Check incorrect symbol
     * @param string $sStr
     * @return string
     */
    protected function _checkIncorrectSymbol($sStr, $sLimitKey = null, $nLimitDefault = null)
    {
        $nLimit = $this->getConfig(array('LEN_LIMIT', $sLimitKey), is_null($nLimitDefault) ? 16384 : $nLimitDefault);
        $sStr = (string)$sStr;
        $k = min(strlen($sStr), $nLimit);
        $sRet = $k < strlen($sStr) ? '[REDUCED]' : '';
        for ($i=0; $i < $k; $i++) {
            $c = substr($sStr, $i, 1);
            $n = ord($c);
            if($n == 0) {
                return '[BINARY CODE]';
            } elseif ($n & 0x80) {
                if ($n & 0x40) {
                    $n1 = $n & 0x7F;
                    $c1 = $c;
                    $b  = true;
                    for ($j = 1; ($j <= 5) && ($n1 & 0x40); $j++, $n1 = $n1 << 1) {
                        $c = substr($sStr, $i + $j, 1);
                        if ((ord($c) & 0xC0) == 0x80) {
                            $c1 .= $c;
                        } else {
                            $b = false;
                            break;
                        }
                    }
                    if ($b) {
                        $sRet .= $c1;
                        $i += ($j - 1);
                    } else {
                        $sRet .= '♣';
                    }
                } else {
                    $sRet .= '♠';
                }
            } elseif($n < 0x20 && $n != 0x0D && $n != 0x0A && $n != 0x09) {
                $sRet .= '♥';
            } elseif($n == 127) {
                $sRet .= '♦';
            } else {
                $sRet .= $c;
            }
        }

        return $sRet;
    } // function _checkIncorrectSymbol

} // class \core\service\log
?>