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
    $html += '<td><a title="Remove" class="btnRemoveGrid btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
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
    $html += '<input type="hidden" class="form-control" name="sale_order_details['+$grid_row+'][unit_id]" id="sale_order_detail_unit_id_'+$grid_row+'" value="'+$obj['unit_id']+'" readonly/>';
    $html += '<input type="text" class="form-control" name="sale_order_details['+$grid_row+'][unit]" id="sale_order_detail_unit_'+$grid_row+'" value="'+$obj['unit']+'" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" type="text" class="form-control fPDecimal" name="sale_order_details['+$grid_row+'][qty]" id="sale_order_detail_qty_'+$grid_row+'" value="0" />';
    $html += '<input type="hidden" class="form-control " name="sale_order_details['+$grid_row+'][utilized_qty]" id="sale_order_detail_utilized_qty_'+$grid_row+'" value="'+$obj['balanced_qty']+'" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" type="text" class="form-control fPDecimal" name="sale_order_details['+$grid_row+'][rate]" id="sale_order_detail_rate_'+$grid_row+'" value="'+$obj['rate']+'" readonly />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control fPDecimal" name="sale_order_details['+$grid_row+'][amount]" id="sale_order_detail_amount_'+$grid_row+'" value="'+$obj['amount']+'" readonly="true" />';
    $html += '</td>';
    $html += '<td style="width: 3%;"></td>';
    $html += '</tr>';

    $('#tblSaleOrder tbody').append($html);
    setFieldFormat();
    $grid_row++;

}




$(document).on('click','#btnAddGrid', function() {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
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
    $html += '<input type="text" class="form-control" name="sale_order_details['+$grid_row+'][unit]" id="sale_order_detail_unit_'+$grid_row+'" value="" readonly="true" />';
    $html += '<input type="hidden" class="form-control" name="sale_order_details['+$grid_row+'][unit_id]" id="sale_order_detail_unit_id_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" type="text" class="form-control fPDecimal" name="sale_order_details['+$grid_row+'][qty]" id="sale_order_detail_qty_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" type="text" class="form-control fPDecimal" name="sale_order_details['+$grid_row+'][rate]" id="sale_order_detail_rate_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control fPDecimal" name="sale_order_details['+$grid_row+'][amount]" id="sale_order_detail_amount_'+$grid_row+'" value="" readonly="true" />';
    $html += '</td>';
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '</tr>';

//
//    $('#tblSaleOrder tbody').prepend($html);
    $('#tblSaleOrder tbody').append($html);
    setFieldFormat();
    $('#sale_order_detail_product_id_'+$grid_row).select2({width: '100%'});
    $('#sale_order_detail_warehouse_id_'+$grid_row).select2({width: '100%'});
    $('#sale_order_detail_product_code_'+$grid_row).focus();

    $grid_row++;

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
                $('#sale_order_detail_product_code_'+$row_id).val(json.product['product_code']);
                $('#sale_order_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#sale_order_detail_unit_'+$row_id).val(json.product['unit']);
                $('#sale_order_detail_rate_'+$row_id).val(json.product['cost_price']);
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
                $('#sale_order_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#sale_order_detail_unit_'+$row_id).val(json.product['unit']);
                $('#sale_order_detail_product_id_'+$row_id).select2('destroy');
                $('#sale_order_detail_product_id_'+$row_id).val(json.product['product_id']);
                $('#sale_order_detail_product_id_'+$row_id).select2({width:'100%'});
                $('#sale_order_detail_rate_'+$row_id).val(json.product['cost_price']);
            }
            else {
                alert(json.error);
                $('#sale_order_detail_unit_id_'+$row_id).val('');
                $('#sale_order_detail_unit_'+$row_id).val('');
                $('#sale_order_detail_product_id_'+$row_id).select2('destroy');
                $('#sale_order_detail_product_id_'+$row_id).val('');
                $('#sale_order_detail_product_id_'+$row_id).select2({width:'100%'});
                $('#sale_order_detail_rate_'+$row_id).val('0.00');
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

function calculateRowTotal($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');

    var $qty = parseFloat($('#sale_order_detail_qty_' + $row_id).val());
    var $rate = parseFloat($('#sale_order_detail_rate_' + $row_id).val());

    var $amount = roundUpto($qty * $rate,2);
    $amount = $amount || 0

    $('#sale_order_detail_amount_' + $row_id).val($amount);

    calculateTotal();
}

function calculateTotal() {
    var $net_amount = 0;
    $('#tblSaleOrder tbody tr').each(function() {
        $row_id = $(this).data('row_id');
        $amount = $('#sale_order_detail_amount_' + $row_id).val();

        $net_amount += parseFloat($amount);
    })

    $('#net_amount').val($net_amount);
}
