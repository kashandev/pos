/**
 * Created by Huzaifa on 9/18/15.
 */
$(document).on('change','#partner_type_id', function() {
    $partner_type_id = $(this).val();
    $.ajax({
        url: $UrlGetPartnerById,
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
            console.log(  )
            if(json.success)
            {
                $('#partner_id').select2('destroy');
                $('#partner_id').select2({width:'100%'});
                $('#partner_id').html('<option selected="selected" value="'+(json.partner['partner_id']??'')+'">'+(json.partner['name']??'')+'</option>');
                $('#partner_id').select2({
                width: '100%',
                ajax: {
                    url: $UrlGetPartnerJSON,
                    dataType: 'json',
                    type: 'post',
                    mimeType:"multipart/form-data",
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page
                        };
                    },
                    processResults: function (data, params) {
                        // parse the results into the format expected by Select2
                        // since we are using custom formatting functions we do not need to
                        // alter the remote JSON data, except to indicate that infinite
                        // scrolling can be used
                        params.page = params.page || 1;

                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 30) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
                minimumInputLength: 2,
                templateResult: formatRepo, // omitted for brevity, see the source of this page
                templateSelection: formatRepoSelection // omitted for brevity, see the source of this page                }
            });
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
function validateWarehouseStock($obj, $isEdit = false)
{
    var $row_id = $($obj).parent().parent().data('row_id');
    var $warehouse_id = $('#sale_return_detail_warehouse_id_'+$row_id).val();
    var $product_id = $('#sale_return_detail_product_id_'+$row_id).val();
    var $document_identity = '';
    if( $isEdit ) {
        $document_identity = $('[name="document_identity"]').val();
    }

    var $change_qty = parseFloat($('#sale_return_detail_qty_'+$row_id).val()) || 0;
    var $document_date = $('[name="document_date"]').val();

    $.ajax({
        url: $url_validate_stock,
        dataType: 'json',
        type: 'post',
        data: {
          'warehouse_id' : $warehouse_id,  
          'product_id' : $product_id,  
          'document_identity' : $document_identity,  
          'document_date' : $document_date
        },
        mimeType:"multipart/form-data",
        beforeSend: function() {
           $('#partner_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            console.log(json);
            if(json.success)
            {
                if( !$isEdit ) {
                    $('#sale_return_detail_stock_qty_'+$row_id).val(json.stock_qty);
                } else {
                    $('#sale_return_detail_stock_qty_'+$row_id).val(parseFloat(json.stock_qty));
                }
                $('#sale_return_detail_cog_rate_'+$row_id).val(json['avg_stock_rate']);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
}
$(document).on('change','#partner_id', function() {
    $partner_id = $(this).val();
    if($partner_id != '') {
        $options = '';
        $options += '<option value="">&nbsp;</option>';
        // $options += '<option value="2">'+$lang['sale_invoice']+'</option>';
        $options += '<option value="39">'+$lang['sale_invoice']+'</option>';
        $('#ref_document_type_id').select2('destroy');
        $('#ref_document_type_id').html($options);
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
    var $partner_type_id = $('#partner_type_id').val();
    var $partner_id = $('#partner_id').val();
    var $ref_document_type_id = $(this).val();
    $.ajax({
        url: $UrlGetRefDocumentNo,
        dataType: 'json',
        type: 'post',
        data: 'ref_document_type_id=' + $ref_document_type_id + '&partner_type_id=' + $partner_type_id + '&partner_id=' + $partner_id,
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
        partner_type_id : $('#partner_type_id').val(),
        partner_id : $('#partner_id').val(),
        ref_document_type_id : $('#ref_document_type_id').val(),
        ref_document_identity : $('#ref_document_identity').val(),
        document_currency_id : $('#document_currency_id').val()
    };
    var $details = [];
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

function fillGrid($obj) {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>';
    $html += '<td>';
    $html += '<input type="hidden" name="sale_return_details['+$grid_row+'][ref_document_type_id]" id="sale_return_detail_ref_document_type_id_'+$grid_row+'" value="'+$obj['document_type_id']+'" />';
    $html += '<input type="hidden" name="sale_return_details['+$grid_row+'][ref_document_identity]" id="sale_return_detail_ref_document_identity_'+$grid_row+'" value="'+$obj['document_identity']+'" />';
    $html += '<a target="_blank" href="'+$obj['href']+'">'+$obj['document_identity']+'</a>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="getProductByCode(this);" type="text" style="min-width: 100px;" class="form-control" name="sale_return_details['+$grid_row+'][product_code]" id="sale_return_detail_product_code_'+$grid_row+'" value="'+$obj['product_code']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<div class="input-group">';
    $html += '<select style="min-width: 100px;" onchange="getProductById(this);" class="form-control select2" id="sale_return_detail_product_id_'+$grid_row+'" name="sale_return_details['+$grid_row+'][product_id]" >';
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
    $html += '<button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="sale_return_detail_product_id_'+$grid_row+'" data-field="product_id">';
    $html += '<i class="fa fa-search"></i>';
    $html += '</button>';
    $html += '</span>';
    $html += '</div>';
    $html += '</td>';
    $html += '<td style="min-width: 300px;">';
    $html += '<input  type="text" style="min-width: 100px;" class="form-control" name="sale_return_details['+$grid_row+'][description]" id="sale_return_detail_description_'+$grid_row+'" value="'+$obj['description']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<select onchange="validateWarehouseStock(this);" class="form-control select2" id="sale_return_detail_warehouse_id_'+$grid_row+'" name="sale_return_details['+$grid_row+'][warehouse_id]" >';
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
    $html += '<input style="min-width: 100px;" type="text" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][stock_qty]" id="sale_return_detail_stock_qty_'+$grid_row+'" value="'+$obj['stock_qty']+'" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input style="min-width: 100px;" type="hidden" class="form-control fPDecimal" id="sale_return_detail_origional_qty_'+$grid_row+'" value="'+$obj['qty']+'" />';
    $html += '<input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][qty]" id="sale_return_detail_qty_'+$grid_row+'" value="'+$obj['qty']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control" name="sale_return_details['+$grid_row+'][unit]" id="sale_return_detail_unit_'+$grid_row+'" value="'+$obj['unit']+'" readonly="true" />';
    $html += '<input type="hidden" class="form-control" name="sale_return_details['+$grid_row+'][unit_id]" id="sale_return_detail_unit_id_'+$grid_row+'" value="'+$obj['unit_id']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][rate]" id="sale_return_detail_rate_'+$grid_row+'" value="'+$obj['rate']+'" />';
    $html += '<input type="hidden" class="form-control" name="sale_return_details['+$grid_row+'][cog_rate]" id="sale_return_detail_cog_rate_'+$grid_row+'" value="'+$obj['cog_rate']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][amount]" id="sale_return_detail_amount_'+$grid_row+'" value="'+$obj['amount']+'" readonly="true" />';
    $html += '<input type="hidden" class="form-control" name="sale_return_details['+$grid_row+'][cog_amount]" id="sale_return_detail_cog_amount_'+$grid_row+'" value="'+$obj['cog_amount']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateDiscountAmount(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][discount_percent]" id="sale_return_detail_discount_percent_'+$grid_row+'" value="'+$obj['discount_percent']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateDiscountPercent(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][discount_amount]" id="sale_return_detail_discount_amount_'+$grid_row+'" value="'+$obj['discount_amount']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][gross_amount]" id="sale_return_detail_gross_amount_'+$grid_row+'" value="'+$obj['gross_amount']+'" readonly="true"/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateTaxAmount(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][tax_percent]" id="sale_return_detail_tax_percent_'+$grid_row+'" value="'+$obj['tax_percent']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateTaxPercent(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][tax_amount]" id="sale_return_detail_tax_amount_'+$grid_row+'" value="'+$obj['tax_amount']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][total_amount]" id="sale_return_detail_total_amount_'+$grid_row+'" value="'+$obj['total_amount']+'" readonly="true" />';
    $html += '</td>';
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a><a id="btnAddGrid" title="Add" class="btn btn-xs btn-primary" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>';
    $html += '</tr>';


    $('#tblSaleReturn tbody').prepend($html);
    setInputFormat();
    $('#sale_return_detail_product_id_'+$grid_row).select2({width: '100%'});
    $('#sale_return_detail_warehouse_id_'+$grid_row).select2({width: '100%'});
    $('#sale_return_detail_rate_'+$grid_row).trigger('change');
    $grid_row++;
    
    calculateTotal();
}

$(document).on('click','#btnAddGrid', function() {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a><a id="btnAddGrid" title="Add" class="btn btn-xs btn-primary" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>';
    $html += '<td>';
    $html += '<input type="hidden" name="sale_return_details['+$grid_row+'][ref_document_type_id]" id="sale_return_detail_ref_document_type_id_'+$grid_row+'" value="" />';
    $html += '<input type="hidden" name="sale_return_details['+$grid_row+'][ref_document_identity]" id="sale_return_detail_ref_document_identity_'+$grid_row+'" value="" />';
    $html += '&nbsp;';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="getProductByCode(this);" type="text" style="min-width: 100px;" class="form-control" name="sale_return_details['+$grid_row+'][product_code]" id="sale_return_detail_product_code_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td style="min-width: 300px;">';
    $html += '<div class="input-group">';
    $html += '<select style="min-width: 100px;" onchange="getProductById(this);" class="form-control select2" id="sale_return_detail_product_id_'+$grid_row+'" name="sale_return_details['+$grid_row+'][product_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $products.forEach(function($product) {
        $html += '<option value="'+$product.product_id+'">'+$product.name+'</option>';
    });
    $html += '</select>';
    $html += '<span class="input-group-btn ">';
    $html += '<button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="sale_return_detail_product_id_'+$grid_row+'" data-field="product_id">';
    $html += '<i class="fa fa-search"></i>';
    $html += '</button>';
    $html += '</span>';
    $html += '</div>';
    $html += '</td>';
    $html += '<td style="min-width: 300px;">';
    $html += '<input  type="text" style="min-width: 100px;" class="form-control" name="sale_return_details['+$grid_row+'][description]" id="sale_return_detail_description_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<select onchange="validateWarehouseStock(this);" class="form-control select2" id="sale_return_detail_warehouse_id_'+$grid_row+'" name="sale_return_details['+$grid_row+'][warehouse_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $warehouses.forEach(function($warehouse) {
        $html += '<option value="'+$warehouse.warehouse_id+'">'+$warehouse.name+'</option>';
    });
    $html += '</select>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input style="min-width: 100px;" type="text" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][stock_qty]" id="sale_return_detail_stock_qty_'+$grid_row+'" value="0" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][qty]" id="sale_return_detail_qty_'+$grid_row+'" value="0" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control" name="sale_return_details['+$grid_row+'][unit]" id="sale_return_detail_unit_'+$grid_row+'" value="" readonly="true" />';
    $html += '<input type="hidden" class="form-control" name="sale_return_details['+$grid_row+'][unit_id]" id="sale_return_detail_unit_id_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][rate]" id="sale_return_detail_rate_'+$grid_row+'" value="0.00" />';
    $html += '<input type="hidden" class="form-control" name="sale_return_details['+$grid_row+'][cog_rate]" id="sale_return_detail_cog_rate_'+$grid_row+'" value="0.00" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][amount]" id="sale_return_detail_amount_'+$grid_row+'" value="0.00" readonly="true" />';
    $html += '<input type="hidden"class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][cog_amount]" id="sale_return_detail_cog_amount_'+$grid_row+'" value="0.00" readonly="true" />';
    $html += '</td>';
    $html += '<td >';
    $html += '<input onchange="calculateDiscountAmount(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][discount_percent]" id="sale_return_detail_discount_percent_'+$grid_row+'" value="0" />';
    $html += '</td>';
    $html += '<td >';
    $html += '<input onchange="calculateDiscountPercent(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][discount_amount]" id="sale_return_detail_discount_amount_'+$grid_row+'" value="0.00" />';
    $html += '</td>';
    $html += '<td >';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][gross_amount]" id="sale_return_detail_gross_amount_'+$grid_row+'" value="" readonly="true"/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateTaxAmount(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][tax_percent]" id="sale_return_detail_tax_percent_'+$grid_row+'" value="0" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateTaxPercent(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][tax_amount]" id="sale_return_detail_tax_amount_'+$grid_row+'" value="0.00" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="sale_return_details['+$grid_row+'][total_amount]" id="sale_return_detail_total_amount_'+$grid_row+'" value="" readonly="true" />';
    $html += '</td>';
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a><a id="btnAddGrid" title="Add" class="btn btn-xs btn-primary" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>';
    $html += '</tr>';


    $('#tblSaleReturn tbody').prepend($html);
    // setFieldFormat();
    $('#sale_return_detail_product_id_'+$grid_row).select2({width: '100%'});
    $('#sale_return_detail_warehouse_id_'+$grid_row).select2({width: '100%'});
    $grid_row++;
});

function getProductById($obj) {
    $product_id = $($obj).val();
    $partner_id = $('#partner_id').val();
    var $row_id = $($obj).parent().parent().parent().data('row_id');
    $.ajax({
        url: $UrlGetProductById,
        dataType: 'json',
        type: 'post',
        data: 'product_id=' + $product_id+'&partner_id=' + $partner_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-search').addClass('fa-refresh fa-spin');
        },
        complete: function() {
            $('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-refresh').removeClass('fa-spin').addClass('fa-search');
        },
        success: function(json) {
            if(json.success) {
                $('#sale_return_detail_description_'+$row_id).val(json.product['name']);
                $('#sale_return_detail_product_code_'+$row_id).val(json.product['product_code']);
                $('#sale_return_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#sale_return_detail_unit_'+$row_id).val(json.product['unit']);
                $('#sale_return_detail_cubic_meter_'+$row_id).val(json.product['cubic_meter']);
                $('#sale_return_detail_cubic_feet_'+$row_id).val(json.product['cubic_feet']);
                $('#sale_return_detail_rate_'+$row_id).val(json.product['sale_price']);
                $('#sale_return_detail_cog_rate_'+$row_id).val(json.product['stock']['avg_stock_rate']);
                $('#sale_return_detail_stock_qty_'+$row_id).val(json.product['stock']['stock_qty']);
                $('#sale_return_detail_rate_'+$row_id).trigger('change');
                $('#sale_return_detail_discount_percent_'+$row_id).trigger('change');
                $('#sale_return_detail_tax_percent_'+$row_id).trigger('change');

                $('#last_rate').val(json.customer_rate);

//                console.log(json.customer_rate);

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
    $partner_id = $('#partner_id').val();

    var $row_id = $($obj).parent().parent().data('row_id');
    $.ajax({
        url: $UrlGetProductByCode,
        dataType: 'json',
        type: 'post',
        data: 'product_code=' + $product_code+'&partner_id=' + $partner_id,
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
                $('#sale_return_detail_description_'+$row_id).val(json.product['name']);
                $('#sale_return_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#sale_return_detail_unit_'+$row_id).val(json.product['unit']);
                $('#sale_return_detail_product_id_'+$row_id).select2('destroy');
                $('#sale_return_detail_product_id_'+$row_id).val(json.product['product_id']);
                $('#sale_return_detail_product_id_'+$row_id).select2({width:'100%'});
                $('#sale_return_detail_rate_'+$row_id).val(json.product['cost_price']);
                $('#sale_return_detail_cubic_meter_'+$row_id).val(json.product['cubic_meter']);
                $('#sale_return_detail_cubic_feet_'+$row_id).val(json.product['cubic_feet']);
                $('#sale_return_detail_stock_qty_'+$row_id).val(json.product['stock']['stock_qty']);

                $('#sale_return_detail_rate_'+$row_id).trigger('change');
                $('#sale_return_detail_discount_percent_'+$row_id).trigger('change');
                $('#sale_return_detail_tax_percent_'+$row_id).trigger('change');
                $('#last_rate').val(json.customer_rate);
            } else {
                alert(json.error);
                $('#sale_return_detail_description_'+$row_id).val('');
                $('#sale_return_detail_unit_id_'+$row_id).val('');
                $('#sale_return_detail_unit_'+$row_id).val('');
                $('#sale_return_detail_stock_qty_'+$row_id).val(0);
                $('#sale_return_detail_product_id_'+$row_id).select2('destroy');
                $('#sale_return_detail_product_id_'+$row_id).val('');
                $('#sale_return_detail_product_id_'+$row_id).select2({width:'100%'});
                $('#sale_return_detail_rate_'+$row_id).val('0.00');
                $('#last_rate').val('0.00');
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
    $('#sale_return_detail_description_'+$row_id).val($data['name']);
    $('#sale_return_detail_product_code_'+$row_id).val($data['product_code']);
    $('#sale_return_detail_unit_id_'+$row_id).val($data['unit_id']);
    $('#sale_return_detail_unit_'+$row_id).val($data['unit']);
    $('#sale_return_detail_rate_'+$row_id).val($data['cost_price']);
    $('#sale_return_detail_product_id_'+$row_id).select2('destroy');
    $('#sale_return_detail_product_id_'+$row_id).val($data['product_id']);
    $('#sale_return_detail_product_id_'+$row_id).select2({width: '100%'});
//    $('#last_rate').val($data['customer_rate']);

}

function removeRow($obj) {
    //console.log($obj);
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_'+$row_id).remove();
    calculateTotal();
}

function calculateRowTotal($obj) {

    var $row_id = $($obj).parent().parent().data('row_id');
    var $origional_qty = parseFloat($('#sale_return_detail_origional_qty_' + $row_id).val());
    var $qty = parseFloat($('#sale_return_detail_qty_' + $row_id).val());
    var $rate = parseFloat($('#sale_return_detail_rate_' + $row_id).val());
    var $cog_rate = parseFloat($('#sale_return_detail_cog_rate_' + $row_id).val());

    if($qty > $origional_qty)
    {
        // save btn disable
        $('.btnsave').attr('disabled',true);
        alert('Quantity of Sale return item cannot be greater than quantity of Sale Invoice');
        $('#sale_return_detail_cog_amount_' + $row_id).val('');
        $('#sale_return_detail_amount_' + $row_id).val('');
        $('#sale_return_detail_discount_amount_' + $row_id).val('');
        $('#sale_return_detail_gross_amount_' + $row_id).val('');
        $('#sale_return_detail_tax_amount_' + $row_id).val('');
        $('#sale_return_detail_total_amount_' + $row_id).val('');
    }
    else
    {
        var $amount = $qty * $rate;
        var $cog_amount = $qty * $cog_rate;
        $amount = roundUpto($amount,2);
        $cog_amount = roundUpto($cog_amount,2);

        var $dis_percent = parseFloat($('#sale_return_detail_discount_percent_'+ $row_id).val() || 0.0000);
        var $discount_amount = roundUpto($amount * $dis_percent / 100,2);

        var $tax_percent = parseFloat($('#sale_return_detail_tax_percent_'+ $row_id).val() || 0.0000);
        var $tax_amount = roundUpto($amount * $tax_percent / 100,2);


        var $gross_amount = roundUpto($amount - $discount_amount,2);

    //    var $tax_amount = parseFloat($('#sale_return_detail_tax_amount_' + $row_id).val());
        var $total_amount = roundUpto($gross_amount + $tax_amount,2);

        $('#sale_return_detail_cog_amount_' + $row_id).val($cog_amount);
        $('#sale_return_detail_amount_' + $row_id).val($amount);
        $('#sale_return_detail_discount_amount_' + $row_id).val($discount_amount);
        $('#sale_return_detail_gross_amount_' + $row_id).val($gross_amount);
        $('#sale_return_detail_tax_amount_' + $row_id).val($tax_amount);
        $('#sale_return_detail_total_amount_' + $row_id).val($total_amount);
        $('.btnsave').removeAttr('disabled');
        calculateTotal();
    }

    
}

function calculateTotal() {
    var $item_amount = 0;
    var $item_discount = 0;
    var $item_tax = 0;
    var $item_total = 0;
    $('#tblSaleReturn tbody tr').each(function() {
        $row_id = $(this).data('row_id');
        $amount = $('#sale_return_detail_amount_' + $row_id).val();
        $discount_amount = $('#sale_return_detail_discount_amount_' + $row_id).val();
        $tax_amount = $('#sale_return_detail_tax_amount_' + $row_id).val();
        $total_amount = $('#sale_return_detail_total_amount_' + $row_id).val();

        $item_amount += parseFloat($amount);
        $item_discount += parseFloat($discount_amount);
        $item_tax += parseFloat($tax_amount);
        $item_total += parseFloat($total_amount);
    })

    var $deduction_amount = $('#deduction_amount').val() || 0.00;
    var $net_amount = $item_total - $deduction_amount;
    var $cash_returned = $('#cash_returned').val() || 0.00;
    var $balance_amount = $net_amount - $cash_returned;

    $('#item_amount').val(roundUpto($item_amount,2));
    $('#item_discount').val(roundUpto($item_discount,2));
    $('#item_tax').val(roundUpto($item_tax,2));
    $('#item_total').val(roundUpto($item_total,2));
    $('#deduction_amount').val(roundUpto($deduction_amount,2));
    $('#net_amount').val(roundUpto($net_amount,2));
    $('#cash_returned').val(roundUpto($cash_returned,2));
    $('#balance_amount').val(roundUpto($balance_amount,2));

}

function calculateDiscountAmount($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $discount_percent = parseFloat($($obj).val() || 0.0000);
    var $amount = parseFloat($('#sale_return_detail_amount_' + $row_id).val() || 0.0000);
    var $discount_amount = roundUpto($amount * $discount_percent / 100,2);
    $('#sale_return_detail_discount_amount_' + $row_id).val($discount_amount);
    calculateRowTotal($obj);
}

function calculateDiscountPercent($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $discount_amount = parseFloat($($obj).val() || 0.0000);
    var $amount = parseFloat($('#sale_return_detail_amount_' + $row_id).val() || 0.0000);
    var $discount_percent = roundUpto($discount_amount / $amount * 100,2);

    $('#sale_return_detail_discount_percent_' + $row_id).val($discount_percent);
    calculateRowTotal($obj);
}

function calculateTaxAmount($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $tax_percent = parseFloat($($obj).val() || 0.0000);
    var $amount = parseFloat($('#sale_return_detail_amount_' + $row_id).val() || 0.0000);
    var $tax_amount = roundUpto($amount * $tax_percent / 100,2);

    $('#sale_return_detail_tax_amount_' + $row_id).val($tax_amount);
    calculateRowTotal($obj);
}

function calculateTaxPercent($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $tax_amount = parseFloat($($obj).val() || 0.0000);
    var $amount = parseFloat($('#sale_return_detail_amount_' + $row_id).val() || 0.0000);
    var $tax_percent = roundUpto($tax_amount / $amount * 100,2);

    $('#sale_return_detail_tax_percent_' + $row_id).val($tax_percent);
    calculateRowTotal($obj);
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