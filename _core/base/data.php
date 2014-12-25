<?php namespace fan\core\base;
/**
 * Any types of Data (config, meta, entity, etc)
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
abstract class data implements \ArrayAccess, \Iterator, \Countable, \Serializable
{
    /**
     * Error messages
     * @var array
     */
    protected $aErrMsg = array(
        0  => 'Reserved for fatal error.',
        3  => 'Incorrect type of key "{KEY_TYPE}". Call class "{CLASS}".',
        5  => 'Unknown type ({TYPE}) of setter.',
        10 => 'Unrecognised setter tryed to change data in "{CLASS}".',
        11 => 'Unrecognised setter tryed to reset data in "{CLASS}".',
        12 => 'Unrecognised setter tryed to unset data for key "{KEY}" in "{CLASS}".',
        14 => 'Set new data data inpossible for key "{KEY}" in the "{CLASS}".',
    );

    /**
     * Saved data
     * @var array
     */
    protected $aData = array();

    /**
     * List of classes who can set/change data
     * If array is empty - any caller can set/change data
     * @var array
     */
    protected $aSetter = array();

    /**
     * Flag shows there are subelements - instances of this class
     * @var boolean
     */
    protected $bMultiLevel = true;

    /**
     * Key of superior in multilevel systems (if null - this is root element)
     * @var string
     */
    protected $sKey = null;

    /**
     * Superior in multilevel systems (if null - this is root element)
     * @var \fan\core\base\data
     */
    protected $oSuperior = null;

    /**
     * Flag allows Full data Rewrite
     * @var boolean
     */
    protected $bFullRewrite = false;

    /**
     * Constructor of Config-data
     * @param array $aData
     * @param string $sKey
     * @param fan\core\service\config\row $oSuperior
     */
    public function __construct($aData = null, $sKey = null, $oSuperior = null)
    {
        $this->sKey      = $sKey;
        $this->oSuperior = $oSuperior;
        if (is_array($aData)) {
            foreach ($aData as $k => $v) {
                $this->set($k, $v, true);
            }
        }
    } // function __construct

    // ======== Main Interface methods ======== \\
    /**
     * Get value of data
     * @param string|array $mKey
     * @param mixed $mDefault
     * @param boolean $bLogError
     * @return mixed
     */
    public function get($mKey = null, $mDefault = null, $bLogError = false)
    {
        if (is_scalar($mKey)) {
            return isset($this->aData[$mKey]) ? $this->aData[$mKey] : $mDefault;
        } elseif (is_null($mKey)) {
            return $this;
        } elseif (!is_array($mKey)) {
            if ($bLogError) {
                $this->_logError(3, array('key_type' => gettype($mKey)));
            }
            return $mDefault;
        }
        return $this->bMultiLevel ? $this->_getMultilevelData($mKey, $mDefault, $bLogError) : array_get_element($this->aData, $mKey, false);
    } // function get

    /**
     * Set value of data
     * @param string|number $mKey
     * @param mixed $mValue
     * @param boolean $bRewriteExisting - rewrite exists value
     * @param boolean $bConvArray - convert array to object of this class (null is true for Multi-Level data)
     * @return \fan\core\base\data
     */
    public function set($mKey, $mValue, $bRewriteExisting = true, $bConvArray = null)
    {
        if ($this->_checkSetter()) {
            $bConvArray = is_null($bConvArray) ? $this->bMultiLevel : !empty($bConvArray);

            if (is_scalar($mKey)) {
                if ($this->bMultiLevel && is_array($mValue) && isset($this->aData[$mKey]) && $this->_isThisClass($this->aData[$mKey])) {
                    foreach ($mValue as $k => $v) {
                        $this->aData[$mKey]->set($k, $v, $bRewriteExisting, $bConvArray);
                    }
                } elseif ($bRewriteExisting || !isset($this->aData[$mKey])) {
                    $this->aData[$mKey] = is_array($mValue) && $bConvArray ? $this->_makeSubData($mKey, $mValue) : $mValue;
                } else {
                    $this->_logError(14, array('key' => $mKey));
                }
            } elseif ($this->bMultiLevel && is_array($mKey)) {
                $sKey = array_shift($mKey);
                if (empty($mKey)) {
                    $this->set($sKey, $mValue, $bRewriteExisting, $bConvArray);
                } else {
                    if (!isset($this->aData[$sKey]) || !$this->_isThisClass($this->aData[$sKey])) {
                        $this->aData[$sKey] = $this->_makeSubData($sKey, array());
                    }

                    if (!is_object($this->aData[$sKey])) {
                        trigger_error('Element of data with key "' . $sKey . '" has incorrect type "' . gettype($this->aData[$sKey]) . '" in class "' . get_class() . '".', E_USER_WARNING);
                    } elseif (!method_exists($this->aData[$sKey], 'set')) {
                        trigger_error('Element of data with key "' . $sKey . '" is instance of class "' . get_class($this->aData[$sKey]) . '" without method "set" in container class "' . get_class() . '".', E_USER_WARNING);
                    } else {
                        $this->aData[$sKey]->set($mKey, $mValue, $bRewriteExisting, $bConvArray);
                    }
                }
            } elseif (is_array($mKey)) {
                $mData =& array_get_element($this->aData, $mKey, true);
                $mData = $mValue;
            } elseif (is_null($mKey) && 0) {
                // ToDo: $this->aData = $mValue;
            } else {
                $this->_logError(3, array('key_type' => gettype($mKey)));
            }

        } else {
            $this->_logError(10);
        }
        return $this;
    } // function set

    public function toArray()
    {
        if (!$this->bMultiLevel) {
            return $this->aData;
        }
        $aRet = array();
        foreach ($this->aData as $k => $v) {
            $aRet[$k] = $this->_isThisClass($v) ? $v->toArray() : $v;
        }
        return $aRet;
    }

    /**
     * Is Allowed Full Rewrite data
     * @return boolean
     */
    public function isFullRewrite()
    {
        return $this->bFullRewrite;
    }

    // ======== Private/Protected methods ======== \\

    /**
     * Restore of Setter
     * @return \fan\core\base\data
     */
    protected function _restoreSetters()
    {
        //Redefine this method for restore list of Setters
        return $this;
    } // function _restoreSetters

    /**
     * Set Classes of Setter
     * @param object|string $mSetter
     * @return boolean
     */
    protected function _setSetter($mSetter)
    {
        if (is_object($mSetter) || is_string($mSetter)) {
            $this->aSetter[] = $mSetter;
            return true;
        }
        $this->_logError(5, array('type' => gettype($mSetter)));
        return false;
    } // function _setSetter

    /**
     * Check Setter
     * @return boolean
     */
    protected function _checkSetter()
    {
        if (empty($this->aSetter)) {
            return true;
        }
        $aTrace = debug_backtrace();
        // Skip calling from this class
        do {
            foreach ($aTrace as $aLink) {
                // ToDo: There is possible collisie for MultiLevel data (Instances of this classes from another branches can change data there)
                if (!isset($aLink['object']) || ($this->bMultiLevel ? !$this->_isThisClass($aLink['object']) : $aLink['object'] !== $this)) {
                    break 2;
                }
            }
            return false;
        } while (false);

        // Check object or class of caller
        foreach ($this->aSetter as $v) {
            if (is_object($v)) {
                if (!empty($aLink['object']) && $aLink['object'] === $v) {
                    return true;
                }
            } elseif ($this->_checkSetterClass($aLink, $v)) {
                return true;
            }
        }

        return false;
    } // function _checkSetter

    /**
     * Get Multilevel Data
     * @param mixed $mKey
     * @param mixed $mDefault
     * @return mixed
     */
    protected function _getMultilevelData($mKey, $mDefault, $bLogError)
    {
        $sKey = array_shift($mKey);
        if (empty($mKey)) {
            return $this->get($sKey, $mDefault, $bLogError);
        } elseif (isset($this->aData[$sKey]) && $this->_isThisClass($this->aData[$sKey])) {
            return $this->aData[$sKey]->get($mKey, $mDefault, $bLogError);
        }
        return $mDefault;
    } // function _getMultilevelData

    /**
     * Convert Array to another structure (usually instance of this class)
     * Methd need to redefine in children classes if it use another parameter of constructor
     * @param string $sKey
     * @param array $aValue
     * @return mixed
     */
    protected function _makeSubData($sKey, $aValue)
    {
        $sClass = get_class($this);
        return new $sClass($aValue, $sKey, $this);
    } // function _makeSubData

    /**
     * Check is Object instance of this Class
     * @param object $oObject
     * @return boolean
     */
    protected function _isThisClass($oObject)
    {
        return get_class_alt($oObject) == get_class($this);
    } // function _isThisClass

    /**
     * Check Class of setter
     * If don't need to pay attention on parent classes - redefine this method
     * @param array $aLink
     * @param string $sClass
     * @return boolean
     */
    protected function _checkSetterClass($aLink, $sClass)
    {
        return $aLink['class'] == $sClass || !empty($aLink['object']) && $aLink['object'] instanceof $sClass;
    } // function _checkSetterClass

    /**
     * Get Objects of Sub-Data - Instances of current class
     * @return array
     */
    protected function _getSubData()
    {
        $aRet = array();
        $sClass = get_class($this);
        foreach ($this->aData as $v) {
            if ($v instanceof $sClass) {
                $aRet[] = $v;
            }
        }
        return $aRet;
    } // function _getSubrows

    /**
     * Log Error message
     * @param string $sErrKey
     * @param array $aReplacement
     * @return boolean
     */
    protected function _logError($sErrKey, $aReplacement = array())
    {
        $sErrMsg = $this->aErrMsg[$sErrKey];
        if (!isset($aReplacement['class'])) {
            $aReplacement['class'] = get_class($this);
        }
        foreach ($aReplacement as $k => $v) {
            $sErrMsg = str_replace('{' . strtoupper($k) . '}', $v, $sErrMsg);
        }
        \fan\project\service\error::instance()->logErrorMessage($sErrMsg, 'Data error', '', true);
        return $this;
    } // function _logError

    // ======== The magic methods ======== \\

    /**
     * Magic set method for data-array
     * @param mixed $sKey
     * @param mixed $mValue
     */
    public function __set($sKey, $mValue)
    {
        $this->set($sKey, $mValue);
    } // function __set

    /**
     * Magic get method for data-array
     * @param mixed $sKey
     * @return mixed
     */
    public function __get($sKey)
    {
        return $this->get($sKey);
    } // function __get

    /**
     * Magic isset method for data-array
     * @param mixed $sKey
     * @return boolean
     */
    public function __isset($sKey)
    {
        return isset($this->aData[$sKey]);
    } // function __isset

    /**
     * Magic unset method for data-array
     * @param mixed $sKey
     */
    public function __unset($sKey)
    {
        if ($this->_checkSetter()) {
            unset($this->aData[$sKey]);
        } else {
            $this->_logError(12, array('key' => $sKey));
        }
    } // function __unset

    /**
     * Magic method for convert this object to string
     * @return string
     */
    public function __toString()
    {
        $sRet = '';
        foreach ($this->aData as $k => $v) {
            if (!empty($sRet)) {
                $sRet .= "\n";
            }
            $sRet .= $k . ' => ';

            if (is_null($v)) {
                $sRet .= '(NULL)';
            } elseif (is_bool($v)) {
                $sRet .= '(boolean) ' . ($v ? 'TRUE' : 'FALSE');
            } elseif (is_scalar($v)) {
                $sRet .= '(' . gettype($v) . ') ' . $v;
            } elseif (is_object($v)) {
                $sRet .= '(Intanse Of ' . get_class($v) . ")\n";
                if (method_exists($v, '__toString')) {
                    $sRet .= $v->__toString();
                } else {
                    // ToDo: Show Another objects
                }
            } elseif (is_array($v)) {
                $sRet .= '(array) ';
                // ToDo: Show Array
            }
        }
        return $sRet;
    } // function __toString

    // ======== Required Interface methods ======== \\
    /**
     * Method of interface ArrayAccess
     * @param mixed $sKey
     * @return boolean
     */
    public function offsetExists($sKey)
    {
        return isset($this->aData[$sKey]);
    } // function offsetExists

    /**
     * Method of interface ArrayAccess
     * @param mixed $sKey
     * @return mixed
     */
    public function offsetGet($sKey)
    {
        return $this->get($sKey);
    } // function offsetGet

    /**
     * Method of interface ArrayAccess
     * @param mixed $sKey
     * @param mixed $mValue
     */
    public function offsetSet($sKey, $mValue)
    {
        $this->set($sKey, $mValue);
    } // function offsetSet

    /**
     * Method of interface ArrayAccess
     * @param mixed $sKey
     */
    public function offsetUnset($sKey)
    {
        unset($this->aData[$sKey]);
    } // function offsetUnset

    /**
     * Method of interface Iterator
     * @return mixed
     */
    public function current()
    {
        return current($this->aData);
    } // function current

    /**
     * Method of interface Iterator
     * @return mixed
     */
    public function key()
    {
        return key($this->aData);
    } // function key

    /**
     * Method of interface Iterator
     */
    public function next()
    {
        next($this->aData);
    } // function next

    /**
     * Method of interface Iterator
     */
    public function rewind()
    {
        reset($this->aData);
    } // function rewind

    /**
     * Method of interface Iterator
     * @return booleean
     */
    public function valid()
    {
        return key($this->aData) !== null;
    } // function valid

    /**
     * Method of interface Countable
     * @return integer
     */
    public function count()
    {
        return count($this->aData);
    } // function count

    /**
     * Method of interface Serializable
     * @return string
     */
    public function serialize()
    {
        return serialize(array(
            'multiLevel'  => $this->bMultiLevel,
            'fullRewrite' => $this->bFullRewrite,
            'errMsg'      => $this->aErrMsg,
            'data'        => $this->aData,
        ));
    } // function serialize

    /**
     * Method of interface Serializable
     * @param string $sRecover
     */
    public function unserialize($sRecover)
    {
        $aRecover = unserialize($sRecover);

        $this->bMultiLevel  = $aRecover['multiLevel'];
        $this->bFullRewrite = $aRecover['fullRewrite'];
        $this->aErrMsg      = $aRecover['errMsg'];

        $this->aData = $aRecover['data'];
        if ($this->bMultiLevel) {
            foreach ($this->aData as $k => $v) {
                if (is_object($v) && $v instanceof \fan\core\base\data) {
                    $v->sKey      = $k;
                    $v->oSuperior = $this;
                }
            }
        }
        // Attention: restore Setter in the children class by method _setSetter
    } // function unserialize

} // class \fan\core\base\data
?>