<?php

require_once 'CRM/SmartdebitReconciliation/Page/MembershipRecurDetails.php';

Class CRM_SmartdebitReconciliation_Page_RepairCorruptRecurring extends CRM_Core_Page {

  function run() {
    // Get parameters
    $cid = CRM_Utils_Array::value('cid', $_GET);
    $payer_reference = CRM_Utils_Array::value('reference_number', $_GET);
    // Find a membershipType for the contact - if they have more than one then bomb out
    $mid = CRM_Utils_Array::value('mid', $_GET);

    // Find recurring for the contact
    // The recurring should have the a status of pending
    $cr_id = CRM_Utils_Array::value('cr_id', $_GET);

    // Assign values to form
    $this->assign('payer_reference', $payer_reference);

    // Initialise values
    $params = array();

    // Find the contribution attached to the recurring record thats also set to pending (incomplete transaction)
    if(!empty($cid)){
      $contact = CRM_SmartdebitReconciliation_Utils::getContactDetails($cid);
      $address = CRM_SmartdebitReconciliation_Utils::getContactAddress($cid);
      $this->assign('aContact', $contact);
      $this->assign('aAddress', $address);

      $params['contact_id'] = $cid;
    }

    if(!empty($mid)){
      $membershipType = CRM_Member_PseudoConstant::membershipType($mid);
      $this->assign('aMembership', $membershipType);
      $params['membership_id'] = $mid;
    }

    if(!empty($cr_id)){
      $cRecur = CRM_SmartdebitReconciliation_Utils::getRecurringContributionRecord($cr_id);
      $this->assign('aContributionRecur', $cRecur);
      $params['contribution_recur_id'] = $cr_id;
    }
    $params['payer_reference'] = $payer_reference;
    // Check the details are correct and everything has been passed over

    // Then Call the IPN code i.e. we're pretending we've just completed the smart debit call and firing the code that was in the
    CRM_SmartdebitReconciliation_Form_SmartdebitReconciliationList::repair_corrupt_in_civicrm_record($params);

    $params = sprintf('reset=1&cid=%d', $cid);
    $url = CRM_Utils_System::url('civicrm/contact/view',$params);
    CRM_Utils_System::redirect($url);
  }
}
