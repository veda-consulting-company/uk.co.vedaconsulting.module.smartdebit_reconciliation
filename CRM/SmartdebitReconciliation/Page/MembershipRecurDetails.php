<?php

class CRM_SmartdebitReconciliation_Page_MembershipRecurDetails extends CRM_Core_Page {
  // Path: civicrm/smartdebit/reconciliation/fixmissingcivi

  public function run() {
    $get = $_GET;
    $cid = $get['cid'];
    $mid = $get['mid'];
    $cr_id = $get['cr_id'];
    $reference_number = $get['reference_number'];
    $this->assign('reference_number', $reference_number);
    if(!empty($cid)){
      $contact = CRM_SmartdebitReconciliation_Utils::_get_contact_details($cid);
      $address = CRM_SmartdebitReconciliation_Utils::_get_address($cid);
      $this->assign('aContact', $contact);
      $this->assign('aAddress', $address);
    }
    // If 'Donation' option is chosen for membership, don't process
    if(!empty($mid) && $mid != 'donation') {
      $membership = CRM_SmartdebitReconciliation_Utils::get_membership($cid, $mid);
      $this->assign('aMembership', $membership);
    }
    // If 'Create New Recurring' option is chosen for recurring, don't process
    if(!empty($cr_id) && $cr_id != 'new_recur') {
      $cRecur = CRM_SmartdebitReconciliation_Utils::_get_contribution_recur($cr_id);
      $this->assign('aContributionRecur', $cRecur);
    }
    parent::run();
  }
}
