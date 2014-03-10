<?php namespace fan\core\view;
/**
 * View data-keeper
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
class keeper extends \fan\core\base\data
{
    /**
     * @var \fan\core\view\router
     */
    protected $oRouter;

    /**
     * View meta constructor
     * @param fan\core\block\base $oRouter
     */
    public function __construct(\fan\core\view\router $oRouter)
    {
        $this->oRouter = $oRouter;
        $this->_setSetter($oRouter);
        $this->_setSetter($oRouter->getBlock());
        $this->bMultiLevel = false;
    } // function __construct

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
        return is_null($mKey) ? $this->toArray() : parent::get($mKey, $mDefault, $bLogError);
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
        if (is_null($mKey) && $this->isFullRewrite()) {
            $this->aData = $mValue;
        } else {
            parent::set($mKey, $mValue, $bRewriteExisting, $bConvArray);
        }
        return $this;
    }

    /**
     * Clear view data
     * @return \fan\core\view\keeper
     */
    public function clear()
    {
        if ($this->_checkSetter()) {
            $this->aData = array();
        }
        return $this;
    }

    /**
     * Get router
     * @return \fan\core\view\router
     */
    public function getRouter()
    {
        return $this->oRouter;
    }

    /**
     * Get block-owner
     * @return \fan\core\block\base
     */
    public function getBlock()
    {
        return $this->oRouter->getBlock();
    }

} // class \fan\core\view\keeper
?>