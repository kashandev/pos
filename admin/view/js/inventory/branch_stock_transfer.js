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
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '<td>';
    $html += '<input onchange="getProductByCode(this);" type="text" class="form-control" name="branch_stock_transfer_details['+$grid_row+'][product_code]" id="branch_stock_transfer_detail_product_code_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<div class="input-group">';
    $html += '<select onchange="getProductById(this);" class="form-control select2" id="branch_stock_transfer_detail_product_id_'+$grid_row+'" name="branch_stock_transfer_details['+$grid_row+'][product_id]" >';
    $html += '<option value="">&nbsp;</option>';
    // $products.forEach(function($product) {
    //     $html += '<option value="'+$product.product_id+'">'+$product.name+'</option>';
    // });
    $html += '</select>';
    $html += '<span class="input-group-btn ">';
    $html += '<button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="branch_stock_transfer_detail_product_id_'+$grid_row+'" data-field="product_id">';
    $html += '<i class="fa fa-search"></i>';
    $html += '</button>';
    $html += '</span>';
    $html += '</div>';
    $html += '</td>';
    $html += '<td>';
    $html += '<select onchange="getWarehouseByBranchId(this);" class="form-control select2" id="branch_stock_transfer_detail_to_company_branch_id_'+$grid_row+'" name="branch_stock_transfer_details['+$grid_row+'][to_company_branch_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $company_branchs.forEach(function($company_branch) {
        $html += '<option value="'+$company_branch.company_branch_id+'">'+$company_branch.name+'</option>';
    });
    $html += '</select>';
    $html += '</td>';
    $html += '<td>';
    $html += '<select class="form-control select2" id="branch_stock_transfer_detail_warehouse_id_'+$grid_row+'" name="branch_stock_transfer_details['+$grid_row+'][warehouse_id]" >';
//    $html += '<option value="">&nbsp;</option>';
//    $warehouses.forEach(function($warehouse) {
//        $html += '<option value="'+$warehouse.warehouse_id+'">'+$warehouse.name+'</option>';
//    });
    $html += '</select>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="branch_stock_transfer_details['+$grid_row+'][unit]" id="branch_stock_transfer_detail_unit_'+$grid_row+'" value="" readonly="true" />';
    $html += '<input type="hidden" class="form-control" name="branch_stock_transfer_details['+$grid_row+'][unit_id]" id="branch_stock_transfer_detail_unit_id_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control fPDecimal" name="branch_stock_transfer_details['+$grid_row+'][stock_qty]" id="branch_stock_transfer_detail_stock_qty_'+$grid_row+'" value="" readonly="true" />';
    $html += '<input type="text" class="form-control fPDecimal" name="branch_stock_transfer_details['+$grid_row+'][cog_rate]" id="branch_stock_transfer_detail_cog_rate_'+$grid_row+'" value="" readonly="true" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" onchange="calculateRowTotal(this);" class="form-control fPDecimal" name="branch_stock_transfer_details['+$grid_row+'][qty]" id="branch_stock_transfer_detail_qty_'+$grid_row+'" value="" />';
    $html += '<input type="text" class="form-control fPDecimal" name="branch_stock_transfer_details['+$grid_row+'][cog_amount]" id="branch_stock_transfer_detail_cog_amount_'+$grid_row+'" value="" readonly="true" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="hidden" class="form-control fPDecimal" id="branch_stock_transfer_detail_exist_rate_'+$grid_row+'"/>';
    $html += '<input type="text" onchange="calculateRowTotal(this);" class="form-control fPDecimal" name="branch_stock_transfer_details['+$grid_row+'][rate]" id="branch_stock_transfer_detail_rate_'+$grid_row+'" value=""  readonly="true" />';

    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" onchange="calculateRowTotal(this);" class="form-control fPDecimal" name="branch_stock_transfer_details['+$grid_row+'][amount]" id="branch_stock_transfer_detail_amount_'+$grid_row+'" value="" readonly="true" />';
    $html += '</td>';
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '</tr>';


    $('#tblBranchStockTransfer tbody').prepend($html);
    $('#branch_stock_transfer_detail_product_id_'+$grid_row).select2({width: '100%'});
    $('#branch_stock_transfer_detail_product_id_'+$grid_row).select2({
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
    $('#branch_stock_transfer_detail_to_company_branch_id_'+$grid_row).select2({width: '100%'});
    $('#branch_stock_transfer_detail_warehouse_id_'+$grid_row).select2({width: '100%'});
    $grid_row++;
});

function getProductById($obj) {
    $product_id = $($obj).val();
    $warehouse_id = $('#warehouse_id').val();
    var $row_id = $($obj).parent().parent().parent().data('row_id');
    var $avg_stock_rate = '';
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
            console.log(json)
            if(json.success)
            {
                $('#branch_stock_transfer_detail_product_code_'+$row_id).val(json.product['product_code']);
                $('#branch_stock_transfer_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#branch_stock_transfer_detail_unit_'+$row_id).val(json.product['unit']);
                $('#branch_stock_transfer_detail_product_code_'+$row_id).trigger('change');
            }
            else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })

    $('#branch_stock_transfer_detail_product_id_'+$grid_row).select2({
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

function getProductByCode($obj) {
    $product_code = $($obj).val();

    $warehouse_id = $('#warehouse_id').val();
    var $row_id = $($obj).parent().parent().data('row_id');
    var $avg_stock_rate = '';
   // var $row_id = $($obj).parent().parent().parent().data('row_id');

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
            console.log(json)
            if(json.success)
            {
                console.log( json )
                $('#branch_stock_transfer_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#branch_stock_transfer_detail_unit_'+$row_id).val(json.product['unit']);
                $('#branch_stock_transfer_detail_product_id_'+$row_id).select2('destroy');
                // $('#branch_stock_transfer_detail_product_id_'+$row_id).val(json.product['product_id']);
                $('#branch_stock_transfer_detail_product_id_'+$row_id).html('<option value="'+ json.product['product_id'] +'">'+ json.product['name'] +'</option>');
                $('#branch_stock_transfer_detail_product_id_'+$row_id).select2({width:'100%'});
                $('#branch_stock_transfer_detail_rate_'+$row_id).val(json.product.stock['avg_stock_rate']);
                $('#branch_stock_transfer_detail_exist_rate_'+$row_id).val(json.product.stock['avg_stock_rate']);

                $avg_stock_rate = json.product.stock['avg_stock_rate'];

                $.ajax({
                    url: $UrlGetWarehouseStock,
                    dataType: 'json',
                    type: 'post',
                    data: 'product_id='+json.product['product_id']+'&warehouse_id='+$warehouse_id,
                    mimeType:"multipart/form-data",
                    beforeSend: function() {
                    },
                    complete: function() {
                    },
                    success: function(json) {
                        console.log(json)
                        if(json.success)
                        {
                            $('#branch_stock_transfer_detail_stock_qty_'+$row_id).val(json.stock_qty);
                            $('#branch_stock_transfer_detail_cog_rate_'+$row_id).val($avg_stock_rate);
                            $('#branch_stock_transfer_detail_exist_cog_rate_'+$row_id).val($avg_stock_rate);
                        }
                        else {
                            alert(json.error);
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        console.log(xhr.responseText);
                    }
                });


               //var $product_pid = json.product['product_id'];
                //console.log($product_pid,json.product['product_id']);
//                $('#branch_stock_transfer_detail_stock_qty_'+$row_id).val(json.product['stock']['stock_qty']);
//                $('#branch_stock_transfer_detail_cog_rate_'+$row_id).val(json.product['stock']['avg_stock_rate']);
                // $('#branch_stock_transfer_detail_product_id_'+$row_id).trigger('change');
            }
            else {
                alert(json.error);
                $('#branch_stock_transfer_detail_unit_id_'+$row_id).val('');
                $('#branch_stock_transfer_detail_unit_'+$row_id).val('');
                $('#branch_stock_transfer_detail_product_id_'+$row_id).select2('destroy');
                $('#branch_stock_transfer_detail_product_id_'+$row_id).html('');
                $('#branch_stock_transfer_detail_product_id_'+$row_id).select2({width:'100%'});
//                $('#branch_stock_transfer_detail_stock_qty_'+$row_id).val('0');
//                $('#branch_stock_transfer_detail_cog_rate_'+$row_id).val('0.00');
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })

    $('#branch_stock_transfer_detail_product_id_'+$grid_row).select2({
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

function setProductInformation($obj) {
    var $data = $($obj).data();
    var $row_id = $('#'+$data['element']).parent().parent().parent().data('row_id');
    $('#_modal').modal('hide');
    $('#branch_stock_transfer_detail_product_code_'+$row_id).val($data['product_code']);
    $('#branch_stock_transfer_detail_unit_id_'+$row_id).val($data['unit_id']);
    $('#branch_stock_transfer_detail_unit_'+$row_id).val($data['unit']);
    $('#branch_stock_transfer_detail_product_code_'+$row_id).trigger('change');
    // $('#branch_stock_transfer_detail_stock_qty_'+$row_id).val($data['stock_qty']);
    // $('#branch_stock_transfer_detail_cog_rate_'+$row_id).val($data['avg_stock_rate']);
    $('#branch_stock_transfer_detail_rate_'+$row_id).val($data['avg_stock_rate']);
    $('#branch_stock_transfer_detail_product_id_'+$row_id).select2('destroy');
    $('#branch_stock_transfer_detail_product_id_'+$row_id).val($data['product_id']);
    $('#branch_stock_transfer_detail_product_id_'+$row_id).html('<option value="'+$data['product_id']+'">'+ $data['name'] +'</option>');
    $('#branch_stock_transfer_detail_product_id_'+$row_id).select2({width: '100%'});
}

// function getWarehouseStock($obj) {
//     var $row_id = $($obj).parent().parent().data('row_id');
//     var $data = {
//         warehouse_id: $('#warehouse_id').val(),
//         product_id: $('#branch_stock_transfer_detail_product_id_'+$row_id).val()
//     };
//     $.ajax({
//         url: $UrlGetWarehouseStock,
//         dataType: 'json',
//         type: 'post',
//         data: $data,
//         mimeType:"multipart/form-data",
//         beforeSend: function() {
//             //$('#partner_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
//         },
//         complete: function() {
//             //$('#loader').remove();
//         },
//         success: function(json) {
//             if(json.success)
//             {
//                 $('#branch_stock_transfer_detail_stock_qty_' + $row_id).val(json.stock_qty);
//             }
//             else {
//                 alert(json.error);
//             }
//         },
//         error: function(xhr, ajaxOptions, thrownError) {
//             console.log(xhr.responseText);
//         }
//     })
// };

function removeRow($obj) {
    //console.log($obj);
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_'+$row_id).remove();
    calculateTotal();
}

function calculateRowTotal($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $stock_qty = parseFloat($('#branch_stock_transfer_detail_stock_qty_'+$row_id).val()) || 0.00;
    var $qty = parseFloat($('#branch_stock_transfer_detail_qty_' + $row_id).val());
    var $exist_rate = parseFloat($('#branch_stock_transfer_detail_exist_rate_' + $row_id).val());

    if(  ($qty>$stock_qty) && ($('#allow_out_of_stock').val() != 1) ) {
        alert('Stock not available');
        $('#branch_stock_transfer_detail_qty_' + $row_id).val(0);
        $('#branch_stock_transfer_detail_rate_' + $row_id).val(0);
        $('#branch_stock_transfer_detail_amount_' + $row_id).val(0);
    } else {
        $('#branch_stock_transfer_detail_rate_' + $row_id).val($exist_rate);
    
    var $cograte = parseFloat($('#branch_stock_transfer_detail_cog_rate_' + $row_id).val());
    var $rate = parseFloat($('#branch_stock_transfer_detail_rate_' + $row_id).val());

    var $cog_amount = roundUpto($qty * $cograte,4);
    var $amount = roundUpto($qty * $rate,4);
    $cog_amount = $cog_amount || 0.00;
    $amount = $amount || 0.00;

    $('#branch_stock_transfer_detail_cog_amount_' + $row_id).val($cog_amount);
    $('#branch_stock_transfer_detail_amount_' + $row_id).val($amount);

    calculateTotal();
  }
}

function calculateTotal() {
    var $total_qty = 0;
    var $total_amount = 0;
    $('#tblBranchStockTransfer tbody tr').each(function() {
        $row_id = $(this).data('row_id');
        $qty = $('#branch_stock_transfer_detail_qty_' + $row_id).val();
        $amount = $('#branch_stock_transfer_detail_amount_' + $row_id).val();

        $total_qty += parseFloat($qty);
        $total_amount += parseFloat($amount);
        //alert($total_amount);
    })

    console.log($total_qty, $total_amount);
    $('#total_qty').val(roundUpto($total_qty,2));
    $('#total_amount').val($total_amount.toFixed(4));
}


function getWarehouseByBranchId($obj) {
    $company_branch_id = $($obj).val();
    var $row_id = $($obj).parent().parent().data('row_id');

    $warehouse_id = $('#branch_stock_transfer_detail_warehouse_id_'+$row_id).val();

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
                console.log($row_id,json.html);
                $('#branch_stock_transfer_detail_warehouse_id_'+$row_id).html(json.html).trigger('change');
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


function getLedger() {
    var $document_type_id = $('#document_type_id').val();
    var $document_id = $('#document_id').val();
    $.ajax({
        url: $UrlGetLedger,
        dataType: 'json',
        type: 'post',
        data: 'document_type_id=' + $document_type_id+'&document_id='+$document_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
        },
        complete: function() {
        },
        success: function(json) {
            if(json.success)
            {
                $('#_modal .modal-title').html(json.title);
                $('#_modal .modal-body').html(json.html);
                $('#_modal').modal();
            } else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
}