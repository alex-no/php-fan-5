<?php
/**
 * Check configuration of PHP
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
class check_configuration extends base
{
    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\
    public function runCheck()
    {
        if (!$this->_checkPhpVersion()) {
            return false;
        }

        return $this->_checkPhpModules();
    } // function runCheck

    // ======== Private/Protected methods ======== \\
    /**
     * Check PHP version
     * @return boolean
     */
    protected function _checkPhpVersion()
    {
        $nVerValue = phpversion();
        //$nVerType  = version_compare($nVerValue, '5.4') > 0 ? 1 : (version_compare($nVerValue, '5.3') < 0 ? -1 : 0);
        $nVerType  = version_compare($nVerValue, '5.3') >= 0 ? 1 : -1;
        $this->aView['nVerType']  = $nVerType;
        $this->aView['nVerValue'] = $nVerValue;
        $this->_parseTemplate('php_version');
        return $nVerType >= 0;
    } // function _checkPhpVersion
    /**
     * Check PHP version
     * @return boolean
     */
    protected function _checkPhpModules()
    {
        $aRequired    = array('SPL', 'Reflection', 'pcre', 'standard', 'json', 'session', 'iconv', 'filter', 'date');
        $aRecommended = array('memcache', 'mbstring', 'mysql', 'mysqli', 'libxml', 'dom', 'SimpleXML', 'xml', 'xmlreader', 'xmlwriter', 'gd', 'exif', 'curl', 'soap');

        $aModules = get_loaded_extensions();

        $aUseRequired    = array();
        $bAllRequired    = true;
        $aUseRecommended = array();
        $bAllRecommended = true;

        foreach ($aRequired as $v) {
            if (in_array($v, $aModules)) {
                $aUseRequired[$v] = 'correct';
            } else {
                $aUseRequired[$v] = 'incorrect';
                $bAllRequired = false;
            }
        }
        foreach ($aRecommended as $v) {
            if (in_array($v, $aModules)) {
                $aUseRecommended[$v] = 'correct';
            } else {
                $aUseRecommended[$v] = 'need';
                $bAllRecommended = false;
            }
        }

        $this->aView['aUseRequired']    = $aUseRequired;
        $this->aView['bAllRequired']    = $bAllRequired;
        $this->aView['aUseRecommended'] = $aUseRecommended;
        $this->aView['bAllRecommended'] = $bAllRecommended;
        $this->_parseTemplate('php_modules');

        return $bAllRequired;
    } // function _checkPhpModules
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
} // class check_configuration
?>