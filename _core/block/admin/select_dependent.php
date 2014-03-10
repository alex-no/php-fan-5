<?php namespace fan\core\block\admin;
/**
 * Block admin select dependent
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
class select_dependent extends base
{
    /**
     * Block constructor
     * @param string $sBlockName Block Name
     * @param \core\service\tab $oTab
     * /
    public function __construct($oTab, $sBasicFilePatch)
    {
        parent::__construct($oTab, $sBasicFilePatch);
    } // function __construct */

    /**
     * Init output block data
     */
    public function init()
    {
        service('role')->setSessionRoles('admin', $this->getMeta('login_timeout'));

        $aData = $this->getData();

        $sMethod = 'do_' . $aData['op'];
        $this->setJson(array(
            'op'   => $aData['op'],
            'data' => $this->$sMethod($aData['data'])
        ));

        $this->setText('ok');
    } // function init


    /**
     * Load data for next list of dependet selects
     */
    public function do_load_next_list($aData)
    {
        $nLevel = $aData['level'];
        $aMeta = $this->getMeta(array('level_data', $nLevel));
        return array(
            'hash'  => ge($aMeta['entity'])->getRowsetByParam(array($aMeta['param_key'] => $aData['cval']))->getArrayHash($aMeta['key'], $aMeta['val']),
            'level' => $nLevel,
            'cval'  => $aData['cval'],
        );
    } // function do_load_next_list

} // class \fan\core\block\admin\select_dependent
?>