<?php namespace fan\core\service\template\type;
/**
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
 */
abstract class image extends base
{
    /**
     * Basic parameters: src, width, height, alt, href
     * @var array
     */
    protected $aBaseParam;

    /**
     * Get Engine List
     * @return array
     */
    public static function getEngineList()
    {
        return array('main');
    }

    /**
     * Get Auto-parse data
     * @return array
     */
    public static function getAutoParseTag()
    {
        return array(
            'img'              => array('method' => 'makeImgTag'),
            'top_signature'    => array('method' => 'makeTopSign'),
            'bottom_signature' => array('method' => 'makeBotSign'),
        );
    }

    /**
     * Set basic template data
     * @param array $aParam
     */
    public function setBaseParam($aParam)
    {
        $this->aBaseParam = $aParam;
        $this->assign('sBaseClass', isset($aParam['class']) ? $aParam['class'] : null);
        $this->assign('sImgLnk', isset($aParam['link']['full_url']) ? $aParam['link']['full_url'] : null);
    } // function setBaseData

    /**
     * Get hidden field form key
     * @return string
     * /
    public function getSpecial()
    {
        return 'Some special data';
    } // function getSpecial */

    /**
     * Make Image Tag
     * @param array $aData
     * @return string
     */
    public function makeImgTag($aData = array())
    {
        $aImage = $this->aBaseParam['img'];
        $sAttr = '';
        foreach (array('class', 'style', 'lang', 'dir', 'alt', 'title') as $k) {
            if (isset($aImage[$k])) {
                $sAttr .= ' ' . $k . '="' . $aImage[$k] . '"';
            } elseif (isset($aData[$k])) {
                $sAttr .= ' ' . $k . '="' . $aData[$k] . '"';
            }
        }
        return '<img src="' . $aImage['full_url'] . '" width="' . $aImage['width'] . '" height="' . $aImage['height'] . '"' . $sAttr . ' />';
    } // function getImgTag

    /**
     * Make Top Signature
     * @param array $aData
     * @return string
     */
    public function makeTopSign($aData = array())
    {
        return (@$this->aBaseParam['signature']['position'] == 'top') ? $this->makeTopSign($aData) : '';
    } // function makeTopSign

    /**
     * Make Bottom Signature
     * @param array $aData
     * @return string
     */
    public function makeBotSign($aData = array())
    {
        return (@$this->aBaseParam['signature']['position'] == 'bottom') ? $this->makeTopSign($aData) : '';
    } // function makeBotSign

    /**
     * Make Top Signature
     * @param array $aData
     * @return string
     */
    protected function makeSignature($aData)
    {
        return $this->aBaseParam['signature']['text'] ? '<span' . (@$aData['class'] ? ' class="' . $aData['class'] . '"' : '') . '>' . $this->aBaseParam['signature']['text'] . '</span>' : '';
    } // function makeSignature
} // class \fan\core\service\template\type\image
?>