<?php namespace app\frontend\extra;
/**
 * Check  class
 * @version 1.1
 */
class check_db extends \project\block\common\simple
{
    /**
     * Flag: Is correct DB setting
     * @var boolean
     */
    protected $bIsCorrect = null;

    /**
     * Init block data
     */
    public function init()
    {
        $this->view->sSqlDir   = realpath(\bootstrap::parsePath('{PROJECT}/../db/dumps'));
        $this->view->isCorrect = $this->isCorrect();
    } // function init

    /**
     * Init block data
     */
    public function isCorrect()
    {
        while (is_null($this->bIsCorrect)) {
            $this->bIsCorrect = false;

            // ------ Check connection to database ------ \\
            try {
                service('database', $this->getMeta('db_connection', 'common'));
            } catch (\core\exception\service\database $e) {
                $this->view->nOperationCode = $e->getOperationCode();
                $this->view->sErrorMessage  = nl2br($e->getMessageForLog());
                $this->view->nErrorNumber   = $e->getErrorNum();
                $this->view->aConnectParam  = $e->getService()->getConnectionParam();
                break;
            }

            // ------ Check objects of entity ------ \\
            $aData = $this->getMeta('entity', array())->toArray();
            try {
                foreach ($aData as $k => &$v) {
                    $v['entity']    = ge($k);
                    $v['db_fields'] = $v['entity']->getDescription()->get('fields', true);
                }
            } catch (\core\exception\service\fatal $e) {
                $this->view->nOperationCode = 50;
                $this->view->sErrorMessage  = nl2br($e->getMessageForLog());
                break;
            // ------ Check table in DB ------ \\
            } catch (\core\exception\model\entity\fatal $e) {
                $this->view->nOperationCode = 51;
                $this->view->sErrorMessage  = nl2br($e->getMessageForLog());
                $this->view->sTableName     = $e->getEntity()->getTableName();
                break;
            }

            // ------ Check DB-fields ------ \\
            foreach ($aData as $k => $v) {
                if (!empty($v['fields'])) {
                    $aNoFields = array_diff($v['fields'], array_keys($v['db_fields']));
                    if (!empty($aNoFields)) {
                        $this->view->nOperationCode = 60;
                        $this->view->aNoFields      = $aNoFields;
                        $this->view->sTableName     = $v['entity']->getTableName();
                        break 2;
                    }
                }
            }

            // ------ Check DB-data ------ \\
            foreach ($aData as $k => $v) {
                if (!empty($v['data'])) {
                    $aNoData = array();
                    foreach ($v['data'] as $id) {
                        $oRow = $v['entity']->getRowById($id);
                        if (!$oRow->checkIsLoad()) {
                            $aNoData[] = $id;
                        }
                    }
                    if (!empty($aNoData)) {
                        $this->view->nOperationCode = 61;
                        $this->view->aNoData        = $aNoData;
                        $this->view->sTableName     = $v['entity']->getTableName();
                        break 2;
                    }
                }
            }

            $this->bIsCorrect = true;
        }
        return $this->bIsCorrect;
    } // function isCorrect
} // class \app\frontend\extra\check_db
?>