/**
 * Created by Huzaifa on 9/18/15.
 */
$(document).on('change', '#project_id', function(){

    $project_id = $(this).val();
    $.ajax({
        url: $UrlGetSubProjects,
        dataType: 'json',
        type: 'post',
        data: 'project_id=' + $project_id+'&sub_project_id='+$sub_project_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#sub_project_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#sub_project_id').select2('destroy');
                $('#sub_project_id').select2({'width':'100%'});
                $('#sub_project_id').html(json.html).trigger('change');
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

 // var $selected_partner = '';

 // $(window).load(function(){
 //    $selected_partner = $('#partner_id').val();
 //    if( $isEdit ){
 //        $('#partner_id').trigger('change')
 //    }
 // })

// $(document).on('change','#partner_category_id', function() {
//     $partner_category_id = $(this).val();
//     $.ajax({
//         url: $UrlGetNewPartner,
//         dataType: 'json',
//         type: 'post',
//         data: 'partner_category_id=' + $partner_category_id+'&partner_id='+$partner_id,
//         mimeType:"multipart/form-data",
//         beforeSend: function() {
//             $('#partner_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
//         },
//         complete: function() {
//             $('#loader').remove();
//         },
//         success: function(json) {
//             if(json.success)
//             {
//                 $('#partner_id').select2('destroy');
//                 $('#partner_id').html(json.html).trigger('change');
//                 $('#partner_id').select2({width:'100%'});
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
               $partners = json.partners;
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
        $('#tblBankReceiptDetail').children('tbody').empty();
    }
    $partner_id = $(this).val();
    // console.log($UrlGetDocuments);
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

    var $cheque_no = $('#cheque_no').val();
    var $cheque_date = $('#cheque_date').val();
    var $bank_name = $('#bank_name').val();
    var $partner_id = $('#partner_id').val();
    //console.log($partners);
//    var $accounts = $partners[$partner_id]['coas'];
    var $accounts = $partner_coas;
    var $document_identity = $('#ref_document_identity').val();
    if($document_identity != '') {
        var $document = $documents[$document_identity];

        var $wh = $('#wht_per').val();
//        $('#wht_tax_per').val($wh);
        var $amount = parseFloat($document['outstanding_amount']) || 0.00;
        var $wht_amount = ($amount * $wh / 100).toFixed(2);
        var $net_amount = ($amount - $wht_amount).toFixed(2);

//        $('#bank_receipt_detail_bank_amount_' + $row_id).val(roundUpto($net_amount,2));
//        $('#bank_receipt_detail_net_amount_' + $row_id).val(roundUpto($amount,2));
        console.log($document);


        $html = '';
            if($document['exempted'] == "1")
            {
                console.log($document['exempted']);

                $html += '<tr style="background-color: #b5d5ff" class="tabletext" id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';

            }
        else{
                $html += '<tr class="tabletext" id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';

            }
        $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a><a  class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>';
        $html += '<td>';
        $html += '<input type="hidden" name="bank_receipt_details['+$grid_row+'][ref_document_type_id]" id="bank_receipt_detail_ref_document_type_id_'+$grid_row+'" value="'+$document['ref_document_type_id']+'" />';
        $html += '<input type="hidden" name="bank_receipt_details['+$grid_row+'][ref_document_identity]" id="bank_receipt_detail_ref_document_identity_'+$grid_row+'" value="'+$document['ref_document_identity']+'" />';
        $html += '<a target="_blank" href="'+$document['href']+'">'+$document_identity+'</a>';
        $html += '</td>';
        $html += '<td style="width: 200px;" >';
        $html += '<select class="form-control select2" id="bank_receipt_detail_coa_id_'+$grid_row+'" name="bank_receipt_details['+$grid_row+'][coa_id]">';
        $.each($accounts, function (index, $account) {
            $html += '<option value="'+$account['coa_level3_id']+'">'+$account['level3_display_name']+'</option>';
        })
        $html += '</select>';
        $html += '</td>';
        $html += '<td>';
        $html += '<input type="text" class="form-control" name="bank_receipt_details['+$grid_row+'][document_amount]" id="bank_receipt_detail_document_amount_'+$grid_row+'" value="'+$document['document_amount']+'" readonly="true"/>';
        $html += '</td>'
        $html += '<td>';
        $html += '<input  type="text" class="form-control" id="bank_receipt_detail_balance_amount_'+$grid_row+'" value="'+$document['outstanding_amount']+'" readonly="true"/>';
        $html += '</td>'
        $html += '<td>';
        $html += '<input onchange="calculateTaxes(this);" type="text" class="form-control fDecimal" name="bank_receipt_details['+$grid_row+'][amount]" id="bank_receipt_detail_amount_'+$grid_row+'" value="0" />';
        $html += '</td>'
        $html += '<td>';
        $html += '<input  type="text" class="form-control" name="bank_receipt_details['+$grid_row+'][bank_amount]" id="bank_receipt_detail_bank_amount_'+$grid_row+'" value="'+$net_amount+'" readonly/>';
        $html += '</td>'
        // $html += '<td>';
        // $html += '<input onchange="calculateWHTAmount(this);" type="text" class="form-control fPDecimal" name="bank_receipt_details['+$grid_row+'][wht_percent]" id="bank_receipt_detail_wht_percent_'+$grid_row+'" value="'+$wh+'"/>';
        // $html += '</td>'
        // $html += '<td>';
        // $html += '<input onchange="calculateWHTPercent(this);" type="text" class="form-control fPDecimal" name="bank_receipt_details['+$grid_row+'][wht_amount]" id="bank_receipt_detail_wht_amount_'+$grid_row+'" value="'+$wht_amount+'"/>';
        // $html += '</td>'
        // $html += '<td hidden="hidden">';
        // $html += '<input onchange="calculateOtherTaxAmount(this);" type="text" class="form-control fPDecimal" name="bank_receipt_details['+$grid_row+'][other_tax_percent]" id="bank_receipt_detail_other_tax_percent_'+$grid_row+'" value="0"/>';
        // $html += '</td>'
        // $html += '<td>';
        // $html += '<input onchange="calculateOtherTaxPercent(this);" type="text" class="form-control fPDecimal" name="bank_receipt_details['+$grid_row+'][other_tax_amount]" id="bank_receipt_detail_other_tax_amount_'+$grid_row+'" value="0.00"/>';
        // $html += '</td>'
        $html += '<td>';
        $html += '<input type="text" class="form-control fDecimal" name="bank_receipt_details['+$grid_row+'][net_amount]" id="bank_receipt_detail_net_amount_'+$grid_row+'" value="'+$amount+'" readonly="true"/>';
        $html += '</td>'
        $html += '<td>';
        $html += '<input type="text" class="form-control" name="bank_receipt_details['+$grid_row+'][remarks]" id="bank_receipt_detail_remarks_'+$grid_row+'" value="" />';
        $html += '</td>'
        $html += '<td>';
        $html += '<input type="text" class="form-control" name="bank_receipt_details['+$grid_row+'][bank_name]" id="bank_receipt_detail_bank_name_'+$grid_row+'" value="'+$bank_name+'" />';
        $html += '</td>'
        $html += '<td>';
        $html += '<input type="text" class="form-control dtpDate" name="bank_receipt_details['+$grid_row+'][cheque_date]" id="bank_receipt_detail_cheque_date_'+$grid_row+'" value="'+$cheque_date+'" />';
        $html += '</td>'
        $html += '<td>';
        $html += '<input type="text" class="form-control" name="bank_receipt_details['+$grid_row+'][cheque_no]" id="bank_receipt_detail_cheque_no_'+$grid_row+'" value="'+$cheque_no+'" />';
        $html += '</td>'
        $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a><a  class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>';
        $html += '</tr>';

        $('#tblBankReceiptDetail tbody').append($html);
        //$('#bank_receipt_detail_ref_document_identity_'+$grid_row).select2({width: '100%'});
        //$('#bank_receipt_detail_coa_id_'+$grid_row).select2({width: '100%'});

        $('#bank_receipt_detail_amount_'+$grid_row).trigger('change');
        calculateTotal();


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
    $html += '<tr class="tabletext" id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '<td style="min-width: 200px;">';
    $html += '<input type="hidden" class="form-control" name="bank_receipt_details['+$grid_row+'][ref_document_type_id]" id="bank_receipt_detail_ref_document_type_id_'+$grid_row+'" value="" />';
    $html += '<input type="hidden" class="form-control" name="bank_receipt_details['+$grid_row+'][ref_document_identity]" id="bank_receipt_detail_ref_document_identity_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td style="width: 200px;">';
    $html += '<select class="form-control select2" id="bank_receipt_detail_coa_id_'+$grid_row+'" name="bank_receipt_details['+$grid_row+'][coa_id]">';
    $.each($accounts, function (index, $account) {
        $html += '<option value="'+$account['coa_level3_id']+'">'+$account['level3_display_name']+'</option>';
    })
    $html += '</select>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="bank_receipt_details['+$grid_row+'][document_amount]" id="bank_receipt_detail_document_amount_'+$grid_row+'" value="0.00" readonly="true"/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" id="bank_receipt_detail_balance_amount_'+$grid_row+'" value="0.00" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateTaxes(this);" type="text" class="form-control fDecimal" name="bank_receipt_details['+$grid_row+'][amount]" id="bank_receipt_detail_amount_'+$grid_row+'" value="0.00" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input  type="text" class="form-control" name="bank_receipt_details['+$grid_row+'][bank_amount]" id="bank_receipt_detail_bank_amount_'+$grid_row+'" value="0.00" readonly/>';
    $html += '</td>';
    // $html += '<td>';
    // $html += '<input onchange="calculateWHTAmount(this);" type="text" class="form-control fPDecimal" name="bank_receipt_details['+$grid_row+'][wht_percent]" id="bank_receipt_detail_wht_percent_'+$grid_row+'" value="0"/>';
    // $html += '</td>';
    // $html += '<td>';
    // $html += '<input onchange="calculateWHTPercent(this);" type="text" class="form-control fPDecimal" name="bank_receipt_details['+$grid_row+'][wht_amount]" id="bank_receipt_detail_wht_amount_'+$grid_row+'" value="0.00"/>';
    // $html += '</td>';
    // // $html += '<td hidden="hidden">';
    // $html += '<input onchange="calculateOtherTaxAmount(this);" type="text" class="form-control fPDecimal" name="bank_receipt_details['+$grid_row+'][other_tax_percent]" id="bank_receipt_detail_other_tax_percent_'+$grid_row+'" value="0"/>';
    // // $html += '</td>'
    // $html += '<td>';
    $html += '<input onchange="calculateOtherTaxPercent(this);" type="text" class="form-control fPDecimal" name="bank_receipt_details['+$grid_row+'][other_tax_amount]" id="bank_receipt_detail_other_tax_amount_'+$grid_row+'" value="0.00"/>';
    // $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control fDecimal" name="bank_receipt_details['+$grid_row+'][net_amount]" id="bank_receipt_detail_net_amount_'+$grid_row+'" value="0.00" readonly="true"/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="bank_receipt_details['+$grid_row+'][remarks]" id="bank_receipt_detail_remarks_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="bank_receipt_details['+$grid_row+'][bank_name]" id="bank_receipt_detail_bank_name_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control dtpDate" name="bank_receipt_details['+$grid_row+'][cheque_date]" id="bank_receipt_detail_cheque_date_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="bank_receipt_details['+$grid_row+'][cheque_no]" id="bank_receipt_detail_cheque_no_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '</tr>';


    $('#tblBankReceiptDetail tbody').append($html);
    //$('#bank_receipt_detail_ref_document_identity_'+$grid_row).select2({width: '100%'});
    //$('#bank_receipt_detail_coa_id_'+$grid_row).select2({width: '100%'});
    setFieldFormat();
    $grid_row++;
    calculateTotal();

    });

function setDocumentInfo($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $data = $($obj).find(':selected').data()
    console.log($data);

    $('#bank_receipt_detail_ref_document_type_id_' + $row_id).val($data['ref_document_type_id']);
    $('#bank_receipt_detail_document_amount_' + $row_id).val($data['document_amount']);
    $('#bank_receipt_detail_amount_' + $row_id).val($data['amount']);
    $('#bank_receipt_detail_net_amount_' + $row_id).val($data['amount']);

    calculateTotal();
}

function removeRow($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_'+$row_id).remove();
    calculateTotal();
}

function calculateTaxes($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    // var $document_amount = parseFloat($('#bank_receipt_detail_document_amount_' + $row_id).val()) || 0.00;
    var $balance_amount = parseFloat($('#bank_receipt_detail_balance_amount_' + $row_id).val()) || 0.00;
    var $amount = parseFloat($('#bank_receipt_detail_amount_' + $row_id).val()) || 0.00;

    if( ($amount > $balance_amount) && !empty($('#bank_receipt_detail_ref_document_identity_' + $row_id).val()) )
    {
        alert('Total amount must be less than or equal to balance amount.');
        $('#bank_receipt_detail_amount_' + $row_id).val(0);
        $amount = 0;
    }


    // if( $('#bank_receipt_detail_ref_document_identity_' + $row_id).val() != '' ){
    //     var $total_amount = 0;
    //     var $total_rows = 0;

    //     $('#tblBankReceiptDetail tbody tr').each(function() {
    //         $grid_row_id = $(this).data('row_id');
    //         $grid_amount = parseFloat($('#bank_receipt_detail_amount_' + $grid_row_id).val()) || 0.00;
    //         $total_amount += $grid_amount;
    //         $total_rows++;
    //     });

    //     if( $total_amount > $document_amount ){
    //         alert('Total amount must be less than or equal to document amount.');
    //         $('#tblBankReceiptDetail tbody tr').each(function() {
    //             $grid_row_id = $(this).data('row_id');
    //             if( $('#bank_receipt_detail_ref_document_identity_' + $grid_row_id).val() != '' ){
    //                 $divide_amount = ($document_amount/$total_rows);
    //                 $('#bank_receipt_detail_amount_' + $grid_row_id).val($divide_amount);
    //                 $('#bank_receipt_detail_net_amount_' + $grid_row_id).val($divide_amount)
    //                 $amount = parseFloat($('#bank_receipt_detail_amount_' + $row_id).val()) || 0.00;
    //             }
    //         });
    //     }
    // }

    var $wht_percent = parseFloat($('#bank_receipt_detail_wht_percent_' + $row_id).val()) || 0.00;
    var $other_tax_percent = parseFloat($('#bank_receipt_detail_other_tax_percent_' + $row_id).val()) || 0.00;

    var $wht_amount = roundUpto(($amount * $wht_percent / 100),2);
    $('#bank_receipt_detail_wht_amount_' + $row_id).val($wht_amount);

    var $other_tax_amount = roundUpto(($amount * $other_tax_percent / 100),2);
    $('#bank_receipt_detail_other_tax_amount_' + $row_id).val($other_tax_amount);

    calculateRowTotal($obj);
}

function calculateWHTAmount($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $wht_percent = parseFloat($($obj).val()) || 0.00;
    var $amount = parseFloat($('#bank_receipt_detail_amount_' + $row_id).val()) || 0.00;

    var $wht_amount = roundUpto(($amount * $wht_percent / 100),2);
    $('#bank_receipt_detail_wht_amount_' + $row_id).val($wht_amount);

    calculateRowTotal($obj);
}

function calculateWHTPercent($obj) {
//    var $row_id = $($obj).parent().parent().data('row_id');
//    var $wht_amount = parseFloat($($obj).val()) || 0.00;
//    var $amount = parseFloat($('#bank_receipt_detail_amount_' + $row_id).val()) || 0.00;
//
//    var $wht_percent = roundUpto(($wht_amount / $amount * 100),2);
//    $('#bank_receipt_detail_wht_percent_' + $row_id).val($wht_percent);
//
    calculateRowTotal($obj);
}

function calculateOtherTaxAmount($obj) {
//    var $row_id = $($obj).parent().parent().data('row_id');
//    var $other_tax_percent = parseFloat($($obj).val()) || 0.00;
//    var $amount = parseFloat($('#bank_receipt_detail_amount_' + $row_id).val()) || 0.00;
//
//    var $other_tax_amount = roundUpto(($amount * $other_tax_percent / 100),2);
//    $('#bank_receipt_detail_other_tax_amount_' + $row_id).val($other_tax_amount);
//
//    calculateRowTotal($obj);
}

function calculateOtherTaxPercent($obj) {
//    var $row_id = $($obj).parent().parent().data('row_id');
//    var $other_tax_amount = parseFloat($($obj).val()) || 0.00;
//    var $amount = parseFloat($('#bank_receipt_detail_amount_' + $row_id).val()) || 0.00;
//
//    var $other_tax_percent = roundUpto(($other_tax_amount / $amount * 100),2);
//    $('#bank_receipt_detail_other_tax_percent_' + $row_id).val($other_tax_percent);
//
    calculateRowTotal($obj);
}

function calculateRowTotal($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $amount = parseFloat($('#bank_receipt_detail_amount_' + $row_id).val()) || 0.00;
    var $wht_amount = parseFloat($('#bank_receipt_detail_wht_amount_' + $row_id).val()) || 0.00;
    var $other_tax_amount = parseFloat($('#bank_receipt_detail_other_tax_amount_' + $row_id).val()) || 0.00;
    var $net_amount = $amount - $wht_amount - $other_tax_amount;

    $('#bank_receipt_detail_bank_amount_' + $row_id).val(roundUpto($net_amount,2));
    $('#bank_receipt_detail_net_amount_' + $row_id).val(roundUpto($amount,2));


    calculateTotal();
}

function calculateTotal() {
    var $amount_total = 0;
    var $wht_total = 0;
    var $other_tax_total = 0;
    var $net_total = 0;
    $('#tblBankReceiptDetail tbody tr').each(function() {
        $row_id = $(this).data('row_id');
        var $amount = $('#bank_receipt_detail_bank_amount_' + $row_id).val() || 0.00;
        var $wht_amount = $('#bank_receipt_detail_wht_amount_' + $row_id).val() || 0.00;
        var $other_tax_amount = $('#bank_receipt_detail_other_tax_amount_' + $row_id).val() || 0.00;
        var $net_amount = $('#bank_receipt_detail_net_amount_' + $row_id).val() || 0.00;

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


function AddTaxes() {
    var $wht_tax_per = $('#wht_tax_per').val();
    var $ot_tax_per =  $('#ot_tax_per').val();

    $('#tblBankReceiptDetail tbody tr').each(function() {

        $row_id = $(this).data('row_id');
//         $('#bank_receipt_detail_wht_percent_' + $row_id).val($wht_tax_per).trigger('change');
//        $('#bank_receipt_detail_other_tax_percent_' + $row_id).val($ot_tax_per).trigger('change');

        var $amount = parseFloat($('#bank_receipt_detail_amount_' + $row_id).val()) || 0.00;
        //var $wht_percent = parseFloat($('#bank_receipt_detail_wht_percent_' + $row_id).val()) || 0.00;
        //var $other_tax_percent = parseFloat($('#bank_receipt_detail_other_tax_percent_' + $row_id).val()) || 0.00;

        var $wht_percent = parseFloat($('#bank_receipt_detail_wht_percent_' + $row_id).val($wht_tax_per)) || 0.00;

        var $wht_amount = roundUpto(($amount * $wht_tax_per / 100),2);
        $('#bank_receipt_detail_wht_amount_' + $row_id).val($wht_amount);

        var $other_tax_amount = roundUpto(($amount * $ot_tax_per / 100),2);
        $('#bank_receipt_detail_other_tax_amount_' + $row_id).val($other_tax_amount);

        var $net_amount = $amount - $wht_amount - $other_tax_amount;

        $('#bank_receipt_detail_bank_amount_' + $row_id).val(roundUpto($net_amount,2));
        $('#bank_receipt_detail_net_amount_' + $row_id).val(roundUpto($amount,2));

// console.log($amount,$wht_tax_per,$ot_tax_per,$wht_amount,$other_tax_amount,$net_amount);

    })

    calculateTotal();
}


function Save() {
    // alert('1');
    if($('#form').valid() == true){
        // alert('2');
        $('#form').submit();
        $('.btnsave').attr('disabled','disabled');
    }
    else{
        // alert('3');
        $('.btnsave').removeAttr('disabled');
    }
}


$(document).ready(function() {
    // var $sale_tax_invoice_id = '<?php echo $this->request->get["sale_tax_invoice_id"]; ?>';
    if($sale_tax_invoice_id != '')
    {
        $.ajax({
           url: $UrlGetSaleDocument,
           dataType: 'json',
           type: 'post',
           data: 'sale_tax_invoice_id=' + $sale_tax_invoice_id,
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
                console.log(json);
                    $('#partner_type_id').val(json.sale_inv['partner_type_id']);
                    $('#partner_id').select2('destroy');
                    $('#partner_id').html(json.html2);
                    $('#partner_id').val(json.sale_inv['partner_id']);
                    $('#partner_id').select2({width:'100%'});
                    $('#tblBankReceiptDetail tbody').empty();
                    $('#ref_document_identity').select2('destroy');
                    $('#ref_document_identity').html(json.html);
                    $('#ref_document_identity').val(json.sale_inv['document_identity']);
                    $('#ref_document_identity').select2({width:'100%'});
                    $partners = json.partners;
                    $documents = json.documents;
                    $partner_coas = json.partner_coas;
                    $('#addRefDocument').trigger('click');
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
});