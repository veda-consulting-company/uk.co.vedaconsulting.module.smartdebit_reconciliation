<?php
function civicrm_api3_smartdebitreconciliation_refreshsdmandatesincivi($params) {
  require_once 'CRM/SmartdebitReconciliation/Form/SmartdebitReconciliationList.php';
  $mandateFetched = CRM_SmartdebitReconciliation_Form_SmartdebitReconciliationList::insertSmartDebitToTable();
  if (empty($mandateFetched)) {
    return civicrm_api3_create_error('No mandate fetched from smart debit');
  }
  return civicrm_api3_create_success(array('No of Records refreshed' => $mandateFetched), $params, 'Smartdebitreconciliation', 'refreshsdmandatesincivi');
}
