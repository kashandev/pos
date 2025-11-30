/**
 * Created by Huzaifa on 9/18/15.
 */

$(document).ready(function () {
    //$('#challan_type_no').trigger('change');
});

// $(document).on('change','#challan_type_no, #challan_type_yes',function() {

//     var $challan_type = $(this).val();
//     $.ajax({
//         url: $UrlGetCustomer,
//         dataType: 'json',
//         type: 'post',
//         data: 'challan_type=' + $challan_type+'&partner_id='+$partner_id,
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
//                 // $('#ref_document_type_id').trigger('change');
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


$(document).on('change', '#partner_type_id', function () {
    $partner_type_id = $(this).val();
    var $challan_type = $('#challan_type').val();

    //    $.ajax({
    //        url: $UrlGetPartner,
    //        dataType: 'json',
    //        type: 'post',
    //        data: 'partner_type_id=' + $partner_type_id+'&partner_id='+$partner_id,
    //        mimeType:"multipart/form-data",
    //        beforeSend: function() {
    //            $('#partner_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
    //        },
    //        complete: function() {
    //            $('#loader').remove();
    //        },
    //        success: function(json) {
    //            if(json.success)
    //            {
    //                $('#partner_id').select2('destroy');
    //                $('#partner_id').html(json.html);
    //                $('#partner_id').select2({width:'100%'});
    //            }
    //            else {
    //                alert(json.error);
    //            }
    //        },
    //        error: function(xhr, ajaxOptions, thrownError) {
    //            console.log(xhr.responseText);
    //        }
    //    })
});

// $(document).on('change','#partner_id', function() {
//    $partner_id = $(this).val();
//    $.ajax({
//        url: $UrlGetCustomerUnit,
//        dataType: 'json',
//        type: 'post',
//        data: 'partner_id=' + $partner_id,
//        mimeType:"multipart/form-data",
//        beforeSend: function() {
// //            $('#customer_unit_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
//        },
//        complete: function() {
// //            $('#loader').remove();
//        },
//        success: function(json) {
//            if(json.success)
//            {
// //                $('#customer_unit_id').select2('destroy');
// //                $('#customer_unit_id').html(json.html);
// //                $('#customer_unit_id').select2({width:'100%'});
//            }
//            else {
//                alert(json.error);
//            }
//        },
//        error: function(xhr, ajaxOptions, thrownError) {
//            console.log(xhr.responseText);
//        }
//    })
// });


$(document).on('change', '#ref_document_type_id', function () {

    $ref_document_type = $(this).val();
    var $partner_type_id = $('#partner_type_id').val();
    var $partner_id = $('#partner_id').val();
    //    var     $ref_document_identity = $('#ref_document_identity').val();
    var $data = {
        //'document_currency_id': $document_currency_id,
        'partner_type_id': $partner_type_id,
        'partner_id': $partner_id,
        'ref_document_identity': $ref_document_identity
    };

    if ($ref_document_type != "") {
        $.ajax({
            url: $UrlGetReferenceDocumentNo,
            dataType: 'json',
            type: 'post',
            data: $data,
            mimeType: "multipart/form-data",
            beforeSend: function () {
                $('#ref_document_identity').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
            },
            complete: function () {
                $('#loader').remove();
            },
            success: function (json) {
                if (json.success) {
                    $('#ref_document_identity').select2('destroy');
                    $('#ref_document_identity').html(json.html);
                    $('#ref_document_identity').select2({ width: '100%' });
                }
                else {
                    alert(json.error);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.responseText);
            }
        })
    }
});


$(document).on('click', '.btnAddGrid', function () {
    $html = '';
    $html += '<tr id="grid_row_' + $grid_row + '" data-row_id="' + $grid_row + '">';
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';

    $html += '<td></td>';
    $html += '<td>';

    $html += '<input onchange="getProductByCode(this);" type="text" class="required form-control" name="delivery_challan_details[' + $grid_row + '][product_code]" id="delivery_challan_detail_product_code_' + $grid_row + '" value="" />';
    $html += '<input type="hidden" name="delivery_challan_details[' + $grid_row + '][available_stock]"id="delivery_challan_detail_available_stock_' + $grid_row + '">';
    //    $html += '<input  type="hidden" class="form-control" name="delivery_challan_details['+$grid_row+'][product_code]" id="delivery_challan_detail_product_code_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<div class="input-group">';
    $html += '<select onchange="getProductById(this);" class="form-control select2 hide" id="delivery_challan_detail_product_id_' + $grid_row + '" name="delivery_challan_details[' + $grid_row + '][product_id]" >';
    $html += '<option value="">&nbsp;</option>';
    //    $products.forEach(function($product) {
    //    $html += '<option value="'+$product.product_id+'">'+$product.name+'</option>';
    //    });
    $html += '</select>';
    $html += '<span class="input-group-btn ">';
    $html += '<button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="delivery_challan_detail_product_id_' + $grid_row + '" data-field="product_id">';
    $html += '<i class="fa fa-search"></i>';
    $html += '</button>';
    $html += '</span>';
    $html += '</div>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" width="350px" class="form-control" name="delivery_challan_details[' + $grid_row + '][description]" id="delivery_challan_detail_description_' + $grid_row + '" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" width="350px" class="form-control" name="delivery_challan_details[' + $grid_row + '][remarks]" id="delivery_challan_detail_remarks_' + $grid_row + '" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<select onchange="getWarehouseStock(this);" class="required form-control select2" id="delivery_challan_detail_warehouse_id_' + $grid_row + '" name="delivery_challan_details[' + $grid_row + '][warehouse_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $warehouses.forEach(function ($warehouse) {
        $html += '<option value="' + $warehouse.warehouse_id + '">' + $warehouse.name + '</option>';
    });
    $html += '</select>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" width="360px" class="form-control" name="delivery_challan_details[' + $grid_row + '][stock_qty]" id="delivery_challan_detail_stock_qty_' + $grid_row + '" value="" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" onchange="calculateRowTotal(this);" class="form-control fPDecimal" name="delivery_challan_details[' + $grid_row + '][qty]" id="delivery_challan_detail_qty_' + $grid_row + '" value="" />';
    $html += '</td>';
    $html += '<td>';
    //    $html += '<select class="form-control select2" id="delivery_challan_detail_unit_id_'+$grid_row+'" name="delivery_challan_details['+$grid_row+'][unit_id]" >';
    //    $html += '<option value="">&nbsp;</option>';
    //    $units.forEach(function($unit) {
    //        $html += '<option value="'+$unit.unit_id+'">'+$unit.name+'</option>';
    //    });
    //    $html += '</select>';
    $html += '<input type="text" class="form-control" id="delivery_challan_detail_unit_' + $grid_row + '" name="delivery_challan_details[' + $grid_row + '][unit]" value="" >';
    $html += '<input type="hidden" class="form-control" id="delivery_challan_detail_unit_id_' + $grid_row + '" name="delivery_challan_details[' + $grid_row + '][unit_id]" value="">';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" onchange="calculateRowTotal(this);" class="form-control fPDecimal" name="delivery_challan_details[' + $grid_row + '][rate]" id="delivery_challan_detail_rate_' + $grid_row + '" value="0" />';
    $html += '<input type="hidden" class="form-control fPDecimal" name="delivery_challan_details[' + $grid_row + '][cog_rate]" id="delivery_challan_detail_cog_rate_' + $grid_row + '" value="0" />';
    $html += '<input type="hidden"  class="form-control fPDecimal" name="delivery_challan_details[' + $grid_row + '][cog_amount]" id="delivery_challan_detail_cog_amount_' + $grid_row + '" value="" />';
    $html += '<input type="hidden" class="form-control fDecimal" name="delivery_challan_details[' + $grid_row + '][tax_percent]" id="delivery_challan_detail_tax_percent_' + $grid_row + '" value="" />';
    $html += '<input type="hidden" class="form-control fDecimal" name="delivery_challan_details[' + $grid_row + '][tax_amount]" id="delivery_challan_detail_tax_amount_' + $grid_row + '" value="" />';
    $html += '<input type="hidden" class="form-control fDecimal" name="delivery_challan_details[' + $grid_row + '][net_amount]" id="delivery_challan_detail_net_amount_' + $grid_row + '" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '</tr>';


    //$('#tblDeliveryChallan tbody').prepend($html);
    $('#tblDeliveryChallan tbody').prepend($html);
    $('#delivery_challan_detail_product_id_' + $grid_row).select2({ width: '100%' });
    $('#delivery_challan_detail_warehouse_id_' + $grid_row).select2({ width: '100%' });
    $('#delivery_challan_detail_product_code_' + $grid_row).focus();

    $('#delivery_challan_detail_product_id_' + $grid_row).select2({
        width: '100%',
        ajax: {
            url: $UrlGetProductJSON,
            dataType: 'json',
            type: 'post',
            mimeType: "multipart/form-data",
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



    $grid_row++;
});

$(document).on('change', '#ref_document_identity', function () {
    var $ref_document_identity = $('#ref_document_identity').val();
    var $partner_id = $('#partner_id').val();


    $.ajax({
        url: $UrlGetSaleOrder,
        dataType: 'json',
        type: 'post',
        data: 'ref_document_identity=' + $ref_document_identity + '&partner_id=' + $partner_id,
        mimeType: "multipart/form-data",
        beforeSend: function () {
            $('#tblDeliveryChallan').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function () {
            $('#loader').remove();
        },
        success: function (json) {
            if (json.success) {
                $('#tblDeliveryChallan tbody').html(json.html);
                $sale_orders = json.sale_orders;
                $('#total_qty').val(json.qty_total);
                $('#po_date').val(json.po_date);
                $('#po_no').val(json.po_no);
                $('#tblDeliveryChallan tbody tr').each(function($index) {
                $('#delivery_challan_details'+$index+'_warehouse_id').trigger('change');
                });

                // $('#salesman_id').select2('destroy');
                // $('#salesman_id').val(json.salesman_id);
                // $('#salesman_id').select2({width:'100%'});


                // $('#salesman_id').trigger('change');

                $('#customer_unit_id').val(json.customer_unit_id).trigger('change');

            } else {
                alert(json.error);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
})

/*
 $(document).on('change','#ref_document_id', function() {
 // var $document_currency_id = $('#document_currency_id').val();
 var $partner_type_id = $('#partner_type_id').val();
 var $partner_id = $('#partner_id').val();
 var $ref_document_type_id = $('#ref_document_type_id').val();
 var $ref_document_identity = $(this).val();


 if($ref_document_identity!='') {
 var $data = {
 //'document_currency_id': $document_currency_id,
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
 //$('#document_currency_id').val(json.data['document_currency_id']);
 // $('#conversion_rate').val(json.data['conversion_rate']);
 // $('#base_currency_id').val(json.data['base_currency_id']);
 // $('#discount').val(json.data['discount']);

 $('').remove();
 $('#tblDeliveryChallan tbody tr').remove();

 $.each(json.data['products'], function($i,$product) {
 fillGrid($product);
 console.log($product);
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
 $('#tblDeliveryChallan tbody').html('');
 }
 });
 */

function getProductById($obj) {
    $product_id = $($obj).val();
    $partner_id = $('#partner_id').val();
    var $row_id = $($obj).parent().parent().parent().data('row_id');
    $.ajax({
        url: $UrlGetProductById,
        dataType: 'json',
        type: 'post',
        data: 'product_id=' + $product_id + '&partner_id=' + $partner_id,
        mimeType: "multipart/form-data",
        beforeSend: function () {
            $('#grid_row_' + $row_id + ' .QSearchProduct i').removeClass('fa-search').addClass('fa-refresh fa-spin');
        },
        complete: function () {
            $('#grid_row_' + $row_id + ' .QSearchProduct i').removeClass('fa-refresh').removeClass('fa-spin').addClass('fa-search');
        },
        success: function (json) {
            if (json.success) {
                $('#delivery_challan_detail_description_' + $row_id).val(json.product['name']);
                $('#delivery_challan_detail_product_code_' + $row_id).val(json.product['product_code']);
                $('#delivery_challan_detail_unit_id_' + $row_id).val(json.product['unit_id']);
                $('#delivery_challan_detail_unit_' + $row_id).val(json.product['unit']);
                //                $('#delivery_challan_detail_stock_qty_'+$row_id).val(json.product['stock']['stock_qty']);
                //                $('#delivery_challan_detail_cog_rate_'+$row_id).val(json.product['stock']['avg_stock_rate']);
                $('#last_rate').val(json.customer_rate);
            }
            else {
                alert(json.error);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
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
        data: 'product_code=' + $product_code + '&partner_id=' + $partner_id,
        mimeType: "multipart/form-data",
        beforeSend: function () {
            $('#grid_row_' + $row_id + ' .QSearchProduct i').removeClass('fa-search').addClass('fa-refresh fa-spin');
        },
        complete: function () {
            $('#grid_row_' + $row_id + ' .QSearchProduct i').removeClass('fa-refresh').removeClass('fa-spin').addClass('fa-search');
        },
        success: function (json) {
            if (json.success) {
                console.log(json)
                $('#delivery_challan_detail_description_' + $row_id).val(json.product['name']);
                $('#delivery_challan_detail_unit_id_' + $row_id).val(json.product['unit_id']);
                $('#delivery_challan_detail_unit_' + $row_id).val(json.product['unit']);
                $('#delivery_challan_detail_product_id_' + $row_id).select2('destroy');
                //                $('#delivery_challan_detail_product_id_'+$row_id).val(json.product['product_id']);
                $('#delivery_challan_detail_product_id_' + $row_id).html('<option selected="selected" value="' + json.product['product_id'] + '">' + json.product['product_code'] + ' -( ' + json.product['name'] + ' )' + '</option>');
                $('#delivery_challan_detail_product_id_' + $row_id).select2({ width: '100%' });
                $('#delivery_challan_detail_stock_qty_' + $row_id).val(json.product['stock']['stock_qty']);
                //                $('#delivery_challan_detail_cog_rate_'+$row_id).val(json.product['stock']['avg_stock_rate']);
                $('#last_rate').val(json.customer_rate);
            }
            else {
                alert(json.error);
                $('#delivery_challan_detail_description_' + $row_id).val('');
                $('#delivery_challan_detail_unit_id_' + $row_id).val('');
                $('#delivery_challan_detail_unit_' + $row_id).val('');
                $('#delivery_challan_detail_product_id_' + $row_id).select2('destroy');
                $('#delivery_challan_detail_product_id_' + $row_id).val('');
                $('#delivery_challan_detail_product_id_' + $row_id).select2({ width: '100%' });
                //                $('#delivery_challan_detail_stock_qty_'+$row_id).val('0');
                //                $('#delivery_challan_detail_cog_rate_'+$row_id).val('0.00');
            }

            $('#delivery_challan_detail_product_id_' + $row_id).select2({
                width: '100%',
                ajax: {
                    url: $UrlGetProductJSON,
                    dataType: 'json',
                    type: 'post',
                    mimeType: "multipart/form-data",
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
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
}

function setProductInformation($obj) {
    var $data = $($obj).data();
    var $row_id = $('#' + $data['element']).parent().parent().parent().data('row_id');
    $('#_modal').modal('hide');
    $('#delivery_challan_detail_product_code_' + $row_id).val($data['product_code']);
    $('#delivery_challan_detail_unit_id_' + $row_id).val($data['unit_id']);
    $('#delivery_challan_detail_unit_' + $row_id).val($data['unit']);
    $('#delivery_challan_detail_stock_qty_' + $row_id).val($data['stock_qty']);
    //    $('#delivery_challan_detail_cog_rate_'+$row_id).val($data['avg_stock_rate']);
    $('#delivery_challan_detail_description_' + $row_id).val($data['name']);
    $('#delivery_challan_detail_product_id_' + $row_id).select2('destroy');
    //$('#delivery_challan_detail_product_id_'+$row_id).val($data['product_id']);
    $('#delivery_challan_detail_product_id_' + $row_id).html('<option selected="selected" value="' + $data['product_id'] + '">' + $data['product_code'] + ' -( ' + $data['name'] + ' )' + '</option>');
    $('#delivery_challan_detail_product_id_' + $row_id).select2({ width: '100%' });

    $('#delivery_challan_detail_product_id_' + $row_id).select2({
        width: '100%',
        ajax: {
            url: $UrlGetProductJSON,
            dataType: 'json',
            type: 'post',
            mimeType: "multipart/form-data",
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

$('[name="document_date"]').change(function () {
    $('#tblDeliveryChallan tbody tr').each(function () {
        $row_id = $(this).data('row_id');
        $('#delivery_challan_detail_warehouse_id_' + $row_id).trigger('change');
    })
});

function getWarehouseStock($obj, $isEdit = 0) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $ref_document_identity = $('#delivery_challan_detail_ref_document_identity_'+$row_id).val();
    var $document_identity = '';
    if ($isEdit) {
        $document_identity = $('[name="document_identity"]').val();
    }
    var $data = {
        warehouse_id: $($obj).val(),
        product_id: $('#delivery_challan_detail_product_id_' + $row_id).val(),
        document_date: $('[name="document_date"]').val(),
        document_identity: $document_identity
    };

    var $change_qty = parseFloat($('#delivery_challan_detail_qty_' + $row_id).val()) || 0;

    $.ajax({
        url: $UrlGetWarehouseStock,
        dataType: 'json',
        type: 'post',
        data: $data,
        mimeType: "multipart/form-data",
        beforeSend: function () {
            $('#partner_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function () {
            $('#loader').remove();
        },
        success: function (json) {
            // console.log(json);
            if (json.success) {
                $available_stock = parseFloat(json.stock_qty) || 0;
                // console.log($available_stock+' - '+$change_qty+' - '+$($obj).val());
                if (($change_qty > $available_stock) && $allow_out_of_stock == 0) {
                    alert('Stock Not Available');
                    $('#delivery_challan_detail_warehouse_id_'+$row_id).prop('selectedIndex',0);
                    
                    // console.log();
                    if(!$('#delivery_challan_detail_so_'+$row_id))
                    {
                        $('#delivery_challan_detail_warehouse_id_' + $row_id).select2('destroy');
                        $('#delivery_challan_detail_warehouse_id_' + $row_id).val('');
                        $('#delivery_challan_detail_warehouse_id_' + $row_id).select2({ width: '100%' });    
                    }
                    $('#delivery_challan_detail_qty_' + $row_id).val(0);
                    $change_qty = 0;
                }
                $('#delivery_challan_detail_available_stock_' + $row_id).val($available_stock);
                $('#delivery_challan_detail_stock_qty_' + $row_id).val(parseFloat(json.stock_qty));
                $('#delivery_challan_detail_cog_rate_' + $row_id).val(json['avg_stock_rate']);
            }
            else {
                alert(json.error);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
};

function removeRow($obj) {
    //console.log($obj);
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_' + $row_id).remove();
    calculateTotal();
}


$(document).on('click', '.btnRemoveGrid', function () {


    //    var $row_id = $('#tblDeliveryChallan tbody tr').data('row_id');
    //    alert($row_id,true);
    //    $('#grid_row_'+$row_id).remove();
    $(this).parent().parent().remove();
    calculateTotal();
});


function calculateRowTotal($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $available_stock = parseFloat($('#delivery_challan_detail_available_stock_' + $row_id).val()) || 0;
    var $qty = parseFloat($('#delivery_challan_detail_qty_' + $row_id).val()) || 0;
    console.log($available_stock+' - '+$qty);
    if (($qty > $available_stock) && $allow_out_of_stock == 0) {
        alert('Stock Not Available');
        $('#delivery_challan_detail_qty_' + $row_id).val(0);
    } else {
        var $rate = parseFloat($('#delivery_challan_detail_rate_' + $row_id).val());
        var $cog_rate = parseFloat($('#delivery_challan_detail_cog_rate_' + $row_id).val());
        var $cogs_amount = roundUpto($qty * $cog_rate, 2);
        var $amount = roundUpto($qty * $rate, 2);

        $amount = $amount || 0.00;

        $('#delivery_challan_detail_cog_amount_' + $row_id).val($cogs_amount);
        $('#delivery_challan_detail_amount_' + $row_id).val($amount);

        var $tax_percent = parseFloat($('#delivery_challan_detail' + $row_id + '_tax_percent').val());
        var $tax_amount = roundUpto($cogs_amount * $tax_percent / 100, 2);
        $('#delivery_challan_detail' + $row_id + '_tax_amount').val($tax_amount);

        var $total_amount = roundUpto($cogs_amount + $tax_amount, 2);
        $('#delivery_challan_detail' + $row_id + '_net_amount').val($total_amount);


        // console.log($row_id, $qty, $cog_rate, $amount,$tax_percent,$tax_amount,$total_amount);
        calculateTotal();
    }

}

function fillGrid($obj) {
    $html = '';
    $html += '<tr id="grid_row_' + $grid_row + '" data-row_id="' + $grid_row + '">';
    $html += '<td><a title="Remove" class="btnRemoveGrid btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '<td>';
    $html += '<input type="text" name="sale_order_details[' + $grid_row + '][ref_document_type_id]" id="delivery_challan_detail_ref_document_type_id_' + $grid_row + '" value="' + $obj['ref_document_type_id'] + '"/>';
    $html += '<input type="hidden" class="form-control" name="delivery_challan_details[' + $grid_row + '][ref_document_type_id]" id="delivery_challan_detail_ref_document_type_id_' + $grid_row + '" value="' + $obj['ref_document_type_id'] + '" readonly/>';
    $html += '<input type="hidden" class="form-control" name="delivery_challan_details[' + $grid_row + '][ref_document_identity]" id="delivery_challan_detail_document_identity_' + $grid_row + '" value="' + $obj['ref_document_identity'] + '" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="delivery_challan_details[' + $grid_row + '][product_code]" id="delivery_challan_detail_product_code_' + $grid_row + '" value="' + $obj['product_code'] + '" readonly/>';

    $html += '</td>';
    $html += '<td>';
    $html += '<input type="hidden" class="form-control" name="delivery_challan_details[' + $grid_row + '][product_id]" id="delivery_challan_detail_product_id_' + $grid_row + '" value="' + $obj['product_id'] + '" readonly/>';
    $html += '<input type="text" class="form-control" name="delivery_challan_details[' + $grid_row + '][product_name]" id="delivery_challan_detail_product_name_' + $grid_row + '" value="' + $obj['product_name'] + '" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" width="360px" class="form-control" name="delivery_challan_details[' + $grid_row + '][description]" id="delivery_challan_detail_description_' + $grid_row + '" value="' + $obj['description'] + '" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" width="360px" class="form-control" name="delivery_challan_details[' + $grid_row + '][remarks]" id="delivery_challan_detail_remarks_' + $grid_row + '" value="' + $obj['remarks'] + '" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="hidden" class="form-control" name="delivery_challan_details[' + $grid_row + '][unit_id]" id="delivery_challan_detail_unit_id_' + $grid_row + '" value="' + $obj['unit_id'] + '" readonly/>';
    $html += '<input type="text" class="form-control" name="delivery_challan_details[' + $grid_row + '][unit]" id="delivery_challan_detail_unit_' + $grid_row + '" value="' + $obj['unit'] + '" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" width="360px" class="form-control" name="delivery_challan_details[' + $grid_row + '][stock_qty]" id="delivery_challan_detail_stock_qty_' + $grid_row + '" value="' + $obj['stock_qty'] + '" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" type="text" class="form-control fPDecimal" name="delivery_challan_details[' + $grid_row + '][qty]" id="delivery_challan_detail_qty_' + $grid_row + '" value="0" />';
    $html += '<input type="hidden" class="form-control " name="delivery_challan_details[' + $grid_row + '][utilized_qty]" id="delivery_challan_detail_utilized_qty_' + $grid_row + '" value="' + $obj['balanced_qty'] + '" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" type="text" class="form-control fPDecimal" name="delivery_challan_details[' + $grid_row + '][rate]" id="delivery_challan_detail_rate_' + $grid_row + '" value="' + $obj['rate'] + '" readonly />';
    $html += '<input type="hidden" class="form-control fPDecimal" name="delivery_challan_details[' + $grid_row + '][rate]" id="delivery_challan_detail_cog_rate_' + $grid_row + '" value="' + $obj['cog_rate'] + '" readonly />';
    $html += '<input type="hidden" class="form-control fDecimal" name="delivery_challan_details[' + $grid_row + '][tax_percent]" id="delivery_challan_detail_tax_percent_' + $grid_row + '" value="' + $obj['tax_percent'] + '" />';
    $html += '<input type="hidden" class="form-control fDecimal" name="delivery_challan_details[' + $grid_row + '][tax_amount]" id="delivery_challan_detail_tax_amount_' + $grid_row + '" value="' + $obj['tax_amount'] + '" />';
    $html += '<input type="hidden" class="form-control fDecimal" name="delivery_challan_details[' + $grid_row + '][net_amount]" id="delivery_challan_detail_net_amount_' + $grid_row + '" value="' + $obj['net_amount'] + '" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control fPDecimal" name="delivery_challan_details[' + $grid_row + '][amount]" id="delivery_challan_detail_amount_' + $grid_row + '" value="' + $obj['amount'] + '" readonly="true" />';
    $html += '</td>';
    $html += '<td style="width: 3%;"></td>';
    $html += '</tr>';

    $('#tblDeliveryChallan tbody').prepend($html);
    $grid_row++;

}

function calculateTotal() {
    var $total_qty = 0;
    var $total_amount = 0;

    $('#tblDeliveryChallan tbody tr').each(function () {

        $row_id = $(this).data('row_id');
        $qty = $('#delivery_challan_detail_qty_' + $row_id).val();
        $amount = $('#delivery_challan_detail_cog_amount_' + $row_id).val();

        $total_qty += parseFloat($qty);
        $total_amount += parseFloat($amount);
    })

    // console.log($total_qty, $total_amount);
    $('#total_qty').val($total_qty);
    $('#total_amount').val($total_amount);
}
