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
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="getProductByCode(this);" type="text" class="form-control" name="quotation_details['+$grid_row+'][product_code]" id="quotation_detail_product_code_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<div class="input-group">';
    $html += '<select onchange="getProductById(this);" class="form-control select2 hide" id="quotation_detail_product_id_'+$grid_row+'" name="quotation_details['+$grid_row+'][product_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $products.forEach(function($product) {
        $html += '<option value="'+$product.product_id+'">'+$product.name+'</option>';
    });
    $html += '</select>';
    $html += '<span class="input-group-btn ">';
    $html += '<button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="quotation_detail_product_id_'+$grid_row+'" data-field="product_id">';
    $html += '<i class="fa fa-search"></i>';
    $html += '</button>';
    $html += '</span>';
    $html += '</div>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="quotation_details['+$grid_row+'][description]" id="quotation_detail_description_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="quotation_details['+$grid_row+'][delivery]" id="quotation_detail_delivery_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control fDecimal" name="quotation_details['+$grid_row+'][stock_qty]" id="quotation_detail_stock_qty_'+$grid_row+'" value=""  readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" onchange="calculateAmount(this);" class="form-control fDecimal" name="quotation_details['+$grid_row+'][qty]" id="quotation_detail_qty_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="hidden" class="form-control fDecimal" name="quotation_details['+$grid_row+'][unit_id]" id="quotation_detail_unit_id_'+$grid_row+'" value="" />';
    $html += '<input type="text" readonly class="form-control " name="quotation_details['+$grid_row+'][unit]" id="quotation_detail_unit_'+$grid_row+'" value="" />';
    // $html += '<select class="form-control select2" id="quotation_detail_unit_id_'+$grid_row+'" name="quotation_details['+$grid_row+'][unit_id]" >';
    // $html += '<option value="">&nbsp;</option>';
    // $units.forEach(function($unit) {
    //     $html += '<option value="'+$unit.unit_id+'">'+$unit.name+'</option>';
    // });
    // $html += '</select>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" onchange="calculateAmount(this);" class="form-control fDecimal" name="quotation_details['+$grid_row+'][rate]" id="quotation_detail_rate_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control fDecimal" name="quotation_details['+$grid_row+'][amount]" id="quotation_detail_amount_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" onchange="calculateTaxAmount(this);" class="form-control fDecimal" name="quotation_details['+$grid_row+'][tax_percent]" id="quotation_detail_tax_percent_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" onchange="calculateTaxAmount(this);" class="form-control fDecimal" name="quotation_details['+$grid_row+'][tax_amount]" id="quotation_detail_tax_amount_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text"  class="form-control fDecimal" name="quotation_details['+$grid_row+'][net_amount]" id="quotation_detail_net_amount_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '</tr>';


//    $('#tblQuotation tbody').prepend($html);
    $('#tblQuotation tbody').append($html);
    setFieldFormat();

    $('#quotation_detail_product_id_'+$grid_row).select2({
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

    // $('#quotation_detail_product_id_'+$grid_row).select2({width: '100%'});
    $('#quotation_detail_warehouse_id_'+$grid_row).select2({width: '100%'});
    // $('#quotation_detail_product_code_'+$grid_row).focus();


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
                $('#quotation_detail_product_code_'+$row_id).val(json.product['product_code']);
                $('#quotation_detail_description_'+$row_id).val(json.product['name']);
                $('#quotation_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#quotation_detail_unit_'+$row_id).val(json.product['unit']);
                $('#quotation_detail_stock_qty_'+$row_id).val(json.product['stock']['stock_qty']);
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
            $('#quotation_detail_product_id_'+$row_id).select2('destroy');
            if(json.success)
            {
                $('#quotation_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#quotation_detail_unit_'+$row_id).val(json.product['unit']);
                // $('#quotation_detail_product_id_'+$row_id).select2('destroy');

                $('#quotation_detail_product_id_'+$row_id).html('<option selected="selected" value="'+json.product['product_id']+'">'+json.product['name']+'</option>');
                
                // $('#quotation_detail_product_id_'+$row_id).val(json.product['product_id']);
                $('#quotation_detail_description_'+$row_id).val(json.product['name']);
                // $('#quotation_detail_product_id_'+$row_id).select2({width:'100%'});
                $('#quotation_detail_stock_qty_'+$row_id).val(json.product['stock']['stock_qty']);


            }
            else {
                alert(json.error);
                $('#quotation_detail_unit_id_'+$row_id).val('');
                $('#quotation_detail_unit_'+$row_id).val('');
                // $('#quotation_detail_product_id_'+$row_id).select2('destroy');
                $('#quotation_detail_product_id_'+$row_id).html('');
                // $('#quotation_detail_product_id_'+$row_id).select2({width:'100%'});
            }
            $('#quotation_detail_product_id_'+$row_id).select2({
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
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
}

function setProductInformation($obj) {
    var $data = $($obj).data();
    console.log($data);
    var $row_id = $('#'+$data['element']).parent().parent().parent().data('row_id');
    $('#_modal').modal('hide');
    $('#quotation_detail_stock_qty_'+$row_id).val($data['stock_qty']);
    $('#quotation_detail_product_code_'+$row_id).val($data['product_code']);
    $('#quotation_detail_unit_id_'+$row_id).val($data['unit_id']);
    $('#quotation_detail_unit_'+$row_id).val($data['unit']);
    $('#quotation_detail_description_'+$row_id).val($data['name']);
    $('#quotation_detail_product_id_'+$row_id).select2('destroy');
    $('#quotation_detail_product_id_'+$row_id).html('<option selected="selected" value="'+$data['product_id']+'">'+$data['name']+'</option>');
    $('#quotation_detail_product_id_'+$row_id).select2({
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
}


function removeRow($obj) {
    //console.log($obj);
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_'+$row_id).remove();
    calculateTotal();
}

function calculateAmount($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $qty = parseFloat($('#quotation_detail_qty_' + $row_id).val()) || 0.00;
    var $rate = parseFloat($('#quotation_detail_rate_' + $row_id).val()) || 0.00;


    var $amount = $qty * $rate;
    $amount = roundUpto($amount,2);

    $('#quotation_detail_amount_' + $row_id).val($amount);
    $('#quotation_detail_tax_percent_' + $row_id).trigger('change');
}

function calculateTaxAmount($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $tax_percent = parseFloat($($obj).val() || 0.0000);
    var $amount = parseFloat($('#quotation_detail_amount_' + $row_id).val() || 0.0000);
    var $tax_amount = roundUpto($amount * $tax_percent / 100,2);

    $('#quotation_detail_tax_amount_' + $row_id).val($tax_amount);
    calculateRowTotal($obj);
}

function calculateTaxPercent($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $tax_amount = parseFloat($($obj).val() || 0.0000);
    var $amount = parseFloat($('#quotation_detail_amount_' + $row_id).val() || 0.0000);
    var $tax_percent = roundUpto($tax_amount / $amount * 100,2);

    $('#quotation_detail_tax_percent_' + $row_id).val($tax_percent);
    calculateRowTotal($obj);
}

function calculateRowTotal($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');

    var $amount = parseFloat($('#quotation_detail_amount_' + $row_id).val());

    var $tax_amount = parseFloat($('#quotation_detail_tax_amount_' + $row_id).val());
    var $total_amount = roundUpto($amount + $tax_amount,2);

    $('#quotation_detail_net_amount_' + $row_id).val($total_amount);

    calculateTotal();
}

function calculateTotal() {
    var $item_amount = 0;
    var $item_discount = 0;
    var $item_tax = 0;
    var $item_total = 0;
    var $total_quantity = 0;
    $('#tblQuotation tbody tr').each(function() {
        var $row_id = $(this).data('row_id');
        var $amount = $('#quotation_detail_amount_' + $row_id).val();
        var $tax_amount = $('#quotation_detail_tax_amount_' + $row_id).val();
        var $total_amount = $('#quotation_detail_net_amount_' + $row_id).val();
        var $quantity = $('#quotation_detail_qty_' + $row_id).val();

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


$(document).on('change','#term_id', function() {
    var $terms ='';
    var $lb ='\n';
    $("#term_id option:selected").each(function () {
        var $this = $(this);
        if ($this.length) {
            var selText = $this.text();
            $terms  += '* '+selText + $lb;
        }
            $('#term_desc').val($terms);

});

});


function Save() {

    $('.btnsave').attr('disabled','disabled');
    if($('#form').valid() == true){
        $('#form').submit();
    }
    else{
        $('.btnsave').removeAttr('disabled');
    }
}