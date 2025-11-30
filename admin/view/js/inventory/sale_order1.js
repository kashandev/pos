/**
 * Created by Huzaifa on 9/18/15.
 */

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
//                 $('#partner_id').html(json.html);
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

$(document).on('change','#partner_id', function() {
    var $partner_type_id = $('#partner_type_id').val();
    var $partner_id = $('#partner_id').val();
    var $ref_document_id = $('#ref_document_id').val();

//    if($ref_document_id!='') {
//
//        $('#TdAdd').hide();
//        $('#TdAdd1').hide();
//    }
//    else{
//        $('#TdAdd').show();
//        $('#TdAdd1').show();
//    }

    $.ajax({
        url: $UrlGetRefDocumentNo,
        dataType: 'json',
        type: 'post',
        data: 'partner_type_id=' + $partner_type_id + '&partner_id=' + $partner_id  ,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#ref_document_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#ref_document_id').html(json['html']).trigger('change');
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

$(document).on('change','#ref_document_id', function() {
    var $document_currency_id = $('#document_currency_id').val();
    var $partner_type_id = $('#partner_type_id').val();
    var $partner_id = $('#partner_id').val();
    var $ref_document_type_id = $('#ref_document_type_id').val();
    var $ref_document_identity = $(this).val();

    if($ref_document_identity!='') {
        var $data = {
            'document_currency_id': $document_currency_id,
            'partner_type_id': $partner_type_id,
            'partner_id': $partner_id,
            'ref_document_type_id': $ref_document_type_id,
            'ref_document_identity': $ref_document_identity
        };

        $.ajax({
            url: $UrlGetRefDocument,
            dataType: 'json',
            type: 'post',
            data: $data,
            mimeType:"multipart/form-data",
            beforeSend: function() {
                $('#ref_document_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
            },
            complete: function() {
                $('#loader').remove();
            },
            success: function(json) {
                if(json.success)
                {
                    $('#document_currency_id').val(json.data['document_currency_id']);
                    $('#conversion_rate').val(json.data['conversion_rate']);
                    $('#base_currency_id').val(json.data['base_currency_id']);
                    $('#discount').val(json.data['discount']);

                    $('').remove();
                    $('#tblSaleOrder tbody tr').remove();

                    $.each(json.data['products'], function($i,$product) {
                        fillGrid($product);
                    });

                    calculateTotal();
                }
                else {
                    alert(json.error);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                console.log(xhr.responseText);
            }
        })
    } else {
        $('#tblSaleOrder tbody').html('');
    }
});



function fillGrid($obj) {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td><a title="Remove" class="btnRemoveGrid btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>';
    $html += '<td>';
    $html += '<a target="_blank" href="'+$obj['href']+'" title="Ref. Document">'+$obj['ref_document_identity']+'</a>';
    $html += '<input type="hidden" class="form-control" name="sale_order_details['+$grid_row+'][ref_document_type_id]" id="sale_order_detail_ref_document_type_id_'+$grid_row+'" value="'+$obj['ref_document_type_id']+'" readonly/>';
    $html += '<input type="hidden" class="form-control" name="sale_order_details['+$grid_row+'][ref_document_identity]" id="sale_order_detail_ref_document_identity_'+$grid_row+'" value="'+$obj['ref_document_identity']+'" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="sale_order_details['+$grid_row+'][product_code]" id="sale_order_detail_product_code_'+$grid_row+'" value="'+$obj['product_code']+'" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="hidden" class="form-control" name="sale_order_details['+$grid_row+'][product_id]" id="sale_order_detail_product_id_'+$grid_row+'" value="'+$obj['product_id']+'" readonly/>';
    $html += '<input type="text" class="form-control" name="sale_order_details['+$grid_row+'][product_name]" id="sale_order_detail_product_name_'+$grid_row+'" value="'+$obj['product_name']+'" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="sale_order_details['+$grid_row+'][description]" id="sale_order_detail_description_'+$grid_row+'" value="'+$obj['description']+'" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="hidden" class="form-control" name="sale_order_details['+$grid_row+'][unit_id]" id="sale_order_detail_unit_id_'+$grid_row+'" value="'+$obj['unit_id']+'"/>';
    $html += '<input type="text" class="form-control" name="sale_order_details['+$grid_row+'][unit]" id="sale_order_detail_unit_'+$grid_row+'" value="'+$obj['unit']+'" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control fPDecimal" name="sale_order_details['+$grid_row+'][stock_qty]" id="sale_order_detail_stock_qty_'+$grid_row+'" value="'+$obj['stock_qty']+'" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateAmount(this);" type="text" class="form-control fPDecimal" name="sale_order_details['+$grid_row+'][qty]" id="sale_order_detail_qty_'+$grid_row+'" value="'+$obj['balanced_qty']+'" />';
    $html += '<input type="hidden" class="form-control " name="sale_order_details['+$grid_row+'][utilized_qty]" id="sale_order_detail_utilized_qty_'+$grid_row+'" value="'+$obj['balanced_qty']+'" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateAmount(this);" type="text" class="form-control fPDecimal" name="sale_order_details['+$grid_row+'][rate]" id="sale_order_detail_rate_'+$grid_row+'" value="'+$obj['rate']+'" readonly />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control fPDecimal" name="sale_order_details['+$grid_row+'][amount]" id="sale_order_detail_amount_'+$grid_row+'" value="'+$obj['amount']+'" readonly="true" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" onchange="calculateTaxAmount(this);" class="form-control fDecimal" name="sale_order_details['+$grid_row+'][tax_percent]" id="sale_order_detail_tax_percent_'+$grid_row+'" value="'+$obj['tax_percent']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" onchange="calculateTaxAmount(this);" class="form-control fDecimal" name="sale_order_details['+$grid_row+'][tax_amount]" id="sale_order_detail_tax_amount_'+$grid_row+'" value="'+$obj['tax_amount']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text"  class="form-control fDecimal" name="sale_order_details['+$grid_row+'][net_amount]" id="sale_order_detail_net_amount_'+$grid_row+'" value="'+$obj['net_amount']+'" />';
    $html += '</td>';
    // $html += '<td style="width: 3%;">';
    $html += '<td><a title="Remove" class="btnRemoveGrid btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>';
    $html += '</td>';
    $html += '</tr>';

    $('#tblSaleOrder tbody').append($html);
    setFieldFormat();
    $grid_row++;
    calculateTotal();
}


$(document).on('click','#btnAddGrid', function() {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td><a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '<td>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="getProductByCode(this);" type="text" class="form-control" name="sale_order_details['+$grid_row+'][product_code]" id="sale_order_detail_product_code_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<div class="input-group">';
    $html += '<select onchange="getProductById(this);" class="form-control select2" id="sale_order_detail_product_id_'+$grid_row+'" name="sale_order_details['+$grid_row+'][product_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $products.forEach(function($product) {
        $html += '<option value="'+$product.product_id+'">'+$product.name+'</option>';
    });
    $html += '</select>';
    $html += '<span class="input-group-btn ">';
    $html += '<button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="sale_order_detail_product_id_'+$grid_row+'" data-field="product_id">';
    $html += '<i class="fa fa-search"></i>';
    $html += '</button>';
    $html += '</span>';
    $html += '</div>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="sale_order_details['+$grid_row+'][description]" id="sale_order_detail_description_'+$grid_row+'" value=""  />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="sale_order_details['+$grid_row+'][unit]" id="sale_order_detail_unit_'+$grid_row+'" value=""  />';
    $html += '<input type="hidden" class="form-control" name="sale_order_details['+$grid_row+'][unit_id]" id="sale_order_detail_unit_id_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control fPDecimal" name="sale_order_details['+$grid_row+'][stock_qty]" id="sale_order_detail_stock_qty_'+$grid_row+'" value="" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateAmount(this);" type="text" class="form-control fPDecimal" name="sale_order_details['+$grid_row+'][qty]" id="sale_order_detail_qty_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateAmount(this);" type="text" class="form-control fPDecimal" name="sale_order_details['+$grid_row+'][rate]" id="sale_order_detail_rate_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control fPDecimal" name="sale_order_details['+$grid_row+'][amount]" id="sale_order_detail_amount_'+$grid_row+'" value="" readonly="true" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" onchange="calculateTaxAmount(this);" class="form-control fDecimal" name="sale_order_details['+$grid_row+'][tax_percent]" id="sale_order_detail_tax_percent_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" onchange="calculateTaxAmount(this);" class="form-control fDecimal" name="sale_order_details['+$grid_row+'][tax_amount]" id="sale_order_detail_tax_amount_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text"  class="form-control fDecimal" name="sale_order_details['+$grid_row+'][net_amount]" id="sale_order_detail_net_amount_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td><a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';

    $html += '</tr>';


//    $('#tblSaleOrder tbody').prepend($html);
    $('#tblSaleOrder tbody').append($html);
    setFieldFormat();
    $('#sale_order_detail_product_id_'+$grid_row).select2({width: '100%'});
    $('#sale_order_detail_warehouse_id_'+$grid_row).select2({width: '100%'});
    $('#sale_order_detail_product_code_'+$grid_row).focus();

    $grid_row++;
    calculateTotal();
});

function getProductById($obj) {
    $product_id = $($obj).val();
    var $row_id = $($obj).parent().parent().parent().data('row_id');
    $.ajax({
        url: $UrlGetProductById,
        dataType: 'json',
        type: 'post',
        data: 'product_id=' + $product_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-search').addClass('fa-refresh fa-spin');
        },
        complete: function() {
            $('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-refresh').removeClass('fa-spin').addClass('fa-search');
        },
        success: function(json) {
            if(json.success)
            {
                $('#sale_order_detail_description_'+$row_id).val(json.product['name']);
                $('#sale_order_detail_product_code_'+$row_id).val(json.product['product_code']);
                $('#sale_order_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#sale_order_detail_unit_'+$row_id).val(json.product['unit']);
                $('#sale_order_detail_rate_'+$row_id).val(json.product['cost_price']);
                $('#sale_order_detail_stock_qty_'+$row_id).val(json.product['stock']['stock_qty']);

            } else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
}

function getProductByCode($obj) {
    $product_code = $($obj).val();
    var $row_id = $($obj).parent().parent().data('row_id');
    $.ajax({
        url: $UrlGetProductByCode,
        dataType: 'json',
        type: 'post',
        data: 'product_code=' + $product_code,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-search').addClass('fa-refresh fa-spin');
        },
        complete: function() {
            $('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-refresh').removeClass('fa-spin').addClass('fa-search');
        },
        success: function(json) {
            if(json.success)
            {
                console.log($row_id);
                $('#sale_order_detail_description_'+$row_id).val(json.product['name']);
                $('#sale_order_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#sale_order_detail_unit_'+$row_id).val(json.product['unit']);
                $('#sale_order_detail_product_id_'+$row_id).select2('destroy');
                $('#sale_order_detail_product_id_'+$row_id).val(json.product['product_id']);
                $('#sale_order_detail_product_id_'+$row_id).select2({width:'100%'});
                $('#sale_order_detail_rate_'+$row_id).val(json.product['cost_price']);
                $('#sale_order_detail_stock_qty_'+$row_id).val(json.product['stock']['stock_qty']);
            }
            else {
                alert(json.error);
                $('#sale_order_detail_description_'+$row_id).val('');
                $('#sale_order_detail_unit_id_'+$row_id).val('');
                $('#sale_order_detail_unit_'+$row_id).val('');
                $('#sale_order_detail_product_id_'+$row_id).select2('destroy');
                $('#sale_order_detail_product_id_'+$row_id).val('');
                $('#sale_order_detail_product_id_'+$row_id).select2({width:'100%'});
                $('#sale_order_detail_rate_'+$row_id).val('0.00');
                $('#sale_order_detail_stock_qty_'+$row_id).val('');
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
}

function setProductInformation($obj) {
    var $data = $($obj).data();
    var $row_id = $('#'+$data['element']).parent().parent().parent().data('row_id');
    $('#_modal').modal('hide');
    $('#sale_order_detail_product_code_'+$row_id).val($data['product_code']);
    $('#sale_order_detail_unit_id_'+$row_id).val($data['unit_id']);
    $('#sale_order_detail_unit_'+$row_id).val($data['unit']);
    $('#sale_order_detail_rate_'+$row_id).val($data['cost_price']);
    $('#sale_order_detail_description_'+$row_id).val($data['name']);
    $('#sale_order_detail_product_id_'+$row_id).select2('destroy');
    $('#sale_order_detail_product_id_'+$row_id).val($data['product_id']);
    $('#sale_order_detail_product_id_'+$row_id).select2({width: '100%'});
}


function removeRow($obj) {
    //console.log($obj);
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_'+$row_id).remove();
    calculateTotal();
}

function calculateAmount($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $qty = parseFloat($('#sale_order_detail_qty_' + $row_id).val()) || 0.00;
    var $rate = parseFloat($('#sale_order_detail_rate_' + $row_id).val()) || 0.00;


    var $amount = $qty * $rate;
    $amount = roundUpto($amount,2);

    $('#sale_order_detail_amount_' + $row_id).val($amount);
    $('#sale_order_detail_tax_percent_' + $row_id).trigger('change');
}

function calculateTaxAmount($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $tax_percent = parseFloat($($obj).val() || 0.0000);
    var $amount = parseFloat($('#sale_order_detail_amount_' + $row_id).val() || 0.0000);
    var $tax_amount = roundUpto($amount * $tax_percent / 100,2);

    $('#sale_order_detail_tax_amount_' + $row_id).val($tax_amount);
    calculateRowTotal($obj);
}

function calculateTaxPercent($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $tax_amount = parseFloat($($obj).val() || 0.0000);
    var $amount = parseFloat($('#sale_order_detail_amount_' + $row_id).val() || 0.0000);
    var $tax_percent = roundUpto($tax_amount / $amount * 100,2);

    $('#sale_order_detail_tax_percent_' + $row_id).val($tax_percent);
    calculateRowTotal($obj);
}

function calculateRowTotal($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');

    var $amount = parseFloat($('#sale_order_detail_amount_' + $row_id).val());

    var $tax_amount = parseFloat($('#sale_order_detail_tax_amount_' + $row_id).val());
    var $total_amount = roundUpto($amount + $tax_amount,2);

    $('#sale_order_detail_net_amount_' + $row_id).val($total_amount);

    calculateTotal();
}

function calculateTotal() {
    var $item_amount = 0;
    var $item_discount = 0;
    var $item_tax = 0;
    var $item_total = 0;
    var $total_quantity = 0;
    $('#tblSaleOrder tbody tr').each(function() {
        var $row_id = $(this).data('row_id');
        var $amount = $('#sale_order_detail_amount_' + $row_id).val();
        var $tax_amount = $('#sale_order_detail_tax_amount_' + $row_id).val();
        var $total_amount = $('#sale_order_detail_net_amount_' + $row_id).val();
        var $quantity = $('#sale_order_detail_qty_' + $row_id).val();

        $item_amount += parseFloat($amount);
        $item_tax += parseFloat($tax_amount);
        $item_total += parseFloat($total_amount);
        $total_quantity += parseFloat($quantity);
    })

    var $net_amount = $item_total ;

    $('#total_quantity').val(roundUpto($total_quantity,0));
    $('#item_amount').val(roundUpto($item_amount,2));
    $('#item_tax').val(roundUpto($item_tax,2));
    $('#item_total').val(roundUpto($item_total,2));
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