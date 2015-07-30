<?php

require_once 'CRM/Utils/Type.php';
require_once 'CRM/SmartdebitReconciliation/Utils.php';
class CRM_SmartdebitReconciliation_Page_AJAX 
{
    static function getMembershipByContactID( ) {

        $selectedContact = CRM_Utils_Array::value( 'selectedContact', $_POST );
        if (empty($selectedContact)){
          return;
        }
        $membership = CRM_SmartdebitReconciliation_Utils::get_membership( $selectedContact );
        $cRecur     = CRM_SmartdebitReconciliation_Utils::get_Recurring_Record( $selectedContact );
        $nullMembership = array( 0 => 'No Membership Found');
        $nullcRecur = array( 0 => 'No Contribution Recur Found');
        $options['membership'] = $membership ? $membership : $nullMembership;
        $options['cRecur']     = $cRecur ? $cRecur : $nullcRecur;
        echo json_encode($options);
        exit;
    } 
    
    static function getNotLinkedRecurringByContactID() {
      $selectedContact = CRM_Utils_Array::value( 'selectedContact', $_POST );
      $mParams = array(
                'version'     => 3,
                'sequential'  => 1,
                'contact_id' => $selectedContact
              );
      $aMembership = civicrm_api('Membership', 'get', $mParams);
      $membershipWithRecur = array();
      foreach ($aMembership['values'] as $membership ) {
        if (!empty($membership['contribution_recur_id'])) {
          $membershipWithRecur [] = $membership['contribution_recur_id'];
        }
      }
      $allRecurringRecords = $originalAllRecurringRecords = CRM_SmartdebitReconciliation_Utils::get_Recurring_Record( $selectedContact );
      foreach ($membershipWithRecur as $linkedRecur) {
        if(array_key_exists($linkedRecur, $allRecurringRecords)) {
          unset($allRecurringRecords[$linkedRecur]);
        }
      }
      $options['cRecurNotLinked']     = $allRecurringRecords;
      $options['cRecur']              = $originalAllRecurringRecords;
      echo json_encode($options);
        exit;
    }
   
}