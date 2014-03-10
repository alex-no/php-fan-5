<?php namespace fan\core\exception\block;
/**
 * Exception a block local error. Usually catch immediate in the block
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
class local extends \fan\core\exception\base
{
    /**
     * Block's object
     * @var \fan\core\block\base
     */
    protected $oBlock = null;

    /**
     * Exception's constructor
     * @param \fan\core\block\base $oBlock Object - instance of block
     * @param string $sLogErrMsg Log error message
     * @param numeric $nCode Error Code
     * @param \Exception $oPrevious Previous Exception
     */
    public function __construct(\fan\core\block\base $oBlock, $sLogErrMsg, $nCode = E_USER_NOTICE, $oPrevious = null)
    {
        $this->oBlock = $oBlock;
        parent::__construct($sLogErrMsg, $nCode, $oPrevious = null);
    } // function __construct

    /**
     * Get object of block
     * @return \fan\core\block\base
     */
    public function getBlock()
    {
        return $this->oBlock;
    } // function getBlock

    /**
     * Get operation for Db (rollback, commit or nothing) when exception occured
     * @param string $sDbOper
     * @return null|string
     */
    protected function _defineDbOper($sDbOper = null)
    {
        if (empty($sDbOper) && method_exists($this->oBlock, 'getExceptionDbOper')) {
            $sDbOper = $this->oBlock->getExceptionDbOper();
        }
        return parent::_defineDbOper($sDbOper);
    } // function _defineDbOper
} // class \fan\core\exception\block\local
?>