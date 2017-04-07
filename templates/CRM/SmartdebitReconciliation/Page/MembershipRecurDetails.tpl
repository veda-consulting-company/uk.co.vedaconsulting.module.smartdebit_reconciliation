<h3>{ts}View Contact Details, Membership and Contribution Recur{/ts}</h3>
<div class="crm-form-block">
  <table class="crm-info-panel">
    <tr>
      <td width="30%">
        Smart Debit Reference :
      </td>
      <td>
        
        {$reference_number}
      </td>
    </tr>
    <tr>
      {if !empty($aContact) }
      <td>
        Name :
      </td>
      <td>
        
        {$aContact.display_name}
      </td>
      {/if}
    </tr>
    <tr>
      {if !empty($aAddress) }
      <td>
        Address :
      </td>
      <td>
        {$aAddress.street_address} <br />
        {$aAddress.city} <br />
        {$aAddress.country_id} <br />
        {$aAddress.postal_code} <br />
      </td>
      {/if}
    </tr>
    {if !empty($aMembership)}
    <tr>
      <td>
        Membership :
      </td>
    </tr>
    <tr>
      <td>
        Type 
      </td>
      <td>
       {$aMembership.type} 
      </td>
    </tr>
    <tr>
      <td>
        Status 
      </td>
      <td>
        {$aMembership.status}
      </td>
     </tr>
     <tr>
      <td>
         Start Date  
      </td>
      <td>
       {$aMembership.start_date}
      </td>
      </tr>
      <tr>
      <td>
        End Date 
      </td>
      <td>
       {$aMembership.end_date}  
      </td>
     </tr>
   {/if}
   {if !empty($aContributionRecur)}
    <tr>
      <td>
        Contribution Recur :
      </td>
    </tr>
    <tr>
      <td>
        Contribution Status 
      </td>
      <td>
        {$aContributionRecur.status} 
      </td>
    </tr>
    <tr>
      <td>
        Amount
      </td>
      <td>
        {$aContributionRecur.amount} 
      </td>
      </tr>
    <tr>
      <td>
        Payment Processor
      </td>
      <td>
        {$aContributionRecur.payment_processor} <br />
      </td>
    </tr>
    {/if}
    
    
  </table>
      
</div>
<div>
    {assign var=aMembershipId value=$aMembership.id}
    {assign var=aContactId value=$aContact.id}
    {assign var=aContributionRecurId value=$aContributionRecur.id}
    {capture assign=crmURL}{crmURL p='civicrm/smartdebit/reconciliation/fix-contact-rec' q="cid=$aContact&mid=$aMembership&cr_id=$aContributionRecur&reference_number=$reference_number"}{/capture}
    <span class="crm-button crm-button-type-upload crm-button_qf_ContactDetails_upload">
  <input type="submit" name="submit" value="Submit" onclick="parent.location='{$crmURL}'"/>
    </span>
</div>