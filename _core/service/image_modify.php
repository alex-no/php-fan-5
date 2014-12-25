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
class image_modify extends \fan\core\base\service\multi
{
    /**
     * @var \fan\core\service\image_modify[] Service's Instances
     */
    private static $aInstances;

    /**
     * @var string Path to Source Image
     */
    protected $sSourcePath;
    /**
     * @var numeric width of Source Image
     */
    protected $nSourceWidth;
    /**
     * @var numeric height of Source Image
     */
    protected $nSourceHeight;
    /**
     * @var numeric Image current width
     */
    protected $nWidth;
    /**
     * @var numeric Image current height
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
     * @var numeric Quality save of Create Image
     */
    private $nQuality = 80;

    /**
     * @var string Image type
     */
    private $sType = null;

    /**
     * @var array Type images for conversion from numeric to text
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
    protected function __construct($sSourcePath, $aCreateParam)
    {
        parent::__construct(empty(self::$aInstances));

        if (!empty($sSourcePath) || !empty($aCreateParam)) {
            $this->setSource($sSourcePath, $aCreateParam);
        }
    } // function __construct

    // ======== Static methods ======== \\

    /**
     * Get Service's instance of current service by $sSourcePath
     * If $sSourcePath isn't set - Get defaul instance
     * @param string $sSourcePath Create Path
     * @param string $aCreateParam Create parameters
     *   "type"    => 'gif', 'jpeg', 'png', 'wbmp', 'xbm'
     *   "quality" => 1-100
     *   "width"   => 1 - max
     *   "height"  => 1 - max
      * @return \fan\core\service\image_modify
     */
    public static function instance($sSourcePath = null, $aCreateParam = array(), $bSaveInstance = true)
    {
        $sName = self::checkName(get_called_class());
        if (!$bSaveInstance || !isset(self::$aInstances[$sName])) {
            $oInstance = new $sName($sSourcePath, $aCreateParam);
            if (!$bSaveInstance) {
                return $oInstance;
            }
            self::$aInstances[$sName] = $oInstance;
        }
        return self::$aInstances[$sName];
    } // function instance

    // ======== Main Interface methods ======== \\

    // ---------- Prepare functions ---------- \\
    /**
     * Prepare Image
     * Set $sSourcePath
     * Make image object from file and set input image parameters
     * @param string $sSourcePath Path to sourse image
     * @param array $aCreateParam array of image parameters (not required)
     * @return \fan\core\service\image_modify
     */
    public function setSource($sSourcePath, $aCreateParam = array())
    {
        // If "Create Parameters" exist - save them
        if (!empty($aCreateParam)) {
            $this->setParam($aCreateParam);
        }

        if (!is_null($sSourcePath)) {
            // If $sSourcePath is empty, but not NULL - get source image from config
            if(empty($sSourcePath)) {
                $sSourcePath = $this->getConfig('DEFAULT_IMAGE');
            }
            // If $sSourcePath is not empty set basic image by source
            if (!empty($sSourcePath)) {
                $sSourcePath = \bootstrap::parsePath($sSourcePath);
                if (!file_exists($sSourcePath)) {
                    $sSourcePath = \bootstrap::parsePath($this->getConfig('BASIC_PATH')) . $sSourcePath;
                }
                $this->sSourcePath = $sSourcePath;

                // Check - file exists and readable
                if(is_readable($sSourcePath)) {
                    if(!exif_imagetype($sSourcePath)) {
                        throw new fatalException($this, 'Incorrect image file format "' . $sSourcePath . '".');
                    }
                    $this->aSourceParam = getimagesize($sSourcePath);
                } else {
                    throw new fatalException($this, 'Image-file "' . $this->sSourcePath . '" isn\'t ' . (file_exists($sSourcePath) ? 'readable.' : 'exist.'));
                }

                // Set parameters by source
                $sType = $this->aConvType[$this->aSourceParam[2]];
                if (is_null($this->sType)) {
                    $this->sType = $sType;
                }

                $sFunc = 'imagecreatefrom' . $sType;
                $this->oImage = $sFunc($sSourcePath);

                $this->nSourceWidth = $this->aSourceParam[0];
                if (empty($this->nWidth)) {
                    $this->nWidth = $this->nSourceWidth;
                }
                $this->nSourceHeight = $this->aSourceParam[1];
                if (empty($this->nHeight)) {
                    $this->nHeight = $this->nSourceHeight;
                }
            }
        }
        // If $sSourcePath is not set - create blank image
        if (empty($sSourcePath)) {
            if($this->nSourceWidth < 1 || $this->nSourceHeight < 1) {
                throw new fatalException($this, 'Image size doesn\'t set (' . $this->nSourceWidth . 'x' . $this->nSourceHeight . ').');
            }
            $this->oImage = imagecreatetruecolor($this->nSourceWidth, $this->nSourceHeight);
        }

        return $this;
    } // function setSource

    /**
     * Set parameter of Image
     * @param array $aParam Path to sourse image
     * @return \fan\core\service\image_modify
     */
    public function setParam($aParam)
    {
        if (isset($aParam['type'])) {
            $this->sType = $aParam['type'];
        }
        if (isset($aParam['quality'])) {
            $this->nQuality = $aParam['quality'] < 1 || $aParam['quality'] > 100 ? 80 : $aParam['quality'];
        }
        if (isset($aParam['width'])) {
            $this->nWidth = $aParam['width'];
            if (empty($this->nSourceWidth)) {
                $this->nSourceWidth = $this->nWidth;
            }
        }
        if (isset($aParam['height'])) {
            $this->nHeight = $aParam['height'];
            if (empty($this->nSourceHeight)) {
                $this->nSourceHeight = $this->nHeight;
            }
        }
        return $this;
    } // function setParam

    /**
     * Make color transparent
     * @param integer|string|array $mColor
     * @return \fan\core\service\image_modify
     */
    public function setTransparent($mColor)
    {
        imagecolortransparent($this->oImage, $this->adaptColor($mColor));
        return $this;
    } // function setTransparent

    /**
     * Get Image Source Param
     * @return array
     */
    public function getSourceParam()
    {
        return $this->aSourceParam;
    } // function setSource

    /**
     * Getting Image Width
     */
    public function getWidth()
    {
        return $this->nWidth;
    } // function getWidth
    /**
     * Getting Image Width
     */
    public function getSourceWidth()
    {
        return $this->nSourceWidth;
    } // function getSourceWidth
    /**
     * Getting Image Heigth
     */
    public function getHeigth()
    {
        return $this->nHeight;
    } // function getHeigth
    /**
     * Getting Image Heigth
     */
    public function getSourceHeigth()
    {
        return $this->nSourceHeight;
    } // function getSourceHeigth

    // ---------- Main Convert functions ---------- \\

    /**
     * Relocating Image
     * @param numeric $nWidth Width of scaling area
     * @param numeric $nHeight Height of scaling area
     * @param integer|string|array $nBgrColor Background Color for fill free area (if $nFixRatio=2)
     * @return \fan\core\service\image_modify
     */
    public function relocate($nWidth = null, $nHeight = null, $nBgrColor = 0XFFFFFF)
    {
        if (is_null($nWidth)) {
            $nWidth = $this->nWidth;
        }
        if (is_null($nHeight)) {
            $nHeight = $this->nHeight;
        }
        if($nWidth < 1 || $nHeight < 1) {
            throw new fatalException($this, 'Image size doesn\'t set (' . $nWidth . 'x' . $nHeight . ').');
        }

        $nLeft = round(($nWidth - $this->nWidth) / 2);
        $nTop  = round(($nHeight - $this->nHeight) / 2);

        $aPosition = array(
            'dstX' => $nLeft,
            'dstY' => $nTop,
            'srcX' => 0,
            'srcY' => 0,
            'dstW' => $nWidth,
            'dstH' => $nHeight,
            'srcW' => $this->nWidth,
            'srcH' => $this->nHeight,
        );
        $this->_replaceImage($nWidth, $nHeight, $aPosition, null, $nBgrColor);
        return $this;
    } // function relocate

    /**
     * Scaling Image
     * If $nFixRatio has value 1, so $nWidth or $nHeight can be chaned
     * @param numeric $nWidth Width of scaling area
     * @param numeric $nHeight Height of scaling area
     * @param numeric $nFixRatio : 0 - not fix ratio, 1 - fix ratio (correct size), 2 - fix ratio (fill free area)
     * @param integer|string|array $mBgrColor Background Color for fill free area (if $nFixRatio=2)
     * @return \fan\core\service\image_modify
     */
    public function scal(&$nWidth, &$nHeight, $nFixRatio = 1, $mBgrColor = 0xFFFFFF)
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
            $aPosition = array(
                'dstX' => $nLeft,
                'dstY' => $nTop,
                'srcX' => 0,
                'srcY' => 0,
                'dstW' => $nWidth_,
                'dstH' => $nHeight_,
                'srcW' => $this->nWidth,
                'srcH' => $this->nHeight,
            );
            $this->_replaceImage($nWidth, $nHeight, $aPosition, null, $nFixRatio == 2 ? $mBgrColor : null);
        } // if convert image
        return $this;
    } // function scal

    /**
     * Croping Image
     * @param numeric $nLeft Left point of cropping area
     * @param numeric $nTop Top point of cropping area
     * @param numeric $nWidth Width of cropping area
     * @param numeric $nHeight Height of cropping area
     * @return \fan\core\service\image_modify
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
            $aPosition = array(
                'dstX' => 0,
                'dstY' => 0,
                'srcX' => $nLeft,
                'srcY' => $nTop,
                'dstW' => $nWidth,
                'dstH' => $nHeight,
                'srcW' => $nWidth,
                'srcH' => $nHeight,
            );
            $this->_replaceImage($nWidth, $nHeight, $aPosition);
        }
        return $this;
    } // function crop

    /**
     * Rotate Image
     * @param numeric $nAngle Angle of rotate in degrees
     * @param integer|string|array $mBgrColor Background color
     * @param numeric $nFix : 0 - not change size, 1-fix width, 2-fix height, 3-fix width and height
     * @return \fan\core\service\image_modify
     */
    public function rotate($nAngle, $mBgrColor = 0xFFFFFF, $nFix = 0)
    {
        while(abs($nAngle) > 360){
            $nAngle = $nAngle > 0 ? $nAngle - 360 : $nAngle + 360;
        } // while $nAngle > 360
        if($nAngle != 0) {
            $oImgTmp = imagerotate($this->oImage, $nAngle, $this->adaptColor($mBgrColor));
            if($nFix) {
                $nWidth  = ($nFix == 1 || $nFix == 3) ? $this->nWidth  : 0;
                $nHeight = ($nFix == 2 || $nFix == 3) ? $this->nHeight : 0;
                $nTempWidth  = imagesx($oImgTmp);
                $nTempHeight = imagesy($oImgTmp);
                $this->_correctSize($nWidth, $nHeight, $nTempWidth, $nTempHeight);
                $aPosition = array(
                    'dstX' => 0,
                    'dstY' => 0,
                    'srcX' => 0,
                    'srcY' => 0,
                    'dstW' => $nWidth,
                    'dstH' => $nHeight,
                    'srcW' => $nTempWidth,
                    'srcH' => $nTempHeight,
                );
                $this->_replaceImage($nWidth, $nHeight, $aPosition, $oImgTmp);
            } else {
                $this->oImage = $oImgTmp;
            } // Fix size
        } // if convert image
        return $this;
    } // function rotate

    /**
     * Image Border
     * @param numeric $nDepth Border width
     * @param integer|string|array $mBrdColor Border color
     * @param bolean $bInline : false - outline border, true - inline
     * @return \fan\core\service\image_modify
     */
    public function border($nDepth, $mBrdColor = 0x000000, $bInline = false)
    {
        if($nDepth > 0) {
            if(!$bInline) {
                $oSrcImg = $this->oImage;
                $nWidth  = $this->nWidth;
                $nHeight = $this->nHeight;
                $this->nWidth  += $nDepth * 2;
                $this->nHeight += $nDepth * 2;
                $this->oImage = imagecreatetruecolor($this->nWidth, $this->nHeight);
                imagecopyresampled($this->oImage, $oSrcImg, $nDepth, $nDepth, 0, 0, $nWidth, $nHeight, $nWidth, $nHeight);
            }
            $nColor = $this->adaptColor($mBrdColor);
            imagefilledrectangle($this->oImage, 0, 0, $this->nWidth, $nDepth - 1, $nColor);
            imagefilledrectangle($this->oImage, 0, 0, $nDepth - 1, $this->nHeight, $nColor);
            imagefilledrectangle($this->oImage, 0, $this->nHeight - $nDepth, $this->nWidth, $this->nHeight, $nColor);
            imagefilledrectangle($this->oImage, $this->nWidth - $nDepth, 0,  $this->nWidth, $this->nHeight, $nColor);
        }
        return $this;
    } // function border

    /**
     * Colorize Image
     * @param integer|string|array $mColor
     * @return \fan\core\service\image_modify
     */
    public function colorize($mColor)
    {
        $nColor = $this->adaptColor($mColor);
        imagefilter($this->oImage, IMG_FILTER_COLORIZE, $nColor >> 16, ($nColor >> 8) & 0xFF, $nColor & 0xFF);
        return $this;
    } // function colorize

    /**
     * Blur Image
     * @return \fan\core\service\image_modify
     */
    public function blur()
    {
        imagefilter($this->oImage, IMG_FILTER_GAUSSIAN_BLUR);
        return $this;
    } // function blur

    /**
     * Grayscale Image
     * @return \fan\core\service\image_modify
     */
    public function grayscale()
    {
        imagefilter($this->oImage, IMG_FILTER_GRAYSCALE);
        return $this;
    } // function grayscale

    /**
     * Sepia Image
     * @return \fan\core\service\image_modify
     */
    public function sepia()
    {
        imagefilter($this->oImage, IMG_FILTER_GRAYSCALE);
        imagefilter($this->oImage, IMG_FILTER_COLORIZE, 50, 25, 5);
        return $this;
    } // function sepia

    /**
     * Drawing watermark
     * @param string $sMarkerMode mode of markering
     * @param numeric $nOpacity opacity of markering
     * @return \fan\core\service\image_modify
     */
    public function markering($sMarkerMode = 'left_bottom', $nOpacity = 10)
    {
        $sPathToPic = \bootstrap::parsePath($this->oConfig['WATERMARK_PATH']);

        $aParam = getimagesize($sPathToPic);
        if ($aParam) {
            $nWidthMark  = $aParam[0];
            $nHeightMark = $aParam[1];

            switch ($sMarkerMode) {
                case 'left_bottom': {
                    $nPosX = 10;
                    $nPosY = $this->nHeight - $nHeightMark - 10;
                    break;
                }
                case 'right_bottom': {
                    $nPosX = $this->nWidth  - $nWidthMark  - 10;
                    $nPosY = $this->nHeight - $nHeightMark - 10;
                    break;
                }
                case 'left_top': {
                    $nPosX = 10;
                    $nPosY = 10;
                    break;
                }
                case 'right_top': {
                    $nPosX = $this->nWidth - $nWidthMark - 10;
                    $nPosY = 10;
                    break;
                }
                case 'center': {
                    $nPosX = intval($this->nWidth  / 2 - $nWidthMark  / 2);
                    $nPosY = intval($this->nHeight / 2 - $nHeightMark / 2);
                    break;
                }
                default: { // 'left_bottom'
                    $nPosX = 10;
                    $nPosY = $this->nHeight - $nHeightMark - 10;
                    break;
                }
            }

            $sType = null;
            switch ($aParam[2]) {
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
            imagecopymerge($this->oImage, $oImgMarker, $nPosX, $nPosY, 0, 0, $nWidthMark, $nHeightMark, $nOpacity);
            imagedestroy($oImgMarker);
        }
        return $this;
    } // function markering

    /**
     * Adapt color data to image functions
     * @param integer|string|array $mColor
     * @return int
     */
    public function adaptColor($mColor)
    {
        if (is_array($mColor)) {
            $aColor = array('r' => 0, 'g' => 0, 'b' => 0);
            foreach ($aColor as $k1 => &$v) {
                $k2 = strtoupper($k1);
                if (isset($mColor[$k1])) {
                    $v = $mColor[$k1];
                } elseif (isset($mColor[$k2])) {
                    $v = $mColor[$k2];
                } else {
                    trigger_error('Color key ' . $k2 . ' isn\'t defined.');
                }
                if (is_string($v)) {
                    $v = hexdec($v);
                }
                $v = abs(round($v)) % 0xFF;
            }
        } else {
            if (is_string($mColor)) {
                $mColor = hexdec($mColor);
            }
            $v = abs(round($mColor)) % 0xFFFFFF;
            $aColor = array(
                'r' => $mColor >> 16,
                'g' => ($mColor >> 8) & 0xFF,
                'b' => $mColor & 0xFF
            );
        }
        $nResult = imagecolorallocate($this->oImage, $aColor['r'], $aColor['g'], $aColor['b']);
        if ($nResult === false) {
            trigger_error('Incorrect value of color ' . var_export($mColor, true));
            return 0;
        }
        return $nResult;
    } // function adaptColor

    // ---------- Finish methods ---------- \\
    /**
     * Output Image Type
     * @return string
     */
    public function getType()
    {
        return $this->sType;
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
            'type'       => $this->sType,
            'headers' => array(
                'contentType' => 'image/' . $this->sType,
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
     * @return \fan\core\service\image_modify
     */
    public function saveAsNew($sNewFile)
    {
        $sFunc = 'image' . $this->sType;
        if(in_array($sFunc, array('imagejpeg', 'imagepng'))) {
            if ($sFunc == 'imagepng' && $this->nQuality > 10) {
                $this->nQuality = round($this->nQuality/10);
            }
            $sFunc($this->oImage, $sNewFile, $this->nQuality);
        } elseif($this->sType && function_exists($sFunc)) {
            $sFunc($this->oImage, $sNewFile);
        } else {
            throw new fatalException($this, 'Incorrect image type (' . $this->sType . ').');
        }
        return $this;
    } // function saveAsNew

    /**
     * Save and replase current Image
     * @param string $sExt Additional extantion for save old image (null - it is not saved bakup)
     * @return \fan\core\service\image_modify
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
     * Get value of coordinate
     * @param array $aCoord
     * @param string $sKey
     * @param numeric $nDefault
     * @return numeric
     */
    protected function _getCoord($aCoord, $sKey, $nDefault = 0)
    {
        if (!isset($aCoord[$sKey])) {
            $aTrace = debug_backtrace();
            trigger_error(
                    'Coordinate isn\'t set for ' . $sKey . '<br />' .
                    (isset($aTrace[1]['file']) ? 'file "<nobr><b>'  . $aTrace[1]['file'] . '</b></nobr>", ' : 'No file') .
                    (isset($aTrace[1]['line']) ? 'line <b>'         . $aTrace[1]['line'] . '</b>.' : ''),
                    E_USER_ERROR
            );
        }
        return array_val($aCoord, $sKey, $nDefault);
    } // function _getCoord

    /**
     * Get Difference beetwim two coordinates
     * @param array $aCoord
     * @param string $sKey1
     * @param string $sKey2
     * @return numeric
     */
    protected function _getCoordDiff($aCoord, $sKey1, $sKey2)
    {
        return abs($this->_getCoord($aCoord, $sKey1) - $this->_getCoord($aCoord, $sKey2));
    } // function _getCoordDiff

    /**
     * Replace base Image to another one
     * @param numeric $nWidth
     * @param numeric $nHeight
     * @param array $aPosition
     * @param resource $oSrcImg
     * @param integer|string|array|null $mBgrColor
     * @return \fan\core\service\image_modify
     */
    protected function _replaceImage($nWidth, $nHeight, $aPosition, $oSrcImg = null, $mBgrColor = null)
    {
        if (is_null($oSrcImg)) {
            $oSrcImg = $this->oImage;
        }
        $this->oImage = imagecreatetruecolor($nWidth, $nHeight);
        if (!is_null($mBgrColor)) {
            imagefilledrectangle($this->oImage, 0, 0, $nWidth, $nHeight, $this->adaptColor($mBgrColor));
        }
        imagecopyresampled($this->oImage, $oSrcImg, $aPosition['dstX'], $aPosition['dstY'], $aPosition['srcX'], $aPosition['srcY'], $aPosition['dstW'], $aPosition['dstH'], $aPosition['srcW'], $aPosition['srcH']);
        $this->nWidth  = $nWidth;
        $this->nHeight = $nHeight;
        return $this;
    } // function _replaceImage

    /**
     * Proportional correction  Image
     * Set $nHeight and $nWidth if it is NULL
     * @param numeric $nWidth Width of image
     * @param numeric $nHeight Height of image
     * @return \fan\core\service\image_modify
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

} // class \fan\core\service\image_modify
?>