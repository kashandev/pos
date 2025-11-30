/**
 * Created by Huzaifa on 9/18/15.
 */

$(document).on('click','.btnAddGrid', function() {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td>';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '<a  class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="getProductByCode(this);" type="text" style="min-width: 100px;" class="required form-control" name="opening_stock_details['+$grid_row+'][product_code]" id="opening_stock_detail_product_code_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<div class="input-group">';
    $html += '<select style="min-width: 300px;" onchange="getProductById(this);" class="required form-control select2" id="opening_stock_detail_product_id_'+$grid_row+'" name="opening_stock_details['+$grid_row+'][product_id]" >';
    $html += '<option value="">&nbsp;</option>';
   // $products.forEach(function($product) {
   //     $html += '<option value="'+$product.product_id+'">'+$product.name+'</option>';
   // });
    $html += '</select>';
    $html += '<span class="input-group-btn ">';
    $html += '<button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="opening_stock_detail_product_id_'+$grid_row+'" data-field="product_id">';
    $html += '<i class="fa fa-search"></i>';
    $html += '</button>';
    $html += '</span>';
    $html += '</div>';
    $html += '<label for="opening_stock_detail_product_id_'+$grid_row+'" class="error"></label>    ';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateAmount(this);" style="min-width: 100px;" type="text" class="form-control fDecimal" name="opening_stock_details['+$grid_row+'][qty]" id="opening_stock_detail_qty_'+$grid_row+'" value="1" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="hidden" class="form-control" name="opening_stock_details['+$grid_row+'][unit_id]" id="opening_stock_detail_unit_id_'+$grid_row+'" value="" />';
    $html += '<input type="text" readonly class="form-control" name="opening_stock_details['+$grid_row+'][unit]" id="opening_stock_detail_unit_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateAmount(this);" style="min-width: 100px;" type="text" class="form-control fDecimal" name="opening_stock_details['+$grid_row+'][rate]" id="opening_stock_detail_rate_'+$grid_row+'" value="0.00" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fDecimal" name="opening_stock_details['+$grid_row+'][amount]" id="opening_stock_detail_amount_'+$grid_row+'" value="0.00" readonly="true" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '<a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '</td>';
    $html += '</tr>';

    if($(this).parent().parent().data('row_id')=='H') {
        $('#tblOpeningStockDetail tbody').prepend($html);
    } else {
        $(this).parent().parent().after($html);
    }
    setFieldFormat();
    //$('#opening_stock_detail_product_id_'+$grid_row).select2({width: '100%'});
    $('#opening_stock_detail_product_id_'+$grid_row).select2({
        width: '100%',
        ajax: {
            url: $UrlGetProductJSON,
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
    calculateTotal();

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
                $('#opening_stock_detail_product_id_'+$row_id).select2('destroy');
                $('#opening_stock_detail_product_code_'+$row_id).val(json.product['product_code']);
                $('#opening_stock_detail_unit_'+$row_id).val(json.product['unit']);
                $('#opening_stock_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#opening_stock_detail_product_id_'+$row_id).html('<option selected="selected" value="'+json.product['product_id']+'">'+json.product['name']+'</option>');
                $('#opening_stock_detail_rate_'+$row_id).val(json.product['cost_price']);

                $('#opening_stock_detail_rate_'+$row_id).trigger('change');

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
            $('#opening_stock_detail_product_id_'+$row_id).select2('destroy');
            if(json.success)
            {
                $('#opening_stock_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#opening_stock_detail_unit_'+$row_id).val(json.product['unit']);
                $('#opening_stock_detail_product_id_'+$row_id).html('<option selected="selected" value="'+json.product['product_id']+'">'+json.product['name']+'</option>');
                $('#opening_stock_detail_rate_'+$row_id).val(json.product['cost_price']).trigger('change');
            } else {
                alert(json.error);
                $('#opening_stock_detail_unit_id_'+$row_id).val('');
                $('#opening_stock_detail_unit_'+$row_id).val('');
                $('#opening_stock_detail_product_id_'+$row_id).html('');
                $('#opening_stock_detail_rate_'+$row_id).val('0.00');
            }
            $('#opening_stock_detail_product_id_'+$row_id).select2({
                width: '100%',
                ajax: {
                    url: $UrlGetProductJSON,
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

            calculateTotal();
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
}

function setProductInformation($obj) {
    var $data = $($obj).data();
    // console.log($data);
    var $row_id = $('#'+$data['element']).parent().parent().parent().data('row_id');
    // alert($row_id);
    $('#_modal').modal('hide');
    $('#opening_stock_detail_product_code_'+$row_id).val($data['product_code']);
    $('#opening_stock_detail_unit_id_'+$row_id).val($data['unit_id']);
    $('#opening_stock_detail_unit_'+$row_id).val($data['unit']);
    $('#opening_stock_detail_rate_'+$row_id).val($data['cost_price']);
    //$('#opening_stock_detail_product_id_'+$row_id).select2('destroy');
    $('#opening_stock_detail_product_id_'+$row_id).html('<option selected="selected" value="'+$data['product_id']+'">'+$data['name']+'</option>');
    $('#opening_stock_detail_product_id_'+$row_id).select2({
        width: '100%',
        ajax: {
            url: $UrlGetProductJSON,
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

    calculateTotal();
}

function removeRow($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_'+$row_id).remove();
    calculateTotal();
}
function addRow($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_'+$row_id).add();

}



function calculateAmount($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $qty = parseFloat($('#opening_stock_detail_qty_' + $row_id).val()) || 0.00;
    var $rate = parseFloat($('#opening_stock_detail_rate_' + $row_id).val()) || 0.00;


    var $amount = $qty * $rate;
    $amount = roundUpto($amount,2);

    $('#opening_stock_detail_amount_' + $row_id).val($amount);

    calculateTotal();
}

function calculateTotal() {
    var $net_amount = 0;
    $('#tblOpeningStockDetail tbody tr').each(function() {
        $row_id = $(this).data('row_id');
        $amount = $('#opening_stock_detail_amount_' + $row_id).val();

        $net_amount += parseFloat($amount);
    })

    $('#net_amount').val($net_amount);
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
