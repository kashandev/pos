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

$(document).on('click','.btnAddGrid', function() {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td>';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '&nbsp;<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '</td>';
    $html += '<td>';
    $html += '<select class="form-control select2 coa_id" id="credit_invoice_detail_coa_id_'+$grid_row+'" name="credit_invoice_details['+$grid_row+'][coa_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $coas.forEach(function($coa) {
        $html += '<option value="'+$coa['coa_level3_id']+'">'+$coa['level3_display_name']+'</option>';
    })
    $html += '</select>';
    $html += '</td>'
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="credit_invoice_details['+$grid_row+'][remarks]" id="credit_invoice_detail_remarks_'+$grid_row+'" value="" />';
    $html += '</td>'
    $html += '<td>';
    $html += '<input onchange="calculateTotal();" type="text" class="form-control fPDecimal text-right" name="credit_invoice_details['+$grid_row+'][amount]" id="credit_invoice_detail_amount_'+$grid_row+'" value="" />';
    $html += '</td>'
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '&nbsp;<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '</tr>';

    if($(this).parent().parent().data('row_id')=='H') {
        $('#tblDebitInvoiceDetail tbody').prepend($html);
    } else {
        $(this).parent().parent().after($html);
    }

    setFieldFormat();
    $('#credit_invoice_detail_coa_id_'+$grid_row).select2({width: '100%'}).select2('open');
    $grid_row++;
});

function removeRow($obj) {
    //console.log($obj);
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_'+$row_id).remove();
    calculateTotal();
}

function calculateTotal() {
    var $total_amount = 0;
    $('#tblDebitInvoiceDetail tbody tr').each(function() {
        $row_id = $(this).data('row_id');
        $amount = $('#credit_invoice_detail_amount_' + $row_id).val();

        $total_amount += parseFloat($amount);
    })

    $('#net_amount').val(roundUpto($total_amount,2));
    calculateBaseAmount();
}

$(document).on('change','#conversion_rate', function() {
    calculateBaseAmount();
})

function calculateBaseAmount() {
    var $net_amount = parseFloat($('#net_amount').val()) || 0.00;
    var $conversion_rate = parseFloat($('#conversion_rate').val()) || 0.00;

    var $base_amount = $net_amount * $conversion_rate;
    $('#base_amount').val($base_amount);
}

function Save() {
    $('.coa_id').each(function() {
        $(this).rules("add", 
            {
                required: true,
                messages: {
                    required: "Account is required",
                  }
            });
    });

    $('.btnsave').attr('disabled','disabled');
    if($('#form').valid() == true){
        $('#form').submit();
    }
    else{
        $('.btnsave').removeAttr('disabled');
    }
}