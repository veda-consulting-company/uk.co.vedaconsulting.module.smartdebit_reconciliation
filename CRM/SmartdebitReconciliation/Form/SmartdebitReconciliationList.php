<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/Session.php';
/**
 * This class provides the functionality to delete a group of
 * contacts. This class provides functionality for the actual
 * addition of contacts to groups.
 */

class CRM_SmartdebitReconciliation_Form_SmartdebitReconciliationList extends CRM_Core_Form {
  /* Smart Debit parameters
    address_3 (String, 0 characters )
    first_name (String, 9 characters ) simon1008
    last_name (String, 9 characters ) simon1008
    regular_amount (String, 6 characters ) �7.60
    start_date (String, 10 characters ) 2013-04-01
    county (String, 0 characters )
    address_1 (String, 9 characters ) simon1008
    postcode (String, 7 characters ) B25 8XY
    title (String, 0 characters )
    email_address (String, 18 characters ) simon1008@veda.com
    current_state (String, 2 characters ) 10
    town (String, 9 characters ) simon1008
    payerReference (String, 5 characters ) 36978
    frequency_type (String, 1 characters ) M
    reference_number (String, 8 characters ) 00000573
    address_2 (String, 0 characters )
  */

  /**
   * Build the form
   *
   * @access public
   * @return void
   */
  function buildQuickForm() {
    // For smart debit sync purpose
    $sync = CRM_Utils_Array::value('sync', $_GET, '');
    $this->assign('sync', $sync);

    $checkAmount = CRM_Utils_Array::value('checkAmount', $_GET);
    $checkFrequency = CRM_Utils_Array::value('checkFrequency', $_GET);
    $checkStatus = CRM_Utils_Array::value('checkStatus', $_GET);
    $checkPayerReference = CRM_Utils_Array::value('checkPayerReference', $_GET);
    $checkMissingFromCivi = CRM_Utils_Array::value('checkMissingFromCivi', $_GET);
    $checkMissingFromSD = CRM_Utils_Array::value('checkMissingFromSD', $_GET);
    // Only display smart debit records that have a matching contact in CiviCRM if hasContact=1
    $hasAmount = CRM_Utils_Array::value('hasAmount', $_GET);
    $hasContact = CRM_Utils_Array::value('hasContact', $_GET);

    $listArray = array();

    // The following differences are highlighted
    // 1. Transaction Id in Smart Debit and Civi for the same contact
    // 2. Transaction Id in Smart Debit and Civi for different contacts
    // 3. Transaction Id in Smart Debit but none found in Civi
    // 4. Transaction Id in Civi but none in Smart Debit

    // Loop through Contributions and Highlight Discrepencies
    //foreach ($smartDebitArray as $key => $smartDebitRecord) {

    // Start Here
    if ($checkAmount || $checkFrequency || $checkStatus || $checkPayerReference) {
      $sql  = " SELECT ctrc.id contribution_recur_id ";
      $sql .= " , ctrc.contact_id ";
      $sql .= " , cont.display_name ";
      $sql .= " , ctrc.payment_instrument_id ";
      $sql .= " , opva.label payment_instrument ";
      $sql .= " , ctrc.start_date ";
      $sql .= " , ctrc.amount ";
      $sql .= " , ctrc.trxn_id  ";
      $sql .= " , ctrc.contribution_status_id ";
      $sql .= " , ctrc.frequency_unit ";
      $sql .= " , ctrc.frequency_interval ";
      $sql .= " , ctrc.financial_type_id ";
      $sql .= " , csd.regular_amount ";
      $sql .= " , csd.frequency_type ";
      $sql .= " , csd.frequency_factor ";
      $sql .= " , csd.current_state ";
      $sql .= " , csd.payerReference ";
      $sql .= " , csd.start_date as smart_debit_start_date ";
      $sql .= " , csd.reference_number ";
      $sql .= " , csd.id as smart_debit_id";
      $sql .= " FROM ";
      $sql .= " civicrm_contribution_recur ctrc ";
      $sql .= " LEFT JOIN civicrm_contact cont ON (ctrc.contact_id = cont.id) ";
      $sql .= " LEFT JOIN civicrm_option_value opva ON (ctrc.payment_instrument_id = opva.value) ";
      $sql .= " LEFT JOIN civicrm_option_group opgr ON (opgr.id = opva.option_group_id) ";
      $sql .= " INNER JOIN civicrm_sd_refresh csd ON csd.reference_number = ctrc.trxn_id ";
      $sql .= " WHERE opgr.name = 'payment_instrument' ";
      $sql .= " AND   opva.label = 'Direct Debit' ";

      $dao = CRM_Core_DAO::executeQuery( $sql);

      // Remove first 2 characters (Ascii characters 194 & 163)
      while ($dao->fetch()) {
        // Smart Debit Record Found
        // 1. Transaction Id in Smart Debit and Civi for the same contact

        $transactionRecordFound = true;
        $different = false;
        $differences = '';
        $separator = '';
        $separatorCharacter = ' | ';

        if ($checkAmount) {
          // Check that amount in CiviCRM matches amount in Smart Debit
          if ($dao->regular_amount != $dao->amount) {
            $different = true;
            $differences .= 'Amount';
            $separator = $separatorCharacter;
          }
        }

        if ($checkFrequency) {
          // Check that frequency in CiviCRM matches frequency in Smart Debit
          if ($dao->frequency_type == 'W' && ($dao->frequency_unit != 'day' || $dao->frequency_interval % 7 != 0) ) {
            $different = true;
            $differences .= 'Frequency';
            $separator = $separatorCharacter;
          }
          elseif ($dao->frequency_type == 'M' && $dao->frequency_unit != 'month' ) {
            $different = true;
            $differences .= 'Frequency';
            $separator = $separatorCharacter;
          }
          elseif ($dao->frequency_type == 'Q' && ($dao->frequency_unit != 'month' && $dao->frequency_interval % 3 != 0)) {
            $different = true;
            $differences .= 'Frequency';
            $separator = $separatorCharacter;
          }
          elseif ($dao->frequency_type == 'Y' && $dao->frequency_unit != 'year' ) {
            $different = true;
            $differences .= 'Frequency';
            $separator = $separatorCharacter;
          }
        }

        /* Smart Debit statuses are as follows
          0 Draft
          1 New
          10 Live
          11 Cancelled
          12 Rejected
         *
         */
        // First case check if Smart Debit is new or live then CiviCRM is in progress
        if ($checkStatus) {
          if (($dao->current_state == 10 || $dao->current_state == 1) && ($dao->contribution_status_id != 5)) {
            $different = true;
            $differences .= $separator. 'Status';
            $separator = $separatorCharacter;
          }
          // Recurring record active in Civi, but smart debit record is not active
          if (!($dao->current_state == 10 || $dao->current_state == 1) && ($dao->contribution_status_id == 5)) {
            $different = true;
            $differences .= $separator. 'Status';
            $separator = $separatorCharacter;
          }
        }

        // 2. Transaction Id in Smart Debit and Civi for different contacts
        if ($checkPayerReference) {
          if ($dao->payerReference != $dao->contact_id) {
            $different = true;
            $differences .= $separator. 'Payer Reference';
            $separator = $separatorCharacter;
          }
        }

        // If different then
        if ($different) {
          $financialType = '';
          if ($dao->financial_type_id) {
            $financialType = CRM_Core_DAO::getFieldValue('CRM_Financial_DAO_FinancialType', $dao->financial_type_id, 'name', 'id');
          }

          $listArray[$dao->smart_debit_id]['recordFound']           = $transactionRecordFound;
          $listArray[$dao->smart_debit_id]['contribution_recur_id'] = $dao->contribution_recur_id;
          $listArray[$dao->smart_debit_id]['contribution_type']     = $financialType;
          $listArray[$dao->smart_debit_id]['contact_id']            = $dao->contact_id;
          $listArray[$dao->smart_debit_id]['sd_contact_id']         = $dao->payerReference;
          $listArray[$dao->smart_debit_id]['contact_name']          = $dao->display_name;
          $listArray[$dao->smart_debit_id]['payment_instrument']    = $dao->payment_instrument;
          $listArray[$dao->smart_debit_id]['start_date']            = $dao->start_date;
          $listArray[$dao->smart_debit_id]['sd_start_date']         = $dao->smart_debit_start_date;
          $listArray[$dao->smart_debit_id]['frequency']             = $dao->frequency_unit;
          $listArray[$dao->smart_debit_id]['sd_frequency']          = $dao->frequency_type;
          $listArray[$dao->smart_debit_id]['amount']                = $dao->amount;
          $listArray[$dao->smart_debit_id]['sd_amount']             = $dao->regular_amount;
          $listArray[$dao->smart_debit_id]['contribution_status_id']    = $dao->contribution_status_id;
          $listArray[$dao->smart_debit_id]['sd_contribution_status_id'] = self::formatSDStatus($dao->current_state);
          $listArray[$dao->smart_debit_id]['transaction_id']        = $dao->trxn_id;
          $listArray[$dao->smart_debit_id]['differences']           = $differences;
          $fixmeurl = CRM_Utils_System::url('civicrm/smartdebit/reconciliation/fixmissingcivi', "cid=".$dao->contact_id."&reference_number=".$dao->reference_number,  TRUE, NULL, FALSE, TRUE, TRUE);
          $listArray[$dao->smart_debit_id]['fix_me_url']						= $fixmeurl;
        }
      }
    }
    if ($checkMissingFromCivi) {
      // 3. Transaction Id in Smart Debit but none found in Civi
      $sql  = " SELECT SQL_CALC_FOUND_ROWS cont.id as contact_id ";
      $sql .= " , cont.display_name ";
      $sql .= " , csd1.regular_amount ";
      $sql .= " , csd1.frequency_type ";
      $sql .= " , csd1.frequency_factor ";
      $sql .= " , csd1.current_state ";
      $sql .= " , csd1.payerReference ";
      $sql .= " , csd1.start_date ";
      $sql .= " , csd1.reference_number ";
      $sql .= " , csd1.id as smart_debit_id";
      $sql .= " , csd1.first_name";
      $sql .= " , csd1.last_name";
      $sql .= " FROM civicrm_sd_refresh csd1";
      $sql .= " LEFT JOIN civicrm_contribution_recur ctrc ON ctrc.trxn_id = csd1.reference_number";
      $sql .= " LEFT JOIN civicrm_contact cont ON cont.id = csd1.payerReference";
      $sql .= " WHERE ( csd1.current_state = %1 OR csd1.current_state = %2 ) ";
      $sql .= " AND ctrc.id IS NULL";
      // Filter records that have an amount recorded against them or not
      if ($hasAmount) {
        $sql .= " AND (csd1.regular_amount IS NOT NULL AND csd1.regular_amount != ' ')";
      }
      else {
        $sql .= " AND (csd1.regular_amount IS NULL OR csd1.regular_amount = ' ')";
      }
      // Filter records with no valid contact ID
      if ($hasContact) {
        $sql .= " AND contact_id IS NOT NULL";
      }
      else {
        $sql .= " AND contact_id IS NULL";
      }
      $params = array( 1 => array( 10, 'Int' ), 2 => array(1, 'Int') );
      $dao = CRM_Core_DAO::executeQuery( $sql, $params);
      while ($dao->fetch()) {
        $differences = 'Transaction ID not Found in CiviCRM';
        $transactionRecordFound = false;

        CRM_Core_Error::debug_log_message('contact_id: '.$dao->contact_id);
        // Add records with no valid contact ID
        if (!empty($dao->contact_id)) {
          // Set values for records with a valid contact ID
          $differences .= ' But Contact Found Using Smart Debit payerReference ' . $dao->payerReference;
          $missingContactID = $dao->contact_id;
          $missingContactName = $dao->display_name;
          $fixmeUrl = CRM_Utils_System::url('civicrm/smartdebit/reconciliation/fixmissingcivi', "cid=" . $dao->contact_id . "&reference_number=" . $dao->reference_number, TRUE, NULL, FALSE, TRUE, TRUE);
        }
        elseif (empty($dao->contact_id)) {
          // Set values for records with no valid contact ID in CiviCRM
          $missingContactID = 0;
          $missingContactName = $dao->first_name.' '.$dao->last_name;
          $fixmeUrl = CRM_Utils_System::url('civicrm/smartdebit/reconciliation/fixmissingcivi', "reference_number=".$dao->reference_number,  TRUE, NULL, FALSE, TRUE, TRUE);
        }
        // Add the record
        $listArray[$dao->smart_debit_id]['fix_me_url'] = $fixmeUrl;
        $listArray[$dao->smart_debit_id]['recordFound'] = $transactionRecordFound;
        $listArray[$dao->smart_debit_id]['contact_id'] = $missingContactID;
        $listArray[$dao->smart_debit_id]['contact_name'] = $missingContactName;
        $listArray[$dao->smart_debit_id]['differences'] = $differences;
        $listArray[$dao->smart_debit_id]['sd_contact_id'] = $dao->payerReference;
        $listArray[$dao->smart_debit_id]['sd_start_date'] = $dao->start_date;
        $listArray[$dao->smart_debit_id]['sd_frequency_type'] = $dao->frequency_type;
        $listArray[$dao->smart_debit_id]['sd_frequency_factor'] = $dao->frequency_factor;
        $listArray[$dao->smart_debit_id]['sd_amount'] = $dao->regular_amount;
        $listArray[$dao->smart_debit_id]['sd_contribution_status_id'] = self::formatSDStatus($dao->current_state);
        $listArray[$dao->smart_debit_id]['transaction_id'] = $dao->reference_number;
        $listArray[$dao->smart_debit_id]['sd_frequency'] = $dao->frequency_type;

        // We've found a contact id matching that in smart debit
        // Need to determine if its a correupt renewal or something
        // i.e. there is a pending payment for the recurring record and the recurring record itself
      }
      $query = "SELECT FOUND_ROWS()";
      $totalRows = CRM_Core_DAO::singleValueQuery($query);
    }

    if ($checkMissingFromSD) {
      // 4. Transaction Id in Civi but none in Smart Debit
      $arrayIndex = 1;
      $sql  = " SELECT SQL_CALC_FOUND_ROWS ctrc.id contribution_recur_id ";
      $sql .= " , ctrc.contact_id ";
      $sql .= " , cont.display_name ";
      $sql .= " , ctrc.payment_instrument_id ";
      $sql .= " , opva.label payment_instrument ";
      $sql .= " , ctrc.start_date ";
      $sql .= " , ctrc.amount ";
      $sql .= " , ctrc.trxn_id  ";
      $sql .= " , ctrc.contribution_status_id ";
      $sql .= " FROM ";
      $sql .= " civicrm_contribution_recur ctrc";
      $sql .= " LEFT JOIN civicrm_contact cont ON (ctrc.contact_id = cont.id) ";
      $sql .= " LEFT JOIN civicrm_option_value opva ON (ctrc.payment_instrument_id = opva.value) ";
      $sql .= " LEFT JOIN civicrm_option_group opgr ON (opgr.id = opva.option_group_id) ";
      $sql .= " LEFT JOIN civicrm_sd_refresh csd ON csd.reference_number = ctrc.trxn_id ";
      $sql .= " WHERE opgr.name = 'payment_instrument' ";
      $sql .= " AND   opva.label = 'Direct Debit' ";
      $sql .= " AND   csd.id IS NULL LIMIT 100 ";
      $dao = CRM_Core_DAO::executeQuery( $sql );

      while ($dao->fetch()) {
        $transactionRecordFound = false;
        $differences = 'Transaction: ' .$dao->trxn_id. ' not Found in Smart Debit';
        $listArray[$arrayIndex]['recordFound']  = $transactionRecordFound;
        $listArray[$arrayIndex]['contact_id']   = $dao->contact_id;
        $listArray[$arrayIndex]['contact_name'] = $dao->display_name;
        $listArray[$arrayIndex]['differences']  = $differences;
        $arrayIndex = $arrayIndex + 1;
      }
      $query = "SELECT FOUND_ROWS()";
      $totalRows = CRM_Core_DAO::singleValueQuery($query);
    }
    if ($checkMissingFromCivi || $checkMissingFromSD) {
      $title = 'Showing '.count($listArray).' of '.$totalRows.' Difference(s)';
    } else {
      $title = 'Found '.count($listArray).' Difference(s)';
    }

    $this->assign('totalRows', $totalRows);
    $this->assign('listArray', $listArray);
    CRM_Utils_System::setTitle('Smart Debit Reconciliation');
  }

  static function getCleanSmartDebitAmount($amount) {
    $numeric_filtered = filter_var($amount, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    return($numeric_filtered);
  }

  /* This function is used when there is a pending recur record and a incomplete transaction
   * This situation normally arises when the callback to the IPN failed or something
   * The code was pulled from the PostProcess hook code in the Direct Debit extension removing anything unecessary
   */
  function repair_corrupt_in_civicrm_record($params) {
    CRM_Core_Error::debug_log_message('SmartDebitReconciliation_repair_corrupt_in_civicrm_record: Not implemented');
    return;
    // FIXME: There are too many undefined vars etc here so disabled until someone has time to fix!
    $aContribValue  = array();
    $aContribParam  = array( 'contact_id' => $params['contact_id'] );
    $aContribReturn = array( 'id'
    , 'contribution_recur_id'
    );
    CRM_Core_DAO::commonRetrieve( 'CRM_Contribute_DAO_Contribution'
      , $aContribParam
      , $aContribValue
      , $aContribReturn
    );
    $contributionID      = $aContribValue['id'];
    $contributionRecurID = $aContribValue['contribution_recur_id'];
    $start_date     = urlencode( $form->_params['start_date'] );

    $paymentProcessorType = urlencode( $form->_paymentProcessor['payment_processor_type'] );
    $membershipID         = urlencode( $form->_values['membership_id'] );
    $contactID            = urlencode( $form->getVar( '_contactID' ) );
    $invoiceID            = urlencode( $form->_params['invoiceID'] );
    $amount               = urlencode( $form->_params['amount'] );
    $trxn_id              = urlencode( $form->_params['trxn_id'] );
    $collection_day       = urlencode( $form->_params['preferred_collection_day'] );

    CRM_Core_Error::debug_log_message( 'paymentProcessorType='.$paymentProcessorType);
    CRM_Core_Error::debug_log_message( 'paymentType='.$paymentType);
    CRM_Core_Error::debug_log_message( 'membershipID='.$membershipID);
    CRM_Core_Error::debug_log_message( 'contributionID='.$contributionID);
    CRM_Core_Error::debug_log_message( 'contactID='.$contactID);
    CRM_Core_Error::debug_log_message( 'invoiceID='.$invoiceID);
    CRM_Core_Error::debug_log_message( 'amount='.$amount);
    CRM_Core_Error::debug_log_message( 'isRecur='.$isRecur);
    CRM_Core_Error::debug_log_message( 'trxn_id='.$trxn_id);
    CRM_Core_Error::debug_log_message( 'start_date='.$start_date);
    CRM_Core_Error::debug_log_message( 'collection_day='.$collection_day);
    CRM_Core_Error::debug_log_message( 'contributionRecurID:' .$contributionRecurID );
    CRM_Core_Error::debug_log_message( 'CIVICRM_UF_BASEURL='.CIVICRM_UF_BASEURL);

    $query = "processor_name=".$paymentProcessorType."&module=contribute&contactID=".$contactID."&contributionID=".$contributionID."&membershipID=".$membershipID."&invoice=".$invoiceID."&mc_gross=".$amount."&payment_status=Completed&txn_type=recurring_payment&contributionRecurID=$contributionRecurID&txn_id=$trxn_id&first_collection_date=$start_date&collection_day=$collection_day";

    // Get the recur ID for the contribution
    $url = CRM_Utils_System::url(
      'civicrm/payment/ipn', // $path
      $query,
      FALSE, // $absolute
      NULL, // $fragment
      FALSE, // $htmlize
      FALSE, // $frontend
      FALSE // $forceBackend
    );

    $url = CIVICRM_UF_BASEURL.$url;
    call_CiviCRM_IPN($url);
    renew_membership_by_one_period($membershipID);
  }

  /*
   * This is the main controlling function for fixing reconciliation records
   * Three possibilities
   *  1. Membership Not Selected, Recurring Not Selected
   *     - Create a Recurring Record only i.e. Must be regular Donor
   *  2. Membership Selected, Recurring Not Selected
   *     - Create a Recurring Record and link to the selected membership
   *  3. Membership Selected, Recurring Selected
   *     - Fix the recurring Record and link the membership and recurring
   *
   * In All cases the recurring details are taken from Smart Debit so its crucial this is correct first
   */
  static function repair_missing_from_civicrm_record($params) {
    foreach (array(
               'contact_id',
               'payer_reference') as $required) {

      if (!isset($params[$required]) || empty($params[$required])) {
        throw new InvalidArgumentException("Missing params[$required]");
      }
    }

    // Get the Smart Debit details for the payer
    $smartDebitResponse = CRM_DirectDebit_Sync::getSmartDebitPayerContactDetails($params['payer_reference']);

    foreach ($smartDebitResponse as $key => $smartDebitRecord) {
      // Setup params for the relevant rec
      list($params['recur_frequency_unit'], $params['recur_frequency_interval']) =
        CRM_DirectDebit_Base::translateSmartDebitFrequencytoCiviCRM($smartDebitRecord['frequency_type'], $smartDebitRecord['frequency_factor']);
      $params['amount'] = self::getCleanSmartDebitAmount($smartDebitRecord['regular_amount']);
      $params['recur_start_date'] = $smartDebitRecord['start_date'].' 00:00:00';
      $params['recur_next_payment_date'] = $smartDebitRecord['start_date'].' 00:00:00';
      $params['payment_processor_id'] = CRM_Core_Payment_Smartdebitdd::getSmartDebitPaymentProcessorID();
      $params['payment_instrument_id'] = CRM_DirectDebit_Base::getDefaultPaymentInstrumentID();
      $params['trxn_id'] = $params['payer_reference'];
      $params['current_state'] = $smartDebitRecord['current_state'];
      list($y, $m, $d) = explode('-', $smartDebitRecord['start_date']);
      $params['cycle_day'] = $d;

      // First Check if a recurring record has beeen selected
      if ((!isset($params['contribution_recur_id']) || empty($params['contribution_recur_id']))) {
        // Create the Recurring
        self::create_recur($params);
      } else {
        // Repair the Recurring
        self::repair_recur($params);
      }

      /* First Check if the membership has beeen selected */
      if ((isset($params['membership_id']) && !empty($params['membership_id']))) {
        // Link it to the Recurring Record
        self::link_membership_to_recur($params);
      }
    }
  }

  /**
   * This is used when the fix process is used on the reconciliation
   * It should ensure the recur details match those of the smart debit record
   *
   * @param $params
   */
  static function repair_recur(&$params) {
    foreach (array(
               'contribution_recur_id',
               'contact_id',
               'recur_frequency_interval',
               'amount',
               'recur_start_date',
               'recur_next_payment_date',
               'recur_frequency_unit',
               'payment_processor_id',
               'payment_instrument_id',
               'trxn_id',
               'cycle_day') as $required) {

      if (!isset($params[$required]) || empty($params[$required])) {
        throw new InvalidArgumentException("Missing params[$required]");
      }
    }

    $contribution_status_id = 5; // In Progress
    if (!($params['current_state'] == 10 || $params['current_state'] == 1)) {
      $contribution_status_id = 3;
    }
    // Create contribution recur record
    $recurParams = array(
      'version' => 3,
      'contribution_recur_id' => $params['contribution_recur_id'],
      'id' => $params['contribution_recur_id'],
      'contact_id' => $params['contact_id'],
      'frequency_interval' => $params['recur_frequency_interval'],
      'amount' => $params['amount'], /* TODO Need to find the amount to charge */
      'contribution_status_id' => $contribution_status_id,
      'start_date' => $params['recur_start_date'],
      'next_sched_contribution' => $params['recur_next_payment_date'],
      'auto_renew' => '1',
      'currency' => 'GBP',
      'frequency_unit' => $params['recur_frequency_unit'],
      'payment_processor_id' => $params['payment_processor_id'],
      'payment_instrument_id' => $params['payment_instrument_id'],
      'contribution_type_id' => '2', /* TODO Get the contribution type ID for recurring memberships */
      'trxn_id' => $params['trxn_id'],
      'create_date' => $params['recur_start_date'],
      'cycle_day' => $params['cycle_day'],
    );
    $recurResult = civicrm_api("ContributionRecur","create", $recurParams);

    // Populate the membership id on repair recur
    $params['contribution_recur_id'] = $recurResult['id'];

    if( $params['contribution_recur_id'] && $params['membership_id']) {
      $columnExists = CRM_Core_DAO::checkFieldExists('civicrm_contribution_recur', 'membership_id');
      if($columnExists) {
        $query = "
                UPDATE civicrm_contribution_recur
                SET membership_id = %1
                WHERE id = %2 ";

        $query_params = array( 1 => array( $params['membership_id'], 'Int' ), 2 => array($params['contribution_recur_id'], 'Int') );
        $dao = CRM_Core_DAO::executeQuery($query, $query_params);
      }
    }
  }

  /**
   * This is used when we need to create a new recurring record
   * @param $params
   */
  function create_recur(&$params) {
    foreach (array(
               'contact_id',
               'recur_frequency_interval',
               'amount',
               'recur_start_date',
               'recur_next_payment_date',
               'recur_frequency_unit',
               'payment_processor_id',
               'payment_instrument_id',
               'trxn_id',
               'cycle_day') as $required) {

      if (!isset($params[$required]) || empty($params[$required])) {
        throw new InvalidArgumentException("Missing params[$required]");
      }
    }
    $contribution_status_id = 5; // In Progress
    if (!($params['current_state'] == 10 || $params['current_state'] == 1)) {
      $contribution_status_id = 3;
    }
    // Create contribution recur record
    $recurParams = array(
      'version' => 3,
      'contact_id' => $params['contact_id'],
      'frequency_interval' => $params['recur_frequency_interval'],
      'amount' => $params['amount'], /* TODO Need to find the amount to charge */
      'contribution_status_id' => $contribution_status_id,
      'start_date' => $params['recur_start_date'],
      'next_sched_contribution' => $params['recur_next_payment_date'],
      'auto_renew' => '1',
      'currency' => 'GBP',
      'frequency_unit' => $params['recur_frequency_unit'],
      'payment_processor_id' => $params['payment_processor_id'],
      'payment_instrument_id' => $params['payment_instrument_id'],
      'contribution_type_id' => '2', /* TODO Get the contribution type ID for recurring memberships */
      'trxn_id' => $params['trxn_id'],
      'create_date' => $params['recur_start_date'],
      'cycle_day' => $params['cycle_day'],
    );
    $recurResult = civicrm_api("ContributionRecur","create", $recurParams);

    $params['contribution_recur_id'] = $recurResult['id'];
    // // Populate the membership id on create recur
    if( $params['contribution_recur_id'] && $params['membership_id'] ) {
      $columnExists = CRM_Core_DAO::checkFieldExists('civicrm_contribution_recur', 'membership_id');
      if($columnExists) {
        $query = "
                UPDATE civicrm_contribution_recur
                SET membership_id = %1
                WHERE id = %2 ";

        $query_params = array( 1 => array( $params['membership_id'], 'Int' ), 2 => array($params['contribution_recur_id'], 'Int') );
        $dao = CRM_Core_DAO::executeQuery($query, $query_params);
      }
    }
  }

  /**
   * This is used when we need to create a linked mem
   * @param $params
   */
  function link_membership_to_recur(&$params) {
    foreach (array(
               'contact_id',
               'membership_id',
               'contribution_recur_id') as $required) {

      if (!isset($params[$required]) || empty($params[$required])) {
        throw new InvalidArgumentException("Missing params[$required]");
      }
    }

    // Update the source table to say we're done
    $selectDDSql     =  " UPDATE civicrm_membership ";
    $selectDDSql     .= " SET contribution_recur_id = %3 ";
    $selectDDSql     .= " WHERE id = %1 ";
    $selectDDSql     .= " AND contact_id = %2 ";
    $selectDDParams  = array( 1 => array( $params['membership_id'] , 'Integer' )
    , 2 => array( $params['contact_id'] , 'Integer' )
    , 3 => array( $params['contribution_recur_id'] , 'Integer' )
    );
    $daoMembershipType = CRM_Core_DAO::executeQuery( $selectDDSql, $selectDDParams );
  }

  static function insertSmartDebitToTable() {
    // If no civicrm_sd, then create that table
    if(!CRM_Core_DAO::checkTableExists('civicrm_sd_refresh')) {
      $creatSql = "CREATE TABLE `civicrm_sd_refresh` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
            `first_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
            `last_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
            `email_address` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
            `address_1` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
            `address_2` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
            `address_3` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
            `town` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
            `county` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
            `postcode` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
            `first_amount` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
            `regular_amount` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
            `frequency_type` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
            `frequency_factor` int(10) unsigned DEFAULT NULL,
            `start_date` datetime NOT NULL,
            `current_state` int(10) unsigned DEFAULT NULL,					
            `reference_number` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
            `payerReference` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
            PRIMARY KEY (`id`)
           ) ENGINE=InnoDB AUTO_INCREMENT=350 DEFAULT CHARSET=latin1";

      CRM_Core_DAO::executeQuery($creatSql);
      $alterQuery = "alter table civicrm_sd_refresh add index reference_number_idx(reference_number)";
      CRM_Core_DAO::executeQuery($alterQuery);
    }
    // if the civicrm_sd table exists, then empty it
    else {
      $emptySql = "TRUNCATE TABLE `civicrm_sd_refresh`";
      CRM_Core_DAO::executeQuery($emptySql);
    }
    CRM_Core_Error::debug_var('CRM_SmartdebitReconciliation_Form_SmartdebitReconciliationList getSmartDebitPayments All', 'Started');
    $smartDebitArray = CRM_DirectDebit_Sync::getSmartDebitPayerContactDetails();
    CRM_Core_Error::debug_var('CRM_SmartdebitReconciliation_Form_SmartdebitReconciliationList getSmartDebitPayments All', 'Ended');
    CRM_Core_Error::debug_var('smart debit array count', count($smartDebitArray));
    if (empty($smartDebitArray)) {
      return FALSE;
    }
    CRM_Core_Error::debug_var('CRM_SmartdebitReconciliation_Form_SmartdebitReconciliationList Insert Into civicrm_sd_refresh table', 'Started');
    foreach ($smartDebitArray as $key => $smartDebitRecord) {
      $sql = "INSERT INTO `civicrm_sd_refresh`(
            `title`,
            `first_name`,
            `last_name`, 
            `email_address`,
            `address_1`, 
            `address_2`, 
            `address_3`, 
            `town`, 
            `county`,
            `postcode`,
            `first_amount`,
            `regular_amount`,
            `frequency_type`,
            `frequency_factor`,
            `start_date`,
            `current_state`,
            `reference_number`,
            `payerReference`
            ) 
            VALUES (%1,%2,%3,%4,%5,%6,%7,%8,%9,%10,%11,%12,%13,%14,%15,%16,%17, %18)";
      $params = array(
        1 => array( self::getArrayFieldValue($smartDebitRecord, 'title', 'NULL'), 'String' ),
        2 => array( self::getArrayFieldValue($smartDebitRecord, 'first_name', 'NULL'), 'String' ),
        3 => array( self::getArrayFieldValue($smartDebitRecord, 'last_name', 'NULL'), 'String' ),
        4 => array( self::getArrayFieldValue($smartDebitRecord, 'email_address', 'NULL'),  'String'),
        5 => array( self::getArrayFieldValue($smartDebitRecord, 'address_1', 'NULL'), 'String' ),
        6 => array( self::getArrayFieldValue($smartDebitRecord, 'address_2', 'NULL'), 'String' ),
        7 => array( self::getArrayFieldValue($smartDebitRecord, 'address_3', 'NULL'), 'String' ),
        8 => array( self::getArrayFieldValue($smartDebitRecord, 'town', 'NULL'), 'String' ),
        9 => array( self::getArrayFieldValue($smartDebitRecord, 'county', 'NULL'), 'String' ),
        10 => array( self::getArrayFieldValue($smartDebitRecord, 'postcode', 'NULL'), 'String' ),
        11 => array( self::getCleanSmartDebitAmount(self::getArrayFieldValue($smartDebitRecord, 'first_amount', 'NULL')), 'String' ),
        12 => array( self::getCleanSmartDebitAmount(self::getArrayFieldValue($smartDebitRecord, 'regular_amount', 'NULL')), 'String' ),
        13 => array( self::getArrayFieldValue($smartDebitRecord, 'frequency_type', 'NULL'), 'String' ),
        14 => array( self::getArrayFieldValue($smartDebitRecord, 'frequency_factor', 'NULL'), 'Int' ),
        15 => array( self::getArrayFieldValue($smartDebitRecord, 'start_date', 'NULL'), 'String' ),
        16 => array( self::getArrayFieldValue($smartDebitRecord, 'current_state', 'NULL'), 'Int' ),
        17 => array( self::getArrayFieldValue($smartDebitRecord, 'reference_number', 'NULL'), 'String' ),
        18 => array( self::getArrayFieldValue($smartDebitRecord, 'payerReference', 'NULL'), 'String' ),
      );
      CRM_Core_DAO::executeQuery($sql, $params);
    }
    CRM_Core_Error::debug_var('CRM_SmartdebitReconciliation_Form_SmartdebitReconciliationList Insert Into civicrm_sd_refresh table', 'Ended');
    $mandateFetchedCount = count($smartDebitArray);
    return $mandateFetchedCount;
  }

  static function getArrayFieldValue($array, $field, $value) {
    if (!isset($array[$field])) {
      return $value;
    }
    else {
      return $array[$field];
    }
  }

  /**
   * Format SmartDebit Status ID for display
   * @param $sdStatus
   * @return string
   */
  static function formatSDStatus($sdStatus)
  {
    switch ($sdStatus) {
      case 0: // Draft
        return 'Draft';
      case 1: // New
        return 'New';
      case 10: // Live
        return 'Live';
      case 11: // Cancelled
        return 'Cancelled';
      case 12: // Rejected
        return 'Rejected';
    }
  }
}
