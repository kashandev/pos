/**
 * Created by Huzaifa on 9/18/15.
 */

$(document).on('click','.btnAddGrid', function() {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>&nbsp;';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '<td>';
    $html += '<select class="form-control select2" id="opening_account_detail_partner_type_id_'+$grid_row+'" name="opening_account_details['+$grid_row+'][partner_type_id]" onChange="getPartners('+$grid_row+');">';
    $html += '<option value="">&nbsp;</option>';
    $.each($partner_types,function($i,$partner_type) {
        $html += '<option value="'+$partner_type['partner_type_id']+'">'+$partner_type['name']+'</option>';
    })
    $html += '</select>';
    $html += '</td>';
    $html += '<td>';
    $html += '<select class="form-control select2" id="opening_account_detail_partner_id_'+$grid_row+'" name="opening_account_details['+$grid_row+'][partner_id]" onChange="getPartnerAccount('+$grid_row+');">';
    $html += '<option value="">&nbsp;</option>';
    $html += '</select>';
    $html += '</td>';
    $html += '<td>';
    $html += '<select class="form-control select2" id="opening_account_detail_ref_document_type_id_'+$grid_row+'" name="opening_account_details['+$grid_row+'][ref_document_type_id]">';
    $html += '<option value="">&nbsp;</option>';
    $html += '<option value="1">'+$lang['purchase_invoice']+'</option>';
    $html += '<option value="39">'+$lang['sale_invoice']+'</option>';
    $html += '<option value="11">'+$lang['debit_invoice']+'</option>';
    $html += '<option value="24">'+$lang['credit_invoice']+'</option>';
    $html += '</select>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="opening_account_details['+$grid_row+'][ref_document_identity]" id="opening_account_detail_ref_document_identity_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control dtpDate" name="opening_account_details['+$grid_row+'][ref_document_date]" id="opening_account_detail_ref_document_date_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control fPDecimal" name="opening_account_details['+$grid_row+'][ref_document_amount]" id="opening_account_detail_ref_document_amount_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<select class="form-control select2" id="opening_account_detail_coa_level3_id_'+$grid_row+'" name="opening_account_details['+$grid_row+'][coa_level3_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $coas.forEach(function($coa) {
        $html += '<option value="'+$coa.coa_level3_id+'">'+$coa.level3_display_name+'</option>';
    })
    $html += '</select>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateTotal(this);" type="text" class="form-control fPDecimal" name="opening_account_details['+$grid_row+'][document_debit]" id="opening_account_detail_document_debit_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateTotal(this);" type="text" class="form-control fPDecimal" name="opening_account_details['+$grid_row+'][document_credit]" id="opening_account_detail_document_credit_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '</tr>';


    if($(this).parent().parent().data('row_id')=='H') {
        $('#tblOpeningAccountDetail tbody').prepend($html);
    } else {
        $(this).parent().parent().after($html);
    }
    setFieldFormat();
    $('#tblOpeningAccountDetail #grid_row_'+$grid_row+' select:first').select2('open');
    $grid_row++;
});


function getPartnerAccount($row_id)
{
    var $partner_id = $('#opening_account_detail_partner_id_' + $row_id).val();
    $.ajax({
        url: $UrlGetPartnerAccount,
        dataType: 'json',
        type: 'post',
        data: 'partner_id='+$partner_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
             $('#tblOpeningAccountDetail').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                var $html = '';
                $.each( $coas , function( key, $account ) {
                    if($account['coa_level3_id']==json.account) {
                        $html += '<option value="'+$account['coa_level3_id']+'" selected="selected">'+$account['level3_display_name']+'</option>';
                    }
                    else
                    {
                        $html += '<option value="'+$account['coa_level3_id']+'" >'+$account['level3_display_name']+'</option>';
                    }
                });
                $('#opening_account_detail_coa_level3_id_' + $row_id).html($html).trigger('change');
            }
            else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
}

function getPartners($row_id) {
    var $partner_type_id = $('#opening_account_detail_partner_type_id_' + $row_id).val();
    var $html = '<option value="">&nbsp;</option>';
    if($partner_type_id != '') {
        $.each( $partners, function( key, $partner ) {
            if($partner['partner_type_id']==$partner_type_id) {
                $html += '<option value="'+$partner['partner_id']+'">'+$partner['name']+'</option>';
            }
        });
    }
    $('#opening_account_detail_partner_id_' + $row_id).html($html).trigger('change');
}

function getCoaLevel3ByPartner($row_id) {
    alert('on ftn scope');
    var $partner_account_id = $('#opening_account_detail_partner_id_' + $row_id).val();
    alert($partner_account_id);
    var $html = '';
    if($partner_id != '') {
        $.each( $coas , function( key, $account ) {
            if($account['coa_level3_id']==$partner_account_id) {
                $html += '<option value="'+$account['coa_level3_id']+'" selected="selected">'+$account['level3_display_name']+'</option>';
            }
        });
    }
    $('#opening_account_detail_coa_level3_id_' + $row_id).html($html).trigger('change');
}

// $(document).on('change','#partner_type_id', function() {
//     $partner_type_id = $(this).val();
//     $.ajax({
//         url: $UrlGetPartner,
//         dataType: 'json',
//         type: 'post',
//         data: 'partner_type_id=' + $partner_type_id+'&partner_id='+$partner_id,
//         mimeType:"multipart/form-data",
//         beforeSend: function() {
//             //$('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-search').addClass('fa-refresh fa-spin');
//             $('#partner_id').select2('destroy');
//         },
//         complete: function() {
//             //$('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-refresh').removeClass('fa-spin').addClass('fa-search');
//             $('#partner_id').select2({width:'100%'});
//         },
//         success: function(json) {
//             if(json.success)
//             {
//                 $('#partner_id').html(json.html);
//             }
//             else {
//                 alert(json.error);
//             }
//         },
//         error: function(xhr, ajaxOptions, thrownError) {
//             console.log(xhr.responseText);
//         }
//     })
// });

function removeRow($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_'+$row_id).remove();
    calculateTotal();
}

function calculateTotal() {
    var $document_debit = 0;
    var $document_credit = 0;
    $('#tblOpeningAccountDetail tbody tr').each(function() {
        $row_id = $(this).data('row_id');
        var $debit_amount = $('#opening_account_detail_document_debit_' + $row_id).val() || 0;
        var $credit_amount = $('#opening_account_detail_document_credit_' + $row_id).val() || 0;

        $document_debit += parseFloat($debit_amount);
        $document_credit += parseFloat($credit_amount);
    })

    $('#document_debit').val(roundUpto($document_debit,4));
    //$('#document_credit').val(roundUpto($document_credit,4));
    $('#document_credit').val($document_credit);
}


function Save() {
    if($('#form').valid()){
        $('#form').submit();
        $('.btnsave').attr('disabled','disabled');
    }
    else{
        $('.btnsave').removeAttr('disabled');
        // console.log('here');
    }
}