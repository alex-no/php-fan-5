<?php namespace core\view;
/**
 * View element of Block
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
abstract class router implements \ArrayAccess, \Countable
{
    /**
     * Parent block
     * @var \core\block\base
     */
    protected $oBlock;
    /**
     * Array of Keepers
     * @var array
     */
    protected $aKeepers = array();
    /**
     * Default Route Keeper
     * @var string
     */
    protected $sDefaultKey = null;

    /**
     * Constructor of View router
     * @param core\block\base $oBlock
     */
    public function __construct(\core\block\base $oBlock)
    {
        if (empty($this->aKeepers) || !is_array($this->aKeepers)) {
            throw new \project\exception\block\fatal($this, 'Keepers list doesn\'t set at the class "' . get_class($this) . '"');
        }
        if (empty($this->sDefaultKey)) {
            reset($this->aKeepers);
            $this->sDefaultKey = key($this->aKeepers);
        } elseif (!array_key_exists($this->sDefaultKey, $this->aKeepers)) {
            throw new \project\exception\block\fatal($this, 'Incorrect default Keepers key "' . $this->sDefaultKey . '" at the class "' . get_class($this) . '"');
        }
        $this->oBlock = $oBlock;
    } // function __construct
    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\

    public function __set($sKey, $mValue)
    {
        $this->set($sKey, $mValue);
    }

    public function __get($sKey)
    {
        return $this->get($sKey);
    }

    public function __isset($sKey)
    {
        return !empty($this->aKeepers[$sKey]);
    }

    // ======== Required Interface methods ======== \\

    public function offsetSet($sKey, $mValue)
    {
        $this->set($sKey, $mValue);
    }

    public function offsetGet($sKey)
    {
        return $this->get($sKey);
    }

    public function offsetExists($sKey)
    {
        return array_key_exists($sKey, $this->aKeepers);
    }

    public function offsetUnset($sKey)
    {
    }
    // ======== Main Interface methods ======== \\
    /**
     * Set special or default view data
     * @param string $sKey
     * @param mixed $mValue
     * @return \core\view\router
     */
    public function set($sKey, $mValue)
    {
        if ($this->_checkSetter()) {
            if (array_key_exists($sKey, $this->aKeepers)) {
                $this->_getKeeper($sKey)->set(null, $mValue);
            } else {
                $sMethod = 'set' . ucfirst(strtolower($this->sDefaultKey));
                if (method_exists($this, $sMethod)) {
                    $this->$sMethod($mValue);
                } else {
                    $oKeeper = $this->_getKeeper($this->sDefaultKey);
                    $oKeeper->set($sKey, $mValue);
                }
            }
        }
        return $this;
    }

    /**
     * Get special or default view data
     * @param string $sKey
     * @return mixed
     */
    public function get($sKey)
    {
        $sMethod = 'get' . ucfirst(strtolower($sKey));
        if (method_exists($this, $sMethod)) {
            return $this->$sMethod();
        }
        if (array_key_exists($sKey, $this->aKeepers)) {
            return $this->_getKeeper($sKey);
        }
        $oKeeper = $this->_getKeeper($this->sDefaultKey);
        return $oKeeper->get($sKey);
    }

    /**
     * Get block-owner
     * @return \core\block\base
     */
    public function getBlock()
    {
        return $this->oBlock;
    }

    /**
     * Alias of getAll-method
     * @return array
     */
    public function toArray()
    {
        return $this->getAll();
    }

    /**
     * Get All data as array
     * @return array
     */
    public function getAll()
    {
        if (count($this->aKeepers) == 1) {
            return $this->_getKeeper($this->sDefaultKey)->toArray();
        }
        $aResult = array();
        foreach ($this->aKeepers as $k => $v) {
            $aResult[$k] = adduceToArray($v);
        }
        return $aResult;
    }

    /**
     * Count of Keepers
     * @return integer
     */
    public function count()
     {
         return count($this->aKeepers);
     }

    // ======== Private/Protected methods ======== \\
    /**
     * Get Keeper
     * @param string $sKey
     * @return \core\view\keeper
     * @throws \core\exception\block\fatal
     */
    public function _getKeeper($sKey)
    {
        if (!array_key_exists($sKey, $this->aKeepers)) {
            throw new \project\exception\block\fatal($this, 'Incorrect name of Keeper "' . $sKey . '"');
        }
        if (empty($this->aKeepers[$sKey])) {
            $sMethod = '_get' . ucfirst($sKey) . 'Keeper';
            $this->aKeepers[$sKey] = method_exists($this, $sMethod) ? $this->$sMethod() : new \project\view\keeper($this);
        }
        return $this->aKeepers[$sKey];
    }

    /**
     * Check Setter
     * @return boolean
     */
    protected function _checkSetter()
    {
        $aTrace = debug_backtrace();
        foreach ($aTrace as $v) {
            if (!isset($v['object'])) {
                return false;
            }
            if ($v['object'] != $this) {
                return $v['object'] == $this->oBlock;
            }
        }
        return false;
    } // function _checkSetter
} // class \core\view\router
?>