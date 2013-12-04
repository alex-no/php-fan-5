<?php namespace core\block\admin;
/**
 * Admin table data class for loader block
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
 * @version of file: 05.001 (29.09.2011)
 */
class data_table extends data
{
    /**
     * Form error
     * @var array
     */
    protected $aInsEtt = array();


    /**
     * Validate Data
     */
    public function validateData(&$aEdit, &$aInsert)
    {
        $aTmpErr = array();
        $this->validateDataOnce($aEdit, 'edit', $aTmpErr);
        $this->validateDataOnce($aInsert, 'ins', $aTmpErr);
        if ($aTmpErr) {
            $aErrMsg = array();
            foreach ($aTmpErr as $v) {
                $aErrMsg[] = implode('; ', $v);
            }
            $this->aErrorMsg[] = implode('\n', $aErrMsg);
            return false;
        }
        return true;
    } // function validateData

    /**
     * Validate Data
     */
    protected function validateDataOnce(&$aData, $sType, &$aTmpErr)
    {
        foreach ($aData as $nId => &$v) {
            $aErr = $this->doValidate($v, $sType, $nId);
            foreach ($aErr as $fld => $err) {
                if (!isset($aTmpErr[$fld]) || !in_array($err, $aTmpErr[$fld])) {
                    if (!isset($aTmpErr[$fld])) {
                        $aTmpErr[$fld] = array();
                    }
                    $aTmpErr[$fld][] = $err;
                }
            }
        }
    } // function validateDataOnce

    /**
     * Parse changed/inserted Data
     * @param array $aEdit
     * @param array $aInsert
     * @return \core\block\admin\data
     */
    public function parseData($aEdit, $aInsert)
    {
        $sEttAcces = $this->getMeta('check_access4edit');
        $aFields   = $this->getMeta(array('table_struct', 'columns'));
        foreach ($aEdit as $id => $v){
            $oEtt = $this->loadEntityById($id);
            if ($sEttAcces && !$oEtt->$sEttAcces($v)) {
                $this->bIsError = true;
                return;
            }
            $this->saveRow($oEtt, $v, $aFields);
            if (!$this->checkDBerror($oEtt, 'Can\'t update row ' . $id . ': ')) {
                return;
            }
        }
        $aConvId = $this->getMeta(array('addParam', 'convId'), array());
        if ($this->getMeta(array('table_struct', 'newData'), true) || $aConvId) {
            $aAddFields = array_keys(adduceToArray($this->getMeta(array('addParam', 'default_val'), array())));
            if (!empty($aConvId[1])) {
                $aAddFields[] = $aConvId[1];
            }
            foreach ($aInsert as $ik => $v){
                if (!$aConvId || isset($v[@$aConvId[1]])) {
                    $oEtt = gr($this->getMeta('entity'));
                    foreach ($this->getMeta(array('addParam', 'default_val'), array()) as $k => $add) {
                        if (!array_key_exists($k, $v)) {
                            $v[$k] = $add;
                        }
                    }
                    $this->saveRow($oEtt, $v, $aFields, $aAddFields);
                    if (!$this->checkDBerror($oEtt, 'Can\'t insert data: ')) {
                        return;
                    }
                    $this->aInsEtt[$ik] = $oEtt;
                }
            }
        }
        return $this;
    } // function parseData

    /**
     * Parse delete Data
     */
    public function deleteData($aDel)
    {
        if ($this->getMeta(array('table_struct', 'showDel'), true)) {
            $sEttAcces = $this->getMeta('check_access4delete');
            foreach ($aDel as $id => $v){
                $oEtt = $this->loadEntityById($id);;
                if ($sEttAcces && !$oEtt->$sEttAcces($v)) {
                    $this->bIsError = true;
                    return;
                }
                $oEtt->delete();
                if (!$this->checkDBerror($oEtt, 'Can\'t delete row ' . $id . ': ')) {
                    return;
                }
            }
        }
    } // function deleteData


    /**
     * Init Template Vars
     */
    public function initTplVar()
    {
        foreach (array('isHead' => true, 'showId' => true, 'showDel' => true) as $k =>$v) {
            $this->setTemplateVar($k, $this->getMeta(array('table_struct', $k), $v));
        }
        $aTplCols  = array();
        $aMetaCols = $this->getMeta(array('table_struct', 'columns'), array());
        foreach ($aMetaCols as $k => $v) {
            $aTplCols[$v['field']] = adduceToArray($v);
        }
        $this->setTemplateVar('columns', $aTplCols);

        $aOpRight  = adduceToArray($this->getMeta('open_right'));
        if ($aOpRight) {
            foreach ($aOpRight as $f => &$o) {
                if(!empty($o['key'])) {
                    $this->aAddParam['open_right'][$f] = $o['key']; // ToDo: What is it?
                }
                if(empty($o['pos'])) {
                    $o['pos'] = 'before';
                }
                if(empty($o['pat'])) {
                    $o['pat'] = 'open_r1';
                }
            }
            $this->setTemplateVar('aOpRight', $aOpRight);
        }

        $aHdOrder = adduceToArray($this->getMeta('order'));
        if ($aHdOrder) {
            $this->setTemplateVar('hdOrder', $aHdOrder);
            foreach ($this->getMeta(array('table_struct', 'columns'), array()) as $v) {
                if(isset($v['field']) && isset($aHdOrder[$v['field']])) {
                    $this->aAddParam['label'][$v['field']] = isset($v['head']) ? adduceToArray($v['head']) : null;
                }
            }
        }
    } // function initTplVar

    /**
     * Get Content ExtraData
     */
    public function getExtraData()
    {
        $aRet = parent::getExtraData();
        $aHdOrder = $this->getMeta('order');
        if ($aHdOrder && !isset($aRet['order'])) {
            $aRet['order'] = $aHdOrder;
        }
        if (!$this->getMeta(array('table_struct', 'newData'), true)) {
            $aRet['not_new'] = 1;
        }
        return $aRet;
    } // function getExtraData

    /**
     * Get Content Data
     * @param $bCacheEnable boolean Cache Enable
     * @return array
     */
    public function getContentData($bCacheEnable = true)
    {
        $sEttKey = $this->getMeta('entity_key', null);
        $aFld = array();
        foreach ($this->getMeta(array('table_struct', 'columns'), array()) as $v) {
            if(@$v['field'] && !(@$v['notSQL'])) {
                $aFld[] = $v['field'];
            }
        }

        $aData = $this->getData();

        $sOrder = '';
        $aOrder = isset($aData['order']) ? $aData['order'] : $this->getMeta('order');
        if ($aOrder) {
            foreach ($aOrder as $k => $v) {
                if ($v) {
                    $sOrder .= $k . ($v == 1 ? ' ASC' : ' DESC') . ',';
                }
            }
            if ($sOrder) {
                $sOrder = ' ORDER BY ' . substr($sOrder, 0, -1);
            }
        }

        if (empty($aData['page'])) {
            $aData['page'] = 1;
        }
        $oEtt = ge($this->getMeta('entity'));
        list($nQtt, $nOffset) = $this->definePager($aData['page'], $oEtt, $sEttKey);

        $mId = $oEtt->getDescription()->getPrimeryKey();
        if (is_array($mId)) {
            $aFld = array_merge($mId, $aFld);
        } else {
            array_unshift($aFld, $mId);
        }
        return $this->getArrayAssoc($oEtt, $sEttKey, $aFld, $nQtt, $nOffset, $sOrder, !$this->getMeta('editId', false));
    } // function getContentData

    /**
     * Run Aggregate Request
     * @param \core\base\model\entity $oEtt
     * @param string $sEttKey
     * @param array $aFld
     * @param number $nQtt
     * @param number $nOffset
     * @param string $sOrder
     * @return \core\base\model\rowset
     */
    protected function getArrayAssoc(\core\base\model\entity $oEtt, $sEttKey, $aFld, $nQtt, $nOffset, $sOrder, $bExcludeId = true)
    {
        /* @var $oRowset \core\model\rowset */
        $oRowset = $sEttKey ?
            $oEtt->getRowsetByKey($sEttKey, $this->getCondition(), $nQtt, $nOffset, $sOrder) :
            $oEtt->getRowsetByParam($this->getCondition(), $nQtt, $nOffset, $sOrder);
        return $oRowset->getArrayAssoc($aFld, $bExcludeId, '_');
    } // function getArrayAssoc

    /**
     * Get field label
     * @param string $sName
     * @return string
     */
    public function getFieldLabel($sName)
    {
        foreach ($this->getMeta(array('table_struct', 'columns'), array()) as $v) {
            if ($v['field'] == $sName && @$v['head']) {
                return $v['head'];
            }
        }
        return $sName;
    } // function getFieldLabel

    /**
     * Load entity by Id
     * @param number $nId
     * @return entity_base
     */
    protected function loadEntityById($nId)
    {
        return gr($this->getMeta('entity'), $nId);
    } // function loadEntityById

    /**
     * Define pager data
     * @param number $nPage
     * @param aggr_entity_base $oAggr
     * @return array
     */
    protected function definePager($nPage, $oEtt, $sEttKey)
    {
        if (!$nPage) {
            $nQtt = $nOffset = -1;
        } else {
            $nQttElm = $sEttKey ? $oEtt->getCountByKey($sEttKey, $this->getCondition()) : $oEtt->getCountByParam($this->getCondition());
            $nQtt = $this->getMeta('elmPerPage');
            $nPageQtt = ceil($nQttElm / $nQtt);
            if ($nPage > $nPageQtt) {
                $nPage = $nPageQtt;
                if ($nPage < 1) {
                    $nPage = 1;
                }
            }
            $nOffset = ($nPage - 1) * $nQtt;
            $this->setJson(array('pager' => array($nPage, $nQttElm < 1 ? 1 : $nPageQtt, $nQttElm)));
        }
        return array($nQtt, $nOffset);
    } // function definePager

} // class \core\block\admin\data_table
?>