<?php namespace fan\core\service\entity\designer;
/**
 * Designer of SQL-request DELETE
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
class delete extends \fan\core\service\entity\designer
{
    /**
     * SQL-request parts
     * @var string
     */
    protected $aQueryParts = array(
        'deleleTable'    => null,
        'whereCondition' => array(),
    );

    // ======== Static methods ======== \\

    // ======== The magic methods ======== \\

    // ======== Main Interface methods ======== \\

    /**
     * Set parts of SQL-requests for delete by parameters
     * @param array $mParam
     * @return \fan\core\service\entity\designer\delete
     */
    public function setDeleteByParam($mParam)
    {
        $this->aQueryParts = array(
            'deleleTable'    => 'DELETE FROM `' . $this->getEntity()->getTableName() . '`',
            'whereCondition' => $this->makeWhere($mParam, false),
        );
        $this->aSrcParam = $mParam;
        return $this;
    } // function setDeleteByParam

    // ======== Private/Protected methods ======== \\

} // class \fan\core\service\entity\designer\delete
?>