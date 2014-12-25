<?php namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;

/**
 * Service Image Processor
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class image_draw extends image_modify
{

// =========================== Main Convert functions ============================ \\

    /**
     * Setting Image Background
     * @param integer|string|array $mBgrColor - color of background
     * @return \fan\core\service\image_draw
     */
    public function setBackground($mBgrColor = 0xFFFFFF)
    {
        imagefilledrectangle($this->oImage, 0, 0, $this->nSourceWidth, $this->nSourceHeight, $this->adaptColor($mBgrColor));
        return $this;
    } // function setBackground

    /**
     * Drawing the text in rectangle
     * @param string $sString
     * @param array $aCoord
     * @param numeric $nFontNumber
     * @param integer|string|array $mFntColor
     * @param string $sTxtAlign
     * @param string $sVertAlign
     * @return \fan\core\service\image_draw
     */
    public function drawText($sString, $aCoord, $nFontNumber = 1, $mFntColor = 0x000000, $sTxtAlign = 'left', $sVertAlign = 'top')
    {
        //Calculating of top y coordinate for text
        if ($sVertAlign == 'top') {
            $top = $this->_getCoord($aCoord, 'top');
        } elseif ($sVertAlign == 'middle') {
            $top = ceil(($this->nSourceHeight - imagefontheight($nFontNumber) - $this->_getCoordDiff($aCoord, 'bottom', 'top')) / 2);
        } elseif ($sVertAlign == 'bottom') {
            $top = $this->nSourceHeight - ($this->_getCoord($aCoord, 'bottom') + imagefontheight($nFontNumber));
        }
        //Calculating of left x coordinate for text
        if ($sTxtAlign == 'left') {
            $left = $this->_getCoord($aCoord, 'left');
        } elseif ($sTxtAlign == 'center') {
            $left = ceil(($this->nSourceWidth - imagefontwidth($nFontNumber) * strlen($sString) - $this->_getCoordDiff($aCoord, 'right', 'left')) / 2);
        } elseif ($sTxtAlign == 'right') {
            $left = $this->nSourceWidth - ($this->_getCoord($aCoord, 'right') + imagefontwidth($nFontNumber) * strlen($sString));
        }

        imagestring($this->oImage, $nFontNumber, $left, $top, $sString, $this->adaptColor($mFntColor));
        return $this;
    } // function drawText

    /**
     * Drawing the text by using ttf font file in rectangle
     * @param string $sString
     * @param array $aCoord : 'left', 'right', 'top', 'bottom', 'height', 'angle'
     * @param string $sFontFile
     * @param integer|string|array $mFntColor
     * @param string $sTxtAlign
     * @param string $sVertAlign
     * @param array $aInfo
     * @return \fan\core\service\image_draw
     */
    public function drawTextTtf($sString, $aCoord, $sFontFile = '', $mFntColor = 0X000000, $sTxtAlign = 'left', $sVertAlign = 'top',  $aInfo = array('linespacing' => 1))
    {
        if (!$sFontFile) {
            $sFontFile = $this->get_config('FONT_FILE', 'arial.ttf');
        }
        $sFontFile = \bootstrap::parsePath($this->getConfig('FONT_PATH', '{PROJECT}/data/font/')) . $sFontFile;

        $iFontHeight = isset($aCoord['height']) ? $aCoord['height'] : $this->nSourceHeight - $this->_getCoordDiff($aCoord, 'bottom', 'top')/2;
        $aStringSize = imageftbbox($iFontHeight, 0, $sFontFile, $sString, $aInfo);
        $iStrWidth  = $aStringSize[4];
        $iStrHeight = -$aStringSize[5];

        //calculating of top y coordinate for text
        if ($sVertAlign == 'top') {
            $nTop = $this->_getCoord($aCoord, 'top');
        } elseif ($sVertAlign == 'middle') {
            $nTop = ceil(($this->nSourceHeight - $iStrHeight - $this->_getCoordDiff($aCoord, 'bottom', 'top')) / 2);
        } elseif ($sVertAlign == 'bottom') {
            $nTop = $this->nSourceHeight - ($this->_getCoord($aCoord, 'bottom') + $iStrHeight);
        }
        //calculating of left x coordinate for text
        if ($sTxtAlign == 'left') {
            $nLeft = $this->_getCoord($aCoord, 'left');
        } elseif ($sTxtAlign == 'center') {
            $nLeft = ceil(($this->nSourceWidth - $iStrWidth - $this->_getCoordDiff($aCoord, 'right', 'left')) / 2);
        } elseif ($sTxtAlign == 'right') {
            $nLeft = $this->nSourceWidth - ($this->_getCoord($aCoord, 'right') + $iStrWidth);
        }

        imagettftext($this->oImage, $iFontHeight, array_val($aCoord, 'angle', 0), $nLeft, $nTop + $iFontHeight, $this->adaptColor($mFntColor), $sFontFile, $sString);
        return $this;
    } // function drawTextTtf

   /**
     * Drawing rectangle
     * @param array $aCoord - array of coordinates
     *   "left" - X-coordinate of left top corner
     *   "top" - Y-coordinate of left top corner
     *   "right" - X-coordinate of right bottom corner
     *   "bottom" - Y-coordinate of right bottom corner
     * @param integer|string|array|null $mBrdColor - border color
     * @param integer|string|array|null $mBgrColor - background color
     * @return \fan\core\service\image_draw
     */
    public function rectangle($aCoord, $mBrdColor = 0x000000, $mBgrColor = 0xFFFFFF)
    {
        if (empty($aCoord)) {
            $aCoord = array(
                'left'   => 0,
                'right'  => 1,
                'top'    => 0,
                'bottom' => 1,
                'angle'  => 0,
                'height' => 0,
            );
        }
        if (!is_null($mBrdColor)) {
            imagerectangle($this->oImage, $aCoord['left'], $aCoord['top'], $this->nWidth - $aCoord['right'], $this->nHeight - $aCoord['bottom'], $this->adaptColor($mBrdColor));
        }
        if (!is_null($mBgrColor)) {
            imagefilledrectangle($this->oImage, $aCoord['left'], $aCoord['top'], $this->nWidth - $aCoord['right'], $this->nHeight - $aCoord['bottom'], $this->adaptColor($mBgrColor));
        }
        return $this;
    } // function rectangle

    /**
     * Drawing polygon
     * @param array $aCoord is an array containing the x and y co-ordinates of the polygons vertices consecutively.
     * @param integer|string|array|null $mBrdColor - border color
     * @param integer|string|array|null $mBgrColor - background color - it can be a scalar value or an array
     * @return \fan\core\service\image_draw
     */
    public function polygon($aCoord, $mBrdColor = 0x000000, $mBgrColor = 0XFFFFFF)
    {
        if (!is_null($mBrdColor)) {
            imagepolygon($this->oImage, $aCoord, count($aCoord) / 2, $this->adaptColor($mBrdColor));
        }
        if (!is_null($mBgrColor)) {
            imagefilledpolygon($this->oImage, $aCoord, count($aCoord) / 2, $this->adaptColor($mBgrColor));
        }
        return $this;
    } // function polygon

   /**
     * Drawing ellipse
     * @param array $aCoord - array of coordinates
     *   "centerX" - X-coordinate of left top corner
     *   "centerY" - Y-coordinate of left top corner
     *   "width" - X-coordinate of right bottom corner
     *   "height" - Y-coordinate of right bottom corner
     * @param integer|string|array|null $mBrdColor - border color
     * @param integer|string|array|null $mBgrColor - background color
     * @return \fan\core\service\image_draw
     */
    public function ellipse($aCoord, $mBrdColor = 0x000000, $mBgrColor = 0XFFFFFF)
    {
        if (!is_null($mBrdColor)) {
            imageellipse(
                    $this->oImage,
                    $aCoord['centerX'],
                    $aCoord['centerY'],
                    $aCoord['width'],
                    $aCoord['height'],
                    $this->adaptColor($mBrdColor)
            );
        }
        if (!is_null($mBgrColor)) {
            imagefilledellipse(
                    $this->oImage,
                    $aCoord['centerX'],
                    $aCoord['centerY'],
                    $aCoord['width'],
                    $aCoord['height'],
                    $this->adaptColor($mBgrColor)
            );
        }
        return $this;
    } // function ellipse

    /**
     * Drawing ellipse sector
     * @param array $aCoord - array of coordinates
     *   "centerX" - X-coordinate of left top corner
     *   "centerY" - Y-coordinate of left top corner
     *   "width"   - X-coordinate of right bottom corner
     *   "height"  - Y-coordinate of right bottom corner
     * @param integer|string|array|null $mBrdColor - border color
     * @param integer|string|array|null $mBgrColor - background color
     * @return \fan\core\service\image_draw
     */
    public function ellipseSector($aCoord, $mBrdColor = 0x000000, $mBgrColor = 0XFFFFFF)
    {
        if (!is_null($mBrdColor)) {
            imagearc(
                    $this->oImage,
                    $aCoord['centerX'],
                    $aCoord['centerY'],
                    $aCoord['width'],
                    $aCoord['height'],
                    $aCoord['startAngle'],
                    $aCoord['endAngle'],
                    $this->adaptColor($mBrdColor)
            );
        }
        if (!is_null($mBgrColor)) {
            imagefilledarc(
                    $this->oImage,
                    $aCoord['centerX'],
                    $aCoord['centerY'],
                    $aCoord['width'],
                    $aCoord['height'],
                    $aCoord['startAngle'],
                    $aCoord['endAngle'],
                    $this->adaptColor($mBgrColor),
                    IMG_ARC_PIE
            );
        }
        return $this;
    } // function ellipseSector



    /**
     * Drawing line
     * @param array $aCoord - array of coordinates
     *   "left"   - X-coordinate of left top corner
     *   "top"    - Y-coordinate of left top corner
     *   "right"  - X-coordinate of right bottom corner
     *   "bottom" - Y-coordinate of right bottom corner
     * @param integer|string|array $mBrdColor - color of axis line
     * @return \fan\core\service\image_draw
     */
    public function line($aCoord, $mBrdColor = 0X000000)
    {
        imageline(
                $this->oImage,
                array_val($aCoord, 'left', 0),
                array_val($aCoord, 'top', 0),
                $this->nWidth  - array_val($aCoord, 'right',  0),
                $this->nHeight - array_val($aCoord, 'bottom', 0),
                $this->adaptColor($mBrdColor)
        );
        return $this;
    } // function line

    /**
     * Drawing Vertical right line
     * @param array $aCoord - array of coordinates
     *   "left" or "right" - X-coordinate of line
     *   "top"    - Y-coordinate of top corner
     *   "bottom" - Y-coordinate of bottom corner
     * @param integer|string|array $mBrdColor - color of axis line
     * @return \fan\core\service\image_draw
     */
    public function lineVertical($aCoord, $mBrdColor = 0X000000)
    {
        if (!isset($aCoord['left'])) {
            $aCoord['left'] = isset($aCoord['right']) ? $this->nWidth - $aCoord['right'] : 0;
        }
        $aCoord['right'] = $this->nWidth - $aCoord['left'];
        return $this->line($aCoord, $mBrdColor);
    } // function lineVertical

    /**
     * Drawing vertical bottom line
     * @param array $aCoord - array of coordinates
     *   "top" or "bottom" - Y-coordinate of line
     *   "left" - X-coordinate of left bottom corner
     *   "right" - X-coordinate of right bottom corner
     * @param integer|string|array $mBrdColor - color of axis line
     * @return \fan\core\service\image_draw
     */
    public function lineHorizontal($aCoord, $mBrdColor = 0X000000)
    {
        if (!isset($aCoord['top'])) {
            $aCoord['top'] = isset($aCoord['bottom']) ? $this->nHeight - $aCoord['bottom'] : 0;
        }
        $aCoord['bottom'] = $this->nHeight - $aCoord['top'];
        return $this->line($aCoord, $mBrdColor);
    } // function line

    /**
     * Getting Font Width in pixels
     * @param numeric $nFontNumber
     * @return numeric
     */
    public function getFontWidth($nFontNumber)
    {
        return imagefontwidth($nFontNumber);
    } // function getFontWidth

    /**
     * Getting Font Heigth in pixels
     * @param numeric $nFontNumber
     * @return numeric
     */
    public function getFontHeigth($nFontNumber)
    {
        return imagefontheight($nFontNumber);
    } // function getFontHeigth

// ========================= Private methods ============================ \\

} // class \fan\core\service\image_draw
?>