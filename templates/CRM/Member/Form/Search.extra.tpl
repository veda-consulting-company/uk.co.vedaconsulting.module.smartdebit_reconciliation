{literal}
<script type="text/javascript">
cj(function() {
  var jq_task_dropdown_id           = 'select#task';
  var jq_change_dd_amount_option_id = '888DD-AMOUNT';
  var fn_add_amend_dd_amount_option = function() {
    cj( jq_task_dropdown_id ).append("<option id='" + jq_change_dd_amount_option_id +"' value='888'>Change DD Amount</option>");
  };

  var fn_find_selected_contact_id = function( selected_row_id ) {
    var all_anchors = cj( '#' + selected_row_id ).parent().siblings().find('a');
    var contact_id = null;
    all_anchors.each( function() {
      if ( cj(this).attr('title') === 'View Membership' ) {
        var url = cj(this).attr('href');
        contact_id = url.match(/cid=(.*?)&/i)[1];
        return false;
      }
    });

    return contact_id;
  };

  var fn_get_recur_contribution_details = function ( cid ) {
    var dataUrl              = "{/literal}{crmURL p='civicrm/ajax/rest'}{literal}";
    var contribution_details = false;
    cj.ajax({
      type: "POST",
      url: dataUrl,
      async: false,
      data: { json: 1
            , sequential: 1
            , entity: "ContributionRecur"
            , action: "exists"
            , contact_id: cid
      },
      dataType: "json",
      success: function( return_data ){
        if( return_data.is_error == 0 && return_data.count !== 0 ){
          contribution_details = return_data.values;
        }
      }
    });

    return contribution_details;
  };

  var fn_amend_dd_amount = function ( contact_id, contribution_recur_id, amount ) {
    cj().crmAPI( 'ContributionRecur'
               , 'amendddamount'
               , { 'q':'civicrm/ajax/rest'
                 , 'sequential':'1'
                 , 'contact_id':contact_id
                 , 'contribution_recur_id':contribution_recur_id
                 , 'amount':amount
                 }
               , { success:function (data){
                     if ( data.is_error !== 1 ) {
                       alert( 'Amount Changed succefully.' );
                     } else {
                       alert( 'Error changing amount - ' + data.error_message );
                     };
                   }
                 }
               );
  };

  var fn_ask_for_new_amount = function( e ) {
    var iSelectedVal  = cj( jq_task_dropdown_id + ' option:selected' ).val();
    if ( iSelectedVal == 888 ) {
      var iCheckedRow   = cj('div#memberSearch .form-checkbox:checked:visible');
      if ( iCheckedRow.length == 0 ) {
        alert( 'You can need to select a Member first.');
      } else if ( iCheckedRow.length != 1 ) {
        alert( 'You can only change one Member at a time.');
      } else {
        var selected_contact_id = fn_find_selected_contact_id( iCheckedRow.attr('id') );
        var oContributionDet    = fn_get_recur_contribution_details( selected_contact_id );
        if ( oContributionDet == false ) {
          alert( 'This Member does not have a Recurring Contribution.');
        } else {
          var iOldDdAmount = oContributionDet.amount;
          var iNewDdAmount = prompt("Enter new DD Amount");
          if ( iNewDdAmount ) {
            if ( isNaN( iNewDdAmount ) ) {
              alert( 'Amount must be a number.');
            } else {
              if ( confirm("Please confirm that you want to change the Conribution amount from " + iOldDdAmount + " to " + iNewDdAmount + " for this user." ) ) {
                fn_amend_dd_amount( selected_contact_id
                                  , oContributionDet.id
                                  , iNewDdAmount );
              }
            }
          }
        }
      }
      cj( jq_task_dropdown_id ).val('');
      return false;
    }
  };

  fn_add_amend_dd_amount_option();
    cj( jq_task_dropdown_id ).change( function( e ) {
      fn_ask_for_new_amount( e );
  });
});

</script>
{/literal}
