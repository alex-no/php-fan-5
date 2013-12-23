<?php namespace core\base\model\spec_file\image;
/**
 * Row of special file
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
 * @version of file: 05.003 (23.12.2013)
 * @abstract
 */
abstract class row extends \core\base\model\spec_file\row
{

    /**
     * @var template_image Object of Image-template
     */
    private static $oTemplate = array();

    /**
     * Get template of image
     * @return template_image
     */
    private static function getTemplate($sEntityName)
    {
        if (!isset(self::$oTemplate[$sEntityName])) {
            $sTemplatePath = \bootstrap::parsePath(ge($sEntityName)->getConfig('TEMPLATE_PATH', '{PROJECT}/special_templates/show_image.tpl'));
            self::$oTemplate[$sEntityName] = service('template')->get($sTemplatePath, 'template_image');
        }
        return self::$oTemplate[$sEntityName];
    }// function getTemplate

    /**
     * Set file from form
     * @param $sFormKey string
     * @param $aAddKeys array
     * @param $sDecription string
     * @param $sAlt string
     * @return boolean true - if file is stored successful
     */
    public function setFormFile($sFormKey, $aAddKeys = array(), $sDecription = '', $sAlt = '')
    {
        if($this->getEntityFile()->setFormFile($sFormKey, $aAddKeys, 'image', $sDecription)) {
            return $this->saveImage($sAlt);
        }
        return false;
    }// function getFormFile

    /**
     * Set file from URL
     * @param $sUrl string
     * @param $sDecription string
     * @param $sAlt string
     * @return boolean true - if file is stored successful
     */
    public function setUrlFile($sUrl, $sDecription = '', $sAlt = '')
    {
        if($this->getEntityFile()->setUrlFile($sUrl, 'image', $sDecription)) {
            return $this->saveImage($sAlt);
        }
        return false;
    }// function setUrlFile

    /**
     * Set file from URL
     * @param $sSrcPath string
     * @param $sDecription string
     * @param $sAlt string
     * @param $bDeleteOrigin boolean
     * @return boolean true - if file is stored successful
     */
    public function setLocalFile($sSrcPath, $sDecription = '', $sAlt = '', $sName = null, $bDeleteOrigin = false)
    {
        $aImgInf = @getimagesize($sSrcPath);
        if($aImgInf) {
            if($this->getEntityFile()->setLocalFile($sSrcPath, 'image', $aImgInf['mime'], $sDecription, $sName, $bDeleteOrigin)) {
                return $this->saveImage($sAlt);
            }
        }
        return false;
    } // function setLocalFile

    /**
     * Get image data
     * @return array
     */
    public function getImageData()
    {
        $aRet = $this->getFields();
        unset($aRet[$this->getEntity()->getDescription()->getPrimeryKey()]);
        $aRet['id']          = $this->getId();
        $aRet['description'] = $this->getEntityFile()->get_description();
        $aRet['src_name']    = $this->getEntityFile()->get_src_name();
        return $aRet;
    } // function getImageData

    /**
     * Rotate Image
     * @param numeric $nAngle Angle of rotate in degrees
     * @param number $nBgrColor Background color
     * @param number $nFix: 0 - not change size, 1-fix width, 2-fix height, 3-fix width and height
     */
    public function rotateImage($nAngle, $nBgrColor = 0xFFFFFF, $nFix = 0)
    {
        $oSI = service('image', $this->getEntityFile()->getFilePath());
        $oSI->rotate($nAngle, $nBgrColor, $nFix);
        $oSI->saveAndReplace(null);
        $this->saveImage();
    }// function rotateImage

    /**
     * Get Advanced Img-tag
     * Allowed Types: 'img', 'nail', 'link', 'blowup1', 'blowup2'
     * $aParam = array(
     *     'class' => 'css_class_for_main_tag',
     *     'img' => array(
     *         'url_prefix' => 'url_prefix_value',
     *         'url_suffix' => 'url_suffix_value',
     *         'full_url'   => 'full_image_url',
     *         'width'      => 'width_value',
     *         'height'     => 'height_value',
     *         'alt'        => 'alternative_text',
     *         'title'      => 'title',
     *         'class'      => 'css_class_for_img_tag',
     *     ),
     *     'link' => array(
     *         'url_prefix' => 'url_prefix_value',
     *         'url_suffix' => 'url_suffix_value',
     *         'full_url'   => 'full_link_url',
     *         'target'     => 'target_value',
     *         'class'      => 'css_class_for_link_tag',
     *     ),
     *     'signature' => array(
     *         'position' => 'top|bottom|none',
     *     ),
     * )
     * @param string $sType
     * @param array $aParam
     * @return string
     */
    public function advGetImgTag($sType, $aParam)
    {
        $sType = strtolower($sType);
        if (!$this->checkIsLoad() || !in_array($sType, array('img', 'nail', 'link', 'blowup1', 'blowup2'))) {
            return null;
        }

        // ----- Set img-param ----- \\
        if ($sType == 'img') {
            $this->setMainImgParam($aParam);
        } else {
            $this->setUrl($aParam['img'], 'nail', '/nail.php?id=', '&anp;w={width}&anp;h={height}');
            $this->resizeImage($aParam['img']);
            $this->setMainImgParam($aParam, false);
        }


        // ----- Set link-param ----- \\
        if (substr($sType, 0, 6) == 'blowup') {
            $this->setUrl($aParam['link'], 'blowup', '/blowup/id-', '.html');
            if (!isset($aTmp['target'])) {
                $aTmp['target'] = '_blank';
            }
        }

        // ----- Make signature-tag ----- \\
        if(!isset($aParam['signature']['position'])) {
            $aParam['signature']['position'] = $this->getConfig('signature_pos', 'none');
        }

        return $this->fetchHtml($sType, $aParam);
    } // function advGetImgTag

    /**
     * Get Img-tag
     * @return array
     */
    public function getImgTag($aParam = null)
    {
        if ($this->checkIsLoad()) {
            $this->setMainImgParam($aParam);
            return $this->fetchHtml('simple', $aParam);
        }
        return null;
    } // function getImgTag

    /**
     * Set file from form
     *
     * @return boolean true - if file is stored successful
     */
    protected function saveImage($sAlt = null)
    {
        $aImgData = @getimagesize($this->getEntityFile()->getFilePath());
        if($aImgData) {
            $this->setId($this->getEntityFile()->getId());
            $this->set_width($aImgData[0]);
            $this->set_height($aImgData[1]);
            $this->set_img_type($aImgData[2]);
            if (!is_null($sAlt)) {
                $this->set_alt($sAlt);
            }
            $this->save();
            return true;
        }
        return false;
    } // function getFormFile

    /**
     * Check access for read file
     * @return boolean true - if access enable
     */
    public function checkAccess()
    {
        return $this->getEntityFile()->checkAccess();
    } // function checkAccess

    /**
     * Chech Is current member Owner
     * @return boolean true - if member is owner
     */
    public function checkIsOwner()
    {
        return $this->getEntityFile()->checkIsOwner();
    } // function checkIsOwner


    /**
     * fetch HTML-code of image
     * @return string
     */
    protected function fetchHtml($sType, $aParam)
    {
        $oTemplate = self::getTemplate(get_class($this));
        $oTemplate->setBaseParam($aParam);
        $oTemplate->assign('img_type', $sType);
        return $oTemplate->fetch();
    } // function fetchHtml

    /**
     * Set parameter if it isn't set
     * @param array $aParam
     * @param string $sKey
     * @param mixed $mVal
     */
    protected function setUrl(&$aParam, $sKey, $sDefPrefix, $sDefSuffix)
    {
        if (!@$aParam['full_url']) {
            $aConf = $this->getConfig($sKey);
            $this->setParam($aParam, 'url_prefix', (isset($aConf['url_prefix']) ? $aConf['url_prefix'] : $sDefPrefix));
            $this->setParam($aParam, 'url_suffix', (isset($aConf['url_prefix']) ? $aConf['url_suffix'] : $sDefSuffix));
            $aParam['full_url'] = $this->checkIsLoad() ? $aParam['url_prefix'] . $this->getId() . $aParam['url_suffix'] : '';
        }
    } // function setUrl

    /**
     * Set parameter if it isn't set
     * @param array $aParam
     * @param string $sKey
     * @param mixed $mVal
     */
    protected function setParam(&$aParam, $sKey, $mVal)
    {
        if (!isset($aParam[$sKey])) {
            $aParam[$sKey] = $mVal;
        }
    } // function setParam


    /**
     * Set Main Image Parameters
     * @param array $aParam
     */
    protected function setMainImgParam(&$aParam, $bFull = true)
    {
        if ($this->checkIsLoad()) {
            $v = &$aParam['img'];
            if ($bFull) {
                $this->setUrl($v, 'img', '/file.php?id=', '');
                $this->setParam($v, 'width', $this->get_width());
                $this->setParam($v, 'height', $this->get_height());
            }
            $this->setParam($v, 'alt', $this->get_alt());
            $this->setParam($v, 'title', $this->get_alt());
        }
    } // function setMainImgParam

    /**
     * Set image size
     * @link array $aImgParam
     * @param array $aParam
     */
    protected function resizeImage(&$aParam)
    {
        $bEnableIncrease = false; // ToDo: move it to config if need enable it

        $nWidth   = $this->get_width();  // Set width  (default: source width)
        $nHeight  = $this->get_height(); // Set height (default: source height)
        $nWidth_  = intval(@$aParam['width']);  // Requested max width
        $nHeight_ = intval(@$aParam['height']); // Requested max height
        if ($nWidth_ || $nHeight_) {
             if ($nHeight_ && $nWidth_) { // Select determinator
                if ($nWidth_ / $nWidth <= $nHeight_ / $nHeight) {
                    $nHeight_ = 0;
                } else {
                    $nWidth_  = 0;
                }
            }
            if (!$nWidth_) {
                if ($nHeight_ < $nHeight || $bEnableIncrease) { // Determine by Height
                    $nWidth  = round($nHeight_ * $nWidth / $nHeight);
                    $nHeight = $nHeight_;
                }
            } elseif ($nWidth_ < $nWidth || $bEnableIncrease) { // Determine by Width
                $nHeight = round($nWidth_ * $nHeight / $nWidth);
                $nWidth  = $nWidth_;
            }
        }

        foreach (array('url_suffix', 'full_url') as $k) {
            if (isset ($aParam[$k])) {
                $aParam[$k] = str_replace(array('{width}', '{height}'), array($nWidth, $nHeight), $aParam[$k]);
            }
        }

        $aParam['width']  = $nWidth;
        $aParam['height'] = $nHeight;
    } // function resizeImage

} // class \core\base\model\spec_file\image\entity
?>