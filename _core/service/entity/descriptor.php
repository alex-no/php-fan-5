<?php namespace core\service\entity;
/**
 * Description of descriptor
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
 * @version of file: 05.001
 */
abstract class descriptor
{
    /**
     * Description object
     * @var \core\service\entity\description
     */
    protected $oDescription = null;
    /**
     * Connection to database
     * @var \core\service\database
     */
    protected $oConnection = null;

    /**
     * Table Name
     * @var string
     */
    protected $sTableName = null;

    public function __construct(\core\service\entity\description $oDescription)
    {
        $this->oDescription = $oDescription;
        $this->oConnection  = $oDescription->getEntity()->getConnection();
        $this->sTableName   = $oDescription->getTableName();
    } // function __construct

    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    /**
     * Return 2x array with description of fields, like:
     * column_name => (type, length, default, collation, attribute, null, auto_increment, comment, mime_type)
     */
    abstract public function getFields();
    /**
     * Return Primery Key - name of field(s): strig (if one field) OR array (if several fields)
     */
    abstract public function getPrimeryKey();
    /**
     * Return 2x array with description of Keys, like:
     * key_name => (type, fields)
     */
    abstract public function getKeys();
    /**
     * Return 2x array with description of Relations, like:
     * number => (name, field, ref_db, ref_table, ref_field, on_delete, on_update)
     */
    abstract public function getRelations();
    /**
     * Return string with Engine of Table
     */
    abstract public function getEngine();
    /**
     * Return string with Create Time of Table
     */
    abstract public function getCreateTime();
    /**
     * Return string with Table Collation
     */
    abstract public function getTableCollation();
    /**
     * Return string with comment OR null if comment doesn't exist
     */
    abstract public function getComment();
    // ======== Private/Protected methods ======== \\
} // class \core\service\entity\descriptor
?>