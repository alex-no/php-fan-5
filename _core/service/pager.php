<?php namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * Captcha manager service
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
class pager extends \fan\core\base\service\multi
{
    /**
     * Service's Instances
     * @var \fan\core\service\pager[]
     */
    private static $aInstances = array();

    /**
     * Form Id
     * @var \fan\core\block\base
     */
    private $oBlock;

    /**
     * Page nummber
     * @var numeric
     */
    protected $nPageNum = null;
    /**
     * Total quantity of pages
     * @var numeric
     */
    protected $nPageQtt = null;

    /**
     * Quantity items per page
     * @var numeric
     */
    protected $nItemPerPage = null;
    /**
     * Total quantity of items
     * @var numeric
     */
    protected $nItemQtt = null;

    /**
     * Service's constructor
     * @param type $oBlock
     */
    protected function __construct(\fan\core\block\base $oBlock)
    {
        parent::__construct(true);
        $this->oBlock = $oBlock;
    } // function __construct

    // ======== Static methods ======== \\
    /**
     *
     * @param string|\fan\core\block\base $mBlock
     * @return \fan\core\service\pager
     */
    public static function instance($mBlock)
    {
        if (is_string($mBlock)) {
            $mBlock = service('tab')->getTabBlock($mBlock);
        } elseif (!is_object($mBlock) || !($mBlock instanceof \fan\core\block\base)) {
            throw new \fan\core\exception\error500('Incorect call service pager. Please point block of data or its name.');
        }

        $sName = $mBlock->getBlockName();
        if (!isset(self::$aInstances[$sName])) {
            self::$aInstances[$sName] = new self($mBlock);
        }
        return self::$aInstances[$sName];
    } // function instance

    // ======== Main Interface methods ======== \\

    /**
     * Set current page number
     * @param numeric $nPageNum
     * @param boolean $bForce
     * @return \fan\core\service\pager
     */
    public function setPageNum($nPageNum, $bForce = false)
    {
        $nPageNum = round($nPageNum);
        if ($nPageNum < 1) {
            $nPageNum = 1;
        }
        if (!is_null($this->nPageQtt) && $nPageNum > $this->nPageQtt) {
            if ($bForce) {
                $this->nPageQtt = $nPageNum;
            } else {
                $nPageNum = $this->nPageQtt;
            }
        }
        $this->nPageNum = $nPageNum;
        return $this;
    } // function setPageNum
    /**
     * Get current page number
     * @return numeric
     */
    public function getPageNum()
    {
        return $this->nPageNum;
    } // function getPageNum

    /**
     * Set quantity of total pages
     * @param numeric $nPageQtt
     * @param boolean $bForce
     * @return \fan\core\service\pager
     */
    public function setPageQtt($nPageQtt, $bForce = true)
    {
        $nPageQtt = round($nPageQtt);
        if ($nPageQtt < 1) {
            $nPageQtt = 1;
        }
        if (!is_null($this->nPageNum) && $nPageQtt < $this->nPageNum) {
            if ($bForce) {
                $this->nPageNum = $nPageQtt;
            } else {
                $nPageQtt = $this->nPageNum;
            }
        }
        $this->nPageQtt = $nPageQtt;
        return $this;
    } // function setPageQtt
    /**
     * Get quantity of total pages
     * @return numeric
     */
    public function getPageQtt()
    {
        return $this->nPageQtt;
    } // function getPageQtt

    /**
     * Set quantity of items per page
     * @param numeric $nItemPerPage
     * @return \fan\core\service\pager
     */
    public function setItemPerPage($nItemPerPage)
    {
        $nItemPerPage = round($nItemPerPage);
        if ($nItemPerPage < 1) {
            $nItemPerPage = $this->getConfig('DEFAULT_ITEM_PER_PAGE', 10);
        }
        $this->nItemPerPage = $nItemPerPage;
        return $this;
    } // function setItemPerPage
    /**
     * Get quantity of items per page
     * @return numeric
     */
    public function getItemPerPage()
    {
        return is_null($this->nItemPerPage) ? $this->getConfig('DEFAULT_ITEM_PER_PAGE', 10) : $this->nItemPerPage;
    } // function getItemPerPage

    public function setItemQtt($nItemQtt)
    {
        $this->nItemQtt = round($nItemQtt);
        return $this;
    } // function setItemQtt
    /**
     * Get quantity of items per page
     * @return numeric
     */
    public function getItemQtt()
    {
        return $this->nItemQtt;
    } // function getItemQtt

    /**
     * Get Items By Parameters (entity_key and sql_key geg from meta-data)
     * @param mixed $mParam
     * @param string $sOrderBy
     * @return \fan\core\base\model\rowset
     * @throws fatalException
     */
    public function getItemsByParam($mParam = array(), $sOrderBy = '')
    {
        $oMeta = $this->oBlock->getMeta('pager');
        if (!is_object($oMeta) || empty($oMeta['entity_key'])) {
            throw new fatalException($this, 'Entity key is not set.');
        }
        $oEtt    = ge($oMeta['entity_key']);
        $sSqlKey = $oMeta['sql_key'];
        return $this->getItemsByKey($oEtt, $sSqlKey, $mParam, $sOrderBy);
    } // function getItemsByParam

    /**
     * Get Items By Entity-key, SQL-key and Parameters
     * @param string|\fan\core\base\model\entity $mEtt
     * @param string $sSqlKey
     * @param mixed $mParam
     * @param string $sOrderBy
     * @return \fan\core\base\model\rowset
     */
    public function getItemsByKey($mEtt, $sSqlKey = '', $mParam = array(), $sOrderBy = '')
    {
        $oEtt = is_object($mEtt) && $mEtt instanceof \fan\core\base\model\entity ? $mEtt : ge($mEtt);

        $this->_definePageNum()
                ->_defineItemPerPage()
                ->_countItemByEtt($mParam, $oEtt, $sSqlKey)
                ->_definePageQtt();

        $nQtt    = $this->getItemPerPage();
        $nOffset = ($this->getPageNum() - 1) * $nQtt;
        $oItems  = empty($sSqlKey) ?
                $oEtt->getRowsetByParam($mParam, $nQtt, $nOffset, $sOrderBy) :
                $oEtt->getRowsetByKey($sSqlKey, $mParam, $nQtt, $nOffset, $sOrderBy);
        return $oItems;
    } // function getItemsByParam

    /**
     * Get page url
     * @return string
     */
    public function getPageUri($iPage, $bAddExt = true, $bAddSid = null, $bProtocol = null)
    {
        $sKey = $this->getConfig('PAGE_REQUEST_KEY', 'page');
        $aModifier = array(
            'exclude' => array(
                'A' => array($sKey),
                'G' => array($sKey),
            )
        );
        $sBy = $this->oBlock->getMeta(array('pager', 'paging_by'));
        if (empty($sBy)) {
            $sBy = $this->getConfig('PAGING_BY', 'GET');
        }
        $aModifier['include'][strtolower($sBy) == 'add' ? 'A' : 'G'][$sKey] = $iPage;

        return service('tab')->getModifiedCurrentURI($aModifier, $bAddExt, $bAddSid, $bProtocol);
    } // function getPageUri

    // ======== Private/Protected methods ======== \\
    /**
     * Count Items by Entity
     * @param mixed $mParam
     * @param string|\fan\core\base\model\entity $mEtt
     * @param string $sSqlKey
     * @return \fan\core\service\pager
     */
    protected function _countItemByEtt($mParam, $mEtt, $sSqlKey = null)
    {
        $oEtt = is_object($mEtt) ? $mEtt : ge($mEtt);
        $this->nItemQtt = empty($sSqlKey) ?
                $oEtt->getCountByParam($mParam) :
                $oEtt->getCountByKey($sSqlKey, $mParam);
        return $this;
    } // function _countItemByEtt

    /**
     * Define current page number
     * @param boolean $bForce
     * @return \fan\core\service\pager
     */
    protected function _definePageNum($bForce = false)
    {
        if (is_null($this->nPageNum) || $bForce) {
            $sKey  = $this->getConfig('PAGE_REQUEST_KEY', 'page');
            $sSrc  = $this->getConfig('PAGE_REQUEST_SRC', 'AG');
            $nPage = (int)service('request')->get($sKey, $sSrc, 1);
            $this->setPageNum($nPage);
        }
        return $this;
    } // function _definePageNum

    /**
     * Define Quantity Items Per Page by Meta
     * @param boolean $bForce
     * @return \fan\core\service\pager
     */
    protected function _defineItemPerPage($bForce = false)
    {
        if (is_null($this->nItemPerPage) || $bForce) {
            $nQtt = (int)$this->oBlock->getMeta(array('pager', 'item_per_page'));
            //ToDo: Pay attantion on quatifyer
            $this->setItemPerPage($nQtt);
        }
        return $this;
    } // function _defineItemPerPage

    protected function _definePageQtt($bForce = false)
    {
        if (is_null($this->nPageQtt) || $bForce) {
            $nPageQtt = ceil($this->getItemQtt() / $this->getItemPerPage());
            $this->setPageQtt($nPageQtt);
        }
        return $this;
    } // function _defineItemPerPage

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\
} // class \fan\core\service\pager
?>