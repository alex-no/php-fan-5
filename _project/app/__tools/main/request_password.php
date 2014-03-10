<?php namespace fan\app\__tools\main;
/**
 * Request password block
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
class request_password extends \fan\project\block\form\usual
{
    /**
     * Current User
     * @var \fan\core\service\user
     */
    protected $oUser;

    /**
     * Init block
     */
    public function init ()
    {
        $this->_parseForm();
    }

    /**
     * Check password and login
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function checkPassword($mValue, $aData)
    {
        $this->oUser = getUser($this->getForm()->getFieldValue($aData['login']));
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

} // class \fan\app\__tools\main\request_password
?>