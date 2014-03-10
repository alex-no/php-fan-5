<?php namespace fan\core\service\entity\descriptor\mysql;

/**
 * Get table description by information_schema
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
class schema extends \fan\core\service\entity\descriptor\mysql
{
    /**
     * Data Base Name
     * @var string
     */
    protected $sDbName = null;

    /**
     * Connection to database
     * @var \fan\core\service\database
     */
    protected $oConnectionSchema = null;

    /**
     * Array of Table Info
     * @var string
     */
    protected $aTableInfo = null;

    /**
     * Array of Constraints
     * @var string
     */
    protected $aConstraints = null;

    public function __construct(\fan\core\service\entity\description $oDescription)
    {
        parent::__construct($oDescription);
//throw new \fan\project\exception\model\reverse($oDescription->getEntity());

        $aCheckParam = array(
            'ENGINE'     => null,
            'PERSISTENT' => 0,
            'HOST'       => 'localhost',
            'DATABASE'   => null,
            'USER'       => null,
            'PASSWORD'   => '',
        );
        $oConnect = $oDescription->getEntity()->getConnection();
        $aParam = $oConnect->getConnectionParam();
        foreach ($aCheckParam as $k => $v) {
            if (!isset($aParam[$k])) {
                if (is_null($v)) {
                    throw new \project\exception\model\reverse($oDescription->getEntity(), 'Undefined required connect parameter ' . $k . ' for connection ' . $oConnect->getConnectionName());
                } else {
                    $aParam[$k] = $v;
                }
            }
        }

        try {
            $this->oConnectionSchema = \fan\project\service\database::instanceByParam(array(
                'ENGINE'     => $aParam['ENGINE'],
                'PERSISTENT' => $aParam['PERSISTENT'],
                'HOST'       => $aParam['HOST'],
                'DATABASE'   => 'information_schema',
                'USER'       => $aParam['USER'],
                'PASSWORD'   => $aParam['PASSWORD'],
                'SCENARIO'   => '',
            ), 'shema');
        } catch (\fan\project\exception\database $oExp) {
            $oExp->disableLog();
            throw new \fan\project\exception\model\reverse($oDescription->getEntity());
        }

        $this->sDbName = $aParam['DATABASE'];
    } // function __construct

    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    /**
     * Check - is Table exists in DB
     * @return boolean
     */
    public function isTableExists()
    {
        $aInfo = $this->_getTableInfo();
        return !empty($aInfo);
    } // function isTableExists
    /**
     * Return 2x array with description of fields, like:
     * column_name => (type, length, default, collation, attribute, null, auto_increment, comment, mime_type)
     * @return array
     */
    public function getFields()
    {
        $aResult = array();
        foreach ($this->_getFields() as $v) {
            //Doesn't used: CHARACTER_MAXIMUM_LENGTH, CHARACTER_OCTET_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE, COLUMN_KEY
            preg_match('/^\w+\((.*)\)\s*(.*)$/', $v['COLUMN_TYPE'], $aMatches);
            $aField = array(
                'type'           => $v['DATA_TYPE'],
                'length'         => empty($aMatches) ? null : $aMatches[1],
                'default'        => empty($v['COLUMN_DEFAULT']) ? null : $v['COLUMN_DEFAULT'],
                'collation'      => $v['COLLATION_NAME'],
                'charset'        => $v['CHARACTER_SET_NAME'],
                'attribute'      => empty($aMatches) ? null : $aMatches[2],
                'null'           => strtoupper($v['IS_NULLABLE']) == 'YES',
                'auto_increment' => strtolower($v['EXTRA']) == 'auto_increment',
                'comment'        => empty($v['COLUMN_COMMENT']) ? null : $v['COLUMN_COMMENT'],
                //'mime_type'      => '',
            );

            $this->_resetDefaultVal($aField);

            $aResult[$v['COLUMN_NAME']] = $aField;
        }

        foreach ($this->getKeys() as $k0 => $v0) {
            foreach ($v0['fields'] as $k1 => $v1) {
                $aResult[$k1]['keys'][] = $k0;
            }
        }
        return $aResult;
    } // function getFields

    /**
     * Return 2x array with description of Relations, like:
     * number => (name, field, ref_db, ref_table, ref_field, on_delete, on_update)
     * @return array
     */
    public function getRelations()
    {
        $aResult = array();
        foreach ($this->_getConstraints() as $v) {
            $aResult[] = array(
                'name'      => $v['CONSTRAINT_NAME'],
                'field'     => $v['COLUMN_NAME'],
                'ref_db'    => $v['UNIQUE_CONSTRAINT_SCHEMA'] == $this->sDbName ? null : $v['UNIQUE_CONSTRAINT_SCHEMA'],
                'ref_table' => $v['REFERENCED_TABLE_NAME'],
                'ref_field' => $v['REFERENCED_COLUMN_NAME'],
                'on_delete' => strtolower($v['DELETE_RULE']),
                'on_update' => strtolower($v['UPDATE_RULE']),
            );
        }

        return $aResult;
    } // function getRelations

    /**
     * Return string with Engine of Table
     * @return string
     */
    public function getEngine()
    {
        $aInfo = $this->_getTableInfo();
        return $aInfo['ENGINE'];
    } // function getEngine
    /**
     * Return string with Create Time of Table
     * @return string
     */
    public function getCreateTime()
    {
        $aInfo = $this->_getTableInfo();
        return $aInfo['CREATE_TIME'];
    } // function getTableCollation
    /**
     * Return string with Table Collation
     * @return string
     */
    public function getTableCollation()
    {
        $aInfo = $this->_getTableInfo();
        return $aInfo['TABLE_COLLATION'];
    } // function getTableCollation
    /**
     * Return string with comment OR null if comment doesn't exist
     * @return string
     */
    public function getComment()
    {
        $aInfo = $this->_getTableInfo();
        return $aInfo['TABLE_COMMENT'];
    } // function getComment

    // ======== Private/Protected methods ======== \\
    /**
     * Get source Table-Info
     * @return array
     */
    protected function _getTableInfo()
    {
        if (is_null($this->aTableInfo)) {
            $aTmp = $this->oConnectionSchema->getAll(
                    '
SELECT
    *
FROM `TABLES`
WHERE
    `TABLE_SCHEMA` = ?
    AND `TABLE_NAME` = ?',
                    array($this->sDbName, $this->sTableName)
            );
            $this->aTableInfo = empty($aTmp[0]) ? array() : $aTmp[0];
        }
        return $this->aTableInfo;
    } // function _getTableInfo

    /**
     * Get source Fields
     * @return array
     */
    protected function _getFields()
    {
        if (empty($this->aSrcFields)) {
            $this->aSrcFields = $this->oConnectionSchema->getAll(
                    '
SELECT
    *
FROM `COLUMNS`
WHERE
    `TABLE_SCHEMA` = ?
    AND `TABLE_NAME` = ?
ORDER BY
    `ORDINAL_POSITION`',
                    array($this->sDbName, $this->sTableName)
            );
        }
        return $this->aSrcFields;
    } // function _getFields

    /**
     * Get source Constraints
     * @return array
     */
    protected function _getConstraints()
    {
        if (is_null($this->aConstraints)) {
            $aTmp = $this->oConnectionSchema->execute(
                    '
SELECT
    REF.*,
    USG.`COLUMN_NAME`,
    USG.`REFERENCED_COLUMN_NAME`
FROM
    `REFERENTIAL_CONSTRAINTS` AS REF
INNER JOIN `KEY_COLUMN_USAGE` AS
    USG ON USG.`CONSTRAINT_NAME` = REF.`CONSTRAINT_NAME`
WHERE
    REF.`CONSTRAINT_SCHEMA` = ?
    AND REF.`TABLE_NAME` = ?
    AND USG.`CONSTRAINT_SCHEMA` = ?
    AND USG.`TABLE_NAME` = ?
ORDER BY
    USG.`ORDINAL_POSITION`',
                    array($this->sDbName, $this->sTableName, $this->sDbName, $this->sTableName)
            );
            $this->aConstraints = empty($aTmp) ? array() : $aTmp;
        }
        return $this->aConstraints;
    } // function _getConstraints
} // class \fan\core\service\entity\descriptor\mysql\schema
?>