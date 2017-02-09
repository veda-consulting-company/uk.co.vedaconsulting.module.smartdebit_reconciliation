<?php

require_once 'smartdebit.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function smartdebit_civicrm_config(&$config) {
  _smartdebit_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function smartdebit_civicrm_xmlMenu(&$files) {
  _smartdebit_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function smartdebit_civicrm_install() {
  return _smartdebit_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function smartdebit_civicrm_uninstall() {
  return _smartdebit_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function smartdebit_civicrm_enable() {
  return _smartdebit_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function smartdebit_civicrm_disable() {
  return _smartdebit_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function smartdebit_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _smartdebit_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function smartdebit_civicrm_managed(&$entities) {
  return _smartdebit_civix_civicrm_managed($entities);
}

function smartdebit_civicrm_navigationMenu( &$params ) {
  // get the id of Administer Menu
  $administerMenuId = CRM_Core_DAO::getFieldValue('CRM_Core_BAO_Navigation', 'Administer', 'id', 'name');

  // skip adding menu if there is no administer menu
  if ($administerMenuId) {
    // get the maximum key under adminster menu
    $maxKey = max( array_keys($params[$administerMenuId]['child']));
    $params[$administerMenuId]['child'][$maxKey+1] =  array (
      'attributes' => array (
        'label' => 'Smart Debit Reconciliation',
        'name' => 'Smart Debit Reconciliation',
        'url' => 'civicrm/smartdebit/reconciliation/list?reset=1',
        'permission' => 'administer CiviCRM',
        'operator'   => NULL,
        'separator'  => TRUE,
        'parentID'   => $administerMenuId,
        'navID'      => $maxKey+1,
        'active'     => 1
      )
    );
  }
}

function smartdebit_civicrm_pageRun(&$page) {
  $pageName = $page->getVar('_name');
  // To avoid standalone new contribution fail
  if ($pageName == 'CRM_Contribute_Page_Tab' && $page->getVar('_contactId')) {
    $paymentProcessorType   = CRM_Core_PseudoConstant::paymentProcessorType(false, null, 'name');
    if(!CRM_Utils_Array::key('Smart Debit', $paymentProcessorType)) {
      return;
    }
    $query = "
      SELECT cr.id, cr.trxn_id FROM civicrm_contribution_recur cr
      INNER JOIN civicrm_payment_processor cpp ON cpp.id = cr.payment_processor_id
      INNER JOIN civicrm_payment_processor_type cppt ON cppt.id = cpp.payment_processor_type_id
      LEFT JOIN civicrm_option_value opva ON (cr.payment_instrument_id = opva.value)
      LEFT JOIN civicrm_option_group opgr ON (opgr.id = opva.option_group_id) 
      WHERE cppt.name = %1 AND cr.contact_id = %2 AND opgr.name = %3 AND opva.label = %4";
    
    $queryParams = array (
      1 => array('Smart Debit', 'String'),
      2 => array($page->getVar('_contactId'), 'Int'),
      3 => array('payment_instrument', 'String'),
      4 => array('Direct Debit', 'String'),
    );
    
    $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
    $contributionRecurDetails = array();
    while ($dao->fetch()) {
      if ($dao->trxn_id) {
        $smartDebitResponse = CRM_SmartdebitReconciliation_Form_SmartdebitReconciliationList::getSmartDebitPayments($dao->trxn_id);
        foreach ($smartDebitResponse[0] as $key => $value) {
          $contributionRecurDetails[$dao->id][$key] = $value;
        }
      }
    }
    $contributionRecurDetails = json_encode($contributionRecurDetails);
    $page->assign('contributionRecurDetails', $contributionRecurDetails);
    $page->assign('smartdebit', TRUE);
  }
}