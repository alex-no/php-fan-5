<?php namespace fan\core\service\email;
 \bootstrap::loadFile('{PROJECT_DIR}/../libraries/PHPMailer/class.phpmailer.php', 1, 3);
/**
 * PHPMailer engine
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
 */
class phpmailer
{
    /**
     * Path to PHPMailer diectory
     * @var string
     */
    protected $sDir;
    /**
     * Main PHPMailer class
     * @var \PHPMailer
     */
    protected $oMail;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sDir = \bootstrap::parsePath('{CORE_DIR}/../libraries/PHPMailer/');
        \bootstrap::getLoader()->registerAutoload(array($this, 'autholoadMailer'), true);

        $this->oMail = new \PHPMailer(false); //ToDo: Use Mailer Exception there

        $this->oMail->Debugoutput = 'error_log';
        $this->oMail->SetLanguage('ru', $this->sDir . 'language/'); //ToDo: Get Language from config
    } // function __construct

    /**
     * Set Facade
     * @param \fan\core\service\email $oFacade
     * @return \fan\core\service\email\phpmailer
     */
    public function setFacade(\fan\core\service\email $oFacade)
    {
        if (empty($this->oFacade)) {
            $this->oFacade = $oFacade;

            $oConfig = $oFacade->getConfig();
            switch ($oConfig->MAILER) {
                case 'SMTP':
                    $this->oMail->IsSMTP();
                    break;
                case 'MAIL':
                    $this->oMail->IsMail();
                    break;
                default:
                    $this->oMail->IsSendmail();
            }

            $sUser = $oConfig->SMTP_USER;
            if (!empty($sUser)) {
                $this->oMail->SMTPAuth = true;
                $this->oMail->Username = $sUser;
                $this->oMail->Password = $oConfig->SMTP_PASSWORD;
            } // check SMTP_USER

            foreach (array(
                'Host'     => 'SMTP_HOST',
                'Port'     => 'PORT',
                'CharSet'  => 'CHARSET',
                'AuthType' => 'AUTH_TYPE',
            ) as $k => $v) {
                if ($oConfig->$v != '') {
                    $this->oMail->$k = $oConfig->$v;
                }
            }

            $this->oMail->SMTPDebug = $oConfig->get('DEBUG', false);
        }

        return $this;
    } // function setFacade

    /**
     * Autholoader for PHPMailer Classes
     * @param string $sClassName
     */
    public function autholoadMailer($sClassName)
    {
        if (in_array($sClassName, array('PHPMailer', 'POP3', 'SMTP'))) {
            $sFileName = $this->sDir . 'class.' . strtolower($sClassName) . '.php';
            if (is_readable($sFileName)) {
                require_once $sFileName;
            }
        }
    } // function autholoadMailer

    /**
     * Set From-parameters
     * @param string $sEmailFrom FROM address
     * @param string $sNameFrom FROM name
     */
    public function setFrom($sEmailFrom, $sNameFrom = '')
    {
        $this->oMail->From = $sEmailFrom;
        if ($sNameFrom) {
            $this->oMail->FromName = $sNameFrom;
        }
    } // function setFromEng

    /**
     * Send Email
     * @param string $sSubj Subject of the email
     * @param string $sBody Body of the email
     * @param string $sEmailTo TO address
     * @param string $sNameTo TO name
     * @param bool $bIsHtml True if the email send as HTML
     * @return string Result of the operation, True if all ok
     */
    public function send($sSubj, $sBody, $sEmailTo, $sNameTo = '', $bIsHtml = false)
    {
        $this->oMail->AddAddress($sEmailTo, $sNameTo);
        $this->oMail->Subject  = trim($sSubj);
        $this->oMail->Body     = $sBody;
        $this->oMail->IsHTML($bIsHtml);
        $bRet = $this->oMail->send();
        if (!empty($this->oMail->ErrorInfo)) {
            $bRet = false;
        }
        if (!$bRet) {
            service('error')->logErrorMessage(
                    empty($this->oMail->ErrorInfo) ? 'Unknown error' : $this->oMail->ErrorInfo,
                    'EMAIL error',
                    $sNameTo . ' &lt;' . $sEmailTo . '&gt;',
                    true,
                    false
            );
        }
        $this->oMail->ClearAddresses();
        $this->oMail->ClearAttachments();
        return $bRet;
    } // function sendEng

    /**
     * Magic method call
     * @param method $sMethod
     * @param array $aArgs
     * @return mixed
     */
    public function __call($sMethod, $aArgs)
    {
        if (method_exists($this->oMail, $sMethod)) {
            return call_user_func_array(array($this->oMail, $sMethod), empty($aArgs) ? array() : $aArgs);
        }
        return null;
    } // function __call
} // class \fan\core\service\email\phpmailer
?>