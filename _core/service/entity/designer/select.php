<?php namespace fan\core\service\entity\designer;
/**
 * Designer of SQL-request SELECT
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
class select extends \fan\core\service\entity\designer
{
    /**
     * SQL-request parts
     * @var string
     */
    protected $aQueryParts = array(
        'operAndFields'   => null,
        'fromTable'       => null,
        'joinTables'      => array(),
        'whereCondition'  => array(),
        'groupBy'         => null,
        'havingCondition' => array(),
        'orderBy'         => null,
    );


    // ======== Static methods ======== \\

    // ======== The magic methods ======== \\

    // ======== Main Interface methods ======== \\

    /**
     * Set parts of SQL-requests by parameters
     * @param mixed $mParam
     * @param string $sOrderBy
     * @return \fan\core\service\entity\designer\select
     */
    public function setSelectByParam($mParam, $sOrderBy = null)
    {
        $this->setMainSqlParts();
        $this->aQueryParts['whereCondition'] = $this->makeWhere($mParam, false);
        $this->aQueryParts['orderBy']        = $sOrderBy;
        $this->aSrcParam = $mParam;
        return $this;
    } // function setSelectByParam

    /**
     * Set parts: operAndFields, fromTable
     * @return \fan\core\service\entity\designer\select
     */
    public function setMainSqlParts()
    {
        $this->aQueryParts['operAndFields']  = 'SELECT *';
        $this->aQueryParts['fromTable']      = 'FROM `' . $this->getEntity()->getTableName() . '`';
        return $this;
    } // function setMainSqlParts

    // ======== Private/Protected methods ======== \\

} // class \fan\core\service\entity\designer\select
?>