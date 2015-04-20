<?php namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * Application service
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
 * @version of file: 05.02.006 (20.04.2015)
 */
class application extends \fan\core\base\service\single
{
    /**
     * @var string Current applicatin name
     */
    private $sName = null;

    /**
     * Used Application Names
     * @var array
     */
    protected $aUsedNames = null;

    /**
     * Service tab constructor
     * @param boolean $bAllowIni
     */
    protected function __construct($bAllowIni = true)
    {
        parent::__construct($bAllowIni);
        $aSysApp = array('__log_viewer', '__tools');
        $aUsedNames = $this->getConfig('used_names', $aSysApp);
        if (empty($aUsedNames)) {
            throw new fatalException($this, 'Used application names isn\'t set.');
        }
        $this->aUsedNames = adduceToArray($aUsedNames);
        foreach ($aSysApp as $v) {
            if (!in_array($v, $this->aUsedNames)) {
                $this->aUsedNames[] = $v;
            }
        }
    } // function __construct

    /**
     * Set Application name
     * @param type $sName
     * @return \fan\core\service\application
     */
    public function setAppName($sName)
    {
        if (empty($sName)) {
            throw new fatalException($this, 'Application name can\'t be empty.');
        } elseif (in_array($sName, $this->aUsedNames)) {
            if ($this->sName != $sName) {
                $this->sName = $sName;
                $this->_broadcastMessage('setAppName', $sName);
                \bootstrap::getLoader()->defineNewApp($this);
            }
        } else {
            trigger_error('Unknown application name "' . $sName . '".', E_USER_WARNING);
        }
        return $this;
    }

    /**
     * Get Application name
     * @return string
     */
    public function getAppName()
    {
        if (empty($this->sName)) {
            trigger_error('Get Application Name while it isn\'t defined.', E_USER_NOTICE);
        }
        return $this->sName;
    }

    /**
     * Get Default Application name
     * @return string
     */
    public function getDefaultAppName()
    {
        $aUsedApp = $this->getConfig('used_app', array());
        return $this->getConfig('default_app', reset($aUsedApp));
    }

    /**
     * Get project name
     * @return string
     */
    public function getProjectName()
    {
        return $this->getConfig('PROJECT_NAME', 'Name of project is not set');
    } // function getProjectName

    /**
     * Get Core Version of PHP-FAN
     * @return string
     */
    public function getCoreVersion()
    {
        return 'PHP-FAN 05.02.006 (2015-04-20)';
    } // function getCoreVersion
} // class \fan\core\service\application
?>