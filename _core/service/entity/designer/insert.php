<?php namespace core\service\entity\designer;
/**
 * Designer of SQL-request INSERT
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
class insert extends \core\service\entity\designer
{
    /**
     * SQL-request parts
     * @var string
     */
    protected $aQueryParts = array(
        'insertTable' => null,
        'setData'     => array(),
    );


    // ======== Static methods ======== \\

    // ======== The magic methods ======== \\

    // ======== Main Interface methods ======== \\

    /**
     * Set parts of SQL-requests for Insert by parameters
     * @param array $mParam
     * @return \core\service\entity\designer\insert
     */
    public function setInsertByParam($mParam)
    {
        $this->aQueryParts = array(
            'insertTable' => 'INSERT INTO `' . $this->getEntity()->getTableName() . '` SET ',
            'setData'     => $this->_makeSetupPart($mParam),
        );
        $this->aSrcParam = $mParam;
        return $this;
    } // function setInsertByParam

    // ======== Private/Protected methods ======== \\

} // class \core\service\entity\designer\insert
?>