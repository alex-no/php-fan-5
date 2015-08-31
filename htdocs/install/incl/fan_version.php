<?php
/**
 * Show fan-version
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
 * @version of file: 05.02.007 (31.08.2015)
 */
class fan_version extends base
{
    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\
    public function runCheck()
    {
        return $this->_showFanVersion();
    } // function runCheck

    // ======== Private/Protected methods ======== \\
    /**
     * Show FAN version
     * @return boolean
     */
    protected function _showFanVersion()
    {
        $sFanVer = 'Unknown';
        $sServApp = FAN_CORE_DIR . '/service/application.php';
        if (file_exists($sServApp)) {
            $sApp = file_get_contents($sServApp);
            if (preg_match('/^\s*return\s*\'([^\']+)\'\;\s*$/m', $sApp, $aMatches)) {
                $sFanVer = $aMatches[1];
            }
        }
        $this->aView['sFanVer'] = $sFanVer;
        $this->_parseTemplate('fan_version');
        return true;
    } // function _showFanVersion
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
} // class check_configuration
?>