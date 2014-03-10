<?php namespace fan\core\service\entity\designer;
/**
 * Designer of snippety SQL-request
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
class snippety extends \fan\core\service\entity\designer
{
    /**
     * SQL-request snippets
     * @var string
     */
    protected $aQueryParts = array(
        'snippet' => array(),
        'orderBy' => null,
    );


    // ======== Static methods ======== \\

    // ======== The magic methods ======== \\

    // ======== Main Interface methods ======== \\

    /**
     * Separate Source SQL-requests at the snippets and set it
     * @param string $sQueryKey
     * @return \fan\core\service\entity\designer\snippety
     */
    public function setSqlRequest($sQueryKey)
    {
        $oRequest = $this->getEntity()->getRequestLoader();
        $sSourceSQL = $oRequest->get($sQueryKey);
        if ($sSourceSQL != '') {
            $this->aQueryParts['snippet'] = substr($sSourceSQL, 0, 2) == '##' ?
                    $this->_parseSQL(trim(substr($sSourceSQL, 2))) :
                    array($sSourceSQL);
        }
        return $this;
    } // function setSqlRequest

    /**
     * Set Snippet of Request
     * @param string $sSourceSQL
     * @return \fan\core\service\entity\designer\snippety
     */
    public function setRequestSnippet($mSnippetValue, $bAllowException = true)
    {
        $this->set('snippet', $mSnippetValue, $bAllowException);
        return $this;
    } // function setRequestSnippet

    /**
     * Add Snippet of Request
     * @param string|array $mSnippetValue
     * @param type $bToEnd
     * @param type $bAllowException
     * @return \fan\core\service\entity\designer\snippety
     */
    public function addRequestSnippet($mSnippetValue, $bToEnd = true, $bAllowException = true)
    {
        $this->add('snippet', $mSnippetValue, $bToEnd, $bAllowException);
        return $this;
    } // function addRequestSnippet

    /**
     * Set Part of Order
     * @param mixed $mPartValue
     * @param boolean $bAllowException
     * @return \fan\core\service\entity\designer\snippety
     */
    public function setOrderPart($mPartValue, $bAllowException = true)
    {
        if (!empty($mPartValue)) {
            $this->set('orderBy', $mPartValue, $bAllowException);
        }
        return $this;
    } // function setOrderPart

    /**
     * Add Part of Order
     * @param string|array $mPartValue
     * @param boolean $bToEnd
     * @param boolean $bAllowException
     * @return \fan\core\service\entity\designer\snippety
     */
    public function addOrderPart($mPartValue, $bToEnd = true, $bAllowException = true)
    {
        $this->add('orderBy', $mPartValue, $bToEnd, $bAllowException);
        return $this;
    } // function addOrderPart

    // ======== Private/Protected methods ======== \\
    /**
     * Parse Source SQL
     * @param type $sSourceSQL
     * @return array
     */
    public function _parseSQL($sSourceSQL)
    {
        $aSnippets = array();

        do {
            $aMatches = array();
            if (preg_match('/^(.*?)[\r\n]+\#\#([^#]+)(?:\-\#\-(\w+(?:\:\w+)?))?\#\#([\r\n].+?)[\r\n]+\#\#\-\-/s', $sSourceSQL, $aMatches)) {
                if (!empty($aMatches[1])) {
                    array_push($aSnippets, $aMatches[1]);
                }
                array_push($aSnippets, $this->getEntity()->getService()->getSnippet($this, $aMatches[4], $aMatches[2], $aMatches[3]));
            } else {
                break;
            }

            $sSourceSQL = substr($sSourceSQL, strlen($aMatches[0]));
        } while(!empty($sSourceSQL));

        if (!empty($sSourceSQL)) {
            array_push($aSnippets, $sSourceSQL);
        }
        return $aSnippets;
    } // function _parseSQL

} // class \fan\core\service\entity\designer\snippety
?>