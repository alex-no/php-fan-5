<?php namespace fan\core\service\captcha\file_maker;
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
class picture_1 extends \fan\core\service\captcha\base
{
    /**
     * @var array Image Info
     */
    protected $aImgInfo;

    /**
     * Get Headers for Binary Data of Captcha
     * @return string
     */
    public function getHeaders()
    {
        $this->getData();
        $aHeaders = $this->aImgInfo['headers'];
        $aHeaders['filename'] = 'captcha.' . $this->aImgInfo['type'];
        return $aHeaders;
    } // function getHeaders

    /**
     * Get Binary Data of Captcha
     * @return string
     */
    public function getData()
    {
        if (empty($this->aImgInfo)) {
            $this->_makeBinaryData();
        }
        return $this->aImgInfo['content'];
    } // function getData

    // ======== Private/Protected methods ======== \\

    /**
     * String of result binary data
     * @return string
     */
    protected function _makeBinaryData()
    {
        $oConf = $this->oConfig['image'];
        /* @var $oConf \fan\core\service\config\row */
        $nWidth   = $oConf->get('width',  180);
        $nHeight  = $oConf->get('height',  60);
        $nQuality = $oConf->get('quality', 80);

        // Make image service
        $oImg = service('image_draw');
        /* @var $oImg \fan\core\service\image_draw */
        $sSrc = $this->_randomChoice($oConf['src_files']);
        if (empty($sSrc)) {
            $oImg->setSource(null, array(
                'width'   => $nWidth,
                'height'  => $nHeight,
                'quality' => $nQuality,
            ));
        } else {
            $oImg->setSource($this->oConfig['SRC_DIR'] . $sSrc, array(
                'quality' => $nQuality,
            ));
            $oImg->crop(
                    rand(0, $oImg->getWidth() - $nWidth),
                    rand(0, $oImg->getHeigth() - $nHeight),
                    $nWidth,
                    $nHeight
            );
        }

        // Draw lines in background
        $this->_drawLines($oImg, $oConf, $oConf->get('line_qtt', 10), $nHeight);

        // Draw captcha text
        $sText = $this->oFacade->getText();
        $nLeft = rand(5, 10);
        $nTop  = rand(5, floor($nHeight / 2));
        $sFont = $this->_randomChoice($oConf['fonts']);
        for($i = 0; $i < strlen($sText); $i++) {
            $nFontHeight = $this->_randomValue($oConf['font_height'], 20, 24);
            $oImg->drawTextTtf($sText{$i}, array(
                'left'   => $nLeft,
                'top'    => rand($nTop, $nTop + floor($nHeight / 10)),
                'height' => $nFontHeight,
                'angle'  => rand(-5, 5),
            ), $sFont, $this->_randomColor($oConf, 'font'));
            $nLeft += floor($nFontHeight * 0.8) + $this->_randomValue($oConf['interval'], 2, 5);
        }

        // Draw lines in front
        $this->_drawLines($oImg, $oConf, $oConf->get('line_qtt', 10), $nHeight);

        $this->aImgInfo = $oImg->getImageInfo();
        return $this;
    } // function _makeBinaryData

    /**
     * Choice random value from array
     * @param type $aArr
     * @return mixed
     */
    protected function _randomChoice($aArr)
    {
        if (is_object($aArr)) {
            if (!method_exists($aArr, 'toArray')) {
                return null;
            }
            $aArr = $aArr->toArray();
        }
        return empty($aArr) ? null : $aArr[array_rand($aArr)];
    } // function _randomChoice

    /**
     * Get random value
     * @param \fan\core\service\config\row $oConf
     * @param numeric $nDefMin
     * @param numeric $nDefMax
     * @return numeric
     */
    protected function _randomValue($oConf, $nDefMin, $nDefMax)
    {
        if (is_object($oConf)) {
            $nMin = $oConf->get(0, $nDefMin);
            $nMax = $oConf->get(1, $nDefMax);
        } else {
            $nMin = $nDefMin;
            $nMax = $nDefMax;
        }
        return rand($nMin, $nMax);
    } // function _randomValue

    /**
     * Get random value
     * @param \fan\core\service\config\row $oConf
     * @param sting $sKey
     * @return numeric
     */
    protected function _randomColor($oConf, $sKey)
    {
        $aSrc = array(
            'r' => array(0, 255),
            'g' => array(0, 255),
            'b' => array(0, 255),
        );

        $aRes = array();
        foreach ($aSrc as $k => $v) {
            $nMin = $oConf->get(array('color', $sKey, $k, 0), $v[0]);
            $nMax = $oConf->get(array('color', $sKey, $k, 1), $v[1]);
            $aRes[$k] = rand($nMin, $nMax);
        }
        return $aRes;
    } // function _randomColor

    /**
     * Draw several Lines
     * @param \fan\core\service\image_draw $oImg
     * @param \fan\core\service\config\row $oConf
     * @param numeric $nQtt
     * @param numeric $nHeight
     * @return \fan\core\service\captcha\file_maker\picture_1
     */
    protected function _drawLines($oImg, $oConf, $nQtt, $nHeight)
    {
        for ($i = 0; $i < $nQtt / 2; $i++)
        {
            $oImg->line(array(
                'left'   => rand(0, 20),
                'right'  => rand(0, 20),
                'top'    => rand(0, $nHeight),
                'bottom' => rand(0, $nHeight),
            ), $this->_randomColor($oConf, 'line'));
        }
        return $this;
    } // function _drawLines

} // class \fan\core\service\captcha\file_maker\picture_1
?>