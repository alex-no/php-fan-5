<?php namespace fan\core\service\matcher\item;
/**
 * Description of handler
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
 *
 * @property string $method
 * @property array $param
 */
class handler extends base
{
    /**
     * Allowed property
     * @var array
     */
    protected $aData = array(
        'key'     => null,
        'method'  => null,
        'param'   => null,
        'ctrlKey' => null, // Config-key used for define current handler
        'mReqKey' => null, // Regexp result, used for define Main Request
    );

    public function offsetGet($sKey)
    {
        if (empty($this->aData['method'])) {
            //$this->init();
        }
        $this->_checkKey($sKey);
        return $this->aData[$sKey];
    }
} // class \fan\core\service\matcher\item\handler
?>