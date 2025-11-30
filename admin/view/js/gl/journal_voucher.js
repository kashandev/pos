/**
 * Created by Huzaifa on 9/18/15.
 */

 function getSubProjects($row_id, $sub_project_id=false)
 {
    $project_id = $('#journal_voucher_detail_project_id_' + $row_id).val();
    $.ajax({
        url: $UrlGetSubProjects,
        dataType: 'json',
        type: 'post',
        data: 'project_id=' + $project_id+'&sub_project_id='+$sub_project_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#journal_voucher_detail_sub_project_id_' + $row_id).parent('td').css({
                'position' : 'relative'
            });
            $style = "position: absolute;z-index: 99999;top: 15px;left: 12px;";
            $('#journal_voucher_detail_sub_project_id_' + $row_id).before('<i style="'+ $style +'" id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#journal_voucher_detail_sub_project_id_' + $row_id).select2('destroy');
                $('#journal_voucher_detail_sub_project_id_' + $row_id).select2({'width':'100%'});
                $('#journal_voucher_detail_sub_project_id_' + $row_id).html(json.html).trigger('change');
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

// $(document).on('change', '#project_id', function(){

//     $project_id = $(this).val();
//     $.ajax({
//         url: $UrlGetSubProjects,
//         dataType: 'json',
//         type: 'post',
//         data: 'project_id=' + $project_id+'&sub_project_id='+$sub_project_id,
//         mimeType:"multipart/form-data",
//         beforeSend: function() {
//             $('#sub_project_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
//         },
//         complete: function() {
//             $('#loader').remove();
//         },
//         success: function(json) {
//             if(json.success)
//             {
//                 $('#sub_project_id').select2('destroy');
//                 $('#sub_project_id').select2({'width':'100%'});
//                 $('#sub_project_id').html(json.html).trigger('change');
//             }
//             else {
//                 alert(json.error);
//             }
//         },
//         error: function(xhr, ajaxOptions, thrownError) {
//             console.log(xhr.responseText);
//         }
//     })

//  });

$(document).on('click','#btnAddGrid, .btnAddGrid', function() {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '<td>';
    $html += '<select onchange="getPartners('+$grid_row+');" class="form-control select2" id="journal_voucher_detail_partner_type_id_'+$grid_row+'" name="journal_voucher_details['+$grid_row+'][partner_type_id]">';
    $html += '<option value="">&nbsp;</option>';
    $.each($partner_types,function($i,$partner_type) {
        $html += '<option value="'+$partner_type['partner_type_id']+'">'+$partner_type['name']+'</option>';
    });
    $html += '</select>';
    $html += '</td>';
    $html += '<td style="width: 200px;">';
    $html += '<select onchange="getDocuments('+$grid_row+');" class="form-control select2" id="journal_voucher_detail_partner_id_'+$grid_row+'" name="journal_voucher_details['+$grid_row+'][partner_id]">';
    $html += '<option value="">&nbsp;</option>';
    $html += '</select>';
    $html += '</td>';
    $html += '<td style="width: 200px;">';
    $html += '<div class="input-group">';
    $html += '<input type="hidden" class="form-control" name="journal_voucher_details['+$grid_row+'][ref_document_type_id]" id="journal_voucher_detail_ref_document_type_id_'+$grid_row+'" value="" />';
    $html += '<select onchange="setDocumentType('+$grid_row+');" class="form-control select2" id="journal_voucher_detail_ref_document_identity_'+$grid_row+'" name="journal_voucher_details['+$grid_row+'][ref_document_identity]">';
    $html += '<option data-document_type_id="0" value="">&nbsp;</option>';
    $html += '</select>';
    $html += '<span class="input-group-addon hide"><i class="fa fa-spinner fa-spin"></i></span>';
    $html += '</div>';
    $html += '</td>';
    $html += '<td>';
    $html += '<select class="form-control select2" id="journal_voucher_detail_coa_id_'+$grid_row+'" name="journal_voucher_details['+$grid_row+'][coa_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $coas.forEach(function($coa) {
        $html += '<option value="'+$coa.coa_level3_id+'">'+$coa.level3_display_name+'</option>';
    });
    $html += '</select>';
    $html += '</td>';

    $html += '<td hidden>';
    $html += '<select onchange="getSubProjects('+$grid_row+')" class="form-control" id="journal_voucher_detail_project_id_'+ $grid_row +'" name="journal_voucher_details['+ $grid_row +'][project_id]">';
    $html += '<option value="">&nbsp;</option>';
    $.each($projects, function($i, $project){
        $html += '<option value="'+$project.project_id+'">'+$project.name+'</option>';
    });
    $html += '</select>';
    $html += '</td>';

    $html += '<td hidden>';
    $html += '<select class="form-control" id="journal_voucher_detail_sub_project_id_'+ $grid_row +'" name="journal_voucher_details['+ $grid_row +'][sub_project_id]">';
    $html += '</select>';
    $html += '</td>';

    $html += '<td hidden>';
    $html += '<select class="form-control" id="journal_voucher_detail_job_cart_id_'+ $grid_row +'" name="journal_voucher_details['+ $grid_row +'][job_cart_id]">';
    $html += '<option value="">&nbsp;</option>';
    $.each($job_carts, function($i, $job_cart){
        $html += '<option value="'+$job_cart.job_cart_id+'">'+$job_cart.name+'</option>';
    });
    $html += '</select>';
    $html += '</td>';



    $html += '<td>';
    $html += '<input type="text" class="form-control" name="journal_voucher_details['+$grid_row+'][remarks]" id="journal_voucher_detail_remarks_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control dtpDate" name="journal_voucher_details['+$grid_row+'][cheque_date]" id="journal_voucher_detail_cheque_date_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="journal_voucher_details['+$grid_row+'][cheque_no]" id="journal_voucher_detail_cheque_no_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateTotal();" type="text" class="form-control fPDecimal" name="journal_voucher_details['+$grid_row+'][document_debit]" id="journal_voucher_detail_document_debit_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateTotal();" type="text" class="form-control fPDecimal" name="journal_voucher_details['+$grid_row+'][document_credit]" id="journal_voucher_detail_document_credit_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '</tr>';


    $('#tblJournalVoucherDetail tbody').append($html);
    setFieldFormat();
    //$('#journal_voucher_detail_ref_document_type_id_'+$grid_row).select2({width: '100%'});
    //$('#journal_voucher_detail_coa_id_'+$grid_row).select2({width: '100%'});
    $grid_row++;
});

function getPartners($row_id) {
    var $selected_partners = [];
    $partner_type_id = $('#journal_voucher_detail_partner_type_id_' + $row_id).val();
    $options = '<option value="">&nbsp;</option>';
    if($partner_type_id != '') {
        $selected_partners = $partners[$partner_type_id];
        $.each($selected_partners, function($key, $partner) {
            $options += '<option value="'+$partner['partner_id']+'">'+$partner['name']+'</option>';
        });
    }

    $('#journal_voucher_detail_partner_id_' + $row_id).select2('destroy');
    $('#journal_voucher_detail_partner_id_' + $row_id).html($options);
    $('#journal_voucher_detail_partner_id_' + $row_id).select2({width:'100%'});
}

function getDocuments($row_id) {
    $partner_type_id = $('#journal_voucher_detail_partner_type_id_' + $row_id).val();
    $partner_id = $('#journal_voucher_detail_partner_id_' + $row_id).val();

    $.ajax({
        url: $UrlGetPendingDocument,
        dataType: 'json',
        type: 'post',
        data: 'partner_type_id=' + $partner_type_id+'&partner_id='+$partner_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            //$('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-search').addClass('fa-refresh fa-spin');
            $('#journal_voucher_detail_ref_document_identity_' + $row_id).siblings('.input-group-addon').removeClass('hide');
        },
        complete: function() {
            //$('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-refresh').removeClass('fa-spin').addClass('fa-search');
            $('#journal_voucher_detail_ref_document_identity_' + $row_id).siblings('.input-group-addon').addClass('hide');
        },
        success: function(json) {
            if(json.success)
            {
                $('#journal_voucher_detail_ref_document_identity_' + $row_id).select2('destroy');
                $('#journal_voucher_detail_ref_document_identity_' + $row_id).html(json.html);
                $('#journal_voucher_detail_ref_document_identity_' + $row_id).select2({width:'100%'});

                $('#journal_voucher_detail_coa_id_' + $row_id).val(json.outstanding_account_id).trigger('change');
            }
            else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    });

    //  $.ajax({
    //     url: $UrlGetPartnerAccount,
    //     dataType: 'json',
    //     type: 'post',
    //     data: 'partner_id='+$partner_id,
    //     mimeType:"multipart/form-data",
    //     beforeSend: function() {
    //          $('#tblJournalVoucherDetail').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
    //     },
    //     complete: function() {
    //         $('#loader').remove();
    //     },
    //     success: function(json) {
    //         if(json.success)
    //         {
    //             var $html = '<option value="">&nbsp;</option>';
    //             $.each( $coas , function( key, $account ) {
    //                 if($account['coa_level3_id']==json.account) {
    //                     $html += '<option value="'+$account['coa_level3_id']+'" selected="selected">'+$account['level3_display_name']+'</option>';
    //                 }
    //                 else
    //                 {
    //                    $html += '<option value="'+$account['coa_level3_id']+'" >'+$account['level3_display_name']+'</option>';
    //                 }
    //             });
    //             $('#journal_voucher_detail_coa_id_' + $row_id).html($html).trigger('change');
    //         }
    //         else {
    //             alert(json.error);
    //         }
    //     },
    //     error: function(xhr, ajaxOptions, thrownError) {
    //         console.log(xhr.responseText);
    //     }
    // })
    
}

function setDocumentType($row_id) {
    var $obj = $('#journal_voucher_detail_ref_document_identity_' + $row_id + ' option:selected').data();
    $('#journal_voucher_detail_ref_document_type_id_' + $row_id).val($obj.document_type_id);
    $('#journal_voucher_detail_coa_id_' + $row_id).val($obj.coa_id).trigger('change');
    if($obj.adjust_on == 'CR') {
        $('#journal_voucher_detail_document_debit_' + $row_id).val(0.00);
        $('#journal_voucher_detail_document_credit_' + $row_id).val(parseFloat($obj.balance_amount).toFixed(2));
    } else if ($obj.adjust_on == 'DR'){
        $('#journal_voucher_detail_document_debit_' + $row_id).val(parseFloat($obj.balance_amount).toFixed(2));
        $('#journal_voucher_detail_document_credit_' + $row_id).val(0.00);
    } else {

    }

    calculateTotal();
}

function removeRow($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_'+$row_id).remove();
    calculateTotal();
}

function calculateTotal() {
    var $document_debit = 0;
    var $document_credit = 0;
    $('#tblJournalVoucherDetail tbody tr').each(function() {
        $row_id = $(this).data('row_id');
        var $debit_amount = $('#journal_voucher_detail_document_debit_' + $row_id).val() || 0;
        var $credit_amount = $('#journal_voucher_detail_document_credit_' + $row_id).val() || 0;

        $document_debit += parseFloat($debit_amount);
        $document_credit += parseFloat($credit_amount);
    })

    $('#document_debit').val($document_debit);
    $('#document_credit').val($document_credit);

    calculateBaseAmount();
}
$(document).on('change','#conversion_rate', function() {
    calculateBaseAmount();
})

function calculateBaseAmount() {
    var $document_debit = parseFloat($('#document_debit').val()) || 0.00;
    var $document_credit = parseFloat($('#document_credit').val()) || 0.00;
    var $conversion_rate = parseFloat($('#conversion_rate').val()) || 0.00;

    var $base_debit = $document_debit * $conversion_rate;
    var $base_credit = $document_credit * $conversion_rate;
    $('#base_debit').val($base_debit);
    $('#base_credit').val($base_credit);
}

function Save() {

    $('.btnsave').attr('disabled','disabled');
    if($('#form').valid() == true){
        $('#form').submit();
    }
    else{
        $('.btnsave').removeAttr('disabled');
    }
}