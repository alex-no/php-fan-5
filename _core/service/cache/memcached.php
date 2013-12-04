<?php namespace core\service\cache;
use project\exception\service\fatal as fatalException;
/**
 * ADOdb wrapper for template engine
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
class memcached extends base
{
    /**
     * Method for load data from cache
     * Must define property $this->mData and $this->aMetaData
     */
    protected function _loadData($bLoadMetaOnly)
    {

    }

    /**
     * Method for save data to cache
     * Must define property $this->mData and $this->aMetaData
     */
    protected function _saveData()
    {

    }

    /**
     * Delete cached data
     */
    protected function _deleteData()
    {

    }

} // class \core\service\cache\memcached
?>