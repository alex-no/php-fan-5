<?php namespace fan\app\__log_viewer\main;
/**
 * Get log data block
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
 * @version of file: 05.02.001 (10.03.2014)
 */
class get_log_data extends \fan\project\block\loader\base
{

    /**
     * Init block
     */
    public function init()
    {
        $aData = $this->getData();
        $aJson = array();

        @list($sDate, $sNum) = explode('_', $aData['date']);
        $bIsUnique = $aData['gr_idt'];

        $nPageQtt = $nCurPage = 1;
        $oParser = service('log')->getLogParser($aData['vr'], $aData['date']);
        if ($oParser->isData()) {
            do {
                if (@$aData['del'] && role('allow_delete')) {
                    $oParser->deleteRows($aData['del'], $bIsUnique);
                    if (!$oParser->isData()) {
                        $aJson['oper'] = 'redraw';
                        break;
                    }
                    $aData['redraw'] = 1;
                }

                $nTotalQtt = $oParser->getQtt($bIsUnique);
                $nElmPerPage = $this->getMeta('elmPerPage', 10);
                $nPageQtt = ceil($nTotalQtt / $nElmPerPage);
                if ($nPageQtt < 1) {
                    $nPageQtt = 1;
                }
                $nCurPage = @$aData['curPage'] ? $aData['curPage'] : 1;
                if ($nCurPage < 1) {
                    $nCurPage = 1;
                } elseif ($nCurPage > $nPageQtt) {
                    $nCurPage = $nPageQtt;
                }


                if (@$aData['redraw']) {
                    $nOffset = ($nCurPage - 1) * $nElmPerPage;
                    $nQtt    = $nElmPerPage;
                    $aJson['oper'] = 'redraw';
                } else {
                    $nAfterLast = $oParser->checkAfterLast(@$aData['lastRecId'], $bIsUnique);
                    if (is_null($nAfterLast)) {
                        $aJson['oper'] = 'none';
                        break;
                    }
                    if ($nAfterLast >= $nCurPage * $nElmPerPage) {
                        $aJson['oper'] = 'page_only';
                        break;
                    }
                    if ($nAfterLast) {
                        $nOffset = $nAfterLast;
                        $nQtt    = $nElmPerPage - ($nAfterLast) % $nElmPerPage; // ToDo: Check do not skip any elements
                        $aJson['oper'] = 'add';
                    } else {
                        $nOffset = 0;
                        $nQtt    = $nElmPerPage;
                        $aJson['oper'] = 'redraw';
                        $nCurPage = 1;
                    }
                }
                $aRecords = $oParser->getDataArr($nOffset, $nQtt, $bIsUnique);
                if ($aRecords) {
                    $aJson['records'] = $aRecords;
                }
            } while (false);
        }

        $aJson['curPage'] = $nCurPage;
        $aJson['pageQtt'] = $nPageQtt;

        $aJson['vr']      = $aData['vr'];
        $aJson['curDate'] = dateM2L($sDate);
        $aJson['date']    = $aData['date'];

        $this->setJson($aJson);
        $this->setText('ok');
    }
} // class \fan\app\__log_viewer\main\get_log_data
?>