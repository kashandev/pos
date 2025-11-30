/**
 * Created by Huzaifa on 9/18/15.
 */
var $discount_policy;

function Save() {

    $('.btnsave').attr('disabled','disabled');
    if($('#form').valid() == true){
        $('#form').submit();
    }
    else{
        $('.btnsave').removeAttr('disabled');
    }
}

$(document).ready(function() {
//    $('#form').valid();

    $('#document_date').trigger('change');
})

$(document).on('change','#partner_id,#document_date', function() {
    var $partner_type_id = $('#partner_type_id').val();
    var $partner_id = $('#partner_id').val();
    var $document_date = $('#document_date').val();
    $.ajax({
        url: $UrlDiscountPolicy,
        dataType: 'json',
        type: 'post',
        data: 'partner_type_id=' + $partner_type_id+'&partner_id='+$partner_id+'&document_date='+$document_date,
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
                $discount_policy = json.policy;
            } else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })

})

$(document).on('change','#product_code', function() {
    var $obj = this;
    var $product_code = $(this).val();
    $.ajax({
        url: $UrlGetProductByCode,
        dataType: 'json',
        type: 'post',
        data: 'product_code=' + $product_code,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#product_code').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
            // $('#product_code').val('');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {

                fillGrid(json.product);
            }
            else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
})

function fillGrid($data) {
    console.log($data);
    var $product_id = $data['product_id'];
    var $handled = false;
    var $ele = $('#tblPOSInvoice tbody tr[data-product_id="'+$product_id+'"]');
    //console.log($data, $product_id, $ele);
    if($ele.length > 0 )  {
        //alert('In if condition');
        var $row_no = $($ele).data('row_id');
        var $qty = $('#pos_invoice_detail_'+$row_no+'_qty').val();
        var $rate = $('#pos_invoice_detail_'+$row_no+'_rate').val();
        var $cog_rate = $('#pos_invoice_detail_'+$row_no+'_cog_rate').val();
        var $discount_percent = $('#pos_invoice_detail_'+$row_no+'_discount_percent').val();
        $qty = parseFloat($qty) + 1;
        var $amount = parseFloat($rate)*parseFloat($qty);
        var $cog_amount = parseFloat($cog_rate)*parseFloat($qty);

        var $discount_amount = ($amount * $discount_percent)/100;
        var $gross_amount = $amount - $discount_amount;

        $('#pos_invoice_detail_'+$row_no+'_qty').val($qty).trigger('change');
        $('#pos_invoice_detail_'+$row_no+'_cog_amount').val($cog_amount);
        $('#pos_invoice_detail_'+$row_no+'_amount').val($amount);
        $('#pos_invoice_detail_'+$row_no+'_discount_amount').val($discount_amount);
        $('#pos_invoice_detail_'+$row_no+'_gross_amount').val($gross_amount);
        $('#pos_invoice_detail_'+$row_no+'_total_amount').val($gross_amount);

    } else {
        //  alert('In else condition');
        var $qty=1;
        var $sale_price = $data['sale_price'];
        var $amount = $qty*$sale_price;
        // var $discount_percent = 10;
        if (typeof $discount_policy['Product'] !== "undefined" && typeof $discount_policy['Product'][$product_id] !== "undefined") {
            $discount_percent = $discount_policy['Product'][$product_id];
        } else if (typeof $discount_policy['Category'] !== "undefined" && typeof $discount_policy['Category'][$data['product_category_id']] !== 'undefined') {
            $discount_percent = $discount_policy['Category'][$data['product_category_id']];
        } else if (typeof $discount_policy['General'] !== "undefined") {
            $discount_percent = $discount_policy['General'];
        } else {
            $discount_percent = 0;
        }

        var $discount_amount = ($amount * $discount_percent)/100;
        var $gross_amount = $amount - $discount_amount;

        $html = '';
        $html += '<tr id="row_id_'+$row_id+'" data-row_id="'+$row_id+'" data-product_id="'+$data['product_id']+'">';
        $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
        $html += '<td>';
        $html += '<input type="text" style="min-width: 100px;" class="form-control" name="pos_invoice_details['+$row_id+'][product_code]" id="pos_invoice_detail_'+$row_id+'_product_code" value="'+$data['product_code']+'" readonly/>';
        $html += '</td>';
        $html += '<td>';
        $html += '<input type="text" style="min-width: 100px;" class="form-control" name="pos_invoice_details['+$row_id+'][product_name]" id="pos_invoice_detail_'+$row_id+'_product_name" value="'+$data['name']+'" readonly/>';
        $html += '<input type="hidden" style="min-width: 100px;" class="form-control" name="pos_invoice_details['+$row_id+'][product_id]" id="pos_invoice_detail_'+$row_id+'_product_id" value="'+$data['product_id']+'" readonly/>';
        $html += '</td>';
        $html += '<td>';
        $html += '<input onchange="calculateAmount('+$row_id+');" type="text" style="min-width: 100px;" class="form-control fPDecimal text-right" name="pos_invoice_details['+$row_id+'][qty]" id="pos_invoice_detail_'+$row_id+'_qty" value="1"/>';
        $html += '</td>';
        $html += '<td>';
        $html += '<input type="text" style="min-width: 100px;" class="form-control" name="pos_invoice_details['+$row_id+'][unit_name]" id="pos_invoice_detail_'+$row_id+'_unit" value="'+$data['unit']+'" readonly/>';
        $html += '<input type="hidden" style="min-width: 100px;" class="form-control" name="pos_invoice_details['+$row_id+'][unit_id]" id="pos_invoice_detail_'+$row_id+'_unit_id" value="'+$data['unit_id']+'" readonly/>';
        $html += '</td>';
        $html += '<td>';
        $html += '<input type="text" style="min-width: 100px;" class="form-control" name="pos_invoice_details['+$row_id+'][stock]" id="pos_invoice_detail_'+$row_id+'_stock" value="'+$data['stock']['stock_qty']+'" readonly/>';
        $html += '</td>';
        $html += '<td>';
        $html += '<input type="text" style="min-width: 100px;" class="form-control text-right" name="pos_invoice_details['+$row_id+'][rate]" id="pos_invoice_detail_'+$row_id+'_rate" value="'+$data['sale_price']+'" readonly/>';
        $html += '<input type="hidden" style="min-width: 100px;" class="form-control" name="pos_invoice_details['+$row_id+'][cog_rate]" id="pos_invoice_detail_'+$row_id+'_cog_rate" value="'+$data['stock']['avg_stock_rate']+'" readonly/>';
        $html += '<input type="hidden" style="min-width: 100px;" class="form-control" name="pos_invoice_details['+$row_id+'][cog_amount]" id="pos_invoice_detail_'+$row_id+'_cog_amount" value="'+$data['stock']['avg_stock_rate']+'" readonly/>';
        $html += '<input type="hidden" style="min-width: 100px;" class="form-control fPDecimal" name="pos_invoice_details['+$row_id+'][amount]" id="pos_invoice_detail_'+$row_id+'_amount" value="'+$amount+'" readonly="true" />';
        $html += '</td>';
        $html += '<td>';
        $html += '<input onchange="calculateDiscountAmount(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="pos_invoice_details['+$row_id+'][discount_percent]" id="pos_invoice_detail_'+$row_id+'_discount_percent" value="'+$discount_percent+'" readonly />';
        $html += '</td>';
        $html += '<td>';
        $html += '<input onchange="calculateAmount('+$row_id+');" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="pos_invoice_details['+$row_id+'][discount_amount]" id="pos_invoice_detail_'+$row_id+'_discount_amount" value="'+$discount_amount+'" readonly />';
        $html += '</td>';
        $html += '<td>';
        $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="pos_invoice_details['+$row_id+'][gross_amount]" id="pos_invoice_detail_'+$row_id+'_gross_amount" value="'+$gross_amount+'" readonly="true"/>';
        $html += '<input type="hidden" style="min-width: 100px;" class="form-control" name="pos_invoice_details['+$row_id+'][total_amount]" id="pos_invoice_detail_'+$row_id+'_total_amount" value="'+$gross_amount+'" readonly/>';
        $html += '</td>';
        $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
        $html += '</tr>';

        $('#tblPOSInvoice tbody').prepend($html);
        setFieldFormat();

        $row_id++;
    }
    calculateTotal();

}

function myfunction(){

    $('#select2-product_id-container').html('');
    //$('#product_id').trigger('change');;

}

//
//$(document).on('change','#product_id',function(){
//
//    $('#product_id').val('').trigger('change');
//
//
//});

function calculateDiscountAmount($obj) {
    var $row_id = $($obj).parent().parent().data('product_id');
    var $discount_percent = parseFloat($($obj).val() || 0.0000);
    var $amount = parseFloat($('#pos_invoice_detail_'+$row_id+'_amount').val() || 0.0000);
    var $discount_amount = roundUpto($amount * $discount_percent / 100,2);

    var $gross_amount = $amount - $discount_amount;
    console.log($row_id, $discount_percent, $amount, $discount_amount, $gross_amount);

    $('#pos_invoice_detail_'+$row_id+'_discount_amount').val($discount_amount);
    $('#pos_invoice_detail_'+$row_id+'_gross_amount').val($gross_amount);
    $('#pos_invoice_detail_'+$row_id+'_total_amount').val($gross_amount);

    calculateTotal();
}

function calculateAmount($row_no) {
    var $to = 0;
    var $qty = $('#pos_invoice_detail_'+$row_no+'_qty').val();
    var $rate = $('#pos_invoice_detail_'+$row_no+'_rate').val();

    var $amount = parseFloat($qty) * parseFloat($rate);
//     $to = parseFloat($qty) * parseFloat($rate);
    $('#pos_invoice_detail_'+$row_no+'_amount').val($amount);
    $('#pos_invoice_detail_'+$row_no+'_total_amount').val($amount);
    var $discount_percent = $('#pos_invoice_detail_'+$row_no+'_discount_percent').val();
    var $discount_amount = $('#pos_invoice_detail_'+$row_no+'_discount_amount').val();
    //var $discount_amount = ($amount * $discount_percent)/100;
    //$('#pos_invoice_detail_'+$row_no+'_discount_amount').val($discount_amount);

    var gross_amount = $amount - $discount_amount

    $('#pos_invoice_detail_'+$row_no+'_gross_amount').val(gross_amount);
    $('#pos_invoice_detail_'+$row_no+'_total_amount').val(gross_amount);

    calculateTotal();
}

function calculateTotal() {
    var $item_amount = 0;
    var $item_discount = 0;
    var $item_total = 0;
    var $item_quantity = 0;

    $('#tblPOSInvoice tbody tr').each(function() {
        var $row_no = $(this).data('row_id');
        var $amount = $('#pos_invoice_detail_'+$row_no+'_amount').val();
        var $discount_amount = $('#pos_invoice_detail_'+$row_no+'_discount_amount').val();
        var $total_amount = $('#pos_invoice_detail_'+$row_no+'_total_amount').val();
        var $total_quantity = $('#pos_invoice_detail_'+$row_no+'_qty').val();
        //alert($total_amount);

        $item_amount += parseFloat($amount);
        $item_total += parseFloat($total_amount);
        $item_quantity += parseFloat($total_quantity);
    })

    $('#item_amount').val($item_amount.toFixed(2));
    $('#total_quantity').val($item_quantity.toFixed(2));
    $('#item_total').val($item_total.toFixed(2));

    calculateNetAmount();
}

function calculateNetAmount() {
    var $item_total = $('#item_total').val() || 0.00;
    var $discount = $('#discount').val() || 0.00;
    var $net_amount = $item_total - $discount;

    $('#discount').val(roundUpto($discount,2));
    $('#net_amount').val(roundUpto($net_amount,2));
}

function removeRow($obj) {
    var $id = $($obj).parent().parent().data('row_id');
    $('#row_id_'+$id).remove();

    calculateTotal();
}