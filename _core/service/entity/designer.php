<?php namespace core\service\entity;
/**
 * Designer of SQL-request
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
abstract class designer
{
    /**
     * SQL-request parts
     * @var string
     */
    protected $aQueryParts = array();
    /**
     * Entity - table data
     * @var \core\base\model\entity
     */
    protected $oEntity = null;

    /**
     * Source Parameters
     * @var array
     */
    protected $aSrcParam = null;
    /**
     * Result Parameters
     * @var array
     */
    protected $aAdjustedParam = null;

    public function __construct(\core\base\model\entity $oEntity = null)
    {
        $this->oEntity = $oEntity;
    } // function __construct

    // ======== Static methods ======== \\

    // ======== The magic methods ======== \\
    /**
     * Magic set property
     * @param string $sPartName
     * @param mixed $mValue
     */
    public function __set($sPartName, $mValue)
    {
        $this->set($sPartName, $mValue);
    } // function __set

    /**
     * Magic get property
     * @param type $sPartName
     * @return type
     */
    public function __get($sPartName)
    {
        return $this->get($sPartName);
    } // function __get

    /**
     * Convert object to string
     * @return type
     */
    public function __toString()
    {
        return $this->assemble();
    } // function __toString

    // ======== Main Interface methods ======== \\
    /**
     * Set value of SQL-part
     * @param string $sPartName
     * @param mixed $mPartValue
     * @return \core\service\entity\designer
     */
    public function set($sPartName, $mPartValue, $bAllowException = true)
    {
        if ($mPartValue != '' && $this->_checkPartName($sPartName, $bAllowException)) {
            $this->aQueryParts[$sPartName] = $mPartValue;
        }
        return $this;
    } // function set

    /**
     * Get value of SQL-part
     * @param string $sPartName
     * @return mixed
     */
    public function get($sPartName, $bAllowException = false)
    {
        return $this->_checkPartName($sPartName, $bAllowException) ? $this->aQueryParts[$sPartName] : null;
    } // function get

    /**
     * Add new part of SQL-request to current
     * @param string $sPartName
     * @param mixed $mNewPart
     * @param boolean $bToEnd
     * @return \core\service\entity\designer
     */
    public function add($sPartName, $mNewPart, $bToEnd = true, $bAllowException = true)
    {
        if ($mNewPart != '') {
            $this->_checkPartName($sPartName, $bAllowException);
            $aCurParts = &$this->aQueryParts[$sPartName];
            if (!is_array($aCurParts)) {
                $aCurParts = empty($aCurParts) ? array() : array($aCurParts);
            }

            if (is_array($mNewPart)) {
                $aCurParts = $bToEnd ? array_merge($aCurParts, $mNewPart) : array_merge($mNewPart, $aCurParts);
            } elseif (is_string($mNewPart)) {
                if ($bToEnd) {
                    array_push($aCurParts, $mNewPart);
                } else {
                    array_unshift($aCurParts, $mNewPart);
                }
            }
        }
        return $this;
    } // function add

    /**
     * Make Where-condition by parameters
     * Important(!): Method modifies Parameters to plain-arrey for use them as plaseholders
     * @param mixed $mParam
     * @param boolean $bMerge
     * @return string|array
     */
    public function makeWhere($mParam, $bMerge = true)
    {
        $aResult = array();
        if(is_array($mParam)) {
            $aAdjustedParam = array();
            $i = 0;
            foreach($mParam as $k => $v) {
                $aResult[$i] = $i == 0 ? 'WHERE ' : 'AND ';
                if(is_numeric($k)) {
                    $aResult[$i] .= $v;
                } elseif(is_array($v)) {
                    if (empty($v)) {
                        trigger_error('Empty array for field "' . $k . '" doesn\'t allowed for make "Where".', E_USER_WARNING);
                        $aResult[$i] .= '';
                    } else {
                        $aResult[$i] .= strstr($k, '?') ? $k : '`' . $k . '` IN (' . implode(',', array_fill(0, count($v), ' ?')) . ')';
                        $aAdjustedParam = array_merge($aAdjustedParam, array_values($v));
                    }
                } elseif(is_null($v)) {
                    $aResult[$i] .= '`' . $k . '` IS null';
                } else {
                    $aResult[$i] .= strstr($k, '?') ? $k : '`' . $k . '` = ?';
                    $aAdjustedParam[] = $v;
                }
                $i++;
            }
            $this->aAdjustedParam = array_merge((array)$this->aAdjustedParam, $aAdjustedParam);
        } elseif (!is_null($mParam)) {
            $aResult[0] = preg_match('/\s*where\s/i', $mParam) ? $mParam : 'WHERE ' . $mParam;
            $mParam = null;
        }
        return $bMerge ? $this->_mergeParts($aResult) : $aResult;
    } // function makeWhere
    /**
     * Assemble SQL-request
     * @return string
     */
    public function assemble($mParam = null)
    {
        if (!is_null($mParam)) {
            $this->aSrcParam = $mParam;
        }

        $sQuery = $this->_mergeParts($this->aQueryParts);

        if (is_null($this->aAdjustedParam) && is_array($mParam)) {
            $this->aAdjustedParam = array_values($mParam);
        }

        return $sQuery;
    } // function assemble

    public function getAdjustedParam()
    {
        return $this->aAdjustedParam;
    } // function getAdjustedParam

    /**
     * Get parts of SQL-request as array
     * @return array
     */
    public function toArray()
    {
        return $this->aQueryParts;
    } // function toArray
    /**
     * Get link to Entity
     * @return \core\service\entity\entity
     */
    public function getEntity()
    {
        return $this->oEntity;
    } // function getEntity

    // ======== Private/Protected methods ======== \\
    /**
     * Check Name of Part
     * @param type $sPartName
     * @param type $bAllowException
     * @return boolean
     * @throws \project\exception\model\entity\fatal
     */
    protected function _checkPartName($sPartName, $bAllowException)
    {
        if (!array_key_exists($sPartName, $this->aQueryParts)) {
            if ($bAllowException) {
                throw new \project\exception\model\entity\fatal($this->getEntity(), 'Incorrect key of SQL-part: "' . $sPartName . '".');
            }
            return false;
        }
        return true;
    } // function _checkPartName

    /**
     * Merge Parts of array (recursively)
     * @param array $aSource
     * @return string
     */
    protected function _mergeParts(array $aSource)
    {
        $sResult        = '';
        $aAdjustedParam = array();
        $bIsSnippet     = false;
        foreach ($aSource as $v) {
            if (!is_null($v)) {
                if (is_array($v)) {
                    $sPart = $this->_mergeParts($v);
                } elseif (is_object($v)) {
                    if ($v instanceof \core\service\entity\snippet) {
                        list($sPart, $aTmpParam) = $v->getSnippetQuery($this->aSrcParam);
                        if (!empty($aTmpParam)) {
                            $aAdjustedParam = array_merge($aAdjustedParam, $aTmpParam);
                        }
                        $bIsSnippet = true;
                    } elseif (method_exists($v, '__toString')) {
                        $sPart = $v->__toString();
                    } else {
                        $sPart = '';
                        trigger_error('Unknown object of SQL-part. Instance of "' . get_class($v) . '".', E_USER_WARNING);
                    }
                } elseif (is_scalar($v)) {
                    $sPart = (string)$v;
                } else {
                    $sPart = '';
                    trigger_error('Incorrect type (' . gettype($v) . ') of SQL-part', E_USER_WARNING);
                }

                if ($sResult != '' && $sPart != '' && preg_match('/\S$/', $sResult) && preg_match('/^\S/', $sPart)) {
                    $sResult .= ' ';
                }
                $sResult .= $sPart;
            }
        }
        if ($bIsSnippet) {
            $this->aAdjustedParam = array_merge((array)$this->aAdjustedParam, $aAdjustedParam);
        }
        return $sResult;
    } // function _mergeParts

    /**
     * Make Setup Part (for UPDATE or INSERT)
     * @param array $aData
     * @return string
     */
    protected function _makeSetupPart(array $aData)
    {
        $sResult = '';
        $aAdjustedParam = array();
        foreach ($aData as $k => $v) {
            if (!empty($sResult)) {
                $sResult .= ', ';
            }
            if (is_null($v)) {
                $sResult  .= '`' . $k . '` = null';
            } else {
                $sResult  .= '`' . $k . '` = ?';
                $aAdjustedParam[] = $v;
            }
        }
        $this->aAdjustedParam = array_merge((array)$this->aAdjustedParam, $aAdjustedParam);
        return $sResult;
    } // function _makeSetupPart

} // class \core\service\entity\designer
?>