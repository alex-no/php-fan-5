<?php namespace core\service\log;
/**
 * Parser of log bootstrap-file
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
class parser_bootstrap extends parser_base
{
    /**
     * Key of dir by bootstrap
     * @var string
     */
    protected $sLogDirKey = 'bootstrap_log';

    /**
     * Key of dir by Apache
     * @var string
     */
    protected $sGlobalLogDirKey = 'apache_log';

    /**
     * Type of record is available
     * @var boolean
     */
    protected $bIsType = false;

    /**
     * Data is serialized
     * @var boolean
     */
    protected $bIsSerialized = false;

    /**
     * setFilePath
     */
    public function setFilePath($sVariety, $sFile)
    {
        $sSrcLogFile  = \bootstrap::getGlobalPath($this->sGlobalLogDirKey) . '/error_' . substr($sFile, 0, 10) . '.log';
        $sDestLogFile = \bootstrap::getGlobalPath($this->sLogDirKey) . '/' . $sFile . '.log';
        if (is_file($sSrcLogFile) && (!is_file($sDestLogFile) || is_writable($sDestLogFile))) {
            if (preg_match_all("/\[\S+\s+(\d{2}\:\d{2}\:\d{2})\](.*?)(?=\[\S+\s+(\d{2}\:\d{2}\:\d{2})\]|$)/s", file_get_contents($sSrcLogFile), $aMatches)) {
                foreach ($aMatches[1] as $k => $v) {
                    $sRow = $v . "\t" . addcslashes(preg_replace("/\s*(\n*\r+|\r*\n+)+\s*/s", "\n", trim($aMatches[2][$k])), "\\\t\r\n\0") . "\n";
                    error_log($sRow, 3, $sDestLogFile);
                }
                unlink($sSrcLogFile);
            }
        }
        parent::setFilePath($sVariety, $sFile);
    } // function setFilePath

} // class \core\service\log\parser_bootstrap
?>