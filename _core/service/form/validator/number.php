<?php namespace core\service\form\validator;
/**
 * Number class of validators
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
 * @version of file: 05.007 (23.02.2014)
 */
class number extends base
{

    /**
     * Check up if a value is a integer number
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function isInt($mValue, $aData)
    {
        if (!preg_match('/^\-?\d+$/', $mValue)) {
            return false;
        }
        if (isset($aData['min_value']) && $mValue < $aData['min_value']) {
            return false;
        }
        if (isset($aData['max_value']) && $mValue > $aData['max_value']) {
            return false;
        }
        return true;
    } // function isInt

    /**
     * Check up if a value is a real number
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function isFloat($mValue, $aData)
    {
        $mValue = str_replace(',', '.', $mValue);
        if (!is_numeric($mValue)) {
            return false;
        }
        if (isset($aData['min_value']) && $mValue < $aData['min_value'] - 0.000001) {
            return false;
        }
        if (isset($aData['max_value']) && $mValue > $aData['max_value'] + 0.000001) {
            return false;
        }
        return true;
    } // function isFloat

    /**
     * Check up if a value is equal to compare field
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function equalTo($mValue, $aData)
    {
        $mValue2 = null;
        if (isset($aData['compare_field'])) {
            $mValue2 = array_val($this->aFieldValue, $aData['compare_field']);
        }
        return $mValue == $mValue2;
    } // function equalTo

    /**
     * Check up if a value is not equal to compare field
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function notEqualTo($mValue, $aData)
    {
        $mValue2 = null;
        if (isset($aData['compare_field'])) {
            $mValue2 = array_val($this->aFieldValue, $aData['compare_field']);
        }
        return $mValue != $mValue2;
    } // function notEqualTo

    /**
     * Check up if a value is greater then compare field
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function greaterThan($mValue, $aData)
    {
        $mValue2 = null;
        if (isset($aData['compare_field'])) {
            $mValue2 = array_val($this->aFieldValue, $aData['compare_field']);
        }
        if (isset($aData['data_type']) && ($aData['data_type'] == 'DATE' || $aData['data_type'] == 'DATETIME')) {
            $mValue  = \project\service\date::instance($mValue)->get('mysql');
            $mValue2 = \project\service\date::instance($mValue2)->get('mysql');
        }
        return $mValue > $mValue2;
    } // function greaterThan

    /**
     * Check up if a value is lesser then compare field
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function lesserThan($mValue, $aData)
    {
        $mValue2 = null;
        if (isset($aData['compare_field'])) {
            $mValue2 = array_val($this->aFieldValue, $aData['compare_field']);
        }
        if (isset($aData['data_type']) && ($aData['data_type'] == 'DATE' || $aData['data_type'] == 'DATETIME')) {
            $mValue  = \project\service\date::instance($mValue)->get('mysql');
            $mValue2 = \project\service\date::instance($mValue2)->get('mysql');
        }
        return $mValue < $mValue2;
    } // function lesserThan

    /**
     * Check up if a value is greater or equal to compare field
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function greaterOrEqualTo($mValue, $aData)
    {
        $mValue2 = null;
        if (isset($aData['compare_field'])) {
            $mValue2 = array_val($this->aFieldValue, $aData['compare_field']);
        }
        return $mValue >= $mValue2;
    } // function greaterOrEqualTo

    /**
     * Check up if a value is lesser or equal to compare field
     *
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function lesserOrEqualTo($mValue, $aData)
    {
        $mValue2 = null;
        if (isset($aData['compare_field'])) {
            $mValue2 = array_val($this->aFieldValue, $aData['compare_field']);
        }
        return $mValue <= $mValue2;
    } // function lesserOrEqualTo

} // class \core\service\form\validator\number
?>