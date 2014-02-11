<?php namespace app\frontend\main\test;
/**
 * Test user block
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
class user extends \project\block\form\usual
{
    /**
     * Init block
     */
    public function init()
    {
        $this->_parseForm();
        $this->view->user = getUser();
    }

    /**
     * Check password and login
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function checkPassword($mValue, $aData)
    {
        $sLogin = $this->getForm()->getFieldValue($aData['login_field']);
        $this->oUser = getUser($sLogin, 'test_usr');
        if (empty($this->oUser)) {
            return false;
        }
        $this->oUser->checkPassword($mValue);
        return $this->oUser->isValid();
    }

    public function onSubmit()
    {
        if (!empty($this->oUser)) {
            $this->oUser->setCurrent();
        }
    }

} // class \app\frontend\main\test\user
?>