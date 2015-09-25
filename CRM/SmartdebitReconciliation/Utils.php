<?php

class CRM_SmartdebitReconciliation_Utils{
  static function _get_membership_type( $membershipTypeID ){
    if( empty($membershipTypeID)){
      return "Not Found";
    }
    $mTypeParams = array(
                'version'     => 3,
                'sequential'  => 1,
                'id'          => $membershipTypeID
    );
    $aMembershipType = civicrm_api('MembershipType', 'get', $mTypeParams);
    if ( !$aMembershipType['is_error']){
      return $aMembershipType['values'][0]['name'];
    }else{
      $sql = "SELECT name 
              FROM civicrm_membership_type
              WHERE id = %1
        ";
      $param = array( 1 => array($membershipTypeID, 'Integer'));
      $dao = CRM_Core_DAO::singleValueQuery($sql, $param);
      return $dao;
    }
  }
  static function _get_membership_status( $membershipStatusID ){
    if( empty($membershipStatusID)){
      return "Not Found";
    }
    $mStatusParams = array(
                'version'     => 3,
                'sequential'  => 1,
                'id'          => $membershipStatusID
    );
    $aMembershipStatus = civicrm_api('MembershipStatus', 'get', $mStatusParams);
    if(!$aMembershipStatus['is_error']){
      return $aMembershipStatus['values'][0]['label'];
    }else{
      $sql = "SELECT label 
              FROM civicrm_membership_status
              WHERE id = %1
        ";
      $param = array( 1 => array($membershipStatusID, 'Integer'));
      $dao = CRM_Core_DAO::singleValueQuery($sql, $param);
      return $dao;
    }
  }
  
  function get_membership( $contactID ){
    $aMembershipOption = null;
    $mParams = array(
                'version'     => 3,
                'sequential'  => 1,
                'contact_id' => $contactID
              );
    $aMembership = civicrm_api('Membership', 'get', $mParams);
    foreach ($aMembership['values'] as $membership ) {
      $mem_id     = $membership['id'];
      if(!empty( $membership['start_date'] )) {
        $start_date = date( 'Y-m-d', strtotime($membership['start_date']));
      }else{
        $start_date = "Null";
      }
      if(!empty( $membership['end_date'] )) {
        $end_date = date( 'Y-m-d', strtotime($membership['end_date']));
      }else{
        $end_date = "Null";
      }
      $type       = self::_get_membership_type( $membership['membership_type_id']);
      $status     = self::_get_membership_status( $membership['status_id']);

      /*$aMembershipOption[] = array(
                               'id'         => $mem_id,
                               'start_date' => $start_date,
                               'end_date'   => $end_date,
                               'type'       => $type,
                               'status'     => $status
                             ); */
      $aMembershipOption[$mem_id] = $type.'/'.$status.'/'.$start_date.'/'.$end_date;
     }
     $aMembershipOption['donation'] = 'Donation';
   return $aMembershipOption;
  }
  
 
  function _get_ContributionId_By_ContributionRecurId( $cRecurID ){
    $contributionParams = array(
                'version'               => 3,
                'sequential'            => 1,
                'contribution_recur_id' => $cRecurID
    );
    $aContributionRecur = civicrm_api('Contribution', 'get', $contributionParams);
    return $aContributionRecur['values'][0];
  }
  function get_Recurring_Record( $contactID ){
    $cRecur = null;
    $aContributionRecur = CRM_Contribute_BAO_ContributionRecur::getRecurContributions($contactID); 
    foreach ( $aContributionRecur as $ContributionRecur){
      $sql = " SELECT name FROM civicrm_payment_processor WHERE id = %1 ";
      $param = array( 1 => array($ContributionRecur['payment_processor_id'], 'Integer') ); 
      $dao = CRM_Core_DAO::singleValueQuery($sql, $param);
      
      /*$acontribution = self::_get_ContributionId_By_ContributionRecurId( $ContributionRecur['id'] );
      $cRecur[] = array(
          'contribution_id'       => $acontribution['id'],
          'contribution_recur_id' => $ContributionRecur['id'],
          'status'                => $ContributionRecur['contribution_status'],
          'amount'                => $ContributionRecur['amount'],
          'payment_processor'     => $dao,
      );*/
      
      $cRecur[$ContributionRecur['id']] = $dao.'/'.$ContributionRecur['contribution_status'].'/'.$ContributionRecur['amount'];
    }
    $cRecur['new_recur'] = 'Create New Recurring';
    return $cRecur;
  }
}
?>
