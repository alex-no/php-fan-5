<?php namespace fan\project\block\root;
/**
 * html 5 root template block
 * @version of file: 05.02.005 (12.02.2015)
 */
class html5 extends \fan\core\block\root\html
{
    public function setMetaByDb($nIdMetaData)
    {
        $oMetaMain = gr('mysql\ad_seo\meta_data_main', $nIdMetaData);
        if ($oMetaMain->checkIsLoad()){
            // Main meta-data
            $sTitle = $oMetaMain->getByLocal('title');
            if (!empty($sTitle)) {
                $this->setTitle($sTitle);
            }
            foreach (array('description', 'keywords') as $k) {
                $sCont = $oMetaMain->getByLocal($k);
                if (!empty($sCont)) {
                    $this->setMetaTag(array(
                        'name'    => $k,
                        'content' => $sCont
                    ));
                }
            }

            // OG meta-data
            $aMetaOg = ge('mysql\ad_seo\meta_data_og')->getRowsetByParam(array('id_meta_data_main' => $oMetaMain->getId()));
            if(count($aMetaOg) > 0){
                $iIdSiteLang = service('locale')->getLanguageId();
                foreach ($aMetaOg as $v) {
                    $nLngId = $v->get_id_site_language();
                    if(is_null($nLngId) || $iIdSiteLang == $nLngId){
                        $this->setMetaTag(array(
                            'property' => $v->get_key(),
                            'content'  => $v->get_value(),
                        ));
                    }
                }
            }
        }
        return $this;
    } // function setMetaByDb

} // class \fan\project\block\root\html5
?>