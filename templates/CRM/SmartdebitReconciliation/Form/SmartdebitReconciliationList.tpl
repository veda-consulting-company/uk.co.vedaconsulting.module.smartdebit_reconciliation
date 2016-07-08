{*
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
*}
{if $sync eq 0}
<table>
  <tr>      
    <td>
      <div style="height:150px; overflow:auto;">
	<p>
	  <a href="{crmURL p='civicrm/smartdebit/reconciliation/list' q='checkMissingFromCivi=1' h=0}">Show All Mandates Missing from CiviCRM</a><br />
	  <a href="{crmURL p='civicrm/smartdebit/reconciliation/list' q='checkMissingFromSD=1' h=0}">Show All Mandates Missing from Smart Debit</a><br />
	  <a href="{crmURL p='civicrm/smartdebit/reconciliation/list' q='checkAmount=1' h=0}">Show All Mandates with Differing Amounts</a><br />
	  <a href="{crmURL p='civicrm/smartdebit/reconciliation/list' q='checkFrequency=1' h=0}">Show All Mandates with Differing Frequencies</a><br />
	  <a href="{crmURL p='civicrm/smartdebit/reconciliation/list' q='checkStatus=1' h=0}">Show All Mandates with Differing Status</a><br />
	  <a href="{crmURL p='civicrm/smartdebit/reconciliation/list' q='checkPayerReference=1' h=0}">Show All Mandates with missing Contact ID from CiviCRM</a><br />
	</p>      
      </div>
    </td>
    {if $directDebitArray}
    <td>
      <div style="height:150px; overflow:auto;">
	<h3>{ts}Direct Debit System Setting{/ts}</h3>
	<div id="help" class="tasklist">{ts}Make sure settings are correct to avoid errors before click fix me, eg payment_instrument_id{/ts}</div>
	<span style="float:right;"><a href="{crmURL p="civicrm/directdebit/display"}" target="_new">Edit Setting</a></span>
	<table>
	  <tr style="background-color: #CDE8FE;">
	    <td><b>{ts}Id{/ts}</b></td>
	    <td><b>{ts}Name{/ts}</td>
	    <td><b>{ts}Value{/ts}</td>
	  </tr>
	  {foreach from=$directDebitArray item=row}
	    {assign var=id value=$row.id}
	    <tr>
	      <td>{$row.id}</td>
	      <td>{$row.name}</td>
	      <td>{$row.value}</td>
	    </tr>
	  {/foreach}  
	</table>
      </div>
    </td>
    {/if}
  </tr>
</table>
{/if}
<h3>{ts}Mis-Matched Contacts {*(Limited to 200 Records)*}{/ts}</h3>
<div style="min-height:400px;"> 
        
    <table  class="selector row-highlight">
        <tr style="background-color: #CDE8FE;">
           <td><b>{ts}Transaction ID{/ts}</td>
           <td><b>{ts}Contact{/ts}</td>
           <td><b>{ts}Differences{/ts}</td>
{*           <td><b>{ts}Payment Instrument{/ts}</td>*}
           <td><b>{ts}Frequency{/ts}</td>
           <td><b>{ts}Total{/ts}</td>
           <td><b>{ts}Status{/ts}</td>
           <td></td>
        </tr>

        {foreach from=$listArray item=row}
            {assign var=id value=$row.id} 
            <tr class="{cycle values="odd-row,even-row"}">
                <td>
		{if $row.contribution_recur_id }
                    <a href="/civicrm/contact/view/contributionrecur?reset=1&id={$row.contribution_recur_id}&cid={$row.contact_id}">{$row.transaction_id}</a>
                {else}
                    {$row.transaction_id}
                {/if}
                </td>                
                <td>
                {if $row.contact_id gt 0}
                    <a href="/civicrm/contact/view?cid={$row.contact_id}">{$row.contact_name}</a>
                {else}
                    {$row.contact_name}
                {/if}
								</td>
                <td>{$row.differences}</td>
{*                <td>{$row.payment_instrument}</td>*}
{*                <td>{$row.start_date|crmDate}/{$row.sd_start_date|crmDate}</td>*}
                {if $row.contribution_recur_id }
                    <td>{$row.frequency}/{$row.sd_frequency}</td>
                    <td>{$row.amount}/{$row.sd_amount}</td>
                    <td>{$row.contribution_status_id}/{$row.sd_contribution_status_id}</td>
                {else}
                    <td>{$row.sd_frequency}</td>
                    <td>{$row.sd_amount}</td>
                    <td>{$row.sd_contribution_status_id}</td>  
                {/if}
                <td>
                {if $row.fix_me_url}
                    <a href="{$row.fix_me_url}" target="_new">Fix Me</a>
                {/if}
                </td>
            </tr>
        {/foreach}  

     </table>
   
</div>

