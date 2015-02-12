<?php namespace fan\core\plain;
//use fan\project\exception\plain\fatal as fatalException;
/**
 * Base access for plain files (uploaded to the server) class
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
 * @version of file: 05.02.005 (12.02.2015)
 */

class db_file
{
    /**
     * Handler object
     * @var \fan\core\service\plain
     */
    protected $oHandler;

    /**
     * Plain config object
     * @var \fan\core\service\config\row
     */
    protected $oConfig;

    /**
     * Key of plain controller
     * @var string
     */
    protected $sKey;

    /**
     * @var numeric - Id of Streams
     */
    protected $nStreamId;

    /**
     * @var string - File Path
     */
    protected $sFilePath;
    /**
     * Content for show nail without saving
     * @var string
     */
    protected $sPlainContent = null;
    /**
     * @var string - File type (possible values: 'image', 'flash', 'video', 'other')
     */
    protected $sFileType = null;

    /**
     * @var integer - ID
     */
    protected $mId;

    /**
     * ContentDisposition: true - inline; false - attachment
     * @var
     */
    protected $bPosition = true;

    /**
     * @var
     */
    protected $sApp = null;

    /**
     * Database row
     * @var \fan\core\base\model\row
     */
    protected $oRow = null;

    /**
     * Constructor of Plain controller db_file
     * @param boolean $bAllowIni
     */
    public function __construct(\fan\core\service\plain $oHandler, $sKey)
    {
        $this->oHandler = $oHandler;
        $this->sKey     = $sKey;
    } // function __construct

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    /**
     * Output file content
     */
    public function outputContent()
    {
        if (!empty($this->nStreamId)) {
            if(rewind($this->nStreamId) === false) {
                //ToDo: Save Error Message there
            } elseif(fpassthru($this->nStreamId) === false) {
                //ToDo: Save Error Message there
            }
        } elseif (!empty($this->sFilePath)) {
            readfile($this->sFilePath);
        } else {
            //ToDo: Save Error Message there
            echo 'Error file source';
        }
    } // outputFile

    /**
     * Get File
     * @return array|string
     */
    public function getFile()
    {
        return $this->_prepare()->_init()->_getContent();
    } // getFile

    /**
     * Set Config
     * @param \fan\core\service\config\row $oConfig
     * @return \fan\core\plain\db_file
     */
    public function setConfig(\fan\core\service\config\row $oConfig)
    {
        if (empty($this->oConfig)) {
            $this->oConfig = $oConfig;
        }
        return $this;
    } // setConfig

    /**
     * Get Key
     * @return string
     */
    public function getKey()
    {
        return $this->sKey;
    } // getKey

    // ======== Private/Protected methods ======== \\

    /**
     * Get content OR content outputer
     * @return array|string
     */
    protected function _getContent()
    {
        return empty($this->sPlainContent) ? array($this, 'outputContent') : $this->sPlainContent;
    } // function _getContent

    /**
     * Set properties file output: mId, mPos, sApp, sErrMsg
     * @return \fan\core\plain\db_file
     */
    protected function _prepare()
    {
        $oSR  = \fan\project\service\request::instance();
        $this->mId = $oSR->get('id', 'AGP');
        if (empty($this->mId)) {
            $this->mId = $oSR->get(0, 'A');
        }
        if (!empty($this->mId)) {
            $this->sApp      = $oSR->get('app',  'GPA');
            $this->sFileType = $oSR->get('type', 'GPA');

            $this->oHandler->addHeader('disposition', $oSR->get('pos', 'GPA', true));
            $this->oHandler->addHeader('response', 200);
        }
        return $this;
    } // function _prepare

    /**
     * Init data
     * @return \fan\core\plain\db_file
     */
    protected function _init()
    {
        if (!empty($this->sApp)) {
            \fan\project\service\application::instance()->setAppName($this->sApp);
        }

        $aData = $this->_getFileData();
        if (!empty($aData)) {
            $this->sFilePath = $aData['filePath'];
            foreach (array('contentType', 'filename', 'modified', 'length', 'legthRange', 'cacheLimit') as $k) {
                if (!empty($aData['headers'][$k])) {
                    $this->oHandler->addHeader($k, $aData['headers'][$k]);
                }
            }
        }

        if (class_exists('\fan\core\service\database', false)) {
            \fan\project\service\database::close();
        }
        if (!empty($this->sFilePath) || !empty($this->sPlainContent)) {
            return $this;
        }

        if (!$this->oHandler->isError()) {
            $this->oHandler->setErrorMessage(msg('ERROR_REQUESTED_FILE_IS_NOT_FOUND'));
        }
        return $this;
    } // function _init

    /**
     * Get file data
     * file data or null - if the file is not valid
     * @param boolean $bIdIsEncrypt
     * @return array|null
     */
    protected function _getFileData($bIdIsEncrypt = null)
    {
        if (empty($this->mId)) {
            return null;
        }
        $oCache = \fan\project\service\cache::instance('file_store');
        $aData  = $oCache->get($this->mId);
        if (!empty($aData)) {
            if (!is_readable($aData['filePath'])) {
                $oCache->delete($this->mId);
            } elseif (!empty($aData) && $aData['fileDate'] == filemtime($aData['filePath']) && $aData['headers']['length'] == filesize($aData['filePath'])) {
                return $aData;
            }
        }

        /* @var $oRow \fan\core\model\file_data\row */
        $oRow = $this->_getRow($bIdIsEncrypt);

        if ($oRow) {
            if (!$oRow->checkAccess()) {
                $this->oHandler->setErrorMessage(msg('ERROR_YOU_DO_NOT_HAVE_PERMISSION'), 403);
                return null;
            } else {
                $sFilePath = \bootstrap::parsePath($oRow->getFilePath());
                if (!is_readable($sFilePath)) {
                    return null;
                }
                $aData = array(
                    'filePath' => $sFilePath,
                    'fileDate' => filemtime($sFilePath),
                    'headers' => array(
                        'contentType' => $oRow->get_mime_type(),
                        'filename'    => $oRow->get_src_name(),
                        'length'      => filesize($sFilePath),
                        'legthRange'  => 'bytes',
                        'modified'    => strtotime($oRow->get_update_date()),
                    ),
                );
                // ToDo: Save cache only if file do not need to check access
                $oCache->set($this->mId, $aData, true);
                return $aData;
            }
        }
        return null;
    } // function _getFileData

    /**
     * Get Entity entity_file_data
     * Return NULL if the file is not valid
     * @return \fan\core\model\file_data\row|null
     */
    protected function _getRow($bIdIsEncrypt = null)
    {
        if (is_null($this->oRow)) {
            $this->oRow = gr(service('entity')->getFileNsSuffix() . 'file_data');
            if (is_null($bIdIsEncrypt)) {
                $this->oRow->loadById($this->mId, false); // !is_numeric($this->mId)
                if (!$this->oRow->checkIsLoad()) {
                    $this->oRow->loadById($this->mId, true);
                }
            } else {
                $this->oRow->loadById($this->mId, $bIdIsEncrypt);
            }
        }
        return $this->oRow->checkIsLoad() && (is_null($this->sFileType) || $this->oRow->get_file_type() == $this->sFileType) ? $this->oRow : null;
    } // function _getRow

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

} // class \fan\core\plain\db_file
?>