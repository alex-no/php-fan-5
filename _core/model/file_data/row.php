<?php namespace core\model\file_data;
/**
 * Description of file_data-row
 *
 * @author Alex
 */
class row extends \core\base\model\row
{
    /**
     * @var \core\model\access_type\row  entity access type
     */
    private $oAT = null;

    /**
     * Inquire path to file at the disk
     * @param  integer $nId
     * @param  boolean $bCheckAddCondition
     * @return string | NULL
     * @access public
     */
    public function getFilePath($nId = null, $bCheckAddCondition = true, $sSrcName = '')
    {
        if ($nId) {
            $bCheckAddCondition = false;
        } else {
            $nId = $this->getId(true);
        }
        $sPath = $this->getMainFilePath($nId);
        if (!empty($sPath) && (!$bCheckAddCondition || $this->get_is_accessible() && !$this->get_is_deleted())) {
            $sPath = $this->getEntity()->getConfig('file_store') . $sPath;
            $aParts = pathinfo($this->get_src_name($sSrcName, true));
            $sPath .= '.' . (empty($aParts['extension']) || $aParts['extension'] == 'php' ? $this->getEntity()->getConfig('file_ext') : $aParts['extension']);
        }
        return $sPath;
    }// function getFilePath

    /**
     * Get main part of file path by ID
     * @param  integer $nId
     * @return string | NULL
     * @access protected
     */
    protected function getMainFilePath($nId)
    {
        $sPath = null;
        if (!empty($nId)) {
            $nTriadLen = strlen($nId) % 3;
            $sPath = str_repeat('0', ($nTriadLen ? 3 - $nTriadLen : 0)) . number_format((int)$nId, 0, '.', '/');
            if ($this->getEntity()->getConfig('path_with_connection')) {
                $sPath = $this->getEntity()->getConnection()->getConnectionName() . '/' . $sPath;
            }
         }
        return $sPath;
    }// function getMainFilePath

    /**
     * Check access for read file
     * @return boolean true - if access is enabled
     * @access public
     */
    public function checkAccess()
    {
        $bRet = true;
        if ($this->get_id_file_access_type(false, true)) {
            if (!$this->oAT) {
                $this->oAT = gr('file_access_type', $this->get_id_file_access_type());
            }
            $sRule = $this->oAT->get_access_rule();
            if ($sRule) { // Check access by rule
                $bRet     = false;
                $aMatches = array();
                foreach (explode(',', $sRule) as $s) {
                    if (preg_match('/^(?:([^\:]+)\:)?(.+)?$/', $s, $aMatches)) {
                        if (role($aMatches[2], $aMatches[1])) {
                            $bRet = true;
                            break;
                        }
                    } else {
                        throw new exception_error_entity_fatal($this, 'Incorret role rule "' . $sRule . '"');
                    }
                }
            }
            if (!$bRet) { // Check personal access
                $oPA = $this->getEntityPA();
                if ($oPA && $oPA->checkIsLoad()) {
                    $nQtt = $oPA->get_access_qtt();
                    if (!$nQtt || ($oPA->get_expire_data() && date('Y-m-d H:i:s') > $oPA->get_expire_data())) {
                        if ($oPA->get_member_type() != 'owner') {
                            $oPA->delete();
                        }
                    } else {
                        $bRet = true;
                        if ($nQtt > 0) {
                            $oPA->set_access_qtt($nQtt - 1);
                        }
                    }
                }
            }
        }
        return $bRet;
    }// function checkAccess

    /**
     * Get entity personal access
     * @return entity_file_personal_access | NULL
     * @access protected
     */
    protected function getEntityPA($nMembId = null)
    {
        if (!$nMembId) {
            $oMember = getUser();
            if (!$oMember || !$oMember->checkIsLoad()) {
                return null;
            }
            $nMembId = $oMember->getId();
        }
        $oPA = ge('entity_file_personal_access')->getDataByParam(array(
            'id_file_data' => $this->getId(),
            'id_member'    => $nMembId,
        ));
        return $oPA;
    }// function getEntityPA

} // class \core\model\file_data\row
?>