<?php namespace app\frontend\main\test;
/**
 * Test form block
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
class form extends \project\block\common\simple
{
    /**
     * Init block data
     */
    public function init()
    {
        $this->view->meta_example = realpath(\bootstrap::parsePath('{PROJECT}/../doc/meta_example/form.meta.php'));

        $aPaths = \project\service\reflector::instance()->getParentPaths($this->_getBlock('test_form'));
        $this->view->form_block = pathinfo(reset($aPaths));
        //$this->view->form_block = realpath(end($aPaths));
    } // function init
} // class \app\frontend\main\test\form
?>