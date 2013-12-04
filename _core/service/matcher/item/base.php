<?php namespace core\service\matcher\item;
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
 */
abstract class base implements \ArrayAccess, \Iterator
{
    /**
     * Current Index of Iterator
     * @var integer
     */
    protected $iIndex = 0;

    /**
     * Allowed property
     * @var array
     */
    protected $aData = array(
    );
    /**
     * Variable property
     * @var array
     */
    protected $aVariable = array(
    );

    /**
     * Facade of service
     * @var \core\service\matcher\item
     */
    protected $oItem = null;

    /**
     * Facade of service
     * @var core\base\service
     */
    protected $oFacade = null;

    /**
     * Make matcher data
     * @param \core\service\matcher\item $oItem
     */
    public function __construct(\core\service\matcher\item $oItem)
    {
        $this->oItem = $oItem;
    }

    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\

    public function __set($sKey, $value)
    {
        return $this->set($sKey, $value);
    }

    public function __get($sKey)
    {
        return $this->get($sKey);
    }

    // ======== Required Interface methods ======== \\

    public function offsetSet($sKey, $mValue)
    {
        return $this->set($sKey, $mValue);
    }

    public function offsetGet($sKey)
    {
        return $this->get($sKey);
    }

    public function offsetExists($sKey)
    {
        $this->_checkKey($sKey);
        return !empty($this->aData[$sKey]);
    }

    public function offsetUnset($sKey)
    {
        $this->_checkKey($sKey);
        $this->_makeException('Isn\'t allowed unset subproperty of item of matcher.');
    }

    public function rewind()
    {
        $this->iIndex = 0;
    }

    public function current()
    {
        return $this->valid() ? $this->aData[$this->_getCurrentKey()] : null;
    }

    public function key()
    {
        return $this->iIndex;
    }

    public function next()
    {
        ++$this->iIndex;
    }

    public function valid()
    {
        return !is_null($this->_getCurrentKey());
    }
    // ======== Main Interface methods ======== \\

    /**
     * Set data by keys
     * @param string $sKey
     * @param mixed $mValue
     * @return \core\service\matcher\item\base
     */
    public function set($sKey, $mValue)
    {
        $sMethod = $this->_checkKey($sKey, 'set');
        if(!is_null($this->aData[$sKey]) && !in_array($sKey, $this->aVariable)) {
            trigger_error('Error. Try to change existing property.', E_USER_WARNING);
        } elseif(method_exists($this, $sMethod)) {
            $this->$sMethod($sKey, $mValue);
        } else {
            $this->aData[$sKey] = $mValue;
        }
        return $this;
    }

    /**
     * Get data by keys
     * @param string $sKey
     * @return mixed
     */
    public function get($sKey)
    {
        $sMethod = $this->_checkKey($sKey, 'get');
        return method_exists($this, $sMethod) ? $this->$sMethod() : $this->aData[$sKey];
    }

    /**
     * Gets All Data as array
     * @return array
     */
    public function toArray()
    {
        return $this->aData;
    } // function toArray

    /**
     * Set Facade
     * @param core\service\matcher $oFacade
     * @return \core\service\matcher\item\base
     */
    public function setFacade(\core\service\matcher $oFacade)
    {
        $this->oFacade = $oFacade;
        return $this;
    } // function setFacade

    // ======== Private/Protected methods ======== \\

    /**
     * Check Data Key
     * @param string $sKey
     * @param string $sMethod
     * @return string
     */
    protected function _checkKey($sKey, $sMethod = '')
    {
        if (!array_key_exists($sKey, $this->aData)) {
            $this->_makeException('Invalid key "' . $sKey . '" while accessing the item property of matcher.');
        }
        if (!empty($sMethod)) {
            foreach (explode('_', $sKey) as $v) {
                $sMethod .= ucfirst($v);
            }
        }
        return $sMethod;
    }

    /**
     * Get Current Key
     * @return string
     */
    protected function _getCurrentKey()
    {
        $aKeys = array_keys($this->aData);
        return isset($aKeys[$this->iIndex]) ? $aKeys[$this->iIndex] : null;
    }

    /**
     * Make Exception
     * @param string $sErrMsg
     * @throws \project\exception\service\fatal
     * @throws \project\exception\fatal
     */
    protected function _makeException($sErrMsg)
    {
        if ($this->oFacade) {
            throw new \project\exception\service\fatal($this->oFacade, $sErrMsg);
        }
        throw new \project\exception\fatal($sErrMsg);
    }

    /**
     * Get Config row of Matcher
     * @param type $mKey
     * @param type $mDefault
     * @return type
     */
    public function _getConfig($mKey, $mDefault = null)
    {
        return $this->oFacade->getConfig()->get($mKey, $mDefault);
    } // function _getConfig

} // class \core\service\matcher\item\base
?>