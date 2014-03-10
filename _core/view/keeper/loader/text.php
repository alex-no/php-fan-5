<?php namespace fan\core\view\keeper\loader;
/**
 * View-data keeper of Block data for loader JSON-data
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
class text extends \fan\core\view\keeper
{
    /**
     * View meta constructor
     * @param fan\core\block\base $oRouter
     */
    public function __construct(\fan\core\view\router $oRouter)
    {
        parent::__construct($oRouter);
        $this->bFullRewrite = true;
    } // function __construct

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\

    /**
     * Get value of data
     * @param string|array $mKey
     * @param mixed $mDefault
     * @param boolean $bLogError
     * @return mixed
     */
    public function get($mKey = null, $mDefault = null, $bLogError = true)
    {
        $sResult = $this->__toString();
        return empty($sResult) ? $mDefault : $sResult;
    } // function get

    /**
     * Set value of data
     * @param string|number $mKey
     * @param mixed $mValue
     * @param boolean $bRewriteExisting - rewrite exists value
     * @param boolean $bConvArray - convert array to object of this class (null is true for Multi-Level data)
     * @return \fan\core\view\keeper
     */
    public function set($mKey, $mValue, $bRewriteExisting = true, $bConvArray = null)
    {
        if (!is_numeric($mKey)) {
            $mKey = empty($mKey) ? 0 : 1;
        }

        if (empty($mKey) && $this->isFullRewrite()) {
            $this->aData = array($mValue);
        } elseif ($mKey < 0) {
            array_unshift($this->aData, $mValue);
        } else {
            array_push($this->aData, $mValue);
        }
        return $this;
    }

    /**
     * Add Router
     * @param \fan\core\view\router\loader $oRouter
     * @return \fan\core\view\keeper\loader\text
     */
    public function addRouter(\fan\core\view\router\loader $oRouter)
    {
        $this->_setSetter($oRouter);
        $this->_setSetter($oRouter->getBlock());
        return $this;
    } // function addRouter

    // ======== Private/Protected methods ======== \\

    // ======== The magic methods ======== \\

    public function __set($iKey, $mValue)
    {
        $this->set($mValue, (int)$iKey);
    }

    public function __get($sKey)
    {
        return $this->get(null);
    }

    public function __toString() {
        return implode('', $this->aData);
    }

    // ======== Required Interface methods ======== \\

} // class \fan\core\view\keeper\loader\text
?>