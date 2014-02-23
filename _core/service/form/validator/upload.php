<?php namespace core\service\form\validator;
/**
 * Upload file class of validators
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
class upload extends base
{
    /**
     * Form rule - check uploaded file
     * @return boolean
     */
    public function uploadError($mValue, $aData)
    {
        return $mValue['error'] == UPLOAD_ERR_OK || $mValue['error'] == UPLOAD_ERR_NO_FILE;
    } // function uploadError

    /**
     * Check name of uploaded file
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    protected function uploadName($mValue, $aData)
    {
        $aParts = explode('.', $mValue['name']);
        if ((!isset($aData['double_ext']) || !empty($aData['double_ext'])) && count($aParts) > 3) {
            return false;
        }
        if ((!isset($aData['empty_name']) || !empty($aData['empty_name'])) && empty($aParts[0])) {
            return false;
        }
        if (isset($aData['allowed_ext']) && is_array($aData['allowed_ext']) && (!isset($aParts[1]) || !in_array($aParts[1], $aData['allowed_ext']))) {
            return false;
        }
        return true;
    } // function uploadName

    /**
     * Check mime-type of uploaded file
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    protected function uploadMime($mValue, $aData)
    {
        $bResult = true;
        if (isset($aData['allowed_mime'])) {
            $lFinfo = finfo_open(FILEINFO_MIME_TYPE);
            $sMime  = finfo_file($lFinfo, $mValue['tmp_name']);
            finfo_close($lFinfo);
            $bResult = in_array($sMime, $aData['allowed_mime']);
        }
        return $bResult;
    } // function uploadMime
} // class \core\service\form\validator\upload
?>