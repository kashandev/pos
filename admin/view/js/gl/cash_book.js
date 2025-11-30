/**
 * Created by Huzaifa on 9/18/15.
 */


$(document).on('change','#partner_id', function() {

    var $partner_type_id = 2;
    var $partner_id = $(this).val();

    $.ajax({
        url: $UrlGetDocuments,
        dataType: 'json',
        type: 'post',
        data: 'partner_type_id=' + $partner_type_id+'&partner_id='+$partner_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#lblRef').after('<i id="loader" class="fa fa-refresh fa-spin"></i>');
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
                console.log('s');
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


$(document).on('click','#addRefDocument', function() {

    var $partner_id = $('#partner_id').val();
    var $document_identity = $('#ref_document_identity').val();
    if($document_identity != '') {

        var $document = $documents[$document_identity];
        var $amount = parseFloat($document['outstanding_amount']) || 0.00;

        console.log($document,$grid_row);


        $html = '';
        $html += '<tr class="tabletext" id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
        $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
        $html += '<td>';
        $html += '<input type="text" class="form-control" name="cash_book_details['+$grid_row+'][partner_name]" id="cash_book_detail_partner_name_'+$grid_row+'" value="'+$document['name']+'" readonly/>';
        $html += '<input type="hidden" name="cash_book_details['+$grid_row+'][partner_id]" id="cash_book_detail_partner_id_'+$grid_row+'" value="'+$document['partner_id']+'" />';
        $html += '</td>';
        $html += '<td>';
        $html += '<input type="hidden" name="cash_book_details['+$grid_row+'][ref_document_type_id]" id="cash_book_detail_ref_document_type_id_'+$grid_row+'" value="'+$document['ref_document_type_id']+'" />';
        $html += '<input type="hidden" name="cash_book_details['+$grid_row+'][ref_document_identity]" id="cash_book_detail_ref_document_identity_'+$grid_row+'" value="'+$document['ref_document_identity']+'" />';
        $html += '<a target="_blank" href="'+$document['href']+'">'+$document_identity+'</a>';
        $html += '</td>';
        $html += '<td>';
        $html += '<input type="text" class="form-control" name="cash_book_details['+$grid_row+'][po_no]" id="cash_book_detail_po_no_'+$grid_row+'" value="'+$document['po_no']+'" readonly="true"/>';
        $html += '</td>'
        $html += '<td>';
        $html += '<input type="text" class="form-control" name="cash_book_details['+$grid_row+'][dc_no]" id="cash_book_detail_dc_no_'+$grid_row+'" value="'+$document['dc_no']+'" readonly="true"/>';
        $html += '</td>'
        $html += '<td>';
        $html += '<input type="text" class="form-control" name="cash_book_details['+$grid_row+'][document_amount]" id="cash_book_detail_document_amount_'+$grid_row+'" value="'+$document['document_amount']+'" readonly="true"/>';
        $html += '</td>'
        $html += '<td>';
        $html += '<input  type="text" class="form-control" name="cash_book_details['+$grid_row+'][balance_amount]" id="cash_book_detail_balance_amount_'+$grid_row+'" value="'+$document['outstanding_amount']+'" readonly="true"/>';
        $html += '</td>'
        $html += '<td>';
        $html += '<input onchange="calculateTotal();" type="text" class="form-control" name="cash_book_details['+$grid_row+'][amount]" id="cash_book_detail_amount_'+$grid_row+'" value="'+$document['outstanding_amount']+'" />';
        $html += '</td>'
        $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
        $html += '</tr>';



        $('#tblCashBook tbody').append($html);
        //$('#cash_book_detail_ref_document_identity_'+$grid_row).select2({width: '100%'});
        //$('#cash_book_detail_coa_id_'+$grid_row).select2({width: '100%'});
        setFieldFormat();
        $grid_row++;

        calculateTotal();
    }

});

//function getDocuments($obj) {
//
//    var $row_id = $($obj).parent().parent().data('row_id');
//    var $partner_id = $('#opening_account_detail_partner_id_' + $row_id).val();
//    var $partner_type_id = 2;
//    console.log($partner_id,$row_id);
//
//    $.ajax({
//        url: $UrlGetDocuments,
//        dataType: 'json',
//        type: 'post',
//        data: 'partner_type_id=' + $partner_type_id+'&partner_id='+$partner_id,
//        mimeType:"multipart/form-data",
//        beforeSend: function() {
////            $('#ref_document_identity').after('<i id="loader" class="fa fa-refresh fa-spin"></i>');
//        },
//        complete: function() {
////            $('#loader').remove();
//        },
//        success: function(json) {
//            if(json.success)
//            {
//                $('#opening_account_detail_document_id_' + $row_id).html(json.html).trigger('change');;
////                //$partners = json.partners;
////                $documents = json.documents;
////                $partner_coas = json.partner_coas;
//
//                console.log(json.html);
//            }
//            else {
//                alert(json.error);
//            }
//        },
//        error: function(xhr, ajaxOptions, thrownError) {
//            console.log(xhr.responseText);
//        }
//    })
//}

function removeRow($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_'+$row_id).remove();
    calculateTotal();
}

function calculateTotal() {
    var $amount_total = 0;
    $('#tblCashBook tbody tr').each(function() {
        $row_id = $(this).data('row_id');
        var $amount = $('#cash_book_detail_amount_' + $row_id).val() || 0.00;

        $amount_total += parseFloat($amount);
    })

    $('#total_amount').val(roundUpto($amount_total,2));
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