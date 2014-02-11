<?php namespace app\frontend\main\test;
/**
 * Test zend block
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
class zend extends \project\block\common\simple
{
    /**
     * Init block
     */
    public function init()
    {
        \bootstrap::getLoader()->registerZend2();

        $oDt = new \Zend_Date('21.12.2012');
        $this->view->data1 = $oDt->getDate();

        $this->view->data2 = \Zend_Json::encode(array('aaa' => 1111, 'bbb' => 2222));
    }
} // class \app\frontend\main\test\zend
?>