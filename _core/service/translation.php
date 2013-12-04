<?php namespace core\service;
/**
 * Description of translation
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
class translation extends \core\base\service\single
{
    /**
     * Combi-message buffer
     * @var array
     */
    private static $aCombiArr = array();
    /**
     * Combi-message language
     * @var string
     */
    private static $sCombiLng = null;

    /**
     * @var \core\service\locale
     */
    private $oLocale;
    /**
     * @var array Editable Language keys
     */
    protected $aEditableLng  = array();

    /**
     * @var array short messages
     */
    protected $aMessages  = array();

    /**
     * @var array short message use tags
     */
    protected $aMsgUseTag = array();
    /**
     * @var array short message tags
     */
    protected $aTags      = array();
    /**
     * @var array short message referers
     */
    protected $aReferers  = array();

    /**
     * @var array List of methods for call in the destructor
     */
    protected $aForCall  = array();

    /**
     * service's constructor
     * @param boolean $bAllowIni
     */
    protected function __construct($bAllowIni = true)
    {
        parent::__construct($bAllowIni);
        $this->oLocale      = \project\service\locale::instance();
        $this->aEditableLng = array_keys($this->oLocale->getAvailableLanguages());
    } // function __construct

    /**
     * Service's destructor
     */
    public function __destruct()
    {
        foreach ($this->aForCall as $m => $v) {
            $this->$m();
        }
    } // function __destruct

    // ======== Static methods ======== \\

    /**
     * Get current language key
     * @return string
     */
    public static function getCombiPart()
    {
        if (!self::$aCombiArr) {
            return null;
        }
        $sKey = array_shift(self::$aCombiArr);
        return \project\service\translation::instance()->getMessage($sKey, self::$sCombiLng);
    } // function getCombiPart

    /**
     * Get text by Current Language
     * @param string $sKey The Key
     * @param string $sLanguage The Language Code
     * @param boolean $bEnableML Enable Multy-Language
     * @return string
     */
    public static function getCombiMessage($aKeyList, $sLng = null, $bEnableML = true)
    {
        if (empty($sLng)) {
            $sLng = \project\service\locale::instance()->getLanguage();
        }
        self::$sCombiLng = $sLng;
        $sKey = array_shift($aKeyList);
        self::$aCombiArr = empty($aKeyList) ? array() : $aKeyList;
        return self::instance()->getMessage($sKey, $sLng);
    } // function getCombiMessage

    // ======== Main Interface methods ======== \\

    /**
     * Set Editable Languageges (for admin-sys)
     * @param string $aEditableLng The Language List
     */
    public function setEditableLng($aEditableLng)
    {
        foreach ($aEditableLng as $sLng) {
            $this->getMessageArr($sLng);
        }
        $this->aEditableLng = $aEditableLng;
    } // function setEditableLng

    /**
     * Get text by Current Language
     * @param string $sKey The Key
     * @param string $sLanguage The Language Code
     * @param boolean $bEnableML Flag: Enable Multi-Language
     */
    public function getMessage($sKey, $sLanguage = null, $bEnableML = true)
    {
        if (!$sKey) {
            return $this->isEnabled() ? null : '';
        }

        $bEnableML = $bEnableML && $this->isEnabled();
        if ($bEnableML) {
            $sKeyF = $this->_formatKey($sKey);
            if (empty($sKeyF)) {
                throw new exception_error_service_fatal($this, 'Incorrect Key. You can\'t create message with key "' . $sKey . '"');
            }

            $aAvailableLng = $this->oLocale->getAvailableLanguages();
            if (!$sLanguage || !isset($aAvailableLng[$sLanguage])) {
                $sLanguage = $this->oLocale->isEnabled() ? $this->oLocale->getLanguage() : $this->oLocale->getDefaultLanguage();
            }

            if (!isset($this->aMessages[$sLanguage])) {
                $this->getMessageArr($sLanguage);
            }
            if (!isset($this->aMessages[$sLanguage][$sKeyF])) {
                $this->_setNewMessage($sKeyF, $sKey);
                $bIsNewMsg = true;
            }

            $sRet  = isset($this->aMessages[$sLanguage][$sKeyF]) ? $this->aMessages[$sLanguage][$sKeyF] : null;
            $isTag = !empty($this->aMsgUseTag[$sKeyF]);
        } else {
            $sRet = $sKey;
            $isTag = strstr($sRet, '{') != false;
        }

        if ($isTag) {
            $aTags = $this->getTagArr();
            if (preg_match_all('/\{([^\}]+)\}/', $sRet, $aMatches)) {
                foreach ($aMatches[1] as $k => $v) {
                    if (isset($aTags[$v])) {
                        //$sRet = str_replace($aMatches[0][$k], $this->getTag($v), $sRet);
                        $sRet = substr_replace($sRet, $this->_getTag($v), strpos($sRet, $aMatches[0][$k]), strlen($aMatches[0][$k]));
                    }
                }
            }
        }

        if ($bEnableML && class_exists('\core\service\tab', false) && \project\service\tab::instance()->isDebugAllowed()) {
            $this->_setReferer($sKeyF);
            $nLen = strpos($sKeyF, '_');
            if ($nLen > 0) {
                $sPref = substr($sKeyF, 0, $nLen);
                if (!$this->getConfig(array('MSG_PREFIX', $sPref), false)) {
                    trigger_error('Incorrect prefix "' . $sKeyF . '" of message key.', E_USER_NOTICE);
                }
            } else {
                trigger_error('Prefix is\'t set for message key "' . $sKeyF . '".', E_USER_NOTICE);
            }
        }
        return $sRet;
    } // function getMessage

    /**
     * Get message for all available languages
     * @return array
     */
    public function getAllMessages()
    {
        foreach ($this->aEditableLng as $sLng) {
            $this->getMessageArr($sLng);
        }
        return $this->aMessages;
    } // function getAllMessages

    /**
     * Get message array
     * @return array
     */
    public function getMessageArr($sLng)
    {
        if (empty($this->aMessages[$sLng])) {
            $sPath = $this->_getFilePath('MESSAGES_PATH', array('{LNG}' => $sLng));
            if (is_readable($sPath)) {
                $this->aMessages[$sLng] = include($sPath);
            } else {
                trigger_error('Undefined message file "' . $sPath . '".', E_USER_WARNING);
                $this->aMessages[$sLng] = array();
            }
        }
        $aRet = $this->aMessages[$sLng];
        foreach ($this->getMsgUseTag() as $k => $v) {
            unset($aRet[$k]);
        }
        return $aRet;
    } // function getMessageArr

    /**
     * Get message use tags array
     * @return array
     */
    public function getMsgUseTag()
    {
        if (empty($this->aMsgUseTag)) {
            $sPath = $this->_getFilePath('USE_TAGS_PATH');
            if (is_readable($sPath)) {
                $this->aMsgUseTag = include($sPath);
            } else {
                trigger_error('Undefined message file "' . $sPath . '".', E_USER_WARNING);
            }
        }
        return $this->aMsgUseTag;
    } // function getMsgUseTag

    /**
     * Edit message array
     * @return array
     */
    public function editMessageArr($sKey, $aData, $bSave = true)
    {
        $this->getAllMessages();

        $bIsTag = false;
        foreach ($aData as $k => $v) {
            $this->aMessages[$k][$sKey] = $v;
            if(strchr($v, '{')) {
                $this->aMsgUseTag[$sKey] = true;
                $bIsTag = true;
                $this->aForCall['_saveMsgUseTag'] = 1;
            }
        }
        if (!$bIsTag && isset($this->aMsgUseTag[$sKey])) {
            unset($this->aMsgUseTag[$sKey]);
            $this->aForCall['_saveMsgUseTag'] = 1;
        }
        if ($bSave) {
            $this->aForCall['_saveMessageArr'] = 1;
        }
    } // function editMessageArr

    /**
     * Delete short message
     * @param string $sKey The Key
     */
    public function deleteMessage($sKey)
    {
        $isDel = false;
        $sKeyF = $this->_formatKey($sKey);
        foreach ($this->aEditableLng as $sLng) {
            $this->getMessageArr($sLng);
            if (isset($this->aMessages[$sLng][$sKeyF])) {
                unset($this->aMessages[$sLng][$sKeyF]);
                $isDel = true;
            }
        }
        if ($isDel) {
            $this->aForCall['_saveMessageArr'] = 1;
            if (isset($this->aMsgUseTag[$sKeyF])) {
                unset($this->aMsgUseTag[$sKeyF]);
                $this->aForCall['_saveMsgUseTag'] = 1;
            }
            $aRef = $this->getRefererArr($sKey);
            if ($aRef) {
                unset($this->aReferers[$sKeyF]);
                $this->aForCall['_saveRefererArr'] = 1;
            }
        }
    } // function deleteMessage

    /**
     * Get tag array
     * @return array
     */
    public function getTagArr()
    {
        if (!$this->aTags) {
            $sPath = $this->_getFilePath('TAGS_PATH');
            $this->aTags = is_readable($sPath) ? include($sPath) : array();
        }
        return $this->aTags;
    } // function getTagArr

    /**
     * Get referer array
     * @return array
     */
    public function getRefererArr($sKey = NULL)
    {
        if (!$this->aReferers) {
            $sPath = $this->_getFilePath('REFERERS_PATH');
            $this->aReferers = is_readable($sPath) ? include($sPath) : array();
            $sLng  = $this->oLocale->getAvailableLanguages();
            if ($sLng == $this->oLocale->getDefaultLanguage()) {
                $this->getMessageArr($sLng);
                foreach ($this->aReferers as $k => $v) {
                    if (!isset($this->aMessages[$sLng][$k])) {
                        unset($this->aReferers[$k]);
                        $this->aForCall['_saveRefererArr'] = 1;
                    }
                }
            }
        }
        return $sKey ? (isset($this->aReferers[$sKey]) ? $this->aReferers[$sKey] : null) : $this->aReferers;
    } // function getRefererArr

    /**
     * Edit message array
     * @return array
     */
    public function editTagArr($sKey, $aData, $bSave = true)
    {
        $this->getMessageArr();
        if (isset($aData['tag'])) {
            $this->aTags[$sKey]['tag'] = $aData['tag'];
            if (strchr($aData['tag'], '{')) {
                $this->aTags[$sKey]['isFunc'] = true;
            } elseif (isset($this->aTags[$sKey]['isFunc'])) {
                unset($this->aTags[$sKey]['isFunc']);
            }
        }
        if (isset($aData['link'])) {
            if ($aData['link']) {
                $this->aTags[$sKey]['link'] = $aData['link'];
            } elseif (isset($this->aTags[$sKey]['link'])) {
                unset($this->aTags[$sKey]['link']);
            }
        }
        if ($bSave) {
            $this->aForCall['_saveTagArr'] = 1;
        }
    } // function editTagArr

    /**
     * Check is Url contain URL
     * @param string $sUrl - Sourse Url
     * @return array modified URL
     */
    public function checkUrlLng($sUrl)
    {
        $sRegExp = '/^((\/\/?)(' . implode('|', array_keys($this->aAvailableLng)) . '))\//';
        if (preg_match($sRegExp, $sUrl, $aMatches)) {
            return $aMatches;
        }
        return null;
    } // function checkUrlLng

    // ======== Private/Protected methods ======== \\

    /**
     * Format message key
     * @param string $sKey The Key
     */
    protected function _formatKey($sKey)
    {
        if (preg_match('/^\w+$/', $sKey)) {
            $sKey = strtoupper($sKey);
        } else {
            $sKey = preg_replace('/\<[^\>]+\>/', ' ', $sKey);
            $sKey = preg_replace('/[^a-zа-я0-9]+/iu', ' ', $sKey);
            $sKey = preg_replace('/\s+/', '_', trim($sKey));
            $sKey = mb_strtoupper($sKey);
            if ($this->getConfig('MSG_KEY_ENGL_ONLY', true)) {
                $sKey = strtr($sKey, array(
                    'А' => 'A',  'Б' => 'B',  'В' => 'V',
                    'Г' => 'G',  'Д' => 'D',  'Е' => 'E', 'Є' => 'Ye',
                    'Ё' => 'Yo', 'Ж' => 'Zh', 'З' => 'Z', 'І' => 'I',
                    'И' => 'I',  'Й' => 'J',  'К' => 'K', 'Ї' => 'Yi',
                    'Л' => 'L',  'М' => 'M',  'Н' => 'N',
                    'О' => 'O',  'П' => 'P',  'Р' => 'R',
                    'С' => 'S',  'Т' => 'T',  'У' => 'U',
                    'Ф' => 'F',  'Х' => 'Kh', 'Ц' => 'Ts',
                    'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shch',
                    'Ь' => '\'', 'Ы' => 'Y',  'Ъ' => '"',
                    'Э' => 'E',  'Ю' => 'Yu', 'Я' => 'Ya',
                ));
                $sKey = iconv('UTF-8', 'ISO-8859-1//IGNORE', $sKey);
            }
        }
        return $sKey;
    } // function _formatKey

    /**
     * Get full path to data-file
     * @param string $sKey The Key
     * @return string
     */
    protected function _getFilePath($sKey, $aRepl = null)
    {
        $sPath = $this->getConfig($sKey);
        if (empty($sPath)) {
            throw new exception_error_service_fatal($this, 'Incorrect Key. Key for path "' . $sKey . '" doesn\'t set');
        }
        if ($aRepl) {
            $sPath = strtr($sPath, $aRepl);
        }
        return \bootstrap::parsePath($sPath);
    } // function _getFilePath

    /**
     * Get tag for short message
     * @param string $sKey The Key
     * @return string
     */
    protected function _getTag($sKey)
    {
        $sRet = $this->aTags[$sKey]['tag'];
        if (@$this->aTags[$sKey]['isFunc'] && preg_match_all('/\{([^\}]+)\}/', $sRet, $aMatches)) {
            foreach ($aMatches[1] as $k => $v) {
                list($sClass, $sMethod, $sArg) = explode(':', $v, 3);
                if (class_exists($sClass) && is_callable(array($sClass, $sMethod))) {
                    $sRet = str_replace($aMatches[0][$k], call_user_func(array($sClass, $sMethod), $sArg), $sRet);
                }
            }
        }
        return $sRet;
    } // function _getTag

    /**
     * Set tag for short message
     * @param string $sKey The Key
     */
    protected function _setReferer($sKey)
    {
        $this->getRefererArr();

        list($sStage, $sPath) = getCurBlockInfo();
        if(!$sPath) {
            $sPath = 'Unknown!';
        }
        if (!isset($this->aReferers[$sKey][$sPath][$sStage])) {
            $oSource = \project\service\matcher::instance()->getItem(0)->source;
            $this->aReferers[$sKey][$sPath][$sStage] = $_SERVER['REQUEST_METHOD'] . ': ' . $oSource;
            $this->aForCall['_saveRefererArr'] = 1;
        }
    } // function _setReferer

    /**
     * Set new short message
     * @param string $sKey The Key
     * @param array $aData
     */
    protected function _setNewMessage($sKeyF, $sKey)
    {
        foreach ($this->aEditableLng as $sLng) {
            $this->getMessageArr($sLng);
            $this->aMessages[$sLng][$sKeyF] = '[' . $sKey . ']';
        }
        $this->aForCall['_saveMessageArr'] = 1;
    } // function _setNewMessage

    /**
     * Save message array
     */
    protected function _saveMessageArr()
    {
        foreach ($this->aEditableLng as $sLng) {
            if (isset ($this->aMessages[$sLng])) {
                ksort($this->aMessages[$sLng]);
                file_put_contents($this->_getFilePath('MESSAGES_PATH', array('{LNG}' => $sLng)), '<?php
/*
 * Short messages array for language "' . $sLng . '"
 */
return ' . var_export($this->aMessages[$sLng], true) . ';
?>');
            } else {
                trigger_error('Unavailable message array for save. Language = "' . $sLng . '"', E_USER_NOTICE);
            }
        }
    } // function _saveMessageArr

    /**
     * Save array of messages used tags
     */
    protected function _saveMsgUseTag()
    {
        ksort($this->aMsgUseTag);
        file_put_contents($this->_getFilePath('USE_TAGS_PATH'), '<?php
/*
 * Array of messages used tags
 */
return ' . var_export($this->aMsgUseTag, true) . ';
?>');
    } // function _saveMsgUseTag

    /**
     * Save tag array
     */
    protected function _saveTagArr()
    {
        ksort($this->aTags);
        file_put_contents($this->_getFilePath('TAGS_PATH'), '<?php
/*
 * Tags array
 */
return ' . var_export($this->aTags, true) . ';
?>');
    } // function _saveTagArr

    /**
     * Save referer array
     */
    protected function _saveRefererArr()
    {
        ksort($this->aReferers);
        file_put_contents($this->_getFilePath('REFERERS_PATH'), '<?php
/*
 * Referer array
 */
return ' . var_export($this->aReferers, true) . ';
?>');
    } // function _saveRefererArr

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\

} // class \core\service\translation
?>