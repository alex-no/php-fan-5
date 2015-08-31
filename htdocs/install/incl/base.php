<?php
/**
 * Base abstract class for all parts of install
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
 * @version of file: 05.02.007 (31.08.2015)
 */
abstract class base
{
    /**
     * @var array
     */
    protected static $aInstances = array();
    /**
     * Global static data for localise teplates
     * @var array
     */
    protected static $aLocale = null;
    /**
     * View-data for template
     * @var array
     */
    protected $aView = array();

    public function __construct()
    {
        $this->_setLocale();
    } // function __construct
    // ======== Static methods ======== \\
    /**
     * Run test class
     * @param string $sMethod
     * @return boolean
     */
    public static function run($sMethod = 'runCheck')
    {
        $sClass = function_exists('get_called_class') ? get_called_class() : 'check_configuration';
        if (!isset(self::$aInstances[$sClass])) {
            self::$aInstances[$sClass] = new $sClass();
        }
        return self::$aInstances[$sClass]->$sMethod();
    } // function run
    // ======== Main Interface methods ======== \\
    // ======== Private/Protected methods ======== \\
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    /**
     * Set Locale
     * @param boolean $bForse
     * @return base
     */
    public function _setLocale($bForse = false)
    {
        if (is_null(self::$aLocale) || $bForse) {

            $aLng = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if (!empty($aLng)) {
                $nWeight  = 0;
                $aMatches = array();
                foreach ($aLng as $v) {
                    preg_match('/(\w{2})(?:\-\w{2})?(?:\;q\=([\d\.]+))?/', $v, $aMatches);
                    $nTmp = empty($aMatches[2]) ? 1 : floatval($aMatches[2]);
                    if ($nTmp > $nWeight && file_exists('locale/' . $aMatches[1] . '.php')) {
                        $sLocale = $aMatches[1];
                        $nWeight = $nTmp;
                    }
                }
            }

            if (empty($sLocale)) {
                $sLocale = 'en';
            }

            self::$aLocale = include('locale/' . $sLocale . '.php');
        }
        return $this;
    } // function _setLocale
    /**
     * Parse and output Template
     * @param string $sTplName
     * @return \fan\install\base
     */
    public function _parseTemplate($sTplName)
    {
        extract($this->aView);

        ob_start();
        include 'tpl/' . $sTplName . '.php';
        $sContent = ob_get_contents();
        ob_end_clean();

        $aMatches = array();
        preg_match_all('/\{\#[A-Z_]+\}/', $sContent, $aMatches);
        foreach ($aMatches[0] as $v) {
            $sKey = substr($v, 2, -1);
            if (isset(self::$aLocale[$sKey])) {
                $sContent = str_replace($v, self::$aLocale[$sKey], $sContent);
            }
        }

        echo $sContent;
        return $this;
    } // function _setLocale
} // class \fan\install\base
?>