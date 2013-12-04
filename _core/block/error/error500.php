<?php namespace core\block\error;
/**
 * Base abstract block of error 500
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
 * @abstract
 */
abstract class error500 extends \core\block\base
{
    /**
     * Set View Vars
     * @param string $sError
     * @param string $sMessage
     * @param string $sCombiMessage
     */
    public function setViewVars($sError, $sMessage, $sCombiMessage)
    {
        $this->view->error   = $sError;
        $this->view->message = $sMessage;
        if ($this->getViewType() == 'loader') {
            $this->view->setJson('error',   $sError);
            $this->view->setJson('message', $sMessage);
            $this->view->setText($sCombiMessage);
        }
    } // function setViewVars
} // class \core\block\error\error500
?>