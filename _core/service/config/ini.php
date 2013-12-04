<?php namespace core\service\config;
/**
 * Description of ini
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
class ini extends base
{
    /**
     * File extention
     * @var string
     */
    protected $sFileExtention = 'ini';

    /**
     * Load Source Data
     * @param string $sSrcFilePath
     * @return array
     */
    protected function _loadSourceData($sSrcFilePath)
    {
        $aArrData = parse_ini_file($sSrcFilePath, true);
        $this->_separateByDot($aArrData);
        return $aArrData;
    } // function _loadSourceData

    /**
     * Separate "dot-key" to sub-Branch
     * @param array $aBranch
     */
    protected function _separateByDot(&$aBranch)
    {
        if(is_array($aBranch)) {
              foreach ($aBranch as $k => $v) {
                $r =& $this->_checkDotSeparatedElm($aBranch, $k, $v);
                if (is_string($r) && substr($r, 0, 1) == '[' && substr($r, -1) == ']') {
                    $aTmp = explode(';', substr($r, 1, -1));
                    $r = array_map('trim', $aTmp);
                } else if (is_array($r)) {
                    $this->_separateByDot($r);
                }
            }
        }
    } // function _separateByDot

    /**
     * Check "dot-key" to sub-Branch
     * @param array $aBranch
     * @param mixed $key
     * @param mixed $val
     */
    protected function &_checkDotSeparatedElm(&$aBranch, $key, $val)
    {
        $nDp = strpos($key, '.');
        if($nDp) {
            $key1 = substr($key, 0, $nDp);
            $key2 = substr($key, $nDp + 1);
            $aBranch[$key1][$key2] = $aBranch[$key];
            unset($aBranch[$key]);
            return $this->_checkDotSeparatedElm($aBranch[$key1], $key2, $val);
        }
        return $aBranch[$key];
    } // function _checkDotSeparatedElm
} // class \core\service\config\ini
?>