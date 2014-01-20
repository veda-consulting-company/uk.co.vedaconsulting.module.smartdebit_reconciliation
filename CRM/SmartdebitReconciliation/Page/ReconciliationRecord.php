<?php

require_once 'CRM/SmartdebitReconciliation/Page/MembershipRecurDetails.php';

Class CRM_SmartdebitReconciliation_Page_ReconciliationRecord extends CRM_Core_Page {

  function run() {
		$get   = $_GET;
		$cid   = $get['cid'];
		$mid   = $get['mid'];
		$cr_id = $get['cr_id'];
		$payer_reference = $get['reference_number'];
		$this->assign('payer_reference', $reference_number);
		
		$params = array();
		
		if(!empty($cid)){
			$contact    = CRM_SmartdebitReconciliation_Page_MembershipRecurDetails::_get_contact_details($cid);
			$address    = CRM_SmartdebitReconciliation_Page_MembershipRecurDetails::_get_address($cid);
			$this->assign('aContact', $contact);
			$this->assign('aAddress', $address);
			
			$params['contact_id'] = $cid;
		}
		
		if(!empty($mid)){
			$membership = CRM_SmartdebitReconciliation_Page_MembershipRecurDetails::_get_membership($mid);
			$this->assign('aMembership', $membership);
			$params['membership_id'] = $mid;
		}
		
		if(!empty($cr_id)){
			$cRecur     = CRM_SmartdebitReconciliation_Page_MembershipRecurDetails::_get_contribution_recur($cr_id);
			$this->assign('aContributionRecur', $cRecur);
			$params['contribution_recur_id'] = $cr_id;
		}
		$params['payer_reference'] = $payer_reference;
		// Check the details are correct and everything has been passed over
		
		// Call the routine that will fix everything
		require_once 'CRM/SmartdebitReconciliation/Form/SmartdebitReconciliationList.php';
		
		CRM_SmartdebitReconciliation_Form_SmartdebitReconciliationList::repair_missing_from_civicrm_record($params);
		
		$params = sprintf('reset=1&cid=%d', $params['contact_id']);
    $url = CRM_Utils_System::url('civicrm/contact/view',$params);
    CRM_Utils_System::redirect($url);

	}
}