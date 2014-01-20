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
<p>
<a href="/civicrm/smartdebit/reconciliation/list?checkMissingFromCivi=1">Show All Mandates Missing from CiviCRM</a><br />
<a href="/civicrm/smartdebit/reconciliation/list?checkMissingFromSD=1">Show All Mandates Missing from Smart Debit</a><br />
<a href="/civicrm/smartdebit/reconciliation/list?checkAmount=1">Show All Mandates with Differing Amounts</a><br />
<a href="/civicrm/smartdebit/reconciliation/list?checkFrequency=1">Show All Mandates with Differing Frequencies</a><br />
<a href="/civicrm/smartdebit/reconciliation/list?checkStatus=1">Show All Mandates with Differing Status</a><br />
<a href="/civicrm/smartdebit/reconciliation/list?checkPayerReference=1">Show All Mandates with missing Contact ID from CiviCRM</a><br />
</p>
<h3>{ts}Mis-Matched Contacts (Limited to 200 Records){/ts}</h3>
<div style="min-height:400px;"> 
        
    <table class="selector">
        <tr style="background-color: #CDE8FE;">
           <td><b>{ts}Transaction ID{/ts}</td>
           <td><b>{ts}Type{/ts}</b></td>
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
            <tr>
                <td>
									<a href="/civicrm/contact/view/contributionrecur?id={$row.contribution_recur_id}">{$row.transaction_id}</a>
								</td>
                <td>{$row.contribution_type}</td>
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
                <td>{$row.frequency}/{$row.sd_frequency}</td>
                <td>{$row.amount}/{$row.sd_amount}</td>
                <td>{$row.contribution_status_id}/{$row.sd_contribution_status_id}</td>
								<td>
                {if $row.recordFound}
                    <a href="/civicrm/contact/view/contributionrecur?id={$row.contribution_recur_id}&action=update">Edit</a>
                {/if}
								{if $row.fix_me_url}
                    <a href="{$row.fix_me_url}" target="_new">Fix Me</a>
                {/if}
								</td>
            </tr>
        {/foreach}  

     </table>
   
</div>

