<?php namespace fan\core\service\entity\descriptor;
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
 * @version of file: 05.02.001 (10.03.2014)
 */
abstract class mysql extends \fan\core\service\entity\descriptor
{
    /**
     * Info about fields
     * @var string
     */
    protected $aSrcFields = array();
    /**
     * Info about keys
     * @var string
     */
    protected $aSrcKeys = array();

    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    /**
     * Return Primery Key - name of field(s): strig (if one field) OR array (if several fields)
     * @return string|array
     */
    public function getPrimeryKey()
    {
        foreach ($this->getKeys() as $k0 => $v0) {
            if (strtoupper($k0) == 'PRIMARY') {
                $aPrimary = array_keys($v0['fields']);
                return count($aPrimary) == 1 ? $aPrimary[0] : $aPrimary;
            }
        }
        return null;
    } // function getPrimeryKey

    /**
     * Return 2x array with description of Keys, like:
     * key_name => (type, fields)
     * @return array
     */
    public function getKeys()
    {
        $aResult = array();
        foreach ($this->_getKeys() as $v) {
            if (!isset($aResult[$v['Key_name']])) {
                $aResult[$v['Key_name']] = array(
                    'type'    => $v['Index_type'],
                    'unique'  => !$v['Non_unique'],
                    'packed'  => $v['Packed'],
                    'comment' => $v['Index_comment'],
                    'fields'  => array(),
                );
            }
            $aResult[$v['Key_name']]['fields'][$v['Column_name']] = array(
                'order'       => $v['Seq_in_index'],
                'subPart'     => isset($v['Sub_part']) ? $v['Sub_part'] : null,
                'collation'   => $v['Collation'],
                'null'        => strtoupper($v['Null']) == 'YES',
                'cardinality' => isset($v['Cardinality']) ? $v['Cardinality'] : null,
            );
        }
        return $aResult;
    } // function getKeys

    // ======== Private/Protected methods ======== \\
    /**
     * Reset Default Value
     * @param array $aField
     * @return \fan\core\service\entity\descriptor
     */
    protected function _resetDefaultVal(&$aField)
    {
        if (is_null($aField['default']) && !$aField['null']) {
            if (in_array($aField['type'], array('tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double', 'real', 'bit', 'boolean', 'serial', 'timestamp', 'year'))) {
                $aField['default'] = 0;
            } elseif (in_array($aField['type'], array('char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'binary', 'varbinary', 'tinyblob', 'mediumblob', 'blob', 'longblob'))) {
                $aField['default'] = 0;
            }
        }
        return $this;
    } // function _resetDefaultVal

    protected function _getKeys()
    {
        if (empty($this->aSrcKeys)) {
            $this->aSrcKeys = $this->oConnection->execute('SHOW KEYS FROM `' . $this->sTableName . '`');
        }
        return $this->aSrcKeys;
    } // function _getKeys

} // class \fan\core\service\entity\descriptor\mysql
?>