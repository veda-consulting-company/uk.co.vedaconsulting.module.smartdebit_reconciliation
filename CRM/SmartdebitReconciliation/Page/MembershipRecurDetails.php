<?php

Class CRM_SmartdebitReconciliation_Page_MembershipRecurDetails extends CRM_Core_Page{
// Path: civicrm/smartdebit/reconciliation/fixmissingcivi

  CONST c_ContributionStatus = "contribution_status";

  static function _get_contact_details( $cid ){
    $Params = array(
      'version'     => 3,
      'sequential'  => 1,
      'id'          => $cid
    );
    $aContact = civicrm_api('Contact', 'get', $Params);
    if( !$aContact['is_error'] ){
      return $aContact['values'][0];
    }else{
      return $aContact['error_message'];
    }
  }

  static function _get_address( $cid ){
    $Params = array(
      'version'     => 3,
      'sequential'  => 1,
      'contact_id'  => $cid
    );
    $aAddress = civicrm_api('Address', 'get', $Params);
    if( !$aAddress['is_error'] ){
      if($aAddress['count'] != 0){
        return $aAddress['values'][0];
      }else{
        return;
      }
    }else{
      return $aAddress['error_message'];
    }
  }

  static function _get_membership( $id ){
    $mParams = array(
      'version'     => 3,
      'sequential'  => 1,
      'id'          => $id
    );
    $aMembership = civicrm_api('Membership', 'get', $mParams);
    $mem         = $aMembership['values'][0];
    $memType     = CRM_SmartdebitReconciliation_Utils::_get_membership_type($mem['membership_type_id']);
    $memStatus   = CRM_SmartdebitReconciliation_Utils::_get_membership_status($mem['status_id']);
    $start_date  = array_key_exists('start_date', $mem) ? $mem['start_date'] : 'Null';
    $end_date    = array_key_exists('end_date', $mem) ? $mem['end_date'] : 'Null';
    if(!empty($mem)){
      $membership  = array(
        'id'        => $mem['id'],
        'status'    => $memStatus,
        'type'      => $memType,
        'start_date'=> $start_date,
        'end_date'  => $end_date,
      );
    }
    return $membership;
  }

  static function _get_optionValue($opGroupID, $value){

    $optionValue = array(
      'version'         => 3,
      'sequential'      => 1,
      'value'           => $value,
      'option_group_id' => $opGroupID
    );
    $aOptionValue = civicrm_api('OptionValue', 'get', $optionValue);
    if(!$aOptionValue['is_error']){
      return $aOptionValue['values'][0];
    }else{
      return $aOptionValue['error_message'];
    }
  }

  static function _get_optionGroup( $groupName ){

    $optionGroup = array(
      'version'     => 3,
      'sequential'  => 1,
      'name'        => $groupName
    );
    $aOptionGroup = civicrm_api('OptionGroup', 'get', $optionGroup);
    if(!$aOptionGroup['is_error']){
      return $aOptionGroup['values'][0];
    }else{
      return $aOptionGroup['error_message'];
    }
  }

  static function _get_contribution_recur( $cRecurID ){
    $cRecurParams = array(
      'version'     => 3,
      'sequential'  => 1,
      'id'          => $cRecurID
    );
    $aContributionRecur = civicrm_api('ContributionRecur', 'get', $cRecurParams);
    if(!$aContributionRecur['is_error']){
      $cRecur = $aContributionRecur['values'][0];
    }

    // get contribution status label
    $cStatusGroupName = self::c_ContributionStatus;
    $cStatusOpGroup = self::_get_optionGroup( $cStatusGroupName );
    $cStatusOpValue = self::_get_optionValue( $cStatusOpGroup['id'], $cRecur['contribution_status_id']);

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
        'status'            => $cStatusOpValue['label'],
        'amount'            => $cRecur['amount'],
        'payment_processor' => $dao
      );
    }
    return $contributionRecur;
  }

  function run() {
    $get   = $_GET;
    $cid   = $get['cid'];
    $mid   = $get['mid'];
    $cr_id = $get['cr_id'];
    $reference_number = $get['reference_number'];
    $this->assign('reference_number', $reference_number);
    if(!empty($cid)){
      $contact    = self::_get_contact_details($cid);
      $address    = self::_get_address($cid);
      $this->assign('aContact', $contact);
      $this->assign('aAddress', $address);
    }
    // If 'Donation' option is chosen for membership, don't process
    if(!empty($mid) && $mid != 'donation'){
      $membership = self::_get_membership($mid);
      $this->assign('aMembership', $membership);
    }
    // If 'Create New Recurring' option is chosen for recurring, don't process
    if(!empty($cr_id) && $cr_id != 'new_recur'){
      $cRecur     = self::_get_contribution_recur($cr_id);
      $this->assign('aContributionRecur', $cRecur);
    }
    parent::run();
  }
}
?>