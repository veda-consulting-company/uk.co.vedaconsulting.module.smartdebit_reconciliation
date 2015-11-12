{if $smartdebit}
<div id="smart_debit_dialog" class="smart_debit_dialog" style="display:none;">
</div>
{if $action eq 16 and $permission EQ 'edit'}
  <div style="display:none;">
    {capture assign=directContribURL}{crmURL p="civicrm/directdebit/newdonation" q="reset=1&action=add&cid=`$contactId`&context=contribution&mode=live"}{/capture}
    <a accesskey="N" href="{$directContribURL}" id="smart_debit" class="button"><span><div class="icon ui-icon-circle-plus"></div>{ts}Submit Donation Direct Debit Contribution{/ts}</span></a>
  </div>
{/if}
{literal}
    <script type="text/javascript">
      CRM.$(function($) {
      $('#smart_debit').insertAfter($('.action-link').find('.button'));
       var smartDetails = {/literal}{$contributionRecurDetails}{literal};
       $('.crm-entity').each(function(){
            var recurIdWithName = $(this).attr('id');
            var splited = recurIdWithName.split("-");
            var recurId = splited[1];
            if (smartDetails.hasOwnProperty(recurId)) {
                var targetHtml = '<table>';
                for (var k in smartDetails[recurId]) {
                    if (smartDetails[recurId].hasOwnProperty(k)) {
                        targetHtml = targetHtml.concat('<tr><td>'+k+'</td><td>' +smartDetails[recurId][k] + '</td></tr>');
                    }
                }
                targetHtml = targetHtml.concat('</table>');
                $(this).find('td:last').append('<span id= "smart_debit_details-' + recurId + '" class="action-item crm-hover-button">View Smart Debit</span>');
                $('#smart_debit_details-'+ recurId).click(function(){
                   $('#smart_debit_dialog').dialog({
                        title: "Smart Debit Details",
                        modal: true,
                        width : 500,
                        height: 400,
                        open: function(){
                            $('#smart_debit_dialog').html(targetHtml);
                        },
                        buttons: { "Done": function() { cj(this).dialog("destroy"); }}
                   });
                });
            }
          });
      });
    </script>
{/literal}
{/if}
