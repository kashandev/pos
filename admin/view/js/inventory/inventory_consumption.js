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

$(document).on('click','#btnAddGrid', function() {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td><a onclick="removeRow(this);" title="Remove" ssclass="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '<td>';
    $html += '<input onchange="getProductByCode(this);" type="text" class="form-control" name="inventory_consumption_details['+$grid_row+'][product_code]" id="inventory_consumption_detail_product_code_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<div class="input-group">';
    $html += '<select onchange="getProductById(this);" class="form-control select2" id="inventory_consumption_detail_product_id_'+$grid_row+'" name="inventory_consumption_details['+$grid_row+'][product_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $products.forEach(function($product) {
        $html += '<option value="'+$product.product_id+'">'+$product.name+'</option>';
    });
    $html += '</select>';
    $html += '<span class="input-group-btn ">';
    $html += '<button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="inventory_consumption_detail_product_id_'+$grid_row+'" data-field="product_id">';
    $html += '<i class="fa fa-search"></i>';
    $html += '</button>';
    $html += '</span>';
    $html += '</div>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" type="text" class="form-control fPDecimal" name="inventory_consumption_details['+$grid_row+'][qty]" id="inventory_consumption_detail_qty_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="inventory_consumption_details['+$grid_row+'][unit]" id="inventory_consumption_detail_unit_'+$grid_row+'" value="" readonly="true" />';
    $html += '<input type="hidden" class="form-control" name="inventory_consumption_details['+$grid_row+'][unit_id]" id="inventory_consumption_detail_unit_id_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td hidden="hidden">';
    $html += '<input onchange="calculateRowTotal(this);" type="hidden" class="form-control fPDecimal" name="inventory_consumption_details['+$grid_row+'][rate]" id="inventory_consumption_detail_rate_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td hidden="hidden">';
    $html += '<input type="hidden" class="form-control fPDecimal" name="inventory_consumption_details['+$grid_row+'][amount]" id="inventory_consumption_detail_amount_'+$grid_row+'" value="" readonly="true" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control " name="inventory_consumption_details['+$grid_row+'][remarks]" id="inventory_consumption_detail_remarks_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '</tr>';

    $('#tblInventoryConsumption tbody').prepend($html);
    setFieldFormat();
    $('#inventory_consumption_detail_product_id_'+$grid_row).select2({width: '100%'});
    $('#inventory_consumption_detail_warehouse_id_'+$grid_row).select2({width: '100%'});
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
                $('#inventory_consumption_detail_product_code_'+$row_id).val(json.product['product_code']);
                $('#inventory_consumption_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#inventory_consumption_detail_unit_'+$row_id).val(json.product['unit']);
                $('#inventory_consumption_detail_rate_'+$row_id).val(json.product['cost_price']);
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
                $('#inventory_consumption_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#inventory_consumption_detail_unit_'+$row_id).val(json.product['unit']);
                $('#inventory_consumption_detail_product_id_'+$row_id).select2('destroy');
                $('#inventory_consumption_detail_product_id_'+$row_id).val(json.product['product_id']);
                $('#inventory_consumption_detail_product_id_'+$row_id).select2({width:'100%'});
                $('#inventory_consumption_detail_rate_'+$row_id).val(json.product['cost_price']);
            }
            else {
                alert(json.error);
                $('#inventory_consumption_detail_unit_id_'+$row_id).val('');
                $('#inventory_consumption_detail_unit_'+$row_id).val('');
                $('#inventory_consumption_detail_product_id_'+$row_id).select2('destroy');
                $('#inventory_consumption_detail_product_id_'+$row_id).val('');
                $('#inventory_consumption_detail_product_id_'+$row_id).select2({width:'100%'});
                $('#inventory_consumption_detail_rate_'+$row_id).val('0.00');
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
    $('#inventory_consumption_detail_product_code_'+$row_id).val($data['product_code']);
    $('#inventory_consumption_detail_unit_id_'+$row_id).val($data['unit_id']);
    $('#inventory_consumption_detail_unit_'+$row_id).val($data['unit']);
    $('#inventory_consumption_detail_rate_'+$row_id).val($data['cost_price']);
    $('#inventory_consumption_detail_product_id_'+$row_id).select2('destroy');
    $('#inventory_consumption_detail_product_id_'+$row_id).val($data['product_id']);
    $('#inventory_consumption_detail_product_id_'+$row_id).select2({width: '100%'});
}


function removeRow($obj) {
    //console.log($obj);
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_'+$row_id).remove();
    calculateTotal();
}

function calculateRowTotal($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');

    var $qty = parseFloat($('#inventory_consumption_detail_qty_' + $row_id).val());
    var $rate = parseFloat($('#inventory_consumption_detail_rate_' + $row_id).val());

    var $amount = roundUpto($qty * $rate,2);
    $amount = $amount || 0

    $('#inventory_consumption_detail_amount_' + $row_id).val($amount);

    calculateTotal();
}

function calculateTotal() {
    var $net_amount = 0;
    $('#tblInventoryConsumption tbody tr').each(function() {
        $row_id = $(this).data('row_id');
        $amount = $('#inventory_consumption_detail_amount_' + $row_id).val();

        $net_amount += parseFloat($amount);
    })

    $('#net_amount').val($net_amount);
}
