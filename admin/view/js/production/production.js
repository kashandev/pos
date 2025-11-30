/**
 * Created by Huzaifa on 9/18/15.
 */

$(document).on('click','#btnAddGrid', function() {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '<td>';
    $html += '<input onchange="getProductByCode(this);" type="text" class="form-control" name="production_details['+$grid_row+'][product_code]" id="production_detail_product_code_'+$grid_row+'" value="" />';
    $html += '</td>'
    $html += '<td>';
    $html += '<div class="input-group">';
    $html += '<select onchange="getProductById(this);" class="form-control select2" id="production_detail_product_id_'+$grid_row+'" name="production_details['+$grid_row+'][product_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $products.forEach(function($product) {
        $html += '<option value="'+$product.product_id+'">'+$product.name+'</option>';
    })
    $html += '</select>';
    $html += '<span class="input-group-btn ">';
    $html += '<button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="production_detail_product_id_'+$grid_row+'" data-field="product_id">';
    $html += '<i class="fa fa-search"></i>';
    $html += '</button>';
    $html += '</span>';
    $html += '</div>';
    $html += '</td>'
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="production_details['+$grid_row+'][unit]" id="production_detail_unit_'+$grid_row+'" value="" readonly="true" />';
    $html += '<input type="hidden" class="form-control" name="production_details['+$grid_row+'][unit_id]" id="production_detail_unit_id_'+$grid_row+'" value="" />';
    $html += '</td>'
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" type="text" class="form-control fPDecimal" name="production_details['+$grid_row+'][qty]" id="production_detail_qty_'+$grid_row+'" value="" />';
    $html += '</td>'
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '</tr>';


    $('#tblProductionDetail tbody').append($html);
    $('#production_detail_product_id_'+$grid_row).select2({width: '100%'});
    $grid_row++;
});

function getMasterProductByCode($obj) {
    $product_code = $($obj).val();
    $.ajax({
        url: $UrlGetProductByCode,
        dataType: 'json',
        type: 'post',
        data: 'product_code=' + $product_code,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#QSearchProduct i').removeClass('fa-search').addClass('fa-refresh fa-spin');
        },
        complete: function() {
            $('#QSearchProduct i').removeClass('fa-refresh').removeClass('fa-spin').addClass('fa-search');
        },
        success: function(json) {
            if(json.success)
            {
                $('#unit_id').val(json.product['unit_id']);
                $('#unit').val(json.product['unit']);
                $('#product_id').select2('destroy');
                $('#product_id').val(json.product['product_id']);
                $('#product_id').select2({width:'100%'});
                getBOM();
            }
            else {
                alert(json.error);
                $('#unit_id').val('');
                $('#unit').val('');
                $('#product_id').select2('destroy');
                $('#product_id').val('');
                $('#product_id').select2({width:'100%'});
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
}

function getMasterProductById($obj) {
    $product_id = $($obj).val();
    var $row_id = $($obj).parent().parent().parent().data('row_id');
    $.ajax({
        url: $UrlGetProductById,
        dataType: 'json',
        type: 'post',
        data: 'product_id=' + $product_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#grid_row'+' .QSearchProduct i').removeClass('fa-search').addClass('fa-refresh fa-spin');
        },
        complete: function() {
            $('#grid_row'+' .QSearchProduct i').removeClass('fa-refresh').removeClass('fa-spin').addClass('fa-search');
        },
        success: function(json) {
            if(json.success)
            {
                $('#product_code').val(json.product['product_code']);
                $('#unit_id').val(json.product['unit_id']);
                $('#unit').val(json.product['unit']);
                getBOM();
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

function setMasterProduct($obj) {
    var $data = $($obj).data();
    $('#_modal').modal('hide');
    $('#product_code').val($data['product_code']);
    $('#unit_id').val($data['unit_id']);
    $('#unit').val($data['unit']);
    $('#product_id').select2('destroy');
    $('#product_id').val($data['product_id']);
    $('#product_id').select2({width: '100%'});

    getBOM();
}

function getBOM() {
    var $product_id = $('#product_id').val();
    $.ajax({
        url: $UrlGetBOM,
        dataType: 'json',
        type: 'post',
        data: 'product_id=' + $product_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#QSearchProduct i').removeClass('fa-search').addClass('fa-refresh fa-spin');
        },
        complete: function() {
            $('#QSearchProduct i').removeClass('fa-refresh').removeClass('fa-spin').addClass('fa-search');
        },
        success: function(json) {
            if(json.success)
            {
                $('#tblProductionDetail tbody').html(json.html);
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

function calculateQuantity($obj) {
    var $master_expected_quantity = $('#expected_quantity').val();
    $('#actual_quantity').val($master_expected_quantity);
    $('#tblProductionDetail tbody tr').each(function() {
        var $row_id = $(this).data('row_id');
        var $unit_quantity = $('#production_detail_unit_quantity_' + $row_id).val();
        var $expected_quantity = $unit_quantity * $master_expected_quantity;
        var $cog_rate = $('#production_detail_cog_rate_' + $row_id).val();
        var $actual_quantity = $expected_quantity;
        var $cog_amount = $actual_quantity * $cog_rate;

        $('#production_detail_expected_quantity_'+ $row_id).val($expected_quantity);
//        $('#production_detail_actual_quantity_'+ $row_id).val($actual_quantity);
        $('#production_detail_cog_amount_'+ $row_id).val($cog_amount);
    })

    calculateTotal();
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
                $('#production_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#production_detail_unit_'+$row_id).val(json.product['unit']);
                $('#production_detail_product_id_'+$row_id).select2('destroy');
                $('#production_detail_product_id_'+$row_id).val(json.product['product_id']);
                $('#production_detail_product_id_'+$row_id).select2({width:'100%'});
                $('#production_detail_rate_'+$row_id).val(json.product['cost_price']);
            }
            else {
                alert(json.error);
                $('#production_detail_unit_id_'+$row_id).val('');
                $('#production_detail_unit_'+$row_id).val('');
                $('#production_detail_product_id_'+$row_id).select2('destroy');
                $('#production_detail_product_id_'+$row_id).val('');
                $('#production_detail_product_id_'+$row_id).select2({width:'100%'});
                $('#production_detail_rate_'+$row_id).val('0.00');
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
}

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
                $('#production_detail_product_code_'+$row_id).val(json.product['product_code']);
                $('#production_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#production_detail_unit_'+$row_id).val(json.product['unit']);
                $('#production_detail_rate_'+$row_id).val(json.product['cost_price']);
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

function setProductInformation($obj) {
    var $data = $($obj).data();
    var $row_id = $('#'+$data['element']).parent().parent().parent().data('row_id');
    $('#_modal').modal('hide');
    $('#production_detail_product_code_'+$row_id).val($data['product_code']);
    $('#production_detail_unit_id_'+$row_id).val($data['unit_id']);
    $('#production_detail_unit_'+$row_id).val($data['unit']);
    $('#production_detail_rate_'+$row_id).val($data['cost_price']);
    $('#production_detail_product_id_'+$row_id).select2('destroy');
    $('#production_detail_product_id_'+$row_id).val($data['product_id']);
    $('#production_detail_product_id_'+$row_id).select2({width: '100%'});
}

function removeRow($obj) {
    //console.log($obj);
    var $row_id = $($obj).parent().parent().parent().data('row_id');
    console.log($row_id);
    $('#grid_row_'+$row_id).remove();
    calculateTotal();
}

function calculateRowTotal($row_id) {
    var $actual_quantity = parseFloat($('#production_detail_actual_quantity_' + $row_id).val());
    var $cog_rate = parseFloat($('#production_detail_cog_rate_' + $row_id).val());
    var $cog_amount = ($actual_quantity * $cog_rate) || 0.00;
    var $cog_amount = roundUpto($cog_amount,2);
    $('#production_detail_cog_amount_' + $row_id).val($cog_amount);
    calculateTotal();
}

function calculateTotal() {
    var $total_amount = 0;
    $('#tblProductionDetail tbody tr').each(function() {
        $row_id = $(this).data('row_id');
        var $amount = $('#production_detail_cog_amount_' + $row_id).val();
        // alert($amount);

        $total_amount += parseFloat($amount);
    })
    var $actual_quantity = $('#actual_quantity').val();
    var $rate = parseFloat($total_amount) / parseFloat($actual_quantity);

    $('#amount').val($total_amount);
    $('#rate').val(roundUpto($rate,2));
}

function calculateRate() {
    var $amount = $('#amount').val();
    var $actual_quantity = $('#actual_quantity').val();

    var $rate = roundUpto(parseFloat($amount)/parseFloat($actual_quantity),2);
    $('#rate').val($rate);
}

function validateForm() {
    var $bolError = false;
    $('#tblProductionDetail tbody tr').each(function() {
        var $id = $(this).data('row_id');
        var $warehouse_id = $('#production_detail_warehouse_id_' + $id).val();
        var $restrict_out_of_stock = $('#restrict_out_of_stock').val();

        // alert($id);
        // alert($warehouse_id);
        // alert($restrict_out_of_stock);
        $('#production_detail_warehouse_id_' + $id).parent().children('label').remove();
        if($warehouse_id == '') {
            $bolError = true;
            $('#production_detail_warehouse_id_' + $id).parent().append('<label for="production_detail_warehouse_id_' + $id + '" class="error">This Field is required.</label>')
        }

        $('#production_detail_actual_quantity_' + $id).parent().children('label').remove();
        if($restrict_out_of_stock == 1) {
            var $actual_quantity = parseFloat($('#production_detail_actual_quantity_' + $id).val());
            var $stock_quantity = parseFloat($('#production_detail_stock_quantity_' + $id).val());
            if($actual_quantity > $stock_quantity) {
                $bolError = true;

                $('#production_detail_actual_quantity_' + $id).parent().append('<label for="production_detail_actual_quantity_' + $id + '" class="error">Available Stock is '+$stock_quantity+'</label>');
            }
        }
    })
    if($bolError == false) {
        $('#form').submit();
    }
}