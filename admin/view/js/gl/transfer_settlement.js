/**
 * Created by Huzaifa on 9/18/15.
 */

//$(document).on('click','#btnAddGrid', function() {
//
//
//    $html = '';
//    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
//    $html += '<td>';
//    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
//    $html +='<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
//    $html += '<td>';
//    $html += '<select onchange="getBranchAccounts('+$grid_row+');" class="form-control select2" id="transfer_settlement_detail_company_branch_id_'+$grid_row+'" name="transfer_settlement_details['+$grid_row+'][company_branch_id]">';
//    $html += '<option value="">&nbsp;</option>';
//    $.each($company_branchs,function($i,$company_branch) {
//        $html += '<option value="'+$company_branch['company_branch_id']+'">'+$company_branch['name']+'</option>';
//    })
//    $html += '</select>';
//    $html += '</td>'
//    $html += '<td>';
//    $html += '<select class="form-control select2" id="transfer_settlement_detail_coa_id_'+$grid_row+'" name="transfer_settlement_details['+$grid_row+'][coa_id]" >';
//    $html += '<option value="">&nbsp;</option>';
//    $coas.forEach(function($coa) {
//        $html += '<option value="'+$coa.coa_level3_id+'">'+$coa.level3_display_name+'</option>';
//    })
//    $html += '</select>';
//    $html += '</td>'
//    $html += '<td>';
//    $html += '<input type="text" class="form-control" name="transfer_settlement_details['+$grid_row+'][remarks]" id="transfer_settlement_detail_remarks_'+$grid_row+'" value="" />';
//    $html += '</td>'
//    $html += '<td>';
//    $html += '<input type="text" class="form-control dtpDate" name="transfer_settlement_details['+$grid_row+'][cheque_date]" id="transfer_settlement_detail_cheque_date_'+$grid_row+'" value="" />';
//    $html += '</td>'
//    $html += '<td>';
//    $html += '<input type="text" class="form-control" name="transfer_settlement_details['+$grid_row+'][cheque_no]" id="transfer_settlement_detail_cheque_no_'+$grid_row+'" value="" />';
//    $html += '</td>'
//    $html += '<td>';
//    $html += '<input onchange="calculateTotal();" type="text" class="form-control fPDecimal" name="transfer_settlement_details['+$grid_row+'][document_debit]" id="transfer_settlement_detail_document_debit_'+$grid_row+'" value="" />';
//    $html += '</td>'
//    $html += '<td>';
//    $html += '<input onchange="calculateTotal();" type="text" class="form-control fPDecimal" name="transfer_settlement_details['+$grid_row+'][document_credit]" id="transfer_settlement_detail_document_credit_'+$grid_row+'" value="" />';
//    $html += '</td>'
//    $html += '<td>';
//    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
//    $html +='<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
//    $html += '</tr>';
//
//
//    $('#tblTransferSettlementDetail tbody').append($html);
//    setFieldFormat();
//    //$('#transfer_settlement_detail_ref_document_type_id_'+$grid_row).select2({width: '100%'});
//    //$('#transfer_settlement_detail_coa_id_'+$grid_row).select2({width: '100%'});
//    $grid_row++;
//});
$(document).on('change','#to_branch_id', function(){
    $to_branch_id = $('#to_branch_id').val();
    console.log($to_branch_id);
    $.ajax({
        url: $UrlGetBranchAccount,
        dataType: 'json',
        type: 'post',
        data: 'to_branch_id=' + $to_branch_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#outstanding_amount').val(json.outstanding_amount);
             }
            else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })

});


function removeRow($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_'+$row_id).remove();
    calculateTotal();
}

function calculateTotal() {
    var $document_debit = 0;
    var $document_credit = 0;
    $('#tblTransferSettlementDetail tbody tr').each(function() {
        $row_id = $(this).data('row_id');
        var $debit_amount = $('#transfer_settlement_detail_document_debit_' + $row_id).val() || 0;
        var $credit_amount = $('#transfer_settlement_detail_document_credit_' + $row_id).val() || 0;

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