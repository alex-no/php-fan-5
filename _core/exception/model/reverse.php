<?php namespace core\exception\model;
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
 * @version of file: 05.001 (29.09.2011)
 */
class reverse extends  \Exception
{
    /**
     * @var \core\base\model\entity
     */
    protected $oEntity = null;

    /**
     * Exception's constructor
     * @param \core\base\model\entity $oEntity
     * @param string $sMessage
     */
    public function __construct(\core\base\model\entity $oEntity, $sMessage = '')
    {
        $this->oEntity = $oEntity;
        parent::__construct((string)$sMessage, null);
    }

    /**
     * Get Entity
     * @return \core\base\model\entity
     */
    public function getEntity()
    {
        return $this->oEntity;
    } // function getEntity

} // class \core\exception\model\reverse
?>