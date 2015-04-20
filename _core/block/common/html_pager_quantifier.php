<?php namespace fan\core\block\common;
/**
 * Pager quantifier class
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
class html_pager_quantifier extends \fan\core\block\form\usual
{
    /**
     * Init pager quantifier
     */
    public function init()
    {
        $aGurrentGet = array();
        $aGet = service('request')->getAll('G', array());
        foreach ($aGet as $k => $v) {
            if ($k != 'pager_quantifier') {
                $aGurrentGet[$k] = $v;
            }
        }
        $this->_setViewVar('aGurrentGet', $aGurrentGet);

        $this->_parseForm(true, !empty($aGet['pager_quantifier']));
    } // function init

    protected function onSubmit()
    {
        $this->oContainer->setElmPerPage($this->aFieldValue['pager_quantifier']);
    } // function onSubmit

    /**
     * Get Dynamic Meta-data
     * @param array $aMeta Allow change meta in the parent chain
     * @return array
     */
    public function getDynamicMeta($aMeta)
    {
        $aSrcData = $this->oContainer->getMeta('quantifier', array());

        $aFormData = array();

        $aMetaFormData = explode(',', $aSrcData['values']);
        foreach ($aMetaFormData as $v) {
            $v = trim($v);
            $aFormData[] = array(
                'value' => $v,
                'text'  => $v,
            );
        }

        return array(
            'form'  => array(
                'fields'    => array(
                    'pager_quantifier'  => array(
                        'label'         => $aSrcData['label'],
                        'data'          => $aFormData,
                        'default_value' => $this->oContainer->getElmPerPage(),
                    ),
                ),
            ),
        );
    } // function getDynamicMeta

    /**
     * Get Meta-data from parent classes
     * @param string $sMetaFile path to Meta-File set from class-file
     */
    public function getParentMeta(){

        $aFileMeta = $this->readMetaFile(substr(__FILE__, 0, -3) . 'meta.php');
        return array_merge_recursive_alt(parent::getParentMeta(), $aFileMeta);

    } // function getParentMeta
} // class \fan\core\block\common\html_pager_quantifier
?>