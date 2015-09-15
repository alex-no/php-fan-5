<?php namespace fan\core\service\obfuscator;
use fan\project\exception\service\fatal as fatalException;
/**
 * Description of obfuscator-engine base
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
 * @version of file: 05.02.008 (15.09.2015)
 */
abstract class base
{
    /**
     * Facade of service
     * @var \fan\core\service\obfuscator
     */
    protected $oFacade;

    /**
     * Drop comments like "/ * ... * /" and "// ..."
     * @var boolean
     */
    protected $bDropComments = true;
    /**
     * Drop "end of row" like "\n" and "\r". If this option is set comments like "// ..." will be dropped anyway
     * @var boolean
     */
    protected $bDropEndRow = true;
    /**
     * Replace several spaces to one
     * @var boolean
     */
    protected $bSpacesToOne = true;

    /**
     * Constructor of obfuscator-engine
     */
    public function __construct()
    {
    } // function __construct

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\

    /**
     * Set Facade
     * @param \fan\core\service\obfuscator $oFacade
     * @return \fan\core\service\obfuscator\base
     */
    public function setFacade(\fan\core\service\obfuscator $oFacade)
    {
        if (empty($this->oFacade)) {
            $this->oFacade = $oFacade;

            $oConfig = $oFacade->getConfig('option', array());
            $aKeys = array(
                'bDropComments' => 'DROP_COMMENTS',
                'bDropEndRow'   => 'DROP_END_ROW',
                'bSpacesToOne'  => 'SPACES_TO_ONE',
            );
            foreach ($aKeys as $k => $v) {
                $this->$k = isset($oConfig[$v]) ? (bool)$oConfig[$v] : true;
            }
        }
        return $this;
    } // function setFacade

    /**
     * Obfuscate string of Content
     * @param string $sText
     * @return string
     */
    abstract public function obfuscate($sText);

    // ======== Private/Protected methods ======== \\

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\

} // class \fan\core\service\obfuscator\base
?>