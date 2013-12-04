<?php namespace core\exception\block;
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
 * @version of file: 05.001 (29.09.2011)
 */
class local extends  \core\exception\base
{
    /**
     * @var block_base Block's object
     */
    protected $oBlock = null;

    /**
     * Exception's constructor
     * @param \core\block\base $oBlock Object - instance of block
     * @param string $sLogMessage Log error message
     * @param error $nCcode Error Code
     */
    public function __construct($oBlock, $sLogMessage, $nCode = E_USER_NOTICE)
    {
        $this->oBlock = $oBlock;
        parent::__construct($sLogMessage, $nCode);
    } // function __construct

    /**
     * Get object of block
     * @return block_base
     */
    public function getBlock()
    {
        return $this->oBlock;
    } // function getBlock
} // class \core\exception\block\local
?>