<?php namespace fan\app\__log_viewer\design;
/**
 * header_main block
 *
 * This file is part PHP-FAN (php-framework of Alexandr Nosov)
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
class header extends \fan\project\block\base
{
    public function init()
    {
        $aEmbeded = $this->getEmbeddedBlocks();
        $aVrts    = $aEmbeded['menu']->getVarieties();
        $sCurDate = date('Y-m-d');
        $aDates   = array();

        foreach ($aVrts as $k) {
            $aDates[$k] = array();

            $sPath = $k == 'bootstrap' ? \bootstrap::getGlobalPath('bootstrap_log') : \bootstrap::parsePath(service('log')->getConfig(array('LOG_DIR', $k)));

            $this->setFileList($aDates[$k], $sPath, '/^(\d{4}\-\d{2}\-\d{2})\_(\d{3})\.log$/');

            if (!isset($aDates[$k][$sCurDate])) {
                $aDates[$k][$sCurDate] = array('000');
            }
        }
        $this->setFileList($aDates['bootstrap'], \bootstrap::getGlobalPath('apache_log'), '/^error_(\d{4}\-\d{2}\-\d{2})\.log$/');

        reset($aDates);
        $sFirstKey = key($aDates);
        $bIsDelete = role('allow_delete');

        $this->_setViewVar('aCurSel', array($sCurDate,  $aDates[$sFirstKey][$sCurDate][0]));
        $this->_setViewVar('aDate', $aDates[$sFirstKey]);
        $this->_setViewVar('isDelete', $bIsDelete);

        $oSes = service('session');
        $sJS  = service('json')->encode($aDates);
        $sJS .= ',\'' . $sCurDate . '\'';
        $sJS .= ',' . ($bIsDelete ? 1 : 0);
        $sJS .= ',\'' . $oSes->getSessionName() . '=' . $oSes->getSessionId() . '\'';
        $this->_getBlock('root')->setEmbedJs('logCtrl.init(' . $sJS . ');');
    } // function init

    public function setFileList(&$aDt, $sPath, $sRegexp)
    {
        if (is_dir($sPath)) {
            $aFiles = scandir($sPath);
            foreach ($aFiles as $v) {
                if(preg_match($sRegexp, $v, $aMatches)) {
                    $sDate = $aMatches[1];
                    if (!isset($aDt[$sDate])) {
                        $aDt[$sDate] = array();
                    }
                    if (isset($aMatches[2])) {
                        $aDt[$sDate][] = $aMatches[2];
                    } elseif (!isset($aDt[$sDate])) {
                        $aDt[$sDate] = array('000');
                    }
                    sort($aDt[$sDate]);
                }
            }
        } else {
            trigger_error('Directory <b>' . $sPath . '</b> isn\'t found.', E_USER_WARNING);
        }
        ksort($aDt);
    } // function setFileList
} // class \fan\app\__log_viewer\design\header
?>