<?php namespace app\frontend\form;
/**
 * Test format class
 * @version 1.1
 */
class test_form_mono extends \project\block\form\usual
{
    /**
     * Init block
     */
    public function init()
    {
        $this->_parseForm();

        $this->view->all_data = print_r(
                $this->getSessionData('field_value', null, true),
                true
        );
    } // function init

    /**
     * Get data for select Variants
     * @param type $sName
     * @return type
     */
    public function getVariants($sName)
    {
        return array(
            array('value' => 'value3', 'text' => $sName . '_3'),
            array('value' => 'value4', 'text' => $sName . '_4'),
        );
    } // function getVariants

    /**
     * Rule of check date
     * @param mixed $mValue
     * @param array $aData
     * @return bool
     */
    public function checkDate($mValue, $aData)
    {
        return $mValue >= $aData['min_date'] && $mValue <= $aData['max_date'];
    } // function checkDate

    public function onSubmit()
    {
        $oForm = $this->getForm();
        $this->setSessionData('field_value', $oForm->getFieldValue());
    } // function onSubmit

} // class \app\frontend\form\test_form_mono
?>