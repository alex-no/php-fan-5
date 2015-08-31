<?php namespace core\service\cache\wrapper;
/**
 * Cache for save data of file class
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
 * @version of file: 05.02.007 (31.08.2015)
 */

class file_data
{
    /**
     * Row ID
     * @var integer
     */
    protected $nId;
    /**
     * Is Encrypted ID
     * @var boolean
     */
    protected $bIdIsEncrypt;
    /**
     * Database row
     * @var \core\base\model\file_data\row
     */
    protected $oRow = null;
    /**
     * Data
     * @var array
     */
    protected $aData = null;
    /**
     * Cache
     * @var \core\service\cache
     */
    protected $oCache = null;

    /**
     * Constructor of Plain controller file_data
     * @param \core\base\model\file_data\row|integer $mRowData
     */
    public function __construct($mRowData, $bIdIsEncrypt = null)
    {
        if (is_integer($mRowData)) {
            $this->nId          = $mRowData;
            $this->bIdIsEncrypt = $bIdIsEncrypt;
        } elseif (is_object($mRowData) && $mRowData instanceof \core\base\model\file_data\row) {
            $this->oRow = $mRowData;
            $this->nId  = $mRowData->getId();
        } else {
            throw new \project\exception\error500('Incorrect call of \core\service\cache\wrapper\file_data');
        }
    } // function __construct

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    /**
     * Get file data
     * file data or null - if the file is not valid
     * @return array|null
     */
    public function getFileData()
    {
        while (empty($this->aData)) {
            $this->aData  = $this->_getCache()->get($this->nId);
            if (!empty($this->aData)) { // && $this->aData['fileDate'] == filemtime($this->aData['filePath']) && $this->aData['headers']['length'] == filesize($this->aData['filePath'])
                break;
            }

            $this->reset();
        }
        return $this->aData;
    } // function getFileData

    public function reset()
    {
        $oRow = $this->_getRow();

        if ($oRow) {
            if (!$oRow->checkAccess()) {
                // ToDo: Additional operation there
                return false;
            } else {
                $sFilePath = \bootstrap::parsePath($oRow->getFilePath());
                $this->aData = array(
                    'filePath' => $sFilePath,
                    'fileDate' => filemtime($sFilePath),
                    'rowData'  => $oRow->toArray(),
                );
                // ToDo: Save cache only if file do not need to check access
                $this->_getCache()->set($this->nId, $this->aData, true);
            }
        }
        return true;
    } // function getFileData

    // ======== Private/Protected methods ======== \\

    /**
     * Get Entity entity_file_data
     * Return NULL if the file is not valid
     * @return \core\model\file_data\row|null
     */
    protected function _getRow()
    {
        if (is_null($this->oRow)) {
            $this->oRow = gr(service('entity')->getFileNsSuffix() . 'file_data');
            if (is_null($this->bIdIsEncrypt)) {
                $this->oRow->loadById($this->nId, false); // !is_numeric($this->mId)
                if (!$this->oRow->checkIsLoad()) {
                    $this->oRow->loadById($this->nId, true);
                }
            } else {
                $this->oRow->loadById($this->nId, $this->bIdIsEncrypt);
            }
        }
        return $this->oRow->checkIsLoad() ? $this->oRow : null;
    } // function _getRow

    /**
     * Get Entity entity_file_data
     * @return \core\model\file_data\row|null
     */
    protected function _getCache()
    {
        if (is_null($this->oCache)) {
            $this->oCache = \project\service\cache::instance('file_store');
        }
        return $this->oCache;
    } // function _getCache

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

} // class \core\service\cache\wrapper\file_data
?>