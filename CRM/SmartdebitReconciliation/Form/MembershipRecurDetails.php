<?php
require_once 'CRM/SmartdebitReconciliation/Utils.php';
class CRM_SmartdebitReconciliation_Form_MembershipRecurDetails extends CRM_Core_Form{
  function preProcess() {
    parent::preProcess();
  }
  function buildQuickForm() {
     $this->addElement( 'select'
                      , 'membership_record'
                      , ts('Select Membership')
                      , array( '' => ts('Loading...')) 
                      );
    
     $this->addElement( 'select'
                      , 'contribution_recur_record'
                      , ts('Select Recur Record')
                      , array( '' => ts('Loading...')) 
                      );
    
     $this->addElement('text', 'contact_name', 'Contact', array('size' => 50, 'maxlength' => 255));
     $this->addElement('hidden', 'cid', 'cid');
  
     $this->addElement('text', 'reference_number', 'Smart Debit Reference', array('size' => 50, 'maxlength' => 255));
    
     $this->addButtons( array(
                          array(
                            'type'      => 'upload',
                            'name'      => ts('Next'),
                            ),
                         )
                      );
     // Get the smart Debit mandate details
		 require_once 'CRM/SmartdebitReconciliation/Form/SmartdebitReconciliationList.php';
     $smartDebitResponse = CRM_SmartdebitReconciliation_Form_SmartdebitReconciliationList::getSmartDebitPayments(CRM_Utils_Array::value('reference_number', $_GET));
		 $smartDebitMandate = $smartDebitResponse[0];
		 $this->assign( 'SDMandateArray', $smartDebitMandate );

		parent::buildQuickForm();
  }
	
  /**
   * This function sets the default values for the form.
   *
   * @access public
   *
   * @return None
   */
  function setDefaultValues() {
    $defaults = array();
    $defaults['reference_number'] = CRM_Utils_Array::value('reference_number', $_GET);

    return $defaults;
  }
	
  function postProcess() {
    $submitValues = $this->_submitValues;
    $cid = $submitValues['cid'];
    $mid = $submitValues['membership_record'];
    $reference_number = $submitValues['reference_number'];
    $cr_id = $submitValues['contribution_recur_record'];
    $params = sprintf('cid=%d&mid=%d&cr_id=%d&reference_number=%s', $cid, $mid, $cr_id, $reference_number);
    $url = CRM_Utils_System::url('civicrm/smartdebit/reconciliation/fix-contact-rec-confirm',$params);
    CRM_Utils_System::redirect($url);
    parent::postProcess();
  }
}
?>
