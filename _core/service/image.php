<?php namespace core\service;
use project\exception\service\fatal as fatalException;
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
 * @version of file: 05.001 (29.09.2011)
 */
class image extends \core\base\service\multi
{
    /**
     * @var array Service's Instances
     */
    private static $aInstances;

    /**
     * @var string Path to Source Image
     */
    protected $sSourcePath;
    /**
     * @var number width of Source Image
     */
    protected $nSourceWidth;
    /**
     * @var number height of Source Image
     */
    protected $nSourceHeight;
    /**
     * @var number Image current width
     */
    protected $nWidth;
    /**
     * @var number Image current height
     */
    protected $nHeight;
    /**
     * @var array Parameters of Source image
     */
    private $aSourceParam;
    /**
     * @var object Data Source image
     */
    protected $oImage;

    /**
     * @var number Quality save of Create Image
     */
    private $nQuality = 80;

    /**
     * @var string Image type
     */
    private $sCreateType = null;

    /**
     * @var array Type images for conversion from number to text
     */
    private $aConvType = array(
        1  => 'gif',
        2  => 'jpeg',
        3  => 'png',
        15 => 'wbmp',
        16 => 'xbm',
    );

    /**
     * Service's constructor
     */
    protected function __construct($sSourcePath = null, $aCreateParam = array())
    {
        parent::__construct(empty(self::$aInstances));

        if ($sSourcePath || $aCreateParam) {
            $this->setSource($sSourcePath, $aCreateParam);
        }
    } // function __construct

    // ======== Static methods ======== \\

    /**
     * Get Service's instance of current service by $sSourcePath
     * If $sSourcePath isn't set - Get defaul instance
     * @param string $sSourcePath Create Path
     * @param string $aCreateParam Create parameters
     * @return \core\service\image
     */
    public static function instance($sSourcePath = null, $aCreateParam = array(), $bSaveInstance = true)
    {
        if (!isset(self::$aInstances[$sSourcePath])) {
            $oInstance = new self($sSourcePath, $aCreateParam);
            if (!$bSaveInstance) {
                return $oInstance;
            }
            self::$aInstances[$sSourcePath] = $oInstance;
        }
        return self::$aInstances[$sSourcePath];
    } // function instance

    // ======== Main Interface methods ======== \\

    // ---------- Prepare functions ---------- \\
    /**
     * Prepare Image
     * Set $sSourcePath
     * Make image object from file and set input image parameters
     * @param string $sSourcePath Path to sourse image
     * @param array $aCreateParam array of image parameters (not required)
     * @return \core\service\image
     */
    public function setSource($sSourcePath, $aCreateParam = array())
    {
        if ($aCreateParam) {
            $this->setParam($aCreateParam);
        }

        if (is_null($sSourcePath)) {
            if($this->nSourceWidth < 1 || $this->nSourceHeight < 1) {
                throw new fatalException($this, 'Image size doesn\'t set (' . $this->nSourceWidth . 'x' . $this->nSourceHeight . ').');
            }
            $this->oImage = imagecreatetruecolor($this->nSourceWidth, $this->nSourceHeight);
        } else {
            if(!$sSourcePath) {
                $sSourcePath = $this->getConfig('DEFAULT_IMAGE');
            }

            $this->sSourcePath = $sSourcePath;

            if(!file_exists($sSourcePath)) {
                $sSourcePath = \bootstrap::parsePath($this->getConfig('BASIC_PATH')) . $this->sSourcePath;
            }

            if(file_exists($sSourcePath)) {
                $this->aSourceParam = @getimagesize($sSourcePath);
                if(!$this->aSourceParam) {
                    throw new fatalException($this, 'Incorrect image file format "' . $this->sSourcePath . '".');
                }

            } else {
                throw new fatalException($this, 'Not exist file "' . $this->sSourcePath . '".');
            }
            $sType = $this->aConvType[$this->aSourceParam[2]];
            $sFunc = 'imagecreatefrom' . $sType;
            $this->oImage        = $sFunc($sSourcePath);
            $this->nSourceWidth  = $this->aSourceParam[0];
            $this->nSourceHeight = $this->aSourceParam[1];
            if (is_null($this->sCreateType)) {
                $this->sCreateType = $sType;
            }
        }
        $this->nWidth  = $this->nSourceWidth;
        $this->nHeight = $this->nSourceHeight;
        return $this;
    } // function setSource

    /**
     * Set parameter Image
     * @param array $aCreateParam Path to sourse image
     * @return \core\service\image
     */
    public function setParam($aCreateParam)
    {
        if (isset($aCreateParam['type'])) {
            $this->sCreateType   = $aCreateParam['type'];
        }
        if (isset($aCreateParam['quality'])) {
            $this->nQuality      = $aCreateParam['quality'] < 1 || $aCreateParam['quality'] > 100 ? 80 : $aCreateParam['quality'];
        }
        if (isset($aCreateParam['width'])) {
            $this->nSourceWidth  = $aCreateParam['width'];
        }
        if (isset($aCreateParam['height'])) {
            $this->nSourceHeight = $aCreateParam['height'];
        }
        return $this;
    } // function setParam

    /**
     * Get Image Source Param
     * @return array
     */
    public function getSourceParam()
    {
        return $this->aSourceParam;
    } // function setSource

    // ---------- Main Convert functions ---------- \\
    /**
     * Scaling Image
     * @param number $nWidth Width of scaling area
     * @param number $nHeight Height of scaling area
     * @param number $nFixRatio: 0 - not fix ratio, 1 - fix ratio (correct size), 2 - fix ratio (fill free area)
     * @param number $nBgrColor Background Color for fill free area (if $nFixRatio=2)
     * @return \core\service\image
     */
    public function scal(&$nWidth, &$nHeight, $nFixRatio = 1, $nBgrColor = 0XFFFFFF)
    {
        $nLeft = 0;
        $nTop  = 0;
        if ($nFixRatio && $nWidth && $nHeight)  {
            if ($this->nWidth/$nWidth > $this->nHeight/$nHeight) {
                // to fall into a width
                $nWidth_  = $nWidth;
                $nHeight_ = 0;
                $this->_correctSize($nWidth_, $nHeight_, $this->nWidth, $this->nHeight);
                if($nFixRatio == 2) {
                    $nTop = round(($nHeight - $nHeight_) / 2);
                } else {
                    $nHeight = $nHeight_;
                }
            } else {
                // to fall into a height
                $nWidth_  = 0;
                $nHeight_ = $nHeight;
                $this->_correctSize($nWidth_, $nHeight_, $this->nWidth, $this->nHeight);
                if($nFixRatio == 2) {
                    $nLeft  = round(($nWidth - $nWidth_) / 2);
                } else {
                    $nWidth = $nWidth_;
                }
            }
        } else {
            $this->_correctSize($nWidth, $nHeight, $this->nWidth, $this->nHeight);
            $nWidth_  = $nWidth;
            $nHeight_ = $nHeight;
        }

        if($nWidth && $nHeight && ($nWidth != $this->nWidth || $nHeight != $this->nHeight)) {
            $oSrcImg = $this->oImage;
            $this->oImage = imagecreatetruecolor($nWidth, $nHeight);
            if($nFixRatio == 2) {
                imagefilledrectangle($this->oImage, 0, 0, $nWidth, $nHeight, $nBgrColor);
            }
            imagecopyresampled($this->oImage, $oSrcImg, $nLeft, $nTop, 0, 0, $nWidth_, $nHeight_, $this->nWidth, $this->nHeight); // imagecopyresized
            $this->nWidth  = $nWidth;
            $this->nHeight = $nHeight;
        } // if convert image
        return $this;
    } // function scal

    /**
     * Relocating Image
     * @param number $nWidth Width of scaling area
     * @param number $nWidth Width of scaling area
     * @param number $nBgrColor Background Color for fill free area (if $nFixRatio=2)
     * @return \core\service\image
     */
    public function relocate(&$nWidth, &$nHeight, $nBgrColor = 0XFFFFFF)
    {
        $nTop  = round(($nHeight - $this->nHeight) / 2);
        $nLeft = round(($nWidth - $this->nWidth) / 2);

        if($nWidth && $nHeight) {
            $oSrcImg = $this->oImage;
            $this->oImage = imagecreatetruecolor($nWidth, $nHeight);
            imagefilledrectangle($this->oImage, 0, 0, $nWidth, $nHeight, $nBgrColor);
            imagecopyresampled($this->oImage, $oSrcImg, $nLeft, $nTop, 0, 0, $this->nWidth, $this->nHeight, $this->nWidth, $this->nHeight); // imagecopyresized
            //toDo: check width and height
        }
        return $this;
    } // function relocate()

    /**
     * Drawing watermark
     * @param string $sMarkerMode mode of markering
     * @param number $nOpacity opacity of markering
     * @return \core\service\image
     */
    public function markering($sMarkerMode = 'left_bottom', $nOpacity = 10)
    {
        $sPathToPic = \bootstrap::parsePath($this->aConfig['WATERMARK_PATH']);

        $params = getimagesize($sPathToPic);
        if ($params) {
            $nWidthMark  = $params[0];
            $nHeightMark = $params[1];

            switch ($sMarkerMode) {
                case 'left_bottom': {
                    $pos_x = 10;
                    $pos_y = $this->nHeight-$nHeightMark-10;
                    break;
                }
                case 'right_bottom': {
                    $pos_x = $this->nWidth-$nWidthMark-10;
                    $pos_y = $this->nHeight-$nHeightMark-10;
                    break;
                }
                case 'left_top': {
                    $pos_x = 10;
                    $pos_y = 10;
                    break;
                }
                case 'right_top': {
                    $pos_x = $this->nWidth-$nWidthMark-10;
                    $pos_y = 10;
                    break;
                }
                case 'center': {
                    $pos_x=intval($this->nWidth/2-$nWidthMark/2);
                    $pos_y=intval($this->nHeight/2-$nHeightMark/2);
                    break;
                }
                default: { // 'left_bottom'
                    $pos_x = 10;
                    $pos_y = $this->nHeight-$nHeightMark-10;
                    break;
                }
            }

            $sType = null;
            switch ($params[2]) {
                case 1: {
                    $sType = 'gif';
                    break;
                }
                case 2: {
                    $sType = 'jpeg';
                    break;
                }
                case 3: {
                    $sType = 'png';
                    break;
                }
            }
            $sFunc      = 'imagecreatefrom' . $sType;
            $oImgMarker = $sFunc($sPathToPic);
            imagecopymerge($this->oImage, $oImgMarker, $pos_x, $pos_y, 0, 0, $nWidthMark, $nHeightMark, $nOpacity);
            imagedestroy($oImgMarker);
        }
        return $this;
    } // function markering

    /**
     * Croping Image
     * @param number $nLeft Left point of cropping area
     * @param number $nTop Top point of cropping area
     * @param number $nWidth Width of cropping area
     * @param number $nHeight Height of cropping area
     * @return \core\service\image
     */
    public function crop($nLeft, $nTop, $nWidth, $nHeight)
    {
        if($nLeft < 0 || $nLeft > $this->nWidth) {
            $nLeft = 0;
        }
        if($nTop < 0 || $nTop > $this->nHeight) {
            $nTop = 0;
        }
        if($nLeft + $nWidth > $this->nWidth || $nWidth==0) {
            $nWidth  = $this->nWidth - $nLeft;
        }
        if($nTop + $nHeight > $this->nHeight || $nHeight==0) {
            $nHeight = $this->nHeight - $nTop;
        }

        if($nWidth != $this->nWidth || $nHeight != $this->nHeight) {
            $oSrcImg = $this->oImage;
            $this->oImage  = imagecreatetruecolor($nWidth, $nHeight);
            imagecopyresampled($this->oImage, $oSrcImg, 0, 0, $nLeft, $nTop, $nWidth, $nHeight, $nWidth, $nHeight); // imagecopyresized
            $this->nWidth  = $nWidth;
            $this->nHeight = $nHeight;
        }
        return $this;
    } // function crop

    /**
     * Rotate Image
     * @param number $nAngle Angle of rotate in degrees
     * @param number $nBgrColor Background color
     * @param number $nFix: 0 - not change size, 1-fix width, 2-fix height, 3-fix width and height
     * @return \core\service\image
     */
    public function rotate($nAngle, $nBgrColor = 0xFFFFFF, $nFix = 0)
    {
        while(abs($nAngle) > 360){
            $nAngle = $nAngle > 0 ? $nAngle - 360 : $nAngle + 360;
        } // while $nAngle > 360
        if($nAngle != 0) {
            $oImgTmp = imagerotate($this->oImage, $nAngle, $nBgrColor);
            if($nFix) {
                $nWidth  = ($nFix == 1 || $nFix == 3) ? $this->nWidth  : 0;
                $nHeight = ($nFix == 2 || $nFix == 3) ? $this->nHeight : 0;
                $nTempWidth  = imagesx($oImgTmp);
                $nTempHeight = imagesy($oImgTmp);
                $this->_correctSize($nWidth, $nHeight, $nTempWidth, $nTempHeight);
                $this->oImage = imagecreatetruecolor($nWidth, $nHeight);
                imagecopyresampled($this->oImage, $oImgTmp, 0, 0, 0, 0, $nWidth, $nHeight, $nTempWidth, $nTempHeight); // imagecopyresized
                $this->nWidth  = $nWidth;
                $this->nHeight = $nHeight;
            } else {
                $this->oImage  = $oImgTmp;
            } // Fix size
        } // if convert image
        return $this;
    } // function rotate

    /**
     * Image Border
     * @param number $nDepth Border width
     * @param number $nBgrColor Background color
     * @param number $nPos: 0 - outline border, 1 - inline
     * @return \core\service\image
     */
    public function border($nDepth, $nBgrColor = 0x000000, $nPos = 0)
    {
        if($nDepth > 0) {
            if(!$nPos) {
                $oSrcImg = $this->oImage;
                $nWidth  = $this->nWidth;
                $nHeight = $this->nHeight;
                $this->nWidth  += $nDepth * 2;
                $this->nHeight += $nDepth * 2;
                $this->oImage = imagecreatetruecolor($this->nWidth, $this->nHeight);
                imagecopyresampled($this->oImage, $oSrcImg, $nDepth, $nDepth, 0, 0, $nWidth, $nHeight, $nWidth, $nHeight);
            }
            imagefilledrectangle($this->oImage, 0, 0, $this->nWidth, $nDepth - 1, $nBgrColor);
            imagefilledrectangle($this->oImage, 0, 0, $nDepth - 1, $this->nHeight, $nBgrColor);
            imagefilledrectangle($this->oImage, 0, $this->nHeight - $nDepth, $this->nWidth, $this->nHeight, $nBgrColor);
            imagefilledrectangle($this->oImage, $this->nWidth - $nDepth, 0,  $this->nWidth, $this->nHeight, $nBgrColor);
        }
        return $this;
    } // function border

    /**
     * Colorize Image
     * @param number $nRedColor Adding Red color
     * @param number $nBlueColor Adding Blue color
     * @param number $nGreenColor Adding Green color
     * @return \core\service\image
     */
    public function colorize($nRedColor, $nBlueColor, $nGreenColor)
    {
        $nRedColor   = abs($nRedColor);
        $nBlueColor  = abs($nBlueColor);
        $nGreenColor = abs($nGreenColor);
        if($nRedColor <= 255 && $nBlueColor <= 255 && $nGreenColor <= 255) {
            imagefilter($this->oImage, IMG_FILTER_COLORIZE, $nRedColor, $nBlueColor, $nGreenColor);
        }
        return $this;
    } // function colorize

    /**
     * Blur Image
     * @return \core\service\image
     */
    public function blur()
    {
        imagefilter($this->oImage, IMG_FILTER_GAUSSIAN_BLUR);
        return $this;
    } // function blur

    /**
     * Grayscale Image
     * @return \core\service\image
     */
    public function grayscale()
    {
        imagefilter($this->oImage, IMG_FILTER_GRAYSCALE);
        return $this;
    } // function grayscale

    /**
     * Sepia Image
     * @return \core\service\image
     */
    public function sepia()
    {
        imagefilter($this->oImage, IMG_FILTER_GRAYSCALE);
        imagefilter($this->oImage, IMG_FILTER_COLORIZE, 50, 25, 5);
        return $this;
    } // function sepia

    /**
     * Make color transparent
     * @param integer $nColor
     * @return \core\service\image
     */
    public function transparent($nColor)
    {
        imagecolortransparent($this->oImage, $nColor);
        return $this;
    } // function transparent

    /**
     * color division into components
     * @param integer $nColor
     * @return int
     */
    public function colorDiv($nColor)
    {
        return imagecolorallocate($this->oImage, $nColor >> 16, ($nColor >> 8) & 0x00FF, $nColor & 0x0000FF);
    } // function colorDiv

    // ---------- Finish methods ---------- \\
    /**
     * Output Image Type
     * @return string
     */
    public function getType()
    {
        return $this->sCreateType;
    } // function getType

    /**
     * Output Image
     * @return array
     */
    public function getImageInfo($nTimeExpires = 0, $bWithContent = true)
    {
        $sContent = $this->getImage();
        $imgPath  = pathinfo($this->sSourcePath);

        return array(
            'sourcePath' => $this->sSourcePath,
            'content'    => $bWithContent ? $sContent : null,
            'headers' => array(
                'contentType' => 'image/' . $this->sCreateType,
                'filename'    => $imgPath['basename'],
                'length'      => strlen($sContent),
                'legthRange'  => 'bytes',
                'modified'    => time(),
                'cacheLimit'  => $nTimeExpires,
            ),
        );
    } // function getImageInfo

    /**
     * Get Image
     * @return string Image content
     */
    public function getImage()
    {
        ob_start();
        $this->saveAsNew(null);
        $sOutput = ob_get_contents();
        @ob_end_clean();
        return $sOutput;
    } // function getImage

    /**
     * Save as new Image
     * @param string $sNewFile Path to new image
     * @return \core\service\image
     */
    public function saveAsNew($sNewFile)
    {
        $sFunc = 'image' . $this->sCreateType;
        if(in_array($sFunc, array('imagejpeg', 'imagepng'))) {
            if ($sFunc == 'imagepng' && $this->nQuality > 10) {
                $this->nQuality = round($this->nQuality/10);
            }
            $sFunc($this->oImage, $sNewFile, $this->nQuality);
        } elseif($this->sCreateType && function_exists($sFunc)) {
            $sFunc($this->oImage, $sNewFile);
        } else {
            throw new fatalException($this, 'Incorrect image type (' . $this->sCreateType . ').');
        }
        return $this;
    } // function saveAsNew

    /**
     * Save and replase current Image
     * @param string $sExt Additional extantion for save old image (null - it is not saved bakup)
     * @return \core\service\image
     */
    public function saveAndReplace($sExt = 'bak')
    {
        if(!is_null($sExt)) {
            $imgPath = pathinfo($this->sSourcePath);
            rename($this->sSourcePath, $imgPath['dirname'] . '/' . $imgPath['filename'] . '.' . $sExt . '.' . $imgPath['extension']);
        }
        $this->saveAsNew($this->sSourcePath);
        return $this;
    } // function saveAndReplace

    // ======== Private/Protected methods ======== \\

    /**
     * Proportional correction  Image
     * Set $nHeight and $nWidth if it is NULL
     * @param number $nWidth Width of image
     * @param number $nHeight Height of image
     * @return \core\service\image
     */
    private function _correctSize(&$nWidth, &$nHeight, $nOldWidth, $nOldHeight)
    {
        if(!$nWidth && $nHeight && $nOldHeight) {
            $nWidth  = round($nHeight * $nOldWidth / $nOldHeight);
        } elseif (!$nHeight && $nWidth && $nOldWidth) {
            $nHeight = round($nWidth * $nOldHeight / $nOldWidth);
        }
        return $this;
    } // function _correctSize

} // class \core\service\image
?>