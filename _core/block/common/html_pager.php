<?php namespace fan\core\block\common;
/**
 * Pager base class
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
class html_pager extends \fan\core\block\base
{
    /**
     * Name of block
     * @var \fan\core\service\pager
     */
    protected $oPager = '';

    /**
     * Init pager
     */
    public function init()
    {
    } // function init


    /**
     * Get page url
     * @return string
     */
    public function getPageUri($iPage)
    {
        return $this->oPager->getPageUri($iPage);
    } // function getPageUri

    /**
     *
     * @return string
     */
    public function getEmbeddedForm()
    {
        return '';
    } // function getEmbeddedForm

    /**
     * Get pager code [usualy for additional pager(s)]
     * @param string $sType - type of pager template
     * @return string HTML code
     */
    public function _getPageGroup($iPageQtt, $iCurPage)
    {
        $aQttLimit = $this->getMeta('qttLimit', array('startEnd' => 2, 'middle' => 5), true);
        $iMidlHalf = floor($aQttLimit['middle'] / 2);
        $iStEnd    = $aQttLimit['startEnd'];
        if ($iPageQtt <= $iStEnd * 2 + $aQttLimit['middle']) {
            $aPages = array(
               range(1, $iPageQtt),
           );
        } elseif ($iCurPage <= $iStEnd + $iMidlHalf + 1) {
            $aPages = array(
                range(1, $iCurPage + $iMidlHalf),
                range($iPageQtt - $iStEnd + 1, $iPageQtt)
            );
        } elseif ($iPageQtt - $iCurPage <= $iStEnd + $iMidlHalf) {
            $aPages = array(
                range(1, $iStEnd),
                range($iCurPage - $iMidlHalf, $iPageQtt)
            );
        } else {
            $aPages = array(
                range(1, $iStEnd),
                range($iCurPage - $iMidlHalf, $iCurPage + $iMidlHalf),
                range($iPageQtt - $iStEnd + 1, $iPageQtt)
            );
        }
        $iStart = $iCurPage - $iMidlHalf;
        $iEnd = $iCurPage + $iMidlHalf;

        return array(
            'aPagesNL' => range(
                $iStart <= 0 ? 1 : $iStart,
                $iEnd >= $iPageQtt ? $iPageQtt : $iEnd
            ),
            'aPages'   => $aPages,
        );
    } // function getPagerCode

    /**
     * Method for redefine in child class
     * Method if run after construct operation
     */
    protected function _postCreate()
    {
        $this->oPager = service('pager', $this->getContainer());
        // ToDo: Define quntifire there
    } // function _postCreate

    /**
     * Method for redefine in child class
     * Method if run before output-view operation
     */
    protected function _preOutput()
    {
        $iPageQtt   = $this->oPager->getPageQtt();
        $iCurPage   = $this->oPager->getPageNum();
        $aPageGroup = $this->_getPageGroup($iPageQtt, $iCurPage);

        $this->view->iPageQtt      = $iPageQtt;
        $this->view->iCurrentPage  = $iCurPage;
        $this->view->aPages        = $aPageGroup['aPages'];
        $this->view->aPagesNL      = $aPageGroup['aPagesNL'];
        $this->view->showIfOnePage = $this->getMeta(array('quantifier', 'allow')) ? true : false;
        $this->view->tplType       = $this->getMeta('tplType', 'references');
    } // function _preOutput

} // class \fan\core\block\common\html_pager
?>