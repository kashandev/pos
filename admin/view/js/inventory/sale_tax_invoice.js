/**
 * Created by Huzaifa on 9/18/15.
 */

$(document).ready(function() {


        // $('#sale_invoice').prop("checked", true);
    
    // else
    // {
    //     $('#sale_tax_invoice').prop( "checked", true);
    //     $('#sale_invoice').prop( "checked", false);
    // }

    // $('#sale_invoice').on('change', function() {
    //     if($(this).is(":checked"))
    //     {
    //         $('#sale_tax_invoice').prop( "checked", false);
    //         $('#tax_per').attr('readonly',true); 
            
    //         // $('input[name="manual_ref_no"]').prop("readonly",false);   
    //     }

    // });
    // $('#sale_tax_invoice').on('change', function() {
    //     if($(this).is(":checked"))
    //     {
    //         $('#sale_invoice').prop( "checked", false);
    //         $('#tax_per').removeAttr('readonly');
    //         // $('input[name="manual_ref_no"]').prop("readonly",true);    
    //     }
    //     else
    //     {
    //         $('#sale_invoice').prop( "checked",true);
    //         $('#tax_per').attr('readonly',true); 
    //         // $('input[name="manual_ref_no"]').prop("readonly",false);
    //     }
    // });
});


$(document).ready(function() {
    $('#ref_document_id').select2({
        width: '100%',
        ajax: {
            url: $GetRefDocumentJson,
            dataType: 'json',
            type: 'post',
            mimeType:"multipart/form-data",
            delay: 100,
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
        minimumInputLength: 3,
        templateResult: formatReposit, // omitted for brevity, see the source of this page
        templateSelection: function(repo) {
        // $('#product_code').val(repo['product_code']);
            return (repo.document_identity) || repo.text;
        } // omitted for brevity, see the source of this page                }
    });

    // if($isEdit==1){
    //     $ref_do_no=$('#sale_tax_invoice_detail_ref_document_identity_0').val();
    //     $('#ref_document_id').val($ref_do_no);
    //     $('#addRefDocDetail').trigger('click');
    // }
});

//$(document).on('change','#ref_document_id',function() {
//    var $ref_document_id = $(this).val();
//    $.ajax({
//        url: $GetRefDocumentRecord,
//        dataType: 'json',
//        type: 'post',
//        data: 'ref_document_id='+$ref_document_id,
//        mimeType:"multipart/form-data",
//        beforeSend: function() {
////            $('#unit_id').parent().before('<i id="loader" class="fa fa-refresh fa-spin pull-right"></i>');
//        },
//        complete: function() {
////            $('#loader').remove();
//        },
//        success: function(json) {
//
//            $('#partner_id').val(json.data['partner_id']).trigger('change');
//            $('#po_no').val(json.data['po_no']);
//
//        },
//        error: function(xhr, ajaxOptions, thrownError) {
//            console.log(xhr.responseText);
//        }
//    })
//})


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
            console.log(json)
            if(json.success)
            {
                $('#partner_id').select2('destroy');
                $('#partner_id').select2({width:'100%'});
                $('#partner_id').html('<option selected="selected" value="'+(json.partner['partner_id']??'')+'">'+(json.partner['name']??'')+'</option>');
                if($customer_no == '') {
                    $('#customer_no').val(json.partner['mobile']);
                }
                else {
                    $('#customer_no').val($customer_no);
                }
     
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

$(document).on('change','#partner_id', function() {

    $partner_id = $(this).val();
    $partner_type_id = $('#partner_type_id').val();

    $.ajax({
        url: $UrlGetPartnerById,
        dataType: 'json',
        type: 'post',
        data: 'partner_type_id=' + $partner_type_id+'&partner_id='+$partner_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                if($customer_no == '') {
                    $('#customer_no').val(json.partner['mobile']);
                }
                else {
                    $('#customer_no').val($customer_no);
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



//$(document).on('change','#partner_id', function() {
//    $partner_id = $(this).val();
//    $.ajax({
//        url: $GetRefDocument,
//        dataType: 'json',
//        type: 'post',
//        data: 'partner_id='+$partner_id,
//        mimeType:"multipart/form-data",
//        beforeSend: function() {
//            $('#ref_document_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
//        },
//        complete: function() {
//            $('#loader').remove();
//        },
//        success: function(json) {
//            if(json.success)
//            {
//                $('#tblSaleInvoice tbody tr').remove();
//
//                $('#ref_document_id').select2('destroy');
//                $('#ref_document_id').html(json.html);
//                $('#ref_document_id').select2({width:'100%'});
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




// function GetDocumentDetails() {
//         var $data = {
//             ref_document_id : $('#ref_document_id').val()
//         };

//         var $details = [];
//         $.ajax({
//             url: $UrlGetDocumentDetails,
//             dataType: 'json',
//             type: 'post',
//             data: $data,
//             mimeType:"multipart/form-data",
//             beforeSend: function() {
//                 //    $('#ref_document_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
//             },
//             complete: function() {
//                 //    $('#loader').remove();
//                 $('#ref_document_id').html('');
//                 $('#ref_document_id').val(null);
//                 $('#ref_document_id').trigger('change');
//             },

//             success: function(json) {
//                 if(json.success)
//                 {
//                     $('#tblSaleInvoice tbody tr').remove();
//                     $('#po_no').val(json.po_no);
//                     $('#po_date').val(json['po_date']);
//                     $('#partner_id').val(json['partner_id']).trigger('change');
//                     $('#customer_unit_id').val(json['customer_unit_id']).trigger('change');
//                     $('#dc_no').val(json['dc_no']);
//                     $('#manual_ref_no').val(json['data']['manual_ref_no']);
//                     $.each(json.data['products'], function($i,$product) {
//                         fillGrid($product);
//                         $('#sale_tax_invoice_detail_stock_qty_'+$i).val(0);
//                         $('#sale_tax_invoice_detail_ref_dc_'+$i).val(1);
//                         $('#sale_tax_invoice_detail_warehouse_id_'+$i).trigger('change');
//                         $('#sale_tax_invoice_detail_stock_qty_'+$i).prop('readonly',true);
//                         $('#sale_tax_invoice_detail_qty_'+$i).prop('readonly',true);
//                         $('#sale_tax_invoice_detail_warehouse_id_'+$i).select2({
//                             disabled: 'readonly'
//                         });
//                         $('#sale_tax_invoice_detail_warehouse_id2_'+$i).val($product['warehouse_id']);
//                         $('#sale_tax_invoice_detail_warehouse_id_'+$i).select2({width:'100%'});
//                     });

//                     //  $details = json['details'];
//                     //  for($i=0;$i<$details.length;$i++) {
//                     //  fillGrid($details[$i]);
//                     //   }
//                 }
//                 else {
//                     alert(json.error);
//                 }
//             },
//             error: function(xhr, ajaxOptions, thrownError) {
//                 console.log(xhr.responseText);
//             }
//         })
// };


// function fillGrid($obj) {
//     var $stock_qty = ''
//     if($obj['stock_qty'])
//     {
//         $stock_qty = $obj['stock_qty'];
//     }
//     $html = '';
//     $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
//     $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
//     $html += '<td>';
//     $html += '<input type="hidden" name="sale_tax_invoice_details['+$grid_row+'][ref_document_detail_id]" id="sale_tax_invoice_detail_ref_document_detail_id_'+$grid_row+'" value="'+$obj['delivery_challan_detail_id']+'" />';
//     $html += '<input type="hidden" name="sale_tax_invoice_details['+$grid_row+'][ref_document_type_id]" id="sale_tax_invoice_detail_ref_document_type_id_'+$grid_row+'" value="'+$obj['ref_document_type_id']+'" />';
//     $html += '<input type="hidden" name="sale_tax_invoice_details['+$grid_row+'][ref_document_identity]" id="sale_tax_invoice_detail_ref_document_identity_'+$grid_row+'" value="'+$obj['ref_document_identity']+'" />';
//     $html += '<a target="_blank" href="'+$obj['href']+'">'+$obj['ref_document_identity']+'</a>';
//     $html += '</td>';
//     $html += '<td>';
//     $html += '<input onchange="getProductByCode(this);" type="text" style="min-width: 100px;" class="form-control" name="sale_tax_invoice_details['+$grid_row+'][product_code]" id="sale_tax_invoice_detail_product_code_'+$grid_row+'" value="'+$obj['product_code']+'" />';
//     $html += '</td>';
//     $html += '<td>';
//     $html += '<div class="input-group">';
//     $html += '<select style="min-width: 100px;" onchange="getProductById(this);" class="form-control select2" id="sale_tax_invoice_detail_product_id_'+$grid_row+'" name="sale_tax_invoice_details['+$grid_row+'][product_id]" >';
//     $html += '<option value="'+ $obj['product_id'] +'">'+$obj['product_name']+'</option>';
//     // $products.forEach(function($product) {
//     //     if($product['product_id'] == $obj['product_id']) {
//     //         $html += '<option value="'+$product.product_id+'" selected="true">'+$product.name+'</option>';
//     //     } else {
//     //         $html += '<option value="'+$product.product_id+'">'+$product.name+'</option>';
//     //     }
//     // });
//     $html += '</select>';
//     $html += '<span class="input-group-btn ">';
//     $html += '<button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="sale_tax_invoice_detail_product_id_'+$grid_row+'" data-field="product_id">';
//     $html += '<i class="fa fa-search"></i>';
//     $html += '</button>';
//     $html += '</span>';
//     $html += '</div>';
//     $html += '</td>';
//     $html += '<td>';
//     $html += '<input style="min-width: 100px;" type="text" class="form-control" name="sale_tax_invoice_details['+$grid_row+'][description]" id="sale_tax_invoice_detail_description_'+$grid_row+'" value="'+$obj['description']+'" />';
//     $html += '</td>';
//     $html += '<td>';
//     $html += '<select onchange="validateWarehouseStock(this);" class="form-control select2 warehouse_id" id="sale_tax_invoice_detail_warehouse_id_'+$grid_row+'" name="sale_tax_invoice_details['+$grid_row+'][warehouse_id]" >';
//     $html += '<option value="">&nbsp;</option>';
//     $warehouses.forEach(function($warehouse) {
//         if($warehouse['warehouse_id'] == $obj['warehouse_id']) {
//             $html += '<option value="'+$warehouse.warehouse_id+'" selected="true">'+$warehouse.name+'</option>';
//         } else {
//             $html += '<option value="'+$warehouse.warehouse_id+'">'+$warehouse.name+'</option>';
//         }
//     });
//     $html += '</select>';
//     $html += '<input style="min-width: 100px;" type="hidden" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][warehouse_id]" id="sale_tax_invoice_detail_warehouse_id2_'+$grid_row+'" value="" />';
//     $html += '<input style="min-width: 100px;" type="hidden" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][ref_dc]" id="sale_tax_invoice_detail_ref_dc_'+$grid_row+'" value="" />';
//     $html += '</td>';
//     $html += '<td>';
//     $html += '<input style="min-width: 100px;" type="text" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][stock_qty]" id="sale_tax_invoice_detail_stock_qty_'+$grid_row+'" value="'+$stock_qty+'" />';
//     $html += '</td>';
//     $html += '<td>';
//     $html += '<input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][qty]" id="sale_tax_invoice_detail_qty_'+$grid_row+'" value="'+$obj['balanced_qty']+'" />';
//     $html += '</td>';
//     $html += '<td>';
//     $html += '<input type="text" readonly style="min-width: 100px;" class="form-control" name="sale_tax_invoice_details['+$grid_row+'][unit]" id="sale_tax_invoice_detail_unit_'+$grid_row+'" value="'+$obj['unit']+'" />';
//     $html += '<input type="hidden" class="form-control" name="sale_tax_invoice_details['+$grid_row+'][unit_id]" id="sale_tax_invoice_detail_unit_id_'+$grid_row+'" value="'+$obj['unit_id']+'" />';
//     $html += '</td>';
//     $html += '<td>';
//     $html += '<input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][rate]" id="sale_tax_invoice_detail_rate_'+$grid_row+'" value="'+$obj['rate']+'" />';
//     $html += '<input type="hidden" class="form-control" name="sale_tax_invoice_details['+$grid_row+'][cog_rate]" id="sale_tax_invoice_detail_cog_rate_'+$grid_row+'" value="'+$obj['cog_rate']+'" />';
//     $html += '</td>';
//     $html += '<td>';
//     $html += '<input type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][amount]" id="sale_tax_invoice_detail_amount_'+$grid_row+'" value="'+$obj['amount']+'" readonly="true" />';
//     $html += '<input type="hidden" class="form-control" name="sale_tax_invoice_details['+$grid_row+'][cog_amount]" id="sale_tax_invoice_detail_cog_amount_'+$grid_row+'" value="'+$obj['cog_amount']+'" />';
//     $html += '</td>';
//     $html += '<td >';
//     $html += '<input onchange="calculateDiscountAmount(this);" type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][discount_percent]" id="sale_tax_invoice_detail_discount_percent_'+$grid_row+'" value="0" />';
//     $html += '</td>';
//     $html += '<td >';
//     $html += '<input onchange="calculateDiscountPercent(this);" type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][discount_amount]" id="sale_tax_invoice_detail_discount_amount_'+$grid_row+'" value="0" />';
//     $html += '</td>';
//     $html += '<td >';
//     $html += '<input type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][gross_amount]" id="sale_tax_invoice_detail_gross_amount_'+$grid_row+'" value="'+$obj['amount']+'" readonly="true"/>';
//     $html += '</td>';
//     $html += '<td>';
//     $html += '<input onchange="calculateTaxAmount(this);" type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][tax_percent]" id="sale_tax_invoice_detail_tax_percent_'+$grid_row+'" value="'+$obj['tax_percent']+'" />';
//     $html += '</td>';
//     $html += '<td>';
//     $html += '<input onchange="calculateTaxPercent(this);" type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][tax_amount]" id="sale_tax_invoice_detail_tax_amount_'+$grid_row+'" value="'+$obj['tax_amount']+'" />';
//     $html += '</td>';
//     $html += '<td>';
//     $html += '<input type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][total_amount]" id="sale_tax_invoice_detail_total_amount_'+$grid_row+'" value="'+$obj['net_amount']+'" readonly="true" />';
//     $html += '</td>';
//     $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
//     $html += '</tr>';


//     $('#tblSaleInvoice tbody').prepend($html);
//     // setFieldFormat();
//     $('#sale_tax_invoice_detail_product_id_'+$grid_row).select2({width: '100%'});
//     $('#sale_tax_invoice_detail_warehouse_id_'+$grid_row).select2({width: '100%'});

//     $('#ref_document_id').select2({
//         width: '100%',
//         ajax: {
//             url: $GetRefDocumentJson,
//             dataType: 'json',
//             type: 'post',
//             mimeType:"multipart/form-data",
//             delay: 100,
//             data: function (params) {
//                 return {
//                     q: params.term, // search term
//                     page: params.page
//                 };
//             },
//             processResults: function (data, params) {
//                 // parse the results into the format expected by Select2
//                 // since we are using custom formatting functions we do not need to
//                 // alter the remote JSON data, except to indicate that infinite
//                 // scrolling can be used
//                 params.page = params.page || 1;

//                 return {
//                     results: data.items,
//                     pagination: {
//                         more: (params.page * 30) < data.total_count
//                     }
//                 };
//             },
//             cache: true
//         },
//         escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
//         minimumInputLength: 3,
//         templateResult: formatReposit, // omitted for brevity, see the source of this page
//         templateSelection: function(repo) {
//         // $('#product_code').val(repo['product_code']);
//             return (repo.document_identity) || repo.text;
//         } // omitted for brevity, see the source of this page                }
//     });

//     $('#sale_tax_invoice_detail_product_id_'+$grid_row).select2({
//         width: '100%',
//         ajax: {
//             url: $UrlGetProductJSON,
//             dataType: 'json',
//             type: 'post',
//             mimeType:"multipart/form-data",
//             delay: 250,
//             data: function (params) {
//                 return {
//                     q: params.term, // search term
//                     page: params.page
//                 };
//             },
//             processResults: function (data, params) {
//                 // parse the results into the format expected by Select2
//                 // since we are using custom formatting functions we do not need to
//                 // alter the remote JSON data, except to indicate that infinite
//                 // scrolling can be used
//                 params.page = params.page || 1;

//                 return {
//                     results: data.items,
//                     pagination: {
//                         more: (params.page * 30) < data.total_count
//                     }
//                 };
//             },
//             cache: true
//         },
//         escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
//         minimumInputLength: 2,
//         templateResult: formatRepo, // omitted for brevity, see the source of this page
//         templateSelection: formatRepoSelection // omitted for brevity, see the source of this page                }
//     });
//     if($('#sale_invoice').is(":checked"))
//     {
        
//         $('#sale_tax_invoice_detail_tax_percent_'+$grid_row).attr('readonly',true);
//         $('#sale_tax_invoice_detail_tax_amount_'+$grid_row).attr('readonly',true);
//     }



//     $grid_row++;

//     calculateTotal();
// }

$(document).on('click','#btnAddGrid', function() {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td><a id="btnAddGrid" title="Add" class="btn btn-xs btn-primary" href="javascript:void(0);"><i class="fa fa-plus"></i></a><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '<td>';
    $html += '<input type="hidden" name="sale_tax_invoice_details['+$grid_row+'][ref_document_type_id]" id="sale_tax_invoice_detail_ref_document_type_id_'+$grid_row+'" value="" />';
    $html += '<input type="hidden" name="sale_tax_invoice_details['+$grid_row+'][ref_document_identity]" id="sale_tax_invoice_detail_ref_document_identity_'+$grid_row+'" value="" />';
    $html += '&nbsp;';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="getProductByCode(this);" type="text" style="min-width: 100px;" class="form-control" name="sale_tax_invoice_details['+$grid_row+'][product_code]" id="sale_tax_invoice_detail_product_code_'+$grid_row+'" value="" autocomplete="off" />';
    $html += '</td>';
    $html += '<td class="hide">';
    $html += '<input type="text" name="sale_tax_invoice_details['+ $grid_row +'][available_stock]" id="sale_tax_invoice_details_available_stock_'+ $grid_row +'" readonly disabled class="form-control">';
    $html += '</td>';
    $html += '<td style="min-width: 300px;">';
    $html += '<div class="input-group">';
    $html += '<select style="min-width: 100px;" onchange="getProductById(this);" class="form-control select2" id="sale_tax_invoice_detail_product_id_'+$grid_row+'" name="sale_tax_invoice_details['+$grid_row+'][product_id]" >';
    $html += '<option value="">&nbsp;</option>';
    // $products.forEach(function($product) {
    //     $html += '<option value="'+$product.product_id+'">'+$product.name+'</option>';
    // });
    $html += '</select>';
    $html += '<span class="input-group-btn ">';
    $html += '<button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="sale_tax_invoice_detail_product_id_'+$grid_row+'" data-field="product_id">';
    $html += '<i class="fa fa-search"></i>';
    $html += '</button>';
    $html += '</span>';
    $html += '</div>';
    $html += '</td>';


    $html += '<td style="min-width: 300px;" class="hide">';
    $html += '<input  type="text" style="min-width: 100px;" class="form-control" name="sale_tax_invoice_details['+$grid_row+'][description]" id="sale_tax_invoice_detail_description_'+$grid_row+'" value="" />';
    $html += '</td>';

    $html += '<td>';
    $html += '<input type="hidden" class="form-control select2 product_category" id="sale_tax_invoice_detail_product_category_id_'+$grid_row+'" name="sale_tax_invoice_details['+$grid_row+'][product_category_id]">';
    $html += '<input style="min-width: 300px;" class="form-control select2 product_category" id="sale_tax_invoice_detail_product_category_'+$grid_row+'" readonly>';
    $html += '</td>';

    $html += '<td class="hide">';
    $html += '<select onchange="validateWarehouseStock(this);" class="form-control select2 warehouse_id" id="sale_tax_invoice_detail_warehouse_id_'+$grid_row+'" name="sale_tax_invoice_details['+$grid_row+'][warehouse_id]" >';
    $warehouses.forEach(function($warehouse,$index) {
        $index == 0 ? $selected = 'selected="true"' : $selected = '';
        $html += '<option value="'+$warehouse.warehouse_id+'" '+$selected+'>'+$warehouse.name+'</option>';
    });
    $html += '</select>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input style="min-width: 100px;" type="text" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][stock_qty]" id="sale_tax_invoice_detail_stock_qty_'+$grid_row+'" value="0" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][qty]" id="sale_tax_invoice_detail_qty_'+$grid_row+'" value="6" />';
    $html += '</td>';
    $html += '<td class="hide">';
    $html += '<input type="text" style="min-width: 100px;" readonly class="form-control" name="sale_tax_invoice_details['+$grid_row+'][unit]" id="sale_tax_invoice_detail_unit_'+$grid_row+'" value="" />';
    $html += '<input type="hidden" class="form-control" name="sale_tax_invoice_details['+$grid_row+'][unit_id]" id="sale_tax_invoice_detail_unit_id_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][rate]" id="sale_tax_invoice_detail_rate_'+$grid_row+'" value="0" />';
    $html += '<input type="hidden" class="form-control" name="sale_tax_invoice_details['+$grid_row+'][cog_rate]" id="sale_tax_invoice_detail_cog_rate_'+$grid_row+'" value="0" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][amount]" id="sale_tax_invoice_detail_amount_'+$grid_row+'" value="0" readonly="true" />';
    $html += '<input type="hidden"class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][cog_amount]" id="sale_tax_invoice_detail_cog_amount_'+$grid_row+'" value="0" readonly="true" />';
    $html += '</td>';
    // $html += '<td>';
    // $html += '<input onchange="calculateDiscountAmount(this);" type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][discount_percent]" id="sale_tax_invoice_detail_discount_percent_'+$grid_row+'" value="0" />';
    // $html += '</td>';
    // $html += '<td>';
    // $html += '<input onchange="calculateDiscountPercent(this);" type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][discount_amount]" id="sale_tax_invoice_detail_discount_amount_'+$grid_row+'" value="0" />';
    // $html += '</td>';
    // $html += '<td>';
    // $html += '<input type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][gross_amount]" id="sale_tax_invoice_detail_gross_amount_'+$grid_row+'" value="" readonly="true"/>';
    // $html += '</td>';
    // $html += '<td>';
    // $html += '<input onchange="calculateTaxAmount(this);" type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][tax_percent]" id="sale_tax_invoice_detail_tax_percent_'+$grid_row+'" value="0" />';
    // $html += '</td>';
    // $html += '<td>';
    // $html += '<input onchange="calculateTaxPercent(this);" type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][tax_amount]" id="sale_tax_invoice_detail_tax_amount_'+$grid_row+'" value="0" />';
    // $html += '</td>';
    // $html += '<td>';
    // $html += '<input type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details['+$grid_row+'][total_amount]" id="sale_tax_invoice_detail_total_amount_'+$grid_row+'" value="" readonly="true" />';
    // $html += '</td>';
    $html += '<td><a id="btnAddGrid" title="Add" class="btn btn-xs btn-primary" href="javascript:void(0);"><i class="fa fa-plus"></i></a><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '</tr>';


    //$('#tblSaleInvoice tbody').prepend($html);
    $('#tblSaleInvoice tbody').append($html);
    // setFieldFormat();
    $('#sale_tax_invoice_detail_product_id_'+$grid_row).select2({width: '100%'});
    $('#sale_tax_invoice_detail_warehouse_id_'+$grid_row).select2({width: '100%'});
    $('#sale_tax_invoice_detail_product_code_'+$grid_row).focus();

    $('#sale_tax_invoice_detail_product_id_'+$grid_row).select2({
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

    // $().val();
    if($('#sale_invoice').is(":checked"))
    {

        $('#sale_tax_invoice_detail_tax_percent_'+$grid_row).attr('readonly',true);
        $('#sale_tax_invoice_detail_tax_amount_'+$grid_row).attr('readonly',true);
    }

    $grid_row++;
});

function getProductById($obj) {
    var $product_id = $($obj).val();
    var $partner_id = $('#partner_id').val();
    var $row_id = $($obj).parent().parent().parent().data('row_id');
    var $stock_qty = 0;
    var $sale_price = 0;
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
        
            $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).select2('destroy');
            $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).select2({width:'100%'});
            if(json.success) {
             $sale_price = parseInt(json.product['sale_price']);
            if(json.product['stock']['stock_qty']!=null){
             $stock_qty = parseInt(json.product['stock']['stock_qty']);
            }
            else {
                $stock_qty = 0;
            }
            if(json.product['sale_price']!=null){
                $sale_price = parseInt(json.product['sale_price']);
            }
            else {
                $sale_price =0;
            }

                $('#sale_tax_invoice_detail_description_'+$row_id).val(json.product['name']);
                $('#sale_tax_invoice_detail_product_code_'+$row_id).val(json.product['product_code']).trigger('change');
                $('#sale_tax_invoice_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#sale_tax_invoice_detail_stock_qty_'+$row_id).val($stock_qty);
                $('#sale_tax_invoice_detail_unit_'+$row_id).val(json.product['unit']);
                $('#sale_tax_invoice_detail_rate_'+$row_id).val($sale_price);
                $('#sale_tax_invoice_detail_product_id_'+$row_id).html('<option selected="selected" value="'+json.product['product_id']+'">'+json.product['name']+'</option>');
                $('#sale_tax_invoice_detail_product_category_id_'+$row_id).val(json.product['product_category_id']);
                $('#sale_tax_invoice_detail_product_category_'+$row_id).val(json.product['product_category']);
                $('#last_rate').val(json.customer_rate);
                //  console.log(json.customer_rate);

            } else {
                alert(json.error);
            }
              $('#sale_tax_invoice_detail_product_id_'+$row_id).select2({
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

function getProductByCode($obj) {


    var $product_code = $($obj).val();
    var $partner_id = $('#partner_id').val();
    var $stock_qty = 0;
    var $sale_price = 0;
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
           
            $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).select2('destroy');
            $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).select2({width:'100%'});
            $('#sale_tax_invoice_detail_product_id_'+$row_id).select2('destroy');
            if(json.success)
            {

             $sale_price = parseInt(json.product['sale_price']);
            if(json.product['stock']['stock_qty']!=null){
             $stock_qty = parseInt(json.product['stock']['stock_qty']);
            }
            else {
                $stock_qty = 0;
            }
            if(json.product['sale_price']!=null){
                $sale_price = parseInt(json.product['sale_price']);
            }
            else {
                $sale_price =0;
            }
                $('#sale_tax_invoice_detail_description_'+$row_id).val(json.product['name']);
                $('#sale_tax_invoice_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#sale_tax_invoice_detail_unit_'+$row_id).val(json.product['unit']);
                $('#sale_tax_invoice_detail_stock_qty_'+$row_id).val($stock_qty);

                $('#sale_tax_invoice_detail_product_id_'+$row_id).html('<option selected="selected" value="'+json.product['product_id']+'">'+json.product['name']+'</option>');
                $('#sale_tax_invoice_detail_product_category_id_'+$row_id).val(json.product['product_category_id']);
                $('#sale_tax_invoice_detail_product_category_'+$row_id).val(json.product['product_category']);
                $('#sale_tax_invoice_detail_rate_'+$row_id).val($sale_price);
                $('#last_rate').val(json.customer_rate);
            } else {
                alert(json.error);
                $('#sale_tax_invoice_detail_description_'+$row_id).val('');
                $('#sale_tax_invoice_detail_unit_id_'+$row_id).val('');
                $('#sale_tax_invoice_detail_unit_'+$row_id).val('');
                $('#sale_tax_invoice_detail_stock_qty_'+$row_id).val(0);
                
                $('#sale_tax_invoice_detail_product_id_'+$row_id).html('');
                $('#sale_tax_invoice_detail_product_category_id_'+$row_id).val('');
                $('#sale_tax_invoice_detail_product_category_'+$row_id).val('');
                $('#sale_tax_invoice_detail_rate_'+$row_id).val('0');
                $('#last_rate').val('0');
            }
            $('#sale_tax_invoice_detail_product_id_'+$row_id).select2({
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


    var $warehouse_id = $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).val();
    var $product_id = $('#sale_tax_invoice_detail_product_id_'+$row_id).val();
    var $ref_document_identity = $('#sale_tax_invoice_detail_ref_document_identity_'+$row_id).val();
    var $document_identity = '';
    // if( $isEdit ) {
    //     $document_identity = $('[name="document_identity"]').val();
    // }
    var $change_qty = parseInt($('#sale_tax_invoice_detail_qty_'+$row_id).val()) || 0;
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
            if(json.success)
            {
              $('#sale_tax_invoice_detail_cog_rate_'+$row_id).val(json['avg_stock_rate']);
              $('#sale_tax_invoice_detail_rate_'+$row_id).trigger('change');

         
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })



        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })

}

function setProductInformation($obj) {
    var $data = $($obj).data();
    var $row_id = $('#'+$data['element']).parent().parent().parent().data('row_id');
    // var $sale_price = parseInt($data['sale_price']);
    $('#_modal').modal('hide');
    $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).select2('destroy');
    $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).select2({width:'100%'});
    $('#sale_tax_invoice_detail_description_'+$row_id).val($data['name']);
    $('#sale_tax_invoice_detail_product_code_'+$row_id).val($data['product_code']).trigger('change');
    $('#sale_tax_invoice_detail_unit_id_'+$row_id).val($data['unit_id']);
    $('#sale_tax_invoice_detail_unit_'+$row_id).val($data['unit']);
    $('#sale_tax_invoice_detail_rate_'+$row_id).val($sale_price);
    $('#sale_tax_invoice_detail_product_id_'+$row_id).select2('destroy');
    $('#sale_tax_invoice_detail_product_id_'+$row_id).html('<option selected="selected" value="'+$data['product_id']+'">'+$data['name']+'</option>');
    $('#sale_tax_invoice_detail_product_id_'+$row_id).select2({
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

function calculateRowTotal($obj, $ref = false,$isEdit = false) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $ref_document_identity = $('#sale_tax_invoice_detail_ref_document_identity_'+$row_id).val();
    var $available_stock = parseInt($('#sale_tax_invoice_detail_stock_qty_'+$row_id).val()) || 0;
    var $qty = parseInt($('#sale_tax_invoice_detail_qty_' + $row_id).val());
    if(  ($qty>$available_stock) && $allow_out_of_stock == 0 && $ref_document_identity == '' ) {
        alert('Stock not available');
        $('#sale_tax_invoice_detail_qty_' + $row_id).val(0);
    } else {
        var $rate = parseInt($('#sale_tax_invoice_detail_rate_' + $row_id).val());
        var $cog_rate = parseInt($('#sale_tax_invoice_detail_cog_rate_' + $row_id).val());

        var $amount = $qty * $rate;
        var $cog_amount = $qty * $cog_rate;
        $amount = roundUpto($amount,0);
        $cog_amount = roundUpto($cog_amount,0);
        var $dis_percent = parseInt($('#sale_tax_invoice_detail_discount_percent_'+ $row_id).val() || 0);
        var $discount_amount = roundUpto($amount * $dis_percent / 100,0);
        var $tax_percent = parseInt($('#sale_tax_invoice_detail_tax_percent_'+ $row_id).val() || 0);
        var $tax_amount = roundUpto($amount * $tax_percent / 100,0);
        var $total_amount = roundUpto($amount,0);
        // var $gross_amount = roundUpto($amount - $discount_amount,0);
        // var $tax_amount = parseInt($('#sale_tax_invoice_detail_tax_amount_' + $row_id).val());
        // var $total_amount = roundUpto($gross_amount + $tax_amount,0);
        $('#sale_tax_invoice_detail_cog_amount_' + $row_id).val($cog_amount);
        $('#sale_tax_invoice_detail_amount_' + $row_id).val($amount);
        // $('#sale_tax_invoice_detail_discount_amount_' + $row_id).val($discount_amount);
        // $('#sale_tax_invoice_detail_gross_amount_' + $row_id).val($gross_amount);
        // $('#sale_tax_invoice_detail_tax_amount_' + $row_id).val($tax_amount);
        $('#sale_tax_invoice_detail_total_amount_' + $row_id).val($total_amount);

        calculateTotal();
    }
}

function calculateTotal() {
    var $total_qty = 0;
    var $item_amount = 0;
    var $item_discount = 0;
    var $item_tax = 0;
    var $item_total = 0;
    $('#tblSaleInvoice tbody tr').each(function() {
        $row_id = $(this).data('row_id');
        $qty = $('#sale_tax_invoice_detail_qty_' + $row_id).val();
        $amount = $('#sale_tax_invoice_detail_amount_' + $row_id).val();
        $discount_amount = $('#sale_tax_invoice_detail_discount_amount_' + $row_id).val();
        $tax_amount = $('#sale_tax_invoice_detail_tax_amount_' + $row_id).val();
        $total_amount = $('#sale_tax_invoice_detail_total_amount_' + $row_id).val();

        $total_qty += parseInt($qty);
        $item_amount += parseInt($amount);
        // $item_discount += parseInt($discount_amount);
        // $item_tax += parseInt($tax_amount);
        // $item_total += parseInt($total_amount);
    })

    var $discount = $('#discount_amount').val() || 0;
    var $cartage = $('#cartage').val() || 0;
    var $cash_received = $('#cash_received').val() || 0;
    var $net_amount = parseInt($item_amount) - parseInt($discount) + parseInt($cartage);
    // var $net_amount = parseInt($item_total) - parseInt($discount) + parseInt($cartage);
    var $balance_amount = parseInt($net_amount) - parseInt($cash_received);
    $('#total_qty').val(roundUpto($total_qty,0));
    $('#item_amount').val(roundUpto($item_amount,0));
    // $('#item_discount').val(roundUpto($item_discount,0));
    // $('#item_tax').val(roundUpto($item_tax,0));
    // $('#item_total').val(roundUpto($item_total,0));
    $('#discount_amount').val(roundUpto($discount,0));
    $('#cartage').val(roundUpto($cartage,0));
    $('#net_amount').val(roundUpto($net_amount,0));
    if($cash_received!=0){
        $('#balance_amount').val(roundUpto($balance_amount,0));
    }
    else {
        $('#balance_amount').val('0');
    }

}

function calculateDiscountAmount($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $discount_percent = parseInt($($obj).val() || 0);
    var $amount = parseInt($('#sale_tax_invoice_detail_amount_' + $row_id).val() || 0);
    var $discount_amount = roundUpto($amount * $discount_percent / 100,0);
    $('#sale_tax_invoice_detail_discount_amount_' + $row_id).val($discount_amount);
    calculateRowTotal($obj);
}

function calculateDiscountPercent($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $discount_amount = parseInt($($obj).val() || 0);
    var $amount = parseInt($('#sale_tax_invoice_detail_amount_' + $row_id).val() || 0);
    var $discount_percent = roundUpto($discount_amount / $amount * 100,0);

    $('#sale_tax_invoice_detail_discount_percent_' + $row_id).val($discount_percent);
    calculateRowTotal($obj);
}

function calculateTaxAmount($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $tax_percent = parseInt($($obj).val() || 0);
    var $amount = parseInt($('#sale_tax_invoice_detail_amount_' + $row_id).val() || 0);
    var $tax_amount = roundUpto($amount * $tax_percent / 100,0);

    $('#sale_tax_invoice_detail_tax_amount_' + $row_id).val($tax_amount);
    calculateRowTotal($obj);
}

function calculateTaxPercent($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $tax_amount = parseInt($($obj).val() || 0);
    var $amount = parseInt($('#sale_tax_invoice_detail_amount_' + $row_id).val() || 0);
    var $tax_percent = roundUpto($tax_amount / $amount * 100,0);

    $('#sale_tax_invoice_detail_tax_percent_' + $row_id).val($tax_percent);
    calculateRowTotal($obj);
}

function AddTaxes() {
    var $tax_per = $('#tax_per').val();

    $('#tblSaleInvoice tbody tr').each(function() {

        $row_id = $(this).data('row_id');

        var $amount = parseInt($('#sale_tax_invoice_detail_amount_' + $row_id).val()) || 0;

        var $wht_percent = parseInt($('#sale_tax_invoice_detail_tax_percent_' + $row_id).val($tax_per)) || 0;

        var $wht_amount = roundUpto(($amount * $tax_per / 100),0);
        $('#sale_tax_invoice_detail_tax_amount_' + $row_id).val($wht_amount);


        var $net_amount = $amount + $wht_amount;

        $('#sale_tax_invoice_detail_total_amount_' + $row_id).val(roundUpto($net_amount,0));
        $('#sale_tax_invoice_detail_amount_' + $row_id).val(roundUpto($amount,0));


    })

    calculateTotal();
}

function Save() {
    $('.warehouse_id').each(function() {
        $(this).rules("add", 
            {
                required: true,
                // remote: {url: $url_validate_stock, type: 'post'},
                messages: {
                    required: "Warehouse is required",
                  }
            });
    });
    $('.btnsave').attr('disabled','disabled');
    if($('#form').valid() == true){
        $('#form').submit();
    }
    else{
        $('.btnsave').removeAttr('disabled');
    }
}

$('[name="document_date"]').change(function(){

    $('#tblSaleInvoice tbody tr').each(function() {
        $row_id = $(this).data('row_id');        
        $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).trigger('change');
    });


});

function validateWarehouseStock($obj, $isEdit = false)
{
    var $row_id = $($obj).parent().parent().data('row_id');
    var $warehouse_id = $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).val();
    var $product_id = $('#sale_tax_invoice_detail_product_id_'+$row_id).val();
    var $ref_document_identity = $('#sale_tax_invoice_detail_ref_document_identity_'+$row_id).val();
    var $document_identity = '';
    if( $isEdit ) {
        $document_identity = $('[name="document_identity"]').val();
    }

    var $change_qty = parseInt($('#sale_tax_invoice_detail_qty_'+$row_id).val()) || 0;
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
            if(json.success)
            {
                $available_stock = parseInt(json.stock_qty) || 0;
                $('#sale_tax_invoice_detail_stock_qty_'+$row_id).val($available_stock);

                if( !$isEdit ) {
                    if(($change_qty > $available_stock) && $allow_out_of_stock == 0)
                    {
                        if($ref_document_identity == '')
                        {
                            // alert('hello');
                            alert('Stock not available');
                            // $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).append('<label style="color:red;"></label>');
                            $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).select2('destroy');
                            $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).val('');
                            $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).select2({width:'100%'});
                            // $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).val('');
                            // $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).val('');
                        }
                    }
                } else {
                    $('#sale_tax_invoice_detail_stock_qty_'+$row_id).val(parseInt(json.stock_qty));
                    if(($change_qty > $available_stock) && $allow_out_of_stock == 0)
                    {
                        if($ref_document_identity == '')
                        {
                            // alert('hello');
                            alert('Stock not available');
                            // $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).append('<label style="color:red;"></label>');
                            $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).select2('destroy');
                            $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).val('');
                            $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).select2({width:'100%'});
                            // $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).val('');
                            // $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).val('');
                        }
                    }
                    if($ref_document_identity)
                    {
                         $('#sale_tax_invoice_detail_qty_'+$row_id).prop('readonly', true);
                        $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).select2({
                            disabled: 'readonly'
                        });
                        $('#sale_tax_invoice_detail_warehouse_id_'+$row_id).select2({width:'100%'});
                    }
                }
                if($ref_document_identity == '')
                {
                 $('#sale_tax_invoice_detail_cog_rate_'+$row_id).val(json['avg_stock_rate']);
                }
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
}