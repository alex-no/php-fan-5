<?php namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * Email manager service
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
 * @version of file: 05.02.001 (10.03.2014)
 */
class email extends \fan\core\base\service\multi
{

    /**
     * @var array Service's Instances
     */
    private static $aInstances;

    /**
     * @var object Encryption engine
     */
    private $oEngine;

    /**
     * @var string Instance Name
     */
    private $sInstName;

    /**
     * Service's constructor
     */
    protected function __construct($sInstName)
    {
        parent::__construct(false, true);

        $this->sInstName = $sInstName;

        if ($this->isEnabled()) {
            $oConfig = $this->oConfig;
            $this->oEngine = $this->_getEngine($oConfig->get('ENGINE', 'phpmailer'));

            if (!empty($oConfig->FROM_EMAIL)) {
                $oLng = \fan\project\service\locale::instance();
                $sKey = 'FROM_NAME' . ($oLng->isEnabled() ? '_' . $oLng->get() : '');
                if (empty($oConfig->$sKey)) {
                    $sKey = 'FROM_NAME';
                }
                $this->setFrom($oConfig->FROM_EMAIL, $oConfig->get($sKey, ''));
            }

        }
    } // function __construct

    /**
     * Get Service's instance of current service
     * @return \core\service\email
     */
    public static function instance($sInstName = 'default')
    {
        $sClassName = __CLASS__;
        if (is_null($sInstName)) {
            $aConfig = service('config')->get($sClassName);
            $sInstName = @$aConfig['DEFAULT_NAME'] ? $aConfig['DEFAULT_NAME'] : 'DEFAULT_EMAIL_NAME';
        }
        if (!isset(self::$aInstances[$sInstName])) {
            self::$aInstances[$sInstName] = new $sClassName($sInstName);
        }
        return self::$aInstances[$sInstName];
    } // function instance

    /**
     * Set From-parameters
     * @param string $sEmailFrom FROM address
     * @param string $sNameFrom FROM name
     */
    public function setFrom($sEmailFrom, $sNameFrom = '')
    {
        if ($this->isEnabled()) {
            $this->oEngine->setFrom($sEmailFrom, $this->_recodingText($sNameFrom, 'NAME_RECODING'));
        } // check enabling status
    } // function setFrom

    /**
     * Clears all recipients assigned in the TO, CC and BCC
     * array.  Returns void.
     * @return void
     */
    public function clearAllRecipients()
    {
        if ($this->isEnabled()) {
            $this->oEngine->ClearAllRecipients();
        } // check enabling status
    }

    /**
     * Adds a "Cc" address. Note: this function works
     * with the SMTP mailer on win32, not with the "mail"
     * mailer.
     * @param string $address
     * @param string $name
     * @return void
    */
    function addCc($address, $name = '')
    {
        if ($this->isEnabled()) {
            $this->oEngine->AddCC($address, $name);
        } // check enabling status
    }

    /**
     * Adds a "Bcc" address. Note: this function works
     * with the SMTP mailer on win32, not with the "mail"
     * mailer.
     * @param string $address
     * @param string $name
     * @return void
     */
    function addBcc($address, $name = '')
    {
         if ($this->isEnabled()) {
            $this->oEngine->AddBCC($address, $name);
        } // check enabling status
    }

    /**
     * Adds a "Reply-to" address.
     * @param string $address
     * @param string $name
     * @return void
     */
    function addReplyTo($address, $name = '')
    {
        if ($this->isEnabled()) {
            $this->oEngine->AddReplyTo($address, $name);
        } // check enabling status
    }

    /**
     * Adds an attachment from a path on the filesystem.
     * Returns false if the file could not be found
     * or accessed.
     * @param string $path Path to the attachment.
     * @param string $name Overrides the attachment name.
     * @param string $encoding File encoding ("8bit", "7bit", "binary", "base64", and "quoted-printable").
     * @param string $type File extension (MIME) type.
     * @return bool
     */
    function addAttachment($path, $name = '', $encoding = 'base64', $type = 'application/octet-stream')
    {
        if ($this->isEnabled()) {
            $this->oEngine->AddAttachment($path, $name, $encoding, $type);
        }
    }

    /**
     * Send Email
     * @param string $sSubj Subject of the email
     * @param string $sBody Body of the email
     * @param string $sEmailTo TO address
     * @param string $sNameTo TO name
     * @param bool $bIsHtml True if the email send as HTML
     */
    public function send($sSubj, $sBody, $sEmailTo, $sNameTo = '', $bIsHtml = false)
    {
        return $this->isEnabled() ? $this->oEngine->send($this->_recodingText($sSubj, 'SUBJECT_RECODING'), $this->_recodingText($sBody, 'BODY_RECODING'), $sEmailTo, $this->_recodingText($sNameTo, 'NAME_RECODING'), $bIsHtml) : null;
    } // function send

    /**
     * Send Email with using template
     * @param string $sTemplateName Template Name
     * @param array $aPlaceholders Placeholders for the email
     * @param string $sEmailTo TO address
     * @param string $sNameTo TO name
     * @param bool $bIsHtml True if the email send as HTML
     */
    public function sendTemplate($sTemplateName, $aPlaceholders, $sEmailTo, $sNameTo = '', $bIsHtml = true)
    {
        $sFullPath = $this->_checkFilename($sTemplateName);
        if (!$sFullPath) {
            throw new fatalException($this, 'Incorrect path for email template "' . $sTemplateName . '"');
        }
        $oST = service('template');
        //$oST->disableStrip();
        $oTplObj = $oST->get($sFullPath);

        if(!is_array($aPlaceholders)) {
            $aPlaceholders = array();
        }
        if (!isset($aPlaceholders['SUBJECT_SEPARATOR'])) {
            $aPlaceholders['SUBJECT_SEPARATOR'] = md5(microtime());
        }
        foreach ($aPlaceholders as $sKey => $sValue) {
            $oTplObj->assign($sKey, $sValue);
        }

        $sContent = $oTplObj->fetch();

        if(strpos($sContent, $aPlaceholders['SUBJECT_SEPARATOR']) === false) {
            $sBody = $sContent;
            $sSubj = 'No subject';
            trigger_error('Use variable {$SUBJECT_SEPARATOR} in the email-template for separate "Subject and Body"', E_USER_NOTICE);
        } else {
            list($sSubj, $sBody) = explode($aPlaceholders['SUBJECT_SEPARATOR'], $sContent, 2);
        }

        return $this->send($sSubj, trim($sBody), $sEmailTo, $sNameTo, $bIsHtml);
    } // function send_template


    /**
     * Send Email with using template
     * @param string $sTemplateName Template Name
     * @param array $aPlaceholders Placeholders for the email
     * @param string $sEmailTo TO address
     * @param string $sNameTo TO name
     * @param bool $bIsHtml True if the email send as HTML
     */
    public function sendTemplatePlain($sTemplateName, $aPlaceholders, $sEmailTo, $sNameTo = '', $bIsHtml = false)
    {
        $sFullPath = $this->_checkFilename($sTemplateName);
        if (!$sFullPath) {
            return null;
        }
        $sContent = @file_get_contents($sFullPath);
        if (!$sContent) {
            return null;
        }

        foreach ((array) $aPlaceholders as $sKey => $sValue) {
            $sContent = str_replace($sKey, $sValue, $sContent);
        }

        list($sSubj, $sBody) = explode("\n", $sContent, 2);
        return $this->send($sSubj, $sBody, $sEmailTo, $sNameTo, $bIsHtml);
    } // function send_template

    /**
     * Get Name of this Instance
     * @return string
     */
    public function getInstanceName()
    {
        return $this->sInstName;
    } // function getInstanceName

    /**
     * Check file name
     * @param string $sTemplateName Template Name
     * @return string full path
     */
    protected function _checkFilename(&$sTemplateName)
    {
        $aDir = array('');
        if (@$this->aConfig['EMAIL_DIR']) {
            $aDir[] = \bootstrap::parsePath($this->aConfig['EMAIL_DIR']);
        }

        $sEmailExt = $this->aConfig['EMAIL_TPL_EXT'];
        if(substr($sTemplateName, -strlen($sEmailExt)) == $sEmailExt) {
            $sTemplateName = substr($sTemplateName, 0, -strlen($sEmailExt));
        }
        $sLanguage = \fan\project\service\locale::instance()->get();
        foreach ($aDir as $sDir) {
            if ($sLanguage && is_file($sDir . $sTemplateName . '.' . $sLanguage . $sEmailExt)) {
                $sTemplateName .= '.' . $sLanguage . $sEmailExt;
                return $sDir . $sTemplateName;
            } elseif (is_file($sDir . $sTemplateName . $sEmailExt)) {
                $sTemplateName .= $sEmailExt;
                return $sDir . $sTemplateName;
            }
        }
        return null;
    } // function _checkFilename

    /**
     * Valide Email by regular expression
     * @param string $sEmail email address
     * @return boolean TRUE if email is valid
     */
    protected function _validEmail($sEmail) {
        return preg_match('/^[a-z][a-z_0-9.-]+@([a-z0-9-]+\.)+[a-z]{2,4}$/i', $sEmail);
    } // function valid_email

    /**
     * Check file name
     * @param string $sSrc Source text
     * @param string $sCode Encoding key
     * @return string Result text
     */
    protected function _recodingText($sSrc, $sCode) {
        $sTmp = '';
        if ($this->oConfig->get($sCode)) {
            @list($sFromC, $sToC) = explode('=>', $this->oConfig->get($sCode), 2);
            $sToC = trim($sToC);
            $sCharset = $sToC ? $sToC : $this->oConfig->get('CHARSET');
            if (!preg_match('/\/\/\w+$/', $sCharset)) {
                $sCharset .= '//IGNORE';
            }
            $sTmp = iconv(trim($sFromC), $sCharset , $sSrc);
        }
        return empty($sTmp) ? $sSrc : $sTmp;
    } // function _recodingText

} // class \fan\core\service\email
?>