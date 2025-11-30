
function GetDocumentDetails() {
    var $data = {
    coa_level3_id : $('#coa_level3_id').val(),
    date_from : $('#date_from').val(),
    date_to : $('#date_to').val()
};

    var $credit = 0;
    var $debit = 0;

    $.ajax({
        url: $UrlGetDocumentDetails,
        dataType: 'json',
        type: 'post',
        data: $data,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            //    $('#ref_document_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            //    $('#loader').remove();
        },

        success: function(json) {
            if(json.success)
            {
                $('#tblBankReconciliationDetail tbody tr').remove();
                $check = 0;
                $.each(json.data, function($i,$product) {
                    fillGrid($product,json.clearing_date);
                });

            }
            else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })

};

function fillGrid($obj, $clearing_date) {
    var $balance = $obj['debit'] - $obj['credit'];
     $check += $balance;

    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td>';
    $html += '<input type="checkbox" onclick="checkFun('+$grid_row+');" id="'+$grid_row+'" name="bank_reconciliation_details['+$grid_row+'][clearance]"    style="width: 25px; height: 25px;margin-left: 25px;" value="1" /></td>';
    $html += '<td>';
    $html += '<input type="text"  autocomplete="off" class="form-control dtpDate" name="bank_reconciliation_details['+$grid_row+'][clearing_date]" id="bank_reconciliation_detail_clearing_date_'+$grid_row+'" value="'+$clearing_date+'"/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control dtpDate" name="bank_reconciliation_details['+$grid_row+'][document_date]" id="bank_reconciliation_detail_document_date_'+$grid_row+'" value="'+$obj['document_date']+'" readonly="true"/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input style="width: 200px;" type="text" class="form-control" name="bank_reconciliation_details['+$grid_row+'][document_identity]" id="bank_reconciliation_detail_document_identity_'+$grid_row+'" value="'+$obj['document_identity']+'" readonly="true"/>';
    $html += '<input type="hidden" class="form-control" name="bank_reconciliation_details['+$grid_row+'][ref_document_identity]" id="bank_reconciliation_detail_ref_document_identity_'+$grid_row+'" value="'+$obj['ref_document_identity']+'" />';
    $html += '<input type="hidden" class="form-control" name="bank_reconciliation_details['+$grid_row+'][ref_document_type_id]" id="bank_reconciliation_detail_ref_document_type_id_'+$grid_row+'" value="'+$obj['ref_document_type_id']+'" />';
    $html += '<input type="hidden" class="form-control" name="bank_reconciliation_details['+$grid_row+'][document_type_id]" id="bank_reconciliation_detail_document_type_id_'+$grid_row+'" value="'+$obj['document_type_id']+'" />';
    $html += '<input type="hidden" class="form-control" name="bank_reconciliation_details['+$grid_row+'][document_id]" id="bank_reconciliation_detail_document_id_'+$grid_row+'" value="'+$obj['document_id']+'" />';
    $html += '<input type="hidden" class="form-control" name="bank_reconciliation_details['+$grid_row+'][conversion_rate]" id="bank_reconciliation_detail_conversion_rate_'+$grid_row+'" value="'+$obj['conversion_rate']+'" />';
    $html += '<input type="hidden" class="form-control" name="bank_reconciliation_details['+$grid_row+'][base_currency_id]" id="bank_reconciliation_detail_base_currency_id_'+$grid_row+'" value="'+$obj['base_currency_id']+'" />';
    $html += '<input type="hidden" class="form-control" name="bank_reconciliation_details['+$grid_row+'][product_id]" id="bank_reconciliation_detail_product_id_'+$grid_row+'" value="'+$obj['product_id']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="bank_reconciliation_details['+$grid_row+'][cheque_no]" id="bank_reconciliation_detail_cheque_no_'+$grid_row+'" value="'+$obj['cheque_no']+'" readonly="true"/>';
    $html += '<input type="hidden" class="form-control" name="bank_reconciliation_details['+$grid_row+'][qty]" id="bank_reconciliation_detail_qty_'+$grid_row+'" value="'+$obj['qty']+'" />';
    $html += '<input type="hidden" class="form-control" name="bank_reconciliation_details['+$grid_row+'][amount]" id="bank_reconciliation_detail_amount_'+$grid_row+'" value="'+$obj['amount']+'" />';
    $html += '<input type="hidden" class="form-control" name="bank_reconciliation_details['+$grid_row+'][document_amount]" id="bank_reconciliation_detail_document_amount_'+$grid_row+'" value="'+$obj['document_amount']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control dtpDate" name="bank_reconciliation_details['+$grid_row+'][cheque_date]" id="bank_reconciliation_detail_cheque_date_'+$grid_row+'" value="'+$obj['cheque_date']+'" readonly="true"/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="bank_reconciliation_details['+$grid_row+'][debit]" id="bank_reconciliation_detail_debit_'+$grid_row+'" value="'+$obj['debit']+'" readonly="true"/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="bank_reconciliation_details['+$grid_row+'][credit]" id="bank_reconciliation_detail_credit_'+$grid_row+'" value="'+$obj['credit']+'" readonly="true"/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="bank_reconciliation_details['+$grid_row+'][balance]" id="bank_reconciliation_detail_balance_'+$grid_row+'" value="'+$check+'" readonly="true"/>';
    $html += '</td>';


    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '</tr>';


    $('#tblBankReconciliationDetail tbody').append($html);

    $grid_row++;
    setFieldFormat();
}


function removeRow($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_'+$row_id).remove();
}

function checkFun($row_id) {
        var $total_debit = 0;
        var $total_credit = 0;
        var $total_balance = 0;

        $('#tblBankReconciliationDetail tbody tr').each(function() {
            $row_id = $(this).data('row_id');

            var $debit = $('#bank_reconciliation_detail_debit_' + $row_id).val() || 0;
            var $credit = $('#bank_reconciliation_detail_credit_' + $row_id).val() || 0;
            if($('#'+$row_id).is(':checked')) {
                $total_debit += parseFloat($debit);
                $total_credit += parseFloat($credit);
        }
        })
        $total_balance = $total_debit - $total_credit;

        $('#total_debit').val(roundUpto($total_debit,4));
        $('#total_credit').val(roundUpto($total_credit,4));
        $('#total_balance').val(roundUpto($total_balance,4));

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