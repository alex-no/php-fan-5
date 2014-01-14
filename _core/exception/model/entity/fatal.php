<?php namespace core\exception\model\entity;
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
 * @version of file: 05.005 (14.01.2014)
 */
class fatal extends  \core\exception\base
{
    /**
     * Entity's object
     * @var \core\base\model\entity
     */
    protected $oEntity = null;

    /**
     * Exception's constructor
     * @param object $oEntity Object - instance of entity
     * @param string $sLogErrMsg Log error message
     * @param numeric $nCode Error Code
     * @param \Exception $oPrevious Previous Exception
     */
    public function __construct(\core\base\model\entity $oEntity, $sLogErrMsg, $nCode = E_USER_ERROR, $oPrevious = null)
    {
        $this->oEntity = $oEntity;

        parent::__construct($sLogErrMsg, $nCode, $oPrevious);

        $this->_logByService($sLogErrMsg, 'Entity fatal error (' . get_class($oEntity) . ').');
    }

    /**
     * Get object of entity
     * @return \core\base\model\entity
     */
    public function getEntity()
    {
        return $this->oEntity;
    } // function getEntity

    /**
     * Get operation for Db (rollback) when exception occured
     * @param string $sDbOper
     * @return null|string
     */
    protected function _getDbOperation($sDbOper = 'rollback')
    {
        return parent::_getDbOperation($sDbOper);
    } // function _getDbOperation
} // class \core\exception\model\entity\fatal
?>