<?php namespace core\service\form\validator;
/**
 * Common class of validators
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
class select extends base
{

    /**
     * Check up a value from select and radio
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function checkSelect($mValue, $aData)
    {
        $sProp = $aData['prop_name'];
        if (is_array($this->$sProp) && $this->$sProp) {
            foreach ($this->$sProp as $e) {
                if ($e->getId() == $mValue) {
                    return true;
                }
            }
        }
        return false;
    } // function checkSelect

    /**
     * Check up a value from select and radio
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function checkSelectArray($mValue, $aData)
    {
        $sProp = $aData['prop_name'];
        if (is_array($this->$sProp) && $this->$sProp) {
            foreach ($this->$sProp as $e) {
                if ($e['value'] == $mValue) {
                    return true;
                }
            }
        }
        return false;
    } // function checkSelectArray

    /**
     * Check up a value from select and radio
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function checkSelectMeta($mValue, $aData)
    {
        $aProp = $this->getMeta($aData['prop_name']);
        if (is_array($aProp) && $aProp) {
            foreach ($aProp as $k => $e) {
                if ($k == $mValue) {
                    return true;
                }
            }
        }
        return false;
    } // function checkSelectMeta

    /**
     * Check up a value from select and radio
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function checkSelectData($mValue, $aData)
    {
        $aProp = $this->getMeta(array('form', 'fields', $aData['prop_name'], 'data'));
        if (is_array($aProp) && $aProp) {
            foreach ($aProp as $e) {
                if ($e['value'] == $mValue) {
                    return true;
                }
            }
        }
        return false;
    } // function checkSelectData

} // class \core\service\form\validator\common
?>