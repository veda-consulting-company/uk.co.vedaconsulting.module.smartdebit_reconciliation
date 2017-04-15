<?php

class CRM_SmartdebitReconciliation_Utils {

  /**
   * Get all memberships for a contact (or membership specified by membershipID)
   * @param $contactID
   * @param null $membershipID
   * @return null
   */
  static function getContactMemberships($contactID, $membershipID = NULL) {
    // Get memberships for contact
    $membershipDetails = CRM_Member_BAO_Membership::getAllContactMembership($contactID);

    $membershipOptions = null;

    // Build membershipOptions array
    foreach ($membershipDetails as $key => $detail) {
      if(!empty( $detail['start_date'] )) {
        $start_date = date( 'Y-m-d', strtotime($detail['start_date']));
      } else {
        $start_date = "Null";
      }
      if (!empty($detail['end_date'])) {
        $end_date = date( 'Y-m-d', strtotime($detail['end_date']));
      } else {
        $end_date = "Null";
      }
      $type = CRM_Member_PseudoConstant::membershipType($detail['membership_type_id']);
      $status = CRM_Member_PseudoConstant::membershipStatus($detail['status_id']);

      $membershipOptions[$key]['id'] = $key;
      $membershipOptions[$key]['start_date'] = $start_date;
      $membershipOptions[$key]['end_date'] = $end_date;
      $membershipOptions[$key]['type'] = $type;
      $membershipOptions[$key]['status'] = $status;
      $membershipOptions[$key] = $type.'/'.$status.'/'.$start_date.'/'.$end_date;
    }
    $membershipOptions['donation'] = 'Donation';
    if ($membershipID && isset($membershipOptions[$membershipID])) {
      return $membershipOptions[$membershipID];
    }
    else {
      return $membershipOptions;
    }
  }

  /**
   * Return the first contribution record for recurring contribution with given ID
   * @param $cRecurID
   * @return mixed
   */
  static function getContributionRecordForRecurringContribution($cRecurID) {
    $contributionParams = array(
      'version'               => 3,
      'sequential'            => 1,
      'contribution_recur_id' => $cRecurID
    );
    $contributionRecords = civicrm_api('Contribution', 'get', $contributionParams);
    if (!empty($contributionRecords['is_error']) && $contributionRecords['count'] > 0) {
      // FIXME: This will always return the first contribution, but there could be more than one
      return $contributionRecords['values'][0];
    }
  }

  /**
   * Get list of recurring contribution records for contact
   * @param $contactID
   * @return mixed
   */
  static function getContactRecurringContributions($contactID) {
    // Get recurring contributions by contact Id
    $aContributionRecur = CRM_Contribute_BAO_ContributionRecur::getRecurContributions($contactID);
    foreach ($aContributionRecur as $ContributionRecur) {
      // Get payment processor name used for recurring contribution
      $sql = " SELECT name FROM civicrm_payment_processor WHERE id = %1 ";
      $param = array( 1 => array($ContributionRecur['payment_processor_id'], 'Integer') );
      $dao = CRM_Core_DAO::singleValueQuery($sql, $param);

      // Create display name for recurring contribution
      $cRecur[$ContributionRecur['id']] = $dao.'/'.$ContributionRecur['contribution_status'].'/'.$ContributionRecur['amount'];
    }
    $cRecur['new_recur'] = 'Create New Recurring';
    return $cRecur;
  }

  /**
   * Get recurring contribution record by recur ID
   * @param $cRecurID
   * @return array
   */
  static function getRecurringContributionRecord($cRecurID) {
    $cRecurParams = array(
      'version'     => 3,
      'sequential'  => 1,
      'id'          => $cRecurID
    );
    $aContributionRecur = civicrm_api('ContributionRecur', 'get', $cRecurParams);
    if(!$aContributionRecur['is_error']){
      $cRecur = $aContributionRecur['values'][0];
    }

    // Get contribution Status label
    $contributionStatusOptions = CRM_Contribute_BAO_Contribution::buildOptions('contribution_status_id', 'validate');
    $contributionStatus = $contributionStatusOptions[$cRecur['contribution_status_id']];

    //get payment processor name
    if(!empty($cRecur['payment_processor_id'])){
      $sql   = "SELECT name 
                FROM civicrm_payment_processor 
                WHERE id = %1
                ";
      $param = array( 1 => array( $cRecur['payment_processor_id'], 'Integer') );
      $dao   = CRM_Core_DAO::singleValueQuery($sql, $param);
    }

    $contributionRecur = array();
    if(!empty($cRecur)){
      $contributionRecur = array(
        'id'                => $cRecur['id'],
        'status'            => $contributionStatus,
        'amount'            => $cRecur['amount'],
        'payment_processor' => $dao
      );
    }
    return $contributionRecur;
  }

  /**
   * Get contact details
   *
   * @param $cid
   * @return mixed
   */
  static function getContactDetails($cid) {
    $Params = array(
      'version'     => 3,
      'sequential'  => 1,
      'id'          => $cid
    );
    $aContact = civicrm_api('Contact', 'get', $Params);
    if (empty($aContact['is_error'])) {
      if ($aContact['count'] > 0) {
        return $aContact['values'][0];
      }
      else {
        return;
      }
    }
    else {
      return $aContact['error_message'];
    }
  }

  /**
   * Get contact Address
   *
   * @param $cid
   */
  static function getContactAddress($cid) {
    $Params = array(
      'version'     => 3,
      'sequential'  => 1,
      'contact_id'  => $cid
    );
    $aAddress = civicrm_api('Address', 'get', $Params);
    if (empty($aAddress['is_error'])) {
      if ($aAddress['count'] > 0){
        return $aAddress['values'][0];
      }
      else {
        return;
      }
    }
    else {
      return $aAddress['error_message'];
    }
  }
}
