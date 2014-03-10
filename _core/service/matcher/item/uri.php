<?php namespace fan\core\service\matcher\item;
/**
 * Separated URI data
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
 * @property string $scheme
 * @property string $host
 * @property string $user
 * @property string $pass
 * @property string $path
 * @property string $query
 * @property string $fragment
 * @property string $full
 */
class uri extends base
{
    /**
     * Allowed property
     * @var array
     */
    protected $aData = array(
        'scheme'   => null,
        'host'     => null,
        'user'     => null,
        'pass'     => null,
        'path'     => null,
        'query'    => null,
        'fragment' => null,
        'full'     => null,
    );

    public function __toString() {
        return $this->aData['full'];
        //return $this->aData['path'] . (empty($this->aData['query']) ? '' : '?' . $this->aData['query']);
    }
} // class \fan\core\service\matcher\item\uri
?>