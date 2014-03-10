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
 * @version of file: 05.02.001 (10.03.2014)
 * @abstract
 */
abstract class entity extends \fan\core\base\model\spec_file\entity
{
    /**
     * @var string RegExp for IMG placeholder
     */
    private static $sImgRegExp = '/\{IMG(?:_(\d+)|-(\d+))\s*(.*?)\}/is';

    /**
     * @var string RegExp for NAIL placeholder
     */
    private static $sAdvImgRegExp = '/\{(IMG|NAIL|LINK|BLOWUP1|BLOWUP2)(?:_(\d+)|-(\d+))(?:\[(\d+)*(\d+)\])\s*(.*?)\}/is';

    /**
     * Rotate Image
     * @param numeric $nId id of image
     * @param numeric $nAngle Angle of rotate in degrees
     * @param numeric $sEntityName Name of entity
     */
    public static function rotateImageById($nId, $nAngle, $sEntityName = 'image')
    {
        $oRow = gr($sEntityName, $nId);
        $oRow->rotateImage($nAngle);
    }// function rotateImageById

    /**
     * Get Img-tag by id
     * @return array
     */
    public static function getImgTagById($nId, $sCssClass = '', $aParam = null, $sEntityName = 'image')
    {
        $oRow = gr($sEntityName, $nId);
        return $oRow->getImgTag($sCssClass, $aParam);
    }// function getImgTagById

    /**
     * Get Img-tag by Place-holder code
     * @return array
     */
    public static function getImgTagByCode($sCode, $aParam = null, $sEntityName = 'image')
    {
        preg_match(self::$sImgRegExp, $sCode, $aMatches);
        $oRow = gr($sEntityName, $aMatches[1]);
        return $oRow->getImgTag($aMatches[2], $aParam);
    }// function getImgTagByCode

    /**
     * Replace Place-holder code to Img-tag
     * @return string
     */
    public static function replaceCodeToImgTag($sCode, $aLinkTbl = NULL, $sEntityName = 'image', $sKeyField = 'id_file_data')
    {
        if (preg_match_all(self::$sImgRegExp, $sCode, $aMatches)) {
            $aRepl = array();
            $aPos  = array(
                'id'    => 1,
                'num'   => 2,
                'class' => 3,
            );
            $aEtt = self::prepareImgEtt($aRepl, $aMatches, $aPos, $aLinkTbl, $sEntityName, $sKeyField, false);

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
     * @return string
     */
    public static function advReplaceCodeToImgTag($sCode, $aParam, $aLinkTbl = NULL, $sEntityName = 'image', $sKeyField = 'id_file_data')
    {
        if (preg_match_all(self::$sAdvImgRegExp, $sCode, $aMatches)) {
            $aRepl = array();
            $aPos  = array(
                'type'   => 1,
                'id'     => 2,
                'num'    => 3,
                'width'  => 4,
                'height' => 5,
                'class'  => 6,
            );
            $aEtt = self::prepareImgEtt($aRepl, $aMatches, $aLinkTbl, $sEntityName, $sKeyField, true);

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
    }// function replaceCodeToImgTag

    /**
     * Replace Place-holder code to Img-tag
     * @return array
     */
    private static function prepareImgEtt(&$aRepl, $aMatches, $aPos, $aLinkTbl, $sEntityName, $sKeyField, $bAdv)
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
        return ge($sEntityName)->getRowsetByParam($sKeyField . ' IN(' . implode(',', array_keys($aRepl)) . ')');
    }// function replaceCodeToImgTag

} // class \fan\core\base\model\spec_file\image\entity
?>