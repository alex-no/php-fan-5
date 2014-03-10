<?php namespace fan\core\service\entity\designer;
/**
 * Designer of SQL-request UPDATE
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
class update extends \fan\core\service\entity\designer
{
    /**
     * SQL-request parts
     * @var string
     */
    protected $aQueryParts = array(
        'insertTable'    => null,
        'setData'        => null,
        'whereCondition' => array(),
    );


    // ======== Static methods ======== \\

    // ======== The magic methods ======== \\

    // ======== Main Interface methods ======== \\

    /**
     * Set parts of SQL-requests for Update by data and parameters
     * @param appay $aData
     * @param mixed $mParam
     * @return \fan\core\service\entity\designer\update
     */
    public function setUpdateByParam($aData, $mParam)
    {
        $this->aQueryParts = array(
            'insertTable'    => 'UPDATE `' . $this->getEntity()->getTableName() . '` SET ',
            'setData'        => $this->_makeSetupPart($aData),
            'whereCondition' => $this->makeWhere($mParam, false),
        );
        $this->aSrcParam = array_merge($aData, $mParam);
        return $this;
    } // function setUpdateByParam

    // ======== Private/Protected methods ======== \\

} // class \fan\core\service\entity\designer\update
?>