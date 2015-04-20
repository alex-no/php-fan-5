<?php namespace fan\core\base\model\spec_file\image;
/**
 * Entity of image file
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
 * @abstract
 */
abstract class entity extends \fan\core\base\model\spec_file\entity
{
    /**
     * @var string RegExp for IMG placeholder
     */
    protected $sImgRegExp = '/\{IMG(?:_(\d+)|-(\d+))\s*(.*?)\}/is';

    /**
     * @var string RegExp for NAIL placeholder
     */
    protected $sAdvImgRegExp = '/\{(IMG|NAIL|LINK|BLOWUP1|BLOWUP2)(?:_(\d+)|-(\d+))(?:\[(\d+)*(\d+)\])\s*(.*?)\}/is';

    /**
     * Rotate Image
     * @param numeric $nId id of image
     * @param numeric $nAngle Angle of rotate in degrees
     */
    public function rotateImageById($nId, $nAngle)
    {
        $oRow = $this->getRowById($nId);
        $oRow->rotateImage($nAngle);
    }// function rotateImageById

    /**
     * Get Img-tag by id
     * @param numeric $nId
     * @param string $sCssClass
     * @param array $aParam
     * @return string
     */
    public function getImgTagById($nId, $sCssClass = '', $aParam = null)
    {
        $oRow = $this->getRowById($nId);
        return $oRow->getImgTag($sCssClass, $aParam);
    }// function getImgTagById

    /**
     * Get Img-tag by Place-holder code
     * @param string $sCode
     * @param array $aParam
     * @return string
     */
    public function getImgTagByCode($sCode, $aParam = null)
    {
        $aMatches = null;
        preg_match($this->sImgRegExp, $sCode, $aMatches);
        $oRow = $this->getRowById($aMatches[1]);
        return $oRow->getImgTag($aMatches[2], $aParam);
    }// function getImgTagByCode

    /**
     * Replace Place-holder code to Img-tag
     * @param string $sCode
     * @param array $aLinkTbl
     * @param string $sKeyField
     * @return string
     */
    public function replaceCodeToImgTag($sCode, $aLinkTbl = NULL, $sKeyField = 'id_file_data')
    {
        $aMatches = null;
        if (preg_match_all($this->sImgRegExp, $sCode, $aMatches)) {
            $aRepl = array();
            $aPos  = array(
                'id'    => 1,
                'num'   => 2,
                'class' => 3,
            );
            $aEtt = $this->_prepareImgEtt($aRepl, $aMatches, $aPos, $aLinkTbl, $sKeyField, false);

            // Replace image code
            foreach ($aEtt as $e) {
                foreach ($aRepl[$e->getId()] as $v) {
                    $sCode = str_replace($v[0], $e->getImgTag($v[1]), $sCode);
                }
            }
        }
        return $sCode;
    }// function replaceCodeToImgTag


    /**
     * Replace Place-holder code to Img-tag
     * @param string $sCode
     * @param array $aParam
     * @param array $aLinkTbl
     * @param string $sKeyField
     * @return string
     */
    public function advReplaceCodeToImgTag($sCode, $aParam, $aLinkTbl = NULL, $sKeyField = 'id_file_data')
    {
        $aMatches = null;
        if (preg_match_all($this->sAdvImgRegExp, $sCode, $aMatches)) {
            $aRepl = array();
            $aPos  = array(
                'type'   => 1,
                'id'     => 2,
                'num'    => 3,
                'width'  => 4,
                'height' => 5,
                'class'  => 6,
            );
            $aEtt = $this->_prepareImgEtt($aRepl, $aMatches, $aPos, $aLinkTbl, $sKeyField, true);

            // Replace image code
            foreach ($aEtt as $e) {
                foreach ($aRepl[$e->getId()] as $v) {
                    $v[2] = strtolower($v[2]);
                    $aParamTmp = $aParam;
                    if (!empty($v[1])) {
                        $k = $v[2] == 'img' || $v[2] == 'nail' ? $v[2] : 'div';
                        $aParamTmp[$k]['class'] = empty($aParamTmp[$k]['class']) ? $v[3] : $aParamTmp[$k]['class'] . ' ' . $v[3];
                    }
                    if (!empty($v[3])) {
                        $aParamTmp['nail']['width'] = $v[3];
                    }
                    if (!empty($v[4])) {
                        $aParamTmp['nail']['height'] = $v[4];
                    }
                    $sCode = str_replace($v[0], $e->advGetImgTag($v[2], $aParamTmp), $sCode);
                }
            }
        }
        return $sCode;
    }// function advReplaceCodeToImgTag

    /**
     * Prepare Entity of image
     * @param array $aRepl
     * @param array $aMatches
     * @param array $aPos
     * @param array $aLinkTbl
     * @param string $sKeyField
     * @param boolean $bAdv
     * @return \fan\core\base\model\rowset
     */
    private function _prepareImgEtt(&$aRepl, $aMatches, $aPos, $aLinkTbl, $sKeyField, $bAdv)
    {
        // Define by ID
        foreach ($aMatches[$aPos['id']] as $k => $id) {
            if ($id) {
                $aRepl[$id][0] = array($aMatches[0][$k], $aMatches[$aPos['class']][$k]);
                if ($bAdv){
                    $aRepl[$id][0][2] = $aMatches[$aPos['type']];
                    $aRepl[$id][0][3] = @$aMatches[$aPos['width']];
                    $aRepl[$id][0][4] = @$aMatches[$aPos['height']];
                }
            }
        }

        // Define by NUM
        if ($aLinkTbl) {
            foreach ($aMatches[$aPos['num']] as $k => $n) {
                if ($n) {
                    $oRow = gr($aLinkTbl[0])->loadByParam(array(
                        $aLinkTbl[1] => $aLinkTbl[2],
                        'order_num'  => $n,
                    ));
                    if ($oRow->checkIsLoad()) {
                        $aRepl[$oRow->id_file_data][1] = array($aMatches[0][$k], $aMatches[$aPos['class']][$k]);
                        if ($bAdv){
                            $aRepl[$id][0][2] = $aMatches[$aPos['type']];
                            $aRepl[$id][0][3] = @$aMatches[$aPos['width']];
                            $aRepl[$id][0][4] = @$aMatches[$aPos['height']];
                        }
                    }
                }
            }
        }

        // Get entity image list
        return $this->getRowsetByParam($sKeyField . ' IN(' . implode(',', array_keys($aRepl)) . ')');
    }// function _prepareImgEtt

} // class \fan\core\base\model\spec_file\image\entity
?>