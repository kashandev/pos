/**
 * Created by Huzaifa on 9/18/15.
 */

//function Save() {
//
//    $('.btnsave').attr('disabled','disabled');
//    if($('#form').valid() == true){
//        $('#form').submit();
//    }
//    else{
//        $('.btnsave').removeAttr('disabled');
//    }
//}
//
//$(document).ready(function() {
//    $('#form').valid();
//})



$(document).on('click','#btnAddGrid', function() {

    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td><a id="btnAddGrid" title="Add" class="btn btn-xs btn-primary" href="javascript:void(0);"><i class="fa fa-plus"></i></a><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '<td>';
    $html += '<input onchange="getProductByCode(this);" type="text" class="form-control" name="stock_transfer_details['+$grid_row+'][product_code]" id="stock_transfer_detail_product_code_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td style="min-width: 250px;">';
    $html += '<div class="input-group">';
    $html += '<select onchange="getProductById(this);" class="form-control select2" id="stock_transfer_detail_product_id_'+$grid_row+'" name="stock_transfer_details['+$grid_row+'][product_id]" >';
    $html += '<option value="">&nbsp;</option>';
    // $products.forEach(function($product) {
    //     $html += '<option value="'+$product.product_id+'">'+$product.name+'</option>';
    // });
    $html += '</select>';
    $html += '<span class="input-group-btn ">';
    $html += '<button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="stock_transfer_detail_product_id_'+$grid_row+'" data-field="product_id">';
    $html += '<i class="fa fa-search"></i>';
    $html += '</button>';
    $html += '</span>';
    $html += '</div>';
    $html += '</td>';
    $html += '<td style="min-width: 250px;">';
    $html += '<input  type="text" style="min-width: 100px;" class="form-control" name="stock_transfer_details['+$grid_row+'][description]" id="stock_transfer_detail_description_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<select class="form-control select2" id="stock_transfer_detail_warehouse_id_'+$grid_row+'" name="stock_transfer_details['+$grid_row+'][warehouse_id]" required>';
    $html += '<option value="">&nbsp;</option>';
    $warehouses.forEach(function($warehouse) {
       $html += '<option value="'+$warehouse.warehouse_id+'">'+$warehouse.name+'</option>';
    });
    $html += '</select>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="stock_transfer_details['+$grid_row+'][unit]" id="stock_transfer_detail_unit_'+$grid_row+'" value="" readonly="true" />';
    $html += '<input type="hidden" class="form-control" name="stock_transfer_details['+$grid_row+'][unit_id]" id="stock_transfer_detail_unit_id_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control fPDecimal" name="stock_transfer_details['+$grid_row+'][stock_qty]" id="stock_transfer_detail_stock_qty_'+$grid_row+'" value="" readonly="true" />';
    $html += '<input type="hidden" class="form-control fPDecimal" name="stock_transfer_details['+$grid_row+'][cog_rate]" id="stock_transfer_detail_cog_rate_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" onchange="calculateRowTotal(this);" class="form-control fPDecimal" name="stock_transfer_details['+$grid_row+'][qty]" id="stock_transfer_detail_qty_'+$grid_row+'" value="" />';
    $html += '<input type="hidden" class="form-control fPDecimal" name="stock_transfer_details['+$grid_row+'][cog_amount]" id="stock_transfer_detail_cog_amount_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" onchange="calculateRowTotal(this);" class="form-control fPDecimal" name="stock_transfer_details['+$grid_row+'][rate]" id="stock_transfer_detail_rate_'+$grid_row+'" value="" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control fPDecimal" name="stock_transfer_details['+$grid_row+'][amount]" id="stock_transfer_detail_amount_'+$grid_row+'" value="" readonly/>';
    $html += '</td>';
    $html += '<td><a id="btnAddGrid" title="Add" class="btn btn-xs btn-primary" href="javascript:void(0);"><i class="fa fa-plus"></i></a><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '</tr>';


    $('#tblStockTransfer tbody').append($html);

    $('#stock_transfer_detail_product_id_'+$grid_row).select2({width: '100%'});
    $('#stock_transfer_detail_product_id_'+$grid_row).select2({
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
    $('#stock_transfer_detail_company_branch_id_'+$grid_row).select2({width: '100%'});
    $('#stock_transfer_detail_warehouse_id_'+$grid_row).select2({width: '100%'});
    $('#stock_transfer_detail_product_code_'+$grid_row).focus();

    $grid_row++;
});

function getProductById($obj) {
    $product_id = $($obj).val();
    $warehouse_id = $('#warehouse_id').val();
    $partner_id = $('#partner_id').val();
    $branch_id = $('#to_branch_id').val();
    var $row_id = $($obj).parent().parent().parent().data('row_id');

    if( $warehouse_id == '' ) {
        alert('Select Warehouse');
        $('#warehouse_id').select2('open');
        $('#stock_transfer_detail_product_id_'+$row_id).select2('destroy');
        $('#stock_transfer_detail_product_id_'+$row_id).select2({width:'100%'});
        $('#stock_transfer_detail_product_id_'+$row_id).select2({
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
        return;
    }

    $.ajax({
        url: $UrlGetProductById,
        dataType: 'json',
        type: 'post',
        data: 'product_id=' + $product_id+'&partner_id=' + $partner_id+'&branch_id=' + $branch_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-search').addClass('fa-refresh fa-spin');
            $('#stock_transfer_detail_stock_qty_' + $row_id).val('Loading...');
            $('#stock_transfer_detail_rate_' + $row_id).val('Loading...');
        },
        complete: function() {
            $('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-refresh').removeClass('fa-spin').addClass('fa-search');
        },
        success: function(json) {
            if(json.success)
            {
                $('#stock_transfer_detail_description_'+$row_id).val(json.product['name']);
                $('#stock_transfer_detail_product_code_'+$row_id).val(json.product['product_code']);
                $('#stock_transfer_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#stock_transfer_detail_unit_'+$row_id).val(json.product['unit']);
//                $('#stock_transfer_detail_stock_qty_'+$row_id).val(json.product['stock']['stock_qty']);
//                $('#stock_transfer_detail_cog_rate_'+$row_id).val(json.product['stock']['avg_stock_rate']);
                $('#txt_last_rate').val(json.branch_rate);

//                document.getElementById('last_rate').innerText = json.branch_rate;

            }
            else {
                alert(json.error);
            }

            getWarehouseStock($('#stock_transfer_detail_warehouse_id_'+$row_id));
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })

    // $.ajax({
    //     url: $UrlGetWarehouseStock,
    //     dataType: 'json',
    //     type: 'post',
    //     data: 'product_id=' + $product_id+'&warehouse_id='+$warehouse_id,
    //     mimeType:"multipart/form-data",
    //     beforeSend: function() {
    //     },
    //     complete: function() {
    //     },
    //     success: function(json) {
    //         if(json.success)
    //         {
    //             $('#stock_transfer_detail_stock_qty_'+$row_id).val(json.stock_qty);
    //             $('#stock_transfer_detail_cog_rate_'+$row_id).val(json.avg_stock_rate);
    //         }
    //         else {
    //             alert(json.error);
    //         }
    //     },
    //     error: function(xhr, ajaxOptions, thrownError) {
    //         console.log(xhr.responseText);
    //     }
    // })

}

function getProductByCode($obj) {
    $product_code = $($obj).val();
    $warehouse_id = $('#warehouse_id').val();
    $partner_id = $('#partner_id').val();
    $branch_id = $('#to_branch_id').val();
    var $row_id = $($obj).parent().parent().data('row_id');

    if( $warehouse_id == '' ) {
        alert('Select Warehouse');
        $('#warehouse_id').select2('open');
        $('#stock_transfer_detail_product_id_'+$row_id).select2('destroy');
        $('#stock_transfer_detail_product_id_'+$row_id).select2({width:'100%'});
        $('#stock_transfer_detail_product_id_'+$row_id).select2({
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
        return;
    }

    $.ajax({
        url: $UrlGetProductByCode,
        dataType: 'json',
        type: 'post',
        data: 'product_code=' + $product_code+'&partner_id=' + $partner_id+'&branch_id=' + $branch_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-search').addClass('fa-refresh fa-spin');
            $('#stock_transfer_detail_stock_qty_' + $row_id).val('Loading...');
            $('#stock_transfer_detail_rate_' + $row_id).val('Loading...');
        },
        complete: function() {
            $('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-refresh').removeClass('fa-spin').addClass('fa-search');
        },
        success: function(json) {
            if(json.success)
            {
                $('#stock_transfer_detail_description_'+$row_id).val(json.product['name']);
                $('#stock_transfer_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#stock_transfer_detail_unit_'+$row_id).val(json.product['unit']);
                $('#stock_transfer_detail_product_id_'+$row_id).select2('destroy');
                $('#stock_transfer_detail_product_id_'+$row_id).html('<option selected="selected" value="'+json.product['product_id']+'">'+json.product['name']+'</option>');
                $('#stock_transfer_detail_product_id_'+$row_id).select2({width:'100%'});
//                $('#stock_transfer_detail_stock_qty_'+$row_id).val(json.product['stock']['stock_qty']);
//                $('#stock_transfer_detail_cog_rate_'+$row_id).val(json.product['stock']['avg_stock_rate']);
                $('#txt_last_rate').val(json.branch_rate);
//                document.getElementById('last_rate').innerText = json.branch_rate;
                getWarehouseStock($('#stock_transfer_detail_warehouse_id_'+$row_id));
            }
            else {
                alert(json.error);
                $('#stock_transfer_detail_description_'+$row_id).val('');
                $('#stock_transfer_detail_unit_id_'+$row_id).val('');
                $('#stock_transfer_detail_unit_'+$row_id).val('');
                $('#stock_transfer_detail_product_id_'+$row_id).select2('destroy');
                $('#stock_transfer_detail_product_id_'+$row_id).val('');
                $('#stock_transfer_detail_product_id_'+$row_id).select2({width:'100%'});
//                $('#stock_transfer_detail_stock_qty_'+$row_id).val('0');
//                $('#stock_transfer_detail_cog_rate_'+$row_id).val('0.00');
                $('#txt_last_rate').val('0.00');

            }

    $('#stock_transfer_detail_product_id_'+$row_id).select2({
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
    var $row_id = $('#'+$data['element']).parent().parent().parent().data('row_id');
    $('#_modal').modal('hide');


    $warehouse_id = $('#warehouse_id').val();
    if( $warehouse_id == '' ) {
            alert('Select Warehouse');
            $('#warehouse_id').select2('open');
            $('#stock_transfer_detail_product_id_'+$row_id).select2('destroy');
            $('#stock_transfer_detail_product_id_'+$row_id).val('');
            $('#stock_transfer_detail_product_id_'+$row_id).select2({width:'100%'});
            $('#stock_transfer_detail_product_id_'+$row_id).select2({
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
            return;
        }


    $('#stock_transfer_detail_description_'+$row_id).val($data['name']);
    $('#stock_transfer_detail_product_code_'+$row_id).val($data['product_code']);
    $('#stock_transfer_detail_unit_id_'+$row_id).val($data['unit_id']);
    $('#stock_transfer_detail_unit_'+$row_id).val($data['unit']);
    $('#stock_transfer_detail_product_id_'+$row_id).select2('destroy');
    $('#stock_transfer_detail_product_id_'+$row_id).html('<option selected="selected" value="'+$data['product_id']+'">'+$data['name']+'</option>');
    $('#stock_transfer_detail_product_id_'+$row_id).select2({
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
    $('#stock_transfer_detail_stock_qty_' + $row_id).val('Loading...');
    $('#stock_transfer_detail_rate_' + $row_id).val('Loading...');
    getWarehouseStock($('#stock_transfer_detail_warehouse_id_'+$row_id))

}


function getWarehouseStock($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $data = {
        warehouse_id: $('#warehouse_id').val(),
        product_id: $('#stock_transfer_detail_product_id_'+$row_id).val()
    };
    $.ajax({
        url: $UrlGetWarehouseStock,
        dataType: 'json',
        type: 'post',
        data: $data,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            //$('#partner_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
            $('#stock_transfer_detail_stock_qty_' + $row_id).val('Loading...');
            $('#stock_transfer_detail_rate_' + $row_id).val('Loading...');
        },
        complete: function() {
            //$('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#stock_transfer_detail_stock_qty_' + $row_id).val(json.stock_qty);
                $('#stock_transfer_detail_rate_' + $row_id).val(json.avg_stock_rate);
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

    var $qty = parseFloat($('#stock_transfer_detail_qty_' + $row_id).val());
    var $cograte = parseFloat($('#stock_transfer_detail_cog_rate_' + $row_id).val());
    var $rate = parseFloat($('#stock_transfer_detail_rate_' + $row_id).val());

    if( $qty > parseFloat($('#stock_transfer_detail_stock_qty_' + $row_id).val()) ) {
        alert('Stock Not Available')
        $('#stock_transfer_detail_qty_' + $row_id).val(0);
        $qty = parseFloat($('#stock_transfer_detail_qty_' + $row_id).val());
    }

    var $cog_amount = roundUpto($qty * $cograte,2);
    var $amount = roundUpto($qty * $rate,2);
    $cog_amount = $cog_amount || 0.00;
    $amount = $amount || 0.00;

    $('#stock_transfer_detail_cog_amount_' + $row_id).val($cog_amount);
    $('#stock_transfer_detail_amount_' + $row_id).val($amount);

    calculateTotal();
}

function calculateTotal() {
    var $total_qty = 0;
    var $total_amount = 0;
    $('#tblStockTransfer tbody tr').each(function() {
        $row_id = $(this).data('row_id');
        $qty = $('#stock_transfer_detail_qty_' + $row_id).val();
        $amount = $('#stock_transfer_detail_amount_' + $row_id).val();

        $total_qty += parseFloat($qty);
        $total_amount += parseFloat($amount);
    })

    console.log($total_qty, $total_amount);
    $('#total_qty').val(roundUpto($total_qty,2));
    $('#total_amount').val(roundUpto($total_amount,2));
}


function getWarehouseByBranchId($obj) {
    $company_branch_id = $('#to_branch_id').val();

    var $row_id = $(this).parent().parent().data('row_id');
    //$warehouse_id = $('#stock_transfer_detail_warehouse_id_'+$row_id).val();
    $warehouse_id ="";


    $.ajax({
        url: $UrlGetWarehouseByBranchId,
        dataType: 'json',
        type: 'post',
        data: 'company_branch_id=' + $company_branch_id +'&warehouse_id='+$warehouse_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
//            $('#partner_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
//            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $warehouses = json.html;
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