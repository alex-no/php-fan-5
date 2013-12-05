<?php namespace app\__log_viewer\main;
/**
 * index block
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
 * @version of file: 02.002
 */
class get_trace extends \core\block\loader\base
{

    public function init()
    {
        $aData = $this->getData();
        $aJson = array();

        $oParser = service('log')->getLogParser($aData['vr'], $aData['date']);
        $aTrace  = $oParser->getTrace($aData['idRecord']);

        if ($aTrace) {
            $aJson['trace'] = $aTrace;
        }

        $aJson['idHtml'] = $aData['idHtml'];

        $this->setJson($aJson);
        $this->setText('ok');
    }

} // class \app\__log_viewer\main\get_trace
?>