<?php

class CRM_SmartdebitReconciliation_Page_MembershipRecurDetails extends CRM_Core_Page {
  // Path: civicrm/smartdebit/reconciliation/fix-contact-rec-confirm

  public function run() {
    // Get parameters
    $cid = CRM_Utils_Array::value('cid', $_GET);
    $mid = CRM_Utils_Array::value('mid', $_GET);
    $cr_id = CRM_Utils_Array::value('cr_id', $_GET);
    $reference_number = CRM_Utils_Array::value('reference_number', $_GET);
    $this->assign('reference_number', $reference_number);
    // Get contact details if set
    if(!empty($cid)){
      $contact = CRM_SmartdebitReconciliation_Utils::getContactDetails($cid);
      $address = CRM_SmartdebitReconciliation_Utils::getContactAddress($cid);
      $this->assign('aContact', $contact);
      $this->assign('aAddress', $address);
    }
    // If 'Donation' option is chosen for membership, don't process
    if(!empty($mid) && $mid != 'donation') {
      $membership = CRM_SmartdebitReconciliation_Utils::getContactMemberships($cid, $mid);
      $this->assign('aMembership', $membership);
    }
    // If 'Create New Recurring' option is chosen for recurring, don't process
    if(!empty($cr_id) && $cr_id != 'new_recur') {
      $cRecur = CRM_SmartdebitReconciliation_Utils::getRecurringContributionRecord($cr_id);
      $this->assign('aContributionRecur', $cRecur);
    }
    parent::run();
  }
}
