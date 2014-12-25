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
 * @version of file: 05.02.004 (25.12.2014)
 */
class phpmailer extends \PHPMailer
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $sDir = \bootstrap::parsePath('{CORE_DIR}/../libraries/PHPMailer');

        $this->PluginDir = $sDir . '/';
        $this->SetLanguage('en', $sDir . '/language/');
    } // function __construct

    /**
     * Set Facade
     * @param \fan\core\service\email $oFacade
     * @return \fan\core\service\email\phpmailer
     */
    public function setFacade(\fan\core\base\service $oFacade)
    {
        if (empty($this->oFacade)) {
            $this->oFacade = $oFacade;

            $oConfig = $oFacade->getConfig();
            switch ($oConfig->MAILER) {
                case 'SMTP':
                    $this->IsSMTP();
                    break;
                case 'MAIL':
                    $this->IsMail();
                    break;
                default:
                    $this->IsSendmail();
            }

            if ($oConfig->SMTP_HOST) {
                $this->Host = $oConfig->SMTP_HOST;
            }

            if ($oConfig->PORT) {
                $this->Port = $oConfig->PORT;
            }

            if ($oConfig->CHARSET) {
                $this->CharSet = $oConfig->CHARSET;
            }

            $sUser = $oConfig->SMTP_USER;
            if ($sUser) {
                $this->SMTPAuth = true;
                $this->Username = $sUser;
                $this->Password = $oConfig->SMTP_PASSWORD;
            } // check SMTP_USER

            if ($oConfig->AUTH_TYPE) {
                $this->AuthType = $oConfig->AUTH_TYPE;
            }

            $this->SMTPDebug = $oConfig->get('DEBUG', false);
        }

        return $this;
    } // function setFacade

    /**
     * Set From-parameters
     * @param string $sEmailFrom FROM address
     * @param string $sNameFrom FROM name
     */
    public function setFromEng($sEmailFrom, $sNameFrom = '')
    {
        $this->From = $sEmailFrom;
        if ($sNameFrom) {
            $this->FromName = $sNameFrom;
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
    public function sendEng($sSubj, $sBody, $sEmailTo, $sNameTo = '', $bIsHtml = false)
    {
        $this->AddAddress($sEmailTo, $sNameTo);
        $this->Subject  = trim($sSubj);
        $this->Body     = $sBody;
        $this->IsHTML($bIsHtml);
        $bRet = parent::Send();
        if (!empty($this->ErrorInfo)) {
            trigger_error('<b>EMAIL error:</b> ' . $this->ErrorInfo . '<br/>' . $sNameTo . ' &lt;' . $sEmailTo . '&gt;', E_USER_WARNING);
        }
        $this->ClearAddresses();
        $this->ClearAttachments();
        return $bRet;
    } // function sendEng
} // class \fan\core\service\email\phpmailer
?>