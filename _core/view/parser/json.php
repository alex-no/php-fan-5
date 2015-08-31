<?php namespace fan\core\view\parser;
/**
 * View parser JSON-type
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
class json extends \fan\core\view\parser
{
    // ======== Static methods ======== \\

    /**
     * Get View-Format
     * @return string
     */
    final static public function getFormat() {
        return 'json';
    } // function getFormat
    /**
     * Get View-Router for block
     * @param \fan\core\block\base $oBlock
     * @return \fan\core\view\router\json
     */
    static public function getRouter(\fan\core\block\base $oBlock) {
        return new \fan\project\view\router\json($oBlock);
    } // function getRouter

    // ======== Main Interface methods ======== \\
   /**
     * Get Final Content Code
     * @return string
     */
    public function getFinalContent()
    {
        $oView = $this->oRootBlock->getView();
        $bUseBase64 = method_exists($oView, 'isUseBase64') && $oView->isUseBase64();
        $sResult = service('json', $bUseBase64)->encode($this->aResult);
        
        $this->_setHeaders($sResult, 'application/json');
        return $sResult;
    } // function getFinalContent

    // ======== Protected methods ======== \\
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

} // class \fan\core\view\parser\json
?>