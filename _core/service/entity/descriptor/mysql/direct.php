<?php namespace fan\core\service\entity\descriptor\mysql;

/**
 * Get table description by SQL-requests: "DESCRIBE table", "SHOW KEYS FROM table", "SHOW CREATE TABLE table",
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
class direct extends \fan\core\service\entity\descriptor\mysql
{
    /**
     * SQL-request for create table
     * @var string
     */
    protected $sCreateTable = '';

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
        $aTmp = $this->oConnection->execute('SHOW  TABLES LIKE \'' . $this->sTableName . '\'');
        return !empty($aTmp);
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
            $aMatches = array();
            preg_match('/^(\w+)\s*(?:\((.*)\)\s*(.*))?$/', $v['Type'], $aMatches);
            $aField = array(
                'type'           => strtolower($aMatches[1]),
                'length'         => isset($aMatches[2]) ? $aMatches[2] : null,
                'default'        => empty($v['Default']) ? null : $v['Default'],
                'collation'      => null,
                'charset'        => null,
                'attribute'      => isset($aMatches[3]) ? $aMatches[3] : null,
                'null'           => strtoupper($v['Null']) == 'YES',
                'auto_increment' => strpos($v['Extra'], 'auto_increment') !== false,
                'comment'        => null,
                //'mime_type'      => null,
            );

            $this->_resetDefaultVal($aField);

            // Field name
            $sPattern = '^\s*\`' . preg_replace('/\W/u', '\\\\$0', $v['Field']) . '\`';
            // Field type
            $sPattern .= '\s+\w+(?:\(.*?\))?';
            // Character set / Collate
            $sPattern .= '\s*(?:CHARACTER\s+SET\s+(\w+))?\s*(?:COLLATE\s+(\w+))?';
            // Comment
            $sPattern .= '.*?(?:COMMENT\s+\'(.+)\')?\,?$';

            if(preg_match('/' . $sPattern . '/imu', $this->_getCreateTable(), $aMatches)) {
                $aField['comment'] = empty($aMatches[3]) ? null : $aMatches[3];
                if (!empty($aMatches[1]) || !empty($aMatches[2])) {
                    $aField['collation']  = implode(' ', array(strval($aMatches[1]), strval($aMatches[2])));
                }
            }
            $aResult[$v['Field']] = $aField;
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

        // Constraint name
        $sPattern = '^\s*CONSTRAINT\s+\`([^\`]+)\`';
        // Foreign key
        $sPattern .= '\s+FOREIGN\s+KEY\s+\(\`([^\`]+)\`\)';
        // References
        $sPattern .= '\s+REFERENCES\s+(?:\`([^\`]+)\`\.)?\`([^\`]+)\`\s*\(\`([^\`]+)\`\)';
        // Delete
        $sPattern .= '(?:\s+ON\s+DELETE\s+(CASCADE|SET\sNULL|NO\sACTION|RESTRICT))?';
        // Update
        $sPattern .= '(?:\s+ON\s+UPDATE\s+(CASCADE|SET\sNULL|NO\sACTION|RESTRICT))?';
        $sPattern .= '\,?\s*$';

        $sCreateTable = $this->_getCreateTable();
        $aMatches = null;
        if(preg_match_all('/' . $sPattern . '/imu', $sCreateTable, $aMatches)) {
            foreach ($aMatches[0] as $k => $v) {
                $aResult[] = array(
                    'name'      => $aMatches[1][$k],
                    'field'     => $aMatches[2][$k],
                    'ref_db'    => empty($aMatches[3][$k]) ? null : $aMatches[3][$k],
                    'ref_table' => $aMatches[4][$k],
                    'ref_field' => $aMatches[5][$k],
                    'on_delete' => empty($aMatches[6][$k]) ? 'restrict' : strtolower($aMatches[6][$k]),
                    'on_update' => empty($aMatches[7][$k]) ? 'restrict' : strtolower($aMatches[7][$k]),
                );
            }
        }
        return $aResult;
    } // function getRelations


    /**
     * Return string with Engine of Table
     * @return string
     */
    public function getEngine()
    {
        $sCreateTable = $this->_getCreateTable();
        return null; // ToDo: this
    } // function getEngine
    /**
     * Return string with Create Time of Table
     * @return string
     */
    public function getCreateTime()
    {
        $sCreateTable = $this->_getCreateTable();
        return null; // ToDo: this
    } // function getTableCollation
    /**
     * Return string with Table Collation
     * @return string
     */
    public function getTableCollation()
    {
        $sCreateTable = $this->_getCreateTable();
        return null; // ToDo: this
    } // function getTableCollation
    /**
     * Return string with comment OR null if comment doesn't exist
     * @return null
     */
    public function getComment()
    {
        $sResult = '';
        $aMatches = null;
        if(preg_match('/\sCOMMENT\=\'(.+?)\'/iu', $this->_getCreateTable(), $aMatches)) {
            $sResult = $aMatches[1];
        }
        return $sResult;
    } // function getComment

    // ======== Private/Protected methods ======== \\
    protected function _getFields()
    {
        if (empty($this->aSrcFields)) {
            $this->aSrcFields = $this->oConnection->execute('DESCRIBE `' . $this->sTableName . '`');
        }
        return $this->aSrcFields;
    } // function _getFields

    protected function _getCreateTable()
    {
        if (empty($this->sCreateTable)) {
            $aTmp = $this->oConnection->execute('SHOW CREATE TABLE `' . $this->sTableName . '`');
            $this->sCreateTable = $aTmp[0]['Create Table'];
        }
        return $this->sCreateTable;
    } // function _getCreateTable

} // class \fan\core\service\entity\descriptor\mysql\direct
?>