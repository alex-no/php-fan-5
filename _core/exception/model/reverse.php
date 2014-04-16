<?php namespace fan\core\exception\model;
/**
 * Exception a fatal error
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
 * @version of file: 05.02.003 (16.04.2014)
 */
class reverse extends \fan\core\exception\base
{
    /**
     * @var \fan\core\base\model\entity
     */
    protected $oEntity = null;

    /**
     * Exception's constructor
     * @param \fan\core\base\model\entity $oEntity
     * @param string $sLogErrMsg
     * @param error $nCode Error Code
     * @param \Exception $oPrevious Previous Exception
     */
    public function __construct(\fan\core\base\model\entity $oEntity, $sLogErrMsg, $nCode = null, $oPrevious = null)
    {
        $this->oEntity = $oEntity;
        parent::__construct($sLogErrMsg, $nCode, $oPrevious);
    }

    /**
     * Get Entity
     * @return \fan\core\base\model\entity
     */
    public function getEntity()
    {
        return $this->oEntity;
    } // function getEntity

    /**
     * Remove property "oBlock" before "print_r" this object
     */
    public function clearProperty()
    {
        $this->_removeEmbededObject('oEntity');
    } // function clearProperty

    /**
     * Get operation for Db (rollback) when exception occured
     * @param string $sDbOper
     * @return null|string
     */
    protected function _defineDbOper($sDbOper = 'nothing')
    {
        return parent::_defineDbOper($sDbOper);
    } // function _defineDbOper

} // class \fan\core\exception\model\reverse
?>