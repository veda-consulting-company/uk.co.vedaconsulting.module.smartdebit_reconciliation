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
//include("smart_debit_includes.php");

/**
 * This class provides the functionality to delete a group of
 * contacts. This class provides functionality for the actual
 * addition of contacts to groups.
 */

class CRM_SmartdebitReconciliation_Form_SmartdebitReconciliationList extends CRM_Core_Form {

    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess()
  {
        parent::preProcess( );
        return false;

    }

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
    function buildQuickForm( ) {
      
      // FOr smart debit sync purpose
        $sync = CRM_Utils_Array::value('sync', $_GET, '');
        $this->assign('sync', $sync);
        
        require_once 'CRM/Core/Config.php';
        $config = CRM_Core_Config::singleton();
        
        $transactionIdList = "'dummyId'";  //Initialised so have at least one entry in list
  
        $smartDebitArray = self::getSmartDebitPayments();
        $listArray = array();
        
        // The following differences are highlighted
        // 1. Transaction Id in Smart Debit and Civi for the same contact
        // 2. Transaction Id in Smart Debit and Civi for different contacts
        // 3. Transaction Id in Smart Debit but none found in Civi
        // 4. Transaction Id in Civi but none in Smart Debit

				// Loop through Contributions and Highlight Discrepencies
        foreach ($smartDebitArray as $key => $smartDebitRecord) {

            // Start Here
            $transactionIdList .= ", '".$smartDebitRecord['reference_number']."' "; // Transaction ID

            $group_name = "CiviCRM Preferences";

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
            $sql .= " FROM ";
            $sql .= " civicrm_contribution_recur ctrc ";
            $sql .= " LEFT JOIN civicrm_contact cont ON (ctrc.contact_id = cont.id) ";
            $sql .= " LEFT JOIN civicrm_option_value opva ON (ctrc.payment_instrument_id = opva.value) ";
            $sql .= " LEFT JOIN civicrm_option_group opgr ON (opgr.id = opva.option_group_id) ";
            $sql .= " WHERE opgr.name = 'payment_instrument' ";
            $sql .= " AND   opva.label = 'Direct Debit' ";
            $sql .= " AND ctrc.trxn_id = %1 ";

            $params = array( 1 => array( $smartDebitRecord['reference_number'], 'String' ) );
            $dao = CRM_Core_DAO::executeQuery( $sql, $params);

            // Remove first 2 characters (Ascii characters 194 & 163)
            $regularAmount = substr($smartDebitRecord['regular_amount'], 2);
            if ($dao->fetch()) {
                // Smart Debit Record Found
                // 1. Transaction Id in Smart Debit and Civi for the same contact

                $transactionRecordFound = true;
                $different = false;
                $differences = '';
                $separator = '';
                $separatorCharacter = ' | ';


								if (CRM_Utils_Array::value('checkAmount', $_GET)) {
									if ($regularAmount != $dao->amount) {
											$different = true;
											$differences .= 'Amount';
											$separator = $separatorCharacter;
									}
								}
								
								if (CRM_Utils_Array::value('checkFrequency', $_GET)) {
									if ($smartDebitRecord['frequency_type'] == 'M' && $dao->frequency_unit != 'month' ) {
											$different = true;
											$differences .= 'Frequency';
											$separator = $separatorCharacter;
									}
									if ($smartDebitRecord['frequency_type'] == 'Y' && $dao->frequency_unit != 'year' ) {
											$different = true;
											$differences .= 'Frequency';
											$separator = $separatorCharacter;
									}
									if ($smartDebitRecord['frequency_type'] == 'Q' && ($dao->frequency_unit != 'month' || $dao->frequency_interval != '3')) {
											$different = true;
											$differences .= 'Frequency';
											$separator = $separatorCharacter;
									}
								}
								
								/* PS No ppoint checking the start date I dont think as converted data always fails
                if (strtotime($smartDebitRecord['start_date']) != strtotime($dao->start_date)) {
                    $different = true;
                    $differences .= $separator. 'Start Date';
                    $separator = $separatorCharacter;
                }
								 * 
								 */
								
							  /* Smart Debit statuses are as follows
									0 Draft
									1 New
									10 Live
									11 Cancelled
									12 Rejected
								 * 
								 */
								// First case check if Smart Debit is new or livet then CiviCRM is in progress
								if (CRM_Utils_Array::value('checkStatus', $_GET)) {
									if (($smartDebitRecord['current_state'] == 10 || $smartDebitRecord['current_state'] == 1) && ($dao->contribution_status_id != 5)) {
											$different = true;
											$differences .= $separator. 'Status';
											$separator = $separatorCharacter;
									}
								}
								
                // 2. Transaction Id in Smart Debit and Civi for different contacts
								if (CRM_Utils_Array::value('checkPayerReference', $_GET)) {
									if ($smartDebitRecord['payerReference'] != $dao->contact_id) {
											$different = true;
											$differences .= $separator. 'Payer Reference';
											$separator = $separatorCharacter;
									}
								}

                // If different then
                if ($different) {
                  $financialType  = CRM_Core_DAO::getFieldValue('CRM_Financial_DAO_FinancialType', $dao->financial_type_id, 'name', 'id');

                    $listArray[$key]['recordFound']           = $transactionRecordFound;
                    $listArray[$key]['contribution_recur_id'] = $dao->contribution_recur_id;
                    $listArray[$key]['contribution_type']     = $financialType;
                    $listArray[$key]['contact_id']            = $dao->contact_id;
                    $listArray[$key]['sd_contact_id']         = $smartDebitRecord['payerReference'];
                    $listArray[$key]['contact_name']          = $dao->display_name;
                    $listArray[$key]['payment_instrument']    = $dao->payment_instrument;
                    $listArray[$key]['start_date']            = $dao->start_date;
                    $listArray[$key]['sd_start_date']         = $smartDebitRecord['start_date'];
                    $listArray[$key]['frequency']             = $dao->frequency_unit;
                    $listArray[$key]['sd_frequency']          = $smartDebitRecord['frequency_type'];
                    $listArray[$key]['amount']                = $dao->amount;
                    $listArray[$key]['sd_amount']             = $regularAmount;
                    $listArray[$key]['contribution_status_id']    = $dao->contribution_status_id;
                    $listArray[$key]['sd_contribution_status_id'] = $smartDebitRecord['current_state'];
                    $listArray[$key]['transaction_id']        = $dao->trxn_id;
                    $listArray[$key]['differences']           = $differences;
//print_r($smartDebitRecord);
//print_r($dao);
//die;
                }

            }
            else {
								if (CRM_Utils_Array::value('checkMissingFromCivi', $_GET) && ($smartDebitRecord['current_state'] == 10 || $smartDebitRecord['current_state'] == 1)) {

									$listArray[$key]['fix_me_url']								= '/civicrm/smartdebit/reconciliation/fixmissingcivi?reference_number='.$smartDebitRecord['reference_number'];									

									// 3. Transaction Id in Smart Debit but none found in Civi                
									$sql  = " SELECT cont.id ";
									$sql .= " ,      cont.display_name ";
									$sql .= " FROM civicrm_contact cont "; 
									$sql .= " WHERE cont.id = %1 ";

									$params = array( 1 => array( $smartDebitRecord['payerReference'], 'String' ) );
									$dao = CRM_Core_DAO::executeQuery( $sql, $params);

									if ($dao->fetch()) {
										$missingContactID = $dao->id;
										$missingContactName = $dao->display_name;
										
										// We've found a contact id matching that in smart debit
										// Need to determine if its a correupt renewal or something
										// i.e. there is a pending payment for the recurring record and the recurring record itself 
										$listArray[$key]['fix_me_url']								= 'civicrm/smartdebit/reconciliation/fix-corrupt-recurring-rec?reference_number='.$smartDebitRecord['reference_number'];									
										
									} else {
										$missingContactID = 0;
										$missingContactName = $smartDebitRecord['first_name'].' '.$smartDebitRecord['last_name'];
									}
									//Contact ID for this transaction found but no recurring contribution exists in Civi for this Contact ID
									$transactionRecordFound = false;

									$differences = 'Transaction: ' .$smartDebitRecord['reference_number']. ' not Found in Civi';

									$listArray[$key]['recordFound']								= $transactionRecordFound;
									$listArray[$key]['contact_id']								= $missingContactID;
									$listArray[$key]['contact_name']							= $missingContactName;
									$listArray[$key]['differences']								= $differences;
									$listArray[$key]['sd_contact_id']							= $smartDebitRecord['payerReference'];
									$listArray[$key]['sd_start_date']							= $smartDebitRecord['start_date'];
									$listArray[$key]['sd_frequency']							= $smartDebitRecord['frequency_type'];
									$listArray[$key]['sd_amount']									= $regularAmount;
									$listArray[$key]['sd_contribution_status_id'] = $smartDebitRecord['current_state'];
                  $listArray[$key]['transaction_id']            = $smartDebitRecord['reference_number'];
                  $listArray[$key]['sd_frequency']              = $smartDebitRecord['frequency_type'];

							  }
            }
        } 
        
			  if (CRM_Utils_Array::value('checkMissingFromSD', $_GET)) {

					// 4. Transaction Id in Civi but none in Smart Debit
					$arrayIndex = $key + 1;

					$sql  = " SELECT ctrc.id contribution_recur_id ";
					$sql .= " , ctrc.contact_id ";
					$sql .= " , cont.display_name ";
					$sql .= " , ctrc.payment_instrument_id ";
					$sql .= " , opva.label payment_instrument ";
					$sql .= " , ctrc.start_date ";
					$sql .= " , ctrc.amount ";
					$sql .= " , ctrc.trxn_id  ";
					$sql .= " , ctrc.contribution_status_id ";
					$sql .= " FROM ";
					$sql .= " civicrm_contribution_recur ctrc ";
					$sql .= " LEFT JOIN civicrm_contact cont ON (ctrc.contact_id = cont.id) ";
					$sql .= " LEFT JOIN civicrm_option_value opva ON (ctrc.payment_instrument_id = opva.value) ";
					$sql .= " LEFT JOIN civicrm_option_group opgr ON (opgr.id = opva.option_group_id) ";
					$sql .= " WHERE opgr.name = 'payment_instrument' ";
					$sql .= " AND   opva.label = 'Direct Debit' ";
					$sql .= " AND ctrc.trxn_id NOT IN ( $transactionIdList )";

					$dao = CRM_Core_DAO::executeQuery( $sql );

					while ($dao->fetch()) {

							$differences = 'Transaction: ' .$dao->trxn_id. ' not Found in Smart Debit';

							$listArray[$arrayIndex]['recordFound']  = $transactionRecordFound;
							$listArray[$arrayIndex]['contact_id']   = $dao->contact_id;
							$listArray[$arrayIndex]['contact_name'] = $dao->display_name;
							$listArray[$arrayIndex]['differences']  = $differences;

							$arrayIndex = $arrayIndex + 1;

					}
				}
				CRM_Utils_System::setTitle( 'Found '.count($listArray).' Difference(s)' );
				
				// Shrink the array if its > 100
				$newListArray = array();
				$newListCounter = 0;
				foreach ($listArray as $key => $listArrayRec) {
          /*
					if ($newListCounter > 199) { 
						break;
					}
           * 
           */
					$newListArray[$key] = $listArrayRec;
					$newListCounter++;
				}
				
        $this->assign( 'listArray', $newListArray );
    }

  /**
   * process the form after the input has been submitted and validated
   *
   * @access public
   * @return None
   */
  public function postProcess() {
		
  }//end of function

  function getSmartDebitPayments($referenceNumber) {
    $paymentProcessorType = CRM_Core_PseudoConstant::paymentProcessorType(false, null, 'name');
    $paymentProcessorTypeId = CRM_Utils_Array::key('Smart Debit', $paymentProcessorType);
  
      $sql  = " SELECT user_name ";
      $sql .= " ,      password ";
      $sql .= " ,      signature "; 
      $sql .= " FROM civicrm_payment_processor "; 
      $sql .= " WHERE payment_processor_type_id = %1 "; 
      $sql .= " AND is_test= %2 ";

      $params = array( 1 => array( $paymentProcessorTypeId, 'Integer' )
                     , 2 => array( '0', 'Int' )    
                     );

      $dao = CRM_Core_DAO::executeQuery( $sql, $params);

      if ($dao->fetch()) {

          $username = $dao->user_name;
          $password = $dao->password;
          $pslid    = $dao->signature;

      }
    
    // Send payment POST to the target URL
    $url = "https://secure.ddprocessing.co.uk/api/data/dump?query[service_user][pslid]=$pslid&query[report_format]=XML";
    
    // Restrict to a single payer if we have a reference
    if ($referenceNumber) {
      $url .= "&query[reference_number]=$referenceNumber";
    }
		
    $response = self::requestPost( $url, $username, $password );    

    // Take action based upon the response status
    switch ( strtoupper( $response["Status"] ) ) {
        case 'OK':
        
            $smartDebitArray = array();
						
					  // Cater for a single response
					  if (isset($response['Data']['PayerDetails']['@attributes'])) {
							$smartDebitArray[] = $response['Data']['PayerDetails']['@attributes'];
						} else {
							foreach ($response['Data']['PayerDetails'] as $key => $value) {
							  $smartDebitArray[] = $value['@attributes'];
							}
						}             
            return $smartDebitArray;
            
        default:
            return false;
    }
   
  }
  
    /*************************************************************
      Send a post request with cURL
        $url = URL to send request to
        $data = POST data to send (in URL encoded Key=value pairs)
    *************************************************************/
    function requestPost($url, $username, $password){
        // Set a one-minute timeout for this script
        set_time_limit(160);

        // Initialise output variable
        $output = array();

        $options = array(
                        CURLOPT_RETURNTRANSFER => true, // return web page
                        CURLOPT_HEADER => false, // don't return headers
                        CURLOPT_POST => true,
                        CURLOPT_USERPWD => $username . ':' . $password,
                        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                        CURLOPT_HTTPHEADER => array("Accept: application/xml"),
                        CURLOPT_USERAGENT => "XYZ Co's PHP iDD Client", // Let Webservice see who we are
                        CURLOPT_SSL_VERIFYHOST => false,
                        CURLOPT_SSL_VERIFYPEER => false,
                      );

        $session = curl_init( $url );

        curl_setopt_array( $session, $options );

        // Tell curl that this is the body of the POST
        curl_setopt ($session, CURLOPT_POSTFIELDS, null );

        // $output contains the output string
        $output = curl_exec($session);
        $header = curl_getinfo( $session );

        //Store the raw response for later as it's useful to see for integration and understanding 
        $_SESSION["rawresponse"] = $output;

        if(curl_errno($session)) {
          $resultsArray["Status"] = "FAIL";  
          $resultsArray['StatusDetail'] = curl_error($session);
        }
        else {
          // Results are XML so turn this into a PHP Array
          $resultsArray = json_decode(json_encode((array) simplexml_load_string($output)),1);  

          // Determine if the call failed or not
          switch ($header["http_code"]) {
            case 200:
              $resultsArray["Status"] = "OK";
              break;
            default:
              $resultsArray["Status"] = "INVALID";
          }
        }

        // Return the output
        return $resultsArray;

    } // END function requestPost()

    function getCleanSmartDebitAmount($smartDebitAmount) {
      return(substr($smartDebitAmount, 2));
    }
    
    function translateSmartDebitFrequency($smartDebitFrequency) {
     if ($smartDebitFrequency == 'Q') {
        return 3;
      }
      return 1;
    }

    function translateSmartDebitFrequencyUnit($smartDebitFrequency) {
      if ($smartDebitFrequency == 'Q') {
        return('month' );
      }
      if ($smartDebitFrequency == 'Y') {
        return('year' );
      }			
      return('month' );
    }

		/* This function is used when there is a pending recur record and a incomplete transaction
		 * This situation normally arises when the callback to the IPN failed or something
		 * The code was pulled from the PostProcess hook code in the Direct Debit extension removing anything unecessary
		 */
		function repair_corrupt_in_civicrm_record($params) {
			require_once 'UK_Direct_Debit/Form/Main.php';

      $aContribValue  = array();
      $aContribParam  = array( 'contact_id' => $form->_contactID );
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

			CRM_Core_Error::debug_log_message('uk_direct_debit_civicrm_postProcess #2');

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

			CRM_Core_Error:: debug_log_message( 'uk_direct_debit_civicrm_postProcess query = '.$query);

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

			CRM_Core_Error::debug_log_message('uk_direct_debit_civicrm_postProcess url='.$url);
			uk_direct_debit_call_civicrm_ipn($url);

//dpm($membershipID, "Before renew_membership - membershipID");
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
    function repair_missing_from_civicrm_record($params) {
      foreach (array(
        'contact_id',
        'payer_reference') as $required) {

        if (!isset($params[$required]) || empty($params[$required])) {
            throw new InvalidArgumentException("Missing params[$required]");
        }
      }

      require_once 'UK_Direct_Debit/Form/Main.php';
      
      // Get the Smart Debit details for the payer
      $smartDebitResponse = self::getSmartDebitPayments($params['payer_reference']);

      foreach ($smartDebitResponse as $key => $smartDebitRecord) {
        // Setup params for the relevant rec
        $params['recur_frequency_interval'] = self::translateSmartDebitFrequency($smartDebitRecord['frequency_type']);
        $params['amount'] = self::getCleanSmartDebitAmount($smartDebitRecord['regular_amount']);
        $params['recur_start_date'] = $smartDebitRecord['start_date'].' 00:00:00';
        $params['recur_next_payment_date'] = $smartDebitRecord['start_date'].' 00:00:00';
        $params['recur_frequency_unit'] = self::translateSmartDebitFrequencyUnit($smartDebitRecord['frequency_type']);
        $params['payment_processor_id'] = self::getSmartDebitPaymentProcessorID();
        $params['payment_instrument_id'] = UK_Direct_Debit_Form_Main::getDDPaymentInstrumentID();
        $params['trxn_id'] = $params['payer_reference'];
        $params['cycle_day'] = 99;

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
  
    /* This is used when the fix process is used on the reconciliation
     * It should ensure the recur details match those of the smart debit record
     */
    function repair_recur(&$params) {
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

      // Create contribution recur record
      $recurParams = array(
        'version' => 3,
        'contribution_recur_id' => $params['contribution_recur_id'],
        'id' => $params['contribution_recur_id'],
        'contact_id' => $params['contact_id'],
        'frequency_interval' => $params['recur_frequency_interval'],
        'amount' => $params['amount'], /* TODO Need to find the amount to charge */
        'contribution_status_id' => 5,
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
      //print('About to call recurr create\n');
      //print_r($params);

      $recurResult = civicrm_api("ContributionRecur","create", $recurParams);
      
      // Populate the membership id on repair recur
      $params['contribution_recur_id'] = $recurResult['id'];
      
      if( $params['contribution_recur_id'] && $params['membership_id']) {
        $query = "
            UPDATE civicrm_contribution_recur
            SET membership_id = %1
            WHERE id = %2 ";

        $params = array( 1 => array( $params['membership_id'], 'Int' ), 2 => array($params['contribution_recur_id'], 'Int') );
        $dao = CRM_Core_DAO::executeQuery($query, $params);
      }
    }
    
    /* This is used when we need to create a new recurring record
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
      
      // Create contribution recur record
      $recurParams = array(
        'version' => 3,
        'contact_id' => $params['contact_id'],
        'frequency_interval' => $params['recur_frequency_interval'],
        'amount' => $params['amount'], /* TODO Need to find the amount to charge */
        'contribution_status_id' => 5,
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
      //print('About to call recurr create\n');
      //print_r($params);

      $recurResult = civicrm_api("ContributionRecur","create", $recurParams);
      
      $params['contribution_recur_id'] = $recurResult['id'];
      // // Populate the membership id on create recur
      if( $params['contribution_recur_id'] && $params['membership_id'] ) {
        $query = "
            UPDATE civicrm_contribution_recur
            SET membership_id = %1
            WHERE id = %2 ";

        $params = array( 1 => array( $params['membership_id'], 'Int' ), 2 => array($params['contribution_recur_id'], 'Int') );
        $dao = CRM_Core_DAO::executeQuery($query, $params);
      }
      //print_r($recurResult);
    }

    /* This is used when we need to create a linked mem
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
          
      //$membershipResult = civicrm_api("Membership","create", $params);
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
    
    function getSmartDebitPaymentProcessorID() {
      // Get all contacts who have the tag set
      $selectSql     =  " SELECT id";
      $selectSql     .= " FROM civicrm_payment_processor cpp ";
      $selectSql     .= " WHERE cpp.class_name = %1 AND cpp.is_test = 0";
      $selectParams  = array( 1 => array( 'uk.co.vedaconsulting.payment.smartdebitdd' , 'String' ) );
      $dao           = CRM_Core_DAO::executeQuery( $selectSql, $selectParams );
      
      while ($dao->fetch()) {
          return $dao->id;
      }
      return 0;
    }
}
