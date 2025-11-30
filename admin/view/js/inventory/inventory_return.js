/**
 * Created by Huzaifa on 9/18/15.
 */

//$(document).on('change','#partner_type_id', function() {
//    $partner_type_id = $(this).val();
//    $.ajax({
//        url: $UrlGetPartner,
//        dataType: 'json',
//        type: 'post',
//        data: 'partner_type_id=' + $partner_type_id+'&department_id='+$department_id,
//        mimeType:"multipart/form-data",
//        beforeSend: function() {
//            $('#department_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
//        },
//        complete: function() {
//            $('#loader').remove();
//        },
//        success: function(json) {
//            if(json.success)
//            {
//                $('#department_id').select2('destroy');
//                $('#department_id').html(json.html);
//                $('#department_id').select2({width:'100%'});
//            }
//            else {
//                alert(json.error);
//            }
//        },
//        error: function(xhr, ajaxOptions, thrownError) {
//            console.log(xhr.responseText);
//        }
//    })
//});



function fillGrid($obj) {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '<td>';
    $html += '<input type="hidden" name="inventory_return_details['+$grid_row+'][ref_document_type_id]" id="inventory_return_detail_ref_document_type_id_'+$grid_row+'" value="'+$obj['ref_document_type_id']+'" />';
    $html += '<input type="hidden" name="inventory_return_details['+$grid_row+'][ref_document_identity]" id="inventory_return_detail_ref_document_identity_'+$grid_row+'" value="'+$obj['document_identity']+'" />';
    $html += '<a target="_blank" href="'+$obj['href']+'">'+$obj['document_identity']+'</a>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="getProductByCode(this);" type="text" style="min-width: 100px;" class="form-control" name="inventory_return_details['+$grid_row+'][product_code]" id="inventory_return_detail_product_code_'+$grid_row+'" value="'+$obj['product_code']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<div class="input-group">';
    $html += '<select style="min-width: 100px;" onchange="getProductById(this);" class="form-control select2" id="inventory_return_detail_product_id_'+$grid_row+'" name="inventory_return_details['+$grid_row+'][product_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $products.forEach(function($product) {
        if($product['product_id'] == $obj['product_id']) {
            $html += '<option value="'+$product.product_id+'" selected="true">'+$product.name+'</option>';
        } else {
            $html += '<option value="'+$product.product_id+'">'+$product.name+'</option>';
        }
    });
    $html += '</select>';
    $html += '<span class="input-group-btn ">';
    $html += '<button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="inventory_return_detail_product_id_'+$grid_row+'" data-field="product_id">';
    $html += '<i class="fa fa-search"></i>';
    $html += '</button>';
    $html += '</span>';
    $html += '</div>';
    $html += '</td>';
    $html += '<td>';
    $html += '<select class="form-control select2" id="inventory_return_detail_warehouse_id_'+$grid_row+'" name="inventory_return_details['+$grid_row+'][warehouse_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $warehouses.forEach(function($warehouse) {
        if($warehouse['warehouse_id'] == $obj['warehouse_id']) {
            $html += '<option value="'+$warehouse.warehouse_id+'" selected="true">'+$warehouse.name+'</option>';
        } else {
            $html += '<option value="'+$warehouse.warehouse_id+'">'+$warehouse.name+'</option>';
        }
    });
    $html += '</select>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="inventory_return_details['+$grid_row+'][qty]" id="inventory_return_detail_qty_'+$grid_row+'" value="'+$obj['qty']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control" name="inventory_return_details['+$grid_row+'][unit]" id="inventory_return_detail_unit_'+$grid_row+'" value="'+$obj['unit']+'" readonly="true" />';
    $html += '<input type="hidden" class="form-control" name="inventory_return_details['+$grid_row+'][unit_id]" id="inventory_return_detail_unit_id_'+$grid_row+'" value="'+$obj['unit_id']+'" />';
    $html += '</td>';
    $html += '<td hidden="hidden">';
    $html += '<input type="hidden" class="form-control" name="inventory_return_details['+$grid_row+'][cog_rate]" id="inventory_return_detail_cog_rate_'+$grid_row+'" value="'+$obj['cog_rate']+'" />';
    $html += '</td>';
    $html += '<td hidden="hidden">';
    $html += '<input type="hidden" class="form-control" name="inventory_return_details['+$grid_row+'][cog_amount]" id="inventory_return_detail_cog_amount_'+$grid_row+'" value="'+$obj['cog_amount']+'" />';
    $html += '</td>';
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '</tr>';


    $('#tblGoodsReceived tbody').prepend($html);
    setFieldFormat();
    $('#inventory_return_detail_product_id_'+$grid_row).select2({width: '100%'});
    $('#inventory_return_detail_warehouse_id_'+$grid_row).select2({width: '100%'});
    $grid_row++;

}


$(document).on('click','#btnAddGrid', function() {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '<td>';
    $html += '<input type="hidden" name="inventory_return_details['+$grid_row+'][ref_document_type_id]" id="inventory_return_detail_ref_document_type_id_'+$grid_row+'" value="" />';
    $html += '<input type="hidden" name="inventory_return_details['+$grid_row+'][ref_document_identity]" id="inventory_return_detail_ref_document_identity_'+$grid_row+'" value="" />';
    $html += '&nbsp;';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="getProductByCode(this);" type="text" class="form-control" name="inventory_return_details['+$grid_row+'][product_code]" id="inventory_return_detail_product_code_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<div class="input-group">';
    $html += '<select onchange="getProductById(this);" class="form-control select2" id="inventory_return_detail_product_id_'+$grid_row+'" name="inventory_return_details['+$grid_row+'][product_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $products.forEach(function($product) {
        $html += '<option value="'+$product.product_id+'">'+$product.name+'</option>';
    });
    $html += '</select>';
    $html += '<span class="input-group-btn ">';
    $html += '<button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="inventory_return_detail_product_id_'+$grid_row+'" data-field="product_id">';
    $html += '<i class="fa fa-search"></i>';
    $html += '</button>';
    $html += '</span>';
    $html += '</div>';
    $html += '</td>';
    $html += '<td>';
    $html += '<select onchange="getWarehouseStock(this);" class="form-control select2" id="inventory_return_detail_warehouse_id_'+$grid_row+'" name="inventory_return_details['+$grid_row+'][warehouse_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $warehouses.forEach(function($warehouse) {
        $html += '<option value="'+$warehouse.warehouse_id+'">'+$warehouse.name+'</option>';
    });
    $html += '</select>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="inventory_return_details['+$grid_row+'][qty]" id="inventory_return_detail_qty_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="inventory_return_details['+$grid_row+'][unit]" id="inventory_return_detail_unit_'+$grid_row+'" value="" readonly="true" />';
    $html += '<input type="hidden" class="form-control" name="inventory_return_details['+$grid_row+'][unit_id]" id="inventory_return_detail_unit_id_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td hidden="hidden">';
    $html += '<input type="text" class="form-control fPDecimal" name="inventory_return_details['+$grid_row+'][stock_qty]" id="inventory_return_detail_stock_qty_'+$grid_row+'" value="" readonly="true" />';
    $html += '<input type="hidden" class="form-control fPDecimal" name="inventory_return_details['+$grid_row+'][cog_rate]" id="inventory_return_detail_cog_rate_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td hidden="hidden">';
    $html += '<input type="hidden" class="form-control fPDecimal" name="inventory_return_details['+$grid_row+'][cog_amount]" id="inventory_return_detail_cog_amount_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '</tr>';


    $('#tblGoodsReceived tbody').prepend($html);
    setFieldFormat();
    $('#inventory_return_detail_product_id_'+$grid_row).select2({width: '100%'});
    $('#inventory_return_detail_warehouse_id_'+$grid_row).select2({width: '100%'});
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
                $('#inventory_return_detail_product_code_'+$row_id).val(json.product['product_code']);
                $('#inventory_return_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#inventory_return_detail_unit_'+$row_id).val(json.product['unit']);
                $('#inventory_return_detail_stock_qty_'+$row_id).val(json.product['stock']['stock_qty']);
                $('#inventory_return_detail_cog_rate_'+$row_id).val(json.product['stock']['avg_stock_rate']);
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
                $('#inventory_return_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#inventory_return_detail_unit_'+$row_id).val(json.product['unit']);
                $('#inventory_return_detail_product_id_'+$row_id).select2('destroy');
                $('#inventory_return_detail_product_id_'+$row_id).val(json.product['product_id']);
                $('#inventory_return_detail_product_id_'+$row_id).select2({width:'100%'});
                $('#inventory_return_detail_stock_qty_'+$row_id).val(json.product['stock']['stock_qty']);
                $('#inventory_return_detail_cog_rate_'+$row_id).val(json.product['stock']['avg_stock_rate']);
            }
            else {
                alert(json.error);
                $('#inventory_return_detail_unit_id_'+$row_id).val('');
                $('#inventory_return_detail_unit_'+$row_id).val('');
                $('#inventory_return_detail_product_id_'+$row_id).select2('destroy');
                $('#inventory_return_detail_product_id_'+$row_id).val('');
                $('#inventory_return_detail_product_id_'+$row_id).select2({width:'100%'});
                $('#inventory_return_detail_stock_qty_'+$row_id).val('0');
                $('#inventory_return_detail_cog_rate_'+$row_id).val('0.00');
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
    $('#inventory_return_detail_product_code_'+$row_id).val($data['product_code']);
    $('#inventory_return_detail_unit_id_'+$row_id).val($data['unit_id']);
    $('#inventory_return_detail_unit_'+$row_id).val($data['unit']);
    $('#inventory_return_detail_stock_qty_'+$row_id).val($data['stock_qty']);
    $('#inventory_return_detail_cog_rate_'+$row_id).val($data['avg_stock_rate']);
    $('#inventory_return_detail_product_id_'+$row_id).select2('destroy');
    $('#inventory_return_detail_product_id_'+$row_id).val($data['product_id']);
    $('#inventory_return_detail_product_id_'+$row_id).select2({width: '100%'});
}

function getWarehouseStock($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $data = {
        warehouse_id: $($obj).val(),
        product_id: $('#inventory_return_detail_product_id_'+$row_id).val()
    };
    $.ajax({
        url: $UrlGetWarehouseStock,
        dataType: 'json',
        type: 'post',
        data: $data,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            //$('#department_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            //$('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#inventory_return_detail_stock_qty_' + $row_id).val(json.stock_qty);
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

function removeRow($obj) {
    //console.log($obj);
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_'+$row_id).remove();
    calculateTotal();
}

function calculateRowTotal($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');

    var $qty = parseFloat($('#inventory_return_detail_qty_' + $row_id).val());
    var $rate = parseFloat($('#inventory_return_detail_cog_rate_' + $row_id).val());

    var $amount = roundUpto($qty * $rate,2);
    $amount = $amount || 0.00;
    console.log($obj, $row_id, $qty, $rate, $amount);

    $('#inventory_return_detail_cog_amount_' + $row_id).val($amount);

    calculateTotal();
}

function calculateTotal() {
    var $total_qty = 0;
    var $total_amount = 0;
    $('#tblGoodsReceived tbody tr').each(function() {
        $row_id = $(this).data('row_id');
        $qty = $('#inventory_return_detail_qty_' + $row_id).val();
        $amount = $('#inventory_return_detail_cog_amount_' + $row_id).val();

        $total_qty += parseFloat($qty);
        $total_amount += parseFloat($amount);
    })

    console.log($total_qty, $total_amount);
    $('#total_qty').val($total_qty);
    $('#total_amount').val($total_amount);
}
$(document).on('change','#department_id', function() {
    $department_id = $(this).val();
    if($department_id != '') {
        $('#ref_document_type_id').select2('destroy');
        $('#ref_document_type_id').html('<option value="">&nbsp;</option><option value="31">'+$lang['delivery_requisition']+'</option>');
        $('#ref_document_type_id').select2({width: '100%'});
    } else {
        $('#ref_document_type_id').select2('destroy');
        $('#ref_document_type_id').html('');
        $('#ref_document_type_id').select2({width: '100%'});
    }
    $('#ref_document_identity').select2('destroy');
    $('#ref_document_identity').html('<option value="">&nbsp;</option>');
    $('#ref_document_identity').select2({width: '100%'});

});

$(document).on('change','#ref_document_type_id', function() {
    var $department_id = $('#department_id').val();
    var $ref_document_type_id = $(this).val();
    $.ajax({
        url: $UrlGetRefDocumentNo,
        dataType: 'json',
        type: 'post',
        data: 'ref_document_type_id=' + $ref_document_type_id +  '&department_id=' + $department_id,
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
                $('#ref_document_identity').select2({width: '100%'});
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

$(document).on('change','#ref_document_identity', function() {
    var $data = {
        department_id : $('#department_id').val(),
        ref_document_type_id : $('#ref_document_type_id').val(),
        ref_document_identity : $('#ref_document_identity').val()
    };

    var $details = [];
    $.ajax({
        url: $UrlGetRefDocument,
        dataType: 'json',
        type: 'post',
        data: $data,
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
                $('#tblGoodsReceived tbody').html('');

                $details = json['details'];
                for($i=0;$i<$details.length;$i++) {
                    fillGrid($details[$i]);
                }
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
