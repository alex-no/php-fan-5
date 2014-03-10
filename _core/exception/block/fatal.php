<?php namespace fan\core\exception\block;
/**
 * Exception a block fatal error
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
class fatal extends local
{
    /**
     * Exception's constructor
     * @param \fan\core\block\base $oBlock Object - instance of block
     * @param string $sLogErrMsg Log error message
     * @param numeric $nCode Error Code
     * @param \Exception $oPrevious Previous Exception
     */
    public function __construct($oBlock, $sLogErrMsg, $nCode = E_USER_ERROR, $oPrevious = null)
    {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }

        parent::__construct($oBlock, $sLogErrMsg, $nCode, $oPrevious);

        $this->_logByService($sLogErrMsg, 'Block\'s exception (CLASS: ' . get_class($oBlock) . ').');
    } // function __construct

    /**
     * Get operation for Db (rollback) when exception occured
     * @param string $sDbOper
     * @return null|string
     */
    protected function _defineDbOper($sDbOper = 'rollback')
    {
        return parent::_defineDbOper($sDbOper);
    } // function _defineDbOper
} // class \fan\core\exception\block\fatal
?>