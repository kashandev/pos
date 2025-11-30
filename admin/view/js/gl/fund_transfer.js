/**
 * Created by Huzaifa on 9/18/15.
 */


 $(document).on('change','#partner_type_id', function() {
    $partner_type_id = $(this).val();
    $.ajax({
        url: $UrlGetPartner,
        dataType: 'json',
        type: 'post',
        data: 'partner_type_id=' + $partner_type_id+'&partner_id='+$partner_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#partner_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#partner_id').select2('destroy');
                $('#partner_id').html(json.html);
                if( $partner_id )
                {
                    $('#partner_id').trigger('change');
                }
                $('#partner_id').select2({width:'100%'});
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

$(document).on('change','#partner_id', function() {
    var $partner_type_id = $('#partner_type_id').val();
    if($partner_id != $(this).val()) {
        $('#tblFundTransferDetail').children('tbody').empty();
    }
    $partner_id = $(this).val();
    $.ajax({
        url: $UrlGetDocuments,
        dataType: 'json',
        type: 'post',
        data: 'partner_type_id=' + $partner_type_id+'&partner_id='+$partner_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#ref_document_identity').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#ref_document_identity').select2('destroy');
                $('#ref_document_identity').html(json.html);
                $('#ref_document_identity').select2({width:'100%'});
                //$partners = json.partners;
                $documents = json.documents;
                $partner_coas = json.partner_coas;
            }
            else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
    calculateTotal();
});

$(document).on('click','#addRefDocument', function() {
    var $partner_id = $('#partner_id').val();
    var $document_identity = $('#ref_document_identity').val();
    if($document_identity != '') {
        //var $document = $partners[$partner_id]['documents'][$document_identity];
        var $document = $documents[$document_identity];
        var $accounts = $partner_coas;
        var $tax_wht = $('#partner_id option:selected').data('wht_tax');
        var $tax_other = $('#partner_id option:selected').data('other_tax');
        $html = '';
        $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
        $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a><a  class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>';
        $html += '<td hidden>';
        $html += '<input type="hidden" name="fund_transfer_details['+$grid_row+'][ref_document_type_id]" id="fund_transfer_detail_ref_document_type_id_'+$grid_row+'" value="'+$document['ref_document_type_id']+'" />';
        $html += '<input type="hidden" name="fund_transfer_details['+$grid_row+'][ref_document_identity]" id="fund_transfer_detail_ref_document_identity_'+$grid_row+'" value="'+$document['ref_document_identity']+'" />';
        $html += '<a target="_blank" href="'+$document['href']+'">'+$document_identity+'</a>';
        $html += '</td>';
        $html += '<td style="width: 200px;">';
        $html += '<select class="form-control select2" id="fund_transfer_detail_coa_id_'+$grid_row+'" name="fund_transfer_details['+$grid_row+'][coa_id]">';
        $.each($accounts, function (index, $account) {
            $html += '<option value="'+$account['coa_level3_id']+'">'+$account['level3_display_name']+'</option>';
        })
        $html += '</select>';
        $html += '</td>'
        $html += '<td>';
        $html += '<input type="text" class="form-control" name="fund_transfer_details['+$grid_row+'][remarks]" id="fund_transfer_detail_remarks_'+$grid_row+'" value="" />';
        $html += '</td>'
        $html += '<td hidden>';
        $html += '<input type="text" class="form-control " name="fund_transfer_details['+$grid_row+'][document_amount]" id="fund_transfer_detail_document_amount_'+$grid_row+'" value="'+$document['document_amount']+'" readonly="true"/>';
        $html += '</td>'
        $html += '<td hidden>';
        $html += '<input type="text" class="form-control " id="fund_transfer_detail_balance_amount_'+$grid_row+'" value="'+$document['outstanding_amount']+'" readonly="true"/>';
        $html += '</td>'
        $html += '<td>';
        $html += '<input onchange="calculateTaxes(this);" type="text" class="form-control fDecimal" name="fund_transfer_details['+$grid_row+'][amount]" id="fund_transfer_detail_amount_'+$grid_row+'" value="0" />';
        $html += '</td>'
        $html += '<td hidden>';
        $html += '<input onchange="calculateWHTAmount(this);" type="text" class="form-control fPDecimal" name="fund_transfer_details['+$grid_row+'][wht_percent]" id="fund_transfer_detail_wht_percent_'+$grid_row+'" value="0"/>';
        $html += '</td>'
        $html += '<td hidden>';
        $html += '<input onchange="calculateWHTPercent(this);" type="text" class="form-control fPDecimal" name="fund_transfer_details['+$grid_row+'][wht_amount]" id="fund_transfer_detail_wht_amount_'+$grid_row+'" value="0.00"/>';
        $html += '</td>'
        $html += '<td hidden>';
        $html += '<input onchange="calculateOtherTaxAmount(this);" type="text" class="form-control fPDecimal" name="fund_transfer_details['+$grid_row+'][other_tax_percent]" id="fund_transfer_detail_other_tax_percent_'+$grid_row+'" value="0"/>';
        $html += '</td>'
        $html += '<td hidden>';
        $html += '<input onchange="calculateOtherTaxPercent(this);" type="text" class="form-control fPDecimal" name="fund_transfer_details['+$grid_row+'][other_tax_amount]" id="fund_transfer_detail_other_tax_amount_'+$grid_row+'" value="0.00"/>';
        $html += '</td>'
        $html += '<td hidden>';
        $html += '<input type="text" class="form-control" name="fund_transfer_details['+$grid_row+'][net_amount]" id="fund_transfer_detail_net_amount_'+$grid_row+'" value="'+$document['outstanding_amount']+'" readonly="true"/>';
        $html += '</td>'
        $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a><a  class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>';
        $html += '</tr>';


        $('#tblFundTransferDetail tbody').append($html);
        //$('#fund_transfer_detail_ref_document_identity_'+$grid_row).select2({width: '100%'});
        //$('#fund_transfer_detail_coa_id_'+$grid_row).select2({width: '100%'});
        $('#fund_transfer_detail_amount_'+$grid_row).trigger('change');
        setFieldFormat();
        $grid_row++;
    }

});

$(document).on('click','#btnAddGrid', function() {
    var $partner_type_id = $('#partner_type_id').val();
    var $partner_id = $('#partner_id').val();
    var $accounts = [];
    if($partner_type_id != '' && $partner_id != '') {
        $accounts = $partner_coas;
    } else {
        $accounts = $coas;
    }

    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    // $html += '&nbsp;';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '<td style="min-width: 200px;" hidden>';
    $html += '<input type="hidden" class="form-control" name="fund_transfer_details['+$grid_row+'][ref_document_type_id]" id="fund_transfer_detail_ref_document_type_id_'+$grid_row+'" value="" />';
    $html += '<input type="hidden" class="form-control" name="fund_transfer_details['+$grid_row+'][ref_document_identity]" id="fund_transfer_detail_ref_document_identity_'+$grid_row+'" value="" />';
    $html += '</td>'
    $html += '<td style="width: 200px;">';
    $html += '<select class="form-control select2" id="fund_transfer_detail_coa_id_'+$grid_row+'" name="fund_transfer_details['+$grid_row+'][coa_id]">';
    $html += '<option value="">&nbsp;</option>';
    $.each($accounts, function (index, $account) {
        $html += '<option value="'+$account['coa_level3_id']+'">'+$account['level3_display_name']+'</option>';
    })
    $html += '</select>';
    $html += '</td>'
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="fund_transfer_details['+$grid_row+'][remarks]" id="fund_transfer_detail_remarks_'+$grid_row+'" value="" />';
    $html += '</td>'
    $html += '<td hidden>';
    $html += '<input type="text" class="form-control" name="fund_transfer_details['+$grid_row+'][document_amount]" id="fund_transfer_detail_document_amount_'+$grid_row+'" value="0.00" readonly="true"/>';
    $html += '</td>';
    $html += '<td hidden>';
    $html += '<input type="text" class="form-control" id="fund_transfer_detail_balance_amount_'+$grid_row+'" value="0.00" readonly="true"/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateTaxes(this);" type="text" class="form-control" name="fund_transfer_details['+$grid_row+'][amount]" id="fund_transfer_detail_amount_'+$grid_row+'" value="0.00" />';
    $html += '</td>'
    $html += '<td hidden>';
    $html += '<input onchange="calculateWHTAmount(this);" type="text" class="form-control fPDecimal" name="fund_transfer_details['+$grid_row+'][wht_percent]" id="fund_transfer_detail_wht_percent_'+$grid_row+'" value="0"/>';
    $html += '</td>'
    $html += '<td hidden>';
    $html += '<input onchange="calculateWHTPercent(this);" type="text" class="form-control fPDecimal" name="fund_transfer_details['+$grid_row+'][wht_amount]" id="fund_transfer_detail_wht_amount_'+$grid_row+'" value="0.00"/>';
    $html += '</td>'
    $html += '<td hidden>';
    $html += '<input onchange="calculateOtherTaxAmount(this);" type="text" class="form-control fPDecimal" name="fund_transfer_details['+$grid_row+'][other_tax_percent]" id="fund_transfer_detail_other_tax_percent_'+$grid_row+'" value="0"/>';
    $html += '</td>'
    $html += '<td hidden>';
    $html += '<input onchange="calculateOtherTaxPercent(this);" type="text" class="form-control fPDecimal" name="fund_transfer_details['+$grid_row+'][other_tax_amount]" id="fund_transfer_detail_other_tax_amount_'+$grid_row+'" value="0.00"/>';
    $html += '</td>'
    $html += '<td hidden>';
    $html += '<input type="text" class="form-control" name="fund_transfer_details['+$grid_row+'][net_amount]" id="fund_transfer_detail_net_amount_'+$grid_row+'" value="0.00" readonly="true"/>';
    $html += '</td>'
    $html += '<td>'
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '</tr>';


    $('#tblFundTransferDetail tbody').append($html);
    //$('#fund_transfer_detail_ref_document_identity_'+$grid_row).select2({width: '100%'});
    //$('#fund_transfer_detail_coa_id_'+$grid_row).select2({width: '100%'});
    $('#fund_transfer_detail_amount_'+$grid_row).trigger('change');
    setFieldFormat();
    $grid_row++;
});

function setDocumentInfo($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $data = $($obj).find(':selected').data();

    $('#fund_transfer_detail_ref_document_type_id_' + $row_id).val($data['ref_document_type_id']);
    $('#fund_transfer_detail_document_amount_' + $row_id).val($data['document_amount']);
    $('#fund_transfer_detail_amount_' + $row_id).val($data['amount']);
    $('#fund_transfer_detail_net_amount_' + $row_id).val($data['amount']);

    calculateTotal();
}

function removeRow($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_'+$row_id).remove();
    calculateTotal();
}

function calculateTaxes($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $balance_amount = parseFloat($('#fund_transfer_detail_balance_amount_' + $row_id).val()) || 0.00;
    var $amount = parseFloat($('#fund_transfer_detail_amount_' + $row_id).val()) || 0.00;

    if( ($amount > $balance_amount) && !empty($('#fund_transfer_detail_ref_document_identity_' + $row_id).val()) )
    {
        alert('Total amount must be less than or equal to balance amount.');
        $('#fund_transfer_detail_amount_' + $row_id).val(0);
        $amount = 0;
    }

    // if( $('#fund_transfer_detail_ref_document_identity_' + $row_id).val() != '' ){
    //     var $total_amount = 0;
    //     var $total_rows = 0;

    //     $('#tblFundTransferDetail tbody tr').each(function() {
    //         $grid_row_id = $(this).data('row_id');
    //         $grid_amount = parseFloat($('#fund_transfer_detail_amount_' + $grid_row_id).val()) || 0.00;
    //         $total_amount += $grid_amount;
    //         $total_rows++;
    //     });

    //     if( $total_amount > $document_amount ){
    //         alert('Total amount must be less than or equal to document amount.');
    //         $('#tblFundTransferDetail tbody tr').each(function() {
    //             $grid_row_id = $(this).data('row_id');
    //             if( $('#fund_transfer_detail_ref_document_identity_' + $grid_row_id).val() != '' ){
    //                 $divide_amount = ($document_amount/$total_rows);
    //                 $('#fund_transfer_detail_amount_' + $grid_row_id).val($divide_amount);
    //                 $('#fund_transfer_detail_net_amount_' + $grid_row_id).val($divide_amount)
    //                 $amount = parseFloat($('#fund_transfer_detail_amount_' + $row_id).val()) || 0.00;
    //             }
    //         });
    //     }
    // }

    var $wht_percent = parseFloat($('#fund_transfer_detail_wht_percent_' + $row_id).val()) || 0.00;
    var $other_tax_percent = parseFloat($('#fund_transfer_detail_other_tax_percent_' + $row_id).val()) || 0.00;

    var $wht_amount = roundUpto(($amount * $wht_percent / 100),2);
    $('#fund_transfer_detail_wht_amount_' + $row_id).val($wht_amount);

    var $other_tax_amount = roundUpto(($amount * $other_tax_percent / 100),2);
    $('#fund_transfer_detail_other_tax_amount_' + $row_id).val($other_tax_amount);

    calculateRowTotal($obj);
}

function calculateWHTAmount($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $wht_percent = parseFloat($($obj).val()) || 0.00;
    var $amount = parseFloat($('#fund_transfer_detail_amount_' + $row_id).val()) || 0.00;

    var $wht_amount = roundUpto(($amount * $wht_percent / 100),2);
    $('#fund_transfer_detail_wht_amount_' + $row_id).val($wht_amount);

    calculateRowTotal($obj);
}

function calculateWHTPercent($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $wht_amount = parseFloat($($obj).val()) || 0.00;
    var $amount = parseFloat($('#fund_transfer_detail_amount_' + $row_id).val()) || 0.00;

    var $wht_percent = roundUpto(($wht_amount / $amount * 100),2);
    $('#fund_transfer_detail_wht_percent_' + $row_id).val($wht_percent);

    calculateRowTotal($obj);
}

function calculateOtherTaxAmount($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $other_tax_percent = parseFloat($($obj).val()) || 0.00;
    var $amount = parseFloat($('#fund_transfer_detail_amount_' + $row_id).val()) || 0.00;

    var $other_tax_amount = roundUpto(($amount * $other_tax_percent / 100),2);
    $('#fund_transfer_detail_other_tax_amount_' + $row_id).val($other_tax_amount);

    calculateRowTotal($obj);
}

function calculateOtherTaxPercent($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $other_tax_amount = parseFloat($($obj).val()) || 0.00;
    var $amount = parseFloat($('#fund_transfer_detail_amount_' + $row_id).val()) || 0.00;

    var $other_tax_percent = roundUpto(($other_tax_amount / $amount * 100),2);
    $('#fund_transfer_detail_other_tax_percent_' + $row_id).val($other_tax_percent);

    calculateRowTotal($obj);
}

function calculateRowTotal($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $amount = parseFloat($('#fund_transfer_detail_amount_' + $row_id).val()) || 0.00;
    var $wht_amount = parseFloat($('#fund_transfer_detail_wht_amount_' + $row_id).val()) || 0.00;
    var $other_tax_amount = parseFloat($('#fund_transfer_detail_other_tax_amount_' + $row_id).val()) || 0.00;
    var $net_amount = ($amount - $wht_amount - $other_tax_amount);
    console.log( $net_amount )
    $('#fund_transfer_detail_net_amount_' + $row_id).val(roundUpto($net_amount,2));
    calculateTotal();
}

function calculateTotal() {
    var $amount_total = 0;
    var $wht_total = 0;
    var $other_tax_total = 0;
    var $net_total = 0;
    $('#tblFundTransferDetail tbody tr').each(function() {
        $row_id = $(this).data('row_id');
        var $amount = $('#fund_transfer_detail_amount_' + $row_id).val() || 0.00;
        var $wht_amount = $('#fund_transfer_detail_wht_amount_' + $row_id).val() || 0.00;
        var $other_tax_amount = $('#fund_transfer_detail_other_tax_amount_' + $row_id).val() || 0.00;
        var $net_amount = $('#fund_transfer_detail_net_amount_' + $row_id).val() || 0.00;

        $amount_total += parseFloat($amount);
        $wht_total += parseFloat($wht_amount);
        $other_tax_total += parseFloat($other_tax_amount);
        $net_total += parseFloat($net_amount);
    })

    $('#total_amount').val(roundUpto($amount_total,2));
    $('#wht_amount').val(roundUpto($wht_total,2));
    $('#other_tax_amount').val(roundUpto($other_tax_total,2));
    $('#net_amount').val(roundUpto($net_total,2));
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