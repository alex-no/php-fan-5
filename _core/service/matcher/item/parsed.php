<?php namespace fan\core\service\matcher\item;
/**
 * Description of parsed
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
 * @property string $app_name
 * @property string $app_prefix
 * @property string $language
 * @property string $src_path
 * @property string $query
 * @property array $main_request
 * @property array $add_request
 * @property array $both_request
 * @property string $class
 * @property string $file
 * @property string $urn
 */
class parsed extends base
{
    /**
     * Allowed property
     * @var array
     */
    protected $aData = array(
        'app_name'     => null,
        'app_prefix'   => null,
        'language'     => null,
        'src_path'     => null,
        'query'        => null,
        'main_request' => null,
        'add_request'  => null,
        'both_request' => null,
        'class'        => null,
        'file'         => null,
        'urn'          => null,
    );

    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\

    public function __toString() {
        return $this->aData['urn'];
    }
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\

    public function getMainRequest()
    {
        if (is_null($this->aData['main_request'])) {
            $this->oItem->parseRequest();
        }
        return $this->aData['main_request'];
    }

    public function getAddRequest()
    {
        if (is_null($this->aData['add_request'])) {
            $this->oItem->parseRequest();
        }
        return $this->aData['add_request'];
    }

    public function getBothRequest()
    {
        if (is_null($this->aData['both_request'])) {
            $this->aData['both_request'] = array_merge($this['main_request'], $this['add_request']);
        }
        return $this->aData['both_request'];
    }

    public function getClass()
    {
        if (is_null($this->aData['class'])) {
            $aMainRequest = $this['main_request'];
            if (empty($aMainRequest)) {
                $this->aData['class'] = '';
            } else {
                $this->aData['class']  = '\\fan\\app\\' . $this->aData['app_name'];
                $this->aData['class'] .= '\\' . $this->_getConfig('main_block_dir', 'main') . '\\';
                $this->aData['class'] .= implode('\\', $aMainRequest);
            }
        }
        return $this->aData['class'];
    }

    public function getFile()
    {
        if (is_null($this->aData['file'])) {
            $aMainRequest = $this['main_request'];
            if (empty($aMainRequest)) {
                $this->aData['file'] = '';
            } else {
                $this->aData['file'] = rtrim(\bootstrap::getLoader()->main, '\\/');
                $this->aData['file'] .= '/' . implode('/', $aMainRequest) . '.php';
            }
        }
        return $this->aData['file'];
    }

    public function getUrn()
    {
        $sUrn =& $this->aData['urn'];
        if (is_null($sUrn)) {

            $sUrn  = '/';
            // ToDo: Possibility to switch positions "language" and "app_prefix"
            if (!empty($this->aData['language'])) {
                $sUrn  .= $this->aData['language'] . '/';
            }
            if (!empty($this->aData['app_prefix'])) {
                $sUrn  .= trim($this->aData['app_prefix'], '/') . '/';
            }

            $sUrn  .= implode('/', $this->aData['both_request']);
        }
        return $sUrn;
    }

    /**
     * Return all data
     * @return array
     */
    public function toArray()
    {
        return $this->aData;
    } // function toArray

    // ======== Private/Protected methods ======== \\

} // class \fan\core\service\matcher\item\parsed
?>