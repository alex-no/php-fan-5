<?php namespace core\base\model\spec_file;
/**
 * Row of special files
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
abstract class row extends \core\base\model\row
{
    /**
     * Entity File Data
     * @var \core\base\model\file_data\row
     */
    protected $oEntityFile = null;

    /**
     * Delete file
     */
    public function delete()
    {
        $oFile = $this->getEntityFile();
        if (parent::delete()) {
            $oFile->delete();
        }
    }

    /**
     * This method will be run after entity record is deleted
     * @param mixed $mDelId - deleted ID
     */
    protected function runAfterDelete($mDelId)
    {
        $this->getEntityFile()->delete();
    }

    /**
     * Get entity_file
     * @return entity_file_data
     */
    public function getEntityFile()
    {
        if (!$this->oEntityFile) {
            $sNs = get_ns_name($this, 2);
            $this->oEntityFile = gr('\\' . $sNs . '\file_data');
            $this->oEntityFile->getEntity()->setConnection($this->getEntity()->getConnection()->getConnectionName());
            $this->oEntityFile->setAllowLoadInfo(false);
            $this->oEntityFile->loadById($this->getId(false));
        }
        return $this->oEntityFile;
    }// function getEntityFile

    /**
     * Get is deleted
     * @return boolean true - if file is deleted
     */
    protected function get_is_deleted()
    {
        return $this->getEntityFile()->get_is_deleted();
    }// function get_is_deleted

    /**
     * Get source name
     * @return string
     */
    public function get_src_name()
    {
        return $this->getEntityFile()->get_src_name();
    }// function get_src_name


    // ======== Set/get access ======== \\

    /**
     * Set access type
     * @param string $sKey type key
     * @param boolean $bSave save this entitty
     */
    public function setAccessType($sKey, $bSave = true)
    {
        $this->getEntityFile()->setAccessType($sKey, $bSave);
        if($bSave) {
            $this->save();
        }
    }// function setAccessType

    /**
     * Set personal access
     * @param string $sMembType   - Member Type: owner/guest
     * @param string $sExpireDate - Expire access data
     * @param number $nAccessQtt  - Max quantity of access
     * @param number $nMemrId     - Memeber Id (NR. By defaulf current member)
     */
    public function setPersonalAccess($sMembType = 'owner', $sExpireDate = null, $nAccessQtt = -1, $nMemrId = 0)
    {
        $this->getEntityFile()->setPersonalAccess($sMembType, $sExpireDate, $nAccessQtt, $nMemrId);
    }// function setPersonalAccess

    /**
     * Remove personal access
     * @param string $nRemoveType - Remove Type: 0 - remove all; 1 -  remove all but not owner; 2 - remove pointed member; 3 -  remove all but not pointed member
     * @param number $nMembId     - Memeber Id (Required for type: 2 and 3)
     */
    public function removePersonalAccess($nRemoveType = 1, $nMembId = null)
    {
        $this->getEntityFile()->removePersonalAccess($nRemoveType, $nMembId);
    }// function removePersonalAccess

    /**
     * Check access for read file
     * @return boolean true - if access enable
     */
    public function checkAccess()
    {
        return $this->getEntityFile()->checkAccess();
    }// function checkAccess

    /**
     * Chech Is current member Owner
     * @return boolean true - if member is owner
     */
    public function checkIsOwner()
    {
        return $this->getEntityFile()->checkIsOwner();
    }// function checkIsOwner
} // class \core\base\model\spec_file\entity
?>