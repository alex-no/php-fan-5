<?php namespace fan\core\service\captcha\text_generator;
/**
 * Siple text geterator for captcha
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class simple extends \fan\core\service\captcha\base
{
    /**
     * Make New text fo captcha
     * @param int $iLength
     * @param string $sType
     * @return \fan\core\service\captcha
     */
    public function makeNewText($iLength, $sType)
    {
        $sResult = '';
        if ($sType == 'char') {
            $aConsonant = array(
                'B', 'C', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'V', 'W', 'X', 'Z', 'TR', 'CR', 'FR', 'DR', 'WR', 'PR', 'TH', 'CH', 'PH', 'ST', 'SL', 'CL'
            );
            $aVowel     = array(
                'A', 'E', 'I', 'O', 'U', 'Y', 'AE', 'OU', 'IO', 'EA', 'OU', 'IA', 'AI'
            );

            $iCcnt = count($aConsonant) - 1;
            $iVcnt = count($aVowel) - 1;
            for ($i = 0; $i < $iLength / 2; $i++) {
                $sResult .= $aConsonant[mt_rand(0, $iCcnt)] . $aVowel[mt_rand(0, $iVcnt)];
            }
        } else {
            for ($i = 0; $i < $iLength; $i++) {
                $sResult .= mt_rand($i == 0 ? 1 : 0, 9);
            }
        }

        return substr($sResult, 0, $iLength);
    } // function makeNewText

} // class \fan\core\service\captcha\text_generator\simple
?>