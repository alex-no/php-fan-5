<?php namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * Captcha manager service
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class captcha extends \fan\core\base\service\multi
{
    /**
     * Service's Instances
     * @var \fan\core\service\captcha[]
     */
    private static $aInstances = array();

    /**
     * Engine object of text generator
     * @var \fan\core\service\captcha\base
     */
    private $oTextGenerator;
    /**
     * Engine object of binary File Maker
     * @var \fan\core\service\captcha\base
     */
    private $oFileMaker;

    /**
     * Form Id
     * @var string
     */
    private $sFormId;

    /**
     * Service's constructor
     * @param string $sFormId
     */
    protected function __construct($sFormId)
    {
        parent::__construct(true);
        $this->sFormId = $sFormId;
        self::$aInstances[$sFormId] = $this;
    } // function __construct

    // ======== Static methods ======== \\
    /**
     *
     * @param string|\fan\core\block\form\usual $mFormId
     * @return \fan\core\service\captcha
     */
    public static function instance($mFormId)
    {
        if (is_object($mFormId) && $mFormId instanceof \fan\core\block\form\usual) {
            $mFormId = $mFormId->getMeta(array('form', 'form_id'));
        } elseif (!is_string($mFormId)) {
            $mFormId = 'main';
        }
        if (!isset(self::$aInstances[$mFormId])) {
            new self($mFormId);
        }
        return self::$aInstances[$mFormId];
    } // function instance

    // ======== Main Interface methods ======== \\
    /**
     * Make New text for captcha
     * @param int $iLength
     * @param string $sType
     * @return \fan\core\service\captcha
     */
    public function makeNewText($iLength = null, $sType = null)
    {
        if (is_null($iLength)) {
            $iLength = $this->getConfig('TEXT_LENGTH', 5);
        }
        if (is_null($sType)) {
            $sType = $this->getConfig('TEXT_TYPE', 'char');
        }
        $sText = $this->_getTextGenerator()->makeNewText($iLength, $sType);
        $this->_getSession()->set($this->sFormId, $sText);
        return $this;
    } // function makeNewText

    /**
     * Get new text for captcha
     * @return string
     */
    public function getText()
    {
        return $this->_getSession()->get($this->sFormId);
    } // function getText

    /**
     * Clear text for captcha from session
     * @return string
     */
    public function clearText()
    {
        return $this->_getSession()->remove($this->sFormId);
    } // function getText

    /**
     * Check captcha
     * @param string $sText checked code
     * @param boolean $bDel delete captcha
     * @return boolean
     */
    public function checkCaptcha($sText, $bDel = true)
    {
        $bRet = strlen($sText) > 0 && strtolower($sText) == strtolower($this->getText());
        if ($bDel) {
            $this->clearText();
        }
        return $bRet;
    } // function checkCaptcha

    /**
     * Get Urn for load captcha
     * @return string
     */
    public function getUrn()
    {
        return $this->getConfig('URN_PREFIX' ,  '/captcha/') . $this->sFormId;
    } // function getUrn

    /**
     * Get Headers for Binary Data of Captcha
     * @return string
     */
    public function getHeaders()
    {
        return $this->_getFileMaker()->getHeaders();
    } // function getHeaders

    /**
     * Get Binary Data of Captcha
     * @return string
     */
    public function getBinaryData()
    {
        return $this->_getFileMaker()->getData();
    } // function getBinaryData

    // ======== Private/Protected methods ======== \\
    /**
     * Object of generator of text is showed on captcha
     * @return object
     */
    protected function _getTextGenerator()
    {
        if (empty($this->oTextGenerator)) {
            $sEngine = $this->getConfig('TEXT_GENERATOR', 'simple');
            $this->oTextGenerator = $this->_getEngine('text_generator\\' . $sEngine);
            $this->oTextGenerator->setConfig($this->oConfig);
        }
        return $this->oTextGenerator;
    } // function _getTextGenerator

    /**
     * Object for make binary file for show a captcha
     * @return object
     */
    protected function _getFileMaker()
    {
        if (empty($this->oFileMaker)) {
            $sEngine = $this->getConfig('FILE_MAKER', 'picture_1');
            $this->oFileMaker = $this->_getEngine('file_maker\\' . $sEngine);
            $this->oFileMaker->setConfig($this->oConfig);
        }
        return $this->oFileMaker;
    } // function _getFileMaker

    /**
     * Get system session
     * @return \fan\core\service\session
     */
    protected function _getSession()
    {
        return service('session', array('captcha', 'service'));
    } // function _getSession

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\
} // class \fan\core\service\captcha
?>