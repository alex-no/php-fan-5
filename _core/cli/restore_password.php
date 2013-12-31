<?php namespace core\cli;
/**
 * Restore password CLI-tool
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
 * @version of file: 05.004 (31.12.2013)
 */
class restore_password
{
    /**
     * Service config
     * @var \core\service\config
     */
    protected $oConf = null;

    /**
     * Init method
     */
    public function init()
    {
        $this->oConf = service('config');
        $sErrDir = \bootstrap::parsePath($this->oConf->get('log', array('LOG_DIR', 'error')));
        $aTmp    = filter_var(scandir($sErrDir), FILTER_VALIDATE_REGEXP, array(
            'flags'   => FILTER_FORCE_ARRAY,
            'options' => array('regexp' => '/.+\.log$/i')
        ));
        $aFiles = array_diff($aTmp, array(false));
        rsort($aFiles);
        foreach ($aFiles as $v) {
            $aContent = file($sErrDir . '/' . $v);
            for ($i = count($aContent) - 1; $i >= 0; $i--) {
                $aData = explode("\t", $aContent[$i]);
                if ($aData[1] == 'custom' && $this->parceData($aData[2])) {
                    break 2;
                }
            }
        }
    }  // function init

    /**
     * Parce Row Data or log-file
     * @param string $sSrc
     * @return boolean
     */
    public function parceData($sSrc)
    {
        $sSrc = trim(str_replace('\n', "\n", $sSrc));
        $aInf = @unserialize($sSrc);
        if (isset($aInf['header']) && trim($aInf['header']) == 'Error authentication') {
            $aMatches = array();
            if (preg_match('/^[^\"]+\"([^\"]+)\"/', $aInf['main_msg'], $aMatches)) {
                $mIdentifier = $aMatches[1];
            }
            if (preg_match('/^.+?\:\s*(\S+).+?\:\s*(\S+)/s', $aInf['note'], $aMatches)) {
                $sHashe = $aMatches[1];
                $sNS    = $aMatches[2];
            }

            $oUserSpace = $this->oConf->get('user', array('space', $sNS));
            if ($oUserSpace['ENGINE'] == 'entity') {
                echo 'In DB-table of entity "' . $oUserSpace['ENGINE_KEY'] .
                        '" for "' .$mIdentifier . '" set password=' . $sHashe . "\n\n";
            } elseif ($oUserSpace['ENGINE'] == 'config') {
                echo 'In the file "' . $oUserSpace['ENGINE_SOURCE'] . '.ini", section "' .
                        $oUserSpace['ENGINE_KEY'] . '" for "' .$mIdentifier .
                        '" set password=' . $sHashe . "\n\n";
            } else {
                return false;
            }
            return true;
        }
        return false;
    } // function parceData
} // class \project\cli\restore_password
?>