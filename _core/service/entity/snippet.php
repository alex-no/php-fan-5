<?php namespace fan\core\service\entity;
use fan\project\exception\model\entity\fatal as fatalException;
/**
 * Description of SQL-snippet
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
class snippet
{
    /**
     * Condition Regular Expressions
     * @var string
     */
    protected $sConditionRe = '/([\+\-\*\/%\(\)=.!&|\^~<>\s]*)(?:(exist|filled|val)\{\$(\w+)\}|const\[\s*([\-\.\d]+|\'.*?(?<!\\\\)\')\s*\])([\+\-\*\/%\(\)=.!&|\^~<>\s]*)/';
    /**
     * Place-Holders Regular Expressions
     * @var string
     */
    protected $sPlaceHoldersRe = '/\{\$(\w+)\}/';
    /**
     * Instance of Snippety-designer
     * @var \fan\core\service\entity\designer\snippety
     */
    protected $oSnippety = null;
    /**
     * Entity - table data
     * @var \fan\core\base\model\entity
     */
    protected $oEntity = null;

    /**
     * Snippet of SQL-request (sourse SQL-snippet)
     * @var string
     */
    protected $sQuery = null;

    /**
     * Source Condition-string
     * @var string
     */
    protected $sSrcCondition = null;

    /**
     * Parsed Condition-string (for eval)
     * @var string
     */
    protected $sCondition = null;

    /**
     * List of Data-Keys used for this SQL-snippet
     * @var array
     */
    protected $aUsedKeys = array();

    /**
     * Callback function/method
     * @var string|array
     */
    protected $mCallback = null;


    public function __construct(\fan\core\service\entity\designer\snippety $oSnippety, $sQuery, $sSrcCondition, $sCallback)
    {
        $this->oSnippety     = $oSnippety;
        $this->oEntity       = $oSnippety->getEntity();
        $this->sSrcCondition = $sSrcCondition;

        $this->sCondition = $this->_parseCondition($sSrcCondition);
        $this->aUsedKeys  = $this->_parsePlaceHolders($sQuery);
        $this->mCallback  = $this->_parseCallback($sCallback);
    } // function __construct

    // ======== Static methods ======== \\

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    /**
     * Main for parse SQL-snippet and prepare Data
     * @param array $aData
     * @return array
     */
    public function getSnippetQuery($aData)
    {
        $bIsValid  = $this->_checkCondition($aData);
        if ($this->mCallback) {
            list($sQuery, $aUsedData) = call_user_func($this->mCallback, $this, $aData, $bIsValid);
        } else {
            $sQuery = $bIsValid ? $this->sQuery : '';
            $aUsedData = $bIsValid ? $this->prepareData($sQuery, $aData) : array();
        }
        return array($sQuery, empty($aUsedData) ? array() : $aUsedData);
    } // function getSnippetQuery

    /**
     * Prepare Data
     * @param array $aData
     * @param array $aUsedKeys
     * @return array
     * @throws fatalException
     */
    public function prepareData(&$sQuery, $aData, $aUsedKeys = null)
    {
        if (is_null($aUsedKeys)) {
            $aUsedKeys = $this->aUsedKeys;
        }

        $nQttQuery = substr_count($sQuery, '?');
        if ($nQttQuery > count($aUsedKeys)) {
            throw new fatalException($this->getEntity(), 'Quantity of "question mark" more than UsedKeys.');
        }

        $aAdjustedParam = array();
        for ($i = 0; $i < $nQttQuery; $i++) {
            $key = $aUsedKeys[$i];
            if (!isset($aData[$key])) {
                array_push($aAdjustedParam, null);
            } elseif (!is_array($aData[$key])) {
                array_push($aAdjustedParam, $aData[$key]);
            } elseif (preg_match('/^((?:[^?]*\?){' . ($i + 1) . '})(.*)$/', $sQuery, $aMatches)) {
                $sQuery = substr($aMatches[1], 0, -1) . implode(',', array_fill(0, count($aData[$key]), ' ?')) . $aMatches[2];
                $aAdjustedParam = array_merge($aAdjustedParam, array_values($aData[$key]));
            } else {
                trigger_error('Can\'t parse SQL-snippet', E_USER_ERROR);
            }
        }
        return $aAdjustedParam;
    } // function prepareData
    /**
     * Get Soure snippet of Sql-request
     * @return string
     */
    public function getSql()
    {
        return $this->sQuery;
    } // function getSql
    /**
     * Get Soure string of Condition
     * @return string
     */
    public function getSrcCondition()
    {
        return $this->sSrcCondition;
    } // function getSrcCondition
    /**
     * Get Final string (for eval) of Condition
     * @return string
     */
    public function getCondition()
    {
        return $this->sCondition;
    } // function getCondition
    /**
     * Get lest of Used Key
     * @return array
     */
    public function getUsedKeys()
    {
        return $this->aUsedKeys;
    } // function getUsedKeys
    /**
     * Get Callback function/method
     * @return string|array
     */
    public function getCallback()
    {
        return $this->mCallback;
    } // function getCallback

    /**
     * Get Instance of Snippety-designer
     * @return \fan\core\service\entity\designer\snippety
     */
    public function getSnippety()
    {
        return $this->oSnippety;
    } // function getSnippety
    /**
     * Get link to Entity
     * @return \fan\core\base\model\entity
     */
    public function getEntity()
    {
        return $this->oEntity;
    } // function getEntity

    // ======== Private/Protected methods ======== \\
    /**
     * Parse Condition
     * @param string $sCondition
     * @return array
     * @throws fatalException
     */
    protected function _parseCondition($sCondition)
    {
        $sResult  = '';
        $aMatches = array();
        if (preg_match_all($this->sConditionRe, $sCondition, $aMatches)) {
            foreach ($aMatches[0] as $k => $v) {
                $sResult .= $aMatches[1][$k];
                if (empty($aMatches[2][$k])) {
                    $sResult .= $aMatches[4][$k];
                } else {
                    if ($aMatches[2][$k] == 'filled') {
                        $sResult .= '!empty($aData[\'' . $aMatches[3][$k] . '\'])';
                    } elseif ($aMatches[2][$k] == 'exist') {
                        $sResult .= 'array_key_exists(\'' . $aMatches[3][$k] . '\', $aData)';
                    } else {
                        $sResult .= 'array_val($aData, \'' . $aMatches[3][$k] . '\')';
                    }
                }
                $sResult .= $aMatches[5][$k];
            }
            $sResult = '$bResult=' . $sResult . '; return true;';

            $aData = array();
            if (!@eval($sResult)) {// Just check syntax of $sResult with empty data
                throw new fatalException($this->getEntity(), 'Incorrect SQL-Condition: "' . $sCondition . '".');
            }
        }
        return $sResult;
    } // function _parseCondition

    /**
     * Parse Place-Holders
     * @param string $sQuery
     * @return string|array
     */
    protected function _parsePlaceHolders($sQuery)
    {
        $aResult  = array();
        $aMatches = array();
        if (preg_match_all($this->sPlaceHoldersRe, $sQuery, $aMatches)) {
            foreach ($aMatches[0] as $k => $v) {
                $aResult[] = $aMatches[1][$k];
                $sQuery = str_replace($v, '?', $sQuery);
            }
        }

        $this->sQuery = $sQuery;
        return $aResult;
    } // function _parsePlaceHolders

    /**
     * Parse Callback
     * @param string $sCallback
     * @return string|array
     * @throws fatalException
     */
    protected function _parseCallback($sCallback)
    {
        if (empty($sCallback)) {
            return null;
        }

        $oEntity = $this->getEntity();
        if (strpos($sCallback, ':')) {
            $mCallback = explode(':', $sCallback);
        } else {
            $mCallback = array($oEntity->getRequestLoader(), $sCallback);
            if (is_callable($mCallback)) {
                return $mCallback;
            }
            $mCallback = array($oEntity, $sCallback);
            if (is_callable($mCallback)) {
                return $mCallback;
            }
            $mCallback = $sCallback;
        }

        if (!is_callable($mCallback)) {
            throw new fatalException($oEntity, 'Incorrect Callback function: "' . $sCallback . '".');
        }
        return $mCallback;
    } // function _parseCallback

    /**
     * Check is data correspond to condition
     * @param array $aData
     * @return boolean
     */
    protected function _checkCondition($aData)
    {
        $bResult = false;
        eval($this->sCondition);
        return $bResult;
    } // function _checkCondition
} // class \fan\core\service\entity\snippet
?>