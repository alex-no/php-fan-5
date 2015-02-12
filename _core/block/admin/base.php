<?php namespace fan\core\block\admin;
/**
 * Base class for loader block
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
 * @version of file: 05.02.005 (12.02.2015)
 * @abstract
 */
abstract class base extends \fan\core\block\loader\base
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
     * Get Hash Array
     * @param array $aArg array('entity' => '', 'sql_key' => '', 'key' => '', 'val' => '', 'param' => '', 'qtt' => '', 'offset' => '', 'order' => '')
     * @param array $aArrMerge array for merge
     * @return array
     */
    public function getHashArray($aArg, $aArrMerge = null, $bMergeBefore = true)
    {
        $aRetData =array();
        foreach ($this->getRowset($aArg) as $e) {
            $aRetData[$e->get($aArg['key'])] = $e->get($aArg['val']);
        }
        if ($aArrMerge) {
            $aRetData = $bMergeBefore ? array_merge_recursive_alt($aArrMerge, $aRetData) : array_merge_recursive_alt($aRetData, $aArrMerge);
        }
        reset($aRetData);
        return array($aRetData, key($aRetData));
    } // function getHashArray */

    /**
     * Get Included Array
     * @param array
     *              array('entity' => '', 'sql_key' => '', 'key' => '', 'val' => '', 'parent' => '', 'param' => '', 'qtt' => '', 'offset' => '', 'order' => '', 'select' => '')
     *              OR (for 0-level only) - array('data' => array('k1' => 'v1', 'k2' => 'v2', 'k2' => 'v2',...), 'select' => '')
     * @return array
     */
    public function getIncludedArray()
    {
        $aRetData =array();
        $aRetKeys =array();
        $aArgs = func_get_args();

        $aArgPrev = array_shift($aArgs);
        if (isset($aArgPrev['data'])) {
            foreach ($aArgPrev['data'] as $k => $v) {
                $aRetData[$k] = array('val' => $v);
            }
        } else {
            $aTmp = $this->getRowset($aArgPrev);
            foreach ($aTmp as $e) {
                /* @var $e \fan\core\base\model\row */
                $aRetData[$e->get($aArgPrev['key'])] = array('val' => $e->get($aArgPrev['val']));
            }
        }
        if (@$aArgPrev['select']) {
            $aRetKeys[0] = $aArgPrev['select'];
        }

        if ($aRetData) {
            $nDepth = 1;

            $aParent =& $aRetData;
            foreach ($aArgs as $k => $aArg) {
                $aInterim[$k] = array();

                if (!@$aArg['parent']) {
                    $aArg['parent'] = $aArgPrev['key'];
                }
                $aParam = array($aArg['parent'] => array_keys($aParent));
                $aArg['param'] = isset($aArg['param']) ? array_merge($aParam, $aArg['param']) : $aParam;

                $aTmp = $this->getRowset($aArg);
                if (!$aTmp) {
                    break;
                }
                foreach ($aTmp as $e) {
                    $k0 = $e->get($aArg['parent']);
                    $k1 = $e->get($aArg['key']);
                    if (isset($aParent[$k0])) {
                        $aInterim[$k][$k1] = array('val' => $e->get($aArg['val']));
                        $aParent[$k0]['child'][$k1] =& $aInterim[$k][$k1];
                    }
                }

                if (@$aArg['select']) {
                    $aRetKeys[$nDepth] = $aArg['select'];
                }

                $aArgPrev = $aArg;
                $aParent =& $aInterim[$k];
                $nDepth++;
            }
            $this->defineRetKeys($aRetKeys, $aRetData, $nDepth, 0);
            ksort($aRetKeys);
        } else {
            $nDepth = 0;
        }

        return array($aRetData, $aRetKeys, $nDepth);
    } // function getIncludedArray */

    /**
     * Get Aggregate Entities
     * @param array $aArg array('entity' => '', 'sql_key' => '', 'key' => '', 'val' => '', 'parent' => '', 'param' => '', 'qtt' => '', 'offset' => '', 'order' => '')
     * @return \fan\core\base\model\rowset
     */
    private function getRowset($aArg)
    {
        if (!isset($aArg['param'])) {
            $aArg['param'] = null;
        }
        if (!isset($aArg['qtt'])) {
            $aArg['qtt'] = -1;
        }
        if (!isset($aArg['offset'])) {
            $aArg['offset'] = -1;
        }
        if (!isset($aArg['order'])) {
            $aArg['order'] = '';
        }

        $oEtt = ge($aArg['entity']);
        if (isset($aArg['sql_key'])) {
            return $oEtt->getRowsetByKey($aArg['sql_key'], $aArg['param'], $aArg['qtt'], $aArg['offset'], $aArg['order']);
        }
        return $oEtt->getRowsetByParam($aArg['param'], $aArg['qtt'], $aArg['offset'], $aArg['order']);
    } // function getRowset


    /**
     * Get first key
     * @link array $aRetKeys
     * @param array $aRetData Return data
     * @param integer $nDepth integer depth
     * @param integer $i integer depth
     */
    private function defineRetKeys(&$aRetKeys, $aRetData, $nDepth, $i)
    {
        if (array_key_exists($i, $aRetKeys)) {
            if ($nDepth <= 1) {
                return true;
            }
            $v = @$aRetData[$aRetKeys[$i]]['child'];
            return $v && $this->defineRetKeys($aRetKeys, $v, $nDepth - 1, $i + 1);
        }
        foreach ($aRetData as $k => $v) {
            if ($nDepth <= 1 || (@$v['child'] && $this->defineRetKeys($aRetKeys, $v['child'], $nDepth - 1, $i + 1))) {
                $aRetKeys[$i] = $k;
                return true;
            }
        }
        return false;
    } // function defineRetKeys

    /**
     * Set template variable value
     * @param string $sKey
     * @param mixed $mValue
     * @return \fan\core\block\admin\data
     */
    protected function setTemplateVar($sKey, $mValue)
    {
        if ($sKey == 'json') {
            $this->setJson($mValue);
        } elseif ($sKey == 'text') {
            $this->setText($mValue);
        } else {
            $this->view[$sKey] = $mValue;
        }
        return $this;
    } // function setTemplateVar


    /**
     * Get template's code
     * @param array $aAddVars
     * @return string
     */
    protected function getTemplateCode($aAddVars = array())
    {
        $sRetHtml  = '';
        $aTmp      = $this->view instanceof \fan\core\view\router && count($this->view) > 0 ? $this->view->toArray() : array();
        $aTplVars  = isset($aTmp['html']) && is_array($aTmp['html']) ? $aTmp['html'] : array();
        $sTemplate = $this->getTemplate();
        if (!empty($sTemplate)) {

            // Transfer template value from meta-data
            $aMetaTplVar = $this->getMeta('tplVars');
            if(is_array($aMetaTplVar)) {
                foreach ($aMetaTplVar as $k => $v) {
                    if (!isset($aTplVars[$k])) {
                        $aTplVars[$k] = $v;
                    }
                }
            }

            $oTemplate = service('template')->get($sTemplate, $this->getMeta('tpl_parent_class'), $this);
            foreach ($aTplVars as $k => $v) {
                $oTemplate->assign($k, $v);
            }
            foreach ($aAddVars as $k => $v) {
                $oTemplate->assign($k, $v);
            }
            $sRetHtml = $oTemplate->fetch();

        } elseif (!empty($aTplVars)) {
            foreach ($aTplVars as $v) {
                if (is_scalar($v)) {
                    $sRetHtml .= $v;
                }
            }
        }
        return $sRetHtml;
    } // function getTemplateCode

} // class \fan\core\block\admin\base
?>