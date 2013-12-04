<?php namespace core\service\user;
use project\exception\service\fatal as fatalException;
/**
 * Parser of log message-file
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
class entity extends base
{
    /**
     * DB row data
     * @var \core\base\model\row
     */
    protected $oRow;

    /**
     * Allowed Get/Set Entity-Methods
     * @var array
     */
    protected $aMethodList = array();

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    /**
     * Convert text of password to text of hash
     * @param string $sPassword
     * @return string
     */
    public function makePasswordHash($sPassword)
    {
        return empty($this->mData['login']) ? '' : md5($this->mData['login'] . $sPassword . $this->oConfig->get('ENGINE_KEY'));
    } // function makePasswordHash

    // ======== Private/Protected methods ======== \\

    /**
     * Save User Data and return TRUE if success
     * @return boolean
     */
    protected function _loadData()
    {
        $oConf = $this->oConfig;
        $this->oRow = null;
        foreach ($oConf->get('IDENTIFYERS') as $v) {
            $oUser = ge($oConf->get('ENGINE_KEY'))->getRowByParam(array($v => $this->mIdentifyer));
            if ($oUser->checkIsLoad()) {
                $this->oRow = $oUser;
                $this->mData = $this->_getEntityData();
                return true;
            }
        }
        return false;
    } // function _loadData

    /**
     * Save User Data and return TRUE if success
     * @return boolean
     */
    protected function _saveData()
    {
        $oRow  = $this->_getRow();
        if (!empty($oRow)) {
            $aMethods = $this->_getMethodList('set');
            if (!empty($aMethods)) {
                foreach ($aMethods as $k => $v) {
                    $oRow->$v($this->mData[$k]);
                }
                $oRow->save();
            }
            return true;
        }
        return false;
    } // function _saveData

    /**
     * Validate User Data before saving
     * @return boolean
     */
    protected function _validateForSave()
    {
        $oRow  = $this->_getRow();
        return !empty($oRow);
    } // function _validateForSave

    /**
     * Get Data by Entity
     * @return array
     */
    protected function _getEntityData()
    {
        $aMethods = $this->_getMethodList('get');
        if (empty($aMethods)) {
            return array();
        }
        foreach (array('id', 'password', 'login', 'roles') as $v) {
            if (!isset($aMethods[$v])) {
                throw new fatalException($this->oFacade, 'Required key "' . $v . '" is not get by method "getMethodList".');
            }
        }

        $aData = array();
        $oRow  = $this->_getRow();
        foreach ($aMethods as $k => $v) {
            $aData[$k] = $oRow->$v();
        }

        return $aData;
    } // function _getEntityData

    /**
     * Get List of Method
     * @param type $sType
     * @return null
     * @throws fatalException
     */
    protected function _getMethodList($sType)
    {
        while (!isset($this->aMethodList[$sType])) {
            $oRow = $this->_getRow();
            if (empty($oRow)) {
                return null;
            }

            $aKeys = $this->_getKeyList();
            if (!method_exists($oRow, 'getMethodList')) {
                throw new fatalException($this->oFacade, 'Method for get User-data "' . get_class($oRow) . '::getMethodList()" isn\'t set. Keys ("' . implode('", "', $aKeys) . '").');
            }

            $this->aMethodList[$sType] = array();
            $aMethods = $oRow->getMethodList($sType);
            if (empty($aMethods)) {
                break;
            }

            foreach ($aKeys as $k) {
                if (isset($aMethods[$k])) {
                    $this->aMethodList[$sType][$k] = $aMethods[$k];
                }
            }
        }

        return $this->aMethodList[$sType];
    } // function _getMethodList

    /**
     * Get Row
     * @return \core\base\model\row|null
     */
    protected function _getRow()
    {
        if (empty($this->oRow)) {
            $sEttKey = $this->oConfig->get('ENGINE_KEY');
            if ($this->bIsNew) {
                $this->oRow = gr($sEttKey);
            } elseif (!empty($this->mData['id'])) {
                $this->oRow = gr($sEttKey, $this->mData['id']);
            } else {
                return null;
            }
        } elseif (!$this->oRow->checkIsLoad() && !$this->bIsNew) {
            return null;
        }
        return $this->oRow;
    } // function _getRow

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

} // \core\service\user\config
?>