<?php namespace fan\core\service\form\validator;
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
 * @version of file: 05.02.004 (25.12.2014)
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
        $aFieldData = $this->oFacade->getFieldData($aData['prop_name']);
        if (is_array($aFieldData)) {
            foreach ($aFieldData as $v) {
                if ($v['value'] == $mValue) {
                    return true;
                }
            }
        }
        return false;
    } // function checkSelect

    /**
     * Checks occurrence of the variable in an array
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function inArray($mValue, $aData)
    {
        if (!empty($aData['value'])) {
            return in_array($mValue, $aData['value']);
        }
        if (!empty($aData['link_meta'])) {
            $aArr = $this->getMeta($aData['link_meta']); //ToDo: getMeta
            return is_array($aArr) && in_array($mValue, $aArr);
        }
        if (!empty($aData['method'])) {
            $aCallBack = empty($aData['class']) ? array($aData['class'], $aData['method']) : array($this->oBlock, $aData['method']);//ToDo: $this->oBlock
            if (is_callable($aCallBack)) {
                $aArr = call_user_func($aCallBack);
                return is_array($aArr) && in_array($mValue, $aArr);
            }
        }
        return false;
    } // function inArray

} // class \fan\core\service\form\validator\common
?>