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
    
   
}