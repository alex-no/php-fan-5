<?php namespace fan\core\block\loader;
use fan\core\exception\block\fatal as exception_block_fatal;
/**
 * Base class for loader form validation block
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
 * @version of file: 05.02.006 (20.04.2015)
 * @abstract
 */
abstract class loader_form_validation extends base
{
    /**
     * Init output block data
     */
    public function init()
    {
        $aData = $this->getData();
        $this->setJson($aData);
        if (empty($aData['field'])) {
            throw new exception_block_fatal($this, 'Method name for check field isn\'t set.');
        } elseif (method_exists($this, 'check_' . $aData['field'])) {
            $sRet = $this->{'check_' . $aData['field']}($aData['value'], $this->getMeta(array('err_message', $aData['field']), ''));
            $this->setText(is_null($sRet) ? 'ok' : $sRet);
        } else {
            throw new exception_block_fatal($this, 'Method "check_' .  $aData['field'] . '" isn\'t found.');
        }
    } // function init

} // class \fan\core\block\loader\loader_form_validation
?>