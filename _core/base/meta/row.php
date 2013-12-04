<?php namespace core\base\meta;
/**
 * Meta Data Row
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
class row extends \core\base\data
{
    /**
     * @var \core\block\all Linked block
     */
    protected $oBlock;

    /**
     * @var \core\base\meta\maker
     */
    protected $oMaker;

    /**
     * @var \core\base\meta\row
     */
    protected $oParent;


    /**
     * @var string
     */
    protected $sKeyName;


    /**
     * Constructor of meta row
     * @param core\block\all $oBlock
     */
    public function __construct(maker $oMaker,array $aData, row $oParent = null, $sKeyName = null)
    {
        $this->oMaker   = $oMaker;
        $this->oBlock   = $oMaker->getBlock();
        $this->aData    = $this->makeData($aData);
        $this->oParent  = $oParent;
        $this->sKeyName = $sKeyName;

        $this->_setSetter($oMaker);
        $this->_setSetter($this->oBlock);
    } // function __construct

    // ======== Main Interface methods ======== \\

    public function makeData($aData)
    {
        $aRet = array();
        foreach ($aData as $k => $v) {
            $aRet[$k] = is_array($v) ? new \project\base\meta\row($this->oMaker, $v, $this, $k) : $v;
        }
        return $aRet;
    }

    // ======== Private/Protected methods ======== \\

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
        return new $sClass($this->oMaker, $aValue, $this, $sKey);
    } // function _makeSubData

} // class \core\base\meta\row
?>