<?php

/**
 * Class CRM_SmartdebitReconciliation_Page_MembershipRecurDetails
 *
 * This page is used to confirm and submit the fix for the contact record
 * Path: civicrm/smartdebit/reconciliation/fix-contact-rec-confirm
 */
class CRM_SmartdebitReconciliation_Form_MembershipRecurDetailsConfirm extends CRM_Core_Form {

  public function buildQuickForm() {
    // Get parameters
    $cid = CRM_Utils_Array::value('cid', $_GET);
    $mid = CRM_Utils_Array::value('mid', $_GET);
    $cr_id = CRM_Utils_Array::value('cr_id', $_GET);
    $reference_number = CRM_Utils_Array::value('reference_number', $_GET);
    $this->addElement('hidden', 'cid', 'cid');
    $this->addElement('hidden', 'mid');
    $this->addElement('hidden', 'cr_id');
    $this->addElement('hidden', 'reference_number');
    //$this->assign('elementNames', $this->getRenderableElementNames());
    $this->assign('reference_number', $reference_number);
    // Get contact details if set
    if(!empty($cid)){
      $contact = CRM_SmartdebitReconciliation_Utils::getContactDetails($cid);
      $address = CRM_SmartdebitReconciliation_Utils::getContactAddress($cid);
      $this->assign('aContact', $contact);
      $this->assign('aAddress', $address);
    }
    // If 'Donation' option is chosen for membership, don't process
    if(!empty($mid) && $mid != 'donation') {
      $membership = CRM_SmartdebitReconciliation_Utils::getContactMemberships($cid, $mid);
      $this->assign('aMembership', $membership);
    }
    // If 'Create New Recurring' option is chosen for recurring, don't process
    if(!empty($cr_id) && $cr_id != 'new_recur') {
      $cRecur = CRM_SmartdebitReconciliation_Utils::getRecurringContributionRecord($cr_id);
      $this->assign('aContributionRecur', $cRecur);
    }

    $this->addButtons( array(
        array(
          'type'      => 'upload',
          'name'      => ts('Confirm'),
        ),
      )
    );

    CRM_Utils_System::setTitle('Confirm changes to Contact');
    parent::buildQuickForm();
  }

  public function postProcess() {
    $submitValues = $this->_submitValues;
    $cid = $submitValues['cid'];
    $mid = $submitValues['mid'];
    $reference_number = $submitValues['reference_number'];
    $cr_id = $submitValues['cr_id'];
    $params = sprintf('cid=%d&mid=%d&cr_id=%d&reference_number=%s', $cid, $mid, $cr_id, $reference_number);
    // FIXME: This path doesn't exist... need to redirect and create a recurring contribution
    $url = CRM_Utils_System::url('civicrm/smartdebit/reconciliation/fix-contact-rec',$params);
    CRM_Utils_System::redirect($url);
    parent::postProcess();
  }

  /**
   * This function sets the default values for the form.
   *
   * @access public
   *
   * @return None
   */
  public function setDefaultValues() {
    $defaults = array();
    $defaults['reference_number'] = CRM_Utils_Array::value('reference_number', $_GET);
    $defaults['cid']              = CRM_Utils_Array::value('cid', $_GET);
    $defaults['mid']              = CRM_Utils_Array::value('mid', $_GET);
    $defaults['cr_id']              = CRM_Utils_Array::value('cr_id', $_GET);
    return $defaults;
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }
}
