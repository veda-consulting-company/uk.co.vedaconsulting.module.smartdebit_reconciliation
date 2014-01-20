<h3>{ts}View Contact Details, Membership and Contribution Recur{/ts}</h3>
<div>
  <table>
    <tr>
      <td>
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
  <input type="submit" name="submit" value="Submit" onclick="parent.location='/civicrm/smartdebit/reconciliation/fix-contact-rec?cid={$aContact.id}&mid={$aMembership.id}&cr_id={$aContributionRecur.id}&reference_number={$reference_number}'"/>
</div>